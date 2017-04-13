<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 11:18
 * @copyright Certic (c) 2015
 */
namespace OscarTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class PublicControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include realpath(__DIR__.'/../../../../../config/application.config.php')
        );
        parent::setUp();
    }

    /**
     * @runInSeparateProcess
     */
    public function testIndexActionCanBeAccessed()
    {
        header('Location : http://foo.com');
        //$this->dispatch('/');
        //$this->assertResponseStatusCode(200);
        //$this->assertXpathQuery('/html');
        **/
    }
}
