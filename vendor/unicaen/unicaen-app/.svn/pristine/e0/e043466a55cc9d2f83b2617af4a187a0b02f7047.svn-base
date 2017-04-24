<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of LdapGroupServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapGroupServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $pluginManager
     * @return LdapGroupService
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        return new LdapGroupService($pluginManager->getServiceLocator()->get('ldap_group_service'));
    }
}