#!/bin/sh
cd /opt/OscarApp

# Composer
composer install

# PATH Access
chmod -R 777 data/DoctrineORMModule

# On attends que Postgresql soit UP
until PGPASSWORD="azerty" psql -h "£CONTAINER_POSTGRESQL" -U "oscar" "oscar_dev" -c '\q'; do
  echo "Postgres is unavailable - sleeping"
  sleep 2
done

# DOCTRINE (MAJ Schema)
php vendor/bin/doctrine-module orm:schema-tool:update --force

### {{{DEMO
php bin/oscar.php auth:sync install/demo/authentification.json
php bin/oscar.php organizations:sync-json install/demo/organizations.json
php bin/oscar.php persons:sync-json install/demo/persons.json
php bin/oscar.php activity:import-json -f install/demo/activity.json
php bin/oscar.php check:privileges -n
php bin/oscar.php check:privileges -n
### DEMO}}}

exit 0