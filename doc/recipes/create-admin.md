# Créer un compte Administrateur local

## Créer un compte local

Pour l'administration de l'application, vous pouvez créer un compte administrateur
dédié en utilisant l'utilitaire en ligne de commande.

Rendez-vous à la racine de l'application :

```bash
cd /var/OscarApp/oscar
```

Créer un compte d'authentification :

```bash
php bin/oscar.php auth:add
```

## Promouvoir un administrateur

Puis, on lui attribue le rôle "Administrateur" :

```bash
php bin/oscar.php auth:promote
```

Utiliser ensuite le navigateur pour vous rendre sur oscar et utiliser les identifiants/mot de passe que vous avez choisi pour vous connecter en tant qu'administrateur.

> Un administrateur peut nommer des administrateurs depuis le menu **Administration > 