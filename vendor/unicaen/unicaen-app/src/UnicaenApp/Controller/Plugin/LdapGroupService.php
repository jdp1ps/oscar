<?php
namespace UnicaenApp\Controller\Plugin;

use \UnicaenApp\Service\Ldap\Group;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Description of LdapGroupService
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapGroupService extends AbstractPlugin
{
    protected $service;
    
    /**
     * 
     * @param Group $service
     */
    public function __construct(Group $service)
    {
        $this->service = $service;
    }
    
    /**
     * 
     * @return Group
     */
    public function __invoke()
    {
        return $this->service;
    }
}