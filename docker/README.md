# OSCAR DOCKER

## Démo/Préprod

### Installation/build

Suivez la [procédure d'installation en production](./PROD.md)



## Développement

### Installation

Suivez la procédure de production,
puis dans le fichier **compose.yml**, décommentez les containers en fin de fichier : 
 - mailhog
 - vite (Si développement UI)
 - kibana (Si développement sur Elasticsearch)

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

Purger la BDD (Stopper l'application avant)

```bash
# Le volume avec les données est créé par docker - donc droit SU requis
sudo rm -Rf volumes/postgresql/*
```

Copier une base de données existante :

> Pensez à adapter l'emplacement du fichier SQL si besoin

```bash
pg_dump --clean --if-exists --no-owner -h HOST -U USER BASE > demo/default/postgresql/init/sql/install.sql
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


