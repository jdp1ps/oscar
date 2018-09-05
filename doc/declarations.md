# Configuration des déclarations

La configuration du système de déclaration peut être modifier en suchargeant les paramètres 
par défaut fixés dans le fichier `config/autoload/global.php`. Pour ça, redéclarez les paramètres en 
modifiant les valeurs dans le fichier `config/autoload/local.php`.



# Options disponibles

### declarationsHours : Mode de déclaration en heures

Définit le mode de saisie des créneaux dans la déclaration par tous les utilisateurs. La valeur `false`(par défaut) fixe 
le mode de déclaration en **pourcentage de jour**. La valeur `true` bascule le mode de déclaration en heure.

### declarationsHoursOverwriteByAuth : Personnalisation du mode de déclaration

Cette option permet d'autoriser (Valeur `true`) ou pas (Valeur `false`) le choix du mode de saisie des créneaux dans la déclaration.

### declarationsDayDuration : Durée d'une journée

Configure la durée normal d'une journée. Pour le moment, ce paramètre ne peut pas être modifié sans effet de bord. 
(ex: La journée est fixée à 7 heures, une personne déclare 100%, puis la journée est fixée à 8 heures. La déclaration 
de 100% a été enregistrée sur l'ancienne valeur, et pour le moment, s'affichera à moins de 100%)

### horslots

Permet de configurer les éléments "Hors-Lot" disponibles. La seule règle à respecter est la correspondance entre la clef 
et la valeur dans l'index `code` : 

```php
<?php
// ...
return [
    'oscar' => [
        
        // Hors-Lot disponibles 
        'horslots' => [
            'conges'    => [ 'code' => 'conges',  'label' => 'Congés',  'description' => 'Congès, RTT, récupération', 'icon' => true ],
            'sport'     => [ 'code' => 'sport', 'label' => 'Sport', 'description' => 'Un simple exemple', 'icon' => true ],
            'other'     => [ 'code' => 'other', 'label' => 'Divers',  'description' => 'Autre activité', 'icon' => true ],
        ],    
    ]
];
```


# Exemple de configuration

```php
<?php
// Fichier config/autoload/global.php
// ...
return [
    'oscar' => [
        
        // ...
        
        // Activation de la déclaration en heure
        // par défaut
        // Valeurs (true,false)
        'declarationsHours' => false,
        
        // Authorise la personnalisation du mode de déclaration par le déclarant
        'declarationsHoursOverwriteByAuth' => false,

        // Durée standard d'une journée pour les déclarants (général)
        'declarationsDayDuration' => 8,

        // Hors-Lot disponibles pour les déclrants
        'horslots' => [
            'conges'    => [ 'code' => 'conges',  'label' => 'Congés',  'description' => 'Congès, RTT, récupération', 'icon' => true ],
            'training'  => [ 'code' => 'training',  'label' => 'Formation',  'description' => 'Vous avez suivi un formation, DIFF, etc...', 'icon' => true ],
            'teaching'  => [ 'code' => 'teaching',  'label' => 'Enseignement',  'description' => 'Cours, TD, fonction pédagogique', 'icon' => true ],
            'sickleave' => [ 'code' => 'sickleave', 'label' => 'Arrêt maladie',  'description' => '', 'icon' => true ],
            'research'  => [ 'code' => 'research', 'label' => 'Autre recherche',  'description' => 'Autre projet de recherche (sans feuille de temps)', 'icon' => true ],
            'other'     => [ 'code' => 'other', 'label' => 'Divers',  'description' => 'Autre activité', 'icon' => true ],
        ],    
    ]
];
```