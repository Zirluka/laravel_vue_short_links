<?php

namespace App\Http\Services;

use App\Exceptions\EmptyStringException;
use App\Models\Link;
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

        $expiration = $userId === null ? now()->addDays(7) : $expiredAt;

        // Если это PostgreSQL (продакшен / dev) — используем быстрый single-insert через sequence
        if (DB::connection()->getDriverName() === 'pgsql') {
            $nextId = (int) DB::scalar("SELECT nextval('links_id_seq')");
            $shortCode = $this->base62->encode($nextId);

            return Link::create([
                'id'           => $nextId,
                'user_id'      => $userId,
                'original_url' => $link,
                'password'     => $password ? Hash::make($password) : null,
                'short_code'   => $shortCode,
                'expired_at'   => $expiration,
                'is_active'    => true,
            ]);
        }

        // Фоллбэк для SQLite (тесты) и других СУБД:
        return DB::transaction(function () use ($link, $password, $userId, $expiration) {
            $model = Link::create([
                'user_id'      => $userId,
                'original_url' => $link,
                'password'     => $password ? Hash::make($password) : null,
                'short_code'   => 'tmp_' . Str::random(12),
                'expired_at'   => $expiration,
                'is_active'    => true,
            ]);

            $model->short_code = $this->base62->encode($model->id);
            $model->save();

            return $model;
        });
    }
}
