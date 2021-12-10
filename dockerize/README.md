# DOCKER

Le dossier `dev` contient tous les fichiers nécessaires à la construction des *containers Docker* pour une version développement de Oscar.

les containers créé :

 - `oscar_dev_postgresql_spartan` : Container Postgresql
 - `oscar_dev_spartan` : Oscar (Application)
 - `oscar_dev_elasticsearch_spartan` : L'index de recherche (Elastic Search)
 - `oscar_dev_adminer_spartan` : Une instance d'Adminer

# Build / Run

```
cd dev
# build
docker-composer build

# Run
docker-composer up -d
```

Accès à oscar (pour les opérations en ligne de commande) : 

```
docker-compose exec oscar_dev_spartan sh
```

Une fois dans l'instance : 

```
su
cd /var/OscarApp/oscar
```

Enjoy