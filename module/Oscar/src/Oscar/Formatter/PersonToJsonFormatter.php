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

class PersonToJsonConnectorBasicFormatter
{
    public function format(Person $person)
    {
        return array(
            'uid' => (string)$person->getId(),
            'login' => $person->getLadapLogin(),
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'displayname' => $person->getDisplayName(),
            'mail' => $person->getEmail(),
            'affectation' => $person->getLdapAffectation(),
            'structure' => $person->getLdapSiteLocation(),
            "datecreated" => $person->getDateCreatedStr(),
            "dateupdated" => $person->getDateUpdatedStr(),
            "datecached" => $person->getDateCachedStr(),
        );
    }

}