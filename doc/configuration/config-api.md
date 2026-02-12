# Oscar API

Oscar dispose d'un API ouverte permettant d'interoger les données dans **Oscar** depuis une application tiers. 


## Accès à l'API

Pour activer cette fonctionnalité, vous devez vous rendre dans l'interface d'administration.

![Configuration de l'API oscar](images/oscar-api.png)

> Si l'option n'est pas disponible, vérifier votre version de Oscar (v2.11 "Macclane" minimum) et que vous disposer des privilèges suffisants

## Gérer les accès

L'inferface de gestion permet de voir les différentes clef d'accès disponibles dans Oscar : 

![Configuration de l'API oscar](images/oscar-api-list.png)

Cette interface permet de configurer les niveaux d'accès ainsi que les différents API disponibles.

![Configuration de l'API oscar](images/oscar-api-fiche.png)
 

## Accès

Les données seront accessibles via les URL : 
 - Liste des personnes : https://oscar.votreuniv.fr/api/persons
 - Personne seule : https://oscar.votreuniv.fr/api/persons/IDPERSON
 - Liste des organisations : https://oscar.votreuniv.fr/api/organizations
 - Orgnisation seule : https://oscar.votreuniv.fr/api/organizations/IDORGANIZATION

L'identification se fait par le mécanisme basic access authentication d'HTTP, c'est-à-dire qu'il faut envoyer avec sa requête un HEADER ```Authorization: Basic credentials```, où ```credentials``` est le résultat de l'encodage en Base64 de l'identifiant et du mot de passe séparés par un caractère deux points ```:```.
Exemple :

```bash
CLIENT_ID=MON_IDENTIFIANT CLIENT_SECRET=MON_MOT_DE_PASSE ENCODED="$( echo -n $CLIENT_ID:$CLIENT_SECRET | base64 -w 0 )"; curl --request GET --url 'https://oscar.votreuniv.fr/api/persons' --header "Authorization: Basic $ENCODED"
```

## Champs retournés par défaut

### API persons

Requête
```bash
CLIENT_ID=MON_IDENTIFIANT CLIENT_SECRET=MON_MOT_DE_PASSE ENCODED="$( echo -n $CLIENT_ID:$CLIENT_SECRET | base64 -w 0 )"; curl --request GET --url 'https://oscar.votreuniv.fr/api/persons' --header "Authorization: Basic $ENCODED"
```

Réponse
```json
{
  "version": "v2.16.0 \"Connor\"",
  "datecreated": "2026-02-12T11:59:43+01:00",
  "time": 1.3344779014587402,
  "total": 1,
  "persons": [
    {
      "uid": "123456",
      "login": "",
      "firstname": "Euphrasie",
      "lastname": "ANTHUSE",
      "displayname": "Euphrase ANTHUSE",
      "mail": "euphrasie.anthuse@votreuniv.fr",
      "civilite": "",
      "preferedlanguage": "",
      "status": "",
      "affectation": "LABOSUP",
      "structure": "109 boulevard du Maréchal Juin , 14000 Caen",
      "inm": "",
      "phone": "",
      "birthday": "",
      "datefininscription": "",
      "datecreated": "2023-05-10T18:47:52+02:00",
      "dateupdated": "2023-05-10T18:47:52+02:00",
      "datecached": "2023-05-10T18:47:52+02:00",
      "text": "Euphrase ANTHUSE",
      "mailMd5": "615c6fb49a9eb1d913a8195720f1f9ee",
      "ucbnSiteLocalisation": "109 boulevard du Maréchal Juin , 14000 Caen",
      "groups": null,
      "roles": []
    }
  ]
}
```


### API person

Requête
```bash
CLIENT_ID=MON_IDENTIFIANT CLIENT_SECRET=MON_MOT_DE_PASSE ENCODED="$( echo -n $CLIENT_ID:$CLIENT_SECRET | base64 -w 0 )"; curl --request GET --url 'https://oscar.votreuniv.fr/api/persons/123456' --header "Authorization: Basic $ENCODED"
```

Réponse
```json
{
  "version": "v2.16.0 \"Connor\"",
  "datecreated": "2026-02-12T11:53:48+01:00",
  "time": 0.020016908645629883,
  "uid": "123456",
  "person": {
    "uid": "123456",
    "login": "apollinaris241",
    "firstname": "WILHELM",
    "lastname": "APOLLINARIS",
    "displayname": "WILHELM APOLLINARIS",
    "mail": "wilhelm.apollinaris@votreuniv.fr",
    "civilite": "",
    "preferedlanguage": "",
    "status": "",
    "affectation": "UFR de Sciences Économiques, de Gestion, de Géographie et d'Aménagement des Territoires",
    "structure": "",
    "inm": "",
    "phone": null,
    "birthday": "",
    "datefininscription": "",
    "datecreated": "2024-03-08T07:32:03+01:00",
    "dateupdated": "2024-03-08T07:32:03+01:00",
    "datecached": "2024-03-08T07:32:03+01:00",
    "text": "WILHELM APOLLINARIS",
    "mailMd5": "fa12a139z2b33ca2019z1e3f75ae1c06",
    "ucbnSiteLocalisation": "",
    "groups": [],
    "roles": []
  }
}
```


### API organizations

Requête
```bash
CLIENT_ID=MON_IDENTIFIANT CLIENT_SECRET=MON_MOT_DE_PASSE ENCODED="$( echo -n $CLIENT_ID:$CLIENT_SECRET | base64 -w 0 )"; curl --request GET --url 'https://oscar.votreuniv.fr/api/organizations' --header "Authorization: Basic $ENCODED"
```

Réponse
```json
{
  "version": "v2.16.0 \"Connor\"",
  "datecreated": "2026-02-12T12:21:46+01:00",
  "time": 0.31410717964172363,
  "total": 1,
  "organizations": [
    {
      "uid": "12345",
      "code": "",
      "shortname": "",
      "longname": "Université de Rennes - Campus Beaulieu ",
      "description": "",
      "address": {
        "address1": "263 avenue Général Leclerc - CS 74205",
        "address2": "",
        "address3": "",
        "zipcode": "35042",
        "city": "Rennes",
        "country": "France"
      },
      "datecreated": "2025-05-06T09:18:20+02:00",
      "dateupdated": "2025-05-06T09:18:20+02:00",
      "datecached": "2025-05-06T09:18:20+02:00",
      "phone": "",
      "url": "",
      "email": "",
      "siret": "",
      "type": "Établissement publique"
    }
  ]
}
```


### API organization

Requête
```bash
CLIENT_ID=MON_IDENTIFIANT CLIENT_SECRET=MON_MOT_DE_PASSE ENCODED="$( echo -n $CLIENT_ID:$CLIENT_SECRET | base64 -w 0 )"; curl --request GET --url 'https://oscar.votreuniv.fr/api/organizations/12345' --header "Authorization: Basic $ENCODED"
```

Réponse
```json
{
  "version": "v2.16.0 \"Connor\"",
  "datecreated": "2026-02-12T12:23:28+01:00",
  "time": 0.012481212615966797,
  "uid": "12345",
  "organization": {
    "uid": "12345",
    "code": "",
    "shortname": "",
    "longname": "Université de Rennes - Campus Beaulieu ",
    "description": "",
    "address": {
      "address1": "263 avenue Général Leclerc - CS 74205",
      "address2": "",
      "address3": "",
      "zipcode": "35042",
      "city": "Rennes",
      "country": "France"
    },
    "datecreated": "2025-05-06T09:18:20+02:00",
    "dateupdated": "2025-05-06T09:18:20+02:00",
    "datecached": "2025-05-06T09:18:20+02:00",
    "phone": "",
    "url": "",
    "email": "",
    "siret": "",
    "type": "Établissement publique"
  }
}
```





## Configurer les formatteurs

Les formatteurs permettent de personnaliser la sortie d'une ou plusieurs API.

Le formatteur de base est **\Oscar\Formatter\PersonToJsonConnectorBasicFormatter**, il produit une sortie au format JSON sous la forme : 

```json
{
  "version": "v2.11.1-macclane#dcf4a55e \u0022Macclaine\u0022 (2020-02-17 10:02:49)",
  "datecreated": "2020-02-17T12:04:44+01:00",
  "time": 0.00012345789,
  "total": 2,
  "persons": [
    {
      "uid": "6357",
      "login": "einstein",
      "firstname": "Albert",
      "lastname": "Einstein",
      "displayname": "Albert Einstein",
      "mail": "albert.einstein@berne-university.de",
      "affectation": "Bureau des brevets",
      "structure": "",
      "datecreated": "2015-11-05T15:33:08+01:00",
      "dateupdated": "2017-07-13T11:26:41+02:00",
      "datecached": "2017-07-13T11:26:41+02:00"
    },
    {
      "uid": "9038",
      "login": "tenseurman",
      "firstname": "Marcel",
      "lastname": "Grossmann",
      "displayname": "Marcel Grossmann",
      "mail": "marcel.grossmann@mathman.de",
      "affectation": "Berne University",
      "structure": "",
      "datecreated": "2016-07-07T16:48:13+02:00",
      "dateupdated": "2017-07-13T11:26:03+02:00",
      "datecached": "2017-07-13T11:26:03+02:00"
    }
  ]
}
```

Si besoin, vous pouvez développer vos propres formatteurs, des classes PHP qui assureront le traitement des données depuis Oscar vers le format de sortie souhaité.

**[A VENIR] Développer un formatteur pour l'API Oscar**


### Configurer les formatteurs disponibles

La première étape necessite d'identifier les formateurs disponible de la fichier de configuration générale : 

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        // ...
        'api' => [
           // Personnalisation des formats disponibles dans l'API (persons)
           'formats' => []
       ],
    ]
);
```

Vous pourrez ensuite depuis l'interface de gestion des accès à l'API choisir parmi les formatteurs configurer celui à utiliser lors de l'accès à l'API.

## Évolutions prévues

 - [ ] API affectation
 - [ ] API activité
 - [ ] API feuille de temps
 - [x] Prise en charge de *formateur* personnalisés
 - [x] Configuration d'un *formatteur* pour les données

