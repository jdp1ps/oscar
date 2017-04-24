<?php
namespace UnicaenAuth\Authentication\Adapter;

use phpCAS;
use UnicaenApp\Exception;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Authentication\Exception\UnexpectedValueException;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcUser\Authentication\Adapter\AbstractAdapter;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Authentication\Adapter\ChainableAdapter;

/**
 * CAS authentication adpater
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Cas extends AbstractAdapter implements ServiceManagerAwareInterface, EventManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $casOptions;

    /**
     * @var phpCAS
     */
    protected $casClient;

    /**
     * Réalise l'authentification.
     *
     * @param AuthEvent $e
     * @return boolean
     * @throws UnexpectedValueException
     * @see ChainableAdapter
     */
    public function authenticate(AuthEvent $e)
    {
//        if ($e->getIdentity()) {
//            return;
//        }
	/* DS : modification liée à une boucle infinie lors de l'authentification CAS */
	if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
                    ->setCode(AuthenticationResult::SUCCESS)
                    ->setMessages(['Authentication successful.']);
            return;
        }

        $config = $this->getOptions()->getCas();
        if (!$config) {
            return; // NB: l'authentification CAS est désactivée ssi le tableau des options est vide
        }

        error_reporting($oldErrorReporting = error_reporting() & ~E_NOTICE);

        $this->getCasClient()->forceAuthentication();

        // at this step, the user has been authenticated by the CAS server
        // and the user's login name can be read with phpCAS::getUser().

        $identity = $this->getCasClient(false)->getUser();

        error_reporting($oldErrorReporting);

        $e->setIdentity($identity);
        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(['Authentication successful.']);

        $this->getEventManager()->trigger('userAuthenticated', $e);
    }

    /**
     *
     * @param AuthEvent $e
     * @see ChainableAdapter
     */
    public function logout(AuthEvent $e)
    {
        if (!$this->getOptions()->getCas()) {
            return; // NB: l'authentification CAS est désactivée ssi le tableau des options est vide
        }

        if ($this->getCasClient()->isAuthenticated()) {
            $router = $this->getServiceManager()->get('router'); /* @var $router TreeRouteStack */
            $returnUrl = $router->getRequestUri()->setPath($router->getBaseUrl())->toString();
            $this->getCasClient(false)->logoutWithRedirectService($returnUrl);
        }
    }

    /**
     * Retourne le client CAS.
     *
     * @param boolean $initClient
     * @return phpCAS
     * @throws Exception
     */
    public function getCasClient($initClient = true)
    {
        if (null === $this->casClient) {
            $this->casClient = new phpCAS();
        }

        if (!$initClient) {
            return $this->casClient;
        }

        if (null === $this->casOptions) {
            $config = $this->getOptions()->getCas();
            if (!isset($config['connection']['default']['params']) || !$config['connection']['default']['params']) {
                throw new Exception("Les paramètres de connexion au serveur CAS sont invalides.");
            }
            $this->casOptions = $config['connection']['default']['params'];
        }

        $options = $this->casOptions;

        if (array_key_exists('debug', $options) && (bool) $options['debug']) {
            $this->casClient->setDebug();
        }

        // initialize phpCAS
        $this->casClient->client($options['version'], $options['hostname'], $options['port'], $options['uri'], true);
        // no SSL validation for the CAS server
        $this->casClient->setNoCasServerValidation();

        return $this->casClient;
    }

    /**
     * Spécifie le client CAS.
     *
     * @param phpCAS $casClient
     * @return self
     */
    public function setCasClient(phpCAS $casClient)
    {
        $this->casClient = $casClient;
        return $this;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $options = array_merge(
                    $this->getServiceManager()->get('zfcuser_module_options')->toArray(),
                    $this->getServiceManager()->get('unicaen-auth_module_options')->toArray());
            $this->setOptions(new ModuleOptions($options));
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

    /**
     * Retrieve EventManager instance
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return self
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }
}