#!/bin/bash

echo "OSCAR INITIALISATION...\n"

# On régle le safe.directory de GIT
git config --global --add safe.directory /var/application


#mkdir -p /var/application/data/documents/activity
#mkdir -p /var/application/data/documents/public
#mkdir -p /var/application/data/documents/request
#mkdir -p /var/application/data/documents/pcru
#mkdir -p /var/application/logs
#mkdir -p /var/application/data/DoctrineORMModule/Proxy
#touch /var/application/config/autoload/oscar-editable.yml
#touch /var/application/logs/oscar.log
#
#chmod -R 777 /var/application/data/documents
#chmod -R 777 /var/application/data/DoctrineORMModule
#chmod -R 777 /var/application/logs
#chmod -R 777 /var/application/config/autoload/oscar-editable.yml


# Installation des dépendances PHP
composer install --no-interaction

## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete

php bin/oscar.php check:privileges -n

## Note de version
php bin/oscar.php infos

php /var/application/bin/oscar-worker.php
