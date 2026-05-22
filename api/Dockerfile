FROM php:8.4-cli-alpine

WORKDIR /app

RUN apk add --no-cache postgresql-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

COPY . .

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan package:discover --ansi \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
