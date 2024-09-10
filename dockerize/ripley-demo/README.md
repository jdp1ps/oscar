# OSCAR DOCKER [v Ripley / démo]

## Construction de l'application

```bash
cd dockerize/ripley-demo

cp .env.dist .env

# Edition de la configuration
nano .env

sudo docker compose build
```

## Lancement de l'application

```bash
sudo docker compose up
```

## Activer le compte administrateur

```bash
# Se connecter à oscar
sudo docker compose exec oscar_ripley exec sh

# Une fois connecté
su

# Création de l'utilisateur
php bin/oscar.php auth:add

# Promotion "Administrateur"
php bin/oscar.php auth:promote
```

