<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Jobs\TrackClickJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Очищаем тестовый Redis перед каждым запуском
    Redis::flushall();

    // Подменяем все HTTP-запросы валидатора успешным ответом 200
    Http::fake([
        '*' => Http::response('OK', 200),
    ]);
});

// --- 1. ТЕСТЫ ПОЛУЧЕНИЯ ССЫЛКИ (GET URL И РАБОТА С КЭШЕМ REDIS) ---

test('can retrieve public link and warms redis cache', function () {
    $link = Link::factory()->create([
        'original_url' => 'https://example.com/target',
        'short_code' => 'pub123',
        'is_active' => true,
        'password' => null,
    ]);

    // 1. Первый запрос: кэша в Redis нет, читаем из базы и кэшируем
    $response = $this->getJson("/api/{$link->short_code}");

    $response->assertOk()
        ->assertExactJson(['data' => 'https://example.com/target']);

    // Проверяем, что ключ физически появился в Redis
    $cached = Redis::get("links:code:{$link->short_code}");
    expect($cached)->not->toBeNull();
    expect(json_decode($cached, true)['original_url'])->toBe('https://example.com/target');

    // 2. Второй запрос: проверяем чтение из Redis
    $this->getJson("/api/{$link->short_code}")
        ->assertOk()
        ->assertExactJson(['data' => 'https://example.com/target']);
});

test('returns 403 when trying to access password protected link without password', function () {
    $link = Link::factory()->create([
        'short_code' => 'secret123',
        'password' => Hash::make('123456'),
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/{$link->short_code}");

    $response->assertForbidden()
        ->assertJsonPath('message', 'Link has password');
});

test('can retrieve password protected link with correct password', function () {
    $link = Link::factory()->create([
        'original_url' => 'https://secret-domain.com',
        'short_code' => 'pass123',
        'password' => Hash::make('secret_pass'),
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/{$link->short_code}/guard", [
        'password' => 'secret_pass',
    ]);

    $response->assertOk()
        ->assertExactJson(['data' => 'https://secret-domain.com']);
});

test('returns 422 when accessing protected link with invalid password', function () {
    $link = Link::factory()->create([
        'short_code' => 'pass123',
        'password' => Hash::make('correct_pass'),
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/{$link->short_code}/guard", [
        'password' => 'wrong_pass',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('returns 404 for inactive or expired links', function () {
    // Неактивная ссылка
    $inactive = Link::factory()->create([
        'short_code' => 'dead123',
        'is_active' => false,
    ]);

    // Протухшая ссылка
    $expired = Link::factory()->create([
        'short_code' => 'old123',
        'is_active' => true,
        'expired_at' => now()->subDay(),
    ]);

    $this->getJson("/api/{$inactive->short_code}")->assertNotFound();
    $this->getJson("/api/{$expired->short_code}")->assertNotFound();
});

// --- 2. ТЕСТЫ СОЗДАНИЯ ССЫЛОК (STORE) ---

test('anonymous user can create short link', function () {
    $response = $this->postJson('/api', [
        'link' => 'https://laravel.com/docs',
    ]);

    $response->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['status', 'code']);

    $code = $response->json('code');

    // Проверяем, что ссылка создана в БД без user_id и сразу закэширована
    $this->assertDatabaseHas('links', [
        'short_code' => $code,
        'user_id' => null,
    ]);
    expect(Redis::get("links:code:{$code}"))->not->toBeNull();
});

test('authenticated user can create password protected link', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api', [
        'link' => 'https://github.com',
        'password' => 'qwerty1234',
    ]);

    $response->assertCreated();
    $code = $response->json('code');

    $link = Link::where('short_code', $code)->first();
    expect($link->user_id)->toBe($user->id);
    expect(Hash::check('qwerty1234', $link->password))->toBeTrue();
});

// --- 3. ТЕСТЫ УПРАВЛЕНИЯ ССЫЛКОЙ И ИНВАЛИДАЦИИ КЭША REDIS ---

test('user can update password and it invalidates redis cache', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'user_id' => $user->id,
        'short_code' => 'inv123',
        'is_active' => true,
    ]);

    // Прогреваем кэш
    Redis::set("links:code:{$link->short_code}", json_encode(['dummy' => 'data']));

    $response = $this->actingAs($user)->patchJson("/api/link/{$link->id}/password", [
        'password' => 'new_secret_pwd',
    ]);

    $response->assertOk();

    // Ключ в Redis должен быть удален (инвалидирован)
    expect(Redis::get("links:code:{$link->short_code}"))->toBeNull();
});

test('user can change link activity state and it invalidates cache', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'user_id' => $user->id,
        'short_code' => 'act123',
        'is_active' => true,
    ]);

    // Прогреваем кэш
    Redis::set("links:code:{$link->short_code}", json_encode(['dummy' => 'data']));

    $this->actingAs($user)->patchJson("/api/link/{$link->id}/active", [
        'is_active' => false,
    ])->assertOk();

    // Проверяем удаление из кэша
    expect(Redis::get("links:code:{$link->short_code}"))->toBeNull();
    $this->assertDatabaseHas('links', [
        'id' => $link->id,
        'is_active' => false,
    ]);
});

test('user can delete own link and it clears redis key', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'user_id' => $user->id,
        'short_code' => 'del123',
        'is_active' => true,
    ]);

    Redis::set("links:code:{$link->short_code}", 'test_payload');

    $this->actingAs($user)->deleteJson("/api/link/{$link->id}")
        ->assertNoContent();

    expect(Redis::get("links:code:{$link->short_code}"))->toBeNull();
    $this->assertDatabaseMissing('links', ['id' => $link->id]);
});

test('user cannot update or delete someone elses link', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $owner->id,
        'is_active' => true,
    ]);

    // Чужой пользователь пытается удалить ссылку -> 404 (firstOrFail)
    $this->actingAs($attacker)
        ->deleteJson("/api/link/{$link->id}")
        ->assertNotFound();

    // Пытается сменить активность -> 404
    $this->actingAs($attacker)
        ->patchJson("/api/link/{$link->id}/active", ['is_active' => false])
        ->assertNotFound();

    $this->assertDatabaseHas('links', ['id' => $link->id]);
});

test('visiting short link dispatches TrackClickJob asynchronously', function () {
    Queue::fake();

    $link = Link::factory()->create([
        'short_code' => 'track123',
        'is_active' => true,
        'password' => null,
    ]);

    $this->getJson("/api/{$link->short_code}", [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Referer' => 'https://google.com',
    ])->assertOk();

    // Проверяем, что задача была отправлена с правильными аргументами
    Queue::assertPushed(TrackClickJob::class, function (TrackClickJob $job) use ($link) {
        return $job->linkId === $link->id
            && $job->referer === 'https://google.com';
    });
});

test('cannot create link pointing to self domain', function () {
    config(['app.url' => 'https://ziplink.ru']);

    $response = $this->postJson('/api', [
        'link' => 'https://ziplink.ru/some-page',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['link']);
});

test('cannot create link if destination returns not 200', function () {
    Http::fake([
        'https://broken-site.com' => Http::response('Not Found', 404),
    ]);

    $response = $this->postJson('/api', [
        'link' => 'https://broken-site.com',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors(['link']);
});
