FROM php:8.2-apache

RUN docker-php-ext-install mysqli

# Default DB connection settings. Override these at deploy time with
# `docker run -e DB_HOST=... -e DB_USER=... ...` or your platform's
# environment-variable settings — never hardcode real production
# credentials here or in config/db.php.
ENV DB_HOST=localhost
ENV DB_USER=root
ENV DB_PASS=
ENV DB_NAME=badminton_booking

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80