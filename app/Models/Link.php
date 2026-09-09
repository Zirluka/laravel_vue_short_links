<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [ "user_id", "original_url", "short_code", "password", "expired_at", "is_active", "clicks_count" ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
