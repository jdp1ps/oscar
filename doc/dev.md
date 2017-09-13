# Données

*Oscar* dispose de mécanismes pour gérer et synchroniser les données tiers.

Il propose des utilitaires en ligne de commande pour **importer des données** depuis un fichier, et un mécanisme pour **synchroniser** des informations depuis une source tiers (les *connectors*).


## Principe de Connectors

Les connectors permettent de *brancher* Oscar sur des sources de donnée et d'automatiser la maintenance de ces données à jour.

Les connectors dans version 2.0 de Oscar s'appuie sur un service REST distant qui va livrer les données à Oscar sous un format standardisé.

Il est possible de développer ces propres services REST si les informations sont réparties de façon plus spécifique dans le SI.


## Connector PERSONS

Données utilisées dans l'application pour les personnes qui participent aux activités de recherche.

Note : Attention, dans Oscar, les comptes pour s'authentifier et les personnes sont des données distinctes, une personne peut être présente dans Oscar sans pour autant avoir de compte pour s'authentifier dessus. Par contre, il existe une relation facultative entre les personnes et les authentifications.

La configuration suivante dans le fichier `/config/autoload/local.php` permet d'activer le connecteur REST pour les personnes.

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

Pour information, la clef `class` permet de choisir une classe à utiliser pour traiter les données. Cette class implémente l'interface `IConnectorPerson`, il est possible d'implémenter vos propres connectors si besoin.

le fichier `/config/connectors/person_rest.yml` contiends les URL utilisées par le connecteur pour obtenir les données :

```yml
# Emplacement du service REST fournissant la liste des personnes
url_persons: 'https://rest.service.tdl/api/persons'

# Emplacement du service REST fournissant les données pour une personne
# Noter la présente du '%s' que Oscar remplacera par l'UID utilisé dans
# le service REST.
url_person: 'https://rest.service.tld/api/person/%s'
```

Les URL correspondent à l'API REST qui devra retourner un JSON standard, pour la liste un tableau d'objet, pour l'accès unitaire un objet simple sous la forme :

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

Dans le rôles, la clef **CODE STRUCTURE** doit correspondre à la valeur *CODE* fournit par le connecteur des organisations (ci-après).

Pour l'URL "liste", le service REST doit retourner un tableau composé d'objets organisés de la même façon.

## Connector ORGANIZATIONS

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
    "dateupdated" : "YYYY-MM-DD utilisé pour appliquer ou pas l'update",
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


## Importer des activités (Installation initiale)


Oscar dispose d'un utilitaire en ligne de commande pour importer des activités depuis un fichier CSV ou JSON.

Un exemple de fichier est présent dans le dossier `install/demo/activity-demo.csv`

Le script se chargera de créer dans oscar les activités/projets en y associant les personnes/organisations si ces dernières sont bien présentes dans Oscar.

Exemple de données JSON :

```json
[
  {
    "uid": "IDENTIFIANTUNIQUE",
    "acronym": "ACRONYME du PROJET",
    "projectlabel": "Théorie de la relativité",
    "label": "Exemple d'activité 1",
    "datestart": null,
    "dateend": null,
    "datesigned": "2017-06-01",
    "pfi": "",
    "amount": 0.0,
    "organizations": {
      "Laboratoire": [
        "Cyberdyne",
        "US Robots"
      ]
    },
    "persons": {
      "Responsable scientifique": [
        "Albert Einstein"
      ],
      "Ingénieur": [
        "John Doe"
      ]
    }
  }
]
```

### uid (requis)
Le champ `uid` est un identifiant unique qui permet à *Oscar* de savoir s'il doit ajouter ou mettre à jour une activité.

### acronym ET projectlabel (optionnels)
Ces deux champs permettent à Oscar de savoir dans quel projet l'activité doit être placée. Oscar cherchera dans sa base de donnée un projet ayant ces données précises, s'il ne trouve pas, il va créer un nouveau projet avec ces informations.

### Autres champs (optionnel)

 * **label** Intitulé de l'activité
 * **datestart** la date de début au format ISO `YYYY-MM-DD`
 * **dateend** la date de fin au format ISO `YYYY-MM-DD`
 * **datesigned** la date de signature au format ISO `YYYY-MM-DD`
 * **pfi** L'EOTP
 * **amount** Le montant prévus (ex: 15000.50, 2500)

### organizations
Cette donné est un **objet** ayant une ou plusieurs clefs correspondants aux rôle d'organisation disponibles dans Oscar.

Dans l'exemple ci dessous, l'activité aura comme *Laboratoire* les organisations *Cyberdyne* et *US Robot* et comme *Composante responsable* l'*Université de Vienne* et *ACME*.

```json
[
    {
        "organizations": {
            "Laboratoire": ["Cyberdyne", "US Robots"],
            "Composante responsable": ["Université de Vienne", "ACME"]
        }
    }
]
```
Si **le rôle est absent de Oscar**, l'information sera ignorée, un warning sera affiché dans le rapport d'importation.

Le contenu de chaque clef est un tableau de chaîne de caractère contenant le **fullname** de l'organisation. Si **Oscar** ne trouve pas l'organisation, il ignorera l'information et ajoutera un *warning* au rapport.

### persons
Cette donné est un **objet** ayant une ou plusieurs clefs correspondants aux rôle des personnes disponibles dans Oscar :

```json
[
    {
        "persons": {
            "Responsable Scientifique": ["Albert Einstein"],
            "Ingénieur": ["Marcel Grossmann", "Maurice Solovine"]
        }
    }
]
```

Chaque clef contient un tableau de chaîne avec comme valeur la nom complet de la personne. Lorsque Oscar recherche cette information, il concatène le **firstname** et le **lastname** séparés par un espace.
