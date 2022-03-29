#####################################
# DOCKERFILE TO BUILD APACHE2, PHP7.4
# BASED ON DEBIAN:BULLSEYE-BACKPORTS
#####################################
FROM debian:bullseye-backports

# Issue du docker-compose
ARG proxy_environnement
ARG service_oscar_name="oscar_dev_spartan"
ARG service_gearman_name="oscar_dev_gearman_spartan"
ARG service_elasticsearch_name="oscar_dev_elasticsearch_spartan"
ARG CONFIG_TEST="../config"

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL description="OSCAR"

# AVEC CETTE SOLUTION CI-DESSOUS (HM)) J'AI EU DES SOUCIS, FAUT'IL FAIRE AUTRE CHOSE QUE COPIER PAS TROUVE DE SOLUTION VIABLE STEPH AVEC COPIE DE FICHIERS.
# PROXY (environment/apt)
#COPY etc/apt/apt.conf.d/proxy /etc/apt/apt.conf.d/proxy
#COPY etc/environment /etc/environment
# REMPLACE PAR SOLUTION CI-DESSOUS FONCTIONNE A TOUS LES COUPS
RUN if [ "$proxy_environnement" != "" ]; \
    then \
      echo "Acquire::http::proxy \"$proxy_environnement\";" > /etc/apt/apt.conf.d/proxy; \
    fi

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y\
        lsb-release \
        apt-transport-https \
        ca-certificates \
        wget \
        git \
        && apt-get update && apt-get upgrade -y && apt-get clean

# PROXY (wget)
# Des doutes sur le fonctionnement, perso j'ai eu des soucis derrière le proxy unicaen (HM)
COPY etc/apt/wgetrc /etc/apt/wgetrc

# INSTALL PACKAGES OS
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
        postgresql-client \
        php7.4 \
        php7.4-gearman \
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
        php-pear \
        php-xdebug \
        && apt-get clean

# Enable xdebug -> Need to install later xdebug
#RUN pecl install xdebug

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

RUN echo 'instantclient,/usr/local/instantclient' | pecl install oci8-2.2.0
RUN echo "extension=oci8.so" > /etc/php/7.4/apache2/conf.d/30-php-oci8.ini && \
    echo "extension=oci8.so" > /etc/php/7.4/cli/conf.d/30-php-oci8.ini && \
    echo "/usr/local/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf && ldconfig

#RUN echo "http_proxy = http://proxy.unicaen.fr:3128"  >> /etc/wgetrc && echo "https_proxy = http://proxy.unicaen.fr:3128" >> /etc/wgetrc;
RUN echo "XDEBUG"
COPY etc/php/7.4/mods-available/xdebug.ini /etc/php/7.4/mods-available/xdebug.ini
RUN phpenmod -v ALL -s ALL xdebug

#POINT DE MONTAGE OSCAR
RUN mkdir -p /var/OscarApp
WORKDIR /var/OscarApp/

#CLEAN
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*

#CONFIG APACHE2
#RUN rm /etc/apache2/site-available/000-default.conf /etc/apache2/site-available/default-ssl.conf
COPY etc/apache2/sites-available/oscar.conf /etc/apache2/sites-available/oscar.conf
RUN a2dissite 000-default && a2dissite default-ssl && a2ensite oscar

#SCRIPT CUSTO CONFIG APACHE2
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite && a2enmod ssl

#Composer (Attention proxy important pour du wget)
RUN wget https://getcomposer.org/download/latest-stable/composer.phar
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN echo "UPDATE"

#SCRIPT INIT
COPY startup.sh /opt/startup.sh
RUN chmod +x /opt/startup.sh

#SUPERVISOR FICHIERS DE CONFIG POUR MULTIPLES SERVICES IN CONTAINER DOCKER
COPY supervisor /etc/supervisor/conf.d/oscar.conf
EXPOSE 80 9000 9003

#SUPERVISOR LA CLEF DU MULTIPLE SERVICES DANS UN CONTAINER DOCKER EN NO DAEMON
CMD supervisord -n
