<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/user",
        summary: "Получение профиля текущего пользователя",
        security: [["sanctum" => []]],
        tags: ["Пользователь"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Профиль получен",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Alex"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "alex@example.com")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Не авторизован")
        ]
    )]
    public function getUser(Request $request): JsonResponse {
        // Возвращаем пользователя через ресурс
        return response()->json([
            "user" => new UserResource($request->user())
        ], 200);
    }

    #[OA\Patch(
        path: "/user",
        summary: "Обновление имени или email профиля",
        security: [["sanctum" => []]],
        tags: ["Пользователь"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Alex New"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "alex_new@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Профиль обновлен"),
            new OA\Response(response: 422, description: "Ошибка валидации")
        ]
    )]
    public function update(UpdateUserRequest $request): JsonResponse {
        // Берем пользователя и валидные поля
        $user = $request->user();
        $validated = $request->validated();

        // Проверяем пароль, если есть, то хешируем и вставляем в валидные поля, если нету, то убираем поле вовсе
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Заполняем пользователя валидными данными и сохраняем
        $user->fill($validated);
        $user->save();

        // Отдаём 200 и пользователя
        return response()->json([
            "user" => new UserResource($user)
        ], 200);
    }

    #[OA\Delete(
        path: "/user",
        summary: "Удаление собственного аккаунта",
        security: [["sanctum" => []]],
        tags: ["Пользователь"],
        responses: [
            new OA\Response(response: 200, description: "Аккаунт удален"),
            new OA\Response(response: 401, description: "Не авторизован")
        ]
    )]
    public function destroy(Request $request): Response {

        // Получаем пользователя, удаляем все токены, разлогиниваемся, удаляем и отдаём 204 No Content
        $user = $request->user();
        $user->tokens()->delete();

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $user->delete();

        return response()->noContent();
    }


}
