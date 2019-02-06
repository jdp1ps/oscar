# Numérotation automatique

Par défaut, **Oscar©** utilise un système de numérotation automatique basé sur l'année en cours, puis une chaîne de sépration ("DRI") et enfin un numéro à 5 chiffres incrémenté automatiquement.

Par exemple, la première activité de 2018 sera numérotée **2018DRI00001**, la deuxième **2018DRI00002**, etc...

Il est possible de modifier le formalise de ce numéro en personnalisant la chaîne de séparation. Pour cela, il faut appliquer **2 modifications techniques** : l'une en base de données, l'autre dans la configuration Oscar.


## Modification dans la base de données

La numérotion automatique repose sur une fonction *Postgresql* : `activity_num_auto(integer)`.

Vous pouvez modifier la fonction en cherchant la ligne 

```sql
num := CONCAT(year, 'DRI', to_char(counter_val, 'fm00000'));
```

Par exemple si vous souhaitez remplacer la chaîne "DRI" par "LAB", la ligne deviendra : 

```sql
num := CONCAT(year, 'LAB', to_char(counter_val, 'fm00000'));
```

> La chaîne de séparation est prévue pour avoir 3 caractères, cette taille pourra étre étendue dans la version 2.9 "Matrix"

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

