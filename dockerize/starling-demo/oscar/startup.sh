#!/bin/sh
cd /var/OscarApp

git config --global --add safe.directory /var/OscarApp

# Composer
composer install

# PATH Access
touch config/autoload/oscar-editable.yml
touch logs/oscar.log
chmod 777 config/autoload/oscar-editable.yml
chmod 777 logs/oscar.log
chmod 777 logs

# Dossier de dépôt des documents
mkdir -p /var/OscarApp/data/documents/activity
mkdir -p /var/OscarApp/data/documents/public
mkdir -p /var/OscarApp/data/documents/signature
mkdir -p /var/OscarApp/data/documents/request

chmod 777 /var/OscarApp/data/documents/activity
chmod 777 /var/OscarApp/data/documents/public
chmod 777 /var/OscarApp/data/documents/signature
chmod 777 /var/OscarApp/data/documents/request

# Copie des fichiers de configuration par défaut
cp -u -p /opt/oscar_config/oscarworker.service config/
cp -u -p /opt/oscar_config/local.php config/autoload/local.php
cp -u -p /opt/oscar_config/unicaen-app.local.php config/autoload/unicaen-app.local.php
cp -u -p /opt/oscar_config/unicaen-auth.local.php config/autoload/unicaen-auth.local.php
cp -u -p /opt/oscar_config/unicaen-signature.local.php config/autoload/unicaen-signature.local.php

# On attends que Postgresql soit UP
until PGPASSWORD="azerty" psql -h "oscar_ripley_postgresql" -U "oscar" "oscar_dev" -c '\q'; do
  echo "Postgres is unavailable - sleeping"
  sleep 2
done

# DOCTRINE (MAJ Schema)
php vendor/bin/doctrine-module orm:schema-tool:update --force

### {{{DEMO
php bin/oscar.php check:privileges -n
php bin/oscar.php check:privileges -n
### DEMO}}}

chmod -R 777 data/DoctrineORMModule

### Lien symbolique unicaen-signature
mkdir -p public/unicaen
cd public/unicaen
ln -s ../../vendor/unicaen/signature/public/dist signature

exit 0
