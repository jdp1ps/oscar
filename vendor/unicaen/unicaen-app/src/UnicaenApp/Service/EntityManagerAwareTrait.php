<?php

namespace UnicaenApp\Service;

use Doctrine\ORM\EntityManager;

/**
 * Code commun aux classes utilisant le gestionnaire d'entité Doctrine.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
trait EntityManagerAwareTrait
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Spécifie le gestionnaire d'entité.
     * 
     * @param EntityManager $entityManager
     * @return self
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        
        return $this;
    }

    /**
     * Retourne le gestionnaire d'entité.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}