# OSCAR DOCKER (developpement)

Copie de travail utilisée pour le développement local

## Prérequis

> Testé sous Debian 12 "Booworm" / Ubuntu

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
# Copie des fichiers de config "Dockerisés"
cp config/autoload/local.docker.php.dist config/autoload/local.php 
cp config/autoload/unicaen-app.local.php.docker.dist config/autoload/unicaen-app.local.php 
cp config/autoload/unicaen-auth.local.php.docker.dist config/autoload/unicaen-auth.local.php 
cp config/autoload/unicaen-signature.local.php.dist config/autoload/unicaen-signature.local.php
```

Puis le **.env** qui centralise toute la configuration : 

```bash
cp .env.dist .env
```

> La configuration de base dans le .env est fonctionnelle pour la version développement


Initialisation des volumes : 

```bash
# script qui va créer les volumes par défaut basé sur la configuration
# et copier les templates
. compose-init.sh
```

On lance les containers + build

```bash
# Build / up
docker compose up --build
```

Une fois les containers lancés, on met à jour le modèle de données

```bash
# Mise à jour du modèle
docker compose exec app-php php vendor/bin/doctrine-module orm:schema-tool:update --complete --force
```

Si besoin on charge les données de base de la démo :

```bash
. docker/docker-sync-demos-datas.sh
```

C'est fini

Accès WEB : http://localhost:8888 (identifiant: administrateur, Mot de passe: administrateur)
Accès BDD : localhost:6543 (Bdd: oscar_dev_db, oscar_dev_user/oscar_dev_pass)
Accès Mailhog (voir les mails envoyés par L'application) : http://localhost:8025
Accès Kibana (Faire du dev sur Elasticsearch) : http://localhost:5601

## Architecture

Il y'a 8 containers : 
 - **app-apache** : Version web (http://localhost:8888)
 - **app-posgres** : Base de donnée (Bdd: oscar_dev_db, Port: 6543, oscar_dev_user/oscar_dev_pass)
 - **app-elasticsearch** : L'index de recherche (Ports non-exposé)
 - **app-gearman** : Serveur de tâche (Ports non-exposé)
 - **app-worker** : Tâche de fond Oscar
 - **app-vite** : Serveur Vite  (http://localhost:5173)
 - **app-kibana** : Kibana  (http://localhost:5601)
 - **app-mailhog** : Un mail catcher (http://localhost:8025)
 - **app-php** : Le moteur PHP (utilisé pour déclencher les commandes PHP)




## Usage

### Commandes de base

Lancement/Arrêt de l'application

```bash
# lancement (Avec build, logs complets en stdout)
docker compose up --build

# lancement (Avec build, mode détaché)
docker compose up --build -d

# Arrêt (Si détaché)
docker compose down
```

Lancer des commandes sur l'application PHP

```bash
# Commandes OSCAR
docker compose exec app-worker php bin/oscar.php

# Mise à jour du modèle
docker compose exec app-worker php vendor/bin/doctrine-module orm:schema-tool:update --force --complete
```


### Autres commandes

Lancer des commandes sur le container Oscar : 

```bash
docker compose exec app-php php bin/oscar.php
```

Build de l'UI : 
```bash
docker compose exec app-vite yarn run build
```

Purger la BDD (Stopper l'application)

```bash
# Le volume avec les données est créé par docker - donc droit SU requis
sudo rm -Rf volumes/postgresql/*
```

Lister les tâches en attentes sur Gearman

```bash
# TODO
```

### Accès à la base de données

#### DBeaver CE

 - Drivers : `Postgresql`
 - Hôte : `app-postgres`
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


