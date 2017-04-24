<?php
namespace UnicaenAppTest\Controller\Plugin\MultipageForm;

use UnicaenAppTest\Form\TestAsset\ContactMultipageForm;
use Zend\Http\Request;

/**
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class CommonTest extends AbstractTest
{
    /**
     * @expectedException \UnicaenApp\Controller\Plugin\Exception\IllegalStateException
     */
    public function testSpecifyingFormWithoutAnyFieldsetThrowsException()
    {
        $form = new ContactMultipageForm();
        // NB: $form->prepareElements() n'étant pas appelé, le formulaire ne possède aucun fieldset
        $this->plugin->setForm($form);
    }

    /**
     * @expectedException \UnicaenApp\Controller\Plugin\Exception\IllegalStateException
     */
    public function testSpecifyingFormHavingOneFieldsetWithoutANameThrowsException()
    {
        $form = $this->getForm();
        $form->get('adresse')->setName('');
        $this->plugin->setForm($form);
    }

    public function testInvokingReturnsPluginItself()
    {
        $form   = $this->getForm();
        $plugin = $this->plugin->setForm($form);
        $result = $plugin();
        $this->assertSame($plugin, $result);
    }

    public function testInvokingWithFormSetsForm()
    {
        $form   = $this->getForm();
        $plugin = $this->plugin;
        $plugin($form);
        $this->assertSame($form, $plugin->getForm());
    }

    public function testCanSetSessionContainer()
    {
        $this->plugin->setSessionContainer($container = new \Zend\Session\Container());
        $this->assertSame($container, $this->plugin->getSessionContainer());
    }

    public function testInvokingInitializesPluginAttributes()
    {
        $form   = $this->getForm();
        $plugin = $this->plugin->setForm($form);

        $plugin();

        $expected = array(
            ContactMultipageForm::FIELDSET_1_NAME => $form->getActionPrefix() . ContactMultipageForm::FIELDSET_1_NAME,
            ContactMultipageForm::FIELDSET_2_NAME => $form->getActionPrefix() . ContactMultipageForm::FIELDSET_2_NAME,
            ContactMultipageForm::FIELDSET_3_NAME => $form->getActionPrefix() . ContactMultipageForm::FIELDSET_3_NAME,
        );
        $this->assertEquals($expected, $this->readAttribute($plugin, 'fieldsetActions'));
        $this->assertEquals(array_values(self::$stepFieldsets), $this->readAttribute($plugin, 'fieldsetOrder'));

        $this->assertEquals($this->getForm()->getConfirmAction(), $plugin->getConfirmAction());
        $this->assertEquals($this->getForm()->getProcessAction(), $plugin->getProcessAction());
        $this->assertEquals($this->getForm()->getCancelAction(), $plugin->getCancelAction());

        $this->assertSame($form, $this->plugin->getForm());
        $this->assertSame($this->controller, $this->plugin->getController());
    }

    public function testClearingSetsFirstFieldsetAsCurrentAndClearsSessionData()
    {
        $this->plugin->setForm($this->getForm());

        $data = $this->plugin->clear();
        $this->assertEquals($this->plugin->getCurrentFieldset(), $this->getForm()->get(ContactMultipageForm::FIELDSET_1_NAME));
        $data = $this->plugin->getFormSessionData();
        $expected = array(
            ContactMultipageForm::FIELDSET_1_NAME => array(),
            ContactMultipageForm::FIELDSET_2_NAME => array(),
            ContactMultipageForm::FIELDSET_3_NAME => array(),
        );
        $this->assertEquals($expected, $data);
    }

    /**
     * @expectedException \UnicaenApp\Controller\Plugin\Exception\IllegalStateException
     */
    public function testStartingThrowsExceptionIfNoFormSet()
    {
        $this->plugin->start();
    }

    public function testStartingReturnsRedirectionToFirstStep()
    {
        $this->plugin->setForm($this->getForm());

        $result = $this->plugin->start();
        $this->assertIsRedirectResponse($result, 1);
    }

    /**
     * @expectedException \UnicaenApp\Controller\Plugin\Exception\IllegalStateException
     */
    public function testProcessingThrowsExceptionIfNoFormSet()
    {
        $this->plugin->process();
    }
    
    
    
    public function getStepActions()
    {
        return array(
            'step1' => array('ajouter-identite', 'assertIsArrayResponse'),
            'step2' => array('ajouter-adresse',  'assertIsRedirectResponse'),
            'step3' => array('ajouter-message',  'assertIsRedirectResponse'),
        );
    }

    /**
     * @dataProvider getStepActions
     */
    public function testDispatchingGetRequestForAnyStepActionReturnsFirstStepResponse($action, $assertion)
    {
        $this->plugin->setForm($this->getForm());

        $this->routeMatch->setParam('action', $action);
        $this->controller->dispatch($this->request, $this->response);
        $result = $this->plugin->process();
        $this->$assertion($result, 1);
    }

    public function testDispatchingGetRequestForPreviouslySubmittedStepReturnsCurrentStepResponse()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep($name   = ContactMultipageForm::FIELDSET_1_NAME, true);
        $result = $this->plugin->process();

        // GET step 1
        $this->dispatchGetRequestOnStep(ContactMultipageForm::FIELDSET_1_NAME);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponse($result, 2);
    }

    public function testDispatchingGetRequestForUnexistingStepReturnsRedirectionToFirstStep()
    {
        $this->plugin->setForm($this->getForm());

        // POST next action on step 1
        $this->dispatchPostRequestOnStep($name = ContactMultipageForm::FIELDSET_1_NAME, true);
        $this->plugin->process();

        // POST next action on step 2
        $this->dispatchPostRequestOnStep($name = ContactMultipageForm::FIELDSET_2_NAME, true);
        $this->plugin->process();

        $this->request = new Request();
        $this->routeMatch->setParam('action', 'unexisting-action');
        $this->controller->dispatch($this->request, $this->response);
        $result = $this->plugin->process();
        $this->assertIsRedirectResponse($result, 1);
    }
}