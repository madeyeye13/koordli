# ── Stage 1: Build frontend assets (Vite) ──────────────────────
FROM node:20-alpine AS node-build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ── Stage 2: PHP application ───────────────────────────────────
FROM php:8.2-fpm AS app

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    libzip-dev libonig-dev \
    ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath gd exif pcntl \
    && pecl install redis && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Verify ffmpeg + ffprobe actually installed together, matching what
# your earlier deployment notes flagged as needing confirmation.
RUN ffmpeg -version && ffprobe -version

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=node-build /app/public/build ./public/build

RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && php artisan storage:link \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
