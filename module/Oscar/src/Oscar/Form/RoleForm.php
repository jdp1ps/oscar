<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/11/15 11:34
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;


use Zend\Form\Form;

class RoleForm extends Form
{
    function __construct( $roles, $enroledData )
    {
        parent::__construct('role');

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        // Enroled
        $this->add([
            'name'   => 'enroled',
            'options' => [
                'label' => $enroledData['label']
            ],
            'attributes'    => [
                'class'       => 'form-control select2',
                'placeholder'   => $enroledData['label'],
                'data-url' => $enroledData['url']
            ],
            'type'=>'Select'
        ]);


        // Role
        $this->add([
            'name'   => 'role',
            'options' => [
                'label' => 'Rôle',
                'value_options' => $roles
            ],
            'type'=>'Select'
        ]);

        $this->add(array(
            'name'  => 'dateStart',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control datepicker',
                'placeholder'   => 'Date de début'
            ],
            'options'   => array(
                'label' => 'Date de début',
                'label_attributes'  => [
                    'class' => 'form-label'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'dateEnd',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control datepicker',
                'placeholder'   => 'Date de fin'
            ],
            'options'   => array(
                'label' => 'Date de fin',
                'label_attributes'  => [
                    'class' => 'form-label'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }
}