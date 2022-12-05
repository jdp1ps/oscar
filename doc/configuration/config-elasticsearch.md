# Système de recherche

Oscar dispose d'un moteur de recherche qui indexe les données les plus usuelles : Activités, Organisation et Personnes.

Le système s'appuit sur **Elastic Search**. Vous devez commencer par installer **Elastic Search** : [Installation d'Elastic Search](./install-elasticsearch.md)

## Recherche des activité

```php
<?php
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

Vous pouvez reconstruire manuellement l'index de recherche avec la commande : 

```bash
# Reconstruction de l'index de recherche
php bin/oscar.php activity:search-rebuild
```



## Recherche des personnes

Depuis *Oscar 2.10 (Creed)*, la recherche des personnes utilise le système de recherche basé sur **ElasticSearch** afin d'obtenir des résultats plus pertinents.

Vous devez configurer la stratégie de recherche dans le fichier de configuration `config/autoload/local.php` : 

```php
<?php
// config/autoload/local.php
return array(
  // ...

  // Système de recherche
  'strategy' => [
    // ...
    'person' => [
      'search_engine' => [
        // Elasticsearch
        'class' => \Oscar\Strategy\Search\PersonElasticSearch::class,
        'params' => [['localhost:9200']]
      ]
    ]
  ]
);
```

> Une fois activée, pensez à lancer la commande de reconstruction d'index de recherche : 

```bash
# Reconstruction de l'index de recherche
php bin/oscar.php persons:search-rebuild
```

## Recherche des organisations

Depuis *Oscar 2.10 (Creed)*, la recherche des organisations utilise le système de recherche basé sur **ElasticSearch** afin d'obtenir des résultats plus pertinents.

Vous devez configurer la stratégie de recherche dans le fichier de configuration `config/autoload/local.php` : 

```php
<?php
// config/autoload/local.php
return array(
  // ...

  // Système de recherche
  'strategy' => [
    // ...
    'organization' => [
      'search_engine' => [
        // Elasticsearch
        'class' => \Oscar\Strategy\Search\OrganizationElasticSearch::class,
        'params' => [['localhost:9200']]
      ]
    ]
  ]
);
```

> Une fois activée, pensez à lancer la commande de reconstruction d'index de recherche : 

```bash
# Reconstruction de l'index de recherche
php bin/oscar.php organizations:search-rebuild
```

