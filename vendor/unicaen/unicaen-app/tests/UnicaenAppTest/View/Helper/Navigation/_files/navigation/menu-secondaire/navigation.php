<?php
return array(
    'home' => array(
        'label' => "Accueil",
        'route' => 'home',
        'pages' => array(
            'apropos' => array(
                'label'   => "À propos",
                'route'   => 'apropos',
            ),
            'contact' => array(
                'label'  => 'Liste des messages',
                'route'  => 'contact',
                'pages' => array(
                    'ajouter' => array(
                        'label' => 'Nouveau message',
                        'route' => 'contact/ajouter',
                        'pages' => array(
                            'identite' => array(
                                'label'      => "Votre identité",
                                'route'      => 'contact/ajouter/identite',
                            ),
                            'adresse' => array(
                                'label'      => 'Votre adresse',
                                'route'      => 'contact/ajouter/adresse',
                                'pages' => array(
                                    'postale' => array(
                                        'label'      => "Votre adresse postale",
                                        'route'      => 'contact/ajouter/adresse/postale',
                                    ),
                                    'mail' => array(
                                        'label'      => 'Votre adresse mail',
                                        'route'      => 'contact/ajouter/adresse/mail',
                                    ),
                                ),
                            ),
                            'message' => array(
                                'label'      => 'Votre message',
                                'route'      => 'contact/ajouter/message',
                            ),
                        ),
                    ),
                    'modifier' => array(
                        'label'      => 'Modifier un message',
                        'route'      => 'contact/modifier',
                        'pages' => array(
                            'identite' => array(
                                'label'      => "Votre identité",
                                'route'      => 'contact/modifier/identite',
                            ),
                        ),
                    ),
                    'invisible' => array(
                        'label'   => 'Supprimer un message',
                        'route'   => 'contact/supprimer',
                        'visible' => false,
                    ),
                ),
            ),
        )
    )
);