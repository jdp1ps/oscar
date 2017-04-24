<?php
namespace UnicaenAuthTest\Provider\Identity;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Acl\NamedRole;

/**
 * Description of LdapTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected $providerClass;
    protected $provider;
    protected $authService;
    protected $serviceManager;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->serviceManager    = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $this->authService       = $this->getMock('Zend\Authentication\AuthenticationService', ['getIdentity']);
        $this->provider          = new $this->providerClass($this->authService);
    }

    public function getInvalidDefaultRole()
    {
        return [
            [12],
            [new \stdClass()],
            [['value']],
        ];
    }

    public function testGettingIdentityRolesReturnsDefaultRoleWhenEmptyIdentityAvailable()
    {
        // fournisseur de l'identité de l'utilisateur connecté
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue(null));

        $roles = $this->provider->getIdentityRoles();
        $this->assertEquals([$this->provider->getDefaultRole()], $roles);
    }

    public function testGettingIdentityRolesReturnsAuthenticatedRoleWhenUnexpectedIdentityAvailable()
    {
        // fournisseur de l'identité de l'utilisateur connecté
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue(new \DateTime()));

        $roles = $this->provider->getIdentityRoles();
        $this->assertEquals([$this->provider->getAuthenticatedRole()], $roles);
    }

    /**
     * @depends testGettingIdentityRolesReturnsPeopleRoles
     */
    public function testGettingIdentityRolesTrigger()
    {
        $event    = new \UnicaenAuth\Provider\Identity\ChainEvent();
        $roles    = [new NamedRole('role id')];
        $provider = $this->getMock($this->providerClass, ['getIdentityRoles'], [$this->authService]);

        $provider->expects($this->once())
                ->method('getIdentityRoles')
                ->will($this->returnValue($roles));

        $provider->getIdentityRolesTrigger($event);

        $this->assertEquals($roles, $event->getRoles());
    }

//    public function testGettingIdentityRolesReturnsDefaultRoleWhenIdentityLdapGroupDoesNotExistInAcl()
//    {
//        // fournisseur de l'identité de l'utilisateur connecté
//        $this->authService->expects($this->once())
//                          ->method('getIdentity')
//                          ->will($this->returnValue($identity = new LdapPeopleEntity(LdapPeopleTestAsset::$data1)));
//
//        $this->assertNotEmpty($identity->getMemberOf(), "Pré-requis non respecté : memberOf vide.");
//
//        // fournisseur des ACL
//        $this->authorize->expects($this->any())
//                        ->method('getAcl')
//                        ->will($this->returnValue($acl = new \Zend\Permissions\Acl\Acl()));
//
//        $roles = $this->provider->getIdentityRoles();
//        $this->assertEquals(array($this->defaultRole), $roles);
//
//        // NB: le rôle par défaut n'a pas besoin d'être connu des ACL
//        $this->setExpectedException('Zend\Permissions\Acl\Exception\InvalidArgumentException');
//        $acl->getRole($this->defaultRole->getRoleId());
//    }
//
//    public function testGettingIdentityRolesReturnsIdentityLdapGroupsWhichExistInAcl()
//    {
//        // fournisseur de l'identité de l'utilisateur connecté
//        $this->authService->expects($this->once())
//                          ->method('getIdentity')
//                          ->will($this->returnValue($identity = new LdapPeopleEntity(LdapPeopleTestAsset::$data1)));
//
//        $this->assertNotEmpty($identity->getMemberOf(), "Pré-requis non respecté : memberOf vide.");
//
//        // fournisseur des ACL
//        $acl = new \Zend\Permissions\Acl\Acl();
//        $acl->addRole($role = new GenericRole('cn=admin_reseau,ou=groups,dc=unicaen,dc=fr'));
//        $this->authorize->expects($this->any())
//                        ->method('getAcl')
//                        ->will($this->returnValue($acl));
//
//        $roles = $this->provider->getIdentityRoles();
//        $this->assertEquals(array($role), $roles);
//    }
}