# DOCKER

Création des images DOCKER.

```bash
. install.sh dev http://proxy.unicaen.fr
```

Soit

```
. install.sh <TAG> <PROXY>
```

les containers créé dans le dossier `local/<TAG>` :

 - `oscar_<TAG>` : Oscar (Application)
 - `oscar_<TAG>_postgresql` : Container Postgresql
 - `oscar_<TAG>_gearman` : Container Gearman
 - `oscar_<TAG>_elasticsearch` : L'index de recherche (Elastic Search)
 - `oscar_<TAG>_adminer` : Une instance d'Adminer

# Build / Run

```
cd local/dev

# build
docker-compose build

# Run
docker-compose up -d
```

Accès à oscar (pour les opérations en ligne de commande) : 

```
docker-compose exec oscar_dev sh
```

Une fois dans l'instance : 

```
su
```

Enjoy

# Dev

Stopper tous les containers

docker stop $(docker ps -a -q)

docker rm $(docker ps -a -q)

docker rmi $(docker images -q)


