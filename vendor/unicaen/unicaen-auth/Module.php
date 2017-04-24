<?php

namespace UnicaenAuth;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Point d'entrée du module d'authentification Unicaen.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{
    /**
     *
     * @return array
     * @see ConfigProviderInterface
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



    /**
     *
     * @return array
     * @see AutoloaderProviderInterface
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }



    /**
     * This method is called once the MVC bootstrapping is complete,
     * after the "loadModule.post" event, once $application->bootstrap() is called.
     *
     * @param EventInterface $e
     *
     * @see BootstrapListenerInterface
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $e)
        /* @var \Zend\Mvc\MvcEvent $e */
    {
        $application = $e->getApplication();
        /* @var $services \Zend\ServiceManager\ServiceManager */
        $services = $application->getServiceManager();

        // transmission des ACL aux aides de vue de navigation
        try {
            $authorizeService = $services->get('BjyAuthorize\Service\Authorize');
            /* @var $authorizeService \BjyAuthorize\Service\Authorize */
            \Zend\View\Helper\Navigation::setDefaultAcl($authorizeService->getAcl());
            \Zend\View\Helper\Navigation::setDefaultRole($authorizeService->getIdentity());
        } catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $snfe) {
            // pas de module BjyAuthorize : pas d'ACL
        }

        /* @var $options Options\ModuleOptions */
        $options = $services->get('unicaen-auth_module_options');

        // si l'auth CAS est demandée, modif de la route de connexion pour zapper le formulaire
        if ($options->getCas() && php_sapi_name() !== 'cli') {
            /* @var $router \Zend\Mvc\Router\Http\TreeRouteStack */
            $router = $services->get('router');
            $router->addRoutes([
                // remplace les routes existantes (cf. config du module)
                'zfcuser' => [
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
                        'login'  => [
                            'type'    => 'Literal',
                            'options' => [
                                'route'    => '/connexion',
                                'defaults' => [
                                    'controller' => 'zfcuser',
                                    'action'     => 'authenticate', // zappe l'action 'login'
                                ],
                            ],
                        ],
                        'logout' => [
                            'type'    => 'Literal',
                            'options' => [
                                'route'    => '/deconnexion',
                                'defaults' => [
                                    'controller' => 'zfcuser',
                                    'action'     => 'logout',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }



    /**
     *
     * @return array
     * @see ServiceProviderInterface
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                // verrue pour forcer le label de l'identifiant qqsoit l'options 'auth_identity_fields'
                'zfcuser_login_form' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $form    = new \ZfcUser\Form\Login(null, $options);
                    $form->setInputFilter(new \ZfcUser\Form\LoginFilter($options));
                    $form->get('identity')->setLabel("Username");

                    return $form;
                },
            ],
        ];
    }
}