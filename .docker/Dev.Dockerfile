FROM php:8.4-apache

## Install deps
RUN apt-get update &&\
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install ssh git unzip &&\
    apt-get clean && apt-get --yes --quiet autoremove --purge &&\
    curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions &&\
    chmod +x /usr/local/bin/install-php-extensions &&\
    install-php-extensions zip pdo pdo_mysql opcache apcu intl soap gd imagick exif xdebug &&\
    mkdir -p /var/www/html/var/log/

## Composer install
RUN curl -sSL https://getcomposer.org/installer | php -- --2.2 --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

## Php configuration
COPY php/dev.php.ini /usr/local/etc/php/conf.d/.

## Apache2 configuration
COPY apache2/app.conf /etc/apache2/sites-enabled/app.conf
RUN a2dissite 000-default && a2enmod rewrite &&\
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

## Perms
ARG UID=1000
RUN groupmod -g ${UID} www-data && usermod -u ${UID} www-data && chown -R www-data:www-data /var/www

USER www-data
WORKDIR /var/www/html
EXPOSE 80
