FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set environment variable to allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www

# Copy Symfony project
COPY . /var/www

# Set permissions for Symfony cache/log directories
RUN chown -R www-data:www-data /var/www/var /var/www/public

# Switch to non-root user for security
RUN useradd -m symfony && chown -R symfony:symfony /var/www
USER symfony

# Install Symfony dependencies (AFTER switching to non-root)
RUN composer install --no-interaction --optimize-autoloader || true

USER root
# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip pkg-config \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

USER www-data

# Default command
CMD ["php-fpm"]
