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
cp config/autoload/local.php.dist config/autoload/local.php 
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php 
cp config/autoload/unicaen-auth.local.php.dist config/autoload/unicaen-auth.local.php 
cp config/autoload/unicaen-signature.local.php.dist config/autoload/unicaen-signature.local.php 
```

Premier lancement : 

```bash
# Build / up
docker compose -f compose.dev.yml up --build

# Connection à Oscar
docker compose -f compose.dev.yml exec oscar-dev-apache /bin/bash

# Depuis oscar
. oscar-update.sh
```

## Architecture

Il y'a X containers : 
 - **oscar-dev-apache** : L'application principale (Port: 8888)
 - **oscar-dev-posgres** : Base de donnée (Port: 6543)
 - **oscar-dev-elasticsearch** : L'index de recherche
 - **oscar-dev-gearman** : Serveur de tâche
 - **oscar-dev-worker** : Executeur de tâche Oscar

TODO : 
 - **oscar-dev-kibana** : Pour les tests Elasticsearch via l'UI
 - **oscar-dev-mailhog** : Pour les mails
 - **oscar-dev-vite** : Pour VITE
 - **oscar-dev-php** : Regrouper le Worker dans un PHPFPM, et brancher le apache dessus (à voir)
 - Utiliser les fichiers .env avec Dotenc (???)


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


