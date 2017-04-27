<?php
namespace UnicaenApp\Form\View\Helper;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\Date;

/**
 * Aide de vue générant le code HTML de l'élément de formulaire "Date".
 *
 * Plugin jQuery requis :
 *   - Datetimepicker (http://trentrichardson.com/examples/timepicker)
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 * @see \UnicaenApp\Form\Element\Date
 */
class FormDate extends \Zend\Form\View\Helper\AbstractHelper
{
    /**
     * @var Date
     */
    protected $element;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $date;
    
    /**
     * @var bool
     */
    protected $includeTime;
    
    /**
     * @var bool
     */
    protected $dateReadonly = false;

    /**
     * @var string
     */
    protected $domId;

    /**
     * @var bool
     */
    protected $renderJs = true;
    
    /**
     * @var string
     */
    protected $domIdLinkVider;
    
    /**
     * Invoke helper as functor
     *
     * @param  Date|null $element
     * @param bool $dateReadonly
     * @param bool $dateSupReadonly
     * @return string|\UnicaenApp\Form\View\Helper\FormDate
     */
    public function __invoke(Date $element = null, $dateReadonly = false)
    {
        if (!$element) {
            return $this;
        }
        
        $this->domId = $element->getAttribute('id') ?: uniqid('date_');
        $this->setDateReadonly($dateReadonly);
        
        return $this->render($element);
    }

    /**
     * Génère le code HTML pour l'élément spécifié.
     *
     * @param  Date $element
     * @return string
     */
    public function render(Date $element)
    {
        $this->element = $element;
        
        $this->domIdLinkVider = uniqid($this->domId . '-clear-sup-');
        
        $this->name        = $this->element->getName();
        $this->date        = $this->element->getDateToString();
        $this->includeTime = $this->element->getIncludeTime();
        
        $labelAttributes = array();
        foreach ((array)$this->element->getLabelAttributes() as $attr => $value) {
            $labelAttributes[$attr] = $value;
        }
        
        $templateDate = <<<EOS
<div class="input-date form-group %s">
    %s 
    <div class="input-group">
        %s
        %s
    </div>
    %s
</div>
EOS;
        
        $title           = $this->element->getAttribute('title');
        $class           = $this->element->getAttribute('class');
        $inputErrorClass = $this->element->getMessages() ? 'input-error' : null;

        $dateClasses = array_merge(
                array('input-date'), 
                (array)$class,
                (array)$inputErrorClass);
        $htmlOptions = array(
            'value'     => $this->date,
            'title'     => $title,
            'maxlength' => $len = strlen($this->element->getDatetimeFormatHuman()),
            'size'      => $len,
            'id'        => $this->domId,
            'class'     => implode(' ', array_filter($dateClasses)),
        );
        if ($this->getDateReadonly()) {
            $htmlOptions['readonly'] = 'readonly';
        }
        
        $this->element->setAttributes($htmlOptions);
        
        $formLabel         = $this->getView()->formLabel($this->element); // array('class'=>'required')
        $formText          = $this->getView()->formElement($this->element);
        $formElementErrors = $this->getView()->formElementErrors($this->element, array('class' => 'error text-danger'));
        
        $linkVider = $this->element->getAttribute('disabled') ?
                null : 
                sprintf('<span class="input-group-addon"><a id="%s" href="#" title="%s">%s</a></span>', $this->domIdLinkVider, "Vider", "Vider");
        
        $errorClass = $inputErrorClass ? 'has-error' : null;
        
        $markup = sprintf($templateDate, $errorClass, $formLabel, $formText, $linkVider, $formElementErrors);

        $js = $this->getJavascript();
        if ($this->getRenderJs()) {
//	    $basePath = $this->getView()->basePath();
//	    $markup .= sprintf('<script type="text/javascript" src="%s/js/jquery-ui-timepicker-addon.js"></script>', $basePath);
//	    $markup .= sprintf('<script type="text/javascript" src="%s/js/jquery-ui-timepicker-fr.js"></script>', $basePath);
            $markup .= '<script>' . $js . '</script>' . PHP_EOL; 
        }
        else {
            $this->getView()->plugin('inlineScript')->appendScript($js);
        }
        
        return $markup;
    }

    /**
     *
     * @return string
     */
    public function getJavascript()
    {
        if (!$this->element) {
            throw new LogicException("Aucun élément spécifié, appelez render() auparavant.");
        }
        
        $dateFormat  = Date::DATE_FORMAT_JAVASCRIPT;
        $timeFormat  = Date::TIME_FORMAT_JAVASCRIPT;
        $datetimeSep = $this->element->getDateTimeSeparator();
        
        $datetimeFormatPhp = $this->element->getDatetimeFormat();
        $dateMin = ($tmp = $this->element->getDateMin()) ? $tmp->format($datetimeFormatPhp) : null;
        $dateMax = ($tmp = $this->element->getDateMax()) ? $tmp->format($datetimeFormatPhp) : null;
        
        $dateMin = $dateMin ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateMin . "')" :
                'null';
        $dateMax = $dateMax ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateMax . "')" :
                'null';

        $jsPrefix = str_replace('-', '_', $this->domId);

        $widget = $this->includeTime ? 'datetimepicker' : 'datepicker';
        
        $js = <<<EOT
$(function() {
    var date_{$jsPrefix} = $("#{$this->domId}");

    // installe 1 calendrier
    date_{$jsPrefix}.$widget({
        dateFormat: '{$dateFormat}',
        timeFormat: '{$timeFormat}', // pris en compte uniquement par datetimepicker
        stepMinute: 5,               // idem
        separator: "$datetimeSep",   // idem
        changeMonth: true,
        changeYear: true/*,
        yearRange: '-1:+1'*/
    });
    if (date_{$jsPrefix}.attr('readonly') || date_{$jsPrefix}.attr('disabled')) {
        date_{$jsPrefix}.datepicker("disable");
    }

    date_{$jsPrefix}.datepicker("option", "minDate", {$dateMin});
    date_{$jsPrefix}.datepicker("option", "maxDate", {$dateMax});

    // un clic sur le lien poubelle vide la date
    if (date_{$jsPrefix}.length) {
        $("#{$this->domIdLinkVider}").click(function() {
            if (!date_{$jsPrefix}.attr('disabled')) {
                date_{$jsPrefix}.val('');
            }
            return false;
        });
    }
});
EOT;
        return $js;
    }
    
    public function getDateReadonly()
    {
        return $this->dateReadonly;
    }

    public function setDateReadonly($dateReadonly)
    {
        $this->dateReadonly = $dateReadonly;
        return $this;
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