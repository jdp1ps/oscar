<?php

namespace UnicaenAuth\Authentication\Storage;

use Zend\Authentication\Storage\StorageInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManager;

/**
 * Implémentation d'une chaîne de responsabilité permettant à plusieurs sources
 * de fournir les données sur l'identité authentifiée éventuelle.
 *
 * Exemples de sources disponibles :
 *  - Ldap (annuaire LDAP)
 *  - Db (table des utilisateurs en base de données)
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see ChainEvent
 * @see \UnicaenAuth\Service\ChainAuthenticationStorageServiceFactory
 * @see Ldap
 * @see Db
 */
class Chain implements StorageInterface, EventManagerAwareInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var ChainEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $resolvedIdentity;

    /**
     * Returns true if and only if storage is empty
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If it is impossible to determine whether storage is empty
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getStorage()->isEmpty();
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }

        $e = $this->getEvent();
        $this->getEventManager()->trigger('read', $e);

        $identity = $e->getContents();

        if ($identity) {
            $this->resolvedIdentity = $identity;
        }
        else {
            $this->resolvedIdentity = null;
        }

        return $this->resolvedIdentity;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \Zend\Authentication\Exception\ExceptionInterface If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $this->getStorage()->write($contents);

        $e = $this->getEvent();
        $e->setParams(compact('contents'));
        $this->getEventManager()->trigger('write', $e);
    }

    /**
     * Clears contents from storage
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {
        $this->getStorage()->clear();

        $e = $this->getEvent();
        $this->getEventManager()->trigger('clear', $e);
    }

    /**
     * getStorage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new \Zend\Authentication\Storage\Session());
        }
        return $this->storage;
    }

    /**
     * setStorage
     *
     * @param StorageInterface $storage
     * @return self
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return self
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
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager();
        }
        return $this->eventManager;
    }

    /**
     * @return ChainEvent
     */
    public function getEvent()
    {
        if (null === $this->event) {
            $this->event = new ChainEvent();
        }
        return $this->event;
    }

    /**
     * @param ChainEvent $event
     * @return self
     */
    public function setEvent(ChainEvent $event)
    {
        $this->event = $event;
        return $this;
    }
}