<?php
namespace UnicaenAuth\Service;

use PDOException;
use UnicaenApp\Exception;
use UnicaenApp\Mapper\Ldap\People as LdapPeopleMapper;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Options\AuthenticationOptionsInterface;
use ZfcUser\Options\ModuleOptions as ZfcUserModuleOptions;

/**
 * Service d'enregistrement dans la table des utilisateurs de l'application
 * de l'utilisateur authentifié avec succès.
 *
 * Est notifié via la méthode 'userAuthenticated()' lorsque l'authentification
 * est terminée avec succès.
 *
 * @see \UnicaenAuth\Authentication\Adapter\AbstractFactory
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class User implements ServiceLocatorAwareInterface, EventManagerAwareInterface
{
    use ServiceLocatorAwareTrait;


    const EVENT_USER_AUTHENTICATED_PRE_PERSIST = 'userAuthenticated.prePersist';

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var AuthenticationOptionsInterface
     */
    protected $zfcUserOptions;

    /**
     * @var LdapPeopleMapper
     */
    protected $ldapPeopleMapper;

    /**
     * Save authenticated user in database from LDAP data.
     *
     * @return bool
     */
    public function userAuthenticated(AuthEvent $e)
    {
        if (!$this->getOptions()->getSaveLdapUserInDatabase()) {
            return false;
        }
        if (!($username = $e->getIdentity())) {
            return false;
        }

        if (is_int($username)) {
            // c'est un id : cela signifie que l'utilisateur existe déjà dans la bdd (et pas dans le LDAP), rien à faire
            return true;
        }

        if (!is_string($username)) {
            throw new Exception("Identité rencontrée inattendue.");
        }

        // recherche de l'individu dans l'annuaire LDAP
        $ldapPeople = $this->getLdapPeopleMapper()->findOneByUsername($username);
        if (!$ldapPeople) {
            return false;
        }

        // update/insert de l'utilisateur dans la table de l'appli
        $mapper = $this->getServiceLocator()->get('zfcuser_user_mapper'); /* @var $mapper \ZfcUserDoctrineORM\Mapper\User */
        try {
            $entity = $mapper->findByUsername($username);
            if (!$entity) {
                $entityClass = $this->getZfcUserOptions()->getUserEntityClass();
                $entity = new $entityClass;
                $entity->setUsername($username);
                $method = 'insert';
            }
            else {
                $method = 'update';
            }
            $entity->setEmail($ldapPeople->getMail());
            $entity->setDisplayName($ldapPeople->getDisplayName());
            $entity->setPassword('ldap');
            $entity->setState(in_array('deactivated', ldap_explode_dn($ldapPeople->getDn(), 1)) ? 0 : 1);

            // déclenche l'événement donnant aux applications clientes l'opportunité de modifier l'entité
            // utilisateur avant qu'elle ne soit persistée
            $event = new UserAuthenticatedEvent(UserAuthenticatedEvent::PRE_PERSIST);
            $event
                    ->setDbUser($entity)
                    ->setLdapUser($ldapPeople)
                    ->setTarget($this);
            $this->getEventManager()->trigger($event);

            // persist
            $mapper->$method($entity);
        }
        catch (PDOException $pdoe) {
            throw new Exception("Impossible d'enregistrer l'utilisateur authentifié dans la base de données.", null, $pdoe);
        }

        return true;
    }


    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
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
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers([
            __CLASS__,
            get_called_class(),
        ]);
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * get ldap people mapper
     *
     * @return LdapPeopleMapper
     */
    public function getLdapPeopleMapper()
    {
        if (null === $this->ldapPeopleMapper) {
            $this->ldapPeopleMapper = $this->getServiceLocator()->get('ldap_people_mapper');
        }
        return $this->ldapPeopleMapper;
    }

    /**
     * set ldap people mapper
     *
     * @param LdapPeopleMapper $mapper
     * @return User
     */
    public function setLdapPeopleMapper(LdapPeopleMapper $mapper)
    {
        $this->ldapPeopleMapper = $mapper;
        return $this;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options)
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
            $this->setOptions($this->getServiceLocator()->get('unicaen-auth_module_options'));
        }
        return $this->options;
    }

    /**
     * @param ZfcUserModuleOptions $options
     */
    public function setZfcUserOptions(ZfcUserModuleOptions $options)
    {
        $this->zfcUserOptions = $options;
        return $this;
    }

    /**
     * @return ZfcUserModuleOptions
     */
    public function getZfcUserOptions()
    {
        if (!$this->zfcUserOptions instanceof ZfcUserModuleOptions) {
            $this->setZfcUserOptions($this->getServiceLocator()->get('zfcuser_module_options'));
        }
        return $this->zfcUserOptions;
    }
}