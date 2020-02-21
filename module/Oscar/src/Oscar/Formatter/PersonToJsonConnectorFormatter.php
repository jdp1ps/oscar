<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 07/01/20
 * Time: 17:22
 */

namespace Oscar\Formatter;


use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;

class PersonToJsonConnectorFormatter
{
    public function format(Person $person)
    {
        $roles = [];
        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization){
            $structureKey = (string)$personOrganization->getOrganization()->getCode();
            if( !$structureKey ) continue;
            if( !array_key_exists($structureKey, $roles) ){
                $roles[$structureKey] = [];
            }
            $roleStr = $personOrganization->getRoleObj()->getRoleId();
            $roles[$structureKey][] = $roleStr;
        }

        return array(
            'uid' => (string)$person->getId(),
            'login' => $person->getLadapLogin(),
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'displayname' => $person->getDisplayName(),
            'mail' => $person->getEmail(),
            'civilite' => "",
            'preferedlanguage' => "",
            'status' => "",
            'status' => "",
            'affectation' => $person->getLdapAffectation(),
            'structure' => $person->getLdapSiteLocation(),
            'inm' => "",
            'phone' => $person->getPhone(),

            'birthday' => '',
            'datefininscription' => '',
            "datecreated" => $person->getDateCreatedStr(),
            "dateupdated" => $person->getDateUpdatedStr(),
            "datecached" => $person->getDateCachedStr(),

            'text' => $person->getDisplayName(),
            'phone' => $person->getPhone(),
            'mail' => $person->getEmail(),
            'mailMd5' => md5($person->getEmail()),
            'ucbnSiteLocalisation' => $person->getLdapSiteLocation() ? $person->getLdapSiteLocation() : "",
            'affectation' => $person->getLdapAffectation() ? $person->getLdapAffectation() : "",
            "groups" => $person->getLdapMemberOf(),
            "roles" => $roles
        );
    }

}