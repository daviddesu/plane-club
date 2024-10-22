#!/bin/sh

if [ -n "$1" ]; then
    APP_PROCESS="$1"
fi

if [ "$APP_PROCESS" = "queue" ]; then
    echo "Starting queue worker..."
    php artisan queue:work --sleep=3 --tries=3 --timeout=3600
elif [ "$APP_PROCESS" = "scheduler" ]; then
    echo "Starting scheduler..."
    php artisan schedule:work
else
    echo "Starting web server..."
    # Start PHP-FPM and Nginx
    php-fpm
fi
