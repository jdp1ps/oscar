<?php
namespace UnicaenAppTest\Controller\Plugin\MultipageForm;

/**
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class WithoutPrgTest extends PrgTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->plugin->setUsePostRedirectGet(false);
    }
}