# Gunakan image PHP dengan Apache
FROM php:8.2-apache

# Salin file proyek ke container
COPY public/ /var/www/html/

# Aktifkan mod rewrite Apache (untuk routing)
RUN a2enmod rewrite

# Port yang digunakan
EXPOSE 80