#!/bin/bash

# SCRIPT à LANCER depuis la racine du dépôt GIT
# - Purge des volumes

cp docker/.env.prod.dist ./.env
source .env

. docker/compose-init.sh

