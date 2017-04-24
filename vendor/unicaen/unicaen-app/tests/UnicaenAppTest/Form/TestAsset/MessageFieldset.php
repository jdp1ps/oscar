<?php
namespace UnicaenAppTest\Form\TestAsset;

use Zend\Form\Element\Textarea;
use Zend\Form\Fieldset;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MessageFieldset extends Fieldset // NB: does not implement InputFilterProviderInterface
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
 
        $this->setLabel("Votre message")
             ->add(new Textarea('message', array('label'=>"Message")));
    }
}