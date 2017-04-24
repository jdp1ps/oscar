<?php

namespace Oscar\Form;

use Oscar\Entity\Organization;
use Zend\Form\Element;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */
class OrganizationIdentificationForm extends \Zend\Form\Form
{
    private $connectors = [];

    function __construct($connectors = [])
    {
        parent::__construct('project');
        $this->connectors = $connectors;

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

        $shortName = new Element\Text('shortName');
        $shortName->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Acronyme'
        ]);
        $this->add($shortName);

        $fullName = new Element\Text('fullName');
        $fullName->setAttributes([
            'class'       => 'form-control',
            'placeholder'   => 'Acronyme'
        ]);
        $this->add($fullName);

        // Source
        $this->add([
            'name'   => 'type',
            'options' => [
                'label' => "Type",
                'value_options' => Organization::getTypesSelect()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // DateStart
        $this->add([
            'name'   => 'dateEnd',
            'options' => [
                'label' => 'Date de fermeture'
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
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
                'label' => 'Date de début'
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
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
            'placeholder'   => 'Description du projet',
            'row'           => 5
        ]);
        $this->add($description);

        $this->add(array(
            'name'  => 's€cure',
            'type'  => 'Csrf',
        ));
    }
}