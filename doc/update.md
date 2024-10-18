# Procédure de mise à jour Oscar

Avant de déclencher une mise à jour, pensez là lire les patchnotes des versions situées entre votre version actuelle et la version qui sera installée.

## Sauvegarde

Faites une sauvegarde de l'application en copiant le dossier oscar :

```bash
cp -r /path/to/oscar /tmp/oscar_save
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
git fetch
git pull
```
Si des conflits de fichiers surviennent, vous pouvez utiliser la commande

```bash
git reset --hard origin/master
```


## Mise à jour des vendors (Librairies tiers)

En utilisant **composer** :

```bash
composer install
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

> Selon la mise à jour, la configuration peut avoir été mise à jour, contrôler le fichier `./config/autoload/local.php.dist`. Si une mise à jour implique des changements plus spécifiques, ces derniers seront documentés en détails dans un document dédié.

Vous pouvez utiliser la commande de diagnostique `php bin/oscar.php check:config`, elle va vérifier les éléments principaux de la configuration pour s'assurer que rien ne manque :

```bash
# Test de la configuration
php bin/oscar.php check:config
```

## Mise à jour des privilèges

Lors d'ajout de fonctionnalité, les privilèges sont généralement enrichis, pour les mettre à jour :

```bash
# Mise à jour des privileges
php bin/oscar.php check:privileges
```

Pour des raisons techniques, cette commande doit être exécutée plusieurs fois jusqu'à obtenir un message :

**Les privilèges sont à jour**


## Remise en service

```bash
# Remettre en production
rm MAINTENANCE
```

## Recalculer les séquences de numérotation automatiquement

Après des dumps/backups de données, il est souhaitable de lancer un recaclule de la séquence des IDS des tables :

```bash
# Recalculer les séquences d'ID
php bin/oscar.php check:sequences-num
```





# ANNEXE : Requêtes de maintenance

> Les requêtes ici ne sont utilisées que dans le cadre du développement, **NE PAS UTILISER EN PRODUCTION**
> 
> ELLES SEMBLENT DANS CERTAINS CAS SUPPRIMER TROP DE DONNEES, AUDIT EN COURS

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
DELETE FROM activityorganization
  WHERE id IN (
    SELECT ao.id
      FROM activityorganization ao
      LEFT JOIN activity a ON a.id = ao.activity_id
      WHERE a.id IS NULL
  );
```

```sql
DELETE FROM activitypayment
  WHERE id IN (
    SELECT j.id
      FROM activitypayment j
      LEFT JOIN activity a ON j.id = j.activity_id
      WHERE a.id IS NULL
  );
```


```sql
-- Suppression des documents orphelins
DELETE FROM contractdocument WHERE grant_id IS NULL;
```

```sql
-- Suppression des jalons orphelins
DELETE FROM activitydate WHERE activity_id IS NULL);
```
