<?php
namespace UnicaenApp\Form\Element;

/**
 * Élément composite de navigation au sein d'un formulaire multi-page.
 * Boutons présents selon le contexte : "Précédent", "Suivant", "Terminer", "Annuler".
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MultipageFormNav extends \Zend\Form\Element
{
    const NAME     = '_nav';
    const PREFIX   = '_';
    const NEXT     = '_next';
    const PREVIOUS = '_previous';
    const SUBMIT   = '_submit';
    const CANCEL   = '_cancel';
    const CONFIRM  = '_confirm';

    /**
     * @var boolean
     */
    private $activatePrevious = false;
    /**
     * @var boolean
     */
    private $activateNext = true;
    /**
     * @var boolean
     */
    private $activateSubmit = false;
    /**
     * @var boolean
     */
    private $activateCancel = true;
    /**
     * @var boolean
     */
    private $activateConfirm = false;

    /**
     * Constructeur.
     * 
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = self::NAME, $options = array())
    {
        parent::__construct($name, $options);

        $this->setActivatePrevious(false)
             ->setActivateNext(true)
             ->setActivateSubmit(false)
             ->setActivateCancel(true)
             ->setActivateConfirm(false);
    }

    /**
     * Autorise ou non le bouton "Précédent".
     *
     * @param boolean $value
     * @return MultipageFormNav
     */
    public function setActivatePrevious($value = true)
    {
        $this->activatePrevious = $value;
        return $this;
    }
    
    /**
     * Autorise ou non le bouton "Suivant".
     *
     * @param boolean $value
     * @return MultipageFormNav
     */
    public function setActivateNext($value = true)
    {
        $this->activateNext = $value;
        return $this;
    }
    
    /**
     * Autorise ou non le bouton "Terminer".
     *
     * @param boolean $value
     * @return MultipageFormNav
     */
    public function setActivateSubmit($value = true)
    {
        $this->activateSubmit = $value;
        return $this;
    }
    
    /**
     * Autorise ou non le bouton "Annuler".
     *
     * @param boolean $value
     * @return MultipageFormNav
     */
    public function setActivateCancel($value = true)
    {
        $this->activateCancel = $value;
        return $this;
    }
    
    /**
     * Autorise ou non le bouton "Confirmer".
     *
     * @param boolean $value
     * @return MultipageFormNav
     */
    public function setActivateConfirm($value = true)
    {
        $this->activateConfirm = $value;
        return $this;
    }

    /**
     * Indique si le bouton "Précédent" est autorisé ou non.
     *
     * @return boolean
     */
    public function getActivatePrevious()
    {
        return $this->activatePrevious;
    }
    
    /**
     * Indique si le bouton "Suivant" est autorisé ou non.
     *
     * @return boolean
     */
    public function getActivateNext()
    {
        return $this->activateNext;
    }
    
    /**
     * Indique si le bouton "Terminer" est autorisé ou non.
     *
     * @return boolean
     */
    public function getActivateSubmit()
    {
        return $this->activateSubmit;
    }
    
    /**
     * Indique si le bouton "Annuler" est autorisé ou non.
     *
     * @return boolean
     */
    public function getActivateCancel()
    {
        return $this->activateCancel;
    }
    
    /**
     * Indique si le bouton "Confirmer" est autorisé ou non.
     *
     * @return boolean
     */
    public function getActivateConfirm()
    {
        return $this->activateConfirm;
    }
}