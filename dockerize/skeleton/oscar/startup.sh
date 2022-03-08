#!/bin/sh
cd /opt/OscarApp

# Composer
composer install

# PATH Access
chmod -R 777 data/DoctrineORMModule

until PGPASSWORD="azerty" psql -h "£CONTAINER_POSTGRESQL" -U "oscar" "oscar_dev" -c '\q'; do
  >&2 echo "Postgres is unavailable - sleeping"
  sleep 1
done

# DOCTRINE (MAJ Schema)
php vendor/bin/doctrine-module orm:schema-tool:update --force

### DEMO DATAS
php bin/oscar.php auth:sync install/demo/authentification.json
php bin/oscar.php organizations:sync-json install/demo/organizations.json
php bin/oscar.php persons:sync-json install/demo/persons.json
php bin/oscar.php activity:import-json -f install/demo/activity.json
php bin/oscar.php check:privileges -n
php bin/oscar.php check:privileges -n

exit 0