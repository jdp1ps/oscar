<?php

namespace Oscar\Form;

use Oscar\Entity\Activity;
use Oscar\Hydrator\ActivityInfosPCRUFormHydrator;
use Oscar\Hydrator\OrganizationFormHydrator;
use Oscar\Service\OrganizationService;
use Oscar\Service\ProjectGrantService;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2021
 */
class ActivityInfosPcruForm extends \Laminas\Form\Form implements InputFilterProviderInterface
{

    private $projectGrantService;
    private $activity;

    function __construct(ProjectGrantService $projectGrantService, Activity $activity)
    {
        parent::__construct('activityinfospcru');
        $this->projectGrantService = $projectGrantService;
        $this->activity = $activity;
    }

    public function init(){
        $this->setHydrator(new ActivityInfosPCRUFormHydrator($this->projectGrantService));

        $this->add(array(
            'name'  => 'objet',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'readonly'  => "readonly"
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'codeunitelabintel',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'sigleunite',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'numcontrattutellegestionnaire',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'readonly'  => "readonly"
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'equipe',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));


        $this->add(array(
            'name'  => 'typecontrat',
            'type'  => 'Select',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ],
                'value_options' => $this->projectGrantService->getPcruTypeContractSelect()
            )
        ));

        $this->add(array(
            'name'  => 'acronyme',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'contratsassocies',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'responsablescientifique',
            'type'  => 'Select',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ],
                'value_options' => $this->projectGrantService->getPCRUService()->getResponsableScientifiques($this->activity)
            )
        ));

        $this->add(array(
            'name'  => 'employeurresponsablescientifique',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'coordinateurconsortium',
            'type'  => 'Checkbox',
            'attributes'    => [
                'class'       => 'checkbox',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'partenaires',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'partenaireprincipal',
            'type'  => 'Select',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ],
                'value_options' => $this->projectGrantService->getPCRUService()->getActivityPartenaires($this->activity)
            )
        ));


        $this->add(array(
            'name'  => 'idpartenaireprincipal',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'readonly' => 'readonly'
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'sourcefinancement',
            'type'  => 'Select',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'value_options' => $this->projectGrantService->getPcruSourceFinancementSelect(),
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'lieuexecution',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'datedernieresignature',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'duree',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'datedebut',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'datefin',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'montantpercuunite',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'couttotaletude',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'montanttotal',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'datedebut',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'polecompetivite',
            'type'  => 'Select',
            'attributes'    => [

            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ],
                'value_options' => $this->projectGrantService->getPcruPoleCompetitiviteSelect()
            )
        ));

        $this->add(array(
            'name'  => 'validepolecompetivite',
            'type'  => 'Checkbox',
            'attributes'    => [

            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'commentaires',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'pia',
            'type'  => 'Checkbox',
            'attributes'    => [
                'class'       => '',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'reference',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
                'readonly'  => "readonly"
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'accordcadre',
            'type'  => 'Checkbox',
            'attributes'    => [

            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'cifre',
            'type'  => 'Checkbox',
            'attributes'    => [

            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'chaireindustrielle',
            'type'  => 'Text',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
        ));

        $this->add(array(
            'name'  => 'presencepartenaireindustriel',
            'type'  => 'Checkbox',
            'attributes'    => [
            ],
            'options'   => array(
                'label_attributes'  => [
                    'class' => 'form-label required'
                ]
            )
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

            'polecompetivite'=> [
                'required' => false,
            ]
        ];
    }
}