# ADAJ

## Terminologie

Le *Mode ADAJ* repose uniquement sur une adaptation de la terminologie métier dans la partie *front* de l'application.

Pour l'activier, modifiez dans `config/autoload/local.php` le paramètre **translator** : 

```php
<?php

// ...

return array(
    'translator' => array(
        'locale' => 'fr_DJ',
    )
    // ... suite de la configuration
);
```

## Nom de l'application

Vous pouvez également modifier le nom de l'application en éditant le fichier `config/autoload/unicaen-app.local.php`

```php
<?php
// ...
$settings = array(
    
    // ...
    
    // Modificiation du nom de l'application
    'app_infos' => array(
        'nom'     => 'A.D.A.J',
        'desc'    => 'Administration Des Accords Juridiques'
    ),
    
    // ...
);

// ...
```

## Apparence

Oscar et Adaj étant une seule et même application, vous pouvez modifier le thème visuel afin d'aviter à vos utilisation de les confondre.

Pour cela, Vous pouvez vous rendre dans le menu `Administration > Configuration et maintenance > Options` et choisir le thème **adaj** dans la partie apparence (puis enregistrer).
