<?php

namespace UnicaenApp\Form\Element;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * FIeldset de formulaire permettant de saisir un RIB.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class RIBFieldset extends Fieldset implements InputFilterProviderInterface
{
    const BIC_NAME   = 'bic';
    const IBAN_NAME  = 'iban';

    /**
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        
        $this->add(array(
            'name' => self::BIC_NAME,
            'options' => array(
                'label' => 'BIC',
            ),
            'attributes' => array(
                'size'      => 11,
                'maxlength' => 11,
            ),
            'type' => 'Text'
        ));
        
        $this->add(array(
            'name' => self::IBAN_NAME,
            'options' => array(
                'label' => 'IBAN',
            ),
            'attributes' => array(
                'size'      => 34,
                'maxlength' => 34,
            ),
            'type' => 'Text'
        ));
        
        $this->add(array(
            'name' => 'hidden',
            'attributes' => array(
                'disabled' => true,
            ),
            'type' => 'Hidden',
        ));
    }
    
    /**
     * 
     * @param array $options
     * @return \UnicaenApp\Validator\RIB
     */
    static public function getDefaultValidator($options = null)
    {
        return new \UnicaenApp\Validator\RIB($options);
    }
    
    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $ibanValidator = new \Zend\Validator\Iban(array(
            'messages' => array(
                \Zend\Validator\Iban::FALSEFORMAT  => $message = "L'IBAN saisi n'est pas valide",
                \Zend\Validator\Iban::CHECKFAILED  => $message,
                \Zend\Validator\Iban::NOTSUPPORTED => $message,
            ),
        ));
        
        return array(
            self::BIC_NAME => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StringToUpper'),
                ),
                'validators' => array(
                    new \Zend\Validator\Regex(array(
                        'pattern'  => "/[0-9a-zA-Z]{8,11}/",
                        'messages' => array(\Zend\Validator\Regex::NOT_MATCH => "Le BIC doit contenir 8 à 11 caractères"),
                    )),
                ),
            ),
            self::IBAN_NAME => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StringToUpper'),
                ),
                'validators' => array(
                    $ibanValidator,
                ),
            ),
        );
    }
}