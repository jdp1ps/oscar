<?php

namespace UnicaenAuth\Service\Traits;

use UnicaenAuth\Service\UserContext;
use RuntimeException;

/**
 * Description of UserContextServiceAwareTrait
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
trait UserContextServiceAwareTrait
{
    /**
     * @var UserContext
     */
    private $serviceUserContext;



    /**
     * @param UserContext $serviceUserContext
     *
     * @return self
     */
    public function setServiceUserContext(UserContext $serviceUserContext)
    {
        $this->serviceUserContext = $serviceUserContext;

        return $this;
    }



    /**
     * @return UserContext
     * @throws RuntimeException
     */
    public function getServiceUserContext()
    {
        if (empty($this->serviceUserContext)) {
            if (!method_exists($this, 'getServiceLocator')) {
                throw new RuntimeException('La classe ' . get_class($this) . ' n\'a pas accès au ServiceLocator.');
            }

            $serviceLocator = $this->getServiceLocator();
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }
            $this->serviceUserContext = $serviceLocator->get('UnicaenAuth\Service\UserContext');
        }

        return $this->serviceUserContext;
    }
}