FROM php:8.2-fpm-bullseye

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install mbstring zip exif pcntl gd pdo_pgsql pgsql
RUN docker-php-ext-configure intl && docker-php-ext-install intl

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install all dependencies including dev dependencies (we'll remove dev ones later)
RUN composer install --no-scripts --no-autoloader

# Copy package.json for better caching
COPY package.json package-lock.json ./
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get update && apt-get install -y nodejs
RUN npm ci

# Copy the rest of the application code
COPY --chown=www:www . .

# Generate optimized autoloader with all dependencies included
# For production, we'll keep dev dependencies for now to ensure all service providers are discovered
RUN composer dump-autoload --optimize

# Run post-autoload-dump scripts which may need dev dependencies
RUN composer run-script post-autoload-dump

# Now we can remove dev dependencies
RUN composer install --no-dev --optimize-autoloader

# Set proper permissions for storage and cache
RUN chown -R www:www /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Switch to non-root user
USER www

# Build frontend assets
RUN npm run build

# Switch back to root for proper permissions
USER root

# Remove development tools and packages to reduce image size
RUN apt-get remove -y git nodejs build-essential \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set proper ownership
RUN chown -R www:www /var/www/html

# Switch to non-root user for running the application
USER www

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
