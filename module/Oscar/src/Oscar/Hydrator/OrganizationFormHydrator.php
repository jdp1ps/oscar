<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:21
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Organization;
use Zend\Hydrator\HydratorInterface;

class OrganizationFormHydrator implements HydratorInterface
{
    private $connectors;
    private $types;

    /**
     * OrganizationFormHydrator constructor.
     * @param $connectors
     * @param $types
     */
    public function __construct($connectors, $types)
    {
        $this->connectors = $connectors;
        $this->types = $types;
    }


    /**
     * @param array $data
     * @param Organization $object
     */
    public function hydrate(array $data, $object)
    {
        $type = array_key_exists($data['typeObj'], $this->types) ? $this->types[$data['typeObj']] : null;

        $object->setTypeObj($type);
        $object->setShortName($data['shortName']);
        $object->setCode($data['code']);
        $object->setSiret($data['siret']);
        $object->setFullName($data['fullName']);
        $object->setLabintel($data['labintel']);

        $object->setDateStart(
            !$data['dateStart'] ? null : new \DateTime($data['dateStart'])
        );
        $object->setDateEnd(
            !$data['dateEnd'] ? null : new \DateTime($data['dateEnd'])
        );



        // ADDRESS
        $object->setStreet1($data['street1']);
        $object->setStreet2($data['street2']);
        $object->setStreet3($data['street3']);
        $object->setZipCode($data['zipCode']);
        $object->setCity($data['city']);
        $object->setCountry($data['country']);
        $object->setPhone($data['phone']);

        $object->setEmail($data['email']);
        $object->setUrl($data['url']);
        $object->setDescription($data['description']);

        foreach ($this->connectors as $connector) {
            $object->setConnectorID($connector, $data['connector_' . $connector]);
        }

        return $object;
    }

    /**
     * @param Organization $object
     * @return array
     */
    public function extract($object)
    {

        $datas = [
            'id'        => $object->getId(),
            'shortName' => $object->getShortName(),
            'code'      => $object->getCode(),
            'labintel'  => $object->getLabintel(),
            'typeObj'   => $object->getTypeObj() ? $object->getTypeObj()->getId() : null,
            'siret'     => $object->getSiret(),
            'fullname'  => $object->getFullName(),
            'dateStart' => $object->getDateStart()  ? $object->getDateStart()->format('Y-m-d')  : '',
            'dateEnd'   => $object->getDateEnd()    ? $object->getDateEnd()->format('Y-m-d')    : '',
            'fullName'  => $object->getFullName(),
            // ADDRESS
            'street1'  => $object->getStreet1(),
            'street2'  => $object->getStreet2(),
            'street3'  => $object->getStreet3(),
            'zipCode'  => $object->getZipCode(),
            'city'  => $object->getCity(),
            'country'  => $object->getCountry(),
            'phone'  => $object->getPhone(),

            'email'  => $object->getEmail(),
            'url'  => $object->getUrl(),
            'description'  => $object->getDescription(),
        ];
        foreach ($this->connectors as $connector) {
            $datas['connector_' . $connector] = $object->getConnectorID($connector);
        }

        return $datas;

    }
}