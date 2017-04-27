<?php
namespace UnicaenAuth\Authentication\Storage;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of ChainAuthenticationStorageServiceFactory
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
        $storages = [
            200 => 'UnicaenAuth\Authentication\Storage\Ldap',
            100 => 'UnicaenAuth\Authentication\Storage\Db',
        ];

        $chain = new Chain();

        foreach ($storages as $priority => $name) {
            $storage = $serviceLocator->get($name);
            $chain->getEventManager()->attach('read', [$storage, 'read'], $priority);
            $chain->getEventManager()->attach('write', [$storage, 'write'], $priority);
            $chain->getEventManager()->attach('clear', [$storage, 'clear'], $priority);
        }

        return $chain;
    }
}