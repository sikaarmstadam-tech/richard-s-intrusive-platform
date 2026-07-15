#!/bin/bash
set -e

# Initialize the mounted volume if database doesn't exist
if [ ! -f /var/www/html/backend/database.sqlite ]; then
    echo "Initializing database from backup..."
    mkdir -p /var/www/html/backend
    if [ -f /var/www/html/backend_backup/database.sqlite ]; then
        cp /var/www/html/backend_backup/database.sqlite /var/www/html/backend/database.sqlite
    fi
fi

# Ensure correct permissions on the SQLite database and directory
chown -R www-data:www-data /var/www/html/backend
chmod -R 777 /var/www/html/backend

# Execute the CMD (default: apache2-foreground)
exec "$@"
