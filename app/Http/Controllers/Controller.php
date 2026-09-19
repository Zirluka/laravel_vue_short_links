<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: "1.0.0",
        title: "ZipLink API Documentation",
        description: "Высокопроизводительный сервис сокращения ссылок и сбора аналитики"
    ),
    OA\Server(
        url: "/api",
        description: "Основной API сервер"
    ),
    OA\SecurityScheme(
        securityScheme: "sanctum",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT",
        description: "Введите токен в формате: Bearer {token}"
    )
]
abstract class Controller
{
    //
}
