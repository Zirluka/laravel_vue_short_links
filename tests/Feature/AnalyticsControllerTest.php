<?php

use App\Models\Click;
use App\Models\Link;
use App\Models\User;

test('owner can retrieve analytics for their link', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'user_id' => $user->id,
        'short_code' => 'stat123',
        'clicks_count' => 2,
    ]);

    Click::factory()->create([
        'link_id' => $link->id,
        'country' => 'United States',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'clicked_at' => now(),
    ]);

    Click::factory()->create([
        'link_id' => $link->id,
        'country' => 'Germany',
        'device_type' => 'mobile',
        'browser' => 'Safari',
        'clicked_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/link/{$link->short_code}/analytics");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_clicks',
                'clicks_over_time',
                'countries',
                'devices',
                'browsers',
                'platforms',
                'referers',
            ]
        ])
        ->assertJsonPath('data.total_clicks', 2);
});

test('stranger cannot view analytics of another user link', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $owner->id,
        'short_code' => 'secret123',
    ]);

    $this->actingAs($stranger)
        ->getJson("/api/link/{$link->short_code}/analytics")
        ->assertForbidden();
});
