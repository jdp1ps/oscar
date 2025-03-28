FROM php:8.2-fpm

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"

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

## Mail rerouté var mailhog avec mhsendmail
RUN wget https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
RUN chmod +x mhsendmail_linux_amd64
RUN mv mhsendmail_linux_amd64 /usr/local/bin/mhsendmail

# Installation de composer
RUN wget https://getcomposer.org/download/2.2.24/composer.phar
RUN mv composer.phar /bin/composer
RUN chmod +x /bin/composer

# Copier le script de surveillance
WORKDIR /var/application

EXPOSE 9000

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
