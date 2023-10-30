<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date October 16, 2015 15:34
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;

use Oscar\Entity\Activity;
use Oscar\Form\Element\KeyValue;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\ProjectGrantService;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Validator\EOTP;
use UnicaenApp\Util;
use Laminas\Filter\StringTrim;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Oscar\Hydrator\ProjectGrantFormHydrator;

class ProjectGrantForm extends Form implements InputFilterProviderInterface, UseServiceContainer
{

    use UseServiceContainerTrait;

    private $numbers;
    private $editable;

    private $organizations = false;
    private $organizationRoles = null;

    public function setNumbers($numbers, $editable)
    {
        $this->numbers = $numbers;
        $this->editable = $editable;
    }

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService()
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    public function addOrganizationsLeader($organizations, $organizationRoles)
    {
        $this->organizations = $organizations;
        $this->organizationRoles = $organizationRoles;
    }

    public function init()
    {
        $hydrator = new ProjectGrantFormHydrator();
        $hydrator->setNumbers($this->numbers);
        $hydrator->addOrganizationsLeader($this->organizations, $this->organizationRoles);
        $hydrator->setServiceContainer($this->getServiceContainer());
        $this->setHydrator($hydrator);

        /** @var ProjectGrantService $grantService */
        $grantService = $this->getServiceContainer()->get(ProjectGrantService::class);

        $this->add([
            'type' => 'Hidden',
            'name' => 'id'
        ]);

//        if( $this->organizations ){
//            foreach ($this->organizations as $organization) {
//                // Status
//                $this->add([
//                    'name'   => 'organization[' . $organization->getId() .']',
//                    'label' => (string)$organization,
//                    'options' => [
//                        'label' => 'Rôle de ' . (string)$organization,
//                        'value_options' => $this->organizationRoles
//                    ],
//                    'attributes' => [
//                        'class' => 'form-control'
//                    ],
//                    'type'=>'Select'
//                ]);
//            }
//        }

        // LABEL
        $label = _("Intitulé de l'activité");
        $this->add([
            'name' => 'label',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-lg',
                'placeholder' => $label,
            ],
            'type' => 'Text'
        ]);

        // DESCRIPTION
        $label = _("Description");
        $this->add([
            'name' => 'description',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-xs',
                'placeholder' => $label,
            ],
            'type' => 'Textarea'
        ]);

        // Source
        $this->add([
            'name' => 'disciplines',
            'options' => [
                'label' => _('Discipline(s)'),
                'value_options' => $this->getProjectGrantService()->getDisciplines()
            ],
            'attributes' => [
                'class' => 'form-control select2',
                'multiple' => 'multiple'
            ],
            'type' => 'Select'
        ]);

        // Status
        $this->add([
            'name' => 'status',
            'options' => [
                'label' => 'Statut',
                'value_options' => $this->getProjectGrantService()->getStatus()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // Financial impact
        $this->add([
            'name' => 'financialImpact',
            'options' => [
                'label' => _('Incidence financière'),
                'value_options' => Activity::getFinancialImpactValues()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // Source
        $this->add([
            'name' => 'activityType',
            'options' => [
                'label' => _("Type d'activité"),
                'value_options' => $this->getProjectGrantService()->getActivityTypes(true)
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // Tune
        $this->add([
            'name' => 'currency',
            'options' => [
                'label' => _("Devise"),
                'value_options' => Util::collectionAsOptions($grantService->getCurrencies())
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);


        // Tune
        $this->add([
            'name' => 'tva',
            'options' => [
                'label' => "TVA",
                'value_options' => $this->getProjectGrantService()->getTVAsValuesOptions()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // CodeEOTP
        $this->add([
            'name' => 'codeEOTP',
            'options' => [
                'label' => 'N°Financier (' . $this->getProjectGrantService()->getOscarConfigurationService()->getFinancialLabel() . ')'
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Text'
        ]);

        // Amount
        $this->add([
            'name' => 'amount',
            'options' => [
                'label' => 'Montant'
            ],
            'attributes' => [
                'class' => 'form-control input-lg'
            ],
            'type' => 'Text'
        ]);

        // Amount
        $this->add([
            'name' => 'fraisDeGestion',
            'options' => [
                'label' => 'Frais de Gestion'
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
            'type' => 'Text'
        ]);

        // Amount
        $this->add([
            'name' => 'fraisDeGestionPartHebergeur',
            'options' => [
                'label' => 'Part hébergeur'
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
            'type' => 'Text'
        ]);

        // Amount
        $this->add([
           'name' => 'fraisDeGestionPartUnite',
           'options' => [
               'label' => 'Part unité'
           ],
           'attributes' => [
               'class' => 'form-control',
           ],
           'type' => 'Text'
       ]);

        // Amount
        $this->add([
            'name' => 'assietteSubventionnable',
            'help' => "% de l'assiette éligible",
            'options' => [
                'label' => 'Assiette éligible'
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Assiette éligible (ex: 5.5)'
            ],
            'type' => 'Text'
        ]);

        $label = "Note";
        $this->add([
            'name' => 'noteFinanciere',
            'options' => [
                'label' => $label,
            ],
            'attributes' => [
                'class' => 'form-control input-xs',
                'placeholder' => $label,
            ],
            'type' => 'Textarea'
        ]);


        // DateStart
        $this->add([
            'name' => 'dateStart',
            'options' => [
                'label' => 'Début du contrat'
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type' => 'Text'
        ]);

        // DateEnd
        $this->add([
            'name' => 'dateEnd',
            'options' => [
                'label' => 'Fin du contrat'
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type' => 'Text'
        ]);

        // DateSigned
        $this->add([
            'name' => 'dateSigned',
            'options' => [
                'label' => 'Date de signature'
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type' => 'Text'
        ]);

        // DateOpened
        $this->add([
            'name' => 'dateOpened',
            'options' => [
                'label' => "Création du N°financier"
            ],
            'attributes' => [
                'class' => 'input-date form-control'
            ],
            'type' => 'Text'
        ]);


        // DateOpened
        $this->add(new KeyValue('numbers', ['keys' => $this->numbers, 'editable' => $this->editable]));


        // Type
        $this->add([
            'name' => 'type',
            'options' => [
                'label' => 'Type de convention',
                'value_options' => $this->getProjectGrantService()->getTypes()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // Type
        $this->add([
            'name' => 'pcruPoleCompetitivite',
            'options' => [
                'label' => 'Pôle de compétitivité (PCRU)',
                'value_options' => $this->getProjectGrantService()->getPcruPoleCompetitiviteSelect()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

        // Type
        $this->add([
            'name' => 'pcruValidPoleCompetitivite',
            'options' => [
                'label' => 'Validé par le pôle de compétitivité (PCRU)',
            ],
            'attributes' => [
                'class' => 'checkbox'
            ],
            'type' => 'Checkbox'
        ]);

        // Type
        $this->add([
            'name' => 'pcruSourceFinancement',
            'options' => [
                'label' => 'Source de financement (PCRU)',
                'value_options' => $this->getProjectGrantService()->getPcruSourceFinancementSelect()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type' => 'Select'
        ]);

    }

    public function getInputFilterSpecification()
    {
        // PFI
        /** @var OscarConfigurationService $oscarConfigurationService */
        $oscarConfigurationService = $this->getServiceContainer()->get(OscarConfigurationService::class);
        $validatorEotp = [];
        if( $oscarConfigurationService->isPfiStrict() ){
            $validatorEotp[] = new EOTP($oscarConfigurationService->getValidationPFI());
        }


        return [
            'centaureId' => [
                'required' => false,
            ],

            'centaureNumConvention' => [
                'required' => false,
            ],

            'source' => [
                'required' => false,
            ],

            'type' => [
                'required' => false,
            ],

            'numbers' => [
                'required' => false,
            ],

            'codeEOTP' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => $validatorEotp
            ],

            'amount' => [
                'required' => false,
            ],

            'dateStart' => [
                'required' => false,
            ],

            'dateEnd' => [
                'required' => false,
            ],

            'status' => [
                'required' => false,
            ],

            'dateSigned' => [
                'required' => false,
            ],

            'dateOpened' => [
                'required' => false,
            ],

            'disciplines' => [
                'required' => false,
            ],

            'pcruPoleCompetitivite' => [
                'required' => false
            ],

            'pcruValidPoleCompetitivite' => [
                'required' => false
            ],

            'pcruSourceFinancement' => [
                'required' => false
            ],
        ];
    }
}
