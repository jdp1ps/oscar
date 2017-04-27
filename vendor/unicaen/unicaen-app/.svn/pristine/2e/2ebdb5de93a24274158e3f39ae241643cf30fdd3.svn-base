<?php
namespace UnicaenApp\Controller\Plugin;

use \UnicaenApp\Service\Ldap\Structure;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Description of LdapStructureService
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapStructureService extends AbstractPlugin
{
    protected $service;
    
    /**
     * 
     * @param Structure $service
     */
    public function __construct(Structure $service)
    {
        $this->service = $service;
    }
    
    /**
     * 
     * @return Structure
     */
    public function __invoke()
    {
        return $this->service;
    }
}