# Données Prod / Préprod / Test

## Privilèges et droits

Ces données sont gérées de façon particulière, voir la doc correpondante.


## Synchro Préprod > test

Les données de test sont situées dans la BDD oscar_test. **Paul Anthony** a
gracieusement mis en place un script qui se charge de répliquer le contenu de la
base de donnée de préproduction dans celle de test. Ce script est situé sur
**OscarPP** la >> `/usr/local/sbin/oscar_pp2test.sh`

Copier les données : 

```bash
# pour sauvegarder
pg_dump --clean --no-owner -U username -W -h serverdb server.tdl database > export.sql

# puis pour resaurer
psql -U username -W -h serverdb < export.sql
```

## Mise à jour du modèle

```bash
# Pour afficher les opérations qui vont être faites
php vendor/bin/doctrine-module orm:schema-tool:update --dump-sql

# Pour les faire...
php vendor/bin/doctrine-module orm:schema-tool:update --force
``̀


