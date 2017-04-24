<?php
namespace UnicaenApp\ORM\Event\Listeners;

use UnicaenAuth\Entity\Db\AbstractUser;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Common\Exception\RuntimeException;
use UnicaenApp\Entity\HistoriqueAwareInterface;

/**
 * Listener Doctrine permettant l'ajout automatique de l'heure de création/modification
 * et de l'auteur de création/modification de toute entité avant qu'elle soit persistée.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class HistoriqueListener implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var mixed
     */
    protected $identity;



    /**
     *
     * @param LifecycleEventArgs $args
     *
     * @return type
     * @throws RuntimeException
     */
    protected function updateHistorique(LifecycleEventArgs $args)
    {
        $em     = $args->getEntityManager();
        $entity = $args->getEntity();
        $user   = null;

        // l'entité doit implémenter l'interface requise
        if (!$entity instanceof HistoriqueAwareInterface) {
            return;
        }
        /* @var $entity \UnicaenApp\Entity\HistoriqueAwareInterface */

        // l'utilisateur connecté sera l'auteur de la création/modification
        if (($identity = $this->getIdentity())) {
            if (isset($identity['db']) && $identity['db'] instanceof AbstractUser) {
                $user = $identity['db'];
                /* @var $user AbstractUser */
            }
        }

        if (null === $user) {
            throw new RuntimeException("Aucun utilisateur connecté disponible pour la gestion de l'historique.");
        }

        $now = new \DateTime();

        /**
         * Historique
         */

        if (null === $entity->getHistoCreation()) {
            $entity->setHistoCreation($now);
        }

        if (null === $entity->getHistoCreateur()) {
            $entity->setHistoCreateur($user);
        }

        $entity->setHistoModificateur($user);
        $entity->setHistoModification($now);

        if (null === $entity->getHistoDestruction() && null === $entity->getHistoDestructeur()) {
            $entity->setHistoModification($now)
                ->setHistoModificateur($user);
        }

        if (null !== $entity->getHistoDestruction() && null === $entity->getHistoDestructeur()) {
            $entity->setHistoDestructeur($user);
        }
    }



    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateHistorique($args);
    }



    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->updateHistorique($args);
    }



    /**
     *
     * @param mixed $identity
     *
     * @return \Common\ORM\Event\Listeners\Histo
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }



    /**
     *
     * @return mixed
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            $authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            if ($authenticationService->hasIdentity()) {
                $this->identity = $authenticationService->getIdentity();
            }
        }

        return $this->identity;
    }



    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate];
    }
}