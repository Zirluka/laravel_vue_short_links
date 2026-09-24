<?php

namespace App\Http\Services;

use App\Exceptions\EmptyStringException;
use App\Models\Link;
use App\Http\Services\Base62Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShortLinkService
{
    public function __construct(
        private readonly Base62Service $base62
    ) {}

    public function shortLink(
        string $link,
        ?string $password = null,
        ?int $userId = null,
        ?string $expiredAt = null
    ): Link {
        if (blank($link)) {
            throw new EmptyStringException("Ссылка не должна быть пустой.");
        }

        // Если гость — строго 7 дней по ТЗ, если авторизован — берем его дату
        $expiration = $userId === null ? now()->addDays(7) : $expiredAt;

        return DB::transaction(function () use ($link, $password, $userId, $expiration) {
            // 1. Создаем запись со временным уникальным плейсхолдером
            $model = Link::create([
                'user_id' => $userId,
                'original_url' => $link,
                'password' => $password ? Hash::make($password) : null,
                'short_code' => 'temp_' . Str::random(10),
                'expired_at' => $expiration ?? now()->addDays(7),
                'is_active' => true,
            ]);

            // 2. Получаем настоящий инкрементный ID и кодируем в Base62
            $model->short_code = $this->base62->encode($model->id);
            $model->save();

            return $model;
        });
    }
}
