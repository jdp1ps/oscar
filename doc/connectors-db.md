# Connectors OSCAR DB

*Oscar* permet d'importer et de synchroniser des données concernant les organisations et les personnes à partir de bases de données Oracle.

## Configuration du connecteur

La première étape pour faire fonctionner le connecteur base de données consiste à définir sa configuration.

 - Copier-coller le fichier [config/connectors/organization_db.yml.dist](../config/connectors/organization_db.yml.dist) en le renommant `config/connectors/organization_db.yml`

Ce document prend pour exemple le connecteur des organisations mais le principe est le même pour configurer le connecteur des personnes. Il suffit de remplacer `organization` par `person` à chacune des étapes.

Il faut ensuite définir les différents paramètres permettant de se connecter à la base de données et de récupérer les informations concernant les :

|     Variable      |                 Exemple                        | Description |
| ----------------- | ---------------------------------------------- | ----------- |
| url_organizations |                                                | Non utilisée pour le connecteur BDD |
| url_organization  |                                                | Non utilisée pour le connecteur BDD |
| access_strategy   | Oscar\Connector\Access\ConnectorAccessOracleDB | Indique la classe PHP chargée de se connecter à la BDD et d'exécuter les requêtes. Laisser la valeur par défaut. |
| db_host           | bdd.unicaen.fr                                 | Adresse du serveur de BDD Oracle |
| db_port           | 1521                                           | Port TCP sur lequel le serveur BDD écoute les connexions |
| db_user           | my_user                                        | Nom d'utilisateur pour la connexion à la BDD. Un utilisateur ayant un accès en lecteur seule uniquement à la table des organisations devrait la plupart du temps suffire. |
| db_password       | my_password                                    | Mot de passe pour la connexion à la BDD |
| db_name           | my_dbname                                      | Nom de la base de données |
| db_charset        | AL32UTF8                                       | Codage de caractères de la BDD |
| db_query_single   | SELECT * FROM organization WHERE ID = :p1      | Requête permettant de récupérer une organisation par son identifiant |
| db_query_all      | SELECT * FROM organization                     | Requête permettant de récupérer toutes les organisation |

Pour activer ce nouveau connecteur, il faut ajouter sa déclaration dans le fichier [config/autoload/local.php](../config/autoload/local.php)

```php
<?php
// /config/autoload/local.php
return array(
    'oscar' => [
        'connectors' => [
            // -------------------------------- Synchronisation des structures
            'organization' => [
                'db' => [
                  'class'     => \Oscar\Connector\ConnectorOrganizationDB::class,
                  'params'    => realpath(__DIR__) . '/../connectors/organization_db.yml'
                ]
            ],
            
            // -------------------------------- Synchronisation des personnes
            'person' => [
                'db' => [
                  'class'     => \Oscar\Connector\ConnectorPersonDB::class,
                  'params'    => realpath(__DIR__) . '/../connectors/person_db.yml'
                ]
            ]
        ],
    ]
);
```

## Vérification de la configuration

On peut alors lancer une vérification via la commande `php bin/oscar.php check:config`

Si tout est OK, la commande indique combien d'organisations et de personnes sont trouvées dans la base de données et seront synchronisées lors du lancement de commande `sync`.


## Données attendues pour les organisations

Les requêtes `db_query_single` et `db_query_all` du fichier `organization_db.yml` permettant de récupérer les informations des organisations doivent retourner les colonnes suivantes :

|  Nom colonne    |               Exemple                       | Type                 | Obligatoire |                 Description                                  |
| --------------- | ------------------------------------------- | -------------------- | ----------- | ------------------------------------------------------------ |
| ID              | 1                                           | Nombre entier        | Oui         | Identifiant unique de l'organisation dans la BDD             |
| CODE            | S23                                         | Chaîne de caractères | Oui         | Code unique de l'organisation dans la BDD                    |
| PARENT          | UNIV                                        | Chaîne de caractères |             | Code de l'organisation parente. Peut être `NULL`             |
| LIBELLE_COURT   | MRSH                                        | Chaîne de caractères | Oui         | Description courte de l'organisation                         |
| LIBELLE_LONG    | Maison de la Recherche en Sciences Humaines | Chaîne de caractères | Oui         | Description longue de l'organisation                         |
| UPDATED_AT      | 2022-09-28T12:29:23.000Z                    | Chaîne de caractères |             | Date de dernière mise à jour de la donnée au format ISO 8601 |
| TYPE_RECHERCHE  | USR                                         | Chaîne de caractères |             | Sigle / acronyme du type de structure (par ex. UMR)          |
| CODE_RECHERCHE  | 3486                                        | Chaîne de caractères |             | Identifiant associé au type de structure. TYPE_RECHERCHE + CODE_RECHERCHE formeront le `labintel` (USR3486) |
| TELEPHONE       | +33 2 31 56 62 00                           | Chaîne de caractères |             | Numéro de téléphone                                          |
| SITE_URL        | https://www.unicaen.fr/recherche/mrsh/      | Chaîne de caractères |             | Adresse du site web                                          |
| TYPE            | Service commun                              | Chaîne de caractères |             | Type de la structure                                         |
| RNSR            | 201221521V                                  | Chaîne de caractères |             | Identifiant répertoire national des structures de recherche (RNSR) |
| ADRESSE_POSTALE | {"address1":"Caen Campus 1 - MRSH - F - 1°Etage - SH  154","address2":"Esplanade de la paix","address3":"CS 14032","zipcode":"14032","city":"Caen Cedex 5","country":"France"} | Chaîne de caractères |             | Adresse postale au format JSON. Voir l'exemple pour les champs à indiquer. |

La requête `db_query_single` ne doit retourner qu'une seule ligne.

La requête `db_query_all` ne doit pas avoir de doublon (la valeur de la colonne ID doit être unique).

## Données attendues pour les personnes

Les requêtes `db_query_single` et `db_query_all` du fichier `person_db.yml` permettant de récupérer les informations des personnes doivent retourner les colonnes suivantes :

|       Nom colonne       |               Exemple                       | Type                 | Obligatoire |                 Description                                  |
| ----------------------- | ------------------------------------------- | -------------------- | ----------- | ------------------------------------------------------------ |
| REMOTE_ID               | 1                                           | Nombre entier        | Oui         | Identifiant unique de la personne dans la BDD                |
| LOGIN                   | mdupont                                     | Chaîne de caractères | Oui         | Login unique                                                 |
| PRENOM                  | Martin                                      | Chaîne de caractères | Oui         | Prénom                                                       |
| NOM                     | DUPONT                                      | Chaîne de caractères | Oui         | Nom de famille                                               |
| EMAIL                   | mdupont@unicaen.fr                          | Chaîne de caractères | Oui         | Adresse email                                                |
| CIVILITE                | M.                                          | Chaîne de caractères |             | Titre de civilité                                            |
| LANGAGE                 | fr                                          | Chaîne de caractères |             | Code ISO 639-1 de la langue privilégiée                      |
| STATUT                  | TITULAIRE                                   | Chaîne de caractères |             | Statut (CDI...)                                              |
| AFFECTATION             | Maison de la Recherche en Sciences Humaines | Chaîne de caractères |             | Composante à laquelle la personne est affectée               |
| INM                     | 334                                         | Nombre entier        |             | Indice majoré pour traitement indiciaire                     |
| TELEPHONE               | +33 2 01 02 03 04                           | Chaîne de caractères |             | Numéro de téléphone                                          |
| DATE_EXPIRATION         | 2025-09-30 23:59:59.000                     | DATE Oracle          |             | Date d'expiration du compte utilisateur                      |
| ROLES                   | {"S23":["Gestionnaire de laboratoire"]}     | Chaîne de caractères |             | Rôles au format JSON. Objet JSON dont les clés sont le code des structures et les valeurs sont un tableau des rôles de la personne au sein de cette structure. |
| ADRESSE_PROFESSIONNELLE | {"address1":"Caen Campus 1 - MRSH - F - 1°Etage - SH  149","address2":"Esplanade de la paix","address3":"CS 14032","zipcode":"14032","city":"Caen Cedex 5","country":"France"} | Chaîne de caractères |             | Adresse postale au format JSON. Voir l'exemple pour les champs à indiquer. |

La requête `db_query_single` ne doit retourner qu'une seule ligne.

La requête `db_query_all` ne doit pas avoir de doublon (la valeur de la colonne ID doit être unique).


## Notes pour les développeurs

### Que faire si la requête ne retourne pas exactement les champs attendus, au format attendu ?

Le [ConnectorAccessOracleDB](../module/Oscar/src/Oscar/Connector/Access/ConnectorAccessOracleDB.php) se contente d'exécuter la requête et de retourner un tableau associatif clé => valeur contenant les résultats. Un seul tableau est retourné dans le cas de la requête `db_query_single`, tandis qu'une liste de tableaux (un pour chaque ligne en base de données) est retournée pour la requête `db_query_all`.

C'est dans la méthode `objectFromDBRow` de chaque connecteur ([ConnectorOrganizationDB](../module/Oscar/src/Oscar/Connector/ConnectorOrganizationDB.php) et [ConnectorPersonDB](../module/Oscar/src/Oscar/Connector/ConnectorPersonDB.php)) que va se faire la correspondance entre les valeurs retournées par la requête SQL et l'objet métier à remplir.

S'il est nécessaire d'effectuer un traitement spécifique, il est donc possible de surcharger/étendre ou remplacer la classe `Connector*DB` voulue pour redéfinir une métode `objectFromDBRow` adaptée. Il faut ensuite bien penser à modifier la configuration dans le fichier [config/autoload/local.php](../config/autoload/local.php) pour indiquer la nouvelle classe `Connector*DB` à utiliser.

### Comment utiliser un autre type de base de données que Oracle ?

Le [ConnectorAccessOracleDB](../module/Oscar/src/Oscar/Connector/Access/ConnectorAccessOracleDB.php) ne permet aujourd'hui d'interroger que des bases de données Oracle. Si on souhaite interroger d'autre types de SGBD, il est possible de copier ce fichier et de l'adapter à un autre type de driver de bases de données, par exemple `ConnectorAccessPostgresDB`. Il faudra alors penser à l'indiquer dans les fichiers de configuration des connecteurs (par exemple `config/connectors/organization_db.yml`) :

```yml
# Accès spécifique
access_strategy:  Oscar\Connector\Access\ConnectorAccessPostgresDB
```
