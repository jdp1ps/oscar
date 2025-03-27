#!/bin/bash

# On régle le safe.directory de GIT
git config --global --add safe.directory /var/application

# Installation des dépendances PHP
composer install --no-interaction

# Cache Doctrine
mkdir -p data/DoctrineORMModule/Proxy \
    && chown -R www-data:www-data data/DoctrineORMModule \
    && chmod 775 data/DoctrineORMModule

# Logs
mkdir -p logs \
  && chown www-data:www-data logs

# Document
mkdir -p docker/dev/volumes/documents \
  && chown www-data:www-data docker/dev/volumes/documents

# Oscar Config Editable
touch config/autoload/oscar-editable.yml \
  && chown www-data:www-data config/autoload/oscar-editable.yml

## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete

## Compte de test
php bin/oscar.php auth:sync install/demo/authentification.docker.json

## Note de version
php bin/oscar.php infos

# Lancement de APACHE
exec apache2-foreground