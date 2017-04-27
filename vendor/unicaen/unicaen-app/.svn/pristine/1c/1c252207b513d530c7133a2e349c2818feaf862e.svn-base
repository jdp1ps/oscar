<?php

namespace UnicaenApp\Session;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Container;

/**
 * Description of SessionManager
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class SessionManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SessionManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appInfos = $serviceLocator->get('unicaen-app_module_options')->getAppInfos();
        
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions(array(
            'name' => md5($appInfos['nom']),
        ));

        $sessionManager = new SessionManager($sessionConfig);

        $chain = $sessionManager->getValidatorChain();
        $chain->attach('session.validate', array(new RemoteAddr(), 'isValid'));
        $chain->attach('session.validate', array(new HttpUserAgent(), 'isValid'));

        Container::setDefaultManager($sessionManager);
        
        return $sessionManager;
    }
}