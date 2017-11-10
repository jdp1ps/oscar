# Procédure de mise à jour Oscar

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

## Mise à jour de la BDD

Il faut mettre à jour le schéma avec l'utilitaire intégré à **Doctrine** :

```bash
# Pour prévisualiser
php vendor/bin/orm:schema-tool:update --dump-sql

# Pour appliquer
php vendor/bin/orm:schema-tool:update --force
```

# Mise à jour des privilèges

```bash
# Mise à jour des privileges
php public/index.php oscar patch checkPrivilegesJSON
```

> La séquence qui gère les ID n'est pas à jour (dans les données initiales). Connectez vous à la base Postegresql pour exécuter cette requète pour metre à jour les ID. `select setval('privilege_id_seq',(select max(id)+1 from privilege), false)`.