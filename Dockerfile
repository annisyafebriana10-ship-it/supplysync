FROM php:8.2-apache

# Install ekstensi yang dibutuhkan Laravel & Supabase (PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Aktifkan mod_rewrite agar routing Laravel berfungsi
RUN a2enmod rewrite

# Arahkan folder utama server ke folder /public milik Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Salin semua file proyekmu ke dalam server Render
WORKDIR /var/www/html
COPY . .

# Install dependencies Laravel menggunakan Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Beri izin agar Laravel bisa menulis file (menyelesaikan masalah Read-Only Vercel)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80