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
            UnicaenAuth\Guard\PrivilegeController::class => [
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
                        'spentSynthesisActivity',
                        'pcru',
                        'pcruInfos',
                        "pcruList",
                        "apiUi",
                        "timesheet"
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
                    'action' => ['delete', 'index', 'search', 'suborganization', 'fiche'],
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
                        \UnicaenAuth\Provider\Privilege\Privileges::DROIT_ROLE_VISUALISATION,
                        \UnicaenAuth\Provider\Privilege\Privileges::DROIT_PRIVILEGE_VISUALISATION,
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
    //
    // Configuration des utilitaires en mode console.
    //
    ////////////////////////////////////////////////////////////////////////////
    'console' => array(
        'router' => array(
            'routes' => array(

                'oscar_execute_command' => [
                    'options' => [
                        'route' => 'exec <command>',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'execute',
                        ],
                    ]
                ],

                'notification_activity_generate' => [
                    'options' => [
                        'route' => 'oscar notifications:generate <idactivity>',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'notificationsActivityGenerate',
                        ],
                    ]
                ],

                'notifications_mail_persons' => [
                    'options' => [
                        'route' => 'oscar notifications:mails:persons',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'notificationsMails',
                        ],
                    ]
                ],

                'notifications_mail_person' => [
                    'options' => [
                        'route' => 'oscar notifications:mails:person <idperson> [-f|--force]',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'notificationsMailsPerson',
                        ],
                    ]
                ],

                'test_mailing' => [
                    'options' => [
                        'route' => 'oscar test:mailer',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'testMailing',
                        ],
                    ]
                ],

                'test_config' => [
                    'options' => [
                        'route' => 'oscar test:config',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'testConfig',
                        ],
                    ]
                ],

                'tokens_with_privilege' => [
                    'options' => [
                        'route' => 'oscar tokens:with-privilege <privilege>',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'tokensWithPrivileges',
                        ],
                    ]
                ],

                'token_has_privilege' => [
                    'options' => [
                        'route' => 'oscar token:has-privilege <token> <privilege> ',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'tokenHasPrivilege',
                        ],
                    ]
                ],


                'json_auth_token' => [
                    'options' => [
                        'route' => 'oscar json:user <token>',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'jsonUser',
                        ],
                    ]
                ],

                'json_notifications' => [
                    'options' => [
                        'route' => 'oscar json:notifications <ids>',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'jsonNotifications',
                        ],
                    ]
                ],


                // Procédure pour lancer des patchs
                'oscar_console_patch' => array(
                    'options' => array(
                        'route' => 'oscar patch <patchname>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'patch',
                        ),
                    ),
                ),

                // Procédure d'anonymisation des données
                'oscar_version' => array(
                    'options' => array(
                        'route' => 'oscar version',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'version',
                        ),
                    ),
                ),

                // -------------------------------------------------------------
                //////////////// PERSON(S)
                'oscar_persons_search_by_connector' => array(
                    'options' => array(
                        'route' => 'oscar persons:search:connector <connector> <value>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'personsSearchConnector',
                        ),
                    ),
                ),

                'oscar_persons_search_build' => array(
                    'options' => array(
                        'route' => 'oscar persons:search:build',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'buildSearchPerson',
                        ),
                    ),
                ),

                'oscar_organizations_search_build' => array(
                    'options' => array(
                        'route' => 'oscar organizations:search:build',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'buildSearchOrganization',
                        ),
                    ),
                ),

                'oscar_organizations_sync' => [
                    'options' => [
                        'route' => 'oscar organizations:sync <connectorkey> [--force|-f] [--verbose|-v]',
                        'defaults' => [
                            'controller' => 'Console',
                            'action' => 'organizationSync',
                        ],
                    ],
                ],

                // -------------------------------------------------------------

                'oscar_search_update' => array(
                    'options' => array(
                        'route' => 'oscar search:update <id>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'updateIndex',
                        ),
                    ),
                ),

                'oscar_status_update' => array(
                    'options' => array(
                        'route' => 'oscar activity:status',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'recalculateStatus',
                        ),
                    ),
                ),

                'oscar_person_sync' => [
                    'options' => array(
                        'route' => 'oscar person:sync <id>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'personSync',
                        ),
                    ),
                ],
                'oscar_persons_sync' => [
                    'options' => array(
                        'route' => 'oscar persons:sync <connectorkey> [-f|--force] [-v|--verbose] [-p|--purge]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'personsSync',
                        ),
                    ),
                ],
                'oscar_authentifications_sync' => [
                    'options' => array(
                        'route' => 'oscar authentifications:sync <jsonpath> [-f|--force]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authentificationsSync',
                        ),
                    ),
                ],
                'oscar_personsjson_sync' => [
                    'options' => array(
                        'route' => 'oscar personsjson:sync <fichier> [-f|--force]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'personJsonSync',
                        ),
                    ),
                ],
                'oscar_organizationsjson_sync' => [
                    'options' => array(
                        'route' => 'oscar organizationsjson:sync <fichier> [-f|--force]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'organizationJsonSync',
                        ),
                    ),
                ],
                'oscar_activityfile_sync' => [
                    'options' => array(
                        'route' => 'oscar activity:sync <fichier> [-f|--force] [--create-missing-project] [--create-missing-person] [--create-missing-organization] [--create-missing-person-role] [--create-missing-organization-role] [--create-missing-activity-type]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'activityFileSync',
                        ),
                    ),
                ],

                'oscar_activityfile_sync2' => [
                    'options' => array(
                        'route' => 'oscar activity:csvtojson <fichier> <config> [<skip>] [-f|--force] [--cp] [--co] [--cpr] [--cor]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'importActivity2',
                        ),
                    ),
                ],

                /////////////////////////////////////////////// Authentification
                'oscar_auth_add' => [
                    'options' => array(
                        'route' => 'oscar auth:add [login] [email] [pass] [displayname]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authAdd',
                        ),
                    ),
                ],
                'oscar_auth_list' => [
                    'options' => array(
                        'route' => 'oscar auth:list',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authList',
                        ),
                    ),
                ],
                'oscar_auth_pass' => [
                    'options' => array(
                        'route' => 'oscar auth:pass <login> [--ldap]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authPass',
                        ),
                    ),
                ],
                'oscar_auth_promote' => [
                    'options' => array(
                        'route' => 'oscar auth:promote <login> [<role>]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authPromote',
                        ),
                    ),
                ],

                'oscar_auth_info' => [
                    'options' => array(
                        'route' => 'oscar auth:info <login> [--org] [--act]',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'authInfo',
                        ),
                    ),
                ],

                'oscar_search_delete' => array(
                    'options' => array(
                        'route' => 'oscar search:delete <id>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'deleteIndex',
                        ),
                    ),
                ),

                'oscar_activity_search' => array(
                    'options' => array(
                        'route' => 'oscar activity:search <exp> <obj>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'searchActivity',
                        ),
                    ),
                ),

                'oscar_search_rebuild' => array(
                    'options' => array(
                        'route' => 'oscar activity:search:build',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'buildSearchActivity',
                        ),
                    ),
                ),
                //////////////////////////////////////////////////////// HARPÉGE
                'console_check_persons' => [
                    'options' => array(
                        'route' => 'oscar persons:check',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'evalHarpegeLdapPersons',
                        ),
                    ),
                ],

                'notification_person' => [
                    'options' => array(
                        'route' => 'oscar notifications:person <idperson>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'notificationsPerson',
                        ),
                    ),
                ],

                'notification_person_remove' => [
                    'options' => array(
                        'route' => 'oscar notifications:person:purge <idperson> <idactivity>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'notificationsPersonActivityPurge',
                        ),
                    ),
                ],

                'oscar_search_find' => array(
                    'options' => array(
                        'route' => 'oscar search:find <search>',
                        'defaults' => array(
                            'controller' => 'Oscar\Controller\Search',
                            'action' => 'find',
                        ),
                    ),
                ),
                'oscar_conf_dump' => array(
                    'options' => array(
                        'route' => 'oscar conf <what>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'conf',
                        ),
                    ),
                ),

                'oscar_privilege' => array(
                    'options' => array(
                        'route' => 'oscar privilege <what>',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'privilege',
                        ),
                    ),
                ),


                'oscar_sync_organization' => array(
                    'options' => array(
                        'route' => 'oscar sync:organization',
                        'defaults' => array(
                            'controller' => 'Console',
                            'action' => 'syncOrganisation',
                        ),
                    ),
                ),
            ),
        ),
    ),

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
            'Logger' => \Oscar\Service\LoggerServiceFactory::class,
            \Oscar\Service\GearmanJobLauncherService::class => \Oscar\Service\GearmanJobLauncherServiceFactory::class,
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
            \Oscar\Service\UserParametersService::class => \Oscar\Service\UserParametersServiceFactory::class
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
            \Oscar\Controller\ConsoleController::class => \Oscar\Factory\OscarUseFactory::class,
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
            'Console' => \Oscar\Controller\ConsoleController::class,
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
