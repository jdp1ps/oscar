<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 07/01/20
 * Time: 17:22
 */

namespace Oscar\Formatter;


use Oscar\Entity\Organization;

class OrganizationToJsonConnectorFormatter
{
    public function format(Organization $organization)
    {
        return array(
            'uid' => (string)$organization->getId(),
            'code' => $organization->getCode(),
            'shortname' => $organization->getShortName(),
            'longname' => $organization->getFullName(),
            'description' => $organization->getDescription(),
            "address" => [
                "address1" => $organization->getStreet1(),
                "address2" => $organization->getStreet2(),
                "address3" => $organization->getStreet3(),
                "zipcode" => $organization->getZipCode(),
                "city" => $organization->getCity(),
                "country" => $organization->getCountry(),
            ],
            'dateupdated' => $organization->getDateUpdated(),
            'phone' => $organization->getPhone(),
            'url' => $organization->getUrl(),
            'email' => $organization->getEmail(),
            'siret' => $organization->getSiret(),
            'type' => $organization->getType(),
        );
    }

}