<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/11/15 11:34
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;

use Oscar\Entity\ActivityType;
use Oscar\Hydrator\ActivityTypeFormHydrator;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class ActivityTypeForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('activitytype');

        $this->setHydrator(new ActivityTypeFormHydrator());

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


        // Role
        $this->add([
            'name'   => 'nature',
            'options' => [
                'label' => "Nature de l'activité",
                'value_options' => ActivityType::getNatures()
            ],
            'type'=>'Select'
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
            'nature' => [ 'required' => false ],
            'description' => [ 'required' => false ],
        ];
    }
}
