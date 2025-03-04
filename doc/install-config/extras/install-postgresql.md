# Installation Postgresql

## Serveur Postgresql

```bash
# Installation du serveur Postgresql
apt-get install postgresql-server
```


## Vérifier l'accès

On se connecte à la base de données Postgresql :
```
psql
postgres-# \conninfo
résultat :
Vous êtes connecté à la base de données « postgres » en tant qu'utilisateur « postgres » via le socket dans « /var/run/postgresql » via le port « 5432 ».
```
CTRL D (deux fois)
```
postgres=# \q
xxx@zzzz:~$ déconnexion
```

Vérification du bon fonctionnement
```
sudo -i -u postgres
```
CTRL D (pour quitter)

## Créer un utilisateur/une base de données

```bash
su - postgres
psql
```

```sql
CREATE USER oscar WITH PASSWORD 'azerty';
CREATE DATABASE oscar_dev;
GRANT ALL PRIVILEGES ON DATABASE oscar_dev to oscar;
\q
```

## Charger des données

```bash
psql -h localhost -U oscar oscar_dev < install/oscar-install.sql
```