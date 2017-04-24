<?php
namespace UnicaenAppTest\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Controller\Plugin\LdapGroupService;

/**
 * Tests du plugin.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see LdapGroupService
 */
class LdapGroupServiceTest extends PHPUnit_Framework_TestCase
{
    public function testInvokingPluginReturnsService()
    {
        $service = $this->getMock('UnicaenApp\Service\Ldap\Group');
        $plugin = new LdapGroupService($service);
        $this->assertSame($service, $plugin() /* équivalent à $plugin->__invoke() */ );
    }
}