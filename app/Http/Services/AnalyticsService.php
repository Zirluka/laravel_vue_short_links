<?php

namespace App\Http\Services;

use App\Models\Link;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // Получение сводной информации по определенной ссылке
    public function getLinkStats(Link $link, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        // 1. Клики по дням
        $clicksOverTime = $link->clicks()
                                ->where('clicked_at', '>=', $startDate)
                                ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                                ->groupBy('date')
                                ->orderBy('date', 'ASC')
                                ->get()
                                ->pluck('count', 'date');

        // 2. Распределение по странам (топ 10)
        $countries = $link->clicks()
                            ->select('country', DB::raw('count(*) as count'))
                            ->groupBy('country')
                            ->orderByDesc('count')
                            ->limit(10)
                            ->get()
                            ->map(fn($item) => [
                                "name" => $item->country ?: "Unknown",
                                "count" => (int) $item->count
                            ]);

        // 3. Распределение по типам устройств
        $devices = $link->clicks()
                        ->select('device_type', DB::raw('count(*) as count'))
                        ->groupBy('device_type')
                        ->orderByDesc('count')
                        ->get()
                        ->map(fn($item) => [
                            'name' => $item->device_type ?: 'Unknown',
                            'count' => (int) $item->count
                        ]);

        // 4. Популярные браузеры
        $browsers = $link->clicks()
                        ->select('browser', DB::raw('count(*) as count'))
                        ->groupBy('browser')
                        ->orderByDesc('count')
                        ->limit(5)
                        ->get()
                        ->map(fn($item) => [
                            'name' => $item->browser ?: 'Unknown',
                            'count' => (int) $item->count
                        ]);

        // 5. OS
        $os = $link->clicks()
                    ->select('os', DB::raw('count(*) as count'))
                    ->groupBy('os')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get()
                    ->map(fn($item) => [
                            'name' => $item->os ?: 'Unknown',
                            'count' => (int) $item->count
                    ]);

        // 5. Referers
        $referers = $link->clicks()
                    ->select('referer', DB::raw('count(*) as count'))
                    ->whereNotNull('referer')
                    ->groupBy('referer')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                    ->map(fn($item) => [
                            'name' => $item->referer ?: 'Unknown',
                            'count' => (int) $item->count
                    ]);

        return [
            'total_clicks' => $link->clicks_count,
            'clicks_over_time' => $clicksOverTime,
            'countries' => $countries,
            'devices' => $devices,
            'browsers' => $browsers,
            'platforms' => $os,
            'referers' => $referers,
        ];
    }

}
