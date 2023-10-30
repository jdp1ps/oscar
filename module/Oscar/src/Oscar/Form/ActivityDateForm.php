<?php
namespace Oscar\Form;


use Doctrine\ORM\Query;
use Oscar\Entity\DateType;
use Oscar\Hydrator\ActivityDateFormHydrator;
use Oscar\Hydrator\DateTypeFormHydrator;
use Oscar\Service\ProjectGrantService;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ActivityDateForm extends Form
{
    /** @var ProjectGrantService */
    private $projectGrantService;

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->projectGrantService;
    }

    /**
     * @param ProjectGrantService $projectGrantService
     */
    public function setProjectGrantService(ProjectGrantService $projectGrantService): void
    {
        $this->projectGrantService = $projectGrantService;
    }

    public function init()
    {
        $hydrator = new ActivityDateFormHydrator();
        $hydrator->setProjectGrantService($this->getProjectGrantService());
        $this->setHydrator($hydrator);

        $this->add(array(
            'name'  => 'id',
            'type'  => 'Hidden',
        ));

        // LABEL
        $this->add([
            'name'   => 'type',
            'attributes'    => [
                'class'       => 'form-control',
            ],
            'type'=>'Text'
        ]);

        // LABEL
        $this->add([
            'name'   => 'dateStart',
            'options' => [
                'label' => 'Date',
            ],
            'attributes'    => [
                'class'       => 'form-control datepicker',
                'placeholder' => 'Date de l\'échéance'
            ],
            'type'=>'Text'
        ]);



        $types = $this->getProjectGrantService()->getDateTypesSelect();

        // Types de date
        $dateType = new Select();
        $dateType->setLabel('Type de jalon')
            ->setName('type')
            ->setAttribute('class', 'form-control')
            ->setValueOptions($types);


        $this->add($dateType);

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



    public function getInputFilterSpecification()
    {
        return [
            'type' => [ 'required' => true ],
            'dateStart' => [ 'required' => true ],
            'comment' => [ 'required' => false ]
        ];
    }
}