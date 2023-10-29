FROM php:8.2-apache

RUN useradd -m lion && echo 'lion:lion' | chpasswd && usermod -aG sudo lion && usermod -s /bin/bash lion

RUN apt-get update \
    && apt-get install -y sudo nano cron sendmail libpng-dev libzip-dev zlib1g-dev \
    && apt-get install -y libonig-dev supervisor libevent-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring gd pdo_mysql mysqli zip

COPY . .

RUN a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD composer install && php -S 0.0.0.0:8000