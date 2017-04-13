<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:15
 * @copyright Certic (c) 2015
 */
namespace Oscar\Form;

use Oscar\Hydrator\PersonFormHydrator;
use Zend\Filter\StringTrim;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\EmailAddress;

class PersonForm extends Form implements InputFilterProviderInterface
{
    private $connectorsName = [];

    public function setConnectors( array $connectorsName ){
        $this->connectorsName = $connectorsName;
    }

    public function init()
    {
        $this->setHydrator(new PersonFormHydrator($this->connectorsName));

        // Les champs
        $this->add([
            'name'   => 'id',
            'type'=>'Hidden'
        ]);
        $this->add([
           'name'   => 'firstname',
            'attributes' => [
              'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Prénom'
            ],
            'type'=>'Text'
        ]);

        $this->add([
           'name'   => 'lastname',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Nom'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'codeHarpege',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Code dans Harpège'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'codeLdap',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'ID LDAP'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'email',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Courriel'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'phone',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Téléphone'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'ldapSiteLocation',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Loaclisation (LDAP)'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'ldapAffectation',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Affectation (LDAP)'
            ],
            'type'=>'Text'
        ]);

        foreach( $this->connectorsName as $connector ){
            $this->add([
                'name'   => 'connector_' . $connector,
                'options' => [
                    'label' => $connector
                ],
                'attributes' => [
                    'class' => 'form-input'
                ],
                'type'=>'Text'
            ]);
        }


        $this->add([
            'name'   => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            ],
            'type'=>'Submit'
        ]);


    }

    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'required'  => true,
                'filters' => [
                    ['name' => StringTrim::class]
                ],
                'validators' => [
                    new EmailAddress()
                ]
            ]
        ];
    }
}
