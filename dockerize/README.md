# OSCAR with DOCKER

Pour installer une version *dockerisée* de Oscar, un dépôt dédié est disponible ici : https://git.unicaen.fr/oscar-staff/oscar-docker


## Instances Oscar (développeur)

> A mettre à jour

 - [**OSCAR RIPLEY** (Version Démo/Test)](./ripley-demo/README.md)    

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


