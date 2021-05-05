<?php

namespace Oscar\Form;

use Oscar\Hydrator\OrganizationFormHydrator;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */
class OrganizationIdentificationForm extends \Zend\Form\Form implements InputFilterProviderInterface
{
    use ServiceLocatorAwareTrait;

    private $connectors = [];
    private $types = [];

    function __construct($connectors = [], $types = [])
    {
        parent::__construct('organization');
        $this->connectors = $connectors;
        $this->types = $types;
    }

    public function init(){
        $this->setHydrator(new OrganizationFormHydrator($this->connectors, $this->types));

        $typesSelect = [];
        $typesSelect[] = "";
        foreach ($this->types as $t ){
            $typesSelect[$t->getId()] = (string)$t;
        }

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

        // Type
        $this->add([
            'name'   => 'typeObj',
            'options' => [
                'label' => 'Type d\'organisation',
                'value_options' => $typesSelect
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);


        $shortName = new Element\Text('shortName');
        $shortName->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Acronyme'
        ]);
        $this->add($shortName);

        $labintel = new Element\Text('labintel');
        $labintel->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Code LABINTEL'
        ]);
        $this->add($labintel);

        $rnsr = new Element\Text('rnsr');
        $rnsr->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'N°RNSR'
        ]);
        $this->add($rnsr);

        $fullName = new Element\Text('fullName');
        $fullName->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Nom complet',
        ]);
        $this->add($fullName);

        // Source
        $this->add([
            'name'   => 'type',
            'options' => [
                'label' => "Type"
            ],
            'attributes' => [
                'class' => 'form-control',
                'list' => 'types'
            ],
            'type'=>'Text'
        ]);

        ////////////////////////////////////////////////////////////////////////
        // Connectors (Ajout dynamique des champs pour les valeurs des connectors)
        foreach( $this->connectors as $connector ){
            $this->add([
                'name'   => 'connector_' . $connector,
                'options' => [
                    'label' => 'N° ' . $connector
                ],
                'attributes' => [
                    'class' => 'form-control',
                    'placeholder'   => 'N° ' . $connector
                ],
                'type'=>'Text'
            ]);
        }

        // DateStart
        $this->add([
            'name'   => 'dateStart',
            'options' => [
                'label' => 'Début du contrat',
                'format' => 'Y-m-d'
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type'=>'DateTime'
        ]);
        //$dateStart->setValue('FICK');
        //$this->get('dateStart')->setValue('FUCK');




//
        // DateEnd
        $this->add([
            'name'   => 'dateEnd',
            'options' => [
                'label' => 'Date de fermeture',
                'format' => 'Y-m-d'
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type'=>'DateTime'
        ]);



        $eotp = new Element\Text('code');
        $eotp->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Code interne'
        ]);
        $this->add($eotp);

        $email = new Element\Text('email');
        $email->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Email'
        ]);
        $this->add($email);

        $url = new Element\Text('url');
        $url->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'URL'
        ]);
        $this->add($url);

        $zipCode = new Element\Text('zipCode');
        $zipCode->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Code Postal'
        ]);
        $this->add($zipCode);

        $city = new Element\Text('city');
        $city->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Ville'
        ]);
        $this->add($city);

        $country = new Element\Text('country');
        $country->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Pays'
        ]);
        $this->add($country);

        $phone = new Element\Text('phone');
        $phone->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Téléphone'
        ]);
        $this->add($phone);

        $street1 = new Element\Text('street1');
        $street1->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Adresse 1'
        ]);
        $this->add($street1);

        $street2 = new Element\Text('street2');
        $street2->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Adresse 2'
        ]);
        $this->add($street2);

        $street3 = new Element\Text('street3');
        $street3->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Adresse 3'
        ]);
        $this->add($street3);

        $siret = new Element\Text('siret');
        $siret->setAttributes([
            'class' => 'form-control',
            'placeholder' => 'N° de SIRET'
        ]);
        $this->add($siret);

        $description = new Element\Textarea('description');
        $description->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Description',
            'row'           => 5
        ]);
        $this->add($description);

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


        ];
    }
}