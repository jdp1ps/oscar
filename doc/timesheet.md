# Feuille de temps


## Déclaration en heures/pourcentage

L'option `(boolean)declarationsHours` permet de configurer la mode de déclaration. Sur `true`, la déclaration affichera des heures, sinon la déclaration sera en pourcentage.

```php
<?php
return array(
    'oscar' => [
        // ...
        
        // Mode de déclaration (pourcentage/heure)
        'declarationsHours' => false,
    ]
);
```

L'option `(boolean)declarationsHoursOverwriteByAuth` permet d'autoriser les utilisateurs à choisir dans le menu préférences le mode d'affichage.

```php
<?php
return array(
    'oscar' => [
        // ...
        
        // Mode de déclaration (pourcentage/heure)
        'declarationsHoursOverwriteByAuth' => true,
    ]
);
```

## Durée par défaut

La clef `declarationsDurations` permet de configurer les durées pour les déclarations, ces valeurs seront utilisées pour l'affichage en pourcentage, et seront utilisé pour le remplissage des journées avec le choix **remplir**.

```php
<?php
return array(
    'oscar' => [   
        // ...
        
        // Durées utilisées pour les contrôles
        'declarationsDurations' => [
            
            // Durée des journées
            'dayLength'     => [
                
                // Durée d'une journée par défaut
                'value' => 7.5,
                
                // Durée maximum
                // Note : Oscar applique un contrôle à partir de
                // cette valeur, si elle est dépassée, la feuille
                // de temps ne pourra pas être envoyée.
                'max' => 10.0,
                
                // Durée spécifique à la journée
                'days' => [
                    '1' => 7.5, // Lundi
                    '2' => 7.5, // Mardi
                    '3' => 7.5, // Mercredi
                    '4' => 7.5, // Jeudi
                    '5' => 7.5, // Vendredi
                    '6' => 0.0, // Samedi
                    '7' => 0.0, // Dimanche
                ]
            ],

            'weekLength'     => [
                // Durée d'une semaine normale
                'value' => 37.0,
                
                // Durée à NE PAS dépasser (bloquant)
                'max' => 44.0,
            ],

            'monthLength' => [
                // Durée d'un mois normal
                'value' => 144.0,
                
                // Durée à NE PAS dépasser (bloquant)
                'max'   => 160.0,
            ],
        ],
    ]
);
``` 

## Choix "Hors-Lot" disponibles

Oscar permet de configurer les créneaux disponibles hors activité. La clef `horslots` permet de choisir et personnaliser les options proposées.

```php
<?php
return array(
   
    'oscar' => [
        // ...
        'horslots' => [
            'conges' => [ 'code' => 'conges',  'label' => 'Congés',  'description' => '', 'icon' => true ],
            'training' => [ 'code' => 'training',  'label' => 'Formation',  'description' => '', 'icon' => true ],
            'teaching' => [ 'code' => 'teaching',  'label' => 'Enseignement',  'description' => '', 'icon' => true ],
            'sickleave' => [ 'code' => 'sickleave', 'label' => 'Arrêt maladie',  'description' => '', 'icon' => true ],
            'research' => [ 'code' => 'research', 'label' => 'Autre recherche',  'description' => '', 'icon' => true ],
            'other' => [ 'code' => 'other', 'label' => 'Divers',  'description' => 'Autre activité', 'icon' => true ],
        ],
    ]
);
``` 
