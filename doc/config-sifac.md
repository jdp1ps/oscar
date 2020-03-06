# Affichage des dépenses (SIFAC)

Oscar permet de synchroniser depuis le SI(Système d'Information) **les dépenses** effectuées dans le cadre des activités de recherche.

## SIFAC

### Connector SIFAC (OCI)

Le premier connecteur disponible est une connection directe à la base de donnée.

Les informations de connection doivent être renseignées dans le fichier de configuration Oscar `config/autoload/local.php` : 

```php
<?php
// config/autoload/local.php
return array(
    // ...
    'oscar' => [
        // ...
        'connectors' => [
            // ...
            'spent' => [
                'sifac' => [
                    'class'     => \Oscar\Connector\ConnectorSpentSifacOCI::class,
                    'params'    => [
                        'username'  => '<SIFAC_DB_USER>',
                        'password'  => '<SIFAC_DB_PASS>',
                        'SID'  => '<SIFAC_DB_SID>',
                        'port'      => '<SIFAC_DB_PORT>',
                        'hostname'  =>'<SIFAC_DB_HOST>',
                        'spent_query' => \Oscar\Connector\ConnectorSpentSifacOCI::SPENT_QUERY
                    ]
                ]
            ]
         ],
    ]
);
```

## Filtrer le compte général

## Plan comptable et qualification des masses