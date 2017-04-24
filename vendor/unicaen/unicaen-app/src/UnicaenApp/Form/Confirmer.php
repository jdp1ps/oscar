<?php

namespace UnicaenApp\Form;

use Zend\Form\Element\Csrf;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;

/**
 * Formulaire permettant de confirmer une action.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Confirmer extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        
        /**
         * Csrf
         */
        $this->add(new Hidden('id'));
        
        /**
         * Csrf
         */
        $this->add(new Csrf('security'));
        
        /**
         * Submit
         */
        $this->add(array(
            'name' => 'confirm',
            'type'  => 'Submit',
            'attributes' => array(
                'value' => 'Oui, je confirme',
            ),
        ));
    }
}