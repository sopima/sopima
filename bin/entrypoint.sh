#!/bin/sh
set -e

echo "[Sopima] Starte Initialisierung..."

mkdir -p /var/www/html/storage/database /var/www/html/storage/uploads
chown -R www-data:www-data /var/www/html/storage

# .env aus Volume laden falls vorhanden
if [ -f /var/www/html/storage/.env ]; then
    cp /var/www/html/storage/.env /var/www/html/.env
    echo "[Sopima] .env aus Storage geladen."
elif [ ! -f /var/www/html/.env ]; then
    echo "[Sopima] Keine .env gefunden – bitte /setup aufrufen."
fi

if [ -f /var/www/html/.env ]; then
    echo "[Sopima] Führe Migrationen aus..."
    php /var/www/html/database/migrate.php
fi

echo "[Sopima] Starte Apache..."
exec apache2-foreground
