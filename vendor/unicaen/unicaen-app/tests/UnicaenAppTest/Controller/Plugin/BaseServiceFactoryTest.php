<?php

namespace UnicaenAppTest\Controller\Plugin;

/**
 * Description of BaseLdapServiceFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class BaseServiceFactoryTest extends \UnicaenAppTest\BaseServiceFactoryTest
{
    protected $pluginManager;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->pluginManager  = $this->getMock('\Zend\Mvc\Controller\PluginManager', array('getServiceLocator'));
    }
}