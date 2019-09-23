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
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ActivityRequestForm extends Form implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    public function init()
    {

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

    }

    public function getInputFilterSpecification()
    {
        return [
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
        ];
    }
}
