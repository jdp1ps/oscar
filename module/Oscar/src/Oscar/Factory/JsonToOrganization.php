<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-15 16:32
 * @copyright Certic (c) 2017
 */

namespace Oscar\Factory;


use Oscar\Entity\Organization;

/**
 * Class JsonToPersonFactory
 * Cette classe permet de générer des objets Organization à partir de données JSON.
 * @package Oscar\Factory
 */
class JsonToOrganization extends JsonToObject implements IJsonToOrganisation
{
    public function __construct()
    {
        parent::__construct(['code', 'shortname']);
    }

    /**
     * @param $jsonData stdObject contenant les données
     * @param $connectorName
     * @return Organization
     */
    public function getInstance($jsonData, $connectorName=null)
    {
        $organization = new Organization();

        return $this->hydrateWithDatas($organization, $jsonData,
            $connectorName);
    }

    /**
     * @param Organization $object
     * @param $jsonData
     */
    function hydrateWithDatas($object, $jsonData, $connectorName = null)
    {
        if ($connectorName !== null) {
            $object->setConnectorID($connectorName,
                $this->getFieldValue($jsonData, 'code'));
        }

        return $object
            ->setDateUpdated(new \DateTime($this->getFieldValue($jsonData,
                'dateupdate', null)))
            ->setShortName($this->getFieldValue($jsonData, 'shortname'))
            ->setCode($this->getFieldValue($jsonData, 'code'))
            ->setFullName($this->getFieldValue($jsonData, 'longname'))
            ->setPhone($this->getFieldValue($jsonData, 'phone'))
            ->setDescription($this->getFieldValue($jsonData, 'description'))
            ->setStreet1(property_exists($jsonData,
                'address') ? $jsonData->address->address1 : null)
            ->setStreet2(property_exists($jsonData,
                'address') ? $jsonData->address->address2 : null)
            ->setZipCode(property_exists($jsonData,
                'address') ? $jsonData->address->zipcode : null)
            ->setCity(property_exists($jsonData,
                'address') ? $jsonData->address->city : null)
            ->setCountry(property_exists($jsonData,
                'address') ? $jsonData->address->country : null)
            ->setBp(property_exists($jsonData,
                'address') ? $jsonData->address->address3 : null);

    }
}