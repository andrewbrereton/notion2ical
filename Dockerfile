FROM php:8.1-fpm-alpine

LABEL org.label-schema.schema-version="1.0"
LABEL org.label-schema.name="notion2ical"
LABEL org.label-schema.vcs-url="https://github.com/andrewbrereton/notion2ical/"

COPY . /app
WORKDIR /app

# Get Composer and install deps
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /home/.composer
COPY --from=composer/composer:2-bin /composer /usr/bin/composer
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress ; composer clear-cache

# Do it
CMD ["php", "src/notion2ical.php"]
