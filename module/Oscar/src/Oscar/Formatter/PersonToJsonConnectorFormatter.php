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
            $structureKey = (string)$personOrganization->getOrganization()->getId();
            $roleStr = $personOrganization->getRoleObj()->getRoleId();
            if( !array_key_exists($structureKey, $roles) ){
                $roles[$structureKey] = [];
            }
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
            "datecreated" => "YYYY-MM-DD",
            "dateupdated" => "YYYY-MM-DD",
            "datecached" => "YYYY-MM-DD",

            'text' => $person->getDisplayName(),
            'phone' => $person->getPhone(),
            'mail' => $person->getEmail(),
            'mailMd5' => md5($person->getEmail()),
            'ucbnSiteLocalisation' => $person->getLdapSiteLocation() ? $person->getLdapSiteLocation() : "",
            'affectation' => $person->getLdapAffectation() ? $person->getLdapAffectation() : "",

            "address" => [
                "address1" => "",
                "address2" => "",
                "address3" => "",
                "zipcode" => "",
                "city" => "",
                "country" => ""
            ],
            "groups" => $person->getLdapMemberOf(),
            "roles" => $roles
        );
    }

}