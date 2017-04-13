<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 04/04/16
 * Time: 15:04
 */

namespace Oscar\Form;


use Oscar\Hydrator\WorkPackageHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class WorkPackageForm extends Form /*implements InputFilterProviderInterface*/
{

    function __construct()
    {
        parent::__construct('workpackage');

        $hydrator = new WorkPackageHydrator();
        $this->setHydrator($hydrator);

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name'  => 'code',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Code'
            ],
            'options'   => array(
                'label' => 'Code',
                'label_attributes'  => [
                    'class' => 'form-label'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'label',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Intitulé'
            ],
            'options'   => array(
                'label' => 'Intitulé',
                'label_attributes'  => [
                    'class' => 'form-label'
                ]
            )
        ));


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
            'name'  => 'description',
            'type'  => 'Textarea',
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Description'
            ],
            'options'   => array(
                'label' => 'Description',
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

    /*public function getInputFilterSpecification()
    {

         return [
             'code' => [ 'required'  => false ],
             'label' => [ 'required'  => false ],
             'description' => [ 'required'  => false ],
             'dateStart' => [ 'required'  => false ],
             'dateEnd' => [ 'required'  => false ],
         ];

    }*/
}