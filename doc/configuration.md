# Configuration métier

La configuration métier de Oscar est placée dans le fichier `config/autoload/local.php`.


## Rappel
Techniquement, Oscar commence par charger la configuration par défaut présente dans le fichier `config/autoload/global.php`. **Attention**, ce fichier est versionné et permet de renseigner des valeurs par défaut, **il est susceptible d'être écrasé lors d'une mise à jour et ne doit donc pas être modifié**.

Notez que toutes les valeurs présentes dans ce fichier peuvent être surchargées par celles présentes dans le fichier `local.php`, dans ce sens, elles seront largement commentées.


## config/autoload/local.php

Ce fichier va contenir la configuration technique et métier de l'application. Les paramètres spécifiques à Oscar sont situés dans le clef **oscar**. Un fichier d'exemple `config/autoload/local.php.dist` est disponible dans le dépôt. Ce fichier propose les paramètres obligatoires ainsi que ceux facultatifs (commentés).

Vous pouvez utiliser la commande `php public/index.php oscar test:config` pour tester la conformité de votre configuration. Ce test, sans être exaustif, vous alertera si des paramètres sont manquants ou mal configurés.


## *oscar.paths* Emplacements des documents administratifs et activités

Les emplacements physiques pour le stockage des documents est situé dans la clef `oscar > paths` :

```php
<?php
return array(
    // (...)

    // Oscar
    'oscar' => [
        // (...)
        // Emplacement
        'paths' => [
            // Emplacement où sont stockés les documents Oscar
            'document_oscar' => dirname(__DIR__ . '/data/documents/oscar/',

            // Emplacement où sont stockés les documents administratifs Oscar
            'document_admin_oscar' => realpath(__DIR__) . '/../..//data/documents/administratifs/',

            // Emplacement de l'index de recherche (emplacement où Lucene écrit
            // sont index de recherche - un DOSSIER).
            'search_activity' => APP_DIR . '/data/luceneindex',

            // Modèle de feuille de temps
            'timesheet_modele' => realpath(__DIR__.'/../../data/timesheet_model.xls'),
        ],
    ],
);
```

## Formalisme du PFI

La règle de contrôle du PFI est précisée dans la clef `oscar > validation > pfi` (Il s'agit d'une expression régulière utilisée par Oscar pour valider les donnnées non-nulles saisies dans Oscar et détecter les PFI saisis dans le moteur de recherche pour proposer des résultats ciblés).

**Attention** : Comme indiqué, l'expression régulière étant utilisée par le moteur de recherche pour trouver une activité de recherche à partir d'un PFI, les expressions régulières trop permissives posent problème. Ex : Si vous utilisez une expression telle que `/.*/mi`, n'importe quelle recherche sera detectée comme étant un PFI, donc Oscar recherchera uniquement les activités ayant pour PFI la saisie de la recherche. Assurez vous donc que ce champ est correctement renseigné. 

```php
<?php
// config/autoload/local.php
return array(
    // (...)

    // Oscar
    'oscar' => [
        // (...)
        'validation' => [
            // ex: 209ED2024
            'pfi' => '/^[0-9]{3}[A-Z]{2,3}[0-9]{2,4}$/mi'
        ]
    ],
);
```

## Recherche des activités

Oscar propose 2 systèmes de recherche des activités, le premier est basé sur **Zend Lucene** (full PHP), l'autre est basé sur **Elastic Search** (Java).


### Elastic Search

Le deuxième système s'appuie sur le moteur de recherche **Elastic Search**. Ce système implique de disposer d'une instance d'**Elastic Search** accessible.

[Installation d'ElasticSearch sous Debian](./install-elasticsearch.md)

On indique ensuite à Oscar l'adresse de l'instance **Elastic Search** :

```php
// config/autoload/local.php
return array(
    // (...)

    // Oscar
    'oscar' => [
        'strategy' => [
            'activity' => [
                'search_engine' => [
                    'class' => \Oscar\Strategy\Search\ActivityElasticSearch::class,
                    'params' => [['127.0.0.1:9200']]
                ]
            ]
        ],
    ],
);
```

> la clef `params` prend bien pour valeur un tableau, contenant un tableau de chaîne avec la liste des noeuds Elastic Search disponibles, dans notre cas il n'y a qu'un seul et unique noeud.


### Zend Lucene

> Depuis la version 2.5.x, l'utilisation de Zend Lucene est dépréciée au profit de Elasticsearch.

Ce système (moins performant) repose sur la librairie **Lucene** de **Zend**. Il ne nécessite pas d'application tiers ou d'installation complémentaire.

```php
// config/autoload/local.php
return array(
    // (...)

    // Oscar
    'oscar' => [
        // (...)
        'strategy' => [
            'activity' => [
                'search_engine' => [
                    'class' => \Oscar\Strategy\Search\ActivityZendLucene::class,
                    'params' => [realpath(__DIR__) . '/../../data/luceneindex']
                ]
            ]
        ],
    ],
);
```


## Mailer

[Configuration du mailer](./mailer.md)


## Notifications

[Configurer les notifications](./notifications.md)

## Feuilles de temps

Configuration des options pour la feuille de temps

[Configurer les feuilles de temps](./timesheet.md)


## Numérotation automatique des activités de recherche

Pour modifier les numéros qui sont sous la forme **2018DRI00001**.

[Modifier le formalisme de la numérotation Oscar](./numerotation.md)


## Activité > Formulaire de saisie

Une option permet de masquer certains champs : 

 - Discipline
 - Frais de gestion

Pour cela, modifier le configuration Oscar dans le fichier `config/autoload/local.php` : 

```php
<?php
// ./config/autoload/local.php
return array(
    // ...

    // Oscar
    'oscar' => [
        
        'activity_hidden_fields' => [
            'disciplines', 
            'fraisDeGestion'],
    // ...
    ]
);
```

## Demande d'activité

Présentation et configuration des [Demande d'activité](./activity-request.md)

## Export des versements

Oscar propose différentes options pour régler la sortie CSV pour l'export des versements. Voici les options par défaut : 

```php
// config/autoload/local.php
return array(
    // ...
    'oscar' => [
    	// Exports
        'export' => [
        
        	// Export des versements
            'payments' => [
            
            	// Chaîne de séparation
                'separator' => '$$',
                
                // Rôles des personnes
                // intitulés des rôles des personnes séparés par une virgule
                'persons' => '',
                
                // Rôles des organisation
                // intitulés des rôles des organisations séparés par une virgule
                'organizations' => 'Composante responsable,Laboratoire,Financeur'
            ]
        ],
     // ..
     ]
);
```

Vous pouvez surcharger ces paramètres dans la configuration locale

