FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    oniguruma-dev \
    linux-headers

RUN docker-php-ext-install \
    zip \
    mbstring \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php", "-a"]