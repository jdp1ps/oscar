# Migration des organisations et des personnes

L'objectif de cette migration est de ne plus passer par Oscar Bridge (connecteur "rest") pour synchroniser les organisations et les personnes dans Oscar mais de se connecter directement à la base de données Octopus (nouveau connecteur "db").

## Migration des organisations

Modifier les scripts SQL dans organization_db.yml car maintenant on se base sur CODE pour identifier les organisations et non plus ID :

```yml
db_query_single: >
  SELECT
    ID, CODE, PARENT, LIBELLE_COURT,
    TO_CHAR(
          CAST (DATE_MODIFICATION AS timestamp) AT TIME ZONE 'UTC',
          'yyyy-mm-dd"T"hh24:mi:ss.ff3"Z"'
      ) UPDATED_AT,
      TYPE_RECHERCHE, CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE,
      RNSR, ADRESSE_POSTALE
  FROM v_oscar_structure WHERE CODE = :p1 ORDER BY UPDATED_AT DESC FETCH FIRST ROW ONLY

db_query_all: >
  /*
  * Requête qui enlève les doublons de la liste les organisations et qui les
  * trie par ordre de profondeur dans l'arborescence des structures.
  * 
  * Cela permet de traiter en premier les organisations parentes, sans voir 
  * apparaître dans la colonne PARENT un code inconnu qui n'aurait pas déjà
  * été rencontré dans la colonne CODE d'une ligne.
  * En théorie toutes les organisations devraient descendre de UNIV (UNICAEN).
  * En pratique, sur OctoPP, 13J-1 et 13J-2 indiquent comme parent 13J, mais
  * pourtant aucun organisation de code 13J n'existe dans OctoPP.
  * On se retrouve ainsi avec trois organisations "racines", UNIV, 13J-1 et
  * 13J-2.
  *  
  * */
  WITH structure_sorted_by_modification AS (
    /*
    * On commence par trier les doublons par date de modification la plus
    * récente.
    * 
    * Pour ce faire, on assigne un numéro de ligne (ROWNUMBER) à chaque ligne
    * concernant un même code d'organisation (doublon).
    * Ainsi toutes les organisations sans doublon auront une seule ligne avec
    * ROWNUMBER égal à 1. Mais pour les CODE qui apparaissent plusieurs fois,
    * la ligne ayant la DATE_MODIFICATION la plus récente aura ROWNUMBER = 1,
    * la suivante ROWNUMBER = 2 et ainsi de suite.
    */
    SELECT
      ID, CODE, PARENT, LIBELLE_COURT, DATE_MODIFICATION, TYPE_RECHERCHE,
      CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE, RNSR,
      ADRESSE_POSTALE,
      ROW_NUMBER() OVER(PARTITION BY CODE ORDER BY DATE_MODIFICATION DESC)
      ROWNUMBER
    FROM v_oscar_structure
  ),
  distinct_structure AS (
  	/*
  	 * Ensuite, on supprime les doublons en ne gardand que la ligne ayant un
  	 * ROWNUMBER égal à 1, c'est-à-dire pour un CODE donné, la ligne ayant la
  	 * DATE_MODIFICATION la plus récente.
  	 */
    SELECT ID, CODE, PARENT, LIBELLE_COURT, DATE_MODIFICATION, TYPE_RECHERCHE,
      CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE, RNSR,
      ADRESSE_POSTALE
    FROM structure_sorted_by_modification WHERE ROWNUMBER = 1
  ),
  niveau_structure (ID, CODE, PARENT, LIBELLE_COURT, DATE_MODIFICATION,
    TYPE_RECHERCHE, CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE,
    RNSR, ADRESSE_POSTALE, NIVEAU) AS
  (
  	/*
  	 * Maintenant que nous avons la liste des organisations sans doublons, on
  	 * va ajouter à chaque ligne une colonne NIVEAU qui indique son niveau dans
  	 * la hiérarchie des organisations.
  	 * Par exemple, UNIV (UNICAEN) sera de niveau 0 (racine) tandis que C68
  	 * (DSI dont le parent est UNIV) sera de niveau 1, C68F (DSI
  	 * DEVELOPPEMENT dont le parent est C68 - DSI) sera de niveau 2 et ainsi de
  	 * suite.
  	 */
  
    /*
    * On commence par lister les organisations n'ayant pas de parent et par
    * leur attribuer le niveau 0.
    */
    SELECT  ID, CODE, PARENT, LIBELLE_COURT, DATE_MODIFICATION, TYPE_RECHERCHE,
      CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE, RNSR,
      ADRESSE_POSTALE, 0
    FROM  distinct_structure
    WHERE PARENT IS NULL OR PARENT NOT IN (SELECT CODE FROM v_oscar_structure)
    
    UNION ALL
    
    /*
     * Et on ajoute ensuite toutes les autres organisations, en leur attribuant
     * comme niveau celui de leur parent auquel on ajoute 1.
     */
    SELECT  p.ID, p.CODE, p.PARENT, p.LIBELLE_COURT, p.DATE_MODIFICATION,
      p.TYPE_RECHERCHE, p.CODE_RECHERCHE, p.LIBELLE_LONG, p.TELEPHONE,
      p.SITE_URL, p.TYPE, p.RNSR, p.ADRESSE_POSTALE, a.NIVEAU + 1
    FROM    distinct_structure p 
    JOIN    niveau_structure a
    ON      p.PARENT = a.CODE
  )
  /*
   * Enfin, on liste les organisations sans doublons par ordre de niveau puis de
   * code.
   * On ne sélectionne que les champs dont on a besoin. On convertit le champ
   * DATE_MODIFICATION au format ISO 8601.
   */
  SELECT
    CODE AS ID, CODE, PARENT, LIBELLE_COURT,
    TO_CHAR(
          CAST (DATE_MODIFICATION AS timestamp) AT TIME ZONE 'UTC',
  'yyyy-mm-dd"T"hh24:mi:ss.ff3"Z"'
      ) UPDATED_AT,
      TYPE_RECHERCHE, CODE_RECHERCHE, LIBELLE_LONG, TELEPHONE, SITE_URL, TYPE,
      RNSR, ADRESSE_POSTALE
  FROM niveau_structure
  ORDER BY NIVEAU, CODE
```

Ensuite, lancer le script SQL qui va modifier les organisations pour remplacer la clé du connecteur "rest" par "db" :

```sql
UPDATE
	organization o
SET connectors = replace(connectors, 's:4:"rest"', 's:2:"db"')
WHERE
	code IS NOT NULL
	AND code != ''
	AND connectors LIKE
        CONCAT('%s:4:"rest";s:', CHAR_LENGTH(code),	':"', code, '";%');
```

Enfin, lancer la synchronisation des organisations :

```bash
php bin/oscar.php organizations:sync db
```

Il sera sans doute nécessaire de forcer la synchronisation, car la date d'update n'est pas forcément cohérente. On dirait que Oscar Bridge met toujours une date d'update à la date du jour alors qu'Octopus met bien à jour la date uniquement quand l'enregistrement a été mis à jour.

## Migration des personnes

Modifier les scripts SQL dans person_db.yml car maintenant on se base sur LOGIN pour identifier les personnes et non plus REMOTE_ID :

```yml
db_query_single: >
  SELECT LOGIN AS REMOTE_ID, PRENOM, NOM, CIVILITE, LOGIN, EMAIL, LANGAGE, STATUT,
    AFFECTATION, INM, TELEPHONE, DATE_EXPIRATION, ADRESSE_PROFESSIONNELLE,
    ROLES
  FROM v_oscar_user
  WHERE LOGIN = :p1
  ORDER BY EMAIL, ADRESSE_PROFESSIONNELLE FETCH FIRST ROW ONLY

db_query_all: >
  /* 
  * Requête qui enlève les doublons de la liste des utilisateurs.
  * 
  * Vu que pour un même LOGIN (donc un utilisateur) on peut avoir jusqu'à
  * 5 lignes, cette requête va permettre de supprimer les doublons en ne gardant
  * que la ligne qui contient le plus d'information :
  *  - pour l'adresse email, une ligne avec une adresse de type
  *     prenom.nom@unicaen.fr est prioritaire sur une adresse
  *     00000000@etu.unicaen.fr
  *  - une ligne avec une ADRESSE_PROFESSIONNELLE est prioritaire sur une ligne
  *    sans
  * 
  * */
  WITH users_with_row_number_by_login AS (
    /*
    * On commence par regrouper les utilisateurs par LOGIN en attribuant
    * à chaque ligne dupliquée un numéro d'ordre et en triant par EMAIL
    * et ADRESSE_PROFESSIONNELLE
    *  */
    SELECT
      LOGIN AS REMOTE_ID, PRENOM, NOM, CIVILITE, LOGIN, EMAIL, LANGAGE, STATUT,
      AFFECTATION, INM, TELEPHONE, DATE_EXPIRATION, ADRESSE_PROFESSIONNELLE,
      ROLES, ROW_NUMBER() OVER(PARTITION BY LOGIN ORDER BY EMAIL,
      ADRESSE_PROFESSIONNELLE) ROWNUMBER
    FROM v_oscar_user
  ),
  distinct_user AS (
   /* Ensuite pour supprimer les doublons, pour chaque LOGIN on ne garde que
    * la première ligne, celle qui contient le plus d'informations pertinentes
    * */
    SELECT * FROM users_with_row_number_by_login WHERE ROWNUMBER = 1
  )
  SELECT * FROM distinct_user ORDER BY LOGIN
```

Supprimer en base de données les persons qui ne sont pas utilisées, c'est-à-dire celles qui ne sont pas liées à des entités métier :

```sql
DELETE FROM person WHERE id IN (
SELECT p.id FROM person p 
LEFT JOIN activityperson ap ON p.id = ap.person_id
LEFT JOIN administrativedocument ad ON p.id = ad.person_id
LEFT JOIN contractdocument cd ON p.id = cd.person_id 
LEFT JOIN notificationperson np ON p.id = np.person_id 
LEFT JOIN organizationperson op ON p.id = op.person_id 
LEFT JOIN person_activity_validator_adm pava ON p.id = pava.person_id 
LEFT JOIN person_activity_validator_prj pavp ON p.id = pavp.person_id 
LEFT JOIN person_activity_validator_sci pavs ON p.id = pavs.person_id 
LEFT JOIN persons_documents pd ON p.id = pd.person_id 
LEFT JOIN projectmember pm ON p.id = pm.person_id 
LEFT JOIN recalldeclaration rd ON p.id = rd.person_id 
LEFT JOIN recallexception re ON p.id = re.person_id 
LEFT JOIN referent r ON p.id = r.person_id 
LEFT JOIN timesheet t ON p.id = t.person_id 
LEFT JOIN timesheetsby tb ON p.id = tb.person_id 
LEFT JOIN validationperiod_adm vpa ON p.id = vpa.person_id 
LEFT JOIN validationperiod_prj vpp ON p.id = vpp.person_id 
LEFT JOIN validationperiod_sci vps ON p.id = vps.person_id 
LEFT JOIN workpackageperson wp ON p.id = wp.person_id 
WHERE
	ap.person_id IS NULL
	AND ad.person_id IS NULL
	AND cd.person_id IS NULL
	AND np.person_id IS NULL
	AND op.person_id IS NULL
	AND pava.person_id IS NULL
	AND pavp.person_id IS NULL
	AND pavs.person_id IS NULL
	AND pd.person_id IS NULL
	AND pm.person_id IS NULL
	AND rd.person_id IS NULL
	AND re.person_id IS NULL
	AND r.person_id IS NULL
	AND t.person_id IS NULL
	AND tb.person_id IS NULL
	AND vpa.person_id IS NULL
	AND vpp.person_id IS NULL
	AND vps.person_id IS NULL
	AND wp.person_id IS NULL);
```

Mettre à jour le connecteur des personnes en insérant uniquement la clé "db" avec comme valeur le ldaplogin :

```sql
UPDATE person
SET connectors = 
    CONCAT('a:1:{s:2:"db";s:', CHAR_LENGTH(ladaplogin), ':"', ladaplogin, '";}')
WHERE
    ladaplogin IS NOT NULL
	AND ladaplogin != '';
```

Enfin, lancer la synchronisation des personnes :

```bash
php bin/oscar.php persons:sync db
```
