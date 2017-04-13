<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:21
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Person;
use Zend\Stdlib\Hydrator\HydrationInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class OrganizationFormHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Person $object
     */
    public function hydrate(array $data, $object)
    {
        $object->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setCodeHarpege($data['codeHarpege'])
            ->setCodeLdap($data['codeLdap'])
            ->setCodeHarpege($data['codeHarpege'])
            ->setEmail($data['email'])
            ->setPhone($data['phone']);

        return $object;
    }

    /**
     * @param Person $object
     * @return array
     */
    public function extract($object)
    {
        return [
            'id' => $object->getId(),
            'firstname' => $object->getFirstname(),
            'lastname' => $object->getLastname(),
            'codeLdap' => $object->getCodeLdap(),
            'phone' => $object->getPhone(),
            'codeHarpege' => $object->getCodeHarpege(),
            'email' => $object->getEmail(),
        ];
    }
}