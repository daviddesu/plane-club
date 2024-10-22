#!/usr/bin/env sh

# Run user scripts, if they exist
for f in /var/www/html/.fly/scripts/*.sh; do
    # Bail out this loop if any script exits with non-zero status code
    bash "$f" -e
done

# Ensure correct ownership of application files
chown -R www-data:www-data /var/www/html

# Execute the start.sh script
if [ -f /var/www/html/.fly/scripts/start.sh ]; then
    echo "Running start.sh script..."
    exec /var/www/html/.fly/scripts/start.sh
else
    echo "start.sh script not found. Exiting."
    exit 1
fi
