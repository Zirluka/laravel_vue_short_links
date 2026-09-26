<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    use HasFactory;
    protected $fillable = [ "id", "user_id", "original_url", "short_code", "password", "expired_at", "is_active", "clicks_count" ];

    public $incrementing = true;

    protected $hidden = ["password"];

    protected function casts(): array {
        return [
            "expired_at" => "datetime",
            "is_active" => "boolean"
        ];
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // Проверка на истекшую ссылку
    public function isExpired(): bool {
        return $this->expired_at !== null && $this->expired_at->isPast();
    }

    // Скоуп для выборки только активных ссылок
    public function scopeActive(Builder $query): Builder {
        return $query->where('is_active', true)
            ->where(function (Builder $sub) {
                $sub->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            });
    }

    // Связь с кликами
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

}
