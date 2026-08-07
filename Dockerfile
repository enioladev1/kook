
ARG PHP_PACKAGES="php84-cli php84-phar php84-ctype php84-curl php84-dom \
    php84-fileinfo php84-iconv php84-mbstring php84-openssl php84-pdo \
    php84-pdo_pgsql php84-pgsql php84-posix php84-session php84-simplexml \
    php84-sodium php84-tokenizer php84-xml php84-xmlreader php84-xmlwriter \
    php84-intl php84-bcmath php84-zip php84-opcache"

# ---- Build: composer deps + frontend assets ----
FROM alpine:3.24 AS build
ARG PHP_PACKAGES

RUN apk add --no-cache $PHP_PACKAGES nodejs npm \
    && ln -sf /usr/bin/php84 /usr/bin/php

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

COPY package.json package-lock.json ./
RUN npm ci

COPY . .


RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --no-interaction \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && npm run build \
    && rm .env \
    && rm -rf node_modules \
    && rm -f storage/logs/*.log

# ---- Runtime ----
FROM alpine:3.24 AS app
ARG PHP_PACKAGES

RUN apk add --no-cache $PHP_PACKAGES php84-fpm php84-pecl-redis php84-pcntl \
    postgresql-client bash nginx supervisor curl libcap-setcap \
    && ln -sf /usr/bin/php84 /usr/bin/php \
    && ln -sf /usr/sbin/php-fpm84 /usr/sbin/php-fpm \
    && sed -i '/^user /d' /etc/nginx/nginx.conf \
    # Grants nginx (only nginx, not the container in general) permission to
    # bind port 80 despite running as a non-root user - the container never
    # needs to run as root just to listen on a privileged port.
    && setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx

COPY docker/opcache.ini /etc/php84/conf.d/zz-opcache.ini
COPY docker/www.conf /etc/php84/php-fpm.d/www.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

RUN addgroup -g 1000 www \
    && adduser -G www -u 1000 -D www \
    && mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/nginx /var/log/php84 \
    && chown -R www:www /var/www/html /run/nginx /var/lib/nginx /var/log/nginx /var/log/php84 \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=15s --retries=3 \
    CMD curl -fsS http://127.0.0.1:80/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
