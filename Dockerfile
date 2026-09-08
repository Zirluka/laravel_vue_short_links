# 1. Используем официальный образ PHP 8.4 с веб-сервером Apache
FROM php:8.4-apache


# 2. Обновляем пакеты и устанавливаем системные зависимости для PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 3. Устанавливаем расширения PHP для работы с PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# 4. Включаем модуль mod_rewrite для Apache (нужен для работы роутинга Laravel)
RUN a2enmod rewrite

# 5. Меняем корневую папку Apache на public-директорию Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 6. Устанавливаем Composer (если планируете запускать команды внутри контейнера)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Указываем рабочую директорию
WORKDIR /var/www/html

# 8. Назначаем права для корректной работы кэша и логов Laravel
RUN chown -R www-data:www-data /var/www/html
