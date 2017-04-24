<?php
namespace UnicaenApp\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormRow;

/**
 * Aide de vue générant chaque élément d'un fieldset de formulaire multi-page.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MultipageFormRow extends FormRow
{
    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element
     * @param null|string $labelPosition
     * @return string
     */
    public function render(ElementInterface $element, $labelPosition = null)
    {
        if ($element instanceof \UnicaenApp\Form\Element\MultipageFormNav) {
            return $this->getView()->multipageFormNav($element);
        }
        elseif ($element instanceof \UnicaenApp\Form\Element\DateInfSup) {
            return $this->getView()->formRowDateInfSup($element);
        }
        
        return parent::render($element);
    }

}