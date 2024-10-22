#!/bin/sh

if [ "$APP_PROCESS" = "queue" ]; then
    echo "Starting queue worker..."
    # Run migrations (optional)
    php artisan queue:work --sleep=3 --tries=3 --timeout=3600
elif [ "$APP_PROCESS" = "scheduler" ]; then
    echo "Starting scheduler..."
    # Run migrations (optional)
    php artisan schedule:work
else
    echo "Starting web server..."
    # Start PHP-FPM and Nginx
    php-fpm
fi
