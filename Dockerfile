FROM node:24-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM dunglas/frankenphp:1-php8.4-alpine AS base
WORKDIR /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions bcmath gd intl mbstring pdo_pgsql zip \
    && printf 'upload_max_filesize=5M\npost_max_size=8M\n' > "$PHP_INI_DIR/conf.d/threatmetrics.ini"

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM base AS runtime
COPY . .
COPY --from=dependencies /app/vendor ./vendor
COPY --from=dependencies /app/bootstrap/cache ./bootstrap/cache
COPY --from=frontend /app/public/build ./public/build
COPY Caddyfile /etc/caddy/Caddyfile

ENV PORT=80 \
    APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    SESSION_DRIVER=database \
    SESSION_SECURE_COOKIE=true \
    CACHE_STORE=database \
    LOG_CHANNEL=stderr \
    VIEW_COMPILED_PATH=/tmp

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
