#!/usr/bin/env sh

# If arguments are passed, execute them
if [ $# -gt 0 ]; then
    echo "Executing passed command: $@"
    exec "$@"
fi

# Ensure correct ownership of application files
chown -R www-data:www-data /var/www/html

# Run user scripts, if they exist (excluding start.sh)
if [ -d /var/www/html/.fly/scripts ]; then
    for f in /var/www/html/.fly/scripts/*.sh; do
        if [ -f "$f" ] && [ "$f" != "/var/www/html/.fly/scripts/start.sh" ]; then
            echo "Running $f..."
            bash "$f" -e
        fi
    done
fi

# Execute the start.sh script
if [ -f /var/www/html/.fly/scripts/start.sh ]; then
    echo "Running start.sh script..."
    exec /var/www/html/.fly/scripts/start.sh
else
    echo "start.sh script not found. Exiting."
    exit 1
fi
