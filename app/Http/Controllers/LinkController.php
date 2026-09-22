<?php

namespace App\Http\Controllers;

use App\Http\Requests\Link\LinkActiveRequest;
use App\Http\Requests\Link\LinkExpiredDateRequest;
use App\Http\Requests\Link\LinkPasswordRequest;
use App\Http\Requests\Link\LinkRequest;
use App\Http\Resources\LinkResource;
use App\Http\Services\ShortLinkService;
use App\Jobs\TrackClickJob;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use OpenApi\Attributes as OA;

class LinkController extends Controller
{
    private const CACHE_PREFIX = 'links:code:';

    #[OA\Get(
        path: "/{code}",
        summary: "Получение целевого URL по коду (с асинхронным трекингом клика)",
        tags: ["Ссылки"],
        parameters: [
            new OA\Parameter(name: "code", in: "path", required: true, schema: new OA\Schema(type: "string", example: "1C"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный возврат URL",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "string", example: "https://example.com/long-page")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Ссылка защищена паролем (требуется POST /{code}/guard)"),
            new OA\Response(response: 404, description: "Ссылка не найдена или истекла")
        ]
    )]
    public function getUrl(Request $request, string $code): JsonResponse
    {
        $linkData = $this->resolveLinkData($code);

        if (!$linkData) {
            return response()->json(['message' => 'Link not found or expired.'], 404);
        }

        if (!empty($linkData['password'])) {
            return response()->json([
                'message' => 'Link has password',
                'errors' => ['code' => ['Forbidden for you']]
            ], 403);
        }

        TrackClickJob::dispatch(
            $linkData['id'],
            $request->ip(),
            $request->userAgent(),
            $request->headers->get('referer')
        );

        return response()->json([
            'data' => $linkData['original_url']
        ], 200);
    }

    #[OA\Post(
        path: "/{code}/guard",
        summary: "Получение URL для ссылки, защищенной паролем",
        tags: ["Ссылки"],
        parameters: [
            new OA\Parameter(name: "code", in: "path", required: true, schema: new OA\Schema(type: "string", example: "1C"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["password"],
                properties: [
                    new OA\Property(property: "password", type: "string", example: "secret123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Пароль верный, возврат URL",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "string", example: "https://example.com/long-page")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Неверный пароль"),
            new OA\Response(response: 404, description: "Ссылка не найдена или истекла")
        ]
    )]
    public function getGuardedUrl(LinkPasswordRequest $request, string $code): JsonResponse
    {
        $linkData = $this->resolveLinkData($code);

        if (!$linkData) {
            return response()->json(['message' => 'Link not found or expired.'], 404);
        }

        if (!empty($linkData['password']) && !Hash::check($request->validated('password'), $linkData['password'])) {
            return response()->json([
                'message' => 'Invalid password',
                'errors' => ['password' => ['Invalid password']]
            ], 422);
        }

        TrackClickJob::dispatch(
            $linkData['id'],
            $request->ip(),
            $request->userAgent(),
            $request->headers->get('referer')
        );

        return response()->json([
            'data' => $linkData['original_url']
        ], 200);
    }

    #[OA\Post(
        path: "/",
        summary: "Создание короткой ссылки",
        tags: ["Ссылки"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["original_url"],
                properties: [
                    new OA\Property(property: "original_url", type: "string", format: "uri", example: "https://example.com/long-page"),
                    new OA\Property(property: "password", type: "string", nullable: true, example: "secret123"),
                    new OA\Property(property: "expired_at", type: "string", format: "date-time", nullable: true, example: "2026-12-31T23:59:59Z")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Ссылка создана",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "short_code", type: "string", example: "1C"),
                        new OA\Property(property: "short_url", type: "string", example: "http://localhost:8000/1C")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Ошибка валидации")
        ]
    )]
    public function shortLink(LinkRequest $request, ShortLinkService $shortLinkService): JsonResponse
    {
        $user = $request->user('sanctum') ?? Auth::guard('sanctum')->user();

        $link = $shortLinkService->shortLink(
            $request->validated('link'),
            $user ? ($request->validated('password') ?? null) : null,
            $user?->id,
            $request->validated('expired_at')
        );

        // Сразу прогреваем кэш в Redis
        $this->cacheLink($link);

        return response()->json([
            'status' => 'success',
            'code' => $link->short_code
        ], 201);
    }

    #[OA\Patch(
        path: "/link/{id}/password",
        summary: "Установка или смена пароля ссылки",
        security: [["sanctum" => []]],
        tags: ["Управление ссылками"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "password", type: "string", nullable: true, example: "newpass")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Пароль обновлен"),
            new OA\Response(response: 403, description: "Нет прав доступа"),
            new OA\Response(response: 404, description: "Ссылка не найдена")
        ]
    )]
    public function setPassword(LinkPasswordRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $password = $request->validated('password');
        $link->password = $password ? Hash::make($password) : null;
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    #[OA\Patch(
        path: "/link/{id}/expired",
        summary: "Установка или продление срока жизни ссылки",
        security: [["sanctum" => []]],
        tags: ["Управление ссылками"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "expired_at", type: "string", format: "date-time", nullable: true, example: "2026-11-20T12:00:00Z")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Срок жизни обновлен"),
            new OA\Response(response: 403, description: "Нет прав"),
            new OA\Response(response: 404, description: "Ссылка не найдена")
        ]
    )]
    public function setExpiresTime(LinkExpiredDateRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $link->expired_at = $request->validated('expired_at');
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    #[OA\Patch(
        path: "/link/{id}/active",
        summary: "Включение или отключение активности ссылки",
        security: [["sanctum" => []]],
        tags: ["Управление ссылками"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["is_active"],
                properties: [
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Статус изменен"),
            new OA\Response(response: 403, description: "Нет прав"),
            new OA\Response(response: 404, description: "Ссылка не найдена")
        ]
    )]
    public function setActive(LinkActiveRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $link->is_active = $request->validated('is_active');
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    #[OA\Delete(
        path: "/link/{id}",
        summary: "Удаление ссылки",
        security: [["sanctum" => []]],
        tags: ["Управление ссылками"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Ссылка удалена"),
            new OA\Response(response: 403, description: "Нет прав"),
            new OA\Response(response: 404, description: "Ссылка не найдена")
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $shortCode = $link->short_code;
        $link->delete();

        $this->invalidateCache($shortCode);

        return response()->json(null, 204);
    }

    // --- Приватные методы для работы с Redis и базой данных ---

    /**
     * Cache-Aside паттерн: Redis -> fallback в Postgres/MySQL
     */
    private function resolveLinkData(string $code): ?array
    {
        $cacheKey = self::CACHE_PREFIX . $code;

        // 1. Читаем из Redis
        $cached = Redis::get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }

        // 2. Fallback в базу с проверкой активности и срока годности
        $link = Link::where('short_code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                      ->orWhere('expired_at', '>', now());
            })
            ->first();

        if (!$link) {
            return null;
        }

        // 3. Сохраняем в кэш
        $this->cacheLink($link);

        return [
            'id' => $link->id,
            'original_url' => $link->original_url,
            'password' => $link->password,
        ];
    }

    private function cacheLink(Link $link): void
    {
        if (!$link->is_active) {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . $link->short_code;
        $payload = json_encode([
            'id' => $link->id,
            'original_url' => $link->original_url,
            'password' => $link->password,
        ]);

        // Если есть срок годности, выставляем TTL ключа в Redis
        if ($link->expired_at) {
            $ttl = (int) now()->diffInSeconds($link->expired_at, false);
            if ($ttl > 0) {
                Redis::setex($cacheKey, $ttl, $payload);
            }
        } else {
            // Без срока — кэшируем на 24 часа (86400 сек)
            Redis::setex($cacheKey, 86400, $payload);
        }
    }

    private function invalidateCache(string $code): void
    {
        Redis::del(self::CACHE_PREFIX . $code);
    }

    private function findUserLink(int $userId, int $id): Link
    {
        return Link::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
