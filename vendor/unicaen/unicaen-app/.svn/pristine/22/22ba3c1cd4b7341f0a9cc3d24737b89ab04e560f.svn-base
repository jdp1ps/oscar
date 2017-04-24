<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of LdapPeopleServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapPeopleServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $pluginManager
     * @return LdapPeopleService
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        return new LdapPeopleService($pluginManager->getServiceLocator()->get('ldap_people_service'));
    }
}