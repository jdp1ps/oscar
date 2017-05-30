# Connecteurs OSCAR

*Oscar* dispose d'un mécanisme pour gérer et synchroniser les données tiers.
La version 2.0 de Oscar s'appui sur un service REST distant qui va livrer les
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

```JSON
// Format unitaire
{
   "uid": "ID UNIQUE *",
   "login": "Identifiant utilisé pour l'authentification LDAP * ",
   "firstname": "Prénom",
   "lastname": "Nom",
   "displayname": "Tel que affiché",
   "mail": "Courriel de la personne (unique)",
   "civilite": "",
   "preferedlanguage": "",
   "status": "ex : CONTRACTUEL, TITULAIRE",
   "affectation": "",
   "structure": "",
   "inm": "",
   "phone": "",
   "birthday": "YYYY-MM-DD",
   "datefininscription": "YYYY-MM-DD",
   "datecreated": "YYYY-MM-DD",
   "dateupdated": "YYYY-MM-DD",
   "datecached": "YYYY-MM-DD",
   "address": {
      "address1" : " address peut être NULL",
      "address2" : "",
      "address3" : "Boîte postale",
      "zipcode" : "",
      "city": "",
      "country": ""
   },
   "groups": [
      "cn=mongroupe,ou=groups,dc=domain,dc=fr"
   ],
   "roles": {
      "CODE STRUCTURE": ["Role1", "Role2", "RoleN"]
   }
}
```
