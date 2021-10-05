<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/11/15 11:34
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;


use Oscar\Hydrator\RoleFormHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class RoleForm extends Form implements InputFilterProviderInterface
{
    function __construct( $roles, $personService, $enroller, $enroledData )
    {
        parent::__construct('role');
        $this->setHydrator(new RoleFormHydrator($personService, $enroller));

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        // Enroled
        $this->add([
            'name'   => 'enrolled',
            'options' => [
                'label' => $enroledData['label']
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => $enroledData['label'],
                'data-url' => $enroledData['url']
            ],
            'type'=>'Hidden'
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
    public function getInputFilterSpecification()
    {
        return [
            'dateStart'=> [
                'required' => false,
            ],

            'dateEnd'=> [
                'required' => false,
            ],

            'enrolled'=> [
                'required' => false,
            ]
        ];
    }

}