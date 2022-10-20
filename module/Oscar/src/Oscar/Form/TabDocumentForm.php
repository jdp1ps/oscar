<?php
/**
 * @author Hervé Marie<herve.marie@unicaen.fr>
 * @date: 17/10/22 14:52
 * @copyright Certic (c) 2022
 */

namespace Oscar\Form;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Role;
use Oscar\Hydrator\TabDocumentFormHydrator;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class TabDocumentForm extends Form implements InputFilterProviderInterface
{

    /**
     * @param array $roles
     * @param EntityManager $em
     * @param array $idRolesChecked
     */
    public function __construct(array $roles, EntityManager $em, array $idRolesChecked = [])
    {
        parent::__construct('tabdocument');

        $this->setHydrator(new TabDocumentFormHydrator($em));

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

        // Roles
       /*
        * $label = 'Roles avec droits';
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
       */

        // / Roles avec droits
        //foreach ($roles as $role){
            $this->add(
                [
                    'type'=>MultiCheckbox::class,
                    'name'   => 'roles',
                    'attributes'    => [
                        'class'       => 'form-control',
                    ],
                    'options' => [
                        'label_attributes' => []
                    ]
                ]
            );
        //}

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
        $this->get('roles')->setValueOptions($this->checkedRoles($roles, $idRolesChecked));
    }


    /**
     * Filter obligatoire (champs obligatoires)
     *
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'label' => [ 'required' => true ],
            'description' => [ 'required' => false ]
        ];
    }


    /**
     * Compare-les id roles déjà attribués (edit) et retour la config pour le champ MultiCheckbox au niveau valuesoptions
     *
     * @param $roles
     * @param $idsRolesChecked
     * @return array
     */
    private function checkedRoles($roles, $idsRolesChecked):array{
        $checkboxRoles = [];
        /**@var Role $entityRole */
        foreach ($roles as $key => $entityRole) {
            if (in_array($entityRole->getId(), $idsRolesChecked)){
                $checkboxRoles [] = ['value'=>$entityRole->getId(), 'label' => $entityRole->getRoleId(), 'selected' => true];
            }else{
                $checkboxRoles [] = ['value'=>$entityRole->getId(), 'label' => $entityRole->getRoleId(), 'selected' => false];
            }
        }
        return $checkboxRoles;
    }
}
