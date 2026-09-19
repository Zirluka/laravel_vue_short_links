<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Click>
 */
class ClickFactory extends Factory
{
    protected $model = Click::class;

    public function definition(): array
    {
        $devices = ['desktop', 'mobile', 'tablet'];
        $browsers = ['Chrome', 'Safari', 'Firefox', 'Edge'];
        $operatingSystems = ['Windows', 'iOS', 'Android', 'macOS', 'Linux'];
        $referers = [
            'https://google.com',
            'https://yandex.ru',
            'https://t.me/',
            'https://vk.com',
            null,
        ];

        return [
            'link_id' => Link::factory(),
            'ip' => fake()->ipv4(),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'referer' => fake()->randomElement($referers),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement($devices),
            'os' => fake()->randomElement($operatingSystems),
            'browser' => fake()->randomElement($browsers),
            'clicked_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
