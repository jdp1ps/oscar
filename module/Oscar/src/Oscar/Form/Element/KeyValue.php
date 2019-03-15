<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-10-13 14:00
 * @copyright Certic (c) 2016
 */

namespace Oscar\Form\Element;


use Zend\Form\Element;

class KeyValue extends Element
{
    public $keys;
    public $editable;

    /**
     * KeyValue constructor.
     * @param $keys
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->keys = $options['keys'];
        $this->editable = $options['editable'];
    }

}