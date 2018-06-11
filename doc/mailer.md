# Mailer

Le **Mailer** est le système permettant de configurer et gérer la distribution des messages électroniques par *Oscar*.

Depuis la version **2.4.x**, les mails envoient la liste des notifications non-lues dans l'application. [Système de notification](./notifications.md)


## Configuration

La configuration du mailer est située dans le fichier `config/autoload/local.php`.

Par défaut, le système de mail utilise la configuration : 

```php
<?php
return [
    // ...
    'oscar' => [
        'urlAbsolute' => 'http://localhost:8080',
        'mailer' => [
            'transport' => [
                'type' => 'file',
                'path' => realpath(__DIR__.'/../../data/mails'),
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
        
        'mailer' => [
            'transport' => [
                // (...)
            ],
        ]
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
                'cmd' => '/usr/sbin/sendmail -bs',
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

#### Tester le mailer

Vous pouvez lancer le test de la configuration en tapant la commande : 

```bash
$ php public/index.php oscar test:mailer
``` 