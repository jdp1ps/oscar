<?php

namespace UnicaenAuth\Form\Droits;

use UnicaenAuth\Entity\Db\Role;
use UnicaenAuth\Service\Traits\RoleServiceAwareTrait;
use Zend\Form\Form;
use UnicaenApp\Util;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Description of RoleForm
 *
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class RoleForm extends Form implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use RoleServiceAwareTrait;

    public function init()
    {
        $hydrator = new RoleFormHydrator;
        $hydrator->setServiceRole( $this->getServiceRole() );
        $this->setHydrator($hydrator);

        $this->add([
            'type'    => 'Text',
            'name'    => 'role-id',
            'options' => [
                'label' => 'Nom',
            ],
        ]);

        $this->add([
            'type'    => 'Text',
            'name'    => 'ldap-filter',
            'options' => [
                'label' => 'Filtre LDAP',
            ],
        ]);

        $this->add([
            'type' => 'Select',
            'name' => 'parent',
            'options' => [
                'label' => 'Parent',
                'empty_option' => '- Aucun -',
                'value_options' => Util::collectionAsOptions($this->getServiceRole()->getList()),
            ],
        ]);

        $this->add([
            'name' => 'id',
            'type' => 'Hidden',
        ]);

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
            'role-id' => [
                'required' => true,
            ],
            'ldap-filter' => [
                'required' => false,
            ],
            'parent' => [
                'required' => false,
            ],
        ];
    }
}





/**
 * Class RoleFormHydrator
 *
 * @package UnicaenAuth\Form\Droits
 * @author  Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class RoleFormHydrator implements HydratorInterface
{
    use RoleServiceAwareTrait;

    /**
     * @param  array $data
     * @param  Role  $object
     *
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $object->setRoleId($data['role-id']);
        $object->setLdapFilter($data['ldap-filter'] ?: null);
        $object->setParent($this->getServiceRole()->get($data['parent']));

        return $object;
    }



    /**
     * @param  Role $object
     *
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'id'          => $object->getId(),
            'role-id'     => $object->getRoleId(),
            'ldap-filter' => $object->getLdapFilter(),
            'parent'      => $object->getParent() ? $object->getParent()->getId() : null,
        ];

        return $data;
    }
}