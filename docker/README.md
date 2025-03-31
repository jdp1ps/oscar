# OSCAR DOCKER (developpement)

Copie de travail utilisée pour le développement local

## Prérequis

> Testé sous Debian 12 "Bookorm" / Ubuntu

 - Docker version 28.0.2, build 0442a73
 - Git version 2.39.5

## Installation

```bash
git clone https://git.unicaen.fr/open-source/oscar.git
```

> Disponible à partir de la version *Starling*, donc pensez à switcher de branche (Mars 2025) : 
> ```bash
> git fetch && git checkout origin/starling
> ```

Copier la configuration initiale

```bash
cp config/autoload/local.docker.php.dist config/autoload/local.php 
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php 
cp config/autoload/unicaen-auth.local.php.dist config/autoload/unicaen-auth.local.php 
cp config/autoload/unicaen-signature.local.php.dist config/autoload/unicaen-signature.local.php

# On autorise l'écriture du dossier Elastic
chmod -R 777 docker/dev/volumes/elasticsearch 
```

Premier lancement : 

```bash
## TODO Création des volumes pour 
# - la BDD
# - Elastic
# - les documents
# Pour le moment, on fait ça à la main

mkdir -p docker/dev/volumes/database
mkdir -p docker/dev/volumes/elasticsearch
mkdir -p docker/dev/volumes/documents/activity
mkdir -p docker/dev/volumes/documents/pcru
mkdir -p docker/dev/volumes/documents/public
mkdir -p docker/dev/volumes/documents/request
mkdir -p docker/dev/volumes/documents/signature

chmod 775 -R docker/dev/volumes/*

# Build / up
docker compose -f compose.dev.yml up --build

# Connection à Oscar
docker compose -f compose.dev.yml exec oscar-dev-apache /bin/bash
```

Accès : http//localhost:8888
Identifiant : administrateur
Mdp : administrateur

## Architecture

Il y'a 8 containers : 
 - **oscar-dev-apache** : Version web (http://localhost:8888)
 - **oscar-dev-posgres** : Base de donnée (Port: 6543)
 - **oscar-dev-elasticsearch** : L'index de recherche (Ports non-exposé)
 - **oscar-dev-gearman** : Serveur de tâche (Ports non-exposé)
 - **oscar-dev-worker** : Tâche de fond Oscar
 - **oscar-dev-vite** : Serveur Vite  (http://localhost:5173)
 - **oscar-dev-kibana** : Kibana  (http://localhost:5601)
 - **oscar-dev-mailhog** : Un mail catcher (http://localhost:8025)
 - **oscar-dev-php** : Le moteur PHP (utilisé pour déclencher les commandes PHP)

   TODO/Idée d'évolution : 
 - Utiliser les fichiers .env avec Dotenc (???)
 - "Variabliser" dans un .env
 - Gérer les accès aux volumes (pour les documents) / Documenter


## Usage

### Commandes de base

Lancement/Arrêt de l'application

```bash
# lancement (Avec build, logs complets en stdout)
docker compose -f compose.dev.yml up --build

# lancement (Avec build, mode détaché)
docker compose -f compose.dev.yml up --build -d

# Arrêt (Si détaché)
docker compose -f compose.dev.yml down
```

Se connecter à l'application avec un bash (pour faire des commandes : bin/oscar.php, commandes composer, etc...)

```bash
docker compose -f compose.dev.yml exec oscar-dev-apache /bin/bash
```

Afficher les logs : 

```bash
# Logs complets (retirer -f pour ne pas avoir de temps réél)
docker compose log compose.dev.yml -f

# On peut préciser les containers à surveiller
# exemple : logs apache et postgresql
docker compose log compose.dev.yml -f oscar-dev-apache oscar-dev-postgres
```

### Autres commandes

Lancer des commandes sur le container Oscar : 

```bash
docker compose -f compose.dev.yml exec oscar-dev-php php bin/oscar.php
```

Build de l'UI : 
```bash
docker compose -f compose.dev.yml exec oscar-dev-vite yarn run build
```

Purger la BDD (Stopper l'application)

```bash
# Le volume avec les données est créé par docker - donc droit SU requis
sudo rm -Rf docker/dev/volumes/databases/oscar_dev_data
```

Lister les tâches en attentes sur Gearman

```bash
# TODO
```

### Accès à la base de données

#### DBeaver CE

 - Drivers : `Postgresql`
 - Hôte : `oscar-dev-postgres`
 - Identifiant : `oscar_devdev_user`
 - Mot de passe : `oscar_devdev_pass`
 - Base de données : `oscar_devdev_db`
 - Port : `6543`

> Peut être modifié dans le fichier **.env.docker.dev**

#### Développement UI (Vite/VueJS)

Dans `config/autoload/local.php` : 

```bash
<?php
// ...
return array(
    'oscar' => [
        // ...
        'vite' => [
            'mode' => 'dev', // par défaut 'prod'
            'src' => __DIR__ . '/../../ui',
            'dest' => __DIR__ . '/../../public/js/oscar/vite/dist',
            'base_url_dev' => 'http://127.0.0.1:5173',
            'base_url_prod' => '/js/oscar/vite/dist',
        ],
        // ...
    ]
)
```

> Le *container* Vite installe automatiquement la dernière version de Node/NPM. à voir si cela nous bloque à un moment et nous oblige à fixer la version 


