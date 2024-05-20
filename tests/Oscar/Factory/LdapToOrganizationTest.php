<?php

namespace Oscar\Factory;

use Oscar\Entity\Organization;
use PHPUnit\Framework\TestCase;


class LdapToOrganizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $types = [
            'Composante' => new \Oscar\Entity\OrganizationType(),
            'Groupement d\'intérêt économique' => new \Oscar\Entity\OrganizationType(),
            'Inconnue' => new \Oscar\Entity\OrganizationType(),
            'Institution' => new \Oscar\Entity\OrganizationType(),
            //set label for 'Laboratoiree'
            'Laboratoire' => new \Oscar\Entity\OrganizationType(),
            'Plateau technique' => new \Oscar\Entity\OrganizationType(),
            'Société' => new \Oscar\Entity\OrganizationType(),
            'Établissement publique' => new \Oscar\Entity\OrganizationType()
        ];
        foreach ($types as $key => $type) {
            $type->setLabel($key);
        }
        $typeMappings = [
            0 => [
                'name' => 'Composante',
                'codes' => [
                    0 => '{SUPANN}S311',
                    1 => '{SUPANN}S252',
                    2 => '{SUPANN}S205',
                    3 => '{SUPANN}S204',
                    4 => '{SUPANN}S201',
                    5 => '{SUPANN}S200'
                ]
            ],
            1 => [
                'name' => 'Établissement publique',
                'codes' => [
                    0 => '{SUPANN}S108',
                    1 => '{SUPANN}S107',
                    2 => '{SUPANN}S106',
                    3 => '{SUPANN}S105',
                    4 => '{SUPANN}S104',
                    5 => '{SUPANN}S103',
                    6 => '{SUPANN}S102',
                    7 => '{SUPANN}S101'
                ]
            ],
            2 => [
                'name' => 'Laboratoire',
                'codes' => [
                    0 => '{SUPANN}S312',
                    1 => '{SUPANN}S310',
                    2 => '{SUPANN}S203'
                ]
            ],
        ];

        $this->factory = new LdapToOrganization($types, $typeMappings);
    }


    public function testLaboratory()
    {
        $data = (object)[
            'businesscategory' => 'research',
            'description' => 'LLDR: Le Laboratoire De Recehrche (UMR 1234)',
            'dn' => 'supannCodeEntite=U012,ou=structures,dc=univ-paris1,dc=fr',
            'labeleduri' => 'https://www.lldr.cnrs.fr/',
            'modifytimestamp' => '20230512172902Z',
            'ou' => 'UMR 1234 - LLDR',
            'postaladdress' => 'Centre Truc$1 PLACE ARISTIDE BRIAND$75000 Paris$France',
            'supanncodeentite' => 'U012',
            'supanntypeentite' => '{SUPANN}S203',
            'telephonenumber' => '+33 1 23 45 67 89',
            'uid' => 'U012',
            'supannrefid' => [
                0 => '{CNRS}UMR2345',
                1 => '{SIRET}123456789',
                2 => '{RNSR}199812919F',
            ]
        ];
        $object = new Organization();

        $result = $this->factory->hydrateWithDatas($object, $data, 'ldap');

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('U012', $result->getConnectorID('ldap'));
        $this->assertEquals('U012', $result->getCode());
        $this->assertEquals('UMR 1234 - LLDR', $result->getShortName());
        $this->assertEquals('LLDR: Le Laboratoire De Recehrche (UMR 1234)', $result->getFullName());
        $this->assertEquals('https://www.lldr.cnrs.fr/', $result->getUrl());
        $this->assertEquals('2023-05-12T17:29:02+00:00', $result->getDateUpdatedStr());
        $this->assertEquals('Centre Truc', $result->getStreet1());
        $this->assertEquals('1 PLACE ARISTIDE BRIAND', $result->getStreet2());
        $this->assertEquals('75000', $result->getZipCode());
        $this->assertEquals('Paris', $result->getCity());
        $this->assertEquals('France', $result->getCountry());
        $this->assertEquals('Laboratoire', $result->getType());
        $this->assertEquals('+33 1 23 45 67 89', $result->getPhone());
        $this->assertEquals('UMR2345', $result->getLabintel());
        $this->assertEquals('123456789', $result->getSiret());
        $this->assertEquals('199812919F', $result->getRnsr());

    }

    public function testLaboratoryWithoutSupanncodeentite()
    {
        $data = (object)[
            'businesscategory' => 'research',
            'description' => 'LLDR: Le Laboratoire De Recehrche (UMR 1234)',
            'dn' => 'supannCodeEntite=U012,ou=structures,dc=univ-paris1,dc=fr',
            'labeleduri' => 'https://www.lldr.cnrs.fr/',
            'modifytimestamp' => '20230512172902Z',
            'ou' => 'UMR 1234 - LLDR',
            'postaladdress' => 'Centre Truc$1 PLACE ARISTIDE BRIAND$75000 Paris$France',
            'supanntypeentite' => '{SUPANN}S203',
            'telephonenumber' => '+33 1 23 45 67 89',
            'uid' => 'U012',
            'supannrefid' => [
                0 => '{CNRS}UMR2345',
                1 => '{SIRET}123456789',
                2 => '{RNSR}199812919F',
            ]
        ];
        $object = new Organization();

        $this->expectException(\Oscar\Exception\OscarException::class);
        $this->expectExceptionMessage("La clef 'supanncodeentite' est manquante dans la source");
        $result = $this->factory->hydrateWithDatas($object, $data, 'ldap');
    }
}
