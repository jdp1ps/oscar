#!/bin/sh
echo "COMPOSER INSTALL"
cd /opt/OscarApp

# Composer
composer install

# DOCTRINE (MAJ Schema)
php vendor/bin/doctrine-module orm:schema-tool:update --force

# PATH Access
chmod -R 777 data/DoctrineORMModule

### DEMO DATAS
php bin/oscar.php auth:sync install/demo/authentification.json
php bin/oscar.php organizations:sync-json install/demo/organizations.json
php bin/oscar.php persons:sync-json install/demo/organizations.json
php bin/oscar.php persons:sync-json install/demo/persons.json

exit 0