FROM php:8.2-apache-bookworm

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"

ENV APACHE_DOCUMENT_ROOT /var/application/public/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# RUN apt-get update && apt-get -y install php8.2-gearman
RUN apt update && apt install -y \
    wget \
    libpq-dev \
    postgresql-client \
    libgearman-dev \
    gearman-job-server

# Configuration PHP
COPY ./php.ini /usr/local/etc/php/conf.d/999-application.ini

RUN pecl install gearman
RUN docker-php-ext-enable gearman

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gettext
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install sockets

RUN apt install -y libsodium-dev
RUN docker-php-ext-install sodium

RUN apt-get install -y libicu-dev \ 
    && docker-php-ext-configure intl \ 
    && docker-php-ext-install intl


# LDAP
RUN apt-get install -y libldap2-dev && \
docker-php-ext-install ldap

RUN apt install zlib1g-dev 
RUN apt install -y libpng-dev
RUN docker-php-ext-install gd

RUN apt install -y libzip-dev
RUN docker-php-ext-install zip

# Git
RUN apt install -y git

# Installation de composer
RUN wget https://getcomposer.org/download/2.2.24/composer.phar
RUN mv composer.phar /bin/composer
RUN chmod +x /bin/composer

RUN a2enmod rewrite


WORKDIR /var/application

RUN mkdir -p data/DoctrineORMModule/Proxy && chown -R www-data data/DoctrineORMModule/Proxy
#RUN chmod 777 config/autoload/oscar-editable.yml
#USER www-data

#RUN composer install
