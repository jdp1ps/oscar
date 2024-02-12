# Connectors LDAP Personnes et Structures

*Oscar* dispose de mécanismes pour gérer et synchroniser les données tiers.

Il propose des utilitaires en ligne de commande pour **importer des données** depuis un fichier, et un mécanisme pour **synchroniser** des informations depuis une source tiers (les *connectors*).


## Principe de Connectors

Les connectors permettent de *brancher* Oscar sur des sources de données et d'automatiser la maintenance de ces données.

Les connectors dans version 2.0 d'Oscar s'appuient sur l'annuaire LDAP qui va livrer les données à Oscar sous un format standardisé.

Oscar possède la possibilité de se connecter à l'annuaire LDAP via Zend Ldap et un accès login/mot de passe fourni par l'administrateur  LDAP.

## Configuration

Les connecteurs (*persons* ou *organizations*) sont déclarés dans le fichier de configuration `config/autoload/local.php` et `config/autoload/global.php` :

```php
<?php
// /config/autoload/local.php
// /config/autoload/global.php
return array(
    'oscar' => [
        'connectors' => [
            'organization' => [
                'ldap' => [
                    'class'     => \Oscar\Connector\ConnectorLdapOrganizationJson::class,
                    'params'    => realpath(__DIR__) . '/../connectors/organization_ldap.yml',
                    'editable'  => false
                ]
                /****/
            ],
            'person' => [
                'ldap' => [
                    'class'     => \Oscar\Connector\ConnectorLdapPersonJson::class,
                    'params'    => realpath(__DIR__) . '/../connectors/person_ldap.yml',
                    'editable'  => false
                ]
                /****/
            ],
        ],
    ]
);
```

> Pour information, la clef `class` permet de choisir une classe à utiliser pour traiter les données. Cette class implémente l'interface `AbstractConnectorOscar`, il est possible d'implémenter vos propres connectors si besoin.

### Configuration des connecteurs

Les connecteurs Personnes et Structures sont configurés dans des fichier *YAML* :

- /config/autoload/organization_ldap.php
- /config/connectors/person_ldap.php


#### HTTP basic

le fichier `/config/connectors/person_ldap.yml` et `/config/connectors/organization_ldap.yml` contient les filtres LDAP pour obtenir les données :

Pour les personnes :

```yml
#filtre LDAP pour les personnes
filtre_ldap: '&(objectClass=inetOrgPerson)(eduPersonAffiliation=researcher),&(objectClass=inetOrgPerson)(eduPersonAffiliation=member),&(objectClass=inetOrgPerson)(eduPersonAffiliation=staff),&(objectClass=inetOrgPerson)(supannCodePopulation={SUPANN}AGA*),&(objectClass=inetOrgPerson)(eduPersonAffiliation=emeritus)'
```

Pour les organisations :

```yml
#filtre LDAP pour les structures
filtre_ldap: '&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=research),&(objectClass=supannEntite)(supannTypeEntite={SUPANN}S*)(businessCategory=administration)'
```

Les filtres Ldap ont un séparateur ',' (virgule).


#### Utilisation des connecteurs en ligne de commande

Depuis la console Oscar, la synchronisation s'exécute tel quel :

- Pour les personnes : php bin/oscar.php ldap:persons:sync
- Pour les organizations : php bin/oscar.php ldap:organizations:sync

D'autres fonctionnalités pour la recherche simple ont été ajoutés : 

- Pour les personnes : php bin/oscar.php ldap:persons:search {username} (exemple : shing)
- Pour les organizations : php bin/oscar.php ldap:organizations:search {nom_court} (exemple:DIREVAL)

#### Utilisation des connecteurs depuis Oscar

Il suffit simplement d'aller dans la page des connecteurs /administration/connectors

