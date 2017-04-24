<?php
namespace UnicaenAuth\Provider\Identity;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Intsancie une chaîne de fournisseurs d'identité.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config'); //'unicaen-auth_module_options'
        if (!isset($config['unicaen-auth']['identity_providers']) || !$config['unicaen-auth']['identity_providers']) {
            throw new \UnicaenApp\Exception\RuntimeException("Aucun fournisseur d'identité spécifié dans la config.");
        }

        $providers = (array) $config['unicaen-auth']['identity_providers'];

        $chain = new Chain();
        $chain->setServiceLocator($serviceLocator);

        foreach ($providers as $priority => $name) {
            $provider = $serviceLocator->get($name);
            $chain->getEventManager()->attach('getIdentityRoles', [$provider, 'injectIdentityRoles'], $priority);
        }

        return $chain;
    }
}