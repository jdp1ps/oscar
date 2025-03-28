FROM debian:12-slim

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"

RUN apt update && apt install -y apache2

RUN a2enmod proxy_fcgi setenvif rewrite

EXPOSE 80

COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

CMD ["apachectl", "-DFOREGROUND"]