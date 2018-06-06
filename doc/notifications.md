# Notifications

Les notifications permettent d'informer les utilisateurs dans changement qui interviennent dans les activités dont ils ont la charge.

Si vous configurez le [Système de mail](./mailer.md), vous pourrez envoyer periodiquement un récapitulatif des notifications par mail aux utilisateurs.

## Configuration

La configuration des notifications est présente dans le fichier `config/autoload/local.php`.

L'option **fixed** permet de configurer des envois imposés sous la forme d'un tableau de chaîne (3 premières lettres du jour avec majuscule et heure au format 24 heures).

```php
<?php
return [
    'oscar' => [
        /*** Notifications ***/
        'notifications' => [
            // Envoi automatique
            'fixed' => ['Mer8'] // ex: IMPOSE une notification chaque mercredis à 8 heures
        ]  
    ]
];
```

## Script d'envoi

La commande `php public/index.php oscar notifications:mails:persons` permet de déclencher la procédure d'envoi des mails : 

```bash
$ php public/index.php oscar notifications:mails:persons
> Notifications des inscrits à 'DayX'
 X personne(s) ont des notifications non-lues
etc...
```

## CRON

Vous pouvez utiliser **crontab** pour déclencher cette procédure automatiquement en ajoutant par exemple cette configuration : 

```bash
# Edition du crontab
$ crontab -e
```

```cron
# Déclenchement de la procédure toutes les heures
5 * * * * php /path/to/oscar/public/index.php oscar notifications:mails:persons
```
