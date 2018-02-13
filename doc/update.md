# Procédure de mise à jour Oscar

## Sauvegarde

Faites une sauvegarde de l'application en copiant le dossier oscar : 

```bash
cp /path/to/oscar /tmp/oscar_save
```

Faites une sauvegarde de la BDD pour pouvoir revenir en arrière en cas de problème.

```bash
pg_dump --clean --no-owner -h [SERVER] -U [USER] [BASE] > /tmp/backup_oscar-prod.sql
```

## Mettre en maintenance

Commencez par mettre Oscar OFFLINE en créant un fichier MAINTENANCE à la racine de la copie :

```bash
touch MAINTENANCE
```

L'application doit maintenant proposer un écran d'accueil "Oscar est en maintenance".


## Mise à jour des sources

Mettre ensuite les sources à jour :

```bash
git pull
```

## Mise à jour des vendors (Librairies tiers)

```bash
# On supprime les anciennes
rm -Rf vendor

# On extrait les dernières
tar xvfz install/vendor.tar.gz

# On accorde les droits d'accès si besoin
chown -R <user>:<group> ./vendor/
```


## Mise à jour de la BDD

Il faut mettre à jour le schéma avec l'utilitaire intégré à **Doctrine** :

```bash
# Pour prévisualiser
php vendor/bin/doctrine-module orm:schema-tool:update --dump-sql

# Pour appliquer
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

## Vérifier la configuration

Selon la mise à jour, la configuration peut avoir été mise à jour, controler le fichier `./config/autoload/local.php.dist`. Si une mise à jour implique des changements plus spécifiques, ces derniers seront documentés en détails dans un document dédié.

# Mise à jour des privilèges

```bash
# Mise à jour des privileges
php public/index.php oscar patch checkPrivilegesJSON
```

> La séquence qui gère les ID n'est pas à jour (dans les données initiales). Connectez vous à la base Postegresql pour exécuter cette requète pour metre à jour les ID. `select setval('privilege_id_seq',(select max(id)+1 from privilege), false)`.


# Requètes de maintenance

> Les requêtes ici ne sont utilisées que dans le cadre du développement 

Supprimer les jointures activité > Personnes orphelines : 

```sql
DELETE FROM activityperson 
WHERE id IN (
  SELECT ap.id FROM activityperson ap 
  LEFT JOIN activity a 
  ON a.id = ap.activity_id WHERE a.id IS NULL
);
```

Supprimer les partenaires orphelins : 


```sql
DELETE FROM activityorganization WHERE id IN (SELECT ao.id FROM activityorganization ao LEFT JOIN activity a ON a.id = ao.activity_id WHERE a.id IS NULL);
```

```sql
DELETE FROM activitypayment WHERE id IN (SELECT j.id FROM activitypayment j LEFT JOIN activity a ON j.id = j.activity_id WHERE a.id IS NULL);
```


```sql
DELETE FROM contractdocument WHERE id IN (SELECT j.id FROM contractdocument j LEFT JOIN activity a ON j.id = j.grant_id WHERE a.id IS NULL);
```

```sql
UPDATE activity SET project_id = null WHERE id IN (SELECT a.id FROM activity a LEFT JOIN project p ON a.project_id = p.id WHERE p.id IS NULL);
```

```sql
DELETE FROM activitydate WHERE id IN (SELECT j.id FROM activitydate j LEFT JOIN activity a ON j.id = j.activity_id WHERE a.id IS NULL);
```