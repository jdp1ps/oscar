# MACCLANE : Patch LDAP
## 6 mai 2021

Ce patch introduit un FIX/Up sur la partie authentification de Oscar. 
Il simplifie la configuration d'authentification multiple 
 - CAS
 - LDAP / Local(Base de données)
 - Shibboleth

Ce patch résout également certains problème d'authentification liè au rôles LDAP.

La mise en place de ce patch implique des changements sur la configuration de l'authentification. Voici le **contenu minimal** attendu dans le fichier `config/autoload/unicaen-auth.local.php`

> **Important** : Toutes les clefs doivent être présentent dans le fichier, même si la méthode de connexion n'est pas utilisée. Pour activer/désactiver une méthode de configuration, modifiez simplement la valeur `enable` sur `true/false` selon les besoins.

```php
<?php
// ./config/autoload/unicaen-auth.local.php
$settings = array(
    // LDAP / DB
    'local' => [
        'order' => 2,
        'enabled' => true,
        'db' => [
            'enabled' => true,
        ],
        'ldap' => [
            'enabled' => true,
        ],
    ],

     // Authentification via la fédération d'identité (Shibboleth).
    'shib' => [
        'order' => 4,
        'enabled' => false,
        'logout_url' => '/Shibboleth.sso/Logout?return=', // NB: '?return=' semble obligatoire!
        'aliases' => [
            'eppn'                   => 'HTTP_EPPN',
            'mail'                   => 'HTTP_MAIL',
            'eduPersonPrincipalName' => 'HTTP_EPPN',
            'supannEtuId'            => 'HTTP_SUPANNETUID',
            'supannEmpId'            => 'HTTP_SUPANNEMPID',
            'supannCivilite'         => 'HTTP_SUPANNCIVILITE',
            'displayName'            => 'HTTP_DISPLAYNAME',
            'sn'                     => 'HTTP_SN',
            'givenName'              => 'HTTP_GIVENNAME',
        ],
    ],
    
    // CAS
    'cas' => [
        'order' => 1,
        'enabled' => false,
        'connection' => [
            'default' => [
                'params' => [
                    'hostname' => 'host.domain.fr',
                    'port'     => 443,
                    'version'  => "2.0",
                    'uri'      => "",
                    'debug'    => false,
                ],
            ],
        ]
    ],
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
```