# ⚡ ZipLink — Highload URL Shortener & Analytics Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue Version](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Database](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Cache & Queue](https://img.shields.io/badge/Redis-Alpine-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Tests](https://img.shields.io/badge/Tests-Pest%20PHP-blueviolet?style=flat-square&logo=pest)](https://pestphp.com)
[![Swagger](https://img.shields.io/badge/API_Docs-Swagger-85EA2D?style=flat-square&logo=swagger&logoColor=black)](/api/documentation)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

Высокопроизводительный сервис сокращения ссылок корпоративного уровня, спроектированный с акцентом на работу под высокими нагрузками (Highload), кэширование, асинхронный сбор аналитики через очереди и безопасность.

---

## 📟 Содержание

- [Архитектура и Highload-решения](#-архитектура-и-highload-решения)
- [Функциональные возможности](#-функциональные-возможности)
- [Технологический стек](#-технологический-стек)
- [Схема базы данных](#-схема-базы-данных)
- [Быстрый запуск (Docker / Sail)](#-быстрый-запуск-docker--sail)
- [Интерактивная документация API (Swagger)](#-интерактивная-документация-api-swagger)
- [Автотестирование](#-автотестирование)
- [Лицензия](#-лицензия)

---

## 🏗 Архитектура и Highload-решения

Приложение спроектировано так, чтобы выдерживать интенсивный поток переходов по коротким URL без деградации реляционной базы данных:

```
[ Client Request ]
        │
        ▼
[ Nginx / Web Server ]
        │
        ├───▶ [ Redis Cache-Aside ] (Проверка короткого кода O(1))
        │             │
        │      (Hit)  ├──▶ [ Мгновенный 301/302 Redirect клиенту ]
        │             │
        │     (Miss)  └──▶ [ PostgreSQL ] ──▶ Сохранение в Redis с TTL
        │
        └───▶ [ Laravel Queue (Redis) ] (Асинхронная задача)
                      │
                      ▼
               [ Job: LogClickMetrics ]
                      │
                      ├───▶ MaxMind GeoIP (Определение страны и города)
                      ├───▶ User-Agent Parser (ОС, браузер, тип устройства)
                      └───▶ Пакетная запись в таблицу `clicks`
```

1. **Алгоритм Base62**: Автоинкрементные идентификаторы БД преобразуются в детерминированные короткие коды по основанию 62 (`[0-9a-zA-Z]`), что исключает коллизии и избавляет систему от тяжелых циклов поиска уникальных случайных строк.
2. **Паттерн Cache-Aside (Redis)**: При переходе по ссылке база данных не опрашивается — данные читаются из RAM Redis со сложностью O(1). Кэш автоматически инвалидируется при изменении параметров или удалении ссылки владельцем.
3. **Атомарные TTL**: Ссылки с ограниченным сроком действия (`expired_at`) сохраняются в Redis со сроком жизни ключа, равным точному остатку секунд до дедлайна.
4. **Асинхронный сбор метрик**: Редирект пользователя выполняется моментально, а сбор аналитических данных (IP, реферер, геопозиция, User-Agent) отправляется в очередь Redis и обрабатывается фоновым воркером.

---

## 🚀 Функциональные возможности

### Публичная часть и Гости
* **Анонимное сокращение**: Быстрое создание ссылок прямо на главной странице без регистрации.
* **Автоэкспирация**: Гостевые ссылки автоматически получают срок жизни в 7 дней и подлежат автоматической очистке.

### Личный кабинет (Vue 3 Dashboard)
* **Полноценный CRUD ссылок**: Настройка целевых URL, ручная деактивация, удаление.
* **Срок действия (Expiration Date)**: Произвольная установка даты и времени прекращения доступности ссылки.
* **Защита паролем**: Доступ к целевому URL только после ввода валидного пароля.
* **Генерация QR-кодов**: Автоматическое создание QR-кода для шеринга ссылки.
* **Детальная визуализация**: Графики кликов по дням, диаграммы распределения по странам, реферерам и типам устройств.

### API для интеграций
* **Personal Access Tokens (Sanctum)**: Выпуск, просмотр и отзыв токенов в личном кабинете.
* **Rate Limiting**: Ограничение частоты запросов для защиты от спама и DDoS.

---

## 🛠 Технологический стек

* **Backend**: PHP 8.2+, Laravel 11+, Laravel Sanctum, Laravel Queues.
* **Frontend**: Vue 3 (Composition API, `<script setup>`), Pinia, Vue Router, Tailwind CSS, Chart.js / ApexCharts.
* **База данных**: PostgreSQL 16 (с составными B-Tree индексами).
* **Кэш и очереди**: Redis (Alpine).
* **Геолокация**: MaxMind GeoIP.
* **Testing**: Pest PHP (Feature & Unit).
* **Окружение**: Docker Compose / Laravel Sail.
* **Документация**: Swagger / OpenAPI 3.0 (L5-Swagger).

---

## 🗄 Схема базы данных

```
┌─────────────────────────┐       ┌───────────────────────────────────┐
│          users          │       │                links              │
├─────────────────────────┤       ├───────────────────────────────────┤
│ id (PK)                 │       │ id (PK)                           │
│ name                    │◀─────┐│ user_id (FK, nullable)            │
│ email (UNIQUE)          │      ││ original_url                      │
│ password                │      ││ short_code (UNIQUE, INDEX)        │
│ remember_token          │      ││ password (nullable)               │
│ created_at / updated_at │      ││ expired_at (INDEX, nullable)      │
└─────────────────────────┘      ││ is_active (INDEX, boolean)        │
                                 ││ clicks_count                      │
                                 ││ created_at / updated_at           │
                                 │└───────────────────────────────────┘
                                 │                   ▲
                                 │                   │ 1:N
                                 │        ┌──────────────────────────┐
                                 │        │          clicks          │
                                 │        ├──────────────────────────┤
                                 │        │ id (PK)                  │
                                 └────────┼ link_id (FK)             │
                                          │ ip                       │
                                          │ country                  │
                                          │ city                     │
                                          │ referer                  │
                                          │ user_agent               │
                                          │ device_type              │
                                          │ os                       │
                                          │ browser                  │
                                          │ clicked_at               │
                                          └──────────────────────────┘
```

---

## 📦 Быстрый запуск (Docker / Sail)

### 1. Клонирование репозитория и настройка `.env`

```bash
git clone https://github.com/zirluka/ziplink.git
cd ziplink

cp .env.example .env
```

Задайте переменные для подключения к PostgreSQL и Redis в `.env` файле:
```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=ziplink
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Запуск контейнеров

```bash
docker compose up -d
```

### 3. Зависимости и миграции бэкенда

```bash
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
```

### 4. Запуск фронтенда (папка `vue/`)

Фронтенд-приложение расположено в папке `vue`:

```bash
cd vue
npm install
npm run dev
```

* Фронтенд: `http://localhost:5173`
* Backend API: `http://localhost` (или `http://localhost:8000`)

---

## 📑 Интерактивная документация API (Swagger)

В проект интегрирован Swagger UI для валидации запросов и тестирования API.

1. Сгенерируйте спецификацию:
```bash
docker compose exec laravel.test php artisan l5-swagger:generate
```
2. Перейдите по адресу:
```
http://localhost/api/documentation
```

---

## 🧪 Автотестирование

Тестовое покрытие ключевых бизнес-сценариев выполнено на **Pest PHP**:

```bash
# Запуск полного набора тестов
docker compose exec laravel.test php artisan test

# Запуск тестов контроллера ссылок (кэш, пароли, экспирация)
docker compose exec laravel.test php artisan test tests/Feature/LinkControllerTest.php

# Запуск юнит-тестов алгоритма Base62
docker compose exec laravel.test php artisan test tests/Unit/Base62ServiceTest.php
```

---

## 📄 Лицензия

Проект распространяется под свободной лицензией MIT. Подробности в файле LICENSE.
