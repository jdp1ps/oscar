# version 2.12 "Spartan"

## Nouveautés

### MAJ Authentification

L'une des librairies PHP utilisée par Oscar a évoluée et implique des changements mineurs dans la configuration.

Il faut maintenant spécifier une clef par mode d'authentification proposée : 

 - local/ldap
 - CAS
 - Shiboleth

```php
<?php
// config/autoload/local.php
$settings = array(
    // Authentification via LDAP/BDD
    'local' => [
        'order' => 2,
        'enabled' => true,
        'db' => [
            'enabled' => true,
        ],
        'ldap' => [
            'enabled' => false,
        ],
    ],
    
    // CAS
    'cas' => [
        'order' => 1,
        'enabled' => true,
        'connection' => [
            'default' => [
                'params' => [
                    'hostname' => 'host.domain.fr',
                    'port'     => 443,
                    'version'  => "2.0",
                    'uri'      => "",
                    'debug'    => false,
                ],
            ],
        ]
    ],   

    // Usurpation (DEV/Préprod)
    'usurpation_allowed_usernames' => array('bouvry', 'turbout'),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
```

### Organisations : Nouveaux champs

Le modèle des organisations a été enrichi, les champs suivants ont été ajoutés : 

 - `duns` : Numéro DUNS, 
 - `tvaintra`  : TVA Intacommunautaire,
 - `labintel` : Numéro Labintel (CNRS)
 - `rnsr` : Numéro RNSR (Répertoire National des Structures de Recherche)

> Ces champs restent facultatifs dans la majorité des cas, mais un de ces champs est attendu par le module PCRU pour authentifier les partenaires


### Activités : Nouveau champs

Les champs : 

 - Source de financement
 - Pôle de compétitivité / Validé par le côle de compétitivité

> Les listes proposent des sources fixes officielles (CNRS)

### Module PCRU

#### Informations PCRU d'une activité

Le module PCRU permet d'automatiser l'extraction des données Oscar vers PCRU. Il propose un nouveau module pour visualiser/gérer les données PCRU d'une activité de recherche depuis la fiche activité :

![Fiche information PCRU](../images/pcru-fiche-infos.png)

Si des informations sont manquantes, un message indiquera comment compléter les informations afin de rendre l'activité éligible à PCRU.

Une fois les donnèes valides, vous pourrez rendre les données éligibles pour l'envoi automatique des informations en cliquant sur **Activer l'envoi PCRU**

Vous pouvez également prévisualiser les documents générés en téléchargeant l'aperçu des fichiers générés

### Gestion PCRU

Un écran centralisé permet de visualiser les données PCRU ainsi que leur état.

![Liste de contrôle PCRU](../images/pcru-list.png)

---

## Mise en place technique

### référenciels

- **Pôles de compétitivité** : Le référenciel des pôles de compétitivité peut être actualisé automatiquement depuis l'interface (Configuration et maintenance > Nomenclatures > Référenciel des pôles de compétitivité), le bouton **Actualiser** permet de charger automatiquement le référenciel.

 - **Source de financement** : Le référenciel des sources de financement peut être actualisé automatiquement depuis l'interface (Configuration et maintenance > Nomenclatures > Référenciel des sources de financement), le bouton **Actualiser** permet de charger automatiquement le référenciel.

### PCRU

Le module PCRU permet de gérer et d'automatiser les transmissions d'information avec PCRU.

 > [Configuration du module PCRU](../config-pcru.md)

