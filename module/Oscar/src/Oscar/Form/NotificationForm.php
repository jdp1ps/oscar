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
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class NotificationForm extends Form
{
    public function init()
    {
        // DESCRIPTION
        $this->add([
            'name'   => 'message',
            'options' => [
                'label' => "Message"
            ],
            'attributes'    => [
                'class'       => 'form-control',
                'placeholder'   => 'Le message',
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
            'message' => [ 'required' => true ]
        ];
    }
}