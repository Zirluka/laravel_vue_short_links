<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUser(Request $request): JsonResponse {
        // Возвращаем пользователя через ресурс
        return response()->json([
            "user" => new UserResource($request->user())
        ], 200);
    }

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
