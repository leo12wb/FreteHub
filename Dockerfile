# ── Base stage ────────────────────────────────────────────────────────────────
FROM php:8.2-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PCOV for code coverage (lighter than Xdebug)
RUN pecl install pcov && docker-php-ext-enable pcov

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ── Development stage ─────────────────────────────────────────────────────────
FROM base AS dev

# Copy project (volumes will override in docker-compose)
COPY . .

# Install ALL dependencies (including dev)
RUN composer install --no-interaction --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ── Production stage ──────────────────────────────────────────────────────────
FROM base AS prod

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
