<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [ "link_id", "ip", "country", "city", "referer", "user_agent", "device_type", "os", "browser", "clicked_at" ];

    public function link(): BelongsTo {
        return $this->belongsTo(Link::class);
    }

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }
}
