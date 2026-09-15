FROM php:8.3-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libonig-dev libxml2-dev libsqlite3-dev && docker-php-ext-install pdo_mysql pdo_sqlite mbstring dom xml xmlwriter && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs && composer install --no-interaction --prefer-dist
CMD ["sh","-c","php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
