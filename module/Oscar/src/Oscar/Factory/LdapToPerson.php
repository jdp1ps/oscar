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
        $codeAffectation = $this->getFieldValue($ldapData, 'supannentiteaffectationprincipale');
        $object->setFirstname($this->getFieldValue($ldapData, 'givenname'))
            ->setLastname($this->getFieldValue($ldapData, 'sn'))
            ->setLadapLogin($this->getFieldValue($ldapData, 'supannaliaslogin'))
            ->setCodeHarpege($codeAffectation)
            ->setEmail($this->getFieldValue($ldapData, 'mail'))
            ->setLdapSiteLocation($this->getFieldValue($ldapData, 'buildingname'))
            ->setDateSyncLdap(new \DateTime());

        $object->setLdapMemberOf($this->getFieldValue($ldapData, 'edupersonorgunitdn', []));
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
        if (property_exists($ldapData, 'telephonenumber')) {
            $object->setPhone($ldapData->telephonenumber);
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
                    echo "Can't create or get Org $affectation \n";
                }
            }
        }


        if ($connectorName !== null) {
            $object->setConnectorID($connectorName, $ldapData->uid);
        }


        return $object;
    }
}
