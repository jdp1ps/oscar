<?php

namespace UnicaenApp\Entity;

use ZfcUser\Entity\UserInterface;

/**
 * Code commun aux entités possédant une gestion d'historique.
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
trait HistoriqueAwareTrait
{
    /**
     * @var \DateTime
     */
    protected $histoCreation;

    /**
     * @var \DateTime
     */
    protected $histoModification;

    /**
     * @var \DateTime
     */
    protected $histoDestruction;

    /**
     * @var UserInterface
     */
    protected $histoCreateur;

    /**
     * @var UserInterface
     */
    protected $histoModificateur;

    /**
     * @var UserInterface
     */
    protected $histoDestructeur;



    /**
     * Set histoCreation
     *
     * @param \DateTime $histoCreation
     *
     * @return self
     */
    public function setHistoCreation($histoCreation)
    {
        $this->histoCreation = $histoCreation;

        return $this;
    }



    /**
     * Get histoCreation
     *
     * @return \DateTime
     */
    public function getHistoCreation()
    {
        return $this->histoCreation;
    }



    /**
     * Set histoDestruction
     *
     * @param \DateTime $histoDestruction
     *
     * @return self
     */
    public function setHistoDestruction($histoDestruction)
    {
        $this->histoDestruction = $histoDestruction;

        return $this;
    }



    /**
     * Get histoDestruction
     *
     * @return \DateTime
     */
    public function getHistoDestruction()
    {
        return $this->histoDestruction;
    }



    /**
     * Set histoModification
     *
     * @param \DateTime $histoModification
     *
     * @return self
     */
    public function setHistoModification($histoModification)
    {
        $this->histoModification = $histoModification;

        return $this;
    }



    /**
     * Get histoModification
     *
     * @return \DateTime
     */
    public function getHistoModification()
    {
        return $this->histoModification;
    }



    /**
     * Set histoModificateur
     *
     * @param UserInterface $histoModificateur
     *
     * @return self
     */
    public function setHistoModificateur(UserInterface $histoModificateur = null)
    {
        $this->histoModificateur = $histoModificateur;

        return $this;
    }



    /**
     * Get histoModificateur
     *
     * @return UserInterface
     */
    public function getHistoModificateur()
    {
        return $this->histoModificateur;
    }



    /**
     * Set histoDestructeur
     *
     * @param UserInterface $histoDestructeur
     *
     * @return self
     */
    public function setHistoDestructeur(UserInterface $histoDestructeur = null)
    {
        $this->histoDestructeur = $histoDestructeur;

        return $this;
    }



    /**
     * Get histoDestructeur
     *
     * @return UserInterface
     */
    public function getHistoDestructeur()
    {
        return $this->histoDestructeur;
    }



    /**
     * Set histoCreateur
     *
     * @param UserInterface $histoCreateur
     *
     * @return self
     */
    public function setHistoCreateur(UserInterface $histoCreateur = null)
    {
        $this->histoCreateur = $histoCreateur;

        return $this;
    }



    /**
     * Get histoCreateur
     *
     * @return UserInterface
     */
    public function getHistoCreateur()
    {
        return $this->histoCreateur;
    }



    /**
     * Détermine si l'entité est historisée ou non
     *
     * @param \DateTime|null $dateObs
     *
     * @return bool
     */
    public function estNonHistorise(\DateTime $dateObs = null)
    {
        if (empty($dateObs)) $dateObs = new \DateTime();


        $dObs = $dateObs->format('Y-m-d');
        $dDeb = $this->getHistoCreation() ? $this->getHistoCreation()->format('Y-m-d') : null;
        $dFin = $this->getHistoDestruction() ? $this->getHistoDestruction()->format('Y-m-d') : null;

        if ($dDeb && !($dDeb <= $dObs)) return false;
        if ($dFin && !($dObs < $dFin)) return false;

        return true;
    }
}