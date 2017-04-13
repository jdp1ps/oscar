<?php

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oscar\Entity\ProjectRepository;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ServiceManagerAwareTrait.
 *
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 04/09/15 11:46
 *
 * @copyright Certic (c) 2015
 */
trait ServiceManagerAwareTrait
{
    /** @var  ServiceManager */
    protected $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Get services
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceManager()->get('Logger');
    }

    /***
     * @return SearchService
     */
    protected function getSearchProjectService()
    {
        return $this->getServiceLocator()->get('Search');
    }

    /**
     * @return UserContext
     */
    protected function getUserContext()
    {
        return $this->getServiceLocator()->get('authUserContext');
    }

    /**
     * @return People
     */
    protected function getLdapUser()
    {
        return $this->getUserContext()->getLdapUser();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Repositories
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return ProjectRepository
     */
    protected function getProjectRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}
