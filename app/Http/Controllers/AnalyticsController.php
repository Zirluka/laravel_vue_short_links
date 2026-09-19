<?php

namespace App\Http\Controllers;

use App\Http\Services\AnalyticsService;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{

    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function show(Request $request, string $code): JsonResponse
    {
        $link = Link::where('short_code', $code)->firstOrFail();

        // Проверяем, что запрашивающий пользователь — владелец ссылки
        if ($link->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden for you.'
            ], 403);
        }


        $days = (int) $request->query('days', 30);
        $data = $this->analyticsService->getLinkStats($link, $days);

        return response()->json([
            'data' => $data
        ]);
    }
}
