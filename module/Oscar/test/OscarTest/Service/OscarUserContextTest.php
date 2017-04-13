<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 03/03/16
 * Time: 09:27
 */

namespace module\Oscar\test\OscarTest\Service;


use Oscar\Service\OscarUserContext;
use OscarTest\Bootstrap;
use UnicaenAuth\Service\UserContext;

class OscarUserContextTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    /** @var  OscarUserContext */
    protected $oscarUserContext;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->oscarUserContext = new OscarUserContext();
        $this->oscarUserContext->setServiceLocator($this->sm);
    }
    public function testHasRole(){

        $this->assertNotNull($this->oscarUserContext, 'oscarUserContext est disponible');
        echo $this->oscarUserContext->getBaseRoleId();
       $this->assertTrue(true);
    }
}