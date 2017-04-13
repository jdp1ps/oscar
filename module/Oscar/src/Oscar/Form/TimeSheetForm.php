<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date October 16, 2015 15:34
 * @copyright Certic (c) 2015
 */
namespace Oscar\Form;

use Oscar\Entity\Activity;
use Oscar\Service\ProjectGrantService;
use Oscar\Validator\EOTP;
use UnicaenApp\Util;
use Zend\Filter\StringTrim;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Oscar\Hydrator\ProjectGrantFormHydrator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class TimeSheetForm
 * @package Oscar\Form
 * @deprecated
 */
class TimeSheetForm extends Form implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    public function init()
    {
        // @todo Créer et implémenter la classe d'hydration des TimeSheets
        //$hydrator = new ProjectGrantFormHydrator();
//        $hydrator->setServiceLocator($this->getServiceLocator());
//        $this->setHydrator($hydrator);

        $this->add([
            'type' => 'Hidden',
            'name' => 'id'
        ]);


        /**
        $grantService = $this->getServiceLocator()->get('ProjectGrantService');


        $this->add([
            'type' => 'Hidden',
            'name' => 'id'
        ]);

        // CentaureId
        $this->add([
            'name'   => 'centaureId',
            'options' => [
                'label' => 'Identifiant dans centaure'
            ],
            'type'=>'Text'
        ]);

        // CentaureId
        $label = "Intitulé de l'activité";
        $this->add([
            'name'   => 'label',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-lg',
                'placeholder' => $label,
            ],
            'type'=>'Text'
        ]);

        // CentaureId
        $label = "Description";
        $this->add([
            'name'   => 'description',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-xs',
                'placeholder' => $label,
            ],
            'type'=>'Textarea'
        ]);

        // CentaureNumConvention
        $this->add([
            'name'   => 'centaureNumConvention',
            'options' => [
                'label' => 'N° de convention',
                //'value_options' =>
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Text'
        ]);

        // Source
        $this->add([
            'name'   => 'source',
            'options' => [
                'label' => 'Source du financement',
                'value_options' => $this->getServiceLocator()->get('ProjectGrantService')->getSources()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // Source
        $this->add([
            'name'   => 'disciplines',
            'options' => [
                'label' => 'Discipline(s)',
                'value_options' => $this->getServiceLocator()->get('ProjectGrantService')->getDisciplines()
            ],
            'attributes' => [
                'class' => 'form-control select2',
                'multiple' => 'multiple'
            ],
            'type'=>'Select'
        ]);

        // Status
        $this->add([
            'name'   => 'status',
            'options' => [
                'label' => 'Statut',
                'value_options' => $this->getServiceLocator()->get('ProjectGrantService')->getStatus()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // Source
        $this->add([
            'name'   => 'activityType',
            'options' => [
                'label' => "Type d'activité",
                'value_options' => $this->getServiceLocator()->get('ActivityTypeService')->getActivityTypes(true)
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // Tune
        $this->add([
            'name'   => 'currency',
            'options' => [
                'label' => "Devise",
                'value_options' => Util::collectionAsOptions($grantService->getCurrencies())
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // Tune
        $this->add([
            'name'   => 'tva',
            'options' => [
                'label' => "TVA",
                'value_options' => Util::collectionAsOptions($grantService->getTVAs())
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // CodeEOTP
        $this->add([
            'name'   => 'codeEOTP',
            'options' => [
                'label' => 'N°PFI'
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Text'
        ]);

        // Amount
        $this->add([
            'name'   => 'amount',
            'options' => [
                'label' => 'Montant'
            ],
            'attributes' => [
                'class' => 'form-control input-lg'
            ],
            'type'=>'Text'
        ]);

        // DateStart
        $this->add([
            'name'   => 'dateStart',
            'options' => [
                'label' => 'Début du contrat'
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
        ]);

        // DateEnd
        $this->add([
            'name'   => 'dateEnd',
            'options' => [
                'label' => 'Fin du contrat'
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
        ]);

        // DateSigned
        $this->add([
            'name'   => 'dateSigned',
            'options' => [
                'label' => 'Date de signature'
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
        ]);

        // DateOpened
        $this->add([
            'name'   => 'dateOpened',
            'options' => [
                'label' => "Création du PFI dans SIFAC"
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Date'
        ]);

        // Type
        $this->add([
            'name'   => 'type',
            'options' => [
                'label' => 'Type de convention',
                'value_options' => $this->getServiceLocator()->get('ProjectGrantService')->getTypes()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);
        /****/
    }

    public function getInputFilterSpecification()
    {
        return [
            /*
            'centaureId'=> [
                'required' => false,
            ],

            'centaureNumConvention'=> [
                'required' => false,
            ],

            'source'=> [
                'required' => false,
            ],

            'codeEOTP'=> [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    new EOTP(),
                ]
            ],

            'amount'=> [
                'required' => false,
            ],

            'dateStart'=> [
                'required' => false,
            ],

            'dateEnd'=> [
                'required' => false,
            ],

            'status'=> [
                'required' => false,
            ],

            'dateSigned'=> [
                'required' => false,
            ],

            'dateOpened'=> [
                'required' => false,
            ],

            'type'=> [
                'required' => true,
            ],

            'disciplines' => [
                'required' => false,
            ],*/
        ];
    }
}
