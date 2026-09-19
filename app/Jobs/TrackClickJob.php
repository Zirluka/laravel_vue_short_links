<?php

namespace App\Jobs;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class TrackClickJob implements ShouldQueue
{
    use Queueable;

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
        $agent = new Agent();
        if ($this->userAgent) {
            $agent->setUserAgent($this->userAgent);
        }

        if ($agent->isMobile()) {
            $deviceType = 'mobile';
        } else if ($agent->isTablet()) {
            $deviceType = 'tablet';
        } else if ($agent->isDesktop()) {
            $deviceType = 'desktop';
        } else {
            $deviceType = 'another';
        }

        $country = null;
        $city = null;

        if ($this->ip) {
            try {
                if ($position = Location::get($this->ip)) {
                    $country = $position->countryName ?: null;
                    $city = $position->cityName ?: null;
                }
            } catch (\Throwable) {
                // GeoIP не должен прерывать запись клика при сетевом сбое
            }
        }

        Click::create([
            'link_id' => $this->linkId,
            'ip' => $this->ip,
            'country' => $country,
            'city' => $city,
            'referer' => $this->referer,
            'user_agent' => $this->userAgent,
            'device_type' => $deviceType,
            'os' => $agent->platform() ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'clicked_at' => now(),
        ]);

        Link::where('id', $this->linkId)->increment("clicks_count");
    }
}
