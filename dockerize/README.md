# OSCAR with DOCKER

Des instances *ready-to-use* basées sur docker sont disponibles. Elles sont basées sur : 

 - Docker version 27.2.0, build 3ab4256

## Instances Oscar

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


