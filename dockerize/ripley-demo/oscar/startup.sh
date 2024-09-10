#!/bin/sh
cd /opt/OscarApp

# Composer
composer install

# PATH Access
chmod -R 777 data/DoctrineORMModule

touch config/autoload/oscar-editable.yml
touch logs/oscar.log
chmod 777 config/autoload/oscar-editable.yml
chmod 777 logs/oscar.log

mkdir -p /var/documents/activity
chmod 777 /var/documents/activity
mkdir -p /var/documents/public
chmod 777 /var/documents/public

cp -u -p /opt/oscar_config/local.php config/autoload/local.php
cp -u -p /opt/oscar_config/unicaen-app.local.php config/autoload/unicaen-app.local.php
cp -u -p /opt/oscar_config/unicaen-auth.local.php config/autoload/unicaen-auth.local.php

# On attends que Postgresql soit UP
until PGPASSWORD="azerty" psql -h "oscar_ripley_postgresql" -U "oscar" "oscar_dev" -c '\q'; do
  echo "Postgres is unavailable - sleeping"
  sleep 2
done

# DOCTRINE (MAJ Schema)
php vendor/bin/doctrine-module orm:schema-tool:update --force

### {{{DEMO
#php bin/oscar.php auth:sync install/demo/authentification.json
#php bin/oscar.php organizations:sync-json install/demo/organizations.json
#php bin/oscar.php persons:sync-json install/demo/persons.json
#php bin/oscar.php activity:import-json -f install/demo/activity.json
php bin/oscar.php check:privileges -n
php bin/oscar.php check:privileges -n
php bin/oscar.php persons:search-rebuild
php bin/oscar.php organizations:search-rebuild
php bin/oscar.php activity:search-rebuild
### DEMO}}}

exit 0
