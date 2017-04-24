<?php

namespace UnicaenAuth\Service\Traits;

use UnicaenAuth\Service\CategoriePrivilegeService;
use RuntimeException;

/**
 * Description of CategoriePrivilegeServiceAwareTrait
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
trait CategoriePrivilegeServiceAwareTrait
{
    /**
     * @var CategoriePrivilegeService
     */
    private $serviceCategoriePrivilege;



    /**
     * @param CategoriePrivilegeService $serviceCategoriePrivilege
     *
     * @return self
     */
    public function setServiceCategoriePrivilege(CategoriePrivilegeService $serviceCategoriePrivilege)
    {
        $this->serviceCategoriePrivilege = $serviceCategoriePrivilege;

        return $this;
    }



    /**
     * @return CategoriePrivilegeService
     * @throws RuntimeException
     */
    public function getServiceCategoriePrivilege()
    {
        if (empty($this->serviceCategoriePrivilege)) {
            if (!method_exists($this, 'getServiceLocator')) {
                throw new RuntimeException('La classe ' . get_class($this) . ' n\'a pas accès au ServiceLocator.');
            }

            $serviceLocator = $this->getServiceLocator();
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }
            $this->serviceCategoriePrivilege = $serviceLocator->get('UnicaenAuth\Service\CategoriePrivilege');
        }

        return $this->serviceCategoriePrivilege;
    }
}