<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-10-13 14:21
 * @copyright Certic (c) 2016
 */

namespace Oscar\View\Helpers;


use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormElement;

class KeyValueHelper extends FormElement
{

    public static function getTemplateLine($name, $key, $value){
        return '
            <div class="keyvalue-line card">
                <strong class="keyvalue-key">'.$key.'</strong> 
                <input type="text" name="'.$name.'['.$key.']" value="'.$value.'" class="keyvalue-value" />
                <button class="btn btn-xs btn-default btn-delete">
                    <i class="icon-trash"></i>
                    <span>Supprimer</span>
                </button>
            </div>
        ';
    }

        public function render(ElementInterface $element)
        {
            $name = $element->getAttribute('name');
            //$class = "keyvalue " . $element->getAttribute('class');
            $value = $element->getValue();
            //var_dump($value);

            if( !is_array($value) )
                $value = [];

            $out = '<div class="keyvalue-widget" data-template="'.htmlentities(self::getTemplateLine($name, '{{key}}', '{{value}}')).'">';
            if( $element->getLabel() )
                $out .= '<label>' . $element->getLabel() . '</label>';

            $out .= '<div class="keyvalue-lines">';
            foreach( $value as $key=>$val ){
                $out .= self::getTemplateLine($name, $key, $val);
            }
            $out .= '</div>
                <nav>
                    <a href="#" class="btn btn-default btn-add">Ajouter</a>
                </nav>
            </div>';
            return $out;
        }
}