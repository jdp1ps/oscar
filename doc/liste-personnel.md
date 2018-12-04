# Liste du personnel

## Présentation
Cette fonctionnalité propose à l'utilisateur de pouvoir visualiser la liste du personnel selon son niveau d'implication. 

L'option, quand elle est disponible, et accessible

## Configuration
La "profondeur" doit être configuré dans les fichiers de configuration Oscar :
 
```php
<?php
// config/autoload/local.php
return array(
    // ...
    'oscar' => [
        // ...
        'listPersonnel' => 0    
    ]
);
```

Les niveaux de visibilité se configure avec n entier : 

 - **0** : *(par défaut)* L'option n'est pas disponible
 - **1** : Propose uniquement la liste des N-1 directs
 - **2** : ... Et les membres des organisations ou l'utilisateur endosse un rôle marqué comme principale
 - **3** : ... Et les personnes impliquées dans les activités des organisations(avec un rôle principale)
 
## Cas particulier

Les utilisateurs disposant du privilège (rôle application) "Liste des personnes" ont automatiquement accès à cette option avec toutes les personnes référencées dans Oscar.