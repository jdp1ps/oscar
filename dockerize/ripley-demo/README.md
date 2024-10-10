# OSCAR DOCKER [v Ripley / démo]

## Prérequis

Les instructions ci-après ont été testées sur la version **27.x** de docker.

```bash
docker version
```

```text
Client: Docker Engine - Community
 Version:           27.2.1
 API version:       1.47
 Go version:        go1.22.7
 Git commit:        9e34c9b
 Built:             Fri Sep  6 12:08:06 2024
 OS/Arch:           linux/amd64
 Context:           default

Server: Docker Engine - Community
 Engine:
  Version:          27.2.1
  API version:      1.47 (minimum version 1.24)
  Go version:       go1.22.7
  Git commit:       8b539b8
  Built:            Fri Sep  6 12:08:06 2024
  OS/Arch:          linux/amd64
  Experimental:     false
 containerd:
  Version:          1.7.21
  GitCommit:        472731909fa34bd7bc9c087e4c27943f9835f111
 runc:
  Version:          1.1.13
  GitCommit:        v1.1.13-0-g58aa920
 docker-init:
  Version:          0.19.0
  GitCommit:        de40ad0
```

## Récupération des sources
```bash
git clone https://git.unicaen.fr/open-source/oscar.git
cd oscar
```

## Copie des fichiers de base de configuration

Copiez les fichiers de configurations de base

```bash
cp dockerize/ripley-demo/oscar/oscar/config/autoload/local.php config/autoload/
cp dockerize/ripley-demo/oscar/oscar/config/autoload/unicaen-app.local.php config/autoload/
cp dockerize/ripley-demo/oscar/oscar/config/autoload/unicaen-auth.local.php config/autoload/
```

## Construction de l'application

Le *docker-compose.yml* est disponible dans les sources de l'application : `dockerize/ripley-demo`

```bash
cd dockerize/ripley-demo
```

### Configuration

La configuration par défaut peut fonctionner directement. D'autres paramètres y seront ajouté progressivement. Principalement des paramètres métiers déstiner à tester des fonctionnalités impliquant des applications tiers.

```bash
# Copie de la configuration par défaut
cp .env.dist .env

# Edition de la configuration
nano .env
```

### Build des images

```bash
# Construction des images
sudo docker compose build
```

## Lancement de l'application

```bash
sudo docker compose up
```

Accès : http://localhost:8181

## Activer le compte administrateur

```bash
# Se connecter à oscar
sudo docker compose exec oscar_ripley sh

# Une fois connecté
su

# Création de l'utilisateur
php bin/oscar.php auth:add

# Promotion "Administrateur"
php bin/oscar.php auth:promote
```


