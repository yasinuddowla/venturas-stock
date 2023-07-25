# Use the official PHP base image
FROM php:7.4-apache

# Install necessary PHP extensions and libraries
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your PHP application files into the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html/

# Expose port 80 for the Apache web server
EXPOSE 80

# Install MySQL client
RUN apt-get update && apt-get install -y default-mysql-client

# Environment variables for MySQL configuration
ENV MYSQL_HOST=mysql
ENV MYSQL_PORT=3306
ENV MYSQL_DATABASE=db_name
ENV MYSQL_USER=your_mysql_user
ENV MYSQL_PASSWORD=mysql_password

# Install and configure PHPMyAdmin (optional, for managing MySQL via a web interface)
RUN apt-get install -y phpmyadmin
RUN ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Start Apache web server
CMD ["apache2-foreground"]
