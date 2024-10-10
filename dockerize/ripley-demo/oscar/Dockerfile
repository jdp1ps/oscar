#####################################
# DOCKERFILE TO BUILD APACHE2, PHP8.2
# BASED ON DEBIAN:BULLSEYE-BACKPORTS
#####################################
FROM debian:bullseye-backports
#FROM debian:bookworm-backports

# Issue du docker-compose
ARG proxy_environnement=""
ARG service_oscar_name="oscar_ripley"
ARG service_gearman_name="oscar_ripley_gearman"
ARG service_elasticsearch_name="oscar_ripley_elasticsearch"
#ARG CONFIG_TEST="../config"

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL description="OSCAR"

# PROXY (environment/apt)
# REMPLACE PAR SOLUTION CI-DESSOUS FONCTIONNE A TOUS LES COUPS
RUN if [ "$proxy_environnement" != "" ]; \
    then \
      echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

RUN apt-get update
RUN apt-get install -y locales && localedef -i en_US -c -f UTF-8 -A /usr/share/locale/locale.alias en_US.UTF-8
ENV LANG en_US.utf8


########################################################################################################################
## PAQUETS de BASE
RUN apt-get install -y\
        lsb-release \
        apt-transport-https \
        ca-certificates \
        wget \
        git

## PROXY (wget)
## Des doutes sur le fonctionnement, perso j'ai eu des soucis derrière le proxy unicaen (HM)
COPY etc/wgetrc /etc/wgetrc

## INSTALL PACKAGES OS
RUN apt-get install -y \
    libboost-all-dev \
    libboost-dev \
    libuuid1 \
    uuid-dev \
    gperf \
    apache2 \
    nano \
    supervisor \
    curl \
    libaio1 \
    libpng-dev \
    libpq-dev \
    libicu-dev \
    ldap-utils \
    libldap2-dev \
    libgearman-dev \
    libzip-dev \
    zip \
    gettext \
    libbz2-dev \
    unzip \
    supervisor \
    gearman-tools \
    postgresql-client

RUN apt-get install -y gnupg

########################################################################################################################
## INSTALLATION de PHP8.2
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
RUN sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
RUN apt-get update

RUN apt-get install -y \
    php8.2 \
    php8.2-gearman \
    php8.2-bcmath \
    php8.2-bz2 \
    php8.2-cli \
    php8.2-curl \
    php8.2-dom \
    php8.2-gd \
    php8.2-intl \
    php8.2-ldap \
    php8.2-mbstring \
    php8.2-pgsql \
    php8.2-xml \
    php8.2-zip \
    php-bcmath \
    php8.2-dev

RUN apt-get clean

# Enable xdebug -> Need to install later xdebug
RUN pecl install xdebug

# SOUCIS DERRIERE PROXY UNICAEN (HM) METHODE COPIE DE FICHIERS, PASSAGE PAR UN TEST IF ET CELA FONCTIONNE DANS TOUS LES CAS DE FIGURE
# PROXY (wget)
#COPY etc/apt/wgetrc /etc/apt/wgetrc
# INSTALL OCI ET UTILISATION DE WGET (ATTENTION PROXY) -> LE HOOK VIA UN if CI-DESSOUS FONCTIONNE BIEN DERRIERE OU PAS UN PROXY (HM)
RUN if [ "$proxy_environnement" != "" ]; \
    then \
      pear config-set http_proxy "$proxy_environnement"; \
      echo "http_proxy = $proxy_environnement"  >> /etc/wgetrc && echo "https_proxy = $proxy_environnement" >> /etc/wgetrc; \
    fi

COPY resources/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip /tmp/
COPY resources/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip /tmp/

RUN unzip -o /tmp/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/ && \
    unzip -o /tmp/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/

RUN ln -sf /usr/local/instantclient_18_5 /usr/local/instantclient && \
    ln -sf /usr/local/instantclient/sqlplus /usr/local/bin/sqlplus

RUN echo 'instantclient,/usr/local/instantclient' | pecl install oci8
RUN echo "extension=oci8.so" > /etc/php/8.2/apache2/conf.d/30-php-oci8.ini && \
    echo "extension=oci8.so" > /etc/php/8.2/cli/conf.d/30-php-oci8.ini && \
    echo "/usr/local/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig

##RUN echo "http_proxy = http://proxy.unicaen.fr:3128"  >> /etc/wgetrc && echo "https_proxy = http://proxy.unicaen.fr:3128" >> /etc/wgetrc;
#RUN echo "XDEBUG"
COPY etc/php/8.2/mods-available/xdebug.ini /etc/php/8.2/mods-available/xdebug.ini
#RUN phpenmod -v ALL -s ALL xdebug

##POINT DE MONTAGE OSCAR
RUN mkdir -p /var/OscarApp
WORKDIR /var/OscarApp/

##CLEAN
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

##CONFIG APACHE2
#RUN rm /etc/apache2/site-available/000-default.conf /etc/apache2/site-available/default-ssl.conf
COPY etc/apache2/sites-available/oscar.conf /etc/apache2/sites-available/oscar.conf
RUN a2dissite 000-default && a2dissite default-ssl && a2ensite oscar

#SCRIPT CUSTO CONFIG APACHE2
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite && a2enmod ssl


##Composer (Attention proxy important pour du wget)
RUN wget https://getcomposer.org/download/2.2.22/composer.phar
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

COPY startup.sh /opt/startup.sh
RUN chmod +x /opt/startup.sh

### OSCAR CONFIG (default)
#RUN mkdir /etc/oscar_config/
#COPY oscar/config/autoload/local.php /opt/oscar_config/local.php
#COPY oscar/config/autoload/unicaen-app.local.php /opt/oscar_config/unicaen-app.local.php
#COPY oscar/config/autoload/unicaen-auth.local.php /opt/oscar_config/unicaen-auth.local.php

##SUPERVISOR FICHIERS DE CONFIG POUR MULTIPLES SERVICES IN CONTAINER DOCKER
COPY supervisor /etc/supervisor/conf.d/oscar.conf
EXPOSE 80 9000 9003

#SUPERVISOR LA CLEF DU MULTIPLE SERVICES DANS UN CONTAINER DOCKER EN NO DAEMON
CMD supervisord -n
