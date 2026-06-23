FROM dunglas/frankenphp:php8.4-alpine

RUN install-php-extensions pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-progress

COPY . .
RUN composer dump-autoload --optimize

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "php-server", "--listen", ":8000", "--root", "/var/www/public"]
