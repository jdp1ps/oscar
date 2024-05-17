<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Factory;


use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationType;

/**
 * Class LdapToOrganization
 * Cette classe permet de générer des objets Organization à partir de données LDAP
 * @package Oscar\Factory
 */
class LdapToOrganization extends JsonToObject implements IJsonToOrganisation
{
    public function __construct(array $types, array $typeMappings)
    {
        parent::__construct(['supanncodeentite', 'code', 'ou', 'supanntypeentite']);
        $this->types = $types;
        $this->typeMappings = $typeMappings;
    }

    private ?array $types;

    protected function getTypeObj(string $typeLabel): ?OrganizationType
    {
        if (is_array($this->types) && array_key_exists($typeLabel, $this->types)) {
            return $this->types[$typeLabel];
        }
        return null;
    }

    /**
     * @param $jsonData stdClass objet contenant les données
     * @param $connectorName
     * @return Organization
     */
    public function getInstance($jsonData, $connectorName = null)
    {
        $organization = new Organization();

        return $this->hydrateWithDatas(
            $organization,
            $jsonData,
            $connectorName
        );
    }

    /**
     * @param Organization $object
     * @param $ldapData
     */
    public function hydrateWithDatas($object, $ldapData, $connectorName = null)
    {
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $this->getFieldValue($ldapData, 'supanncodeentite')
            );
        }
        $shortName = $this->getFieldValue($ldapData, 'ou');
        if (preg_match('/^([A-Z]+\s*\d*)\s*-?\s*/', $shortName, $matches)) {
            $code = $matches[1];
        } else {
            throw new \Exception("Invalid short name: $shortName");
        }
        if (property_exists($ldapData, 'modifytimestamp')) {
            $rawdateupdated = $this->getFieldValue($ldapData, 'modifytimestamp', null);
            $dateupdated = \DateTime::createFromFormat(
                'YmdHis', substr($rawdateupdated, 0, 14),
                new \DateTimeZone('UTC')
            );
        }
        if (!isset($dateupdated) || !$dateupdated instanceof \DateTime) {
            $dateupdated = new \DateTime();
        }

        $object
            ->setDateUpdated($dateupdated)
            ->setShortName($shortName)
            ->setCode($code)
            ->setFullName($this->getFieldValue($ldapData, 'description'))
            ->setPhone($this->getFieldValue($ldapData, 'telephonenumber'))
            ->setDescription($this->getFieldValue($ldapData, 'description'))
            //TODO org email ?
            ->setEmail($this->getFieldValue($ldapData, 'email'))
            ->setUrl($this->getFieldValue($ldapData, 'labeleduri'))
            //TODO org Siret ?
            ->setSiret($this->getFieldValue($ldapData, 'siret'))
            ->setDuns($this->getFieldValue($ldapData, 'duns'))
            ->setTvaintra($this->getFieldValue($ldapData, 'tvaintra'));
        $ldapType = $this->getFieldValue($ldapData, 'supanntypeentite');
        $this->assignOrgTypes($ldapType, $object);
        // if there is now type, throw an exception
        if (null === $object->getType()) {
            throw new \Exception("Invalid type: $ldapType");
        }

        $this->extractIdentifiers($ldapData, $object);

        // adresse example : Centre Meudon$1 PLACE ARISTIDE BRIAND$92190 MEUDON$FRANCE
        $address = $this->getFieldValue($ldapData, 'postaladdress');
        $addressFields = explode('$', $address);
        if (count($addressFields) == 3) {
            // adresse sur une ligne pas de nom d'institution, seulement la rue
            array_splice($addressFields, 1, 0, '');
        }
        if (count($addressFields) == 4) {
            $zipCity = explode(' ', $addressFields[2], 2);
            //check that zip code is a number
            if (is_numeric($zipCity[0])) {
                $object
                    ->setStreet1($addressFields[0])
                    ->setStreet2($addressFields[1])
                    ->setZipCode($zipCity[0])
                    ->setCity($zipCity[1])
                    ->setCountry($addressFields[3]);
            } else {
                throw new \Exception("Invalid zip code: $zipCity[0]");
            }
        } else {
            throw new \Exception("Invalid address: $addressFields");
        }

        return $object;
    }

    /**
     * @param string|null $ldapType
     * @param Organization $object
     * @return void
     */
    private function assignOrgTypes(?string $ldapType, Organization $object): void
    {
        foreach ($this->typeMappings as $typeMapping) {
            if (in_array($ldapType, $typeMapping['codes'])) {
                $object->setType($typeMapping['name']);
                $object->setTypeObj($this->getTypeObj($typeMapping['name']));
                break;
            }
        }
    }

    /**
     * @param \stdClass $ldapData
     * @param Organization $object
     * @throws \Oscar\Exception\OscarException
     */
    private function extractIdentifiers(stdClass $ldapData, Organization $object)
    {
        $supannRefid = $this->getFieldValue($ldapData, 'supannrefid', []);
        if (!is_array($supannRefid)) {
            $supannRefid = [$supannRefid];
        }
        foreach ($supannRefid as $refid) {
            if (preg_match('/^{RNSR}(.*)$/', $refid, $matches)) {
                $object->setRnsr($matches[1]);
            } elseif (preg_match('/^{CNRS}(.*)$/', $refid, $matches)) {
                $object->setLabintel($matches[1]);
            }
        }
    }
}
