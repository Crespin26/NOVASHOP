FROM php:8.5-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libicu-dev libzip-dev libonig-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql intl zip bcmath pcntl mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/tmp/composer
WORKDIR /app
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
