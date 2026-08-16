# Installation

## Requirements

- PHP 8.2 or higher
- PHP extensions: `pdo_sqlite`, `mbstring`, `fileinfo`, `zip`
- Composer
- Apache 2.4 with `mod_rewrite`

### Check PHP extensions

```bash
php -m | grep -E "pdo_sqlite|mbstring|fileinfo|zip"
```

### Enable mod_rewrite

```bash
a2enmod rewrite
systemctl reload apache2
```

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/sopima/sopima.git
cd sopima
```

### 2. Install dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Create configuration

```bash
cp .env.example .env
```

Edit `.env`:

```env
APP_NAME=Sopima
APP_URL=https://your-domain.example.com
APP_SECRET=
DB_SQLITE_PATH=/path/to/sopima/storage/database/sopima.sqlite
```

`APP_SECRET` is generated automatically by the setup wizard.

### 4. Prepare storage directory

```bash
mkdir -p storage/database storage/uploads
chown -R www-data:www-data storage/
chmod 755 storage/database storage/uploads
```

### 5. Configure Apache VHost

```apache
<VirtualHost *:80>
    ServerName your-domain.example.com
    DocumentRoot /path/to/sopima/public
    <Directory /path/to/sopima/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/sopima_error.log
    CustomLog ${APACHE_LOG_DIR}/sopima_access.log combined
</VirtualHost>
```

### 6. Reload Apache

```bash
systemctl reload apache2
```

### 7. Run the setup wizard

Open `https://your-domain.example.com/setup` in your browser.

The wizard guides you through:
- System check (PHP, extensions, write permissions)
- Generating `APP_SECRET`
- Database migration
- Creating the first admin account

---

## Docker

### 1. Create configuration file

```bash
cp .env.example .env
nano .env
```

Set at minimum:

```env
APP_NAME=Sopima
APP_URL=http://your-domain.example.com:8080
APP_SECRET=
DB_SQLITE_PATH=/var/www/html/storage/database/sopima.sqlite
```

`APP_SECRET` can be left blank – the setup wizard generates it automatically.

### 2. Start the container

```bash
docker compose up -d
```

### 3. Run setup

Open `http://your-domain.example.com:8080/setup` in your browser.

The wizard detects the existing configuration and guides you through migration and admin creation.

---

## Updates

```bash
git pull
composer install --no-dev --optimize-autoloader
php database/migrate.php
```

New migrations are detected and applied automatically.

---

## Troubleshooting

**500 Internal Server Error**
- Check Apache log: `tail -50 /var/log/apache2/sopima_error.log`
- PHP syntax error: `php -l public/index.php`
- Is `mod_rewrite` active? `apache2ctl -M | grep rewrite`

**Database error**
- Check write permissions: `ls -la storage/database/`
- Run migration again: `php database/migrate.php`

**Login fails**
- Run setup again: `/setup` (only if no admin exists yet)
- SQLite file present? `ls -la storage/database/`