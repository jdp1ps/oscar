FROM php:8.2-cli

LABEL authors="Stéphane BOUVRY<stephane.bouvry@unicaen.fr>"

# Installer les dépendances nécessaires
RUN apt update && apt install -y libgearman-dev && \
    pecl install gearman && \
    docker-php-ext-enable gearman

RUN docker-php-ext-install gettext

# Copier le script de surveillance
WORKDIR /var/application

# Exécuter le script en boucle
CMD ["php", "/var/application/bin/oscar-worker.php"]