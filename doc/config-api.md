# Oscar API

Oscar dispose d'un API ouverte permettant d'interoger les données dans **Oscar** depuis une application tiers. 


## Accès à l'API

Pour activer cette fonctionnalité, vous devez vous rendre dans l'interface d'administration.

![Configuration de l'API oscar](images/oscar-api.png)

> Si l'option n'est pas disponible, vérifier votre version de Oscar (v2.11 "Macclane" minimum) et que vous disposer des privilèges suffisants

## Gérer les accès

L'inferface de gestion permet de voir les différentes clef d'accès disponibles dans Oscar : 

![Configuration de l'API oscar](images/oscar-api-list.png)

Cette interface permet de configurer les niveaux d'accès ainsi que les différents API disponibles.

![Configuration de l'API oscar](images/oscar-api-fiche.png)
 

## Accès

Les données seront accessibles via les URL : 


## Configurer les formatteurs

Les formatteurs permettent de personnaliser la sortie d'une ou plusieurs API.

La première étape necessite d'identifier les formateurs disponible de la fichier de configuration générale : 

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'api' => [
           // Personnalisation des formats disponibles dans l'API (persons)
           'formats' => [
               'persons' => [
                   'ADAJ' => \Oscar\Formatter\FormatPersonToJSONExample::class,
                   'Basic' => \Oscar\Formatter\PersonToJsonConnectorBasicFormatter::class
               ]
           ]
       ],
    ]
);
```

Vous pourrez ensuite

## Évolutions prévues

 - [ ] API affectation
 - [ ] API activité
 - [ ] API feuille de temps
 - [x] Prise en charge de *formateur* personnalisés
 - [x] Configuration d'un *formatteur* pour les données

