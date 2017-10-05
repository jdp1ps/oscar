# Oscar en ligne de commande

Oscar dispose d'un accès terminal pour lancer automatiquement certaines tâche de maintenance


## Accès / Droits

### Créer un accès 'libre'

```bash
php public/index.php oscar auth:add
```

Une série d'invites permettront de renseigner les différents paramètres du compte à créer.

### Créer un administrateur

Se connecter en SSH sur la machine hébergeant Oscar et se rendre dans le dossier racine :

On commence par créer un utilisateur, puis lui attribuer le rôle *Administrateur* :

```bash
php public/index.php oscar auth:promote hulk Administrateur
```

### Lister les authentifications actives

On peut également lister les authentifications actives dans la BDD : 

```bash
php public/index.php oscar auth:promote hulk Administrateur
```

### Personnes (Person)

Rechercher la/les personne par connector ID : 

```
php public/index.php oscar persons:search:connector CONNECTOR VALUE
```

Par exemple : 

```bash
php public/index.php oscar persons:search:connector rest ed209
```

Recherche parmis les personnes celle qui a, pour le connector **rest** la valeur **ed209**.

## Moteur de recherche

Reconstruire l'index de recherche des activités :

```bash
php public/index.php oscar activity:search:build
```
