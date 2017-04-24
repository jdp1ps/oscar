<?php

namespace UnicaenAuth\Guard;

use BjyAuthorize\Guard\Route;
use Zend\ServiceManager\ServiceLocatorInterface;
use UnicaenAuth\Provider\Privilege\PrivilegeProviderAwareTrait;
use UnicaenApp\Traits\SessionContainerTrait;


/**
 * Description of PrivilegeRoute
 *
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class PrivilegeRoute extends Route
{
    use PrivilegeProviderAwareTrait;
    use SessionContainerTrait;



    public function __construct(array $rules, ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        parent::__construct($this->privilegesToRoles($rules), $serviceLocator);
    }



    protected function privilegesToRoles(array $rules)
    {
        $pr = $this->getPrivilegeProvider()->getPrivilegesRoles();

        foreach ($rules as $index => $rule) {
            if (isset($rule['privileges'])) {
                $rolesCount    = 0;
                $privileges    = (array)$rule['privileges'];
                $rule['roles'] = isset($rule['roles']) ? (array)$rule['roles'] : [];
                foreach ($pr as $privilege => $roles) {
                    if (in_array($privilege, $privileges)) {
                        $rolesCount += count($roles);
                        $rule['roles'] = array_unique(array_merge($rule['roles'], $roles));
                    }
                }
                unset($rule['privileges']);
                if (0 < count($rule['roles'])) {
                    $rules[$index] = $rule;
                }else{
                    unset($rules[$index]);
                }
            }
        }

        return $rules;
    }



    /**
     * Pour récupérer le serviceLocator depuis les traits de service
     *
     * @return ServiceLocatorInterface
     */
    protected function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    public static function getResourceId($route)
    {
        return 'route/'.$route;
    }

}