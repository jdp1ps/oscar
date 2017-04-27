<?php

namespace UnicaenApp\Entity;
use ZfcUser\Entity\UserInterface;

/**
 * Interface des entités possédant une gestion d'historique.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface HistoriqueAwareInterface
{
    /**
     * Set histoCreation
     *
     * @param \DateTime $histoCreation
     * @return self
     */
    public function setHistoCreation($histoCreation);

    /**
     * Get histoCreation
     *
     * @return \DateTime 
     */
    public function getHistoCreation();

    /**
     * Set histoDestruction
     *
     * @param \DateTime $histoDestruction
     * @return self
     */
    public function setHistoDestruction($histoDestruction);

    /**
     * Get histoDestruction
     *
     * @return \DateTime 
     */
    public function getHistoDestruction();

    /**
     * Set histoModification
     *
     * @param \DateTime $histoModification
     * @return self
     */
    public function setHistoModification($histoModification);

    /**
     * Get histoModification
     *
     * @return \DateTime 
     */
    public function getHistoModification();

    /**
     * Set histoModificateur
     *
     * @param UserInterface $histoModificateur
     * @return self
     */
    public function setHistoModificateur(UserInterface $histoModificateur = null);

    /**
     * Get histoModificateur
     *
     * @return UserInterface
     */
    public function getHistoModificateur();

    /**
     * Set histoDestructeur
     *
     * @param UserInterface $histoDestructeur
     * @return self
     */
    public function setHistoDestructeur(UserInterface $histoDestructeur = null);

    /**
     * Get histoDestructeur
     *
     * @return UserInterface
     */
    public function getHistoDestructeur();

    /**
     * Set histoCreateur
     *
     * @param UserInterface $histoCreateur
     * @return self
     */
    public function setHistoCreateur(UserInterface $histoCreateur = null);

    /**
     * Get histoCreateur
     *
     * @return UserInterface
     */
    public function getHistoCreateur();



    /**
     * @param \DateTime|null $dateObs
     *
     * @return boolean
     */
    public function estNonHistorise(\DateTime $dateObs = null);
}