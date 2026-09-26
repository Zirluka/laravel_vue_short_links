<?php

namespace App\Jobs;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class TrackClickJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $linkId,
        public ?string $ip,
        public ?string $userAgent,
        public ?string $referer,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Если ссылка уже была удалена владельцем во время нахождения задачи в очереди
        if (!Link::where('id', $this->linkId)->exists()) {
            return;
        }

        $agent = new Agent();
        if ($this->userAgent) {
            $agent->setUserAgent($this->userAgent);
        }

        if ($agent->isMobile()) {
            $deviceType = 'mobile';
        } elseif ($agent->isTablet()) {
            $deviceType = 'tablet';
        } elseif ($agent->isDesktop()) {
            $deviceType = 'desktop';
        } else {
            $deviceType = 'another';
        }

        $country = null;
        $city = null;

        if ($this->ip && !in_array($this->ip, ['127.0.0.1', '::1'], true)) {
            try {
                if ($position = Location::get($this->ip)) {
                    $country = $position->countryName ?: null;
                    $city = $position->cityName ?: null;
                }
            } catch (\Throwable) {
                // Игнорируем сетевые ошибки GeoIP
            }
        }

        Click::create([
            'link_id'     => $this->linkId,
            'ip'          => $this->ip,
            'country'     => $country,
            'city'        => $city,
            'referer'     => $this->referer ? Str::limit($this->referer, 1000, '') : null,
            'user_agent'  => $this->userAgent ? Str::limit($this->userAgent, 500, '') : null,
            'device_type' => $deviceType,
            'os'          => $agent->platform() ?: 'Unknown',
            'browser'     => $agent->browser() ?: 'Unknown',
            'clicked_at'  => now(),
        ]);

        Link::where('id', $this->linkId)->increment('clicks_count');
    }
}
