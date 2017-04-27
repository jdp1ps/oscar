<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace UnicaenAuth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of building the {@see \BjyAuthorize\Service\Authorize} service
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class AuthorizeServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Application\Service\Authorize
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuthorizeService($serviceLocator->get('BjyAuthorize\Config'), $serviceLocator);
    }
}
