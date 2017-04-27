<?php

namespace UnicaenLdapFuncTest\Service;

use PHPUnit_Framework_TestCase;
use UnicaenLdap\Entity\People as PeopleEntity;
use UnicaenLdap\Filter\People as PeopleFilter;
use UnicaenLdapFuncTest\Bootstrap;

/**
 * @group Service
 */
class ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \UnicaenLdap\Service\Service
     */
    protected $service;


    protected function setUp()
    {
        $this->service = Bootstrap::getServiceManager()->get('ldapServicePeople');
    }

    public function testCanRetrieveServiceFromManager()
    {
        $service = Bootstrap::getServiceManager()->get('ldapServicePeople');

        $this->assertInstanceOf('UnicaenLdap\Service\Service', $service);
        $this->assertEquals('dc=unicaen,dc=fr', $service->getLdap()->getBaseDn());
    }

    public function testCanFindPeopleByNoIndividu()
    {
        $filter = PeopleFilter::noIndividu("21237");
        $result = $this->service->search($filter);

        $this->assertPeopleFindResult($result);
    }

    public function testCanFindPeopleByUsername()
    {
        $filter = PeopleFilter::username("gauthierb");
        $result = $this->service->search($filter);

        $this->assertPeopleFindResult($result);
    }

    private function assertPeopleFindResult($result)
    {
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf("UnicaenLdap\Entity\People", $result);

        $result->rewind();
        $people = $result->current(); /** @var PeopleEntity $people */
        $this->assertEquals($people->get('cn'), 'Gauthier Bertrand');
    }
}