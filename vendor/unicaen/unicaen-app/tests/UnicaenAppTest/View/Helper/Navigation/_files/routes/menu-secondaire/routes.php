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
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/identite',
                        ),
                    ),
                    'adresse' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/adresse',
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'postale' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/adresse-postale',
                                ),
                            ),
                            'mail' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/adresse-mail',
                                ),
                            ),
                        ),
                    ),
                    'message' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/message',
                        ),
                    ),
                ),
            ),
            'modifier' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/modifier',
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'identite' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/identite',
                        ),
                    ),
                ),
            ),
        ),
    ),
);