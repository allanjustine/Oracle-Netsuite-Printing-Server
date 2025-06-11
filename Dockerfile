FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk --no-cache add \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www

# Install Composer (Laravel's dependency manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your Laravel project into the container
COPY . /var/www

EXPOSE 1002

CMD ["php-fpm"]
