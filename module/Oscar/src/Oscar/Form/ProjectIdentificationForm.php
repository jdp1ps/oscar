<?php

namespace Oscar\Form;

use Oscar\Hydrator\ProjectFormHydrator;
use Laminas\Form\Element;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */
class ProjectIdentificationForm extends \Laminas\Form\Form
{

    function init()
    {
        $this->setHydrator(new ProjectFormHydrator());

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

        $acronym = new Element\Text('acronym');
        $acronym->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Acronyme'
        ]);
        $this->add($acronym);

        $description = new Element\Textarea('description');
        $description->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Description du projet',
            'row'           => 5
        ]);
        $this->add($description);

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }
}