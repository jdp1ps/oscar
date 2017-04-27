<?php
namespace UnicaenAuthTest\Provider\Identity;

/**
 * Description of LdapTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class LdapTest extends BaseTest
{
    protected $providerClass = 'UnicaenAuth\Provider\Identity\Ldap';
    protected $mapper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Db', ['findOneByDn']);
    }

    public function provideAuthServiceIdentity()
    {
        $identity1 = $this->getMock('UnicaenAuth\Entity\Ldap\People', ['getRoles', 'getUsername']);
        $identity1->expects($this->once())
                  ->method('getRoles')
                  ->will($this->returnValue(['cn=admin_reseau,ou=groups,dc=unicaen,dc=fr']));
        $identity1->expects($this->once())
                  ->method('getUsername')
                  ->will($this->returnValue('username1'));

        $expectedRoles1 = ['cn=admin_reseau,ou=groups,dc=unicaen,dc=fr', 'username1'];

        $identity2 = $this->getMock('UnicaenAuth\Entity\Ldap\People', ['getRoles', 'getUsername']);
        $identity2->expects($this->once())
                  ->method('getRoles')
                  ->will($this->returnValue(['cn=admin_reseau,ou=groups,dc=unicaen,dc=fr']));
        $identity2->expects($this->once())
                  ->method('getUsername')
                  ->will($this->returnValue('username2'));

        $expectedRoles2 = ['cn=admin_reseau,ou=groups,dc=unicaen,dc=fr', 'username2'];

        return [
            'object-identity' => [$identity1, $expectedRoles1],
            'array-identity'  => [['ldap' => $identity2], $expectedRoles2],
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