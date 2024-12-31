<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Factory;


use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;

/**
 * Class LdapToPerson
 * Cette classe permet de générer des objets Person à partir de données LDAP
 * @package Oscar\Factory
 */
class LdapToPerson extends JsonToObject implements IJsonToPerson
{


    private array $rolesMapping;

    private OrganizationRepository $organizationRepository;

    public function __construct(array $rolesMapping, OrganizationRepository $organizationRepository)
    {
        parent::__construct(['uid', 'givenname', 'sn', 'supannaliaslogin']);
        $this->rolesMapping = $rolesMapping;
        $this->organizationRepository = $organizationRepository;
    }

    public function getInstance($jsonData, $connectorName = null)
    {
        $person = new Person();

        return $this->hydrateWithDatas($person, $jsonData, $connectorName);
    }


    public function hydrateWithDatas($object, $ldapData, $connectorName = null)
{
    $codeAffectation = $this->ensureSingleValue($this->getFieldValue($ldapData, 'supannentiteaffectationprincipale'));

    $object->setFirstname($this->ensureSingleValue($this->getFieldValue($ldapData, 'givenname')))
        ->setLastname($this->ensureSingleValue($this->getFieldValue($ldapData, 'sn')))
        ->setLadapLogin($this->ensureSingleValue($this->getFieldValue($ldapData, 'uid')))
        ->setCodeHarpege($this->ensureSingleValue($this->getFieldValue($ldapData, 'supannempid')))
        ->setEmail($this->ensureSingleValue($this->getFieldValue($ldapData, 'mail')))
        ->setPhone($this->ensureSingleValue($this->getFieldValue($ldapData, 'telephonenumber')))
        ->setLdapSiteLocation($this->ensureSingleValue($this->getFieldValue($ldapData, 'buildingname')))
        ->setDateSyncLdap(new \DateTime());

        $object->setLdapMemberOf($this->getFieldValue($ldapData, 'memberof', []));
        $organizationAffectation = null;
        if (null !== $codeAffectation) {
            try {
                /** @var \Oscar\Entity\Organization $organizationAffectation */
                $organizationAffectation = $this->organizationRepository->getOrganisationByCode($codeAffectation);
                if (null !== $organizationAffectation) {
                    $object->setLdapAffectation($organizationAffectation->getShortName());
                }
            } catch (\Exception $e) {
                $object->setLdapAffectation($codeAffectation);
            }
        }
        $rolesEntites = $this->getFieldValue($ldapData, 'supannroleentite', []);
        if (!is_array($rolesEntites)) {
            $rolesEntites = [$rolesEntites];
        }
        $ldapData->roles = [];
        foreach ($rolesEntites as $entity) {
            preg_match('/\[type=([^\]]+)\]/', $entity, $typeMatch);
            preg_match('/\[code=([^\]]+)\]/', $entity, $codeMatch);
            if ($typeMatch && $codeMatch) {
                $code = $codeMatch[1];
                $type = $typeMatch[1];
                if (array_key_exists($type, $this->rolesMapping)) {
                    if (!array_key_exists($code, $ldapData->roles)) {
                        $ldapData->roles[$code] = [];
                    }
                    $ldapData->roles[$code][] = $this->rolesMapping[$type];
                }
            }
        }

        $affectations = $this->getFieldValue($ldapData, 'supannentiteaffectation', []);
        if (!is_array($affectations)) {
            $affectations = [$affectations];
        }
        foreach ($affectations as $affectation) {
            if (!array_key_exists($affectation, $ldapData->roles)) {
                $ldapData->roles[$affectation] = [];
            }
            if(null=== $organizationAffectation){
                try {
                    $organizationAffectation = $this->organizationRepository->getOrganisationByCode($affectation);
                    if (null !== $organizationAffectation) {
                        $object->setLdapAffectation($organizationAffectation->getShortName());
                    }
                } catch (\Exception $e) {
                    echo "Can't create or get Org $affectation for person " . $object->getLadapLogin() . PHP_EOL;
                }
            }
        }


        if ($connectorName !== null) {
            $object->setConnectorID($connectorName, $ldapData->uid);
        }


        return $object;
    }
    private function ensureSingleValue($value)
    {
        if (is_array($value)) {
            return !empty($value) ? $value[0] : null;
        }
        return $value;
    }
}
