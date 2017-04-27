<?php

namespace UnicaenAuth\Authentication\Storage;

use PDOException;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Authentication\Exception\InvalidArgumentException;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Storage\StorageInterface;
use ZfcUser\Mapper\UserInterface as UserMapper;
use Doctrine\DBAL\DBALException;

/**
 * Db authentication storage.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Db implements ChainableStorage, ServiceManagerAwareInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var UserMapper
     */
    protected $mapper;

    /**
     * @var mixed
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
     * @params ChainEvent $e
     * @throws InvalidArgumentException If reading contents from storage is impossible
     * @return void
     */
    public function read(ChainEvent $e)
    {
        if (!$this->resolvedIdentity) {
            $identity = $this->findIdentity();
            if ($identity) {
                $this->resolvedIdentity = $identity;
            } 
            else {
                $this->resolvedIdentity = null;
            }
        }
        
        $e->addContents('db', $this->resolvedIdentity);
    }

    /**
     * Writes $contents to storage
     *
     * @params ChainEvent $e
     * @throws \Zend\Authentication\Exception\InvalidArgumentException If writing $contents to storage is impossible
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
     * @params ChainEvent $e
     * @throws \Zend\Authentication\Exception\InvalidArgumentException If clearing contents from storage is impossible
     * @return void
     */
    public function clear(ChainEvent $e)
    {
        $this->resolvedIdentity = null;
        $this->getStorage()->clear();
    }
    
    /**
     * 
     * @return null
     */
    protected function findIdentity()
    {
        $id = $this->getStorage()->read();
        
        // si on obtient autre chose qu'un scalaire, l'utilisateur a déjà été 
        // recherché/trouvé dans la base de données
        if ($id && !is_scalar($id)) {
            return $id;
        }
            
        /**
         * 1ere tentative :
         * 
         * Recherche dans la base de données de l'utilisateur dont l'id correspond à ce qui
         * est stoqué en session.
         * 
         * NB: En cas de problème de connexion ou de service 'zfcuser_user_mapper' introuvable,
         * cela signifie sans doute que l'application n'utilise pas de table des utilisateurs.
         */
        if (is_int($id) || is_scalar($id)) {
            try {
                $identity = $this->getMapper()->findById($id);
            }
            catch (DBALException $dbale) {
                $identity = null;
            }
            catch (PDOException $pdoe) {
                $identity = null;
            }
            catch (ServiceNotFoundException $e) {
                $identity = null;
            }
        }
        
        /**
         * 2e tentative : 
         * 
         * Recherche de l'utilisateur dont le supannAliasLogin correspond à ce qui
         * est stoqué en session.
         * 
         * NB: En cas de problème de connexion ou de service 'zfcuser_user_mapper' introuvable,
         * cela signifie sans doute que l'application n'utilise pas de table des utilisateurs.
         */
        if (is_string($id)) {
            try {
                $identity = $this->getMapper()->findByUsername($id);
            }
            catch (DBALException $dbale) {
                $identity = null;
            }
            catch (PDOException $pdoe) {
                $identity = null;
            }
            catch (ServiceNotFoundException $e) {
                $identity = null;
            }
        }
        
        return $identity;
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
     * @return Db
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * getMapper
     *
     * @return UserMapper
     */
    public function getMapper()
    {
        if (null === $this->mapper) {
            $this->mapper = $this->getServiceManager()->get('zfcuser_user_mapper');
        }
        return $this->mapper;
    }

    /**
     * setMapper
     *
     * @param UserMapper $mapper
     * @return Db
     */
    public function setMapper(UserMapper $mapper = null)
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
}