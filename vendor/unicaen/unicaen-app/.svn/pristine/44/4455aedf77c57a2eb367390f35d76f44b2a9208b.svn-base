<?php

namespace UnicaenApp\Service;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Transmet à une instance le gestionnaire d'entité Doctrine, ssi sa classe implémente l'interface qui va bien.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see EntityManagerAwareInterface
 */
class EntityManagerAwareInitializer implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (method_exists($serviceLocator, 'getServiceLocator')) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
//        if ($serviceLocator instanceof ControllerManager || $serviceLocator instanceof FormElementManager) {
//            $serviceLocator = $serviceLocator->getServiceLocator();
//        }
        if ($instance instanceof EntityManagerAwareInterface) {
            $instance->setEntityManager($serviceLocator->get('doctrine.entitymanager.orm_default'));
        }
    }
}
