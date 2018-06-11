# Administration OSCAR

## Base de donnée


### Copie de la base de données

Dupliquer la base de données (via le programme psql): 

```sql
CREATE DATABASE <copy> WITH TEMPLATE <original> OWNER <user>;
```


### Vider la base

Puis exécuter le script du fichier ./databases_maintenance/delete-database.sql : 

```bash
psql -U <USENAME> -h <HOST> -W -d <DATABASE> -a -f databases_maintenance/delete-database.sql
```


### Dump de la base dans un fichier

```bash
pg_dump --clean --no-owner -h <HOST> -U <USER> <DATABASE> > path/to/output.sql
```


### Commande usuelles POSTGRES

Ces commandes sont utilisables dans le client Postgresql (psql).


```psql
# Liste des bases de données
\l

# Selectionner une base de données
\c <DATABASE>
```
