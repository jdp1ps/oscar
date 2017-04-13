<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 15:04
 * @copyright Certic (c) 2016
 */

namespace Oscar\Form;


use Oscar\Entity\OscarFacet;
use Oscar\Hydrator\DateTypeFormHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DateTypeForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('datetype');

        $this->setHydrator(new DateTypeFormHydrator());

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        // LABEL
        $label = 'Intitulé';
        $this->add([
            'name'   => 'label',
            'options' => [
                'label' => $label
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => $label,
            ],
            'type'=>'Text'
        ]);

        // LABEL
        $label = 'Nature';
        $this->add([
            'name'   => 'facet',
            'options' => [
                'label' => $label,
                'value_options' => OscarFacet::getFacets()
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => $label,
            ],
            'type'=>'Select'
        ]);

        // DESCRIPTION
        $label = 'Description';
        $this->add([
            'name'   => 'description',
            'options' => [
                'label' => $label
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => $label,
            ],
            'type'=>'Textarea'
        ]);
        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }



    public function getInputFilterSpecification()
    {
        return [
            'label' => [ 'required' => true ],
            'description' => [ 'required' => false ]
        ];
    }
}