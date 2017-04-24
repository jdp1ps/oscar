<?php
namespace UnicaenAuth\Authentication\Adapter;

use PDOException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use UnicaenAuth\Options\ModuleOptions;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Options\AuthenticationOptionsInterface;

/**
 * Adpater d'authentification à partir de la base de données.
 * 
 * Ajout par rapport à la classe mère : si aucune base de données ou table n'existe,
 * l'authentification ne plante pas (i.e. renvoit false).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Db extends \ZfcUser\Authentication\Adapter\Db implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Authentification.
     *
     * @param AuthEvent $e
     * @return boolean
     */
    public function authenticate(AuthEvent $e)
    {
        if ($e->getIdentity()) {
            return;
        }
        
        try {
            $result = parent::authenticate($e);
        }
        catch (PDOException $e) {
            return false;
        }
        catch (ServiceNotFoundException $e) {
            return false;
        }
       
        return $result;
    }

    /**
     * @param AuthenticationOptionsInterface $options
     * @return self
     */
    public function setOptions(AuthenticationOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('unicaen-auth_module_options'));
        }
        return $this->options;
    }

    /**
     * Get service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return self
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}