<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date October 16, 2015 15:34
 * @copyright Certic (c) 2015
 */
namespace Oscar\Form;

use Oscar\Entity\Activity;
use Oscar\Entity\TimeSheet;
use Oscar\Form\Element\KeyValue;
use Oscar\Service\ProjectGrantService;
use Oscar\Validator\EOTP;
use UnicaenApp\Util;
use Zend\Filter\StringTrim;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Oscar\Hydrator\ProjectGrantFormHydrator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProjectGrantForm extends Form implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    public function init()
    {
        $hydrator = new ProjectGrantFormHydrator();
        $hydrator->setServiceLocator($this->getServiceLocator());
        $this->setHydrator($hydrator);

        /** @var ProjectGrantService $grantService */
        $grantService = $this->getServiceLocator()->get('ProjectGrantService');

        $this->add([
            'type' => 'Hidden',
            'name' => 'id'
        ]);

        // LABEL
        $label = _("Intitulé de l'activité");
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

        // DESCRIPTION
        $label = _("Description");
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

        // Source
        $this->add([
            'name'   => 'disciplines',
            'options' => [
                'label' => _('Discipline(s)'),
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

        // Financial impact
        $this->add([
            'name'   => 'financialImpact',
            'options' => [
                'label' => _('Incidence financière'),
                'value_options' => Activity::getFinancialImpactValues()
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
                'label' => _("Type d'activité"),
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
                'label' => _("Devise"),
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
                'label' => 'EOTP'
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

        // Amount
        $this->add([
            'name'   => 'fraisDeGestion',
            'help' => '% du montant consacré aux frais de gestion',
            'options' => [
                'label' => 'Frais de Gestion'
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Frais de gestion (ex: 12.5)'
            ],
            'type'=>'Text'
        ]);

        // Amount
        $this->add([
            'name'   => 'assietteSubventionnable',
            'help' => "% de l'assiette subventionnable",
            'options' => [
                'label' => 'Assiette subventionnable'
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Assiette subventionnable (ex: 5.5)'
            ],
            'type'=>'Text'
        ]);

        $label = "Note financière";
        $this->add([
            'name'   => 'noteFinanciere',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-xs',
                'placeholder' => $label,
            ],
            'type'=>'Textarea'
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
            'type'=>'Text'
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
            'type'=>'Text'
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
            'type'=>'Text'
        ]);

        // DateOpened
        $this->add([
            'name'   => 'dateOpened',
            'options' => [
                'label' => "Création du PFI"
            ],
            'attributes' => [
                'class' => 'input-date'
            ],
            'type'=>'Text'
        ]);

        // DateOpened
        $this->add([
            'name'   => 'numbers',
            'type'=>KeyValue::class
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

    }

    public function getInputFilterSpecification()
    {
        return [
            'centaureId'=> [
                'required' => false,
            ],

            'centaureNumConvention'=> [
                'required' => false,
            ],

            'source'=> [
                'required' => false,
            ],

            'type'=> [
                'required' => false,
            ],

            'numbers'=> [
                'required' => false,
            ],

            'codeEOTP'=> [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    new EOTP($this->getServiceLocator()->get('Config')['oscar']['validation']['pfi']),
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

            'disciplines' => [
                'required' => false,
            ],
        ];
    }
}
