<?php
namespace UnicaenAuthTest\Provider\Role;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Provider\Role\Config;
use UnicaenAuth\Acl\NamedRole;
use UnicaenApp\Entity\Ldap\Group;

/**
 * Description of ConfigTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = $this->getMock('UnicaenApp\Mapper\Ldap\Group', ['findOneByDn']);
    }

    public function testLoadingRolesWithoutLdapDnCreatesNamedRoles()
    {
        $options = [
            'user' => [
                'name' => "Profil standard",
                'children' => [
                    'Gestionnaire', // pas de nom pour celui-là
                    'admin' => [
                        'name' => "Administrateur",
                    ],
                ],
            ],
        ];

        $this->mapper
                ->expects($this->never())
                ->method('findOneByDn');

        $config = new Config($this->mapper, $options);

        $roles = $config->getRoles();

        $expected = [
            $guest = new NamedRole('user', null, "Profil standard"),
            new NamedRole('Gestionnaire', $guest, "Gestionnaire"), // le nom attribué par défaut est l'id
            new NamedRole('admin', $guest, "Administrateur"),
        ];
        $this->assertEquals($expected, $roles);
    }

    public function testLoadingRolesWithExistingLdapDnCreatesNamedRoles()
    {
        $options = [
            'user' => [
                'name' => "Profil standard",
                'children' => [
                    'cn=support_info,ou=groups,dc=unicaen,dc=fr', // pas de nom pour celui-là
                    'cn=dsi-infra,ou=groups,dc=unicaen,dc=fr' => [
                        'name' => "DSI Infrastructure",
                    ],
                ],
            ],
        ];

        $map = [
            [$dn = 'cn=support_info,ou=groups,dc=unicaen,dc=fr', new Group(['dn' => $dn, 'description' => "DSI Support"])],
        ];
        $this->mapper
                ->expects($this->exactly(1))
                ->method('findOneByDn')
                ->will($this->returnValueMap($map));

        $config = new Config($this->mapper, $options);

        $roles = $config->getRoles();

        $expected = [
            $guest = new NamedRole(
                    'user',
                    null,
                    "Profil standard"),
            new NamedRole(
                    'cn=support_info,ou=groups,dc=unicaen,dc=fr',
                    $guest,
                    "DSI Support"), // le nom attribué est la description LDAP
            new NamedRole(
                    'cn=dsi-infra,ou=groups,dc=unicaen,dc=fr',
                    $guest,
                    "DSI Infrastructure"),
        ];
        $this->assertEquals($expected, $roles);
    }

    public function testLoadingRolesWithUnexistingLdapDnCreatesNamedRoles()
    {
        $options = [
            'user' => [
                'name' => "Profil standard",
                'children' => [
                    'cn=unknown,ou=groups,dc=unicaen,dc=fr', // groupe introuvable
                ],
            ],
        ];

        $map = [
            ['cn=unknown,ou=groups,dc=unicaen,dc=fr', null],
        ];
        $this->mapper
                ->expects($this->exactly(1))
                ->method('findOneByDn')
                ->will($this->returnValueMap($map));

        $config = new Config($this->mapper, $options);

        $roles = $config->getRoles();

        $expected = [
            $guest = new NamedRole(
                    'user',
                    null,
                    "Profil standard"),
            new NamedRole(
                    'cn=unknown,ou=groups,dc=unicaen,dc=fr',
                    $guest,
                    'cn=unknown,ou=groups,dc=unicaen,dc=fr'), // le nom attribué est l'id
        ];
        $this->assertEquals($expected, $roles);
    }
}