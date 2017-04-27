<?php

namespace UnicaenApp\Form\View\Helper;

use Zend\Form\View\Helper\FormMultiCheckbox;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Class FormAdvancedMultiCheckbox
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 *
 */
class FormAdvancedMultiCheckbox extends FormMultiCheckbox
{

    /**
     * hauteur de la liste
     *
     * @var string
     */
    protected $height = 'auto';

    /**
     * @var string
     */
    protected $overflow = 'auto';



    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }



    /**
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }



    /**
     * @return string
     */
    public function getOverflow()
    {
        return $this->overflow;
    }



    /**
     * @param string $overflow
     */
    public function setOverflow($overflow)
    {
        $this->overflow = $overflow;
        return $this;
    }



    /**
     * Render a form <select> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $template = '
        <div class="form-advanced-multi-checkbox input-sm form-control" style="height:auto">
            <div id="items" style="max-height:'.$this->height.';overflow:'.$this->overflow.'">
                %s
            </div>
            <a class="btn btn-default btn-xs select-all" role="button"><span class="glyphicon glyphicon-ok-circle"></span> Tout sélectionner</a>
            <a class="btn btn-default btn-xs select-none" role="button"><span class="glyphicon glyphicon-remove-circle"></span> Ne rien sélectionner</a>
        </div>
        ';
        return sprintf($template, parent::render( $element ));
    }
}
