<?php

namespace UnicaenAuth\Event\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Event\UserAuthenticatedEvent;

/**
 * Classe abstraites pour les classes désirant scruter un événement déclenché lors de l'authentification
 * utilisateur.
 *
 * Événements disponibles :
 * - juste avant que l'entité utilisateur ne soit persistée.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see UserAuthenticatedEvent
 */
abstract class AuthenticatedUserSavedAbstractListener implements ListenerAggregateInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * Renseigne les relations 'intervenant' et 'personnel' avant que l'objet soit persisté.
     *
     * @param Event $e
     */
    abstract public function onUserAuthenticatedPrePersist(UserAuthenticatedEvent $e);

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
                'UnicaenAuth\Service\User',
                UserAuthenticatedEvent::PRE_PERSIST,
                [$this, 'onUserAuthenticatedPrePersist'],
                100);
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}