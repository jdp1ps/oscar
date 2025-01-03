<?php

use Psr\Container\ContainerInterface;

return [
    /**  **/
    'unicaen-signature' => [

        'enabled' => true,

        /////////////////////////////////////////////////////////////////////////////////
        // DEVELOPPEMENT
        'vite_mode'              => 'prod', // mode développement de l'UI
//        'vite_mode'              => 'dev', // mode développement de l'UI

        // Emplacement où sont archivés les documents en cours de signature
        'documents_path' => __DIR__ . '/../../data/documents/signature',


        /////////////////////////////////////////////////////////////////////////////////
        /// SYSTEME de LOG
        'logger'         => [
            ///////////////////////////////////////
            // Activation d'un logger autonome
            'enable'          => true, // Actif
            'level'           => \Monolog\Logger::INFO, // Niveau de log
            'file'            => __DIR__ . '/../../logs/signature.log', // Fichier d'écriture
            'file_permission' => 0666,

            ///////////////////////////////////////
            /// Sortie standard (pour le développement le built-in serveur)
            'stdout'          => false,

            ///////////////////////////////////////
            /// Logger complémentaire (celui de l'application utilisant le module)
            /// -> implementation de LoggerInterface (ex: Monolog)
            // 'customLogger' => null
            'customLogger'    => 'Logger' // customLogger (LoggerInterface)
        ],
        /////////////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////////////
        /// Logique métier

        /**
         * Retourne l'email de l'utilisateur courant. Cette méthode est utilisé lors d'un VISA INTERNE pour vérifier
         * si l'utilisateur courant est autorisé à Valider/Refuser le document.
         */
        'current_user'   => function (ContainerInterface $sc): string {
            $person = $sc->get(\Oscar\Service\OscarUserContext::class)->getCurrentPerson();
            if ($person) {
                return $person->getEmail();
            }
            return "";
        },

        'notifications_messages' => [
            ///////////////////////////////////////////////// ADRESSE ROOT du OSCAR ICI ///
            'base_url' => 'https://oscar.unicaen.fr'
        ],


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Diffusion des notifications
        'notification_strategy' => [
            function(ContainerInterface $container, string $email, string $subject, string $message) {
                $logger = $container->get("Logger");
                try {
                    /** @var \Oscar\Service\MailingService $mailer */
                    $mailer = $container->get(\Oscar\Service\MailingService::class);
                    $mail = $mailer->newMessage($subject);
                    $mail->setTo($email)
                        ->setSubject($subject)
                        ->setBody($message);
                    $mailer->send($mail);
                    $logger->info("Mail signature à : $email");
                } catch (Exception $e) {
                    $logger->error("ERREUR MAIL SIGNATURE : ". $e->getMessage());
                }
            }
        ],

        /**
         * Méthodes personnalisées de récupération des utilisateurs
         *
         * La finalité est d'obtenir une liste de destinataires sous la forme :
         * [
         *   ['email'=>string, 'firstname'=>string, 'lastname'=>string ],
         *   ['email'=>string, 'firstname'=>string, 'lastname'=>string ],
         *  ]
         *
         * Note : firstname/lastname sont optionnels
         */
        'get_recipients_methods' => [
            [
                ///// key : Clef unique pour identifier le méthode
                'key'               => 'persons_by_role',
                'label'             => 'Personnes par rôle', // Intitulé
                'description'       => 'Sélectionne les personnes en fonction de leurs rôles', // Description

                // Les options sont utilisées pour configurer la méthode d'obtention des destinataires
                // Il y'a 2 étapes :
                //  ETAPE 1 - options
                // Les options permettent de définir un ou plusieurs critères
                // Chaque critère a un nom unique (key), et un tableau de valeurs possibles.
                // Ces valeurs sont fixées avec la clef 'values', soit des valeurs fixes, soit une fonction retournant
                // la liste des CLEFS=>VALEURS disponible pour cette option.
                //  ETAPE 2 - getRecipients
                // Un méthode 'getRecipients' est appelé lors du déclenchement d'un étape de signature,
                // cette méthode reçoit les options configurées sous la forme KEY_OPTION => [VALEURA,VALEURSB,...]
                'options'           => [ // Options
                    [
                        'key'          => 'role_person_id',
                        'label'        => "Rôle dans l'activité",
                        // Type d'affichage (checkbox / recipients)
                        'type'         => 'checkbox',

                        ///////////////////////////////////////////////////
                        // array|function
                        // Retourne un tableau VALEUR => LABEL.
                        //
                        // Ex : [
                        //        15 => "Valeur d'option A",
                        //        24 => "Valeur d'option B",
                        //        37 => "Valeur d'option C",
                        //      ]
                        'values'       => function (ContainerInterface $s) {
                            return $s->get(
                                \Oscar\Service\OscarUserContext::class
                            )->getAvailableRolesActivityOrOrganization();
                        },

                        // Valeurs par défaut
                        'defaultValue' => []
                    ],
                    [
                        'key'    => 'role_organisation_id',
                        'label'  => "étendre aux structures",
                        'type'   => 'checkbox',
                        'values' => function ($s) {
                            return $s->get(
                                \Oscar\Service\OscarUserContext::class
                            )->getAvailabledRolesOrganizationActivity();
                        },

                        'defaultValue' => []
                    ]
                ],

                //////////////////////////////////////
                /// TODO / DEPRECATED ?
                'methods_on_create' => null,

                //////////////////////////////////////
                /// Retourne les destinataires sous la forme :
                /// [
                ///    ['email'=>string, 'firstname'=>string, 'lastname'=>string ],
                ///    ['email'=>string, 'firstname'=>string, 'lastname'=>string ],
                /// ]
                ///
                /// $options corresponds au options configurées avant, dans cet exemple, 'getRecipients' va recevoir les
                /// valeurs :
                /// [
                ///   'role_person_id' => [VALEUR1,VALEUR2],
                ///   'role_organisation_id' => [VALEURA,VALEURB]
                /// ]
                ///
                'getRecipients'     => function ($sc, $options) {
                    return $sc->get(\Oscar\Service\ProjectGrantService::class)->getRecipients($options);
                }
            ],
        ],

        // Configuration des parafeurs numérique
        'letterfiles'            => [

            /************
            [
                // Nom visible côté applicatif
                'label' => 'ESUP',

                // Code (unique)
                'name'  => 'esup',

                'description' => 'Parafeur numérique ESUP',

                // Utilisé par défaut
                'default'     => true,

                // URL d'accès au parapheur pour les destinataires
                'url_recipient' => 'https://signature-pp.unicaen.fr',

                // [DEV] Emplacement où sont archivés les échanges de données ave le parapheur
                'archive_exchange' => __DIR__.'/../../logs/signature_exchange',

                // Classe
                'class'       => \UnicaenSignature\Strategy\Letterfile\Esup\EsupLetterfileStrategy::class,

                // Niveaux de signature disponible
                'levels'      => [
                    \UnicaenSignature\Utils\SignatureConstants::VISA_HIDDEN => 'hidden',
                    \UnicaenSignature\Utils\SignatureConstants::VISA_VISUAL => 'visa',
                    \UnicaenSignature\Utils\SignatureConstants::SIGN_VISUAL => 'pdfImageStamp',
                    \UnicaenSignature\Utils\SignatureConstants::SIGN_CERTIF => 'certSign',
                    \UnicaenSignature\Utils\SignatureConstants::SIGN_EIDAS  => 'nexuSign',
                ],

                // Configuration du parafeur
                'config'      => [
                    // ESUP configuration
                    'url'           => "https://signature-pp.unicaen.fr",

                    // Créateur
                    'createdByEppn' => 'oscar242@unicaen.fr',
                ]
            ],
            /********/
            [
                'label'   => 'OSCAR visa',
                'name'    => 'internal', // internal est une clef dédiée pour identifier le parapheur interne
                'default' => false,
                'class'   => \UnicaenSignature\Strategy\Letterfile\InternalVisa\InternalVisaStrategy::class,
                'url_recipient' => 'http://localhost:8181/signature/my-documents',
                'url_visa' => 'http://localhost:8181/signature/my-documents',
                'levels'  => [
                    'visa_hidden' => 'visa_hidden',
                ],
                'config'  => [
                    'checkUserAccess' => function ($sc, $signatureRecipient) {
                        return true;
                        //return $sc->get(\Oscar\Service\ProjectGrantService::class)->getRecipients($options);
                    }
                ]
            ]
            /************/
        ]
    ]
    /******/
];