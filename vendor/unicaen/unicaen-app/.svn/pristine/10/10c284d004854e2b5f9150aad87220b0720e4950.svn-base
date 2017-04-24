<?php
namespace UnicaenAppTest\Form\TestAsset;

use Zend\Form\Element\Text;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AdresseFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
 
        $this->setLabel("Votre adresse")
             ->add(new Text('email', array('label'=>"Email")));
    }
 
    public function getInputFilterSpecification()
    {
        return array(
            'email' => array(
                'required' => true,
                'filters'  => array(
                    array('name' => '\Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'=> 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array('isEmpty' => "Vous devez renseigner votre adresse mail"),
                        )
                    ),
                    array(
                        'name' => '\Zend\Validator\EmailAddress',
                        'options' => array(
                            'messages' => array('emailAddressInvalidFormat' => "L'adresse mail spécifiée est invalide"),
                        ),
                    ),
                ),
            ),
        );
    }
}
