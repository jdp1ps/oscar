#!/bin/bash

echo "OSCAR INITIALISATION...\n"

# On régle le safe.directory de GIT
git config --global --add safe.directory /var/application

# Installation des dépendances PHP
composer install --no-interaction

# Cache Doctrine
mkdir -p data/DoctrineORMModule/Proxy \
    && chown -R www-data:www-data data/DoctrineORMModule \
    && chmod 775 data/DoctrineORMModule

# Ecriture des dossiers (documents)
chown www-data -R /var/documents

# Logs
mkdir -p logs \
  && touch logs/oscar.log \
  && chown www-data logs

# Oscar Config Editable
touch config/autoload/oscar-editable.yml \
  && chown www-data:www-data config/autoload/oscar-editable.yml

## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete

php bin/oscar.php check:privileges -n

## Note de version
php bin/oscar.php infos

php /var/application/bin/oscar-worker.php
