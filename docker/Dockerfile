FROM php:8.5-cli-alpine

# Install build tools + linux headers + utilities
RUN apk add --no-cache bash autoconf gcc g++ make curl git linux-headers

# Copy composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Install Xdebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Verify installation
RUN php -v && php -m | grep xdebug && composer -V && git --version