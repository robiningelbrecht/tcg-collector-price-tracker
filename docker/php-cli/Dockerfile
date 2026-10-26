FROM php:8.5-cli

COPY docker/php-cli/php.ini "${PHP_INI_DIR}/conf.d/docker-php-config.ini"

RUN apt-get update && apt-get install -y \
    procps \
    curl \
    git \
    unzip \
    libzip-dev \
    libicu-dev

RUN docker-php-ext-install zip
RUN docker-php-ext-install intl

RUN curl -L -o "pie.phar" https://github.com/php/pie/releases/download/1.3.1/pie.phar
RUN php pie.phar install xdebug/xdebug
RUN docker-php-ext-enable xdebug
COPY docker/php-cli/xdebug.ini "${PHP_INI_DIR}/conf.d"
RUN rm pie.phar

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
