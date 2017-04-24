<?php
namespace UnicaenLdap;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class LdapFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Structure
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options \UnicaenLdap\Options\ModuleOptions */
        $options = $serviceLocator->get('ldapOptions');
        return new Ldap( $options->getLdap() );
    }
}
