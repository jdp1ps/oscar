<?php

namespace UnicaenAuth\Service\Traits;

use UnicaenAuth\Service\RoleService;
use RuntimeException;

/**
 * Description of RoleServiceAwareTrait
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
trait RoleServiceAwareTrait
{
    /**
     * @var RoleService
     */
    private $serviceRole;



    /**
     * @param RoleService $serviceRole
     *
     * @return self
     */
    public function setServiceRole(RoleService $serviceRole)
    {
        $this->serviceRole = $serviceRole;

        return $this;
    }



    /**
     * @return RoleService
     * @throws RuntimeException
     */
    public function getServiceRole()
    {
        if (empty($this->serviceRole)) {
            if (!method_exists($this, 'getServiceLocator')) {
                throw new RuntimeException('La classe ' . get_class($this) . ' n\'a pas accès au ServiceLocator.');
            }

            $serviceLocator = $this->getServiceLocator();
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }
            $this->serviceRole = $serviceLocator->get('UnicaenAuth\Service\Role');
        }

        return $this->serviceRole;
    }
}