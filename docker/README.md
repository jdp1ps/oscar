# Installation OSCAR avec Docker

> Note : Si vous êtes développeur/testeur, une version plus spécifique (locale) est disponible ici [Oscar Docker Dev](./DEV.md)

## Etape 1 : Récupération des sources

```bash
# Récupération du dépôt
git clone https://git.unicaen.fr/open-source/oscar.git
cd oscar

# Copie de la configuration par défaut
cp docker/.env.prod.dist .env

# 
cp docker/compose.prod.yml ./compose.yml
```

## Etape 2 : Proxy

Si vous n'êtes pas derrière un proxy, passez cette étape

```bash
# MAJ du Proxy
sed -i 's%^HTTP_PROXY=.*%HTTP_PROXY=http://votre-proxy:3128%' .env
```

## Etape 3 : Volumes

Vous devez créer tous les volumes de l'application pour l'archivage des fichiers, écriture des logs, configuration personnalisées, etc...

Un script permet de vous simplifier cette étape (en se basant sur votre .env) :

```bash
. docker/compose-init.sh
```

Si vous êtes en démo, tout est prêt.


## Etape 4 : Proxypass de l'hôte > container oscar

Si vous êtes en local, passez cette étape

```bash
# Activation du mod Proxy
sudo  a2enmod proxy proxy_http

# Un exemple de Vhost
sudo cp docker/host.apache.proxy.conf /etc/apache2/sites-available/000-default.conf

# Renseigner votre domaine (ou avec votre éditeur préféré)
sed -i 's%^oscar-host-name%VOTRE-NOM-DE-DOMAINE.EXT%' /etc/apache2/sites-available/000-default.conf

# Reboot apache 
sudo systemctl restart apache2
```


## Etape 5 : Lancement

```bash
sudo docker compose up
```

Vous avez un oscar fonctionnel à cette étape

- En local : http://localhost:8888
- Avec Proxypass : http://VOTRE-NOM-DE-DOMAINE.EXT

## Etape 6 : Données de démonstration

```bash
. docker/docker-sync-demos-datas.sh
```

Accès : administrateur/administrateur