<?php
namespace UnicaenAppTest\Controller\Plugin\MultipageForm;

use UnicaenApp\Form\Element\MultipageFormNav;
use UnicaenAppTest\Form\TestAsset\ContactMultipageForm;
use Zend\Http\Request;

/**
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
abstract class PrgTest extends AbstractTest
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

    public function testDispatchingNextPostRequestForNextStepWithInvalidPostDataReturnsSameStepResponseWithFormErrors()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, false);
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 1);
        $this->assertNotEmpty($result['fieldset']->getMessages());
    }

    public function testDispatchingNextPostRequestForNextStepWithValidPostDataReturnsRedirectionToNextStep()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $stepIndex => $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $result = $this->plugin->process();
            $this->assertIsRedirectResponse($result, $stepIndex <= 2 ? $stepIndex + 1 : $stepIndex);
        }
    }

    public function getPostDataValidityFlag()
    {
        return array(
            array(false),
            array(true)
        );
    }

    /**
     * @dataProvider getPostDataValidityFlag
     */
    public function testDispatchingPreviousPostRequestOnOneStepReturnsRedirectionToPreviousStep($validPostData)
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep($name          = ContactMultipageForm::FIELDSET_1_NAME, true);
        $result        = $this->plugin->process();
        $fieldsetValue = $this->getForm()->get($name)->getValue();

        // Go back : POST previous action on step 2 with invalid post data
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, $validPostData, MultipageFormNav::PREVIOUS);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponse($result, 1);

        // GET step 1
        $this->dispatchGetRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME);
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 1);
        $this->assertEquals($fieldsetValue, $result['fieldset']->getValue());
    }

    public function testDispatchingGetAndNextPostRequestForEachStep()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $stepIndex => $fieldsetName) {
            // GET
            $this->dispatchGetRequestOnStep($fieldsetName);
            $result = $this->plugin->process();
            $this->assertIsArrayResponse($result, $stepIndex);
            $this->assertEmpty($result['fieldset']->getMessages());
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $result = $this->plugin->process();
            $this->assertIsRedirectResponse($result, $stepIndex < 3 ? $stepIndex + 1 : $stepIndex);
        }
    }

    public function testDispatchingPreviousPostRequestForEachStep()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $stepIndex => $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $result = $this->plugin->process();
        }

        foreach (array_reverse(self::$stepFieldsets, true) as $stepIndex => $fieldsetName) {
            // POST previous action
            $this->dispatchPostRequestOnStep($fieldsetName, true, MultipageFormNav::PREVIOUS);
            $result = $this->plugin->process();
            $this->assertIsRedirectResponse($result, $stepIndex > 1 ? $stepIndex - 1 : $stepIndex);
        }
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testCancelingReturnsRedirectionToFirstStepIfNoCancelActionSet()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();

        // cancel
        $result = $this->plugin->setCancelAction(null)->cancel();
        $this->assertIsRedirectResponse($result, 1);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testCancelingReturnsRedirectionToCancelAction()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();

        // cancel
        $result = $this->plugin->cancel();
        $this->assertIsRedirectResponseToCancelAction($result);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingCancelPostRequestReturnsRedirectionToCancelAction()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();
        
        // POST cancel action
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, true, MultipageFormNav::CANCEL);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponseToCancelAction($result);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingUnknownActionPostRequestReturnsCurrentStepResponse()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();
        
        // POST unknown action with invalid post data
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, false, '_unknown');
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 2);
        
        // POST unknown action with valid post data
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, true, '_unknown');
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 2);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingGetRequestForConfirmingToSoonReturnsRedirectionToCurrentStep()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();

        // GET confirmation
        $this->request = new Request();
        $this->routeMatch->setParam('action', $this->getForm()->getConfirmAction());
        $this->controller->dispatch($this->request, $this->response);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponse($result, 2);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingSubmitPostRequestReturnsConfirmationResponse()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();
        
        // POST submit action on step 2 with invalid post data
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, false, MultipageFormNav::SUBMIT);
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 2);
        
        // POST submit action on step 2 with valid post data
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, true, MultipageFormNav::SUBMIT);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponseToConfirmAction($result);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingSubmitPostRequestOnLastStepReturnsConfirmationResponse()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();

        // POST next action on step 2
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_2_NAME, true);
        $this->plugin->process();

        // POST submit action on step 3
        $this->dispatchPostRequestOnStep(ContactMultipageForm::FIELDSET_3_NAME, true, MultipageFormNav::SUBMIT);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponseToConfirmAction($result);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingGetRequestForConfirmingAfterAllStepsCompletedReturnsConfirmationResponse()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $this->plugin->process();
        }

        // GET confirmation
        $this->request = new Request();
        $this->routeMatch->setParam('action', $this->getForm()->getConfirmAction());
        $this->controller->dispatch($this->request, $this->response);
        $result = $this->plugin->process();
        $this->assertIsArrayResponse($result, 3, true);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingConfirmPostRequestOnConfirmationStepReturnsFinalActionResponse()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $this->plugin->process();
        }

        // POST confirm action
        $this->dispatchPostRequestOnAction('ajouter-confirmer', MultipageFormNav::CONFIRM);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponseToFinalAction($result);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingPreviousPostRequestOnConfirmationStepReturnsRedirectionToLastStep()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $this->plugin->process();
        }

        // POST previous action
        $this->dispatchPostRequestOnAction('ajouter-confirmer', MultipageFormNav::PREVIOUS);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponse($result, 3);
    }

    /**
     * @depends testDispatchingGetAndNextPostRequestForEachStep
     */
    public function testDispatchingCancelPostRequestOnConfirmationStepReturnsRedirectionToLastStep()
    {
        $this->plugin->setForm($this->getForm());

        foreach (self::$stepFieldsets as $fieldsetName) {
            // POST next action
            $this->dispatchPostRequestOnStep($fieldsetName, true);
            $this->plugin->process();
        }

        // POST cancel action
        $this->dispatchPostRequestOnAction('ajouter-confirmer', MultipageFormNav::CANCEL);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponseToCancelAction($result);
    }
}