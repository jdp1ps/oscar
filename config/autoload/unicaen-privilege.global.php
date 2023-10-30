<?php

return [
    'unicaen-auth' => [
        'privilege_entity_class' => \Oscar\Entity\Privilege::class,
        'enable_privileges'      => true,
    ],

    'bjyauthorize' => [
        'identity_provider' => \UnicaenAuthentification\Provider\Identity\Chain::class,

        'role_providers' => [
            /**
             * Fournit les rôles issus de la base de données éventuelle de l'appli.
             * NB: si le rôle par défaut 'guest' est fourni ici, il ne sera pas ajouté en double dans les ACL.
             * NB: si la connexion à la base échoue, ce n'est pas bloquant!
             */
            //'UnicaenAuth\Provider\Role\DbRole'   => [],
            /**
             * Fournit le rôle correspondant à l'identifiant de connexion de l'utilisateur.
             * Cela est utile lorsque l'on veut gérer les habilitations d'un utilisateur unique
             * sur des ressources.
             */
            //'UnicaenAuth\Provider\Role\Username' => [],
        ],

        'resource_providers' => [
            \UnicaenPrivilege\Service\Privilege\PrivilegeService::class => [],
        ],
        'rule_providers'     => [
            \UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider::class => [],
        ],

        'guards' => [
            \UnicaenPrivilege\Guard\PrivilegeController::class => [
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['index'],
                    'privileges' => [
                       \Oscar\Provider\Privileges::DROIT_ROLE_VISUALISATION,
                        \Oscar\Provider\Privileges::DROIT_PRIVILEGE_VISUALISATION,
                    ],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['roles'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_ROLE_VISUALISATION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['privileges'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_PRIVILEGE_VISUALISATION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['role-edition', 'role-suppression'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_ROLE_EDITION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['privileges-modifier'],
                    'privileges' => [\Oscar\Provider\Privileges::DROIT_PRIVILEGE_EDITION],
                ],
            ],
        ],
    ],
];