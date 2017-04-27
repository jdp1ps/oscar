<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of LdapStructureServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapStructureServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $pluginManager
     * @return LdapStructureService
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        return new LdapStructureService($pluginManager->getServiceLocator()->get('ldap_structure_service'));
    }
}