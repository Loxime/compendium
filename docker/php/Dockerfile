FROM php:8.4-fpm-alpine

RUN apk add --no-cache postgresql-dev icu-dev libzip-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql intl \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts
COPY . .
RUN composer dump-autoload --classmap-authoritative --no-interaction
COPY docker/php/entrypoint.sh /usr/local/bin/compendium-entrypoint
RUN chmod +x /usr/local/bin/compendium-entrypoint
ENTRYPOINT ["compendium-entrypoint"]
CMD ["php-fpm"]
