#!/bin/sh
set -e

echo "[Sopima] Starte Initialisierung..."

# storage-Verzeichnisse sicherstellen
mkdir -p /var/www/html/storage/database /var/www/html/storage/uploads
chown -R www-data:www-data /var/www/html/storage

# .env vorhanden?
if [ ! -f /var/www/html/.env ]; then
    echo "[Sopima] Keine .env gefunden – bitte /setup aufrufen."
else
    echo "[Sopima] Führe Migrationen aus..."
    php /var/www/html/database/migrate.php
fi

echo "[Sopima] Starte Apache..."
exec apache2-foreground
