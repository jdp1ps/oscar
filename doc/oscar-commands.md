# Oscar en ligne de commande

Oscar dispose d'un accès terminal pour lancer automatiquement certaines tâche de maintenance


## Accès / Droits

### Créer un accès 'libre'

```bash
php public/index.php oscar auth:add logindemaurice maurice@domain.tld SuperMotDePasse Maurice
```

### Créer un administrateur

Se connecter en SSH sur la machine hébergeant Oscar et se rendre dans le dossier racine : 

On commence par créer un utilisateur :  

```bash
php public/index.php oscar auth:add hulk b.banner@domaine.tld PasColere "Bruce Banner"
```

Puis lon lui attribut le rôle "Administrateur" : 

```bash
php public/index.php oscar auth:promote hulk Administrateur 
``

## Moteur de recherche

Reconstruire l'index de recherche des activités : 

```bash
php public/index.php oscar activity:search:build
```









