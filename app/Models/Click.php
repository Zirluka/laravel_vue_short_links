<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [ "link_id", "ip", "country", "city", "referer", "user_agent", "device_type", "os", "browser", "clicked_at" ];

    public function link() {
        return $this->belongsTo(Link::class);
    }

}
