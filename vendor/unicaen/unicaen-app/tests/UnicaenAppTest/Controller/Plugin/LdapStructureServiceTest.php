<?php
namespace UnicaenAppTest\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Controller\Plugin\LdapStructureService;

/**
 * Tests du plugin.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see LdapStructureService
 */
class LdapStructureServiceTest extends PHPUnit_Framework_TestCase
{
    public function testInvokingPluginReturnsService()
    {
        $service = $this->getMock('UnicaenApp\Service\Ldap\Structure');
        $plugin = new LdapStructureService($service);
        $this->assertSame($service, $plugin() /* équivalent à $plugin->__invoke() */ );
    }
}