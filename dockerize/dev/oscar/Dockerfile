####################################
# DOCKERFILE TO BUILD APACHE2, PHP7
# BASED ON DEBIAN
####################################
#FROM debian
FROM debian:bullseye-backports

# Issue du docker-compose
ARG proxy_environnement
ARG tag_version_oscar="Spartan"
ARG service_oscar_name="oscar_dev_spartan"
ARG service_gearman_name="oscar_dev_gearman_spartan"
ARG service_elastic_search_name="oscar_dev_elasticsearch_spartan"
ARG CONFIG_TEST="../config"

# Infos
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL version="$tag_version_oscar"
LABEL description="OSCAR $tag_version_oscar - CONF etc"

# PROXY (environment/apt)
COPY etc/apt/apt.conf.d/proxy /etc/apt/apt.conf.d/proxy
COPY etc/environment /etc/environment

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y\
        lsb-release \
        apt-transport-https \
        ca-certificates \
        wget \
        git \
        && apt-get update && apt-get upgrade -y && apt-get clean

# PROXY (wget)
# Ca ca marche pas...
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
        php-pear \
        php-xdebug \
        #php-mcrypt \
        && apt-get clean

# Enable xdebug -> Need to install later xdebug
#RUN pecl install xdebug

# Install OCI
#RUN pear config-set http_proxy "${http_proxy}"
RUN pear config-set http_proxy "http://proxy.unicaen.fr:3128"
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


#Install GEARMAN CLIENT FROM SOURCE
#RUN mkdir -p /tmp/install \
#    && cd /tmp/install \
#    && wget https://github.com/wcgallego/pecl-gearman/archive/master.zip \
#    && unzip master.zip \
#    && cd pecl-gearman-master \
#    && phpize \
#    && sh configure \
#    && make install

RUN echo "http_proxy = http://proxy.unicaen.fr:3128"  >> /etc/wgetrc && echo "https_proxy = http://proxy.unicaen.fr:3128" >> /etc/wgetrc;

RUN mkdir -p /tmp/install \
    && cd /tmp/install \
    && wget https://github.com/gearman/gearmand/releases/download/1.1.19.1/gearmand-1.1.19.1.tar.gz \
    && tar xzf gearmand-1.1.19.1.tar.gz \
    && cd  gearmand-1.1.19.1
WORKDIR /tmp/install/gearmand-1.1.19.1/
RUN ./configure \
    && make \
    && make install

#RUN echo "extension=gearman.so" >> /etc/php/7.4/mods-available/gearman.ini
RUN echo "extension=gearman.so" > /etc/php/7.4/cli/conf.d/gearman.so
RUN phpenmod -v ALL -s ALL gearman

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


#SUPERVISOR FICHIERS DE CONFIG POUR MULTIPLES SERVICES IN CONTAINER DOCKER
COPY supervisor /etc/supervisor/conf.d/oscar.conf
EXPOSE 80 9000 9003

#SUPERVISOR LA CLEF DU MULTIPLE SERVICES DANS UN CONTAINER DOCKER EN NO DAEMON
CMD supervisord -n
