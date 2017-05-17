#

## Gestion des Accès


### Créer un accès 'libre'

```bash
php public/index.php oscar auth:add <login> <email> <pass> <displayname>
```


### Créer un administrateur

Se connecter en SSH sur la machine hébergeant Oscar et se rendre dans le dossier racine : 

On commence par créer un utilisateur :  

```bash
php public/index.php oscar auth:add <login> <email> <pass> <displayname>
```

Puis lon lui attribut le rôle "Administrateur" : 

```bash
php public/index.php oscar auth:promote <login> <role> 
```
