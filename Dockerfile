FROM php:8.2-cli

# Install extension yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip gd mbstring

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project
COPY . .

# Install dependency
RUN composer install

# Run app
CMD php -S 0.0.0.0:$PORT -t public