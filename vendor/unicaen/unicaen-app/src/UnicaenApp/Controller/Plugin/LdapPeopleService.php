<?php
namespace UnicaenApp\Controller\Plugin;

use UnicaenApp\Service\Ldap\People;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Description of LdapPeopleService
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapPeopleService extends AbstractPlugin
{
    protected $service;
    
    /**
     * 
     * @param People $service
     */
    public function __construct(People $service)
    {
        $this->service = $service;
    }
    
    /**
     * 
     * @return People
     */
    public function __invoke()
    {
        return $this->service;
    }
}