<?php

namespace Oscar\Form;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 14:46
 * @copyright Certic (c) 2015
 */
class GrantSourceForm extends \Zend\Form\Form
{

    function __construct()
    {
        parent::__construct('grantsource');

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        $this->add(array(
            'name'  => 'label',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Intitulé du financement'
            ],
            'options'   => array(
                'label' => 'Intitulé',
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'description',
            'type'  => 'Textarea',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Intitulé du financement'
            ],
            'options'   => array(
                'label' => 'Description simple',
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'informations',
            'type'  => 'Textarea',
            'attributes'    => [
                'class'       => 'form-control ckeditor-simple',
                'placeholder'   => 'Informations détaillées'
            ],
            'options'   => array(
                'label' => 'Détails',
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 's€cure',
            'type'  => 'Csrf',
        ));
    }
}