#!/bin/bash

echo "OSCAR INITIALISATION...\n"

# On régle le safe.directory de GIT
git config --global --add safe.directory /var/application

# Installation des dépendances PHP
composer install --no-interaction

## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete

php bin/oscar.php check:privileges -n

mkdir -p public/unicaen
ln -sf vendor/unicaen/signature/public/dist public/unicaen/signature

## Note de version
php bin/oscar.php infos

php /var/application/bin/oscar-worker.php
