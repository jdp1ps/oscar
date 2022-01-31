####################################
# DOCKERFILE TO BUILD APACHE2, PHP7
# BASED ON DEBIAN
####################################
#FROM debian
FROM debian:bullseye-backports

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL version="Oscar Spartan Demo"
LABEL description="OSCAR DEMO"

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y\
        lsb-release \
        apt-transport-https \
        ca-certificates \
        wget \
        git \
        && apt-get update && apt-get upgrade -y && apt-get clean

# INSTALL PACKAGES OS
RUN apt-get install -y  \
        apache2 \
        nano \
        supervisor \
        curl \

        # Pour OCI 8
        libaio1 \

        libpng-dev \
        libpq-dev \
        libicu-dev \
        ldap-utils \
        libldap2-dev \
        libgearman-dev \
        libzip-dev \
        zip \
        #wget \
        gettext \
        libbz2-dev \
        unzip \
        supervisor \
        gearman-tools \
        postgresql-client \
        php7.4 \
        php7.4-bz2 \
        php7.4-cli \
        php7.4-curl \
        php7.4-dom \
        php7.4-gd \
        php7.4-intl \
        php7.4-ldap \
        php7.4-mbstring \
        php7.4-pgsql \
        php7.4-xml \
        php7.4-zip \
        php-bcmath \
        php7.4-dev \
        php-xdebug \
        && apt-get clean

# Install OCI
#pear config-set http_proxy "${http_proxy}"
COPY resources/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip /tmp/

RUN unzip -o /tmp/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/

RUN ln -sf /usr/local/instantclient_18_5 /usr/local/instantclient && \
    ln -sf /usr/local/instantclient/sqlplus /usr/local/bin/sqlplus

RUN echo 'instantclient,/usr/local/instantclient' | pecl install oci8-2.2.0 && \
    echo "extension=oci8.so" > /etc/php/7.4/apache2/conf.d/30-php-oci8.ini && \
    echo "extension=oci8.so" > /etc/php/7.4/cli/conf.d/30-php-oci8.ini && \
    echo "/usr/local/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig


# Install GEARMAN CLIENT FROM SOURCE
RUN mkdir -p /tmp/install \
    && cd /tmp/install \
    && wget https://github.com/wcgallego/pecl-gearman/archive/master.zip \
    && unzip master.zip \
    && cd pecl-gearman-master \
    && phpize \
    && sh configure \
    && make install

RUN echo "extension=gearman.so" >> /etc/php/7.4/mods-available/gearman.ini
RUN phpenmod -v ALL -s ALL gearman

#CLEAN
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

########################################################################################################################
# ---------------
# OSCAR (Spartan)
# ---------------

# Récupération des sources et passage sur la branche spartan
RUN mkdir -p /var/OscarApp
WORKDIR /var/OscarApp/
RUN git clone https://demo:glpat-FSA-1dKi6gM9XF_9sY2L@git.unicaen.fr/open-source/oscar.git
WORKDIR /var/OscarApp/oscar/
RUN git checkout spartan && git pull

# Installation de composer
WORKDIR /tmp/
RUN wget https://getcomposer.org/download/latest-stable/composer.phar
RUN mv composer.phar /usr/bin/composer
RUN chmod +x /usr/bin/composer

# installation des dépendances PHP
WORKDIR /var/OscarApp/oscar/
RUN composer install

# Configuration Apache
COPY etc/apache2/sites-available/oscar.conf /etc/apache2/sites-available/oscar.conf
RUN a2dissite 000-default && a2dissite default-ssl && a2ensite oscar

#RUN echo "BUILD3" >> /tmp/version

# Fichiers de configuration Oscar
COPY oscar/config/autoload/local.php /var/OscarApp/oscar/config/autoload/local.php
COPY oscar/config/autoload/unicaen-app.local.php /var/OscarApp/oscar/config/autoload/unicaen-app.local.php
COPY oscar/config/autoload/unicaen-auth.local.php /var/OscarApp/oscar/config/autoload/unicaen-auth.local.php
COPY oscar/config/oscarworker.service /var/OscarApp/oscar/config/oscarworker.service
RUN touch /var/OscarApp/oscar/config/autoload/oscar-editable.yml
RUN chmod 777 /var/OscarApp/oscar/config/autoload/oscar-editable.yml

# Droits d'accès aux fichiers de LOG/DOSIERS pour Oscar
RUN touch /var/OscarApp/oscar/logs/worker.log
RUN touch /var/OscarApp/oscar/logs/oscar.log
RUN mkdir -p /var/OscarApp/oscar/tmp
RUN chmod 777 -R /var/OscarApp/oscar/logs
RUN chmod 777 -R /var/OscarApp/oscar/data
RUN chmod 777 -R /var/OscarApp/oscar/tmp

# Supervisor (Made in Hervé :P)
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite && a2enmod ssl

RUN echo "1.0.1" >> /var/OscarApp/version


#SUPERVISOR FICHIERS DE CONFIG POUR MULTIPLES SERVICES IN CONTAINER DOCKER
COPY supervisor /etc/supervisor/conf.d/oscar.conf
EXPOSE 80 9000

#SUPERVISOR LA CLEF DU MULTIPLE SERVICES DANS UN CONTAINER DOCKER EN NO DAEMON
CMD supervisord -n
