# Oscar en ligne de commande

Oscar dispose d'un accès terminal pour lancer automatiquement certaines tâche de maintenance.

La liste des commandes est disponible en tappant `php bin/oscar.php`

Toutes les commandes ne sont pas forcement documentées ici


## Accès / Droits

 - **Créer un compte** : `php bin/oscar.php auth:add`
 - **Modifier un mot de passe (mot de passe en 'dur')** : `php bin/oscar.php auth:pass [LOGIN]`  
 - **Modifier en forçant le mot de passe LDAP** : `php bin/oscar.php auth:pass [LOGIN] --ldap`
 - **Promouvoir un compte avec un rôle (ex: Administrateur)** : `php bin/oscar.php auth:promote [LOGIN]`
 - **Liste des comptes d'authentification actifs** : `php bin/oscar.php auth:list`
 - **Informations détaillées sur un compte** : `php bin/oscar.php auth:info [LOGIN]`

### Personnes (Person)

 - **Recherche une personne** : `php bin/oscar.php persons:search [TERME]`
 - **Afficher les différents rôles d'une personne** : `php bin/oscar.php person:roles [LOGIN]`
 - **Reconsruire l'index de recherche des personnes** : `php bin/oscar.php persons:search-rebuild`
