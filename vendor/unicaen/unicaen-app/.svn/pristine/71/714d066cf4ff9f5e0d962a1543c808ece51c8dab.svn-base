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
                                'controller' => 'contact',          // NB: les clés 'controller' et 'action' sont superflues du fait
                                'action'     => 'ajouter-identite', // qu'une 'route' est spécifiée mais c'est pour les tests.
                                'visible'    => true,
                                'params'     => array('source' => 'ldap', 'branch' => 'people'),
                                'class'      => 'step1',
                                'custom'     => 'foo',
                            ),
                            'adresse' => array(
                                'label'      => 'Votre adresse',
                                'route'      => 'contact/ajouter/adresse',
                                'controller' => 'contact',         // NB: idem.
                                'action'     => 'ajouter-adresse',
                                'visible'    => true,
                                'params'     => array('type' => 'mail'),
                                'class'      => 'step2',
                                'custom'     => 'foo',
                            ),
                            'message' => array(
                                'label'      => 'Votre message',
                                'route'      => 'contact/ajouter/message',
                                'controller' => 'contact-other',   // NB: idem.
                                'action'     => 'ajouter-message',
                                'withtarget' => true,
                                'visible'    => false,
                                'custom'     => 'foo',
                            ),
                        ),
                    ),
                    'supprimer' => array(
                        'label'      => 'Supprimer le message',
                        'title'      => 'Supprimer le message n°{id}',
                        'route'      => 'contact/supprimer',
                        'visible'    => false,
                        'withtarget' => true,
                        'class'      => 'iconify', // NB: manque la propriété 'icon'
                        'modal'      => false, // force la désactivation du mode modal 
                    ),
                    'modifier' => array(
                        'label'      => 'Modifier le message',
                        'title'      => 'Modifier le message n°{id}',
                        'route'      => 'contact/modifier',
                        'visible'    => false,
                        'withtarget' => true,
                        'class'      => 'iconify',
                        'icon'       => 'glyphicon glyphicon-pencil',
                    ),
                    'envoyer' => array(
                        'label'      => 'Envoyer le message',
                        'title'      => 'Envoyer le message n°{id}',
                        'route'      => 'contact/envoyer',
                        'visible'    => false,
                        'withtarget' => true,
                        'icon'       => 'glyphicon glyphicon-pencil', // NB: manque la classe 'iconify'
                    ),
                ),
            ),
        )
    )
);