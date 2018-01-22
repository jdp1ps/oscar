<?php
/**
 * UnicaenAuth Global Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    /**
     * Flag indiquant si l'utilisateur authenitifié avec succès via l'annuaire LDAP doit
     * être enregistré/mis à jour dans la table des utilisateurs de l'appli.
     */
    'save_ldap_user_in_database' => true,
    /**
     * Enable registration
     * Allows users to register through the website.
     * Accepted values: boolean true or false
     */
    'enable_registration' => false,
    'enable_privileges' => true,
);

$config = [
    'unicaen-auth' => $settings,
    'zfcuser' => [
        $k = 'enable_registration' => isset($settings[$k]) ? $settings[$k] : false,
        'user_entity_class' => 'Oscar\Entity\Authentification',
    ],
    'bjyauthorize' => [
        'identity_provider' => 'UnicaenAuth\Provider\Identity\Chain',

    ]
];

if ($settings['enable_privileges']) {
    $privileges = [
        'bjyauthorize' => [
            'guards' => [
                'UnicaenAuth\Guard\PrivilegeController' => [
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['index'],
                        'privileges' => [
                            \UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_VISUALISATION,
                            \UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        ],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['roles'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_VISUALISATION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['privileges'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_VISUALISATION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['role-edition', 'role-suppression'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_EDITION],
                    ],
                    [
                        'controller' => 'UnicaenAuth\Controller\Droits',
                        'action' => ['privileges-modifier'],
                        'privileges' => [\UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_EDITION],
                    ],
                ],
            ],
        ],
        'navigation' => [
            // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
            'default' => [
                // And finally, here is where we define our page hierarchy
                'home' => [
                    'pages' => [
                        'droits' => [
                            'label' => 'Droits d\'accès',
                            'title' => 'Gestion des droits d\'accès',
                            'route' => 'droits',
                            'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                'index'),
                            'pages' => [
                                'roles' => [
                                    'label' => "Rôles",
                                    'title' => "Gestion des rôles",
                                    'route' => 'droits/roles',
                                    'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                        'roles'),
                                    'withtarget' => true,
                                ],
                                'privileges' => [
                                    'label' => "Privilèges",
                                    'title' => "Gestion des privilèges",
                                    'route' => 'droits/privileges',
                                    'resource' => \UnicaenAuth\Guard\PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits',
                                        'privileges'),
                                    'withtarget' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
} else {
    $privileges = [];
}

return array_merge_recursive($config, $privileges);

/**
 * You do not need to edit below this line
 *
 * return array(
 * 'unicaen-auth' => $settings,
 * 'zfcuser'      => array(
 * $k='enable_registration' => isset($settings[$k]) ? $settings[$k] : false,
 * 'user_entity_class' => 'Oscar\Entity\Authentification',
 * 'enable_user_state' => true // Permet de gérer l'état du compte
 * ),
 *
 * );*/