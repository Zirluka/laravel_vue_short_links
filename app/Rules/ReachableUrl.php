<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ReachableUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // Сначала пробуем быстрый HEAD-запрос без скачивания тела ответаста
            // Вывляем короткий таймаут (5 сек), чтобы не подвешивать создание ссылки
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->withHeaders([
                    'User-Agent' => 'ZipLink-Validator/1.0',
                ])
                ->head($value);

            // Если удаленный сервер не поддерживает метод HEAD (405 Method Not Allowed), пробуем обычный GET
            if ($response->status() === 405) {
                $response = Http::timeout(5)
                    ->connectTimeout(3)
                    ->withHeaders([
                        'User-Agent' => 'ZipLink-Validator/1.0',
                    ])
                    ->get($value);
            }

            // Требование ТЗ: код ответа должен быть строго 200
            if ($response->status() !== 200) {
                $fail("Целевой URL недоступен (сервер вернул HTTP-код: {$response->status()}). Ожидается 200 OK.");
            }
        } catch (\Throwable $e) {
            $fail('Не удалось подключиться к целевому URL. Проверьте правильность адреса и доступность сайта.');
        }
    }
}
