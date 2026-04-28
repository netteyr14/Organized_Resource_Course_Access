FROM php:8.2-apache

# Install only PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite (if using .htaccess)
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Copy Aiven SSL cert if needed
# COPY certs/ca.pem /var/www/certs/ca.pem

EXPOSE 80