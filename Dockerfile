FROM php:8.4-fpm
LABEL authors="Zalewska&Skudlik"
ARG DOCKER_ENV

COPY /composer.lock /composer.json /var/www/

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    postgresql

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip exif pcntl bcmath
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd
# RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

COPY ./ /var/www

RUN mkdir -p /var/www/storage/logs /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions /var/www/storage/framework/views \
    /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache \
    && chown -R www:www /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]
