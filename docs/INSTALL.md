# Installation

## Voraussetzungen

- PHP 8.2 oder höher
- PHP-Extensions: `pdo`, `pdo_sqlite`, `mbstring`, `fileinfo`, `zip`
- Composer
- Apache 2.4 mit `mod_rewrite`
- SQLite 3

### PHP-Extensions prüfen

```bash
php -m | grep -E "pdo|mbstring|fileinfo|zip"
```

### mod_rewrite aktivieren

```bash
a2enmod rewrite
systemctl reload apache2
```

---

## Installation

### 1. Repository klonen

```bash
git clone https://github.com/sopima/sopima.git
cd sopima
```

### 2. Abhängigkeiten installieren

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Konfiguration anlegen

```bash
cp .env.example .env
```

`.env` anpassen:

```env
APP_URL=https://ihre-domain.example.com
APP_SECRET=                        # openssl rand -hex 32

DB_DRIVER=sqlite
DB_SQLITE_PATH=/pfad/zu/sopima/storage/database/sopima.sqlite
```

### 4. Storage-Verzeichnis vorbereiten

```bash
mkdir -p storage/database storage/uploads
chown -R www-data:www-data storage/
chmod 755 storage/database storage/uploads
```

### 5. Datenbank migrieren

```bash
php database/migrate.php
```

### 6. Ersten Admin anlegen

```bash
php bin/create-admin.php
```

### 7. Apache VHost einrichten

```apache
<VirtualHost *:80>
    ServerName ihre-domain.example.com
    DocumentRoot /pfad/zu/sopima/public

    <Directory /pfad/zu/sopima/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sopima_error.log
    CustomLog ${APACHE_LOG_DIR}/sopima_access.log combined
</VirtualHost>
```

### 8. Apache neu laden

```bash
systemctl reload apache2
```

---

## Troubleshooting

**500 Internal Server Error**
- Apache-Log prüfen: `tail -50 /var/log/apache2/sopima_error.log`
- PHP-Syntaxfehler: `php -l public/index.php`
- `mod_rewrite` aktiv? `apache2ctl -M | grep rewrite`

**Datenbankfehler**
- Schreibrechte prüfen: `ls -la storage/database/`
- Migration nochmal ausführen: `php database/migrate.php`

**Login schlägt fehl**
- Admin neu anlegen: `php bin/create-admin.php`
- SQLite-Datei vorhanden? `ls -la storage/database/`

---

## Updates

```bash
git pull
composer install --no-dev --optimize-autoloader
php database/migrate.php
```

Neue Migrationen werden automatisch erkannt und eingespielt.
