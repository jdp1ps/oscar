<?php
namespace UnicaenAppTest\Mapper\Ldap;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class CommonFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * @var FactoryInterface
     */
    protected $factory;
    
    /**
     * @var array
     */
    protected $options = array(
        'ldap' => array(
            'connection' => array(
                'default' => array(
                    'params' => array(
                        'host'                => 'ldap.unicaen.fr',
                        'username'            => "uid=xxxxxxxx,ou=system,dc=unicaen,dc=fr",
                        'password'            => "xxxxxxxxxx",
                        'baseDn'              => "ou=people,dc=unicaen,dc=fr",
                        'bindRequiresDn'      => true,
                        'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
                    )
                )
            )
        )
    );
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', array('get'));
        $this->serviceManager->expects($this->any())
                             ->method('get')
                             ->with('unicaen-app_module_options')
                             ->will($this->returnValue(new ModuleOptions($this->options)));
    }
}