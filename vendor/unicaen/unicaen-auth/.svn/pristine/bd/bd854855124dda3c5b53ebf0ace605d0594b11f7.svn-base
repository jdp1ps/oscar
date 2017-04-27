<?php
namespace UnicaenAuthTest\Options;

use PHPUnit_Framework_TestCase;
use UnicaenAuth\Options\ModuleOptions;

/**
 * Description of ModuleOptionsTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->options = new ModuleOptions();
    }

    public function testSuperClass()
    {
        $this->assertInstanceOf('ZfcUser\Options\ModuleOptions', $this->options);
    }

    public function testOptionsDefaultValues()
    {
        $options = new ModuleOptions();

        $this->assertInternalType('array', $array = $options->getUsurpationAllowedUsernames());
        $this->assertEmpty($array);

        $this->assertInternalType('array', $array = $options->getCas());
        $this->assertEmpty($array);

        $this->assertFalse($this->options->getSaveLdapUserInDatabase());
    }

    public function testConstructorSetsOptions()
    {
        $options = [
            'usurpation_allowed_usernames' => ['bob'],
            'save_ldap_user_in_database'  => true,
            'cas'                         => ['Cas config'],
        ];
        $moduleOptions = new ModuleOptions($options);

        $this->assertEquals(array_shift($options), $moduleOptions->getUsurpationAllowedUsernames());
        $this->assertEquals(array_shift($options), $moduleOptions->getSaveLdapUserInDatabase());
        $this->assertEquals(array_shift($options), $moduleOptions->getCas());
    }
}