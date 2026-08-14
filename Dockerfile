FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libsqlite3-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring fileinfo zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/storage

VOLUME ["/var/www/html/storage"]

EXPOSE 80
