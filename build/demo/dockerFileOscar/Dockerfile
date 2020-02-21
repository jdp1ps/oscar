####################################
# DOCKERFILE TO BUILD APACHE2, PHP7
# BASED ON DEBIAN
####################################
FROM debian
ARG proxy_environnement
ARG user_docker_git_demo
ARG token_user_docker_git_demo
ARG tag_version_oscar
ARG service_oscar_name
ARG service_gearman_name
ARG service_elastic_search_name
LABEL authors="Herve Marie <herve.marie@unicaen.fr> Stéphane Bouvry <stephane.bouvry@unicaen.fr>"
LABEL version=${TAG}
LABEL description="Version Démo Docker Oscar Gearman branch $TAG"

#VOLUME ["/var/www/html/"]
#ACTIVER OU DESACTIVER PROXY ATTENTION IL FAUT IMPERATIVEMENT ACQUERIR LE PAQUET WGET SANS CUSTOMISATION DE SON /etc/wgetrc AVANT
#DE LANCER DES AJOUTS DANS /etc/wgetrc POUR UTILISATION DE WGET SINON L'INSTALATION DE WGET SI AJOUT DE CUSTOMISATION RETOURNERA
#UNE ERREUR DE TYPE : Sub-process /usr/bin/dpkg returned an error code (1) -> ERREUR PEUT PARLANTE !!! :p
RUN if [ "$proxy_environnement" != "noProxy" ]; \
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
RUN if [ "$proxy_environnement" != "noProxy" ]; \
    then \
      echo "http_proxy = $proxy_environnement"  >> /etc/wgetrc && echo "https_proxy = $proxy_environnement" >> /etc/wgetrc; \
    fi
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.3.list
# INSTALL PACKAGES OS
RUN apt-get install -y  \
        apache2 \
        nano \
        supervisor \
        curl \
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
        php7.3 \
        php7.3-bz2 \
        php7.3-cli \
        php7.3-curl \
        php7.3-dom \
        php7.3-gd \
        php7.3-intl \
        php7.3-ldap \
        php7.3-mbstring \
        php7.3-pgsql \
        php7.3-xml \
        php7.3-zip \
        php-bcmath \
        php7.3-dev \
        #php-mcrypt \
        && apt-get clean

# Enable xdebug -> Need to install later xdebug
#RUN pecl install xdebug

# Install GEARMAN CLIENT FROM SOURCE
RUN mkdir -p /tmp/install \
    && cd /tmp/install \
    && wget https://github.com/wcgallego/pecl-gearman/archive/master.zip \
    && unzip master.zip \
    && cd pecl-gearman-master \
    && phpize \
    && sh configure \
    && make install
RUN echo "extension=gearman.so" >> /etc/php/7.3/mods-available/gearman.ini
RUN phpenmod -v ALL -s ALL gearman

#CLONE DEPOT OSCAR AVEC TOKEN DE DEPLOIEMENT (READ-ONLY)
RUN mkdir -p /var/OscarApp
WORKDIR /var/OscarApp/
RUN git clone https://$user_docker_git_demo:$token_user_docker_git_demo@git.unicaen.fr/open-source/oscar.git .
RUN git checkout $tag_version_oscar
#DROITS REPERTOIRES ET APACHE USER
RUN chown -R  www-data:www-data . && chmod -R 755 data

#INIT COMPOSER
RUN wget https://getcomposer.org/composer.phar && mv composer.phar /bin/composer && chmod +x /bin/composer

#PROXY POUR COMPOSER UTILISANT VARIABLE ENVIRONNEMENT SI BESOIN
ENV http_proxy="$proxy_environnement"
RUN if [ "${http_proxy}" != "noProxy" ]; then export http_proxy; fi
#AJOUT DES DEPENDANCES VIA COMPOSER
RUN composer install --prefer-dist

#PARAMETRAGES OSCAR
#FICHIER EDITABLE OSCAR ET FICHIER DE CONFIG APPLI
RUN touch config/autoload/oscar-editable.yml && chmod 777 config/autoload/oscar-editable.yml && touch logs/oscar.log && chmod 777 logs/oscar.log
RUN cp config/autoload/local.php.dist config/autoload/local.php
#WORKER POUR GEARMAN
RUN cp install/oscarworker.dist.service config/oscarworker.service

#MODIFICATIONS DU FICHIER DE CONFIG OSCAR
RUN sed -i 's#localhost:9200#elasticsearchdemo:9200#g' config/autoload/local.php
RUN sed -i "s#'host' *=> .*#'host'=>'postgresdemo',#" config/autoload/local.php
RUN sed -i "s#'gearman-job-server-host' *=> .*#'gearman-job-server-host'=>'gearmandemo',#" config/autoload/local.php
RUN sed -i 's#TRANSPORT (file)#TRANSPORT (file)\*/#' config/autoload/local.php
#MODIFICATIONS CONFIG GEARMAN OSCAR
RUN sed -i 's#/path/to/oscar/bin/oscar-worker.php#/var/OscarApp/bin/oscar-worker.php#' config/oscarworker.service

#CLEAN
RUN apt-get autoremove -y && apt-get clean && rm -rf /tmp/* /var/tmp/*
#NO PROXY POUR LE DIALOGUE DES CONTAINERS ENTRE EUX
ENV no_proxy=${service_oscar_name},${service_gearman_name},${service_elastic_search_name}

#CONFIG APACHE2
COPY apache_default /etc/apache2/sites-available/000-default.conf
#SCRIPT CUSTO CONFIG APACHE2
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite && a2enmod ssl
#SUPERVISOR FICHIERS DE CONFIG POUR MULTIPLES SERVICES IN CONTAINER DOCKER
COPY supervisor /etc/supervisor/conf.d/oscar.conf
EXPOSE 80
#SUPERVISOR LA CLEF DU MULTIPLE SERVICES DANS UN CONTAINER DOCKER EN NO DAEMON
CMD supervisord -n
