FROM dunglas/frankenphp:1-php8.5-alpine

WORKDIR /var/www

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1

# hadolint ignore=DL3018
RUN apk add --no-cache \
  bash \
  curl \
  tzdata

RUN set -eux; \
	install-php-extensions \
        @composer \
        ctype \
        curl \
        dom \
        iconv \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_sqlite \
        tokenizer \
        xml \
        zip;

COPY docker/web/config/php.ini ${PHP_INI_DIR}/php.ini
COPY docker/web/config/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/web/config/crontab /etc/crontabs/root
COPY docker/web/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . /var/www/
RUN rm -Rf docker \
    && mkdir -p var/cache var/log storage/database \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && composer dump-env prod \
    && bin/console cache:warmup

ENTRYPOINT ["docker-entrypoint.sh"]
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]
