<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:15
 * @copyright Certic (c) 2015
 */
namespace Oscar\Form;

use Oscar\Hydrator\PersonFormHydrator;
use Laminas\Filter\StringTrim;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\EmailAddress;

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
            'name'   => 'ladapLogin',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Identifiant de connection'
            ],
            'type'=>'Text'
        ]);

        $this->add([
           'name'   => 'ldapfininscription',
           'attributes' => [
               'class' => 'form-control'
           ],
           'options' => [
               'label' => "Date fin d'inscription"
           ],
           'type'=>'Date'
       ]);

        //

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
                'label' => 'Localisation'
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'ldapAffectation',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Affectation'
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
                    'class' => 'form-control'
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
            'ldapfininscription' => [
                'required' => false
            ],
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
