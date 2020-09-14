# Extraction des activités

Ces fonctionnalités sont disponibles depuis l'écran de recherche des activités, elles permettent d'enrichir la fonctionnalité d'exportation en permettant de **configurer la mise en forme** de certaines données ainsi que la possibilité de définir des **champs calculés**.

## Format des types de données

Les options sont configurables depuis le menu **Administration > Configuration et maintenance > Options**

## Colonnes calculées

Les colonnes calculées permettent de configurer des colonnes supplémentaires. Les configurer implique des connaissances en PHP car elles reposent sur l'utilisation d'un *closure* qui va reçevoir en paramètre chaque activité à exporter pour réaliser un traitement dessus.


### Exemple : Configuration utilisée à Caen

Cette configuration permet d'ajouter une colonne *Multilaboratoire* qui affichera le code du seul labo ou "Multilaboratoire" si l'activité a plusieurs laboratoires impliqués et ajoute une colonne avenant qui indique si l'activité est un avenant (en se basant sur la présente textuelle du terme avenant dans l'intitulé) : 




```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'export' => [
            // ------------------------------------------------------------
            //
            // CHAMPS ICI
            //
            // ------------------------------------------------------------
        ]
    ]
);
```

#### Champ calculé : Codes labo solo ou "Multi"

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'export' => [
            // ...
            'computedFields' => [
                'laboratoriesCodes' => [
                    'label' => 'Laboratoire actif (code)',
                    'handler' => function( \Oscar\Entity\Activity $activity ){
                        $labos = [];
                        /** @var \Oscar\Entity\ActivityOrganization $activityOrganization */
                        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
                            if( $activityOrganization->getRole() == "Laboratoire" 
                                        && !$activityOrganization->isOutOfDate() 
                                        && !$activityOrganization->getOrganization()->isClose() ){
                                $labos[] = (string)$activityOrganization->getOrganization()->getCode() ?? 'N.D';
                            }
                        }
                        return count($labos) > 1 ? "Multilaboratoire" : implode(', ', $labos);
                    }
                ],
            ]
        ]
    ]
);
```

#### Champ calculé : Nom labo solo ou "Multi"

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'export' => [
            // ...
            'computedFields' => [
                'laboratoriesNames' => [
                    'label' => 'Laboratoire actif (nom)',
                    'handler' => function( \Oscar\Entity\Activity $activity ){
                        $labos = [];
                        /** @var \Oscar\Entity\ActivityOrganization $activityOrganization */
                        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
                            if( $activityOrganization->getRole() == "Laboratoire" && !$activityOrganization->isOutOfDate() && !$activityOrganization->getOrganization()->isClose() ){
                                $labos[] = $activityOrganization->getOrganization()->getShortName() ?? $activityOrganization->getOrganization()->getFullName();
                            }
                        }
                        return count($labos) > 1 ? "Multilaboratoire" : implode(', ', $labos);
                    }
                ],
            ]
        ]
    ]
);
```

#### Champ calculé : Avenant

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'export' => [
            'avenant' => [
                'label' => 'Avenant',
                'handler' => function( \Oscar\Entity\Activity $activity ){
                    return strpos(strtolower($activity->getLabel()), 'avenant') >= 0 ? "O" : "N";
                }
            ],
        ]
    ]
);
```

