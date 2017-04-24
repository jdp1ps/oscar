<?php

namespace UnicaenAuth\Authentication\Storage;

use UnicaenAuth\Entity\Ldap\People;
use UnicaenApp\Mapper\Ldap\People as LdapPeopleMapper;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Authentication\Exception\InvalidArgumentException;
use Zend\Authentication\Storage\StorageInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Authentication\Storage\Session;

/**
 * Ldap authentication storage.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Ldap implements ChainableStorage, ServiceManagerAwareInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var LdapPeopleMapper
     */
    protected $mapper;
    
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var People
     */
    protected $resolvedIdentity;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws InvalidArgumentException If reading contents from storage is impossible
     * @return People
     */
    public function read(ChainEvent $e)
    {
        $identity = $this->findIdentity();
        
        $e->addContents('ldap', $identity);
        
        return $identity;
    }

    /**
     * 
     * @return null
     */
    protected function findIdentity()
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }

        $identity = $this->getStorage()->read();

        if (is_scalar($identity)) {
            try {
                $identity = $this->getMapper()->findOneByUsername($identity);
            }
            catch (\Zend\Ldap\Exception\LdapException $exc) {
                $identity = null;
            }
            catch (\UnicaenApp\Exception $exc) {
                $identity = null;
            }
        }

        if ($identity) {
            $this->resolvedIdentity = new People($identity);
        } else {
            $this->resolvedIdentity = null;
        }

        return $this->resolvedIdentity;
    }
    
    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws InvalidArgumentException If writing $contents to storage is impossible
     * @return void
     */
    public function write(ChainEvent $e)
    {
        $contents = $e->getParam('contents');
        
        $this->resolvedIdentity = null;
        $this->getStorage()->write($contents);
    }

    /**
     * Clears contents from storage
     *
     * @throws InvalidArgumentException If clearing contents from storage is impossible
     * @return void
     */
    public function clear(ChainEvent $e)
    {
        $this->resolvedIdentity = null;
        $this->getStorage()->clear();
    }

    /**
     * getStorage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new Session());
        }
        return $this->storage;
    }

    /**
     * setStorage
     *
     * @param StorageInterface $storage
     * @access public
     * @return Ldap
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * getMapper
     *
     * @return LdapPeopleMapper
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = $this->getServiceManager()->get('ldap_people_mapper');
        }
        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param LdapPeopleMapper $mapper
     * @return Ldap
     */
    public function setMapper(LdapPeopleMapper $mapper = null)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $locator
     * @return self
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options = null)
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
}
