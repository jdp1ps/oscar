<?php

namespace Oscar\Form;

use Laminas\Form\Form;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */
class ProjectForm extends Form
{

    function __construct()
    {
        parent::__construct('project');

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        $this->add(array(
            'name'  => 'label',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Nom du projet'
            ],
            'options'   => array(
                'label' => 'Nom du projet',
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'acronym',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Acronyme'
            ],
            'options'   => array(
                'label' => 'Acronyme',
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
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }
}