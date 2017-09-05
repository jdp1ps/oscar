# Connecteurs

*Oscar* dispose d'un mécanisme pour gérer et synchroniser les données tiers.
La version 2.0 de Oscar s'appuit sur un service REST distant qui va livrer les
données à Oscar sous un format standardisé.

Il est possible de développer ces propres services REST si les informations
sont réparties de façon plus spécifique dans le SI.


## Source pour les Persons
Données utilisées dans l'application pour les personnes qui participent aux
activités de recherche.

### Connecteur REST

La configuration suivante dans le fichier `/config/autoload/local.php` permet
d'activer le connecteur REST pour les personnes.

```php
// /config/autoload/local.php
<?php
return array(
   // ...
   'oscar' => [
      'connectors' => [
            'person' => [
               'rest' => [
                  'class'     => \Oscar\Connector\ConnectorPersonREST::class,
                  'params'    => APP_DIR . '/config/connectors/person_rest.yml',
                  'editable'  => false
               ]
            ]
         ]
      ]
);
```

le fichier `/config/connectors/person_rest.yml` contiends les URL utilisées pour
obtenir les données :

```yml
# Emplacement du service REST fournissant la liste des personnes
url_persons: 'https://rest.service.tdl/api/persons'

# Emplacement du service REST fournissant les données pour une personne
# Noter la présente du '%s' que Oscar remplacera par l'UID utilisé dans
# le service REST.
url_person: 'https://rest.service.tld/api/person/%s'
```

L'API REST doit retourner un JSON standard, pour la liste un tableau d'objet,
pour l'accès unitaire un objet simple sous la forme :

(les valeurs terminées par un \* sont obligatoire)

```JSON
{
   "uid": "p00000237",
   "login": "sbouvry",
   "firstname": "Stéphane",
   "lastname": "Bouvry",
   "displayname": "Stéphane Bouvry",
   "mail": "sbouvry@domain.tdl",
   "civilite": "Mr",
   "preferedlanguage": "fr",
   "status": "CONTRACTUEL",
   "affectation": "LABO LAB",
   "structure": "UFR de Science",
   "inm": "237",
   "phone": "0231237237",
   "birthday": "YYYY-MM-DD",
   "datefininscription": "YYYY-MM-DD",
   "datecreated": "YYYY-MM-DD",
   "dateupdated": "YYYY-MM-DD",
   "datecached": "YYYY-MM-DD",
   "address": {
      "address1" : "1, place du centre",
      "address2" : "bâtiment B",
      "address3" : "BP 237",
      "zipcode" : "14021",
      "city": "Caen",
      "country": "France"
   },
   "groups": [
      "cn=mongroupe,ou=groups,dc=domain,dc=fr"
   ],
   "roles": {
      "CODE STRUCTURE": ["Role1", "Role2", "RoleN"]
   }
}
```

Voici les données minimales attendues : 

```JSON
{
   "uid": "ID UNIQUE",
   "login": "Identifiant utilisé pour l'authentification LDAP (supannAliasLogin) * ",
   "firstname": "Prénom * ",
   "lastname": "Nom * ",
   "displayname": "Tel que affiché * ",
   "mail": "Courriel de la personne (unique) * ",
   "civilite": "",
   "preferedlanguage": "",
   "status": "",
   "affectation": "",
   "structure": "",
   "inm": "",
   "phone": "",
   "birthday": "",
   "datefininscription": "",
   "datecreated": "",
   "dateupdated": "YYYY-MM-DD",
   "datecached": "YYYY-MM-DD",
   "address": null,
   "groups": [],
   "roles": {}
}
```

Important : Oscar gère l'authentification séparement, il établie la jonction entre 
la Personne (donnée) et l'autentification en utilisant la valeur présente dans login 
qui doit correspondre au champ "supannAliasLogin" côté LDAP.

Dans le rôles, la clef 'CODE STRUCTURE' doit correspondre à la valeur 'CODE' fournit par le connection des organisations.


Pour l'URL "liste", le service REST doit retourner un tableau composé d'objets organisés de la même façon.

## Données pour les organisations

De la même façon, 2 URL peuvent être utilisées pour synchroniser les données des structures. Voici le modèle attendu : 

```json
{
    "id" : "ID UNIQUE",
    "code" : "CODE UNIQUE (utiliser pour les affectations des personnes)",
    "shortname" : "ex : ANR",
    "longname" : "ex : Agence Nationale de la Recherche",
    "description" : "",
    "address" : { 
      "address1" : " address peut être NULL",
      "address2" : "",
      "address3" : "Boîte postale",
      "zipcode" : "",
      "city": "",
      "country": ""
    },
    "dateupdated" : "YYYY-MM-DD utilisé pour appliquer ou pas l"update,
    "phone" : "",
    "url" : "",
    "email" : "",
    "siret" : ""
}
```

Données minimales attendues : 

```json
{
    "id" : "ID UNIQUE",
    "code" : "CODE UNIQUE (utiliser pour les affectations des personnes)",
    "shortname" : "ex : ANR",
    "longname" : "ex : Agence Nationale de la Recherche",
    "description" : "",
    "address" : null,
    "dateupdated" : "YYYY-MM-DD",
    "phone" : "",
    "url" : "",
    "email" : "",
    "siret" : ""
}
```

## Importer des activités

Oscar dispose d'un utilitaire en ligne de commande pour importer des activités depuis un fichier CSV ou JSON.

