FROM php:8.2-apache

# Install basic required tools (git, unzip) without MongoDB build deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nano\
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json ./composer.json

# Install PHP dependencies (excluding dev) with optimized autoloader
RUN composer install --no-dev --optimize-autoloader

COPY app /var/www/html/app

# Update Apache document root to point to /app
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/app|' /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/app && \
    chmod -R 755 /var/www/html/app

# Enable Apache rewrite module
RUN a2enmod rewrite

# Allow .htaccess overrides in app directory
RUN echo '<Directory "/var/www/html/app">' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 6148

CMD ["apache2-foreground"]
