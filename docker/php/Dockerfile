# ─── Stage 1: Build frontend assets ──────────────────────────────────────────
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Устанавливаем зависимости отдельным слоем — кэш не инвалидируется при изменении кода
COPY ./backend/package*.json ./
RUN npm ci

COPY ./backend/ .
RUN npm run build

# ─── Stage 2: PHP application ─────────────────────────────────────────────────
FROM php:8.5-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./backend/ .

# Накладываем скомпилированные фронтенд-ассеты (public/build в .gitignore)
COPY --from=frontend-builder /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

COPY ./docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
