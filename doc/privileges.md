# Privilèges

## Présentation

Les privilèges permettent d'accorder à un rôle précis l'accès à une fonctionnalité. L'interface pour les gérer est disponible dans le menu `Administration > Gestion des droits`

![Interface de gestion des privilèges](images/ui-privileges.png)

La gestion des privilèges est gérée en base de donnée via les tables : 

 - **privilege**, qui liste les privilèges disponibles
 - **categorie_privilege** qui permet d'organiser ces privilèges en catégorie
 - **role_privilege** qui permet d'affecter un privilège à un rôle
 
## Mise à jour

Lorsque qu'une nouvelle fonctionnalité implique un nouveau privilège, il n'est pas présent dans la table privilège, la fonctionnalité n'est donc pas disponible dans l'interface.

Pour ajouter ce privilège, il faut **mettre à jour les données de la table privilege**.

Une procédure automatique existe pour effectuer la mise à jour des privilèges.

1. Se connecter à la machine Oscar en SSH
2. Se rendre à la racine de oscar
3. Taper la commande `php public/index.php oscar patch checkPrivilegesJSON`

L'invite en ligne de commande vous indiquera les opérations qui seront réalisée

## Erreur connue

Il est possible (si les privilèges ont été gérés manuellement), que l'indexation postgesql ne soit pas à jour. Pour le vérifier, se connecter à la base de donnée posgresql : 

```bash
psql -h <url du serveur postgresl> -U <utilisateur> <base de donnée>
```

On commence par afficher le plus grand ID de la table privilege : 

```sql
SELECT MAX(id) FROM privilege;
```

Ensuite on vérifie que la sequence est à jour, elle doit retourner une valeur supérieur à celle obtenue avec la requête précédente.

```sql
SELECT last_value FROM privilege_id_seq;
```

Si ça n'est pas le cas, il faut mettre à jour la sequence avec cette requête, dans cet exemple le `XX` correspond à la valeur du dernier ID incrémenté de 1 : 

```sql
ALTER SEQUENCE privilege_id_seq RESTART WITH XX;
```