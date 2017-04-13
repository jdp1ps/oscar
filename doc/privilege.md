# Gestion des privilèges dans Oscar©

La gestion des accès est gérée à 2 endroits : 

 - Le fichier de configuration oscar *module/Oscar/config/module.config.php* qui va gérer les *gardes* (Fonctionnalité Bjyauthorise étendue par UnicaenAuth)
 - La base de données
 

## Configurer des gardes avec UnicaenAuth
 
```php
'guards' => [
    UnicaenAuth\Guard\PrivilegeController::class => [
        // Exemple de régle
        [ 
            // Le controlleur
            'controller' =>  'Public',
            
            // La (ou les) méthode à controller
            'action' => ['index'],
            
            // Les rôles requis
            'roles' => [],
        ],
        // ...
```

Le contrôle des gardes permet également de vérifier si la personne authentifié dispose des privilèges qui vont bien. 

```php
'guards' => [
    UnicaenAuth\Guard\PrivilegeController::class => [
        // Exemple de régle
        [ 
            'controller' =>  'Public',
            'action' => ['index'],
            // Privilèges à avoir pour exécuter la méthode
            'privileges' => \Oscar\Provider\Privileges::PROJECT_INDEX
        ],
        // ...
```


## Créer un privilège

Les privilèges doivent : 
 - figurer en base de données (sans quoi une exception sera levée),
 - être déclarés sous la forme d'une constante dans le fichier `Oscar/src/Provider/Privileges`.
 
Cette procédure peut être automatisée : 
 
1. On commence par **créer le privilège dans la base de donnée**
2. On utilise *UnicaenCode* regénérer le contenu de la classe `Oscar\Provider\Privileges`.


## Sauvegarde des privilèges pour le déploiment

Comme les privilèges doivent être présents en BDD et dans la classe `Oscar\Provider\Privileges`, il est important de disposer d'une réplique des données suivantes :
 
 - **Rôles déclarés** (Objet `Oscar\Entity\Authentification`, table *authentification*),
 - **Famille de privilège**  (Objet `Oscar\Entity\CategoriePrivilege`, table *categorie_privilege*),
 - **Privilèges**  (Objet `Oscar\Entity\Privilege`, table *privilege*),
 - **Rôle -> Privilège**  table *authentification_role*),

La commande `php public/index.php oscar privilege dump` permet de générer les requêtes à exécuter pour créer/mettre à jour les *rôles*, les *privilèges* et l'association *Role-Privilege*. ex : 

```bash
php public/index.php oscar privilege dump > data/privileges.sql
```

## Restaurer / Mettre à jour les privilèges

```bash
psql -U username -h host -W < data/privileges.sql
```


## Créer un rôle

En BDD. Pour le moment, la liste des rôles est statique dans le *middleware*, il faudra automatiser cette partie à la manière des privilèges.


## Donner un privilège à un role

Via l'interface


## Filtre LDAP > Rôle

 - Groupe OSCAR : (memberOf=cn=projet_oscar,ou=groups,dc=unicaen,dc=fr)
 - DRI : (memberOf=cn=structure_dir-recherche-innov,ou=groups,dc=unicaen,dc=fr) 

