<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Factory;


use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationType;
use Oscar\Exception\OscarException;

/**
 * Class LdapToOrganization
 * Cette classe permet de générer des objets Organization à partir de données LDAP
 * @package Oscar\Factory
 */
class LdapToOrganization extends JsonToObject implements IJsonToOrganisation
{
    /**
     * @var array Oscar organization types
     */
    private array $types;

    /**
     * @var array ldap type to Oscar type mapping
     */
    private array $typeMappings;

    /**
     * @var string class name of the address parser
     */
    private string $addressParser;

    /**
     * @param array $types Oscar organization types
     * @param array $typeMappings ldap type to Oscar type mapping
     * @param string $addressParser class name of the address parser
     */
    public function __construct(array $types, array $typeMappings, string $addressParser)
    {
        parent::__construct(['ou', 'supanncodeentite', 'supanntypeentite']);
        $this->types = $types;
        $this->typeMappings = $typeMappings;
        $this->addressParser = $addressParser;

    }

    protected function getTypeObj(string $typeLabel): ?OrganizationType
    {
        if (is_array($this->types) && array_key_exists($typeLabel, $this->types)) {
            return $this->types[$typeLabel];
        }
        return null;
    }

    public function getInstance($jsonData, $connectorName = null)
    {
        // just to fullfill the interface
    }

    /**
     * @param Organization $object
     * @param $ldapData
     */
    public function hydrateWithDatas($object, $ldapData, $connectorName = null)
    {
        $code = $this->getFieldValue($ldapData, 'supanncodeentite');
        if ($connectorName !== null) {
            $object->setConnectorID(
                $connectorName,
                $code
            );
        }
        $shortName = $this->getFieldValue($ldapData, 'ou');
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

        $uri = $this->getFieldValue($ldapData, 'eduorghomepageuri'); // Refeds EduOrg attribute
        if (null === $uri) {
            $uri = $this->getFieldValue($ldapData, 'labeleduri'); // Supann attribute
        }
        $fullName = $this->getFieldValue($ldapData, 'eduorglegalname'); // Refeds EduOrg attribute
        if (null === $fullName) {
            $fullName = $this->getFieldValue($ldapData, 'description'); // Supann attribute
        }
        $description = $this->getFieldValue($ldapData, 'info');
        if (null === $description) {
            $description = $this->getFieldValue($ldapData, 'description');
        }
        $object
            ->setDateUpdated($dateupdated)
            ->setShortName($shortName)
            ->setCode($code)
            ->setFullName($fullName)
            ->setPhone($this->getFieldValue($ldapData, 'telephonenumber'))
            ->setDescription($description)
            ->setEmail($this->getFieldValue($ldapData, 'mail'))
            ->setUrl($uri);
        $ldapType = $this->getFieldValue($ldapData, 'supanntypeentite');
        $this->assignOrgTypes($ldapType, $object);
        if (null === $object->getType()) {
            throw new \Exception("Invalid type: $ldapType");
        }

        $this->extractIdentifiers($ldapData, $object);

        $address = $this->getFieldValue($ldapData, 'postaladdress');
        if (null !== $address) {
            try {
                $addressParser = new $this->addressParser();
                //stdClass
                /** @var \stdClass $address */
                $address = $addressParser->parse($address, $object);
                // if adresse is not false, assign fields to object
                if ($address) {
                    $object->setStreet1($address->street1)
                        ->setStreet2($address->street2)
                        ->setStreet3($address->street3)
                        ->setZipCode($address->zipCode)
                        ->setCity($address->city)
                        ->setCountry($address->country);
                }
            } catch (OscarException $e) {
                throw new \Exception("Invalid address: $address");
            }
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
    private function extractIdentifiers(\stdClass $ldapData, Organization $object)
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
            } elseif (preg_match('/^{SIRET}(.*)$/', $refid, $matches)) {
                $object->setSiret($matches[1]);
            }
        }
    }
}
