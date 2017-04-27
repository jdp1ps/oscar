<?php

namespace UnicaenAuth\Service;

use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Options\ModuleOptions;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class AbstractService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * @var \BjyAuthorize\Service\Authorize
     */
    private $serviceAuthorize;



    /**
     * @return \UnicaenAuth\Service\AuthorizeService
     */
    protected function getServiceAuthorize()
    {
        if (!$this->serviceAuthorize) {
            $this->serviceAuthorize = $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
        }

        return $this->serviceAuthorize;
    }



    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        if (!$this->entityManager) {
            $moduleOptions = $this->getServiceLocator()->get('unicaen-auth_module_options');
            /* @var $moduleOptions ModuleOptions */
            $this->entityManager = $this->getServiceLocator()->get($moduleOptions->getEntityManagerName());
        }

        return $this->entityManager;
    }
}
