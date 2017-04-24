<?php

namespace UnicaenApp\View\Helper;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Description of HistoriqueViewHelper
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class HistoriqueViewHelper extends AbstractHtmlElement
{
    /**
     * @var HistoriqueAwareInterface
     */
    protected $entity;

    /**
     * @var \DateTime
     */
    protected $histoModification;

    /**
     * @var ZfcUser\Entity\UserInterface
     */
    protected $histoModificateur;

    /**
     * @var boolean
     */
    protected $isDeleted;



    /**
     * @return HistoriqueAwareInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }



    /**
     * @param HistoriqueAwareInterface $entity
     */
    public function setEntity(HistoriqueAwareInterface $entity)
    {
        $this->entity = $entity;

        if ($this->entity->estNonHistorise()) {
            $this->setHistoModificateur($entity->getHistoModificateur());
            $this->setHistoModification($entity->getHistoModification());
            $this->setIsDeleted(false);
        } else {
            $this->setHistoModificateur($entity->getHistoDestructeur());
            $this->setHistoModification($entity->getHistoDestruction());
            $this->setIsDeleted(true);
        }
        return $this;
    }



    /**
     * @return \DateTime
     */
    public function getHistoModification()
    {
        return $this->histoModification;
    }



    /**
     * @param \DateTime $histoModification
     */
    public function setHistoModification($histoModification)
    {
        $this->histoModification = $histoModification;
        return $this;
    }



    /**
     * @return ZfcUser\Entity\UserInterface
     */
    public function getHistoModificateur()
    {
        return $this->histoModificateur;
    }



    /**
     * @param ZfcUser\Entity\UserInterface $histoModificateur
     */
    public function setHistoModificateur($histoModificateur)
    {
        $this->histoModificateur = $histoModificateur;
        return $this;
    }



    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }



    /**
     * @param boolean $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }



    /**
     *
     * @param HistoriqueAwareInterface $entity
     *
     * @return self
     */
    public function __invoke(HistoriqueAwareInterface $entity = null)
    {
        if ($entity) $this->setEntity($entity);

        return $this;
    }



    /**
     * Retourne le code HTML.
     *
     * @return string Code HTML
     */
    public function __toString()
    {
        return $this->render();
    }



    /**
     *
     *
     * @return string Code HTML
     */
    public function render()
    {
        if (!$this->getHistoModificateur() || !$this->getHistoModification()) {
            return '';
        }

        $action = ($this->isDeleted) ? 'Suppression' : 'Dernière modification';

        $template = "<hr /><div class=\"pull-right\"><em>$action: Le %s par %s</em></div><br />";

        return sprintf($template, $this->getHistoModification()->format('d/m/Y à H:i'), $this->getHistoModificateur());
    }
}