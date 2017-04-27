<?php

namespace UnicaenApp\Form\View\Helper;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\Date;
use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\Element\SearchAndSelect;
use Zend\Form\Element\Button;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

/**
 * Aide de vue générant un élément de fomulaire à la mode Bootsrap 3.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormControlGroup extends AbstractHelper
{
    protected $includeLabel   = true;
    protected $addClearButton = false;

    /**
     * Appel de l'objet comme une fonction.
     *
     * @param ElementInterface $element
     * @param string|null $pluginClass
     * @return string|FormControlGroup
     */
    public function __invoke(ElementInterface $element = null, $pluginClass='formElement')
    {
        if (null === $element) {
            return $this;
        }

        return $this->render($element, $pluginClass);
    }

    /**
     * Génère le code HTML.
     *
     * @param ElementInterface $element
     * @param string|null $pluginClass
     * @return string
     */
    public function render(ElementInterface $element, $pluginClass = 'formElement')
    {
        if (!$element instanceof Button) {
            $class      = $element->getAttribute('class');
            $labelClass = array_key_exists('class', $tmp = (array) $element->getLabelAttributes()) ? $tmp['class'] : '';
            $element
                ->setAttribute('class', $class . ' form-control')
                ->setLabelAttributes(array('class' => $labelClass . ' control-label'));
        }
        if ($element instanceof Date) {
            $helper = $this->getView()->plugin('formDate');
            return $helper($element);
        }
        if ($element instanceof DateInfSup) {
            $helper = $this->getView()->plugin('formDateInfSup');
            return $helper($element);
        }

        if ($element instanceof SearchAndSelect) {
            $helper = $this->getView()->plugin('formSearchAndSelect');
            $helper->setAutocompleteMinLength(2);
            $input = $helper($element);
        }else {
            if (is_string($pluginClass)) {
                $helper = $this->getView()->plugin($pluginClass);
                $input = $helper($element);
            }elseif ($pluginClass instanceof \Zend\Form\View\Helper\AbstractHelper) {
                $input = $pluginClass($element);
            }else{
                throw new LogicException('Attribut $pluginClass incorrect');
            }

        }

        $label = null;
        if ($this->includeLabel && $element->getLabel() && !$element instanceof Button) {
            $helper = $this->getView()->plugin('formLabel');
            $label = $helper($element);
        }

        if ($element->hasAttribute('info_icon')) {
            $info = $element->getAttribute('info_icon');
            $label .= sprintf(
                '&nbsp;<span data-toggle="tooltip" data-placement="bottom" class="info-icon glyphicon glyphicon-info-sign" title="%s"></span>',
                $info);
        }

        if ($element instanceof MultiCheckbox) {
            $input  = '<div class="multicheckbox">' . $input . '</div>';
        }

        $button = $this->addClearButton ?
            '<span class="input-group-btn"><button class="btn btn-default btn-xs" type="button" title="Vider" onclick="$(this).siblings(\':input\').val(null).focus();"><span class="glyphicon glyphicon-remove"></span></button></span>' :
            null;

        $errors = null;
        if ($element->getMessages()) {
            $helper = $this->getView()->plugin('formElementErrors');
            $errors = $helper($element, array('class' => 'error text-danger'));
        }

        $class = array();
        $class[] = $this->addClearButton ? 'input-group' : null;
        $class[] = $errors ? 'has-error' : null;
        $class   = implode(' ', $class);

        $format = <<<EOT
<div class="form-group $class">
    $label
    $input
    $button
    $errors
</div>
EOT;
        return $format;
    }

    /**
     *
     * @return bool
     */
    public function getIncludeLabel()
    {
        return $this->includeLabel;
    }

    /**
     *
     * @return bool
     */
    public function getAddClearButton()
    {
        return $this->addClearButton;
    }

    /**
     *
     * @param bool $includeLabel
     * @return self
     */
    public function setIncludeLabel($includeLabel)
    {
        $this->includeLabel = $includeLabel;
        return $this;
    }

    /**
     *
     * @param bool $addClearButton
     * @return self
     */
    public function setAddClearButton($addClearButton)
    {
        $this->addClearButton = $addClearButton;
        return $this;
    }
}