FROM php:8.2-apache

# Copy semua file ke container
COPY . /var/www/html/

# Aktifkan modul Apache
RUN a2enmod rewrite headers

# Set permissions
RUN chown -R www-data:www-data /var/www/html/public/uploads
RUN chmod -R 755 /var/www/html/public/uploads

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 80