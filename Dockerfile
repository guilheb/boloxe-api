#----------------------------------------------
# Build PHP dependencies (Composer)
#----------------------------------------------
FROM serversideup/php:8.4-fpm-nginx-alpine AS php_assets
WORKDIR /var/www/html

# Copy the entire project into the Composer build stage
COPY --chown=www-data:www-data . /var/www/html

USER www-data
RUN /usr/bin/composer install --no-dev --optimize-autoloader
