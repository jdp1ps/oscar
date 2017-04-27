<?php

namespace <namespace>;

use Zend\Form\<type>;
use Zend\InputFilter\InputFilterProviderInterface;
<if useServiceLocator>
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
<endif useServiceLocator>
<if useHydrator>
use Zend\Stdlib\Hydrator\HydratorInterface;
<endif useHydrator>



/**
 * Description of <classname>
 *
 * @author <author>
 */
class <classname> extends <type> implements <if useServiceLocator>ServiceLocatorAwareInterface, <endif useServiceLocator>InputFilterProviderInterface
{
    <if useServiceLocator>
    use ServiceLocatorAwareTrait;
    <endif useServiceLocator>

    public function init()
    {
        <if useHydrator>
        $hydrator = new <classname>Hydrator;
        $this->setHydrator($hydrator);
        <endif useHydrator>

        /* Ajoutez vos éléments de formulaire ici */

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary',
            ],
        ]);
    }



    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            /* Filtres et validateurs */
        ];
    }

}



<if useHydrator>
class <classname>Hydrator implements HydratorInterface
{

    /**
     * @param  array    $data
     * @param           $object
     *
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        /* on peuple l'objet à partir du tableau de données */

        return $object;
    }



    /**
     * @param  $object
     *
     * @return array
     */
    public function extract($object)
    {
        $data = [
            /* On peuple le tableau avec les données de l'objet */
        ];

        return $data;
    }
}
<endif useHydrator>