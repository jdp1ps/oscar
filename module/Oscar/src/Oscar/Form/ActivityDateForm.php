<?php
namespace Oscar\Form;


use Doctrine\ORM\Query;
use Oscar\Entity\DateType;
use Oscar\Hydrator\ActivityDateFormHydrator;
use Oscar\Hydrator\DateTypeFormHydrator;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ActivityDateForm extends Form implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function init()
    {
        $hydrator = new ActivityDateFormHydrator();
        $hydrator->setServiceLocator($this->getServiceLocator());
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



        $types = $this->getServiceLocator()->get('ActivityService')->getDateTypesSelect();

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