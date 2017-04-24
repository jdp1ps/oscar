<?php
namespace UnicaenApp;

return [
    'asset_manager'   => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            // Base “route”, which describes the base match needed, the root of the tree
            'home'             => [
                // The Literal route is for doing exact matching of the URI path
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            // A propos
            'apropos'          => [
                'type'     => 'Zend\Mvc\Router\Http\Literal',
                'options'  => [
                    'route'    => '/apropos',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'apropos',
                    ],
                ],
                'priority' => 9999,
            ],
            // Contact
            'contact'          => [
                'type'     => 'Zend\Mvc\Router\Http\Literal',
                'options'  => [
                    'route'    => '/contact',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'contact',
                    ],
                ],
                'priority' => 9999,
            ],
            // Plan de navigation
            'plan'             => [
                'type'     => 'Zend\Mvc\Router\Http\Literal',
                'options'  => [
                    'route'    => '/plan',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'plan',
                    ],
                ],
                'priority' => 9999,
            ],
            // Mentions légales
            'mentions-legales' => [
                'type'     => 'Zend\Mvc\Router\Http\Literal',
                'options'  => [
                    'route'    => '/mentions-legales',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'mentions-legales',
                    ],
                ],
                'priority' => 9999,
            ],
            // Informatique et libertés
            'il'               => [
                'type'     => 'Zend\Mvc\Router\Http\Literal',
                'options'  => [
                    'route'    => '/informatique-et-libertes',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'informatique-et-libertes',
                    ],
                ],
                'priority' => 9999,
            ],
            // Rafraîchissement de la session
            'refresh-session'  => [
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/refresh-session',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Application',
                        'action'     => 'refresh-session',
                    ],
                ],
            ],
            'cache' => [
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/cache',
                    'defaults' => [
                        'controller' => 'UnicaenApp\Controller\Cache',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'js' => [
                        'type'    => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/js[/:version]',
                            'defaults' => [
                                'action' => 'js'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'css' => [
                        'type'    => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/css[/:version]',
                            'defaults' => [
                                'action' => 'css'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application'      => [
                // The Literal route is for doing exact matching of the URI path
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/application',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
                // Hints to the router that no other segments will follow it
                'may_terminate' => true,
                // Additional routes that stem from the base “route” (i.e., build from it)
                'child_routes'  => [
                    'default' => [
                        // A Segment route allows matching any segment of a URI path
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults'    => [
//                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories'          => [
            'translator'                  => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'navigation'                  => 'Zend\Navigation\Service\DefaultNavigationFactory',
            // service de gestion de la session
            'Zend\Session\SessionManager' => 'UnicaenApp\Session\SessionManagerFactory',
            // service d'accès aux options de config de ce module
            'unicaen-app_module_options'  => 'UnicaenApp\Options\ModuleOptionsFactory',
            // mapper d'accès aux individus de l'annuaire LDAP
            'ldap_people_mapper'          => 'UnicaenApp\Mapper\Ldap\PeopleFactory',
            // mapper d'accès aux groupes de l'annuaire LDAP
            'ldap_group_mapper'           => 'UnicaenApp\Mapper\Ldap\GroupFactory',
            // mapper d'accès aux structures de l'annuaire LDAP
            'ldap_structure_mapper'       => 'UnicaenApp\Mapper\Ldap\StructureFactory',
            // service de manipulation des individus de l'annuaire LDAP
            'ldap_people_service'         => 'UnicaenApp\Service\Ldap\PeopleFactory',
            // service de manipulation des groupes de l'annuaire LDAP
            'ldap_group_service'          => 'UnicaenApp\Service\Ldap\GroupFactory',
            // service de manipulation des structures de l'annuaire LDAP
            'ldap_structure_service'      => 'UnicaenApp\Service\Ldap\StructureFactory',

            // Gestion des CSV
            'ViewCsvRenderer'             => 'UnicaenApp\Mvc\Service\ViewCsvRendererFactory',
            'ViewCsvStrategy'             => 'UnicaenApp\Mvc\Service\ViewCsvStrategyFactory',

            'MessageCollector'            => 'UnicaenApp\Service\MessageCollectorFactory',

            'MessageConfig'               => 'UnicaenApp\Message\MessageConfigFactory',
            'MessageRepository'           => 'UnicaenApp\Message\MessageRepositoryFactory',
            'MessageService'              => 'UnicaenApp\Message\MessageServiceFactory',
        ],
        'invokables'         => [
            'UnicaenApp\HistoriqueListener' => 'UnicaenApp\ORM\Event\Listeners\HistoriqueListener',
        ],
        'abstract_factories' => [
            'UnicaenApp\Service\Doctrine\MultipleDbAbstractFactory',
        ],
        'initializers'       => [
            'UnicaenApp\Service\EntityManagerAwareInitializer',
        ],
    ],
    'form_elements'   => [
        'invokables'   => [
            'UploadForm' => 'UnicaenApp\Controller\Plugin\Upload\UploadForm',
        ],
        'initializers' => [
            'UnicaenApp\Service\EntityManagerAwareInitializer',
        ],
    ],
    'hydrators' => [
        'initializers' => [
            'UnicaenApp\Service\EntityManagerAwareInitializer',
        ],
    ],
    'view_helpers'    => [
        'factories'    => [
            'appInfos'          => 'UnicaenApp\View\Helper\AppInfosFactory',
            'appLink'           => 'UnicaenApp\View\Helper\AppLinkFactory',
            'userProfileSelect' => 'UnicaenAuth\View\Helper\UserProfileSelectFactory',
            'Message'           => 'UnicaenApp\Message\View\Helper\MessageHelperFactory',
        ],
        'invokables'   => [
            'appConnection'         => 'UnicaenApp\View\Helper\AppConnection',
            'messenger'             => 'UnicaenApp\View\Helper\Messenger',
            'modalAjaxDialog'       => 'UnicaenApp\View\Helper\ModalAjaxDialog',
            'confirm'               => 'UnicaenApp\View\Helper\ConfirmHelper',
            'toggleDetails'         => 'UnicaenApp\View\Helper\ToggleDetails',
            'multipageFormFieldset' => 'UnicaenApp\Form\View\Helper\MultipageFormFieldset',
            'multipageFormNav'      => 'UnicaenApp\Form\View\Helper\MultipageFormNav',
            'multipageFormRow'      => 'UnicaenApp\Form\View\Helper\MultipageFormRow',
            'multipageFormRecap'    => 'UnicaenApp\Form\View\Helper\MultipageFormRecap',
            'formControlGroup'      => 'UnicaenApp\Form\View\Helper\FormControlGroup',
            'formDate'              => 'UnicaenApp\Form\View\Helper\FormDate',
            'formDateInfSup'        => 'UnicaenApp\Form\View\Helper\FormDateInfSup',
            'formRowDateInfSup'     => 'UnicaenApp\Form\View\Helper\FormRowDateInfSup',
            'formSearchAndSelect'   => 'UnicaenApp\Form\View\Helper\FormSearchAndSelect',
            'formLdapPeople'        => 'UnicaenApp\Form\View\Helper\FormLdapPeople',
            'formErrors'            => 'UnicaenApp\Form\View\Helper\FormErrors',
            'messageCollector'      => 'UnicaenApp\View\Helper\MessageCollectorHelper',
            /* Nouvelles aides de vue qui surchargent les anciennent pour exploiter des directives de configuration */
            'headScript'            => 'UnicaenApp\View\Helper\HeadScript',
            'inlineScript'          => 'UnicaenApp\View\Helper\InlineScript',
            'headLink'              => 'UnicaenApp\View\Helper\HeadLink',
            'Uploader'                  => 'UnicaenApp\View\Helper\Upload\UploaderHelper',
            'formAdvancedMultiCheckbox' => 'UnicaenApp\Form\View\Helper\FormAdvancedMultiCheckbox',
            'historique'                => 'UnicaenApp\View\Helper\HistoriqueViewHelper',
            'tabajax'                   => 'UnicaenApp\View\Helper\TabAjax\TabAjaxViewHelper',
            'tag'                       => 'UnicaenApp\View\Helper\TagViewHelper',
        ],
        'initializers' => [
            'UnicaenApp\Service\EntityManagerAwareInitializer',
        ],
    ],
    'translator'      => [
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '/%s/Zend_Captcha.php',
            ],
            [
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '/%s/Zend_Validate.php',
            ],
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'UnicaenApp\Controller\Application' => 'UnicaenApp\Controller\ApplicationController',
            'UnicaenApp\Controller\Cache'       => 'UnicaenApp\Controller\CacheController',
        ],
    ],
    'view_manager'    => [
        // RouteNotFoundStrategy configuration
        'display_not_found_reason' => false, // display 404 reason in template
        'not_found_template'       => 'error/404', // e.g. '404'
        // ExceptionStrategy configuration
        'display_exceptions'       => false,
        'exception_template'       => 'error/index',
        // Doctype with which to seed the Doctype helper
        'doctype'                  => 'HTML5',
        // TemplateMapResolver configuration
        // template/path pairs
        'template_map'             => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
            //            'unicaen-app/application/apropos'                  => __DIR__ . '/../view/application/apropos.phtml',
            //            'unicaen-app/application/contact'                  => __DIR__ . '/../view/application/contact.phtml',
            //            'unicaen-app/application/plan'                     => __DIR__ . '/../view/application/plan.phtml',
            //            'unicaen-app/application/mentions-legales'         => __DIR__ . '/../view/application/mentions-legales.phtml',
            //            'unicaen-app/application/informatique-et-libertes' => __DIR__ . '/../view/application/informatique-et-libertes.phtml',
        ],
        // TemplatePathStack configuration
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
        // Layout template name
        'layout'                   => 'layout/layout', // e.g., 'layout/layout'
        // Additional strategies to attach
        'strategies'               => [
            'ViewJsonStrategy', // register JSON renderer strategy
            'ViewCsvStrategy', // register CSV renderer strategy
            //            'ViewFeedStrategy', // register Feed renderer strategy
        ],
    ],
    // All navigation-related configuration is collected in the 'navigation' key
    'navigation'      => [
        // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
        'default' => [
            // And finally, here is where we define our page hierarchy
            'home' => [
                'label' => _("Accueil"),
                'title' => _("Page d'accueil de l'application"),
                'route' => 'home',
                'order' => -100, // make sure home is the first page
                'pages' => [
                    'etab'                     => [
                        'label'    => _("Université de Caen Normandie"),
                        'title'    => _("Page d'accueil du site de l'Université de Caen Normandie"),
                        'uri'      => 'http://www.unicaen.fr/',
                        'class'    => 'ucbn',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'resource' => 'controller/UnicaenApp\Controller\Application:etab', // ACL (cf. module BjyAuthorize)
                        'order'    => 1000,
                    ],
                    'apropos'                  => [
                        'label'    => _("À propos"),
                        'title'    => _("À propos de cette application"),
                        'route'    => 'apropos',
                        'class'    => 'apropos',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:apropos',
                        'order'    => 1001,
                    ],
                    'contact'                  => [
                        'label'    => _("Contact"),
                        'title'    => _("Contact concernant l'application"),
                        'route'    => 'contact',
                        'class'    => 'contact',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:contact',
                        'order'    => 1002,
                    ],
                    'plan'                     => [
                        'label'    => _("Plan de navigation"),
                        'title'    => _("Plan de navigation au sein de l'application"),
                        'route'    => 'plan',
                        'class'    => 'plan',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:plan',
                        'order'    => 1003,
                    ],
                    'mentions-legales'         => [
                        'label'    => _("Mentions légales"),
                        'title'    => _("Mentions légales"),
                        'uri'      => 'http://www.unicaen.fr/outils-portail-institutionnel/mentions-legales/',
                        'class'    => 'ml',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:mentions-legales',
                        'order'    => 1004,
                    ],
                    'informatique-et-libertes' => [
                        'label'    => _("Informatique et libertés"),
                        'title'    => _("Informatique et libertés"),
                        'uri'      => 'http://www.unicaen.fr/outils-portail-institutionnel/informatique-et-libertes/',
                        'class'    => 'il',
                        'visible'  => false,
                        'footer'   => true, // propriété maison pour inclure cette page dans le menu de pied de page
                        'sitemap'  => true, // propriété maison pour inclure cette page dans le plan
                        'resource' => 'controller/UnicaenApp\Controller\Application:informatique-et-libertes',
                        'order'    => 1005,
                    ],
                ],
            ],
        ],
    ],
    'public_files' => [
        'head_scripts'  => [
            1 => 'https://gest.unicaen.fr/public/jquery-1.11.3.min.js',
	        2 => 'https://gest.unicaen.fr/public/jquery-ui-1.11.4/jquery-ui.min.js',
        ],
        'inline_scripts'  => [
	        1 => 'https://gest.unicaen.fr/public/bootstrap-3.3.5/js/bootstrap.min.js',
            2 => '/js/app.js',
            3 => '/js/util.js',
            4 => '/js/unicaen.js',
        ],
        'stylesheets' => [
            1 => 'https://gest.unicaen.fr/public/jquery-ui-1.11.4/jquery-ui.min.css',
    	    2 => 'https://gest.unicaen.fr/public/jquery-ui-1.11.4/jquery-ui.structure.min.css',
	        3 => 'https://gest.unicaen.fr/public/jquery-ui-1.11.4/jquery-ui.theme.min.css',
            4 => 'https://gest.unicaen.fr/public/bootstrap-3.3.5/css/bootstrap.min.css',
            5 => 'https://gest.unicaen.fr/public/bootstrap-3.3.5/css/bootstrap-theme.min.css',
            6 => '/css/unicaen.css',
            7 => '/css/app.css',
        ],
        'cache_enabled' => false,
    ],
    'bjyauthorize' => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                [
                    'controller' => 'UnicaenApp\Controller\Cache',
                    'action' => ['js', 'css'],
                    'roles' => [],
                ],
            ],
        ],
    ],
];
