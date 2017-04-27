<?php
namespace UnicaenApp\Form\View\Helper;

use Zend\Form\View\Helper\FormRow;
use UnicaenApp\Form\Element\DateInfSup;
use Zend\Form\ElementInterface;

/**
 * Aide de vue générant le code HTML **complet** de l'élément de formulaire composite "DateInfSup", c'est à dire :
 *   - le label global de l'élément composite ;
 *   - les champs de saisie des dates et leurs labels (délégué à l'aide de vue "FormDateInfSup") ;
 *   - les messages d'erreurs de validation.
 * 
 * Hérite de l'aide de vue "FormRow".
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 * @see \UnicaenApp\Form\Element\DateInfSup
 * @see FormDateInfSup
 * @see FormRow
 */
class FormRowDateInfSup extends FormRow
{
    /**
     * @var bool
     */
    protected $renderJs = true;

    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element
     * @param null|string $labelPosition
     * @return string
     */
    public function render(ElementInterface $element, $labelPosition = null)
    {
        if (!$element instanceof DateInfSup) {
            throw new \Zend\Form\Exception\InvalidArgumentException("Cette aide de vue ne prend en charge que les élément de type 'DateInfSup'.");
        }
        
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();
        $elementErrors   = $elementErrorsHelper->render($element);

        // Does this element have errors ?
        if (!empty($elementErrors) && !empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttributes = $classAttributes . $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        $elementHelper->setRenderJs($this->getRenderJs());
        
        $elementString = $elementHelper->render($element);

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }

            $label = $escapeHtmlHelper($label);

            // DateInfSup elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            $markup = sprintf(
                '<fieldset class="dateinfsup"><legend>%s</legend>%s</fieldset>',
                $label,
                $elementString);

            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }

        return $markup;
    }
    
    /**
     * Retrieve the FormDateInfSup helper
     *
     * @return FormDateInfSup
     */
    protected function getElementHelper()
    {
        if (method_exists($this->view, 'plugin')) {
            $this->elementHelper = $this->view->plugin('formDateInfSup');
        }

        return parent::getElementHelper();
    }

    public function getRenderJs()
    {
        return $this->renderJs;
    }

    public function setRenderJs($renderJs = true)
    {
        $this->renderJs = $renderJs;
        return $this;
    }
}