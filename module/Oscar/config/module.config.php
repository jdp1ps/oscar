<?php

use Oscar\Service\AccessResolverService;
use Oscar\View\Helpers\DateRenderer;
use Oscar\View\Helpers\UnicaenDoctrinePaginatorHelper;

$conf = new Symfony\Component\Yaml\Parser();

return array(

    'bjyauthorize' => [

        'role_providers' => [
            'RoleProvider' => []
        ],

        ////////////////////////////////////////////////////////////// Resources
        'guards' => [
            \UnicaenPrivilege\Guard\PrivilegeController::class => [
                ////////////////////////////////////////////////////////////////////////
                // PUBLIC
                [
                    'controller' => 'Public',
                    'action' => ['index','devVitejs'],
                    'roles' => [],
                ],

                [
                    'controller' => '\UnicaenAuth\Controller\Utilisateur',
                    'action' => ['usurperIdentite'],
                    'roles' => []
                ],

                [
                    'controller' => 'Public',
                    'action' => ['documentation', 'parameters', 'gitlog'],
                    'roles' => ['user'],
                ],

                [
                    'controller' => 'Notification',
                    'action' => ['indexPerson', 'index', 'test', 'notifyPerson', 'history'],
                    'roles' => ['user'],
                ],

                [
                    'controller' => 'Administration',
                    'action' => [
                        'access',
                        "accessAPI",
                        'accounts',
                        'accueil', // OK
                        'activityIndexBuild', // OK
                        'connectorsConfig',
                        'connectorTest',
                        'connectorExecute',
                        'connectorsHome',
                        'connectorConfigure',
                        'contractSigned',
                        'contratTypePcru',
                        'declarersList',
                        'discipline', // OK
                        'documentSections',
                        'index',
                        'logs',
                        'messages',
                        'organizationRole',
                        'organizationRoleApi',
                        'organizationType',
                        'oscarWorkerStatus',
                        'paysIso3166',
                        'pcru',
                        'roleAPI',
                        'roles',
                        'rolesEdit',
                        'testconfig',
                        'tva',
                        'typeDocument',
                        'typeDocumentApi',
                        'numerotation',
                        'parameters',
                        'polesCompetitivite',
                        'privileges2',
                        'planComptableLoaded',
                        'sourcesFinancement',
                        'userLogs',
                        'users',
                        'userRoles',
                    ],
                    'roles' => ['user']
                ],

                [
                    'controller' => 'Connector',
                    'action' => ['person', 'persons', 'organization', "organizations"],
                    'roles' => ['user']
                ],

                ////////////////////////////////////////////////////////////////
                // PROJET
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'Project',
                    'action' => ['edit', 'export', 'makeProject', 'organizations', 'persons', 'new', 'search', 'show'],
                    'roles' => ['user'],
                ],
                [
                    'controller' => 'Project',
                    'action' => [
                        'currentUserProjects',
                        'currentUserStructureProjects',
                        'exportMany'
                    ],
                    'privileges' => \Oscar\Provider\Privileges::PROJECT_DASHBOARD,
                    'roles' => ['user'],
                ],
                [
                    'controller' => 'Project',
                    'action' => ['index', 'rebuildIndex', 'simplifyPartners', 'simplifyMembers', 'fusion'],
                    'privileges' => \Oscar\Provider\Privileges::PROJECT_INDEX
                ],
                [
                    'controller' => 'Project',
                    'action' => ['addActivities'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_CHANGE_PROJECT
                ],
                [
                    'controller' => 'Project',
                    'action' => ['delete', 'empty'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN
                ],

                ////////////////////////////////////////////////////////////////
                // ACTIVITÉS
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'Activity',
                    'action' => [
                        'show',
                        'show2',
                        'edit',
                        'new',
                        'duplicate',
                        'persons',
                        'organizations',
                        'makeProject',
                        'delete',
                        'visualization',
                        'documentsJson',
                        'activitiesOrganizations',
                        'changeProject',
                        'jsonApi',
                        'notifications',
                        'generateNotifications',
                        'generatedDocument',
                        'pcru',
                        'requestFor',
                        'adminDemande',
                        'api',
                        'gant',
                        'estimatedSpent',
                        'estimatedSpentExport',
                        'spentList',
                        'newInStructure',
                        'signedDocumentSend',
                        'spentSynthesisActivity',
                        'personsAccessDeep',
                        'pcru',
                        'pcruInfos',
                        "pcruList",
                        "apiUi",
                        "timesheet",
                    ],

                    'roles' => ['user'],
                ],
                [
                    'controller' => 'Activity',
                    'action' => ['advancedSearch','exportJSON', 'almostDone', 'almostStart'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_INDEX
                ],
                [
                    'controller' => 'Activity',
                    'action' => ['orphans'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_CHANGE_PROJECT
                ],
                [
                    'controller' => 'Activity',
                    'action' => ['debug'],
                    'roles' => ['Administrateur']
                ],

                // DEPENSES
                // --- VERSEMENTS
                [
                    'controller' => 'Depense',
                    'action' => ['activity', 'manageSpendTypeGroup', 'activityApi', 'compteAffectation'],
                    'roles' => ['user'],
                ],

                // --- VERSEMENTS
                [
                    'controller' => 'ActivityPayment',
                    'action' => ['index', 'indexRest', 'rest'],
                    'roles' => ['user'],
                ],
                [
                    'controller' => 'ActivityPayment',
                    'action' => ['change', 'new', 'income', 'late', 'difference'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_PAYMENT_MANAGE
                ],

                // --- DATES CLEFS
                [
                    'controller' => 'ActivityDate',
                    'action' => ['index', 'activity'],
                    'roles' => ['user'],
                ],
                [
                    'controller' => 'ActivityDate',
                    'action' => ['change', 'new'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_MILESTONE_MANAGE
                ],

                // --- WorkPackage
                [
                    'controller' => 'WorkPackage',
                    'action' => ['index', 'new', 'edit', 'delete'],
                    'privileges' => \Oscar\Provider\Privileges::ACTIVITY_MILESTONE_MANAGE
                ],
                // --- WorkPackage
                [
                    'controller' => 'WorkPackage',
                    'action' => ['rest'],
                    'roles' => ['user']
                ],

                // --- Type d'activités - { controller: ActivityType, action: [new, delete, edit, index, move, merge], roles: [user] }
                [
                    'controller' => 'ActivityType',
                    'action' => ['new', 'delete', 'edit', 'index', 'move', 'merge'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN,
                ],

                // ---   - { controller: ActivityDate, action: [index, change, new], roles: [user] }
                [
                    'controller' => 'DateType',
                    'action' => ['index', 'change', 'new', 'edit', 'delete'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MILESTONETYPE_MANAGE,
                ],

                // DOCUMENTS
                [
                    'controller' => 'ContractDocument', // --- Upload
                    'action' => ['upload', 'delete', 'changeType', 'index', 'show', 'download'],
                    'roles' => ['user']
                ],

                // EXPORT
                [
                    'controller' => 'Activity',
                    'action' => ['csv', 'csvPayments'],
                    'roles' => ['user'],
                ],

                // TIMESHEET
                [
                    'controller' => 'Timesheet',
                    'action' => [

                        'highDelay',
                        'indexPersonActivity',

                        'sauvegarde',
                        'declaration',
                        'resume',
                        "declaration2",
                        "indexActivity",
                        "validateTimesheet",
                        'excel',
                        'organizationLeader',
                        'declarant',
                        'declarantAPI',
                        'validationActivity',
                        'validationActivity2',
                        'validationHWPPerson',
                        'validatePersonPeriod',
                        'importIcal',
                        'declarations',
                        'resumeActivity',
                        'resolveInvalidLabels',
                        'syntheseActivity',
                        'synthesisAll',
                        'synthesisActivityPeriod',
                        'synthesisActivityPeriodsBounds',
                        'exportActivityDates',
                        'checkperiod',
                        'validations',
                        'validations2',
                    ],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'Timesheet',
                    'action' => ['declarers', 'recallDeclarer'],
                    'roles' => ['Administrateur']
                ],

                [
                    'controller' => 'Api',
                    'action' => [
                        'activity',
                        'activityType',
                        'activityTypePcru',
                        'help',
                        'organization',
                        'organizations',
                        'person',
                        'persons',
                        'referencielPcruPoleCompetitivite',
                        'roles',
                        'adminManageAccess',
                    ],
                    'roles' => []
                ],

                ////////////////////////////////////////////////////////////////
                // PERSON
                ///////////////////////////////////////////////////////////////
                [
                    'controller' => 'Person',
                    'action' => ['personnel', 'access', 'delete', 'index', 'show', 'search', 'declarers'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'Person',
                    'action' => ['synchronize', 'boss'],
                    'privileges' => \Oscar\Provider\Privileges::PERSON_INDEX
                ],
                [
                    'controller' => 'Person',
                    'action' => [
                        'edit',
                        'new',
                        'merge',
                        'organizationRole',
                        'notificationPerson',
                        'notificationPersonGenerate',
                        'affectation'
                    ],
                    'privileges' => \Oscar\Provider\Privileges::PERSON_EDIT
                ],

                // Membre
                [
                    'controller' => 'Enroll',
                    'action' => [
                        'personProjectNew',
                        'personProjectDelete',
                        'personProjectEdit',
                        'organizationPersonNew',
                        'organizationPersonDelete',
                        'organizationPersonEdit',
                        'personActivityNew',
                        'personActivityDelete',
                        'personActivityEdit',
                        'organizationActivityNew',
                        'activityOrganizationDelete',
                        'activityOrganizationEdit',
                        'personProjectNew',
                        'personProjectDelete',
                        'personProjectEdit',
                        'organizationProjectNew',
                        'organizationProjectDelete',
                        'organizationProjectEdit',
                        'organizationPersonClose'

                    ],
                    'roles' => ['user']
                ],

                [
                    'controller' => 'TabDocument',
                    'action' => ['index', 'new', 'edit', 'delete', 'migrateDocuments'],
                    'roles' => ['user']
                ],

                ////////////////////////////////////////////////////////////////
                // ORGANIZATION
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'Organization',
                    'action' => [
                        'delete',
                        'fiche',
                        'index',
                        'search',
                        'suborganization',
                    ],
                    'roles' => ['user']

                ],

                [
                    'controller' => 'Organization',
                    'action' => ['show'],
                    'privileges' => \Oscar\Provider\Privileges::ORGANIZATION_SHOW
                ],
                [
                    'controller' => 'Organization',
                    'action' => ['edit', 'new', 'merge', 'close'],
                    'privileges' => \Oscar\Provider\Privileges::ORGANIZATION_EDIT
                ],
                [
                    'controller' => 'Organization',
                    'action' => ['merge'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN
                ],
                [
                    'controller' => 'Organization',
                    'action' => ['sync'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN
                ],
                [
                    'controller' => 'Organization',
                    'action' => ['fusion', 'scission', 'exportCsv'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN
                ],
                [
                    'controller' => 'Organization',
                    'action' => ['synchronizeConnector', 'sync'],
                    'privileges' => \Oscar\Provider\Privileges::MAINTENANCE_MENU_ADMIN
                ],

                ////////////////////////////////////////////////////////////////
                // DOCUMENTS ADMINISTRATIFS
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'AdministrativeDocument',
                    'action' => ['index', 'download'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'AdministrativeDocument',
                    'action' => ['upload'],
                    'privileges' => \Oscar\Provider\Privileges::ADMINISTRATIVE_DOCUMENT_NEW
                ],
                [
                    'controller' => 'AdministrativeDocument',
                    'action' => ['delete'],
                    'privileges' => \Oscar\Provider\Privileges::ADMINISTRATIVE_DOCUMENT_DELETE
                ],

                ////////////////////////////////////////////////////////////////
                // PUBLIC
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'Public',
                    'action' => ['changelog'],
                    'roles' => ['user'],
                ],
                [
                    'controller' => 'Public',
                    'action' => ['test'],
                    'roles' => ['Administrateur'],
                ],
                [
                    'controller' => 'Console',
                    'roles' => [],
                ],

                ////////////////////////////////////////////////////////////////
                // DROITS
                ////////////////////////////////////////////////////////////////
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action' => ['index'],
                    'privileges' => [

                        \Oscar\Provider\Privileges::DROIT_ROLE_VISUALISATION,
                        \Oscar\Provider\Privileges::DROIT_PRIVILEGE_VISUALISATION,
                    ],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action' => ['roles'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_ROLE_VISUALISATION],
                ],

                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action' => ['privileges'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_PRIVILEGE_VISUALISATION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action' => ['role-edition', 'role-suppression'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_ROLE_EDITION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action' => ['privileges-modifier'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_PRIVILEGE_EDITION],
                ],
            ]
        ],
    ],



    ////////////////////////////////////////////////////////////////////////////
    // ROUTES
    'router' => array(
        'routes' => $conf->parse(file_get_contents(__DIR__ . '/routes.yml'))
    ),

    // Service (métier)
    'service_manager' => array(

//        // Automatisation ???
//        'abstract_factories' => [
//            \Oscar\Factory\OscarFactory::class
//        ],

        'invokables' => [
            'AccessResolverService' => AccessResolverService::class,
            'SessionService' => \Oscar\Service\SessionService::class,
        ],

        'factories' => array(
            \Oscar\Service\ActivityLogService::class => \Oscar\Service\ActivityLogServiceFactory::class,
            \Oscar\Service\ActivityRequestService::class => \Oscar\Service\ActivityRequestServiceFactory::class,
            \Oscar\Service\ActivityTypeService::class => \Oscar\Service\ActivityTypeServiceFactory::class,
            \Oscar\Service\AdministrativeDocumentService::class => \Oscar\Service\AdministrativeDocumentServiceFactory::class,
            \Oscar\Service\ConnectorService::class => \Oscar\Service\ConnectorServiceFactory::class,
            \Oscar\Service\ContractDocumentService::class => \Oscar\Service\ContractDocumentServiceFactory::class,
            \Oscar\Service\DocumentFormatterService::class => \Oscar\Service\DocumentFormatterServiceFactory::class,
            \Oscar\Service\GearmanJobLauncherService::class => \Oscar\Service\GearmanJobLauncherServiceFactory::class,
            'Logger' => \Oscar\Service\LoggerServiceFactory::class,
            \Oscar\Service\MailingService::class => \Oscar\Service\MailingServiceFactory::class,
            \Oscar\Service\MaintenanceService::class => \Oscar\Service\MaintenanceServiceFactory::class,
            \Oscar\Service\MilestoneService::class => \Oscar\Service\MilestoneServiceFactory::class,
            \Oscar\Service\NotificationService::class => \Oscar\Service\NotificationServiceFactory::class,
            \Oscar\Service\OrganizationService::class => \Oscar\Service\OrganizationServiceFactory::class,
            \Oscar\Service\OscarConfigurationService::class => \Oscar\Service\OscarConfigurationServiceFactory::class,
            \Oscar\Service\OscarUserContext::class => \Oscar\Service\OscarUserContextFactory::class,
            \Oscar\Service\PCRUService::class => \Oscar\Service\PCRUServiceFactory::class,
            \Oscar\Service\PersonService::class => \Oscar\Service\PersonServiceFactory::class,
            \Oscar\Service\ProjectService::class => \Oscar\Service\ProjectServiceFactory::class,
            \Oscar\Service\ProjectGrantService::class => \Oscar\Service\ProjectGrantServiceFactory::class,
            \Oscar\Service\TimesheetService::class => \Oscar\Service\TimesheetServiceFactory::class,
            \Oscar\Service\SessionService::class => \Oscar\Service\SessionServiceFactory::class,
            \Oscar\Service\SpentService::class => \Oscar\Service\SpentServiceFactory::class,
            \Oscar\Service\TypeDocumentService::class => \Oscar\Service\TypeDocumentServiceFactory::class,
            'RoleProvider' => \Oscar\Provider\RoleProviderFactory::class,
            \Oscar\Service\UserParametersService::class => \Oscar\Service\UserParametersServiceFactory::class,
        ),
    ),

    'translator' => array(
        'locale' => 'fr_DJ', // en_US
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),

    // On doit déclaré ici les Controlleurs 'invoquables'
    'controllers' => array(
        'factories' => [
            'Activity' => \Oscar\Controller\ProjectGrantControllerFactory::class,
            'ActivityDate' => \Oscar\Controller\ActivityDateControllerFactory::class,
            'ActivityPayment' => \Oscar\Controller\ActivityPaymentControllerFactory::class,
            'ActivityType' => \Oscar\Controller\ActivityTypeControllerFactory::class,
            'Api' => \Oscar\Controller\ApiControllerFactory::class,
            'Administration' => \Oscar\Controller\AdministrationControllerFactory::class,
            'AdministrativeDocument' => \Oscar\Controller\AdministrativeDocumentControllerFactory::class,
            'Connector' => \Oscar\Controller\ConnectorControllerFactory::class,
            'DateType' => \Oscar\Controller\DateTypeControllerFactory::class,
            'Depense' => \Oscar\Controller\DepenseControllerFactory::class,
            \Oscar\Controller\EnrollController::class => \Oscar\Controller\EnrollControllerFactory::class,
            'ContractDocument' => \Oscar\Controller\ContractDocumentControllerFactory::class,
            'TabDocument' => \Oscar\Controller\TabDocumentControllerFactory::class,
            'Notification' => \Oscar\Controller\NotificationControllerFactory::class,
            'Organization' => \Oscar\Controller\OrganizationControllerFactory::class,
            'Project' => \Oscar\Controller\ProjectControllerFactory::class,
            'Public' => \Oscar\Controller\PublicControllerFactory::class,
            'Timesheet' => \Oscar\Controller\TimesheetControllerFactory::class,
            'Person' => \Oscar\Controller\PersonControllerFactory::class,
            'WorkPackage' => \Oscar\Controller\WorkPackageControllerFactory::class,
        ],
        "aliases" => [
            'Enroll' => \Oscar\Controller\EnrollController::class
        ]
    ),

    // Emplacement des templates
    'view_manager' => array(
        'exception_template' => 'error/index',
        'template_map' => array(
            'error/index' => __DIR__ . '/../view/error.phtml',
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'unicaen-auth/droits/privileges' => __DIR__ . '/../view/unicaen-auth/droits/privileges.phtml',
            'unicaen-auth/tbl-link' => __DIR__ . '/../view/unicaen-auth/droits/partials/tbl-link.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    // Aide de vue (Helper)
    'view_helpers' => [
        'invokables' => [
            'pager' => UnicaenDoctrinePaginatorHelper::class,
            'dater' => DateRenderer::class,
            'unAllowed' => \Oscar\View\Helpers\UnAllowed::class,
            'hasAccess' => \Oscar\View\Helpers\Access::class,
            'duration' => \Oscar\View\Helpers\Duration::class,
            'moment' => \Oscar\View\Helpers\Moment::class,
            'activity' => \Oscar\View\Helpers\ActivityHtml::class,
            'strEmpty' => \Oscar\View\Helpers\StrEmpty::class,
            'fileSize' => \Oscar\View\Helpers\Filesize::class,
            'userUI' => \Oscar\View\Helpers\UserUIHelper::class,
            'Currency' => \Oscar\View\Helpers\Currency::class,
            'currency' => \Oscar\View\Helpers\Currency::class,
            'keyvalue' => \Oscar\View\Helpers\KeyValueHelper::class,
            'slugify' => \Oscar\View\Helpers\Slugify::class,
        ],
        'factories' => [
            'activityTypeHlp' => \Oscar\View\Helpers\ActivityTypeHelperFactory::class,
            'Grant' => \Oscar\View\Helpers\GrantFactory::class,
            'Vite' => \Oscar\View\Helpers\ViteFactory::class,
            'hasRole' => \Oscar\View\Helpers\HasRoleFactory::class,
            'link' => \Oscar\View\Helpers\LinksFactory::class,
            'options' => \Oscar\View\Helpers\OptionsFactory::class,
        ],
        'abstract_factories' => [
            'HasPrivilege' => \Oscar\Factory\ViewHelperInvokatorFactory::class,
        ],
        'aliases' => [
            'grant' => 'Grant',
            'hasPrivilege' => 'HasPrivilege',
        ]
    ],

    // Formulaires
    'form_elements' => [
        'invokables' => [
            'ActivityPayment' => \Oscar\Form\ActivityPaymentForm::class
        ]
    ]
);
