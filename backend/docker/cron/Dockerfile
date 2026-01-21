FROM php:8.3-cli

# Instalamos cron y extensiones necesarias
RUN apt-get update && apt-get install -y \
    cron \
    vim \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

# Copiamos el crontab
COPY docker/cron/crontab /etc/cron.d/symfony-cron

# Permisos obligatorios para cron
RUN chmod 0644 /etc/cron.d/symfony-cron && \
    crontab /etc/cron.d/symfony-cron

# Archivo de logs
RUN touch /var/log/cron.log

# Ejecutar cron en primer plano
CMD ["cron", "-f"]
