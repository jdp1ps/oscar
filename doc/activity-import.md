% Importer des activités dans Oscar
% Université de Caen
% Décembre 2017

# Présentation

**Oscar** dispose d'un utilitaire en ligne de commande permettant de synchroniser des activités depuis **un fichier JSON**.

Cet utilitaire sera utilisé pour importer et synchroniser des activités dans Oscar.

La procédure de synchronisation s'utilise en ligne de commande :

```bash
$ php public/index.php oscar activity:sync path/to/file.json
```

# Source des données

## Format JSON

Le fichier source est au [format JSON]([http://json.org/). Un échantillon de ce fichier est disponible dans les sources de l'application dans le dossier `./install/demo/activity.json`. Le contenu du fichier se présente sous la forme d'un tableau d'objet.

```json
[
  {
    "uid": "A0001",
    "acronym": "RELACSV",
    "projectlabel": "Théorie de la relativité",
    "label": "Exemple d'activité 1",
    "datestart": "",
    "dateend": "",
    "datesigned": "2017-06-01",
    "pfi": "12PFI3456",
    "datePFI": "2017-07-04",
    "type": "ANR",
    "amount": "0.0",
    "tva": null,
    "currency": null,
    "assietteSubventionnable": null,
    "financialImpact": "Aucune",
    "organizations": {
      "Laboratoire": ["Cyberdyne", "US Robots"]
    },
    "persons": {
      "Responsable scientifique": ["Albert Einstein"],
      "Ingénieur": ["Maurice Solovine", "Marcel Grossman"]
    },
    "milestones": []
  },
  {
    "uid": "A0002",
    "acronym": "RELACSV",
    "projectlabel": "Théorie de la relativité",
    "label": "Exemple d'activité 2",
    "datestart": "2015-01-01",
    "dateend": "2017-12-31",
    "datesigned": "2015-02-01",
    "pfi": "",
    "type": "Colloques",
    "amount": 15000,
    "tva": 19.6,
    "currency": "Yens",
    "assietteSubventionnable": 5,
    "financialImpact": "Aucune",  
    "milestones": [

    ],
    "payments": [

    ],
    "organizations": {
      "Laboratoire": [
        "Cyberdyne",
        "US Robots"
      ],
      "Composante responsable": [
        "ACME"
      ]
    },
    "persons": {
      "Responsable scientifique": [
        "Albert Einstein",
        "Maurice Solovine"
      ],
      "Ingénieur": [
        "John Doe",
        "Marcel Grossmann"
      ]
    },
    "disciplines": [
      "Physique",
      "Chimie"
    ]
  }
]
```

Le tableau contient des **objets JSON**, chaque objet correspond à UNE activité.

## Données d'une activité

Voici la liste des clefs attendues :

Clef          | Type      | PÊ Vide   | Unique | Description
------|------|------|------|------------------------------------------
uid             | String    | Non       | Oui    | Identifiant d'import (évite les doublons et permet de mettre à jour les données importées)
acronym         | String    | Non       | Non    | Acronyme du projet, Si Oscar ne trouve pas de projet existant avec cet acronyme, il le créera automatiquement
projectlabel | String    | Oui       | Non    | Utilisé pour créer le projet si il n'existe pas
label           | String    | NR        | Non    | Intitulé de l'activité
description           | String    | Oui        | Non    | Description de l'activité
datestart       | Date ISO  | Oui       | Non    | Date de début de l'activité
dateend         | Date ISO  | Oui       | Non    | Date de fin de l'activité
datesigned         | Date ISO  | Oui       | Non    | Date de la signature de la convention
pfi             | String    | Oui       | Non    | EOTP/PFI de l'activité de recherche
datePFI         | Date ISO  | Oui       | Non    | Date d'ouverture du PFI
type            | String    | Oui       | Non    | Type d'activité, si Oscar ne trouve pas de type correspondant, la donnée est ignorée
amount          | Double    | Oui       | Non    | Montant de la convention
tva          | Double    | Oui       | Non    | Montant de la TVA (ex: 19.6)
currency          | Double    | Oui       | Non    | Nom ou symbole de la devise
assietteSubventionnable          | Double    | Oui       | Non    | Assiette subventionnable
financialImpact          | String    | Oui       | Non    | Aucune,Recette,Dépense
organizations   | Object    | Oui       | Non    | Voir détails dans [Gestion des organisations](#organizations)
persons         | Object    | Oui       | Non    | Voir détails dans [Gestion des personnes](#persons)
milestones      | Array     | Oui       | Non    | Voir détails dans [Gestion des jalons](#milestones)
payments        | Array     | Oui       | Non    | Voir détails dans [Gestion des versements](#payments)
disciplines        | Array     | Oui       | Non    | Voir détails dans [Gestion des disciplines](#disciplines)


```json
[
  {
    "uid": "",
    "acronym": "",
    "projectlabel": "",
    "label": "",
    "description": "",
    "datestart": "",
    "dateend": "",
    "datesigned": "",
    "datePFI": "",
    "pfi": "",
    "type": "",
    "amount": "",
    "tva": null,
    "currency": null,
    "assietteSubventionnable": null,
    "financialImpact": "Aucune",
    "organizations": {},
    "persons": {},
    "milestones": [],
    "status": 404,
    "payments": []    
  }
]
```

## Détails des champs

### La clef `uid`

Cette clef contient une valeur unique permettant à Oscar de maintenir le lien logique entre l'activité dans la base de données et l'information dans le fichier JSON. Elle permet de mettre à jour l'activité si le script d'importation est éxécuté plusieurs fois.

### La clef `status`

Cette clef permet de rensigner le statut de l'activité en utilisant un code standard (un entier) : 

| CODE | Correspondance texte |
|------|----------------------|
| 101  | Actif
| 102  | Brouillon
| 103  | Déposé
| 200  | Terminé
| 201  | Résilié
| 250  | Dossier abandonné
| 201  | Refusé
| 404  | Conflit (pas de statut)

> Si aucun statut n'est fourni, la valeur par défaut sera 404 (Conflit, pas de statut)


### Donnée projet (les clefs `acronym` et `projectlabel`)

La clef `acronym` correspond à l'acronyme du projet. Elle est utilisée par Oscar pour retrouver le projet dans la base de données.

Si plusieurs activités ont la même valeur `acronym`, elles sont agrégées dans le même projet.

Si Oscar ne trouve pas le projet dans la base de données, il tentera de le créer. Il utilisera alors la clef `projectlabel` pour renseigner l'intitulé du projet.


### la clef `type`

La valeur doit correspondre à l'intitulé d'un type d'activité, si Oscar ne trouve pas de type correspondant, il n'affecte pas de type à l'activité.

On peut voir la liste des types d'activités dans le menu **Administration > Gérer les types d'activités**.


<a id="organizations"></a>

### La clef `organizations`

La clef `organizations` permet d'associer des organisations à une activité avec une affectation de structure (Rôle d'organisation)..

Elle est de type **Object** et se compose d'un nombre libre de clef.

Chaque clef correspond à un rôle.

```json
{
  "organizations": {
    "Role A": [],
    "Role B": []
  }
}
```

Oscar cherchera dans la base de données une correspondance entre la valeur de la clef (Dans l'exemple ci dessus, les rôles sont *Role A* et *Role B*) et la liste des rôles disponibles dans la base de données : **Administration > Affectation des structures**. Si Oscar ne trouve pas de correspondance, il tentera de créer le rôle.


Par exemple si l'activité implique en tant que Laboratoire les organisations *Cyberdyne* et *Black Mesa*, la clef se présentera ainsi :

```json
{
  "organizations": {
    "Laboratoire": ["Cyberdyne", "Black Mesa"]
  }
}
```

Si l'on souhaite ajouter d'autres organisations avec un rôle différent, il suffit d'ajouter une clef avec le rôle en question :

```json
{
  "organizations": {
    "Laboratoire": ["Cyberdyne", "Black Mesa"],
    "Financeur": ["Wayne Enterprise", "LexCorp"]
  }
}
```

Le nom de l'organisation utilisé comme valeur correspond au champ "Nom complet" dans la fiche organisation dans Oscar. **Si l'organisation n'existe pas dans Oscar**,  Oscar tentera de la créer.


> Si les données des organisations sont synchronisées avec le SI, il faut synchroniser les organisations AVANT d'importer les activités pour éviter la création de doublon.


<a id="persons"></a>

### La clef `persons`

La clef `persons` permet d'associer une personne à une activité avec un rôle.

Elle fonctionne sur le même principe que le clef `organizations`.

Elle se compose de clefs correspondants aux rôles des personnes dans l'activité. Chaque clef rôle contient un tableau avec les nom complet des personnes (Prénom + Nom séparés par un espace) :

```json
{
  "persons": {
    "Responsable Scientifique": ["Albert Einstein"],
    "Ingénieur": ["Maurice Solovine", "Marcel Grossmann"]
  }
}
```

Comme pour les organisations, Oscar se chargera d'ajouter les rôles et les personnes si elles sont absentes de la base de données.

> Si les données des personnes sont synchronisées avec le SI, il faut synchroniser les personnes AVANT d'importer les activités pour éviter la création de doublon.


<a id="milestones"></a>

### La clef `milestones`

La clef `milestones` est utilisée pour ajouter des jalons à une activité.

La valeur est un tableau contenant des Objets JSON

```json
{
    "milestones": [
        {
            "type": "Rapport scientifique",
            "date": "2014-07-03"
        },
        {
            "type": "Fin des dépenses",
            "date": "2018-01-31"
        }
    ]
}
```

Ces objets contiennent une clef `date` qui contiendra une Date ISO correspondant à la date d'échéance du jalon, ainsi qu'une clef `type` correspondant au type de jalon (**Administration > Gérer les types d'activités**) :



> Si oscar trouve un Jalon de même type à la même date, il ne crée pas le jalon.


<a id="payments"></a>

### La clef payments

La clef `payments` est utilisée pour ajouter des versements à une activité.

La valeur est un tableau contenant des Objets JSON

```json
"payments": [
      {},
      {}
    ]
```

Ces objets contiennent une clef `date` qui contiendra une Date ISO correspondant à la date prévisionnelle et une clef `amount` contenant un *double* correspondant au montant du versement :

```json
{
    "milestones": [
        {
            "amount": 249.5,
            "date": "2014-07-03",
            "predicted": null
        },
        {
            "amount": 3249.5,
            "date": null,
            "predicted": "2018-01-31"
        }
    ]
}
```

> Les versements sans valeur dans la clef `date` mais avec une clef `predicted` seront tagués comme prévisionnels.


<a id="disciplines"></a>
### La clef disciplines (array de string)

La clef `disciplines` permet de spécifier une ou plusieurs disciplines a associer à l'activité. Le séparateur `#` dans la source CSV permet de gérer plusieurs disciplines : La valeur `Physique#Chimie` donnera `["Physique","Chimie"]. 

> Si la discipline est manquante, elle sera créée.

## La clef TVA (float, ex: 19.6)

La valeur doit correspondre à un taux présent dans la base de données (table `tva`)


## Currency (string, ex: €)

La valeur doit correspondre à un symbole / un intitulé de devise présent en base de données (table `currency`), Si rien n'est trouvé, Oscar mettra automatiquement l'euro en devise.


## assietteSubventionnable ( defaut : null, ex: 5.0 )

Valeur 


## financialImpact (ex: Recette)

Valeurs possibles : Aucune, Recette ou Dépense


# Importation depuis un fichier Excel

Oscar propose un utilitaire en ligne de commande pour convertir une source de donnée CSV en un fichier JSON.

```bash
php public/index.php oscar activity:csvtojson chemin/vers/source.csv chemin/vers/config.php
```

Ce script implique de configurer la correspondance entre les colonnes de la source CSV et la destination de le JSON dans un fichier de configuration PHP.

```php
<?php
//
return [
    0 => "uid",

    2   => 'project.acronym',
    4   => "project.label",

    3   => "label",
    1   => 'description',
    5   => "PFI",
    19  => "datePFI",
    22  => "dateSigned",

    6   => "organizations.Composante Responsable",
    7   => "organizations.Laboratoire",
    21   => "organizations.Laboratoire",

    8   => "persons.Responsable Scientifique",
    9   => "persons.Ingénieur",
    10   => "persons.Ingénieur",

    11  => "amount",

    12  => "payments.1.2",
    15  => "payments.1.2",
    24  => "payments.-1",

    18  => "milestones.Rapport financier",
    20  => "type",

    25  => [
        'key' => "persons.Participants",
        'separator' => ','
    ],
    
    26  => "tva",
    27  => "financialImpact",
    28  => "currency",
    29  => "assietteSubventionnable",
    30  => "status",
    31 => "disciplines"
];
```

Le tableau de configuration permet d'associer une colonne (index numérique), à une donnée dans le JSON résultant.

La clef numérique correspond à l'emplacement de la colonne dans le fichier CSV.

> Attention : En PHP, la première colonne a l'index 0, pas 1.

La valeur de la clef correspond à un code qui sera utilisé pour savoir comment ranger la valeur dans le JSON. Parmis les codes disponibles, certains sont simples :

| Code | Correspondance
|---|------
| label | Intitulé de l'activité
| amount | Montant
| dateStart | Date de début au format YYYY-MM-DD
| dateEnd | Date de fin au format YYYY-MM-DD
| PFI | PFI

Les objets plus complexes comme les organisations, les personnes, les versements et les jalons sont gérés avec des code "pointés". Ils sont sous la forme **objet.paramètre**.


### organizations.Role

La clef `organizations` prend pour paramètre le rôle de l'organisation trouvé dans la cellule, Si par exemple la colonne 3 contient un laboratoire, la configuration se présentera ainsi :

Valeur dans la cellule : Chaîne ou vide

```php
return [
  3 => "organizations.Laboratoire",
]
```

On obtiendra en JSON :

```json
{
  "organizations": {
    "Laboratoire": ["Valeur de la colonne"]
  }
}
```

### persons.Role

La clef `persons` prend pour paramètre le rôle de la personne trouvé dans la cellule. Si par exemple la colonne 7 contient le responsable scientifique, la configuration se présentera ainsi :

```php
return [
  7 => "persons.Responsable scientifique",
]
```

On obtiendra en JSON :

```json
{
  "persons": {
    "Responsable scientifique": ["Valeur de la colonne"]
  }
}
```

<a id="data-composite-1"></a>   
### Donnèes multiples persons/organizations (2.5.x)

Les `persons` et les `organizations` autorisent un paramètrage avancé pour permettre d'extraire des données multiples depuis une même colonne : 

```php
<?php
//
return [
    // (...)
    425 => [
        'key' => "persons.Ingénieur",
        'separator' => ','
    ]
];
```

Le tableau associatif de configuration permet de spécifier le mode de traitement sous la forme `persons.Role` ou `organizations.Role`, ainsi que le séparateur utilisé dans la colonne pour isoler les informations.

Dans l'exemple, la colonne va contenir des ingénieurs séparés par des virgules.

La donnée de colonne `Max Plank, Albert Einstein` produirait : 

```json
{
  "persons": {
    "Ingénieur": [
      "Max Plank",
      "Albert Einstein"
    ]
  }
}
```


### milestones.Type

La clef `milestones` prend pour paramètre le type de jalon trouvé dans la cellule. Si par exemple la colonne 13 contient la date du rapport scientifique, la configuration se présentera ainsi :

```php
return [
  13 => "milestones.Rapport scientifique",
]
```

On obtiendra en JSON :

```json
{
  "milestones": [
      {
        "type": "Rapport scientifique",
        "date": "VALEUR DE LA COLONNE"
      }
  ]
}
```


### payments.POSITIONS

La clef `payments` indique l'emplacement du montant du versement et prends comme premier paramètre la date prévue du versement, et en deuxième paramêtre la date effective sous la forme `payments.PREVU.EFFECTIF`.

#### Exemple 1

|   Activité                | Acronym Projet    | Premier versement (montant)  | Premier versement (prévu)  | 1er versement (effectif)
|---------------------------|-------------------|---------------------------|-------------------------------|-------------------------
| La relativité restreinte  | RELATIV           | 2000.00                | 2017-01-01                       | 2017-01-06

```php
return [
  2 => "payments.1.2",
]
```

Le premier chiffre indique l'emplacement de la colonne contenant la date prévue (une colonne après).
Le deuxième chiffre indique l'emplacement de la colonne content la date effective (deux colonnes après).

On obtiendra : 

```json
{
  "payments": [
     {
        "amount": 20000,
        "date": "2017-01-07",
        "predicted": "2017-01-01"
      }
  ]
}
```

#### exemple 2

Si les colonnes sont dans des ordres différents, il faut prendre comme référence la colonne contenant le montant, ici la 4ème colonne (index 3), et mettre l'emplacement des autres colonnes par rapport à celle ci : 

Ex: 

|   Activité                | Acronym Projet    | Premier versement (prévu)  | Premier versement (montant)  | 1er versement (effectif)
|---------------------------|-------------------|---------------------------|-------------------------------|-------------------------
| La relativité restreinte  | RELATIV           | 2016-01-01                | 2500.50                       | 2016-01-10

Ce qui donne : 

```php
return [
  2 => "payments.-1.1",
]
```

On obtiendra en JSON :

```json
{
  "payments": [
      {
        "amount": 2500.5,
        "date": "2016-01-10",
        "predicted": "2016-01-01"
      }
  ]
}
```

#### Statut

Au moment de l'injection des données JSON dans Oscar, Oscar regardera si une date effective (`date`) est présente (non null), si c'est le cas, le versement créé sera marqué comme EFFECTUÉ, sinon il sera marqué comme PRÉVU.
