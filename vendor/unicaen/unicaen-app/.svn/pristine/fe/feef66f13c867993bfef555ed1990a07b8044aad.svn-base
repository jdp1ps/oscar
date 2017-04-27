<?php
namespace UnicaenApp\Form\View\Helper;

use Zend\Form\Fieldset;
use Zend\Form\View\Helper\AbstractHelper;
use Zend\View\Exception\InvalidArgumentException;

/**
 * Aide de vue générant un fieldset de formulaire multi-pages.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MultipageFormFieldset extends AbstractHelper
{
    /**
     * Point d'entrée.
     * 
     * @param Fieldset $fieldset Eventuel fieldset concerné. Sinon utilisation de $this->getView()->fieldset
     * @return string
     */
    public function __invoke()
    {
        if (!($fieldset = $this->getView()->fieldset)) {
            throw new InvalidArgumentException("Aucun fieldset trouvé dans la vue.");
        }
        if (!$fieldset instanceof Fieldset) {
            throw new InvalidArgumentException("Le fieldset spécifié dans la vue n'est pas valide.");
        }
        if (!count($fieldset->getElements())) {
            throw new InvalidArgumentException("Le fieldset spécifié dans la vue ne possède aucun élément.");
        }
        return $this->render($fieldset);
    }
    
    /**
     * Génère le code HTML.
     * 
     * @param Fieldset $fieldset Fieldset concerné
     * @return string
     */
    public function render(Fieldset $fieldset)
    {
        $template = '<form method="POST"><fieldset><legend>%s</legend>%s</fieldset></form>';
        
        $elements = '';
        foreach ($fieldset as $element) {
            $elements .= $this->getView()->multipageFormRow($element);
        }
        
        $label = $fieldset->getLabel();
        $step  = "Étape";
        $on    = "sur";
        
        if (($translator = $this->getTranslator()) !== null) {
            $label = $translator->translate($label, $this->getTranslatorTextDomain());
            $step  = $translator->translate($step, $this->getTranslatorTextDomain());
            $on    = $translator->translate($on, $this->getTranslatorTextDomain());
        }
                
        $html = sprintf($template, $label, $elements);

        if (isset($this->getView()->stepIndex)) {
            $step .= " " . $this->getView()->stepIndex;
            if (isset($this->getView()->stepCount)) {
                $step .= " $on " . $this->getView()->stepCount;
            }
            $html = "<h2>$step</h2>" . PHP_EOL . $html;
        }
            
        return $html;
    }
}