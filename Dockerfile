FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli

# Enable Apache rewrite (optional but good)
RUN a2enmod rewrite

# Copy project files
COPY app/ /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
