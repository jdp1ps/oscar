<?php
namespace UnicaenApp\Form\View\Helper;

use UnicaenApp\Form\Element\MultipageFormNav as MultipageFormNavElement;
use Zend\Form\Element\Submit;
use Zend\Form\Exception\InvalidArgumentException;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Aide de vue générant l'élément de navigation au sein d'un formulaire multi-pages.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MultipageFormNav extends AbstractHelper
{
    /**
     * Point d'entrée.
     * 
     * @return string
     */
    public function __invoke(MultipageFormNavElement $element)
    {
        return $this->render($element);
    }
    
    /**
     * Génère le code HTML.
     * 
     * @param \UnicaenApp\Form\Element\MultipageFormNav $element
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(MultipageFormNavElement $element)
    {
        $view = $this->getView();
        $name = $element->getName();
        
        $cancel = $prev = $next = $submit = $confirm = null;
        
        $labelNext    = "Suivant >";
        $labelPrev    = "< Précédent";
        $labelCancel  = "Annuler";
        $labelSubmit  = "Terminer";
        $labelConfirm = "Confirmer et enregistrer";
        
        $titleNext    = "Passer à l'étape suivante";
        $titlePrev    = "Revenir à l'étape précédente";
        $titleCancel  = "Abandonner définitivement la saisie";
        $titleSubmit  = "Terminer la saisie";
        $titleConfirm = "Confirmer et enregistrer la saisie";
        
        if (($translator = $this->getTranslator()) !== null) {
            $labelNext    = $translator->translate($labelNext, $this->getTranslatorTextDomain());
            $labelPrev    = $translator->translate($labelPrev, $this->getTranslatorTextDomain());
            $labelCancel  = $translator->translate($labelCancel, $this->getTranslatorTextDomain());
            $labelSubmit  = $translator->translate($labelSubmit, $this->getTranslatorTextDomain());
            $labelConfirm = $translator->translate($labelConfirm, $this->getTranslatorTextDomain());
            
            $titleNext    = $translator->translate($titleNext, $this->getTranslatorTextDomain());
            $titlePrev    = $translator->translate($titlePrev, $this->getTranslatorTextDomain());
            $titleCancel  = $translator->translate($titleCancel, $this->getTranslatorTextDomain());
            $titleSubmit  = $translator->translate($titleSubmit, $this->getTranslatorTextDomain());
            $titleConfirm = $translator->translate($titleConfirm, $this->getTranslatorTextDomain());
        }

        if (!$next && $element->getActivateNext()) {
            $next = new Submit(
                    $name . '[' . MultipageFormNavElement::NEXT . ']', 
                    array('label' => $labelNext)
            );
            $next->setAttributes(array(
                'title' => $titleNext, 
                'class' => 'multipage-nav next'));
            $next = $view->formButton($next);
        }
        
        if (!$prev && $element->getActivatePrevious()) {
            $prev = new Submit(
                    $name . '[' . MultipageFormNavElement::PREVIOUS . ']', 
                    array('label' => $labelPrev)
            );
            $prev->setAttributes(array(
                'title' => $titlePrev, 
                'class' => 'multipage-nav previous', 'style'=>'float: left;'));
            $prev = $view->formButton($prev);
        }
        
        if (!$cancel && $element->getActivateCancel()) {
            $cancel = new Submit(
                    $name . '[' . MultipageFormNavElement::CANCEL . ']',
                    array('label' => $labelCancel) 
            );
            $cancel->setAttributes(array(
                'title'   => $titleCancel, 
                'class'   => 'multipage-nav cancel',
                'onclick' => 'askConfirmation(this)'));
            $cancel = $view->formButton($cancel);
        }
        
        if (!$submit && $element->getActivateSubmit()) {
            $submit = new Submit(
                    $name . '[' . MultipageFormNavElement::SUBMIT . ']',
                    array('label' => $labelSubmit)
            );
            $submit->setAttributes(array(
                'title' => $titleSubmit, 
                'class' => 'multipage-nav submit'));
            $submit = $view->formButton($submit);
        }

        if (!$submit && !$confirm && $element->getActivateConfirm()) {
            $next = new Submit(
                    $name . '[' . MultipageFormNavElement::CONFIRM . ']', 
                    array('label' => $labelConfirm)
            );
            $next->setAttributes(array(
                'title' => $titleConfirm, 
                'class' => 'multipage-nav confirm'));
            $next = $view->formButton($next);
        }
        
        $parts = array();
        $parts[0] = $next; // en 1er pour pouvoir valider avec la touche "Entrée"
        $parts[1] = $prev;
        $parts[2] = $submit;
        $parts[3] = '<div class="clearer"></div>';
        $parts[4] = $cancel;
        
        // si les boutons "Terminer" et "Précédent" sont présents tous les 2, on les intervertit
        // pour pouvoir valider (terminer) avec la touche "Entrée"
        if ($prev && $submit) {
            $parts[1] = $submit;
            $parts[2] = $prev;
        }

        $html = implode(' ' . PHP_EOL, array_filter($parts));

        return $html;
    }
}
