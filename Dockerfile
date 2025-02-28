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

RUN composer install --no-dev --optimize-autoloader

COPY app /var/www/html/app

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/app|' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/app && \
    chmod -R 755 /var/www/html/app

RUN a2enmod rewrite

RUN echo '<Directory "/var/www/html/app">' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 6148

CMD ["apache2-foreground"]

