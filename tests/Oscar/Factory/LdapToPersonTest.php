<?php

namespace Oscar\Factory;

use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use PHPUnit\Framework\TestCase;


class LdapToPersonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $rolesMapping = [
            '{SUPANN}S312' => 'Directeur de laboratoire'
        ];
        $organizationRepository = $this->createMock(OrganizationRepository::class);
        $organizationRepository->expects($this->any())
            ->method('getOrganisationByCode')
            ->will($this->returnCallback(function ($code) {
                if ($code == 'UO2') {
                    throw new \Exception('No organization found');
                }
                $organization = new Organization();
                $organization->setShortName('Laboratoire économique');
                return $organization;
            }));


        $this->factory = new LdapToPerson($rolesMapping, $organizationRepository);
    }


    public function testLaboratory()
    {
        $data = (object)[
            'buildingname' => 'Immeuble de recherche',
            'telephonenumber' => '+33 1 44 07 81 00',
            'dn' => 'uid=bthomas,ou=people,dc=univ-paris1,dc=fr',
            'edupersonaffiliation' =>
                [
                    0 => 'member',
                    1 => 'teacher',
                    2 => 'faculty',
                    3 => 'researcher',
                    4 => 'employee',
                ],
            'edupersonorgunitdn' =>
                [
                    0 => 'ou=U02,ou=structures,o=Paris1,dc=univ-paris1,dc=fr',
                    1 => 'ou=U02C,ou=structures,o=Paris1,dc=univ-paris1,dc=fr',
                ],
            'givenname' => 'Benoit',
            'labeleduri' => 'http://perso.univ-paris1.fr/bthomas',
            'mail' => 'Benoit.Thomas@univ-paris1.fr',
            'modifytimestamp' => '20240511072157Z',
            'postaladdress' => '106 BOULEVARD DE L\'HÔPITAL$75013 PARIS$FRANCE',
            'sn' => 'Thomas',
            'supannaliaslogin' => 'bthomas',
            'supannentiteaffectation' =>
                [
                    0 => 'U02',
                    1 => 'U02C',
                ],
            'supannentiteaffectationprincipale' => 'U02',
            'supannroleentite' => [
                0 => '[role={SUPANN}D30][type={SUPANN}S312][code=U02C]',
                1 => '[role={UAI:0751717J:HARPEGE.FCSTR}532][type={SUPANN}S312][code=U097]',
                2 => '[role={UAI:0751717J}DIR_SITE][type={SIHAM}LDT][code=0307_A]',
            ],
            'uid' => 'bthomas',
        ];
        $object = new Person();

        $result = $this->factory->hydrateWithDatas($object, $data, 'ldap');
        $this->assertEquals('Benoit', $result->getFirstname());
        $this->assertEquals('Thomas', $result->getLastname());
        $this->assertEquals('bthomas', $result->getLadapLogin());
        $this->assertEquals('Immeuble de recherche', $result->getLdapSiteLocation());
        $this->assertEquals('+33 1 44 07 81 00', $result->getPhone());
        $this->assertEquals(['Directeur de laboratoire'], $data->roles['U02C']);


    }

}
