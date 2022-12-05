<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 15:04
 * @copyright Certic (c) 2016
 */

namespace Oscar\Form;

use Doctrine\ORM\EntityManager;
use Oscar\Entity\OscarFacet;
use Oscar\Hydrator\DateTypeFormHydrator;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DateTypeForm extends Form implements InputFilterProviderInterface
{
    public function __construct(array $roles, EntityManager $em, array $idsRolesCheck = [])
    {
        parent::__construct('datetype');

        $this->setHydrator(new DateTypeFormHydrator($em));

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
                'class'       => 'form-control input-lg',
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

        $label = 'Progression';
        $this->add([
            'name'   => 'finishable',
            'options' => [
                'label' => $label,
                'use_hidden_element' => false,
                'checked_value' => 1,
                'unchecked_value' => 0
            ],
            'attributes'    => [
                'class'     => 'form-control',
                'checked'     => 1
            ],
            'type'=>Checkbox::class
        ]);


        // Fréquence des notifications
        $label = 'Fréquence des notifications';
        $this->add([
            'name'   => 'recursivity',
            'options' => [
                'label' => $label
            ],
            'help'=> [
                'before' => "Indiquer les jours séparés par des virgules, par exemple : 30,15,1,0"
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

        // ROLES
        $label = 'Roles';
        $this->add(
            [
                'type'=>MultiCheckbox::class,
                'name'   => 'roles',
                'attributes'    => [
                    'class'       => 'form-control',
                    'multiple' => 'multiple',
                ],
                'options' => [
                    'label_attributes' => []
                ]
            ]
        );

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));

        $this->get('roles')->setValueOptions($this->checkedRoles($roles, $idsRolesCheck));

    }


    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'label' => [ 'required' => true ],
            'description' => [ 'required' => false ],
            'recursivity' => [ 'required' => false ],
            'finishable' => [ 'required' => false ],
            'roles' => ['required' => false],
        ];
    }

    /**
     * Compare-les id roles déjà attribués (edit) et retour la config pour le champ MultiCheckbox au niveau valuesoptions
     * @param $roles
     * @param $idsRolesCheck
     * @return array
     */
    private function checkedRoles($roles, $idsRolesCheck):array{
        $checkboxRoles = [];
        foreach ($roles as $key => $entityRole) {
            if (in_array($entityRole->getId(), $idsRolesCheck)){
                $checkboxRoles [] = ['value'=>$entityRole->getId(), 'label' => $key, 'selected' => true];
            }else{
                $checkboxRoles [] = ['value'=>$entityRole->getId(), 'label' => $key, 'selected' => false];
            }
        }
        return $checkboxRoles;
    }

}
