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

RUN apt install -y unzip libaio1 build-essential expect

# Driver OCI (Oracle)
COPY resources/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip /tmp/

RUN unzip -o /tmp/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/

RUN ln -sf /usr/local/instantclient_18_5 /usr/local/instantclient && \
    ln -sf /usr/local/instantclient/sqlplus /usr/local/bin/sqlplus

RUN echo 'instantclient,/usr/local/instantclient' | pecl install oci8
RUN echo "extension=oci8.so" > /usr/local/etc/php/conf.d/910-php-oci8.ini
COPY ./99-oscar.ini /usr/local/etc/php/conf.d/911-oscar.ini
RUN echo "/usr/local/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig
#
#RUN rm /tmp/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip
#RUN rm /tmp/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip
#RUN rm /tmp/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip


# Copier le script de surveillance
WORKDIR /var/application

EXPOSE 9000

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
