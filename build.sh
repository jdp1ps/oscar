#!/bin/bash

# Création de l'utilisateur POSTGRESQL pour les Tests
service postgresql start

sudo -u postgres psql -c "CREATE USER oscar_test WITH PASSWORD 'azerty';"
sudo -u postgres psql -c "CREATE DATABASE oscar_test;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE oscar_test TO oscar_test"

sudo -u postgres psql oscar_test < install/oscar-install.sql

php vendor/bin/doctrine-module orm:schema-tools:update --force
