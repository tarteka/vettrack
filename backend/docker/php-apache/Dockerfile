ARG PHP_VERSION=8.2-apache
FROM php:${PHP_VERSION}

RUN apt-get update && apt-get install -y \
    git vim unzip libicu-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql intl zip \
    && docker-php-ext-enable opcache

RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

# Crear grupo y usuario guno con UID/GID 1000
RUN groupadd -g 1000 guno && \
    useradd -u 1000 -g 1000 -d /var/www/symfony -s /sbin/nologin -c "Docker guno user" guno && \
    usermod -a -G www-data guno

# Asignar permisos correctos al directorio de trabajo
RUN chown -R guno:www-data /var/www/symfony && \
    chmod -R 775 /var/www/symfony

# Cambiar al usuario guno (no root)
USER guno