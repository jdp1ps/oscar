<?php
namespace UnicaenApp\Form\View\Helper;

use Zend\Form\Element\Text;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\DateInfSup;

/**
 * Aide de vue générant le code HTML de l'élément de formulaire composite "DateInfSup", c'est à dire :
 *   - le champ de saisie de la date (et heure éventuelle) inférieure ainsi que son label associé ; 
 *   - éventuellement, le champ de saisie éventuel de la date (et heure éventuelle) supérieure 
 *     ainsi que son label associé (uniquement si la date supérieure est activée) ; 
 *   - éventuellement, un lien permettant de vider la date supérieure (via jQuery).
 *
 * Plugin jQuery requis :
 *   - Datetimepicker (http://trentrichardson.com/examples/timepicker)
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 * @see \UnicaenApp\Form\Element\DateInfSup
 */
class FormDateInfSup extends \Zend\Form\View\Helper\AbstractHelper
{
    /**
     * @var DateInfSup
     */
    protected $element;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $dateInf;
    
    /**
     * @var string
     */
    protected $dateSup;
    
    /**
     * @var bool
     */
    protected $includeTime;
    
    /**
     * @var bool
     */
    protected $dateInfReadonly = false;
    
    /**
     * @var bool
     */
    protected $dateSupReadonly = false;
    
    /**
     * @var bool
     */
    protected $dateSupActivated = true;
    
    /**
     * @var bool
     */
    protected $dateSupRequired = true;

    /**
     * @var string
     */
    protected $domId = 'dateinfsup';

    /**
     * @var bool
     */
    protected $renderJs = true;
    
    /**
     * @var string
     */
    protected $domIdInf;
    
    /**
     * @var string
     */
    protected $domIdSup;
    
    /**
     * @var string
     */
    protected $domIdLinkVider;
    
    /**
     * Invoke helper as functor
     *
     * @param  DateInfSup|null $element
     * @param bool $dateInfReadonly
     * @param bool $dateSupReadonly
     * @return string|\UnicaenApp\Form\View\Helper\FormDateInfSup
     */
    public function __invoke(DateInfSup $element = null, $dateInfReadonly = false, $dateSupReadonly = false)
    {
        if (!$element) {
            return $this;
        }
        
        $this->setDateInfReadonly($dateInfReadonly);
        $this->setDateSupReadonly($dateSupReadonly);
        
        return $this->render($element);
    }

    /**
     * Génère le code HTML pour l'élément spécifié.
     *
     * @param  DateInfSup $element
     * @return string
     */
    public function render(DateInfSup $element)
    {
        $this->element = $element;
        
        $this->dateSupActivated = $this->element->getDateSupActivated();
        $this->dateSupRequired  = $this->element->getInputFilter()->getDateSupRequired();
        
        $this->domIdInf       = uniqid($this->domId . '-inf-text-');
        $this->domIdSup       = uniqid($this->domId . '-sup-text-');
        $this->domIdLinkVider = uniqid($this->domId . '-clear-sup-');
        
        $this->name        = $this->element->getName();
        $this->dateInf     = $this->element->getDateInfToString();
        $this->dateSup     = $this->element->getDateSupToString();
        $this->includeTime = $this->element->getIncludeTime();
        
        $labelAttributesDateInf = $labelAttributesDateSup = array();
        foreach ((array)$this->element->getLabelAttributes() as $attr => $value) {
            if (is_array($value)) {
                $labelAttributesDateInf[$attr] = $value[0];
                $labelAttributesDateSup[$attr] = $value[1];
            }
            else {
                $labelAttributesDateInf[$attr] = $value;
                $labelAttributesDateSup[$attr] = $value;
            }
        }
        
        $templateDateInf = <<<EOS

<div class="input-dateinf form-group %s">
    %s 
    <div class="controls">
        %s
    </div>
</div>
EOS;
        $templateDateSup = <<<EOS

<div class="input-datesup form-group %s">
    %s 
    <div class="controls">
        %s
    </div>
</div>
EOS;
        $titles  = $this->element->getAttribute('title');
        $classes = $this->element->getAttribute('class');
        if (!is_array($titles)) {
            $titles = $titles ? array($titles, $titles) : array();
        }
        if (!is_array($classes)) {
            $classes = $classes ? array($classes, $classes) : array();
        }
        
        $inputErrorClass = array_key_exists('inf', $this->element->getMessages()) ? 'input-error' : null;
        $dateInfClasses = array_merge(
                array('input-dateinf', 'required'), 
                (array)array_shift($classes),
                (array)$inputErrorClass);
        $htmlOptions = array(
            'value'     => $this->dateInf,
            'title'     => array_shift($titles),
            'maxlength' => $len = strlen($this->element->getDatetimeFormatHuman()),
            'size'      => $len,
            'id'        => $this->domIdInf,
            'class'     => implode(' ', array_filter($dateInfClasses)),
        );
        if ($this->getDateInfReadonly()) {
            $htmlOptions['readonly'] = 'readonly';
        }
        $inf = new Text("$this->name[inf]");
        $inf->setLabel($this->element->getDateInfLabel() ?: " ");
        $inf->setLabelAttributes($labelAttributesDateInf);
        $inf->setAttributes($htmlOptions);
        $formText = $this->getView()->formElement($inf);
        $formLabel = $this->getView()->formLabel($inf); // array('class'=>'required')

        $errorClass = $inputErrorClass ? 'error' : null;
        
        $markup = sprintf($templateDateInf, $errorClass, $formLabel, $formText);

        if ($this->dateSupActivated) {
            $inputErrorClass = array_key_exists('sup', $this->element->getMessages()) ? 'input-error' : null;
            $dateSupClasses = array_merge(
                    array('input-datesup'), 
                    $this->dateSupRequired ? array('required') : array(), 
                    (array)array_shift($classes),
                    (array)$inputErrorClass);
            $htmlOptions = array(
                'value'     => $this->dateSup,
                'title'     => array_shift($titles),
                'maxlength' => $len = strlen($this->element->getDatetimeFormatHuman()),
                'size'      => $len,
                'id'        => $this->domIdSup,
                'class'     => implode(' ', array_filter($dateSupClasses)),
            );
            if ($this->getDateSupReadonly()) {
                $htmlOptions['readonly'] = 'readonly';
            }
            $sup = new Text("$this->name[sup]");
            $sup->setLabel($this->element->getDateSupLabel() ?: " ");
            $sup->setLabelAttributes($labelAttributesDateSup);
            $sup->setAttributes($htmlOptions);
            $formText = $this->getView()->formElement($sup);
            $formLabel = $this->getView()->formLabel($sup); // $this->options[self::OPTION_DATE_SUP_REQUIRED] ? array('class'=>'required') : null

            $errorClass = $inputErrorClass ? 'error' : null;
            
            $markup .= sprintf($templateDateSup, $errorClass, $formLabel, $formText);
        }

        $linkVider = null;
        if (!$this->dateSupRequired) {
            $linkVider = sprintf(
                    '<a id="%s" href="#" title="%s">%s</a>',
                    $this->domIdLinkVider, "Vider", "Vider");
        }

        $template = '%s %s';
        $markup = sprintf($template, $markup, $linkVider) . PHP_EOL;

        $js = $this->getJavascript();
        if ($this->getRenderJs()) {
	    $basePath = $this->getView()->basePath();
	    $markup .= sprintf('<script type="text/javascript" src="%s/js/jquery-ui-timepicker-addon.js"></script>', $basePath);
	    $markup .= sprintf('<script type="text/javascript" src="%s/js/jquery.ui.datepicker-fr.js"></script>', $basePath);
	    $markup .= sprintf('<script type="text/javascript" src="%s/js/jquery-ui-timepicker-fr.js"></script>', $basePath);
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
        
        $dateFormat  = DateInfSup::DATE_FORMAT_JAVASCRIPT;
        $timeFormat  = DateInfSup::TIME_FORMAT_JAVASCRIPT;
        $datetimeSep = $this->element->getDateTimeSeparator();
        
//        $this->options[self::OPTION_DATE_INF_MIN] = \DateTime::createFromFormat('d/m/Y H:i', '05/12/2012 09:00');
        
        $datetimeFormatPhp = $this->element->getDatetimeFormat();
        $dateInfMin = ($tmp = $this->element->getDateInfMin()) ? $tmp->format($datetimeFormatPhp) : null;
        $dateInfMax = ($tmp = $this->element->getDateInfMax()) ? $tmp->format($datetimeFormatPhp) : null;
        $dateSupMin = ($tmp = $this->element->getDateSupMin()) ? $tmp->format($datetimeFormatPhp) : null;
        $dateSupMax = ($tmp = $this->element->getDateSupMax()) ? $tmp->format($datetimeFormatPhp) : null;
        
        $dateInfMin = $dateInfMin ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateInfMin . "')" :
                'null';
        $dateInfMax = $dateInfMax ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateInfMax . "')" :
                'null';
        $dateSupMin = $dateSupMin ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateSupMin . "')" :
                'null';
        $dateSupMax = $dateSupMax ? 
                "$.datepicker.parseDateTime('{$dateFormat}', '{$timeFormat}', '" . $dateSupMax . "')" :
                'null';

        $jsPrefix = str_replace('-', '_', $this->domId);

        $widget = $this->includeTime ? 'datetimepicker' : 'datepicker';
        
        $js = <<<EOT
$(function() {
    var dateInf_{$jsPrefix} = $("#{$this->domIdInf}"),
        dateSup_{$jsPrefix} = $("#{$this->domIdSup}"),
        dates_{$jsPrefix}   = dateSup_{$jsPrefix}.length ? $("#{$this->domIdInf}, #{$this->domIdSup}") : $("#{$this->domIdInf}");

    // installe 1 calendrier, ou 2 calendriers JQuery qui se contraignent mutuellement
    dates_{$jsPrefix}.$widget({
        dateFormat: '{$dateFormat}',
        timeFormat: '{$timeFormat}', // pris en compte uniquement par datetimepicker
        stepMinute: 5,               // idem
        separator: "$datetimeSep",   // idem
        changeMonth: true,
        changeYear: true,
        yearRange: '-1:+1',
        onClose: function(selectedDateText, datepickerInstance) {
            if (dateSup_{$jsPrefix}.length) {
                onSelectDatepicker_{$jsPrefix}(this, selectedDateText, datepickerInstance);
            }
        }
    });
    if (dateInf_{$jsPrefix}.attr('readonly') || dateInf_{$jsPrefix}.attr('disabled')) {
        dateInf_{$jsPrefix}.datepicker("disable");
    }
    if (dateSup_{$jsPrefix}.length && (dateSup_{$jsPrefix}.attr('readonly') || dateSup_{$jsPrefix}.attr('disabled'))) {
        dateSup_{$jsPrefix}.datepicker("disable");
    }

    function onSelectDatepicker_{$jsPrefix}(input, selectedDateText, datepickerInstance) {
        var otherValue = dates_{$jsPrefix}.not($(input)).val();
        // date courante (heure éventuelle comprise) :
        var date = $(input).datetimepicker('getDate');
        // change le jour min ou max de l'autre calendrier
        var dateOption = $(input).attr('id') == "{$this->domIdInf}" ? "minDate" : "maxDate";
        dates_{$jsPrefix}.not($(input)).datepicker("option", dateOption, date);
        // change l'heure min ou max de l'autre calendrier
        var datetimeOption = $(input).attr('id') == "{$this->domIdInf}" ? "minDateTime" : "maxDateTime";
        dates_{$jsPrefix}.not($(input)).datetimepicker("option", datetimeOption, date);
        if (!otherValue) {
            dates_{$jsPrefix}.not($(input)).val(otherValue); // à faire car datetimepicker("option", datetimeOption, date) remplie le champ!
        }
    }

    dateInf_{$jsPrefix}.datepicker("option", "minDate", {$dateInfMin});
    dateInf_{$jsPrefix}.datepicker("option", "maxDate", {$dateInfMax});
    dateSup_{$jsPrefix}.datepicker("option", "minDate", {$dateSupMin});
    dateSup_{$jsPrefix}.datepicker("option", "maxDate", {$dateSupMax});

    if (dateSup_{$jsPrefix}.length) {
        onSelectDatepicker_{$jsPrefix}(
                dateInf_{$jsPrefix},
                dateInf_{$jsPrefix}.val(),
                dateInf_{$jsPrefix}.data("datepicker"));
        onSelectDatepicker_{$jsPrefix}(
                dateSup_{$jsPrefix},
                dateSup_{$jsPrefix}.val(),
                dateSup_{$jsPrefix}.data("datepicker"));
    }

    // un clic sur le lien poubelle vide la date de fin
    if (dateSup_{$jsPrefix}.length) {
        $("#{$this->domIdLinkVider}").click(function() {
            dateSup_{$jsPrefix}.val('');
            onSelectDatepicker_{$jsPrefix}(
                    dateSup_{$jsPrefix},
                    null,
                    dateSup_{$jsPrefix}.data("datepicker"));
            return false;
        });
    }
});
EOT;
        return $js;
    }
    
    public function getDateInfReadonly()
    {
        return $this->dateInfReadonly;
    }

    public function setDateInfReadonly($dateInfReadonly)
    {
        $this->dateInfReadonly = $dateInfReadonly;
        return $this;
    }

    public function getDateSupReadonly()
    {
        return $this->dateSupReadonly;
    }

    public function setDateSupReadonly($dateSupReadonly)
    {
        $this->dateSupReadonly = $dateSupReadonly;
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