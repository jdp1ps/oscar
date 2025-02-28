# Mailer

Le **Mailer** est le système permettant de configurer et gérer la distribution des messages électroniques par *Oscar*.

Depuis la version **2.4.x**, les mails envoient la liste des notifications non-lues dans l'application. [Système de notification](config-notifications.md)

## Configuration

La configuration du mailer est située dans le fichier `config/autoload/local.php`.

Par défaut, le système de mail utilise la configuration :

```php
<?php
return [
    // config/autoload/local.php
    'oscar' => [
        'urlAbsolute' => 'http://localhost:8080',
        'mailer' => [
            'transport' => [
                'type' => 'smtp',
                'host' => 'smtp.domain.tld',
                'port' => 465,
                'username' => 'smithagent',
                'password' => '@m4S!n9 P4$VV0rd',
                'security' => 'ssl',
            ],
            'administrators' => [],
            'from' => [ 'oscar-bot@oscar.fr' => 'Oscar Bot'],
            'copy' => [],
            'send' => false,
            'send_false_exception' => [],
            'template' => realpath(__DIR__.'/../../module/Oscar/view/mail.phtml'),
            'subjectPrefix' => '[OSCAR DEV]'
        ],
    ]
];
```

### administrators

Ce paramètre permet de renseigner les adresses des administrateurs technique de l'application : 

```php
<?php
return [
    // ...
    'oscar' => [
        // ...
        'mailer' => [
            // (...)
            'administrators' => ['stephane.bouvry@unicaen.fr', 'karin.fery@unicaen.fr'],           
        ],
    ]
];
```

Ce paramètre est pour le moment utilisé par la commande `php bin/oscar.php check:mailer` pour distribuer un mail de test lors de la configuration du *mailer*.

> Il sera probablement utilisé par la suite pour des outils de diagnostic

### from, subjectPrefix et copy

Le paramètre `òscar.mailer.from` permet d'indiquer l'expéditeur visible par l'utilisateur lors de la reception du mail.

Le paramètre `òscar.mailer.subjectPrefix` permet d'indiquer un préfixe ajouté à tous les sujets des emails distribués. Cela peut être utile pour distinguer les mails si vous avez plusieurs instances d'Oscar actives simultanément.

Le paramètre `òscar.mailer.copy` n'est pour le moment pas utilisé.


### Modifier le gabarit des mails

Dans la configuration `oscar.mailer`, la clef `template` permet spécifier le gabarit pour mettre en forme les emails. Le fichier par défaut est `module/Oscar/view/mail.phtml`.

```bash
cp module/Oscar/view/mail.phtml data/mail.phtml
```

Puis indiquez dans la configuration l'emplacement du gabarit : 

```php
<?php
return [
    // config/autoload/local.php
    'oscar' => [
        // ...
        'mailer' => [
            '// ...
            'template' => realpath(__DIR__.'/../../data/mail.phtml'),
        ],
    ]
];
```

### urlAbsolute : URL dans les mails

Le paramètre `urlAbsolute` permet à l'utilitaire en ligne de commande de générer des URLs absolues. Ce paramètre est requis si vous avez configuré l'envoi des notifications par email.

```php
<?php
//config/autoload/local.php
return array(
    // ...
    'oscar' => [
        // (...)

        // Utilisé pour la génération des URLs dans les mails en ligne de commande
        'urlAbsolute' => 'http://localhost:8080',
    ],
);
```

La clef `transport` va permettre de configurer le mode d'envoi des mails : SMTP, sendmail et file pour le debug.


#### Envoi SMTP

Le type de transport **smtp** permet d'utiliser un serveur SMTP pour distribuer les mails.

```php
<?php
//config/autoload/local.php
return array(
    // ...
    'oscar' => [
        'mailer' => [
            /**** TRANSPORT (smtp) ****/
            'transport' => [
                'type' => 'smtp',
                'host' => 'smtp.domain.tld',
                'port' => 465,
                'username' => 'smithagent',
                'password' => '@m4S!n9 P4$VV0rd',
                'security' => 'ssl',
            ],
        ]
    ],
    // ...
);
```

#### Envoi Sendmail

Le type de transport **sendmail** permet d'utiliser SENDMAIL/EXIM pour distribuer les mails.

```php
<?php
//config/autoload/local.php
return array(
    // ...
    // Accès BDD
    'oscar' => [
        'mailer' => [
            /**** TRANSPORT (sendmail) ****/
            'transport' => [
                'type' => 'sendmail',
                'cmd' => '/usr/sbin/sendmail -t',
                // EXIM
                // 'cmd' => '/usr/sbin/exim -bs',
            ],
            /****/
        ]
    ],
    // ...
);
```

#### Envoi DEBUG/PreProd (v2.4.x)

Cette dernière option permet de ne pas envoyer les mails, mais de copier les mails sous la forme de fichier dans le chemin indiqué, cette méthode permet de tester les mails générés avant un passage en production :

```php
<?php
//config/autoload/local.php
return array(
    // ...
    // Accès BDD
    'oscar' => [
        'mailer' => [
            /**** TRANSPORT (Fichier) ****/
            'transport' => [
                'type' => 'file',

                // Dossier où sont déposé les fichiers EML
                'path' => realpath(__DIR__.'/../../data/mails'),
            ],
            /****/
        ]
    ],
    // ...
);
```

## Tester le mailer

Vous pouvez lancer le test de la configuration en tapant la commande :

```bash
$ php public/index.php oscar test:mailer
```

## Options de Test/Préprod

Avant de passer en production, vous pouvez utiliser le paramètre `send` sur FALSE pour désactiver la distribution et utiliser le tableau `send_false_exception` pour renseigner les adresses à distribuer :
wrap 
```php
<?php
return [
    // ...
    'oscar' => [
        'urlAbsolute' => 'http://localhost:8080',
        'mailer' => [
            // (...)
            'copy' => [],
            
            // Distribution désactivée
            'send' => false,
            
            // Sauf pour les adresses suivantes : 
            'send_false_exception' => ['stephane.bouvry@unicaen.fr', 'karin.fery@unicaen.fr'],
            
            // (...)
        ],
    ]
];
```
