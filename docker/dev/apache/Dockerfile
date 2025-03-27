FROM php:8.2-apache-bookworm

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"

# RUN apt-get update && apt-get -y install php8.2-gearman
RUN apt update && apt install -y \
    wget \
    libpq-dev \
    postgresql-client \
    libgearman-dev \
    gearman-job-server

RUN pecl install gearman  \
    && docker-php-ext-enable gearman \
    # XDebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install gettext \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install sockets \
    # Sodium
    && apt install -y libsodium-dev \
    && docker-php-ext-install sodium \
    # Intl
    && apt install -y libicu-dev \
    && docker-php-ext-configure intl \ 
    && docker-php-ext-install intl \
    # Ldap
    && apt install -y libldap2-dev \
    && docker-php-ext-install ldap \
    # Gd
    && apt install zlib1g-dev  \
    && apt install -y libpng-dev \
    && docker-php-ext-install gd \
    # Zip
    && apt install -y libzip-dev \
    && docker-php-ext-install zip \
    # Git
    && apt install -y git

# Installation de composer
RUN wget https://getcomposer.org/download/2.2.24/composer.phar
RUN mv composer.phar /bin/composer && chmod +x /bin/composer

RUN apt install -y libbz2-dev \
    && docker-php-ext-install bz2

RUN apt install -y apache2-suexec-custom sudo git
RUN apt install -y sendmail

RUN a2enmod rewrite
#RUN a2enmod suexec
#
#
#RUN groupadd app
#RUN useradd -m -s /bin/bash -g app app
## RUN chown -R app:app /var/application

# Configuration PHP
COPY ./php.ini /usr/local/etc/php/conf.d/999-application.ini

## Mail rerouté var mailhog avec mhsendmail
RUN wget https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
RUN chmod +x mhsendmail_linux_amd64
RUN mv mhsendmail_linux_amd64 /usr/local/bin/mhsendmail

WORKDIR /var/application

# Copier le script d'entrée
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Définir le script comme point d'entrée
ENTRYPOINT ["/entrypoint.sh"]

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

