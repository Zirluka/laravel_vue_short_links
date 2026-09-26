<?php

namespace App\Http\Services;

use App\Models\Link;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getLinkStats(Link $link, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        // Базовый запрос с фильтром по дате для консистентности всех графиков
        $scopedClicks = fn() => $link->clicks()->where('clicked_at', '>=', $startDate);

        // 1. Клики по дням (с заполнением нулями пропущенных дат)
        $clicksRaw = $scopedClicks()
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $clicksOverTime = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $clicksOverTime[$date] = (int) ($clicksRaw[$date] ?? 0);
        }

        // 2. Распределение по странам (топ 10)
        $countries = $scopedClicks()
            ->select('country', DB::raw('count(*) as count'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->country ?: 'Unknown',
                'count' => (int) $item->count,
            ]);

        // 3. Распределение по типам устройств
        $devices = $scopedClicks()
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->device_type ?: 'Unknown',
                'count' => (int) $item->count,
            ]);

        // 4. Популярные браузеры (топ 5)
        $browsers = $scopedClicks()
            ->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->browser ?: 'Unknown',
                'count' => (int) $item->count,
            ]);

        // 5. Операционные системы (топ 5)
        $os = $scopedClicks()
            ->select('os', DB::raw('count(*) as count'))
            ->groupBy('os')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->os ?: 'Unknown',
                'count' => (int) $item->count,
            ]);

        // 6. Источники переходов (топ 10)
        $referers = $scopedClicks()
            ->select('referer', DB::raw('count(*) as count'))
            ->whereNotNull('referer')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name'  => $item->referer,
                'count' => (int) $item->count,
            ]);

        return [
            'total_clicks'     => $link->clicks_count,
            'clicks_over_time' => $clicksOverTime,
            'countries'        => $countries,
            'devices'          => $devices,
            'browsers'         => $browsers,
            'platforms'        => $os,
            'referers'         => $referers,
        ];
    }
}
