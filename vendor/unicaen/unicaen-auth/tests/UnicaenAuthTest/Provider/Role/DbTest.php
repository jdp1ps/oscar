<?php

namespace UnicaenAuthTest\Provider\Role;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Provider\Role\DbRole;
use BjyAuthorize\Provider\Role\ObjectRepositoryProvider;

/**
 * Description of DbTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DbTest extends PHPUnit_Framework_TestCase
{
    protected $provider;
    protected $objectRepository;

    protected function setUp()
    {
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository', []);
        $this->provider         = new DbRole($this->objectRepository);
    }

    public function testGettingRolesReturnsParentClassRoles()
    {
        ObjectRepositoryProvider::$throwException = false;
        $this->assertEquals(['Role 1', 'Role 2'], $this->provider->getRoles());
    }

    public function testException()
    {
        ObjectRepositoryProvider::$throwException = true;
        $this->assertEquals([], $this->provider->getRoles());
    }
}

namespace BjyAuthorize\Provider\Role;

use Doctrine\Common\Persistence\ObjectRepository;

class ObjectRepositoryProvider implements ProviderInterface
{
    public static $throwException = false;

    public function __construct(ObjectRepository $objectRepository)
    {
    }

    public function getRoles()
    {
        if (self::$throwException) {
            throw new \PDOException("No db connection!");
        }
        else {
            return ['Role 1', 'Role 2'];
        }
    }
}