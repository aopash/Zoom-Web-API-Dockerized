FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    git \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json ./composer.json

RUN cat composer.json

RUN composer install --no-dev --optimize-autoloader

RUN ls -l /var/www/html/vendor

COPY . .

RUN chown -R www-data:www-data /var/www/html

RUN a2enmod rewrite

EXPOSE 6148

CMD ["apache2-foreground"]

