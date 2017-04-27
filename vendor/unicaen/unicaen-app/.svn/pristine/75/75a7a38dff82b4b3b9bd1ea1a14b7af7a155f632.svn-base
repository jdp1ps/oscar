<?php
namespace UnicaenAppTest\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Controller\Plugin\LdapPeopleService;

/**
 * Tests du plugin.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see LdapPeopleService
 */
class LdapPeopleServiceTest extends PHPUnit_Framework_TestCase
{
    public function testInvokingPluginReturnsService()
    {
        $service = $this->getMock('UnicaenApp\Service\Ldap\People');
        $plugin = new LdapPeopleService($service);
        $this->assertSame($service, $plugin() /* équivalent à $plugin->__invoke() */ );
    }
}