<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSelfDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parsedUrl = parse_url($value);
        $host = strtolower($parsedUrl['host'] ?? '');

        if (empty($host)) {
            $fail('Некорректный формат URL.');
            return;
        }

        // 1. Получаем хост собственного приложения
        $appHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost());

        // Запрещаем сокращать ссылки на собственный домен
        if ($host === $appHost || str_ends_with($host, '.' . $appHost)) {
            $fail('Нельзя сокращать ссылку, ведущую на этот же сервис.');
            return;
        }

        // 2. Дополнительная защита от SSRF (запрет обращения к внутренним адресам)
        $blacklistedHosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        if (in_array($host, $blacklistedHosts, true) || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            $fail('Указанный адрес недоступен для сокращения.');
            return;
        }

        // Проверка на приватные диапазоны IP
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $fail('Указанный адрес недоступен для сокращения.');
        }
    }
}
