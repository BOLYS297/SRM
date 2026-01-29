#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  echo "APP_KEY manquant. Ajoute APP_KEY dans Render > Environment."
  exit 1
fi

php artisan package:discover --ansi
php artisan config:cache
php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=8080
