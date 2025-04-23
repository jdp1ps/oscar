#!/bin/bash

sudo docker compose exec app-php php vendor/bin/doctrine-module orm:schema-tool:update --force --complete

# Chargement des organisations
sudo docker compose exec app-php php bin/oscar.php organizations:sync-json install/demo/organizations.json

# Chargement des personnes
sudo docker compose exec app-php php bin/oscar.php persons:sync-json install/demo/persons.json

# Chargement des comptes
sudo docker compose exec app-php php bin/oscar.php auth:sync install/demo/authentification.json

# Chargement des activités
sudo docker compose exec app-php php bin/oscar.php activity:import-json -f install/demo/activity.json

# Réindexation des activités
sudo docker compose exec app-php php bin/oscar.php activity:search-rebuild

# Réindexation des activités
sudo docker compose exec app-php php bin/oscar.php check:privileges -n