# Numérotation automatique

Par défaut, **Oscar©** utilise un système de numérotation automatique basé sur l'année en cours, puis une chaîne de sépration ("DRI") et enfin un numéro à 5 chiffres incrémenté automatiquement.

Par exemple, la première activité de 2018 sera numérotée **2018DRI00001**, la deuxième **2018DRI00002**, etc...

Il est possible de modifier le formalise de ce numéro en personnalisant la chaîne de séparation. Pour cela, il faut appliquer **2 modifications techniques** : l'une en base de données, l'autre dans la configuration Oscar.


## Modification dans la base de données

La numérotion automatique repose sur une fonction *Postgresql* : `activity_num_auto(integer)`.

Vous pouvez modifier la fonction en cherchant la ligne 

```sql
separator text := 'DRI';
```

Par exemple si vous souhaitez remplacer la chaîne "DRI" par "LAB", la ligne deviendra : 

```sql
separator text := 'LAB';
```

La fonction finale doit ressembler à ça : 

```sql

DECLARE
	activity_record activity;
	year int;
	last_num text;
	num text;
	separator text := 'DRI';
	counter_val int;
BEGIN
    ------------------------------------------------------------------------------------
    -- On récupère l'activité qui va bien
    SELECT * INTO activity_record FROM activity WHERE id = activity_id;

    -- Err : Pas d'activité
    IF activity_record IS NULL THEN
        RAISE EXCEPTION 'Activité % non trouve', activity_id;
    END IF;

    -- Err : Activité déjà numérotée
    IF activity_record.oscarnum IS NOT NULL THEN
        RAISE EXCEPTION 'Cette activité (%) est déjà numérotée', activity_id;
    END IF;
    -------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------
    -- Récupération du plus grand numéro précédent :

    -- On récupère l'année de l'activité (Si elle est null, on utilise l'année courante)
    year := EXTRACT(YEAR FROM activity_record.dateSigned);
    IF year IS NULL THEN
        year = EXTRACT(YEAR FROM activity_record.dateCreated);
    END IF;
    IF year IS NULL THEN
        year = EXTRACT(YEAR FROM CURRENT_TIMESTAMP);
    END IF;

    -- On récupère le dernier numéro pour cette année
    SELECT MAX(oscarNum) INTO last_num FROM activity WHERE oscarnum LIKE year || (separator ||'%');
    
    IF last_num IS NULL THEN
        counter_val := 0;
    ELSE
        counter_val := substring(last_num FROM (5 + char_length(separator)) FOR 5)::int;
    END IF;

    counter_val := counter_val + 1;

    num := CONCAT(year, separator, to_char(counter_val, 'fm00000'));

    UPDATE activity SET oscarNum = num WHERE id = activity_id;

    RETURN num;
END;
```

Ensuite, il faut mettre à jour la configuration Oscar : 


## Modifier la configuration dans Oscar

Oscar dispose d'une option pour savoir la forme de la numérotation, par défaut, il s'attends à trouver l'année, puis la chaîne "DRI", puis 5 nombres. La chaîne "DRI" est configurable en ajoutant une clef `oscar.oscar_num_separator` dans la configuration générale (fichier `./config/autoload/local.php`) : 

```php
<?php

return array(
    // ...
    
    // Oscar
    'oscar' => [
        'oscar_num_separator' => 'LAB',
    ]
);
```

Cette ne configuration, une fois choisie ne doit pas être modifiée. Dans le cas contraire, il faudra penser à mettre à jour les numérotations des activités déjà enregistrées en base de données pour qu'elles respectent toutes le même formalisme.

```sql
-- Mettre à jour la numérotation sous la forme yyyyLABxxxxx
UPDATE activity SET oscarnum = regexp_replace(oscarnum, '(\d{4})(.*)(\d{5})', '\1LAB\3');
```

