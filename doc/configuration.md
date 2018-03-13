# Configuration métier

La configuration métier de Oscar est placé dans le fichier `config/autoload/local.php`. 


## Rappel
Techniquement, Oscar commence par charger la configuration par défaut présente dans le fichier `config/autoload/global.php`. **Attention**, ce fichier est versionné et permet de renseigner des valeurs par défaut, **il est susceptible d'être écrasé lors d'une mise à jour et ne dois donc pas être modifié**.

Notez que toutes les valeurs présentes dans ce fichier peuvent être surchargées par celles présentes dans le fichier `local.php`, dans ce sens, elles seront largement commentées.


## config/autoload/local.php

Ce fichier va contenir la configuration technique et métier de l'application. Les paramètres spécifiques à Oscar sont situés dans le clef **oscar**. Un fichier d'exemple `config/autoload/local.php.dist` est disponible dans le dépôt. Ce fichier propose les paramètres obligatoires ainsi que ceux facultatifs (commentés).


## Emplacements des documents administratifs et activités

Les emplacements physiques pour le stoquage des documents est situé dans la clef `oscar > paths` : 

```php
return array(
    // (...)

    // Oscar
    'oscar' => [
        // (...)
        // Emplacement
        'paths' => [
            // Emplacement où sont stoqués les documents Oscar
            'document_oscar' => dirname(__DIR__ . '/data/documents/oscar/',

            // Emplacement où sont stoqués les documents administratifs Oscar
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

La règle de contrôle du PFI est précisée dans la clef `oscar > validation > pfi` (Il s'agit d'une expression régulière utilisé par Oscar pour valider les donnnées non-nulles saisie dans Oscar).

```php
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

Oscar propose 2 système de recherche des activités, le premier est basé sur **Zend Lucene** (full PHP), l'autre est basé sur **Elastic Search** (Java).


### Zend Lucene

Ce système (moins performant) repose sur la librairie **Lucene** de **Zend**. Il ne necessite pas d'application tiers ou d'installations complémentaires.

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

### Elastic Search

Le deuxième système s'appuis sur le moteur de recherche **Elastic Search**. Ce système implique de disposer d'une instance d'**Elastic Search** accessible.

#### Installation d'Elastic Search : Debian

Vous pouvez installer **Elastice Search** en tant que service à partir des dépôts Debian officiels : 

```bash
# Installation
$ sudo apt-get install elasticsearch

# Configuration en service
$ sudo update-rc.d elasticsearch defaults 95 10

# Lancement du service
$ sudo /etc/init.d/elasticsearch start
```


#### Configurer Oscar

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

> la clef `params` prends bien pour valeur un tableau, contenant un tableau de chaîne avec la liste des noeuds Elastic Search disponibles, dans notre cas il n'y a qu'un seul et unique noeud.

> Lors de la reconstruction de l'index en ligne de commande, assurez-vous de ne pas l'executer en tant que ROOT.