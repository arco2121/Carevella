# STAGE 1: Frontend
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# STAGE 2: PHP & Apache su Alpine (molto più compatibile con Render)
FROM php:8.4-apache-bullseye

# Installazione dipendenze di sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip git curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Installazione Node e Concurrently
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
RUN npm install -g concurrently

# ABILITA REWRITE
RUN a2enmod rewrite

# CONFIGURAZIONE PORTA 10000 E LOG SU STDOUT (METODO AGGRESSIVO)
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:10000/' /etc/apache2/sites-available/000-default.conf

# Sovrascriviamo la configurazione dei log per forzarli a sparire se danno errore
RUN echo "ErrorLog /dev/null" >> /etc/apache2/apache2.conf && \
    echo "CustomLog /dev/null combined" >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .
COPY --from=node_builder /app/public/build ./public/build

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Permessi
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage

EXPOSE 10000

# Avvio: Migrazioni -> Apache + Reverb + MQTT
CMD php artisan migrate --force && concurrently "apache2-foreground" "php artisan reverb:start" "php artisan mqtt:listen"
