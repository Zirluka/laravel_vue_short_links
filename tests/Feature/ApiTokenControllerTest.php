<?php

use App\Models\User;

test('user can list their personal access tokens', function () {
    $user = User::factory()->create();
    $user->createToken('First Token');
    $user->createToken('Second Token');

    $response = $this->actingAs($user)
        ->getJson('/api/tokens');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['name' => 'First Token'])
        ->assertJsonFragment(['name' => 'Second Token']);
});

test('user can create new personal access token and receives plain text token', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/tokens', [
            'name' => 'Mobile App Token',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'token',
            'data' => ['id', 'name', 'created_at'],
        ]);

    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('Mobile App Token');
});

test('user can revoke a specific token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('To Be Deleted');

    $response = $this->actingAs($user)
        ->deleteJson("/api/tokens/{$token->accessToken->id}");

    $response->assertOk();
    expect($user->tokens()->count())->toBe(0);
});

test('user cannot revoke token belonging to another user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $tokenB = $userB->createToken('User B Token');

    $response = $this->actingAs($userA)
        ->deleteJson("/api/tokens/{$tokenB->accessToken->id}");

    $response->assertNotFound();
    expect($userB->tokens()->count())->toBe(1);
});
