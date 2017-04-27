<?php
namespace UnicaenAppTest\Controller\Plugin\MultipageForm;

use UnicaenApp\Form\Element\MultipageFormNav;

/**
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class WithPrgTest extends PrgTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->plugin->setUsePostRedirectGet(true);
    }

    protected function dispatchPostRequestOnStep($fieldsetName, $validPostData = true, $submitName = MultipageFormNav::NEXT)
    {
        // comme le plugin PostRedirectGet est activé
        parent::dispatchPostRequestOnStep($fieldsetName, $validPostData, $submitName);
        $this->plugin->process();
        $this->dispatchGetRequestOnStep($fieldsetName);
    }

    protected function dispatchPostRequestOnAction($actionName, $submitName = MultipageFormNav::CONFIRM)
    {
        // comme le plugin PostRedirectGet est activé
        parent::dispatchPostRequestOnAction($actionName, $submitName);
        $this->plugin->process();
        $this->dispatchGetRequestOnAction($actionName);
    }
}