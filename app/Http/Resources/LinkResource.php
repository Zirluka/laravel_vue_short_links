<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'original_url' => $this->original_url,
            'short_code' => $this->short_code,
            'has_password' => $this->password != null,
            'expired_at' => $this->expired_at,
            'is_active' => $this->is_active,
            'clicks_count' => $this->clicks_count
        ];
    }
}
