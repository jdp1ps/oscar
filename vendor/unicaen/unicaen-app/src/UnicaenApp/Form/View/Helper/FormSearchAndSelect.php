<?php
namespace UnicaenApp\Form\View\Helper;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\SearchAndSelect;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Exception\InvalidElementException;
use Zend\Form\View\Helper\FormText;

/**
 * Aide de vue générant le code HTML de l'élément de formulaire du même nom.
 *
 * @author <bertrand.gauthier@unicaen.fr>
 * @see \UnicaenApp\Form\Element\SearchAndSelect
 */
class FormSearchAndSelect extends FormText
{
    /**
     * @var SearchAndSelect
     */
    protected $element;
    
    /**
     * @var string
     */
    protected $autocompleteSource;
    
    /**
     * @var int
     */
    protected $autocompleteMinLength = 2;
    
    /**
     * @var string
     */
    protected $spinnerSource = "//gest.unicaen.fr/images/ajax-loader-r.gif";
    
    /**
     * Invoke helper as functor
     *
     * @param  SearchAndSelect|null $element
     * @return string|FormSearchAndSelect
     */
    public function __invoke(ElementInterface $element = null)
    {
        if ($element && !$element instanceof SearchAndSelect) {
            throw new InvalidElementException("L'élément spécifié n'est pas du type attendu.");
        }
        
        $this->element = $element;
        return parent::__invoke($element);
    }

    /**
     * Render a form <input> element from the provided $element
     *
     * @param  Text $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof SearchAndSelect) {
            throw new InvalidElementException("L'élément spécifié n'est pas du type attendu.");
        }
        
        $this->element = $element;
        $name          = $this->element->getName();
        $id            = SearchAndSelect::ID_ELEMENT_NAME;
        $label         = SearchAndSelect::LABEL_ELEMENT_NAME;
        
        if (!$this->element->getAttribute('id')) {
            $this->element->setAttribute('id', uniqid('sas-'));
        }
        
        $this->element->setAttribute('class', 'sas');
        
        // L'élément est multivalué : 
        //   'id'    => identifiant unique (ex: login de la personne), 
        //   'label' => libellé affiché (ex: nom complet de la personne)
        $this->element->setName($name . "[$id]");
        
        // render parent
        $markup = parent::render($this->element);
        
        $elementDomId      = $this->element->getAttribute('id');
        $autocompleteDomId = $elementDomId . '-autocomplete';

        $autocomplete = new Text();
        $autocomplete->setAttributes($this->element->getAttributes())
                     ->setName($name . "[$label]")
                     ->setAttribute('id', $autocompleteDomId)
                     ->setAttribute('class', 'form-control input-sm')
                     ->setValue($this->element->getValueLabel() ?: $this->element->getValue());
        
        $markup .= $this->getView()->formText($autocomplete);
        
        $markup .= <<<EOS
<style>
    ul.ui-autocomplete {
        z-index: 5000
    }
    .ui-autocomplete-loading {
        background: white url("{$this->getSpinnerSource()}") right center no-repeat;
    }
</style>
EOS;

//        $this->getView()->plugin('inlineScript')->appendScript($this->getJavascript());
        $markup .= PHP_EOL . '<script>' . $this->getJavascript() . '</script>' . PHP_EOL;
        
        return $markup;
    }

    /**
     * 
     * @return string
     * @throws LogicException
     */
    public function getJavascript()
    {
        if (!$this->element) {
            throw new LogicException("Aucun élément spécifié, appelez render() auparavant.");
        }
        
        $elementDomId      = $this->element->getAttribute('id');
        $autocompleteDomId = $elementDomId . '-autocomplete';
        
        $js = <<<EOT
// cache l'élément contenant l'id de la sélection
$("#{$elementDomId}").css('display', 'none');

$(function() {
    // jQuery UI autocomplete
    var autocomp = $("#{$autocompleteDomId}");
    autocomp.autocompleteUnicaen({ // autocompleteUnicaen() définie dans "public/js/util.js"
        elementDomId: '$elementDomId',
        source: '{$this->getAutocompleteSource()}',
        minLength: {$this->getAutocompleteMinLength()},
        delay: 750
    });
});
EOT;
        return $js;
    }
    
    /**
     * 
     * 
     * @param string|array $autocompleteSource
     * @return SearchAndSelect
     */
    public function setAutocompleteSource($autocompleteSource)
    {
        $this->autocompleteSource = $autocompleteSource;
        return $this;
    }

    /**
     * 
     * 
     * @return string|array $autocompleteSource
     */
    public function getAutocompleteSource()
    {
        if (null !== $this->autocompleteSource) {
            return $this->autocompleteSource;
        }
        return $this->element->getAutocompleteSource();
    }

    /**
     * 
     * @return int
     */
    public function getAutocompleteMinLength()
    {
        return $this->autocompleteMinLength;
    }

    /**
     * 
     * @param int $autocompleteMinLength
     * @return SearchAndSelect
     */
    public function setAutocompleteMinLength($autocompleteMinLength)
    {
        $this->autocompleteMinLength = $autocompleteMinLength;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getSpinnerSource()
    {
        return $this->spinnerSource;
    }

    /**
     * 
     * @param string $spinnerSource
     * @return SearchAndSelect
     */
    public function setSpinnerSource($spinnerSource)
    {
        $this->spinnerSource = $spinnerSource;
        return $this;
    }
}