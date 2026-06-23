#!/bin/sh
set -e

php artisan config:clear
php artisan migrate --force
php artisan db:seed --force

exec "$@"
