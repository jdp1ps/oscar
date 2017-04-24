<?php

use UnicaenAuth\Provider\Privilege\Privileges;

$settings = [

    /**
     * Fournisseurs d'identité.
     */
    'identity_providers'  => [
        300 => 'UnicaenAuth\Provider\Identity\Basic', // en 1er
        200 => 'UnicaenAuth\Provider\Identity\Db',    // en 2e
        100 => 'UnicaenAuth\Provider\Identity\Ldap',  // en 3e @deprecated
    ],
];

return [
    'zfcuser'         => [
        /**
         * Enable registration
         * Allows users to register through the website.
         * Accepted values: boolean true or false
         */
        'enable_registration'     => true,
        /**
         * Modes for authentication identity match
         * Specify the allowable identity modes, in the order they should be
         * checked by the Authentication plugin.
         * Default value: array containing 'email'
         * Accepted values: array containing one or more of: email, username
         */
        'auth_identity_fields'    => ['username', 'email'],
        /**
         * Login Redirect Route
         * Upon successful login the user will be redirected to the entered route
         * Default value: 'zfcuser'
         * Accepted values: A valid route name within your application
         */
        'login_redirect_route'    => 'home',
        /**
         * Logout Redirect Route
         * Upon logging out the user will be redirected to the enterd route
         * Default value: 'zfcuser/login'
         * Accepted values: A valid route name within your application
         */
        'logout_redirect_route'   => 'home',
        /**
         * Enable Username
         * Enables username field on the registration form, and allows users to log
         * in using their username OR email address. Default is false.
         * Accepted values: boolean true or false
         */
        'enable_username'         => false,
        /**
         * Enable Display Name
         * Enables a display name field on the registration form, which is persisted
         * in the database. Default value is false.
         * Accepted values: boolean true or false
         */
        'enable_display_name'     => true,
        /**
         * Authentication Adapters
         * Specify the adapters that will be used to try and authenticate the user
         * Default value: array containing 'ZfcUser\Authentication\Adapter\Db' with priority 100
         * Accepted values: array containing services that implement 'ZfcUser\Authentication\Adapter\ChainableAdapter'
         */
        'auth_adapters'           => [
            300 => 'UnicaenAuth\Authentication\Adapter\Ldap', // notifié en 1er
            200 => 'UnicaenAuth\Authentication\Adapter\Db',   //         ensuite (si échec d'authentification Ldap)
            100 => 'UnicaenAuth\Authentication\Adapter\Cas',  //         ensuite (si échec d'authentification Db)
        ],

        // telling ZfcUser to use our own class
        'user_entity_class'       => 'UnicaenAuth\Entity\Db\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
    ],
    'bjyauthorize'    => [

        /* role providers simply provide a list of roles that should be inserted
         * into the Zend\Acl instance. the module comes with two providers, one
         * to specify roles in a config file and one to load roles using a
         * Zend\Db adapter.
         */
        'role_providers'    => [
            /**
             * 2 rôles doivent systématiquement exister dans les ACL :
             * - le rôle par défaut 'guest', c'est le rôle de tout utilisateur non authentifié.
             * - le rôle 'user', c'est le rôle de tout utilisateur authentifié.
             */
            'UnicaenAuth\Provider\Role\Config'   => [
                'guest' => ['name' => "Non authentifié(e)", 'selectable' => false, 'children' => [
                    'user' => ['name' => "Authentifié(e)", 'selectable' => false],
                ]],
            ],
        ],

        // strategy service name for the strategy listener to be used when permission-related errors are detected
        //    'unauthorized_strategy' => 'BjyAuthorize\View\RedirectionStrategy',
        'unauthorized_strategy' => 'UnicaenAuth\View\RedirectionStrategy',

        /* Currently, only controller and route guards exist
         */
        'guards'                => [
            /* If this guard is specified here (i.e. it is enabled), it will block
             * access to all controllers and actions unless they are specified here.
             * You may omit the 'action' index to allow access to the entire controller
             */
            'BjyAuthorize\Guard\Controller'         => [
                ['controller' => 'index', 'action' => 'index', 'roles' => []],
                ['controller' => 'zfcuser', 'roles' => []],
                ['controller' => 'Application\Controller\Index', 'roles' => []],

                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'etab', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'apropos', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'contact', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'plan', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'mentions-legales', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'informatique-et-libertes', 'roles' => []],
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'refresh-session', 'roles' => []],
                ['controller' => 'UnicaenAuth\Controller\Utilisateur', 'action' => 'selectionner-profil', 'roles' => []],
            ],
        ],
    ],
    'unicaen-auth'    => $settings,
    'doctrine'        => [
        'driver' => [
            // overriding zfc-user-doctrine-orm's config
            'zfcuser_entity'  => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [
                    __DIR__ . '/../src/UnicaenAuth/Entity/Db',
                ],
            ],
            'orm_auth_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/UnicaenAuth/Entity/Db',
                ],
            ],
            'orm_default'     => [
                'class'   => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => [
                    'UnicaenAuth\Entity\Db' => 'zfcuser_entity',
                    'UnicaenAuth\Entity\Db' => 'orm_auth_driver',
                ],
            ],
        ],
    ],
    'view_manager'    => [
        'template_map'        => [
            'error/403' => __DIR__ . '/../view/error/403.phtml',
        ],
        'template_path_stack' => [
            'unicaen-auth' => __DIR__ . '/../view',
        ],
    ],
    'translator'      => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'zfcuser'     => [
                'type'          => 'Literal',
                'priority'      => 1000,
                'options'       => [
                    'route'    => '/auth',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'login'    => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/connexion',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'login',
                            ],
                        ],
                    ],
                    'logout'   => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/deconnexion',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'logout',
                            ],
                        ],
                    ],
                    'register' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/creation-compte',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'register',
                            ],
                        ],
                    ],
                ],
            ],
            'utilisateur' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/utilisateur',
                    'defaults' => [
                        '__NAMESPACE__' => 'UnicaenAuth\Controller',
                        'controller'    => 'Utilisateur',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'default' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/:action[/:id]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]*',
                            ],
                            'defaults'    => [
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
            'droits'      => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/droits',
                    'defaults' => [
                        '__NAMESPACE__' => 'UnicaenAuth\Controller',
                        'controller'    => 'Droits',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'roles'      => [
                        'type'          => 'Segment',
                        'may_terminate' => true,
                        'options'       => [
                            'route'    => '/roles',
                            'defaults' => [
                                'action' => 'roles',
                            ],
                        ],
                        'child_routes'  => [
                            'edition'     => [
                                'type'          => 'Segment',
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/edition[/:role]',
                                    'constraints' => [
                                        'role' => '[0-9]*',
                                    ],
                                    'defaults'    => [
                                        'action' => 'role-edition',
                                    ],
                                ],
                            ],
                            'suppression' => [
                                'type'          => 'Segment',
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/suppression/:role',
                                    'constraints' => [
                                        'role' => '[0-9]*',
                                    ],
                                    'defaults'    => [
                                        'action' => 'role-suppression',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'privileges' => [
                        'type'          => 'Literal',
                        'may_terminate' => true,
                        'options'       => [
                            'route'    => '/privileges',
                            'defaults' => [
                                'action' => 'privileges',
                            ],
                        ],
                        'child_routes'  => [
                            'modifier' => [
                                'type'          => 'Segment',
                                'may_terminate' => true,
                                'options'       => [
                                    'route'    => '/modifier',
                                    'defaults' => [
                                        'action' => 'privileges-modifier',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    // All navigation-related configuration is collected in the 'navigation' key
    'navigation'      => [
        // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
        'default' => [
            // And finally, here is where we define our page hierarchy
            'home' => [
                'pages' => [
                    'login'    => [
                        'label'   => _("Connexion"),
                        'route'   => 'zfcuser/login',
                        'visible' => false,
                    ],
                    'register' => [
                        'label'   => _("Enregistrement"),
                        'route'   => 'zfcuser/register',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases'            => [
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
            'UnicaenAuth\Privilege\PrivilegeProvider'   => 'UnicaenAuth\Service\Privilege',

            'unicaen-auth_user_service'               => 'UnicaenAuth\Service\User', // pour la compatibilité
            'authUserContext'                         => 'UnicaenAuth\Service\UserContext', // pour la compatibilité
        ],
        'invokables'         => [
            'UnicaenAuth\Authentication\Storage\Db'   => 'UnicaenAuth\Authentication\Storage\Db',
            'UnicaenAuth\Authentication\Storage\Ldap' => 'UnicaenAuth\Authentication\Storage\Ldap',
            'UnicaenAuth\View\RedirectionStrategy'    => 'UnicaenAuth\View\RedirectionStrategy',
            'UnicaenAuth\Service\UserContext'         => 'UnicaenAuth\Service\UserContext',
            'UnicaenAuth\Service\User'                => 'UnicaenAuth\Service\User',
            'UnicaenAuth\Service\Privilege'           => 'UnicaenAuth\Service\PrivilegeService',
            'UnicaenAuth\Service\CategoriePrivilege'  => 'UnicaenAuth\Service\CategoriePrivilegeService',
            'UnicaenAuth\Service\Role'                => 'UnicaenAuth\Service\RoleService',
        ],
        'abstract_factories' => [
            'UnicaenAuth\Authentication\Adapter\AbstractFactory',
        ],
        'factories'          => [
            'unicaen-auth_module_options'              => 'UnicaenAuth\Options\ModuleOptionsFactory',
            'zfcuser_auth_service'                     => 'UnicaenAuth\Authentication\AuthenticationServiceFactory',
            'UnicaenAuth\Authentication\Storage\Chain' => 'UnicaenAuth\Authentication\Storage\ChainServiceFactory',
            'UnicaenAuth\Provider\Identity\Chain'      => 'UnicaenAuth\Provider\Identity\ChainServiceFactory',
            'UnicaenAuth\Provider\Identity\Ldap'       => 'UnicaenAuth\Provider\Identity\LdapServiceFactory',
            'UnicaenAuth\Provider\Identity\Db'         => 'UnicaenAuth\Provider\Identity\DbServiceFactory',
            'UnicaenAuth\Provider\Identity\Basic'      => 'UnicaenAuth\Provider\Identity\BasicServiceFactory',
            'UnicaenAuth\Provider\Role\Config'         => 'UnicaenAuth\Provider\Role\ConfigServiceFactory',
            'UnicaenAuth\Provider\Role\DbRole'         => 'UnicaenAuth\Provider\Role\DbRoleServiceFactory',
            'UnicaenAuth\Provider\Role\Username'       => 'UnicaenAuth\Provider\Role\UsernameServiceFactory',
            'BjyAuthorize\Service\Authorize'           => 'UnicaenAuth\Service\AuthorizeServiceFactory', // surcharge!!!
        ],
        'initializers'       => [
            'UnicaenAuth\Service\UserAwareInitializer',
        ],
    ],

    'controllers'   => [
        'invokables' => [
            'UnicaenAuth\Controller\Utilisateur' => 'UnicaenAuth\Controller\UtilisateurController',
            'UnicaenAuth\Controller\Droits'      => 'UnicaenAuth\Controller\DroitsController',
        ],
    ],

    'form_elements' => [
        'invokables' => [
            'UnicaenAuth\Form\Droits\Role' => 'UnicaenAuth\Form\Droits\RoleForm',
        ],
    ],

    'view_helpers'  => [
        'factories'  => [
            'userConnection'             => 'UnicaenAuth\View\Helper\UserConnectionFactory',
            'userCurrent'                => 'UnicaenAuth\View\Helper\UserCurrentFactory',
            'userStatus'                 => 'UnicaenAuth\View\Helper\UserStatusFactory',
            'userProfile'                => 'UnicaenAuth\View\Helper\UserProfileFactory',
            'userInfo'                   => 'UnicaenAuth\View\Helper\UserInfoFactory',
            'userProfileSelect'          => 'UnicaenAuth\View\Helper\UserProfileSelectFactory',
            'userProfileSelectRadioItem' => 'UnicaenAuth\View\Helper\UserProfileSelectRadioItemFactory',
        ],
        'invokables' => [
            'appConnection' => 'UnicaenAuth\View\Helper\AppConnection',
        ],
    ],
];