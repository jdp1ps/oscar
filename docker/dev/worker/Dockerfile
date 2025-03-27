FROM php:8.2-cli

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

# Copier le script de surveillance
WORKDIR /var/application

# Exécuter le script en boucle
CMD ["php", "/var/application/bin/oscar-worker.php"]