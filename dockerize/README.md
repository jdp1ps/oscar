# DOCKER

Création des images DOCKER.

```shell
. install.sh dev http://proxy.unicaen.fr:3128
# ou ""
. install.sh dev ""
```

Soit

```shell
. install.sh <TAG> <PROXY>
# <PROXY> mettre "" pour une valeur de proxy par défaut vide
```

les containers créé dans le dossier `local/<TAG>` :

 - `oscar_<TAG>` : Oscar (Application)
 - `oscar_<TAG>_postgresql` : Container Postgresql
 - `oscar_<TAG>_gearman` : Container Gearman
 - `oscar_<TAG>_elasticsearch` : L'index de recherche (Elastic Search)
 - `oscar_<TAG>_adminer` : Une instance d'Adminer
 - `oscar_<TAG>_mailhog` : Une instance de Mailhog

# Build / Run

```shell
# cd local/<tag>
cd local/dev

# build
docker-compose build

# Run
docker-compose up -d
```

Accès à oscar (pour les opérations en ligne de commande) : 

```shell
#docker-compose exec oscar_<TAG> sh
docker-compose exec oscar_dev sh
```

Une fois dans l'instance : 

```
su
```

Enjoy

# Dev

```bash
# Copier les données de prod dans la VM
pg_dump --clean --no-owner -h url.postgresql.ext -U user_login database > local/dev/postgresql/install-oscar.sql
```

```shell
#Stopper tous les containers
docker stop $(docker ps -a -q)
```

```shell
#Supprimer les containes (éteints ou non)
docker rm $(docker ps -a -q)
```

```shell
#Supprimer les images Docker
docker rmi $(docker images -q)
```

## Proxy

> **PROXY** :
> Pour le service *Docker*, il faut configurer le proxy dans le fichier `/etc/systemd/system/docker.service.d/http-proxy.conf`


