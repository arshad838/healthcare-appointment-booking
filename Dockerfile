# Use official PHP 8.3 image with Apache web server
FROM php:8.3-apache

# Install PDO MySQL extension for secure database connectivity
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite for flexible routing (if needed)
RUN a2enmod rewrite

# Set working directory inside container
WORKDIR /var/www/html

# Copy project files into the container Apache document root
COPY . /var/www/html/

# Adjust file and folder permissions so Apache can read and write files safely
RUN chown -R www-data:www-data /var/www/html/

# Expose HTTP port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
