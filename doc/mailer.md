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

### administrators

Ce paramètre permet de renseigner les adresses des administrateurs technique de l'application : 

```php
<?php
return [
    // ...
    'oscar' => [
        'urlAbsolute' => 'http://localhost:8080',
        'mailer' => [
            // (...)
            'administrators' => ['stephane.bouvry@unicaen.fr', 'karin.fery@unicaen.fr'],           
            // (...)
        ],
    ]
];
```

Ce paramètres est pour le moment utilisé par la commande `php pubic/index.php oscar test:mailer` pour distribuer un mail de test lors de la configuration du *mailer*.

> Il sera probablement utilisé par la suite pour des outils de diagnostic

### from, subjectPrefix et copy

Le paramètre `òscar.mailer.from` permet d'indiquer l'expéditeur visible par l'utilisateur lors de la reception du mail.

Le paramètre `òscar.mailer.subjectPrefix` permet d'indiquer un préfixe ajouté à tous les sujets des emails distribués. Cela peut être utile pour distinguer les mails si vous avez plusieurs instances d'Oscar actives simultanément.

Le paramètre `òscar.mailer.copy` n'est pour le moment pas utilisé.


### Gabarit

Dans la configuration `oscar.mailer`, la clef `template` permet spécifier le gabarit pour mettre en forme les emails.

```php
<body style="background:#EFEFEF; font-family: Helvetica, Arial, sans-serif">
<table border="0" style="border: none; width: 90%; margin: 14px 5%; tab" cellpadding="0" cellspacing="0" width="90%">
    <tr style="background: #455790">
        <td width="32" style="border: none; padding: 8px">
            <img src="https://oscar.unicaen.fr/images/oscar-white.png" alt="OSCAR" style="height: 32px;">
        </td>
        <td style="font-size: 22px;">
            <span style="color: #EEE; text-shadow: -1px 1px 2px #000000"><?= $title ? : 'Rapport' ?></span>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 1em 4em; background: white"><?= $body ?></td>
    </tr>

    <tr>
        <td colspan="2" style="padding: 2em 4em; background: white">
            <div style="width: 75%; border-top: dotted 1px #999999; padding: .5em; font-size: .75em; color: #777777; margin: 0 auto; text-align: center">
                <p>Ceci est un mail automatique, merci de <strong>ne pas y répondre.</strong>
                Pour ne plus recevoir des notifications, vous pouvez vous rendre dans les paramètres de votre compte pour ajuster la fréquence des envois des courriels.</p>
            </div>
    </tr>

    <tr style="background: #455790; font-size: 12px; color: #efefef;  text-shadow: -1px 1px 2px #000000">
        <td colspan="2" style="padding: 1em 4em; text-align: center">Oscar<sup>©</sup>, 2015-2018 - Université de Normandie </td>
    </tr>
</table>
</body>
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

## Tester le mailer

Vous pouvez lancer le test de la configuration en tapant la commande :

```bash
$ php public/index.php oscar test:mailer
```

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
