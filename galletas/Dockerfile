FROM php:8.3-cli
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache
EXPOSE 8080
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan storage:link && \
    php -S 0.0.0.0:8080 -t public
