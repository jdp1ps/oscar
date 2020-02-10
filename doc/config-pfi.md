# Formalisme du PFI

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
