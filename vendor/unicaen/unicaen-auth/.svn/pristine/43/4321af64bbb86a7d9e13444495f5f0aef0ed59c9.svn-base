<?php

namespace UnicaenAuthTest\Authentication;

use UnicaenAppTest\BaseServiceFactoryTest;

/**
 * Description of ModuleOptionsFactoryTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModuleOptionsFactoryTest extends BaseServiceFactoryTest
{
    protected $factoryClass = 'UnicaenAuth\Authentication\AuthenticationServiceFactory';
    protected $serviceClass = 'Zend\Authentication\AuthenticationService';

    public function testCanCreateService()
    {
        $storage = $this->getMock('UnicaenAuth\Authentication\Storage\Chain', []);
        $adapter = $this->getMock('ZfcUser\Authentication\Adapter\AdapterChain', []);

        $this->serviceManager->expects($this->exactly(2))
                ->method('get')
                ->will($this->returnValueMap([
                    ['UnicaenAuth\Authentication\Storage\Chain', $storage],
                    ['ZfcUser\Authentication\Adapter\AdapterChain', $adapter],
                ]));

        $service = $this->factory->createService($this->serviceManager);

        $this->assertInstanceOf($this->serviceClass, $service);
    }
}