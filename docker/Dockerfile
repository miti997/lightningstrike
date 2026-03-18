FROM php:8.5-cli-alpine
RUN apk add --no-cache git curl unzip bash
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN php -v && composer -V && git --version
