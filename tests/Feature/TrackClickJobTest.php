<?php

use App\Jobs\TrackClickJob;
use App\Models\Link;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

test('job correctly parses user agent, geoip location, stores click, and increments link clicks_count', function () {
    // 1. Мокаем вызов GeoIP: подменяем ответ на фиксированный объект Position
    $mockPosition = new Position();
    $mockPosition->countryName = 'United States';
    $mockPosition->cityName = 'Ashburn';

    Location::shouldReceive('get')
        ->once()
        ->with('8.8.8.8')
        ->andReturn($mockPosition);

    $link = Link::factory()->create([
        'clicks_count' => 0,
    ]);

    $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
    $ip = '8.8.8.8';
    $referer = 'https://t.me/';

    $job = new TrackClickJob($link->id, $ip, $userAgent, $referer);
    $job->handle();

    // 2. Проверяем счетчик
    $link->refresh();
    expect($link->clicks_count)->toBe(1);

    // 3. Проверяем данные клика и локацию в БД
    $this->assertDatabaseHas('clicks', [
        'link_id' => $link->id,
        'ip' => $ip,
        'country' => 'United States',
        'city' => 'Ashburn',
        'referer' => $referer,
        'device_type' => 'mobile',
        'os' => 'iOS',
        'browser' => 'Safari',
    ]);
});
