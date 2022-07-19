FROM php:8.0-fpm-alpine
ARG LOCAL_USER_ID=1000

RUN set -ex \
    && apk add --no-cache --virtual .build-deps \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        oniguruma-dev \
    && docker-php-ext-install -j$(nproc) gd mbstring tokenizer mysqli pdo_mysql

RUN deluser www-data
RUN adduser -D -h /home/www-data -u "${LOCAL_USER_ID}" -s /bin/bash www-data
