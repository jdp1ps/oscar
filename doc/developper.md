# Participer au développement

## Installation via Docker

Suivez la procédure d'installation de la version DOCKER dev dans le dépôt idoine.
[Docker de développement Oscar](https://git.unicaen.fr/certic/oscar_devs_docker)

## Lancement

```bash
# Se placer dans le dépôt de Docker Dev Oscar
cd ~/path/to/docker_devs_oscar

# Lancer l'infra oscar
docker-compose up -d
```

# POI (UI)

La compilation des sources s'appis sur POI (v9) qui necessite une ancienne version de nodejs (Carbon).
Commencez par installer **nvm** [Installation de NVM](https://github.com/nvm-sh/nvm#installing-and-updating).

Une fois installé, installez **NodeJS Carbon** :

```bash
# Installation de NodeJS Carbon
nvm install v8.17.0
```

Puis depuis le dossier OSCAR, installez les outils de compilation :

```bash
npm install
```

l'executable POI sera disponible de la dossier `node_modules/.bin/poi`.

exemple :

```bash
nodejs node
```

# Se connecter au container applicatif

```bash
# Lancer l'infra oscar
docker exec oscardevmacclane bash
```
