FROM php:8.2-apache

RUN useradd -m lion && echo 'lion:lion' | chpasswd && usermod -aG sudo lion && usermod -s /bin/bash lion

RUN apt-get update -y \
    && apt-get install -y nano default-mysql-client curl wget unzip cron sendmail libpng-dev libzip-dev \
    && apt-get install -y zlib1g-dev libonig-dev supervisor libevent-dev libssl-dev libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install ev \
    && docker-php-ext-install mbstring gd pdo_mysql mysqli zip pdo_pgsql pgsql \
    && docker-php-ext-enable gd zip

RUN a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

CMD php -S 0.0.0.0:8000
