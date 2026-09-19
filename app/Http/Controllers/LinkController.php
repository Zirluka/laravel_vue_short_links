<?php

namespace App\Http\Controllers;

use App\Http\Requests\Link\LinkActiveRequest;
use App\Http\Requests\Link\LinkExpiredDateRequest;
use App\Http\Requests\Link\LinkPasswordRequest;
use App\Http\Requests\Link\LinkRequest;
use App\Http\Resources\LinkResource;
use App\Http\Services\ShortLinkService;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class  LinkController extends Controller
{
    private const CACHE_PREFIX = 'links:code:';

    // 1. Быстрый переход / получение URL через Redis
    public function getUrl(string $code): JsonResponse
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

        return response()->json([
            'data' => $linkData['original_url']
        ], 200);
    }

    // 2. Получение ссылки, защищенной паролем
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

        return response()->json([
            'data' => $linkData['original_url']
        ], 200);
    }

    // 3. Создание ссылки
    public function shortLink(LinkRequest $request, ShortLinkService $shortLinkService): JsonResponse
    {
        $user = Auth::user();

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

    // 4. Смена пароля + Инвалидация
    public function setPassword(LinkPasswordRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $password = $request->validated('password');
        $link->password = $password ? Hash::make($password) : null;
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    // 5. Срок жизни + Инвалидация
    public function setExpiresTime(LinkExpiredDateRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $link->expired_at = $request->validated('expired_at');
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    // 6. Активность + Инвалидация
    public function setActive(LinkActiveRequest $request, int $id): JsonResponse
    {
        $link = $this->findUserLink($request->user()->id, $id);

        $link->is_active = $request->validated('is_active');
        $link->save();

        $this->invalidateCache($link->short_code);

        return response()->json(['link' => new LinkResource($link)], 200);
    }

    // 7. Удаление + Очистка кэша
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
