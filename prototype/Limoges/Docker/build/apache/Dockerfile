#image Apache-PHP7 pour OSCAR

FROM debian:stretch

MAINTAINER damien.rieu@unilim.fr

################################ VARIABLE ###########################################

ENV OSCAR_APP_DIR /var/oscar/oscar
ENV OSCAR_DOCDIR /opt/documents
ENV OSCAR_CONNECTDIR /var/oscar/connector

ENV OSCAR_VAR /var/oscar
ENV OSCAR_OPT /opt/oscar
ENV OSCAR_ROOT /root/oscar

#####################################################################################

RUN apt-get update  -y 
RUN apt-get upgrade -y
RUN apt-get install -y apache2
RUN apt-get install -y php 
RUN apt-get install -y php-ldap
RUN apt-get install -y php-curl
RUN apt-get install -y php-cli
RUN apt-get install -y php-pgsql
RUN apt-get install -y php-intl
RUN apt-get install -y php-mcrypt
RUN apt-get install -y php-pear
RUN apt-get install -y php-dev
RUN apt-get install -y libaio1
RUN apt-get install -y libapache2-mod-php 

RUN apt-get install -y cron

######################################### TODO : cron installation de postfix

RUN apt-get install -y dnsutils
RUN apt-get install -y unzip
RUN apt-get install -y lynx
RUN apt-get install -y curl
RUN apt-get install -y screen
RUN apt-get install -y net-tools

RUN apt-get install -y git-core
RUN apt-get install -y vim

#Fix bug with tar command by using bsdtar

RUN apt-get install -y --no-install-recommends bsdtar

# PHP OCI

ENV OCI_DIR /usr/lib/oracle/12.2/client64/lib/
RUN mkdir -p $OCI_DIR
COPY ./oci/instantclient-basiclite-linux.x64-12.2.0.1.0.zip /root
RUN unzip /root/instantclient-basiclite-linux.x64-12.2.0.1.0.zip -d /root/

COPY ./oci/instantclient-sdk-linux.x64-12.2.0.1.0.zip /root
RUN unzip /root/instantclient-sdk-linux.x64-12.2.0.1.0.zip -d /root/

RUN mv /root/instantclient_12_2/* /usr/lib/oracle/12.2/client64/lib/
RUN ln -s libclntsh.so.12.1 $OCI_DIR/libclntsh.so


RUN echo "# Oracle Instant Client" >> /etc/ld.so.conf.d/x86_64-oracle.conf
RUN echo "/usr/lib/oracle/12.2/client64/lib" >> /etc/ld.so.conf.d/x86_64-oracle.conf

RUN ldconfig

RUN echo "instantclient,/usr/lib/oracle/12.2/client64/lib" | pecl install oci8

RUN echo "extension=oci8.so" >> /etc/php/7.0/mods-available/oci8.ini
RUN ln -s /etc/php/7.0/mods-available/oci8.ini /etc/php/7.0/cli/conf.d/ 
RUN ln -s /etc/php/7.0/mods-available/oci8.ini /etc/php/7.0/apache2/conf.d/

# OSCAR

#################################################### TODO : GIT CLONE

#RUN git clone git@git.unicaen.fr:bouvry/oscar.git
#RUN echo "Clone de l'application OSCAR"
#RUN git clone https://git.unilim.fr/Docker/oscar_caen.git $OSCAR_APP_DIR

RUN mkdir -p $OSCAR_APP_DIR
RUN mkdir -p $OSCAR_APP_DIR/public
RUN mkdir -p $OSCAR_DOCDIR

RUN echo 'Creating OSCAR data directory'
RUN mkdir -p $OSCAR_APP_DIR/data
RUN chown -R www-data:www-data $OSCAR_APP_DIR/data

RUN echo 'Creating OSCAR logs directory'
RUN mkdir -p $OSCAR_APP_DIR/logs
RUN chown -R www-data:www-data $OSCAR_APP_DIR/logs

RUN echo 'Creating OSCAR docs directory'

RUN mkdir -p $OSCAR_DOCDIR/activity
RUN chown -R www-data:www-data $OSCAR_DOCDIR/activity

RUN mkdir -p $OSCAR_DOCDIR/admin
RUN chown -R www-data:www-data $OSCAR_DOCDIR/admin

RUN echo 'Creating public directory'
RUN mkdir -p $OSCAR_APP_DIR/public

COPY ./oscar.tar.gz $OSCAR_VAR
RUN bsdtar xzf $OSCAR_VAR/oscar.tar.gz -C $OSCAR_VAR
RUN bsdtar xzf $OSCAR_APP_DIR/install/vendor.tar.gz -C $OSCAR_APP_DIR

RUN echo "Clone des scripts unilim"
RUN git clone https://git.unilim.fr/rieup01/oscar.git $OSCAR_ROOT

RUN mkdir -p $OSCAR_CONNECTDIR
RUN chown www-data:www-data $OSCAR_CONNECTDIR

RUN touch $OSCAR_CONNECTDIR/person_connector.log
RUN chmod o=+w $OSCAR_CONNECTDIR/person_connector.log
RUN touch $OSCAR_CONNECTDIR/organization_connector.log
RUN chmod o=+w $OSCAR_CONNECTDIR/organization_connector.log

############## Archive ###############################

RUN mkdir -p $OSCAR_VAR/archive/
RUN mkdir -p $OSCAR_VAR/archive/postgresql
RUN mkdir -p $OSCAR_VAR/archive/synchro

RUN mkdir -p $OSCAR_OPT
RUN mkdir -p $OSCAR_OPT/postgresql-backup

RUN echo 'Configure connectors'
RUN cp $OSCAR_ROOT/connectors/PersonConnector.php $OSCAR_CONNECTDIR
RUN cp $OSCAR_ROOT/connectors/oscar.ini $OSCAR_CONNECTDIR
RUN cp $OSCAR_ROOT/connectors/OrganizationConnector.php $OSCAR_CONNECTDIR
RUN cp $OSCAR_ROOT/connectors/index.php $OSCAR_CONNECTDIR

RUN chmod 770 $OSCAR_CONNECTDIR/PersonConnector.php
RUN chmod 770 $OSCAR_CONNECTDIR/oscar.ini
RUN chmod 770 $OSCAR_CONNECTDIR/OrganizationConnector.php
RUN chown www-data:www-data $OSCAR_CONNECTDIR/index.php

RUN chown www-data:www-data $OSCAR_CONNECTDIR/organization_connector.log
RUN chown www-data:www-data $OSCAR_CONNECTDIR/person_connector.log

COPY ./dev/connectors/person_rest.yml $OSCAR_APP_DIR/config/connectors
COPY ./dev/connectors/organization_rest.yml $OSCAR_APP_DIR/config/connectors

RUN echo 'Copy configuration files'
COPY ./dev/autoload/global.php $OSCAR_APP_DIR/config/autoload/
COPY ./dev/autoload/unicaen-app.local.php $OSCAR_APP_DIR/config/autoload/
COPY ./dev/autoload/unicaen-auth.local.php $OSCAR_APP_DIR/config/autoload/
COPY ./dev/autoload/local.php $OSCAR_APP_DIR/config/autoload/
COPY ./dev/autoload/unicaen-auth.global.php $OSCAR_APP_DIR/config/autoload/

# Crontab configuration

RUN echo 'Crontab configuration'
RUN echo "#Crontab configuration" >> /var/spool/cron/crontabs/root 
RUN chmod o=rw /var/spool/cron/crontabs/root
RUN echo "# Save postgres database" >> /var/spool/cron/crontabs/root
RUN echo "# 00 22 * * * /root/oscar/scripts/crontab/save_oscar_db.sh >> /var/oscar/archive/postgresql/save_oscar_db.log" >> /var/spool/cron/crontabs/root
RUN echo "# Launch Scripts of Synchro" >> /var/spool/cron/crontabs/root
RUN echo "# 00 01 * * * /root/oscar/scripts/crontab/synchro_organization_oscar.sh >> /var/oscar/archive/synchro/synchro_organization_oscar.log" >> /var/spool/cron/crontabs/root

RUN echo '# Launch Scripts for PERSON Synchro' >> /var/spool/cron/crontabs/root
RUN echo "# 00 02 * * * /root/oscar/scripts/crontab/synchro_person_oscar.sh >> /var/oscar/archive/synchro/synchro_person_oscar.log" >> /var/spool/cron/crontabs/root
RUN echo '# Send a mail with  Apache error log' >> /var/spool/cron/crontabs/root
RUN echo "# 00 03 * * * /root/oscar/scripts/crontab/check_apache_oscar.sh > /dev/null 2>&1" >> /var/spool/cron/crontabs/root

# Apache configuration

RUN a2enmod rewrite
RUN a2enmod ssl  

COPY ./dev/000-default.conf /etc/apache2/sites-available/000-default.conf 
COPY ./dev/ports.conf /etc/apache2/ports.conf 

COPY ./dev/certificats/oscar_unilim_fr.crt /etc/ssl/certs/
COPY ./dev/certificats/DigiCertCA.crt /etc/ssl/certs/
COPY ./dev/certificats/oscar_unilim_fr.key /etc/ssl/private/

# Clean
RUN rm $OSCAR_VAR/oscar.tar.gz

EXPOSE 80
EXPOSE 81


CMD ["/usr/sbin/apache2ctl","-D","FOREGROUND"]
