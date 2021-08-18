FROM php:8.0-apache

# update the repository sources list
# and install basic dependencies
RUN apt-get update -y \
  && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    vim \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# install & enable mysql driver
RUN docker-php-ext-install pdo_mysql

# install & enable xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# install & enable intl
RUN docker-php-ext-configure intl --enable-intl
RUN docker-php-ext-install intl
RUN docker-php-ext-enable intl

# install & enable php zip
RUN docker-php-ext-install zip
RUN docker-php-ext-enable zip

# install gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd

# change uid and gid of apache to docker user uid/gid
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# reconfigure the virtual hosts to use the ones defined in .docker/apache-vhost.conf
COPY .docker/apache-vhost.conf /etc/apache2/sites-available/custom-vhost.conf
RUN a2dissite 000-default.conf
RUN a2ensite custom-vhost.conf

# use custom php.ini
COPY .docker/php.ini /usr/local/etc/php/

# activate mod rewrite
RUN a2enmod rewrite

# set our application folder as an environment variable
ENV APP_HOME /var/www/html

# set the working dir
WORKDIR $APP_HOME

# change ownership of our application
RUN chown -R www-data:www-data $APP_HOME
