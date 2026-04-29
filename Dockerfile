# ==========================================
# STAGE 1: Frontend (Compilazione Vite/Tailwind)
# ==========================================
FROM node:20 AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
# Questo crea i file statici in public/build
RUN npm run build

# ==========================================
# STAGE 2: Backend (PHP + Apache + Worker)
# ==========================================
FROM php:8.2-apache

# Installa dipendenze di sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installa estensioni PHP (pcntl è cruciale per Reverb e i Worker)
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Abilita mod_rewrite per le rotte di Laravel
RUN a2enmod rewrite

# Imposta la DocumentRoot sulla cartella public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

# Copia i file compilati di Vite dallo Stage 1
COPY --from=node_builder /app/public/build ./public/build

# Installa Composer e le dipendenze PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Imposta i permessi per Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Comando di default (avvia il server web)
CMD ["apache2-foreground"]
