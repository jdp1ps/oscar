<?php
namespace UnicaenApp\Service\Ldap;

use Zend\Ldap\Ldap;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class GroupFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Group
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('unicaen-app_module_options')->getLdap();
//        if (!$config) {
//            throw new \UnicaenApp\Exception(
//                    "Impossible de créer le service d'accès aux structures " . 
//                    "car aucune info de connexion à l'annuaire LDAP n'a été fournie (option 'ldap_connection_infos').");
//        }
        $options = isset($config['connection']['default']['params']) ? $config['connection']['default']['params'] : array();
        return new Group(new Ldap($options));
    }
}
