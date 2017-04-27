<?php
namespace UnicaenAuthTest\Provider\Identity;

/**
 * Description of LdapTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DbTest extends BaseTest
{
    protected $providerClass = 'UnicaenAuth\Provider\Identity\Db';

    public function provideAuthServiceIdentity()
    {
        $role = new \UnicaenAuth\Entity\Db\Role();
        $role->setRoleId('role id');

        $identity1 = $this->getMock('UnicaenAuth\Entity\Db\User', ['getRoles', 'getUsername']);
        $identity1->expects($this->once())
                  ->method('getRoles')
                  ->will($this->returnValue([$role]));
        $identity1->expects($this->once())
                  ->method('getUsername')
                  ->will($this->returnValue($username = 'username1'));

        $expectedRoles1 = [$role, $username];

        $role2 = new \UnicaenAuth\Entity\Db\Role();
        $role2->setRoleId('role id 2');

        $identity2 = $this->getMock('UnicaenAuth\Entity\Db\User', ['getRoles', 'getUsername']);
        $identity2->expects($this->once())
                  ->method('getRoles')
                  ->will($this->returnValue([$role]));
        $identity2->expects($this->once())
                  ->method('getUsername')
                  ->will($this->returnValue($username = 'username2'));

        $expectedRoles2 = [$role, $username];

        return [
            'object-identity' => [$identity1, $expectedRoles1],
            'array-identity'  => [['db' => $identity2], $expectedRoles2],
        ];
    }

    /**
     * @dataProvider provideAuthServiceIdentity
     */
    public function testGettingIdentityRolesReturnsPeopleRoles($identity, $expectedRoles)
    {
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($identity));

        $roles = $this->provider->getIdentityRoles();

        $this->assertEquals($expectedRoles, $roles);
    }
}