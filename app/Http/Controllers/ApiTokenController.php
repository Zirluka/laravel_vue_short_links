<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ApiTokenController extends Controller
{
    #[OA\Get(
        path: "/tokens",
        summary: "Получение списка активных API-токенов",
        security: [["sanctum" => []]],
        tags: ["API Tokens"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список токенов",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Bot Token"),
                                    new OA\Property(property: "last_used_at", type: "string", nullable: true, example: null),
                                    new OA\Property(property: "created_at", type: "string", example: "2026-09-19T22:00:00Z")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse {
        $tokens = $request->user()->tokens()->get([
            'id',
            'name',
            'abilities',
            'last_used_at',
            'created_at'
        ]);

        return response()->json([
            'data' => $tokens
        ]);
    }

    #[OA\Post(
        path: "/tokens",
        summary: "Выпуск нового персонального API-токена",
        security: [["sanctum" => []]],
        tags: ["API Tokens"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "CI/CD Token")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Токен создан (показывается только 1 раз)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Token created successfully."),
                        new OA\Property(property: "token", type: "string", example: "1|abcdef123456..."),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Ошибка валидации")
        ]
    )]
    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255']
        ]);

        $token = $request->user()->createToken($validated['name']);

        return response()->json([
            'message' => 'Token created successfully. Please copy it now, it will not be shown again.',
            'token' => $token,
            'data' => [
                'id' => $token->accessToken->id,
                'name' => $token->accessToken->name,
                'created_at' => $token->accessToken->created_at,
            ]
        ], 201);
    }


    #[OA\Delete(
        path: "/tokens/{tokenId}",
        summary: "Отозвать конкретный токен по ID",
        security: [["sanctum" => []]],
        tags: ["API Tokens"],
        parameters: [
            new OA\Parameter(name: "tokenId", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Токен отозван"),
            new OA\Response(response: 404, description: "Токен не найден")
        ]
    )]
    public function destroy(Request $request, int $tokenId): JsonResponse {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Token not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }

    #[OA\Delete(
        path: "/tokens",
        summary: "Отозвать все токены текущего пользователя",
        security: [["sanctum" => []]],
        tags: ["API Tokens"],
        responses: [
            new OA\Response(response: 200, description: "Все токены успешно отозваны")
        ]
    )]
    public function destroyAll(Request $request): JsonResponse {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully.',
        ]);
    }

}
