<?php
return array(
    'home' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route'    => '/',
        ),
    ),
    'apropos' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route'    => '/apropos',
        ),
    ),
    'contact' => array(
        'type'    => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route'    => '/contact',
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'ajouter' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/ajouter',
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'identite' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/identite[/:source[/:branch]]',
                        ),
                    ),
                    'adresse' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/adresse[/:type]',
                        ),
                    ),
                    'message' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/message[/:id]',
                        ),
                    ),
                ),
            ),
            'supprimer' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/supprimer/:id',
                ),
            ),
            'modifier' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/modifier/:id',
                ),
            ),
            'envoyer' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/envoyer/:id',
                ),
            ),
        ),
    ),
);