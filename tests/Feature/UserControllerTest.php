<?php

use App\Models\User;

test("user can get, update and delete account", function () {
    $user = User::factory()->create();

    // 1. Получение профиля
    $this->actingAs($user)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);

    // 2. Обновления профиля (имени)
    $this->actingAs($user)
        ->patchJson('/api/user', ["name" => "Updated Name"])
        ->assertOk()
        ->assertJsonPath("user.name", "Updated Name");

    // 3. Удаления профиля
    $this->actingAs($user)
        ->deleteJson('/api/user')
        ->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
