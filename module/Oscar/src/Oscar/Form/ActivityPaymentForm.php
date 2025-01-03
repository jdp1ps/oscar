<?php
namespace Oscar\Form;


use Doctrine\ORM\Query;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\DateType;
use Oscar\Hydrator\ActivityDateFormHydrator;
use Oscar\Hydrator\ActivityPaymentFormHydrator;
use Oscar\Hydrator\DateTypeFormHydrator;
use Oscar\Service\ProjectGrantService;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ActivityPaymentForm extends Form implements InputFilterProviderInterface, ElementInterface
{
    private $projectGrantService;

    /**
     * @return mixed
     */
    public function getProjectGrantService() :ProjectGrantService
    {
        return $this->projectGrantService;
    }

    /**
     * @param mixed $projectGrantService
     */
    public function setProjectGrantService( ProjectGrantService $projectGrantService): void
    {
        $this->projectGrantService = $projectGrantService;
    }

    public function init()
    {
        $hydrator = new ActivityPaymentFormHydrator();
        $hydrator->setProjectGrantService($this->getProjectGrantService());
        $this->setHydrator($hydrator);

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        // LABEL
        $this->add([
            'name'   => 'amount',
            'options' => [
                'label' => 'Montant'
            ],
            'attributes'    => [
                'class'       => 'form-control input-lg',
            ],
            'type'=>'Text'
        ]);

        // LABEL
        $label = 'Status du versemment';
        $this->add([
            'name'   => 'status',
            'options' => [
                'label' => 'Statut',
                'value_options' => ActivityPayment::getStatusPayments()
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        // LABEL
        $label = 'Date effective';
        $this->add([
            'name'   => 'datePayment',
            'options' => [
                'label' => $label,
            ],
            'attributes'    => [
                'class'       => 'form-control datepicker',
                'placeholder' => $label
            ],
            'type'=>'Text'
        ]);

        // Date prévue
        $label = 'Date prévue';
        $this->add([
            'name'   => 'datePredicted',
            'options' => [
                'label' => $label,
            ],
            'attributes'    => [
                'class'       => 'form-control datepicker',
                'placeholder' => $label
            ],
            'type'=>'Text'
        ]);

        $this->add([
            'name'   => 'codeTransaction',
            'options' => [
                'label' => 'N° de pièce'
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Text'
        ]);

        // Types de date
        $currencies = $this->getProjectGrantService()->getCurrenciesSelect();
        $this->add([
            'name'   => 'currency',
            'options' => [
                'label' => 'Devise pour le versement',
                'value_options' => $currencies
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Select'
        ]);

        $this->add([
            'name'   => 'rate',
            'options' => [
                'label' => 'Taux'
            ],
            'attributes' => [
                'class' => 'form-control'
            ],
            'type'=>'Text'
        ]);

        // DESCRIPTION
        $label = 'Commentaire';
        $this->add([
            'name'   => 'comment',
            'options' => [
                'label' => $label
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => $label,
            ],
            'type'=>'Textarea'
        ]);

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }


    /**
     * Règles de vérification des données du formulaire.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'amount' => [ 'required' => true ],
            'datePredicted' => [
                'required' => ($this->get('status')->getValue() == ActivityPayment::STATUS_PREVISIONNEL)
            ],
            'datePayment' => [
                'required' => ($this->get('status')->getValue() == ActivityPayment::STATUS_REALISE)
            ],
            'currency' => [ 'required' => true ],
            'comment' => [ 'required' => false ]
        ];
    }
}