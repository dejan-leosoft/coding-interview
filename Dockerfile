# PHP 8.5 + Composer image for the Symfony 7.4 coding exercises.
FROM php:8.5-cli

# install-php-extensions resolves the system libraries each extension needs.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip sqlite3 \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions intl zip pdo_sqlite pdo_mysql pdo_pgsql

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=dev

WORKDIR /app

# Install dependencies first so this layer is cached until composer files change.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script auto-scripts \
    && mkdir -p var && chmod -R 777 var

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
