# STAGE 1: Solo per il frontend (Vite)
FROM node:20 AS node_builder
WORKDIR /app
COPY package*.json ./
# Qui npm install installerà solo quello che serve a Node
RUN npm install
COPY . .
# Adesso "npm run build" eseguirà solo "vite build" e non cercherà Composer
RUN npm run build

# STAGE 2: PHP, Apache e Composer
FROM php:8.2-apache

# Installazione dipendenze di sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip git curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installazione estensioni PHP
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Installazione di Node.js anche qui (necessario per il trucco 'concurrently' che dicevamo)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
RUN npm install -g concurrently

# Abilita mod_rewrite
RUN a2enmod rewrite

# Configurazione DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

# Prendiamo i file compilati dallo Stage 1
COPY --from=node_builder /app/public/build ./public/build

# Installazione Composer (qui funziona perché siamo nell'immagine PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Permessi
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Avvio dei processi (Web + Reverb + MQTT)
CMD ["concurrently", "apache2-foreground", "php artisan reverb:start", "php artisan mqtt:listen", "php artisan mqtt:subscribe"]
