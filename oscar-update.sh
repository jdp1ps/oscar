#!/bin/bash

COMPOSER=composer
PHP=php

echo "############################################### COMPOSER UPDATE"
## mise à jour des dépendances PHP
${COMPOSER} install

echo "############################################### MODEL UPDATE"
## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete


echo "############################################### CHECK PRIVILEGES"
## Mise à jour des privilèges
php bin/oscar.php check:privileges

echo "############################################### UPDATE INFOS"
php bin/oscar.php infos

