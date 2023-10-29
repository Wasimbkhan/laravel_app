FROM php:8.2-fpm

ARG user
ARG uid

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#----------------------------------------------------------------------------------------------------------------
# Install Composer globally
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#Copy the composer.json and composer.lock files into the container
#COPY composer.json /var/www/html/
#-----------------------------------------------------------------------------------------------------------------

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Create working directory 
WORKDIR /var/www/html
COPY . /var/www/html
COPY --chown=$user . /var/www/html
RUN chown -R $user:$user /var/www/html
USER $user

#Install Composer dependencies & RUN composer install
RUN composer install

CMD ["php artisan key:generate"]



