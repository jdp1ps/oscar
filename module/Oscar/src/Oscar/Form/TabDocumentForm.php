<?php
/**
 * @author Hervé Marie<herve.marie@unicaen.fr>
 * @date: 17/10/22 14:52
 * @copyright Certic (c) 2022
 */

namespace Oscar\Form;

use Doctrine\ORM\EntityManager;
use Oscar\Hydrator\TabDocumentFormHydrator;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;

use Laminas\InputFilter\InputFilterProviderInterface;

class TabDocumentForm extends Form implements InputFilterProviderInterface
{
    const AUCUN  = 0;
    const READ  = 1;
    const WRITE  = 2;

    /**
     * @param array $roles
     * @param EntityManager $em
     */
    public function __construct(array $roles, EntityManager $em)
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

        // DEFAULT
        $label = 'Onglet par défaut';
        $this->add([
                       'name'   => 'default',
                       'options' => [
                           'label' => $label,
                           'checked_value' => 'on'
                       ],
                       'attributes'    => [
                           'class'       => 'form-control',
                           'placeholder'   => $label,
                       ],
                       'type'=>'Checkbox'
                   ]);

        // Gestion des rôles associés
        foreach($roles as $role)
        {
            $this->add(
                [
                    'type' => Select::class,
                    'name' => 'roleId_' . $role->getId(),
                    'options' => [
                        'value_options' => [
                            self::AUCUN => 'Aucun',
                            self::READ => 'Lecture uniquement',
                            self::WRITE => 'Lecture et écriture',
                        ],
                    ]
                ]);
        }

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
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
}
