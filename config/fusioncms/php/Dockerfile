FROM php:8.3-fpm-bookworm

RUN apt-get update -y \
    && apt-get install -y \
        curl \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libgmp-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl dom gd gmp intl mbstring mysqli soap zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/fusion-entrypoint.sh
COPY install.php /usr/local/bin/fusion-install.php

RUN chmod +x /usr/local/bin/fusion-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/fusion-entrypoint.sh"]
CMD ["php-fpm"]
