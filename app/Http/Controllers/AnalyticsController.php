<?php

namespace App\Http\Controllers;

use App\Http\Services\AnalyticsService;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AnalyticsController extends Controller
{

    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    #[OA\Get(
        path: "/link/{code}/analytics",
        summary: "Сводная аналитика по кликам ссылки",
        security: [["sanctum" => []]],
        tags: ["Аналитика"],
        parameters: [
            new OA\Parameter(name: "code", in: "path", required: true, schema: new OA\Schema(type: "string", example: "1C")),
            new OA\Parameter(name: "days", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 30))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Агрегированные данные для графиков",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_clicks", type: "integer", example: 120),
                                new OA\Property(property: "clicks_over_time", type: "object"),
                                new OA\Property(property: "countries", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "devices", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "browsers", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "platforms", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "referers", type: "array", items: new OA\Items(type: "object"))
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Нет доступа к чужой аналитике"),
            new OA\Response(response: 404, description: "Ссылка не найдена")
        ]
    )]
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
