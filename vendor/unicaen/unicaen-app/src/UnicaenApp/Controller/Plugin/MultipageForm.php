<?php
namespace UnicaenApp\Controller\Plugin;

use UnicaenApp\Controller\Plugin\Exception\IllegalStateException;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Http\Response;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Session\Container;

/**
 * Plugin de contrôleur facilitant la mise en oeuvre d'un formulaire multi-pages.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipageForm extends AbstractPlugin
{
    /**
     * Navigation action constants
     */
    const ACTION_PREFIX   = 'prefix';
    const ACTION_NEXT     = 'next';
    const ACTION_PREVIOUS = 'previous';
    const ACTION_SUBMIT   = 'submit';
    const ACTION_CANCEL   = 'cancel';
    const ACTION_CONFIRM  = 'confirm';

    const NAV      = \UnicaenApp\Form\Element\MultipageFormNav::NAME;
    const PREFIX   = \UnicaenApp\Form\Element\MultipageFormNav::PREFIX;
    const NEXT     = \UnicaenApp\Form\Element\MultipageFormNav::NEXT;
    const PREVIOUS = \UnicaenApp\Form\Element\MultipageFormNav::PREVIOUS;
    const SUBMIT   = \UnicaenApp\Form\Element\MultipageFormNav::SUBMIT;
    const CANCEL   = \UnicaenApp\Form\Element\MultipageFormNav::CANCEL;
    const CONFIRM  = \UnicaenApp\Form\Element\MultipageFormNav::CONFIRM;

    /**
     * Navigation element names
     *
     * @var array
     */
    protected $navigationElements = array(
        self::ACTION_PREFIX   => self::PREFIX,
        self::ACTION_NEXT     => self::NEXT,
        self::ACTION_PREVIOUS => self::PREVIOUS,
        self::ACTION_SUBMIT   => self::SUBMIT,
        self::ACTION_CANCEL   => self::CANCEL,
        self::ACTION_CONFIRM  => self::CONFIRM);

    /**
     * Session storage object
     *
     * @var Container
     */
    protected $sessionContainer;

    /**
     * The complete form instance
     *
     * @var \UnicaenApp\Form\MultipageForm
     */
    protected $form;

    /**
     * The current fieldset instance
     *
     * @var Form
     */
    protected $currentFieldset;

    /**
     * The mapping of fieldset name to controller action
     *
     * @var array
     */
    protected $fieldsetActions = array();

    /**
     * The order in which the fieldsets appear
     *
     * @var array
     */
    protected $fieldsetOrder = array();

    /**
     * The action that will be used for confirming the whole form values
     *
     * @var string
     */
    protected $confirmAction;

    /**
     * The action that will be used for processing the form
     *
     * @var string
     */
    protected $processAction;

    /**
     * The action for canceling the form
     *
     * @var string
     */
    protected $cancelAction;

    /**
     * Current route match
     *
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Post Redirect Get pattern activation
     *
     * @var bool 
     */
    protected $usePostRedirectGet = false;
    
    /**
     * Response
     *
     * @var array|bool|Response
     */
    protected $prgResult;

    /**
     * The __invoke() method is called when a script tries to call an object as a function.
     * 
     * @param \UnicaenApp\Form\MultipageForm $form Formulaire multi-pages concerné
     * @return self
     */
    public function __invoke(\UnicaenApp\Form\MultipageForm $form = null)
    {
        if (null !== $form) {
            $this->setForm($form);
        }
        return $this;
    }
    
    /**
     * Return response to start process.
     * 
     * @return Response
     */
    public function start()
    {
        if (null === $this->getForm()) {
            throw new IllegalStateException('No form instance set.');
        }
        
        $this->clear();
        
        $fieldset = $this->fieldsetOrder[0];
        $target = $this->fieldsetActions[$fieldset];
        
        return $this->redirect($target);
    }

    /**
     * Point d'entrée.
     * 
     * @return array|Response
     * @throws IllegalStateException
     */
    public function process()
    {
        if (null === $this->getForm()) {
            throw new IllegalStateException('No form instance set.');
        }
        
        $routeMatch = $this->getController()->getEvent()->getRouteMatch();
        $action = $routeMatch->getParam('action');
        
        $activeFieldsetName = $this->getActiveFieldsetName();
        
        if ($this->isFieldsetAction($action) && $this->isFieldset($activeFieldsetName)) {
            // Si le fieldset demandé (d'après l'action de la requête) n'est pas le dernier fieldset actif,
            // on redirige vers l'action associée à ce dernier
            $actionFieldsetName = array_search($action, $this->fieldsetActions);
            if ($activeFieldsetName !== $actionFieldsetName) {
                $response = $this->redirect($this->fieldsetActions[$activeFieldsetName]);
            } 
            // Sinon, on continue
            else {
                $response = $this->handle();
            }
        }
        elseif ($action === $this->getConfirmAction()) {
            if (!$this->isValidForm()) {
                // Redirect to the latest valid action
                $response = $this->redirect($this->getLastValidAction());
            } else {
                // Handle the confirmation step
                $response = $this->handleConfirm();
            }
        }
        else {
            $response = $this->start();
        }
        
        return $response;
    }
    
    /**
     * Handle the form
     *
     * @return array|Response
     */
    protected function handle()
    {
        // post redirect get pattern if required
        if ($this->getUsePostRedirectGet()) {
            $prg = $this->getController()->plugin('prg'); /* @var $prg \Zend\Mvc\Controller\Plugin\PostRedirectGet */
            $this->prgResult = $prg();
            if ($this->prgResult instanceof Response) {
                // returned a response to redirect us
                return $this->prgResult;
            }
        }
        
        $submitAction = $this->getSubmitAction();
        $currentFieldset = $this->getCurrentFieldset();
        
        if ($submitAction === false) {
            return array(
                'fieldset'  => $currentFieldset, 
                'stepIndex' => $this->getCurrentStepIndex(),
                'stepCount' => $this->getStepCount());
        }

        $postData = $this->getPostData();
        $currentFieldset->setValue($postData)
                        ->populateValues($postData);
        
        $valid = $this->isValidCurrentFieldset();
        $this->setValues($currentFieldset, $valid);
        
        $this->getSessionContainer()->action = $submitAction;
        
        switch ($submitAction) {
            
            // previous
            case $this->navigationElements[self::ACTION_PREVIOUS]:
                $position = array_search($currentFieldset->getName(), $this->fieldsetOrder);

                if ($position <= 0) {
                    $fieldset = $this->fieldsetOrder[0];
                } else {
                    $fieldset = $this->fieldsetOrder[$position - 1];
                }

                $this->setActiveFieldsetName($fieldset);

                $target = $this->fieldsetActions[$fieldset];
                break;

            // submit
            case $this->navigationElements[self::ACTION_SUBMIT]:
                if (!$valid) {
                    return array(
                        'fieldset'  => $currentFieldset, 
                        'stepIndex' => $this->getCurrentStepIndex(),
                        'stepCount' => $this->getStepCount());
                }

                $target = $this->getConfirmAction() ?: $this->getProcessAction();
                break;

            // cancel
            case $this->navigationElements[self::ACTION_CANCEL]:
                return $this->cancel();
                break;

            // next or other
            case $this->navigationElements[self::ACTION_NEXT]:
            default:
                if (!$valid) {
                    return array(
                        'fieldset'  => $currentFieldset, 
                        'stepIndex' => $this->getCurrentStepIndex(),
                        'stepCount' => $this->getStepCount());
                }

                $position = intval(array_search($currentFieldset->getName(), $this->fieldsetOrder));

                $fieldsetCount = count($this->fieldsetOrder);
                if ($position === $fieldsetCount - 1) {
                    $fieldset = $this->fieldsetOrder[$fieldsetCount - 1];
                } else {
                    $fieldset = $this->fieldsetOrder[$position + 1];
                }
                
                $this->setActiveFieldsetName($fieldset);
                $target = $this->fieldsetActions[$fieldset];
        }
        
        $response = $this->redirect($target);
        
        return $response;
    }
    
    /**
     * Handle the confirmation step
     *
     * @return array|Response
     */
    protected function handleConfirm()
    {
        // post redirect get pattern if required
        if ($this->getUsePostRedirectGet()) {
            $prg = $this->getController()->plugin('prg');
            $this->prgResult = $prg();
            if ($this->prgResult instanceof Response) {
                // returned a response to redirect us
                return $this->prgResult;
            }
        }
        
        $submitAction = $this->getSubmitAction();
        
        switch ($submitAction) {
            
            case $this->navigationElements[self::ACTION_CONFIRM]:
                $response = $this->redirect($this->getProcessAction());
                break;
            
            case $this->navigationElements[self::ACTION_PREVIOUS]:
                $response = $this->redirect($this->getLastValidAction());
                break;
            
            case $this->navigationElements[self::ACTION_CANCEL]:
                $response = $this->cancel();
                break;
            
            case false: // GET request
            default:
                $data = $this->getFormSessionData();
                $this->getForm()->setValue($data)
                                ->getInputFilter()->setData($data);
                $response = array(
                    'form'      => $this->getForm(), 
                    'stepIndex' => $this->getCurrentStepIndex(),
                    'stepCount' => $this->getStepCount()
                );
        }
        
        return $response;
    }
    
    /**
     * Cancel multipage form process.
     * 
     * @return Response
     */
    public function cancel()
    {
        if (!$this->getCancelAction()) {
            $this->clear();
            $fieldset = $this->fieldsetOrder[0]; // first step fieldset
            $target = $this->fieldsetActions[$fieldset];
            $this->setActiveFieldsetName($fieldset);
        } 
        else {
            $target = $this->getCancelAction();
        }

        return $this->redirect($target);
    }

    /**
     * Determine if a form has been validated
     *
     * @param string $fieldsetName
     * @return boolean
     */
    protected function isValidFieldset($fieldsetName)
    {
        // Loop through the fieldset => action mapping
        foreach ($this->fieldsetActions as $name => $action) {
            
            // If this loop hasn't found an invalid action yet, and the currentAction and action match
            // we can assume this is the currently active fieldset.
            if ($fieldsetName === $name) {
                break;
            }

            // If the provided fieldset isn't complete yet, we're too far.
            // This means that the provided action is invalid.
            if (!$this->isCompleteFieldset($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if all fieldsets have been validated
     *
     * @return boolean
     */
    protected function isValidForm()
    {
        // Loop through the fieldset => action mapping
        foreach ($this->fieldsetActions as $name => $action) {
            if (!$this->isValidFieldset($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve current last valid action
     *
     * @return string
     */
    protected function getLastValidAction()
    {
        // Loop through the form->action mapping
        foreach ($this->fieldsetActions as $fieldsetName => $action) {
            
            $lastValidFieldset = $fieldsetName;

            // If the provided form isn't complete yet, we're too far.
            // This means that the provided action is invalid.
            if (!$this->isCompleteFieldset($fieldsetName)) {
                break;
            }
        }

        return $this->fieldsetActions[$lastValidFieldset];
    }

    /**
     * Determine if a form has been submitted and successfully validated
     *
     * @param string $action
     * @return mixed
     */
    protected function isCompleteFieldset($formName)
    {
        // Retrieve the validation state from the session
        return isset($this->getSessionContainer()->valid[$formName]) && $this->getSessionContainer()->valid[$formName];
    }

    /**
     * Set the action used for confirming the complete form
     *
     * @param string $action
     * @return self
     */
    public function setConfirmAction($action)
    {
        $this->confirmAction = $action;
        return $this;
    }

    /**
     * Get the confirming action
     *
     * @return string
     */
    public function getConfirmAction()
    {
        return $this->confirmAction;
    }

    /**
     * Set the action used for processing the complete form
     *
     * @param string $action
     * @return self
     */
    public function setProcessAction($action)
    {
        $this->processAction = $action;
        return $this;
    }

    /**
     * Get the processing action
     *
     * @return string
     */
    public function getProcessAction()
    {
        return $this->processAction;
    }

    /**
     * Set a custom cancel action
     *
     * @param string $action
     * @return self
     */
    public function setCancelAction($action)
    {
        $this->cancelAction = $action;
        return $this;
    }

    /**
     * Get the custom cancel action
     *
     * @return string
     */
    public function getCancelAction()
    {
        return $this->cancelAction;
    }

    /**
     * Set sequence of actions.
     *
     * @param array $fieldsetActionMapping fieldset name => action name
     * @return self
     */
    protected function setFieldsetActionMapping(array $fieldsetActionMapping = array())
    {
        $fieldsetActions = array();
        $fieldsetOrder   = array();

        foreach ($fieldsetActionMapping as $key => $value) {
            $fieldsetActions[$key] = $value;
            $fieldsetOrder[] = $key;
        }

        $this->fieldsetActions = $fieldsetActions;
        $this->fieldsetOrder   = $fieldsetOrder;
        
        // Reset the session if this is the first time the forms/actions are mapped
        if (null === $this->getSessionContainer()->valid || !array_key_exists($fieldsetOrder[0], $this->getSessionContainer()->valid)) {
            $this->clear();
        }

        return $this;
    }

    /**
     * Set values for an action
     *
     * @param Fieldset $fieldset
     * @param boolean $valid
     * @return self
     */
    protected function setValues(Fieldset $fieldset, $valid = false)
    {
        $fieldsetName = $fieldset->getName();
        
        // Get the form values and their element names
        $formValues = $fieldset->getValue();
        $formKeys = array_keys($formValues);
        
        // Loop through the element names to see if there are action elements (default prefixed with _)
        foreach ($formKeys as $key) {
            // If an element with the action prefix is found, remove it from the array.
            if (strpos($key, $this->navigationElements[self::ACTION_PREFIX]) === 0) {
                // We don't want to store actions in the session.
                unset($formValues[$key]);
            }
        }
        
        // Elaborate setup to write some values to the session arrays
        // This is needed to work around a bug/feature in PHP 5.2.0 iirc
        $validForms = $this->getSessionContainer()->valid;
        $sessionFormValues = $this->getSessionContainer()->value;
        
        $validForms[$fieldsetName] = (bool) $valid;
        $sessionFormValues[$fieldsetName] = /*array_key_exists($fieldsetName, $formValues) ? $formValues[$fieldsetName] :*/ $formValues;

        // Write the validation state and form values to the session
        $this->getSessionContainer()->valid = $validForms;
        $this->getSessionContainer()->value = $sessionFormValues;
        
        // Chaining
        return $this;
    }

    /**
     * Retrieve fieldset values from the session
     *
     * @param string $fieldsetName
     * @return mixed
     */
    protected function getSessionValues($fieldsetName)
    {
        $values = isset($this->getSessionContainer()->value[$fieldsetName]) ?
                $this->getSessionContainer()->value[$fieldsetName] :
                array();
        
        return $values;
    }

    /**
     * Set the form instance
     *
     * @param \UnicaenApp\Form\MultipageForm $form
     * @return self
     */
    public function setForm(\UnicaenApp\Form\MultipageForm $form)
    {
        $this->form = $form;

        // Check if we have some fieldsets
        if (!($fieldsets = $this->getForm()->getFieldsets())) {
            throw new IllegalStateException('The form needs to have fieldsets.');
        }
        // Loop through all the fieldsets and check if they all have a name
        foreach ($fieldsets as $fieldset) { /* @var $fieldset Fieldset */
            if (!($fieldset->getName())) {
                throw new IllegalStateException('A fieldset needs to have a name.');
            }
        }
        
        $this->setFieldsetActionMapping($this->getForm()->getFieldsetActionMapping());
        if (!$this->getConfirmAction()) {
            $this->setConfirmAction($this->getForm()->getConfirmAction());
        }
        if (!$this->getProcessAction()) {
            $this->setProcessAction($this->getForm()->getProcessAction());
        }
        if (!$this->getCancelAction()) {
            $this->setCancelAction($this->getForm()->getCancelAction());
        }
        
        return $this;
    }

    /**
     * Retourne l'instance du formulaire complet.
     *
     * @return \UnicaenApp\Form\MultipageForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get the current fieldset
     *
     * @return Fieldset
     */
    public function getCurrentFieldset()
    {
        // Get the active form's name
        $fieldsetName = $this->getActiveFieldsetName();

        // Fetch the form instance and assign it as being the currently active fieldset
        return $this->getFieldset($fieldsetName);
    }

    /**
     * Get a fieldset by name.
     *
     * @param string $fieldsetName
     * @return Fieldset
     */
    protected function getFieldset($fieldsetName)
    {
        $fieldsets = $this->form->getFieldsets();
        
        $fieldset = $fieldsets[$fieldsetName]; /* @var $fieldset Fieldset */
        
        // Populate the fieldset with session data
        $fieldset->populateValues($this->getSessionValues($fieldsetName)); // populate()

        return $fieldset;
    }

    /**
     * Get all data from the fieldsets from the session.
     *
     * @return array
     */
    public function getFormSessionData()
    {
        $formData = array();

        foreach ($this->fieldsetActions as $formName => $action) {
            $formData[$formName] = $this->getSessionValues($formName);
        }

        return $formData;
    }

    /**
     * Get the form data. If it's empty and a submitted form is available,
     * populate it first from POST.
     *
     * @return array
     */
    protected function getPostData()
    {
        if ($this->getUsePostRedirectGet() && is_array($this->prgResult)) {
            $requestPostData = $this->prgResult;
        }
        elseif ($this->getController()->getRequest()->isPost()) {
            $requestPostData = $this->getController()->getRequest()->getPost()->toArray();
        }
        else {
            $requestPostData = array();
        }

        $postData = array();
        
        if ($requestPostData) {
            $currentFieldset = $this->getCurrentFieldset();

            if (isset($requestPostData[$currentFieldset->getName()])) {
                $requestPostData = $requestPostData[$currentFieldset->getName()];
            }

            $elements = $currentFieldset->getElements();
            $postData = array_intersect_key($requestPostData, $elements);

            // ajout: les boutons d'action peuvent être au sein d'un élément composite de nom "_nav"
            if (isset($requestPostData[self::NAV])) {
                $postData += $requestPostData[self::NAV];
                unset($postData[self::NAV]);
            }
        }
            
        return $postData;
    }

    /**
     * Use the redirector plugin to navigate the controller
     *
     * @param string $action
     * @param string $controller
     * @return Response
     */
    protected function redirect($action, $controller = null)
    {
        $routeMatch = $this->getController()->getEvent()->getRouteMatch();
        if (null === $controller) {
            $controller = $routeMatch->getParam('controller');
        }
        
        /**
         * Attention! 
         * 
         * On peut se retrouver avec plusieurs clés 'Location' dans le header de la réponse HTTP, 
         * ce qui pose problème dans les tests unitaires car Response::getHeaders()->get('Location')->getUri()
         * ne renvoit que la première 'Location' du header.
         * 
         * 1/ On ne fait pas appel à $this->getController()->plugin('redirect'), mais on
         * instancie le plugin Redirect à la main.
         * 
         * 2/ On remet à zéro le header de la réponse systématiquement.
         */
        
        $this->getController()->getEvent()->getResponse()->getHeaders()->clearHeaders();
        
        $redirector = new \Zend\Mvc\Controller\Plugin\Redirect();
        $redirector->setController($this->getController());
        $params = array('action' => $action, 'controller' => $controller);
        $response = $redirector->toRoute($routeMatch->getMatchedRouteName(), $params);
        $response->setStatusCode(303);
        
        return $response;
    }

    /**
     * Get the action used to submit the form
     *
     * @return string|bool
     */
    public function getSubmitAction()
    {
        $formData = $this->getPostData();
        
        if (!empty($formData)) {
            $submitAction = null;
            $formDataKeys = array_keys($formData);
            foreach ($formDataKeys as $key) {
                if (strpos($key, $this->navigationElements[self::ACTION_PREFIX]) === 0) {
                    $submitAction = $key;
                    break;
                }
            }
            if ($submitAction && in_array($submitAction, $this->navigationElements)) {
                return $submitAction;
            }
        }

        return false;
    }

    protected function isValidCurrentFieldset()
    {
        $currentFieldset = $this->getCurrentFieldset();
        $postData = $this->getPostData();
        
        // populate elements values
        foreach ($postData as $elementName => $value) {
            if ($currentFieldset->has($elementName)) {
                $currentFieldset->get($elementName)->setValue($value);
            }
        }
        
        // validate each element value
        if ($currentFieldset instanceof InputFilterProviderInterface) {
            $filter = new InputFilter();
            foreach ($currentFieldset->getInputFilterSpecification() as $name => $spec) {
                $filter->add($spec, $name);
            }
            $filter->setData($postData);
            $valid = $filter->isValid();
            $currentFieldset->setMessages($filter->getMessages());
            
            return $valid;
        }
        
        return true;
    }

    /**
     * Return the current step index.
     *
     * @return int 1 <= index <= step count
     */
    public function getCurrentStepIndex()
    {
        $indexes = array_keys($this->fieldsetOrder, $this->getActiveFieldsetName());
        $index = $indexes ? 1 + $indexes[0] : 0;
        return $index;
    }

    /**
     * Return the number of steps.
     *
     * @param bool $includeConfirmation Count or not confirmation step
     * @return int
     */
    public function getStepCount($includeConfirmation = false)
    {
        $count = count($this->fieldsetActions);
        $count = $count && $includeConfirmation && $this->getConfirmAction() ? $count + 1 : $count;
        return $count;
    }

    /**
     * Check if the action is an action for this form.
     *
     * @param string $action Action name
     * @return string Found fieldset name
     */
    protected function isFieldsetAction($action)
    {
        return array_search($action, $this->fieldsetActions);
    }

    /**
     * Check if the action is an action for this form.
     *
     * @param string $action
     * @return boolean
     */
    protected function isFieldset($formName)
    {
        return array_key_exists($formName, $this->fieldsetActions);
    }

    /**
     * Get the active form name
     *
     * @return string
     */
    protected function getActiveFieldsetName()
    {
        return $this->getSessionContainer()->active;
    }

    /**
     * Set the active form name
     *
     * @param string $formName
     * @return self
     */
    protected function setActiveFieldsetName($formName)
    {
        $this->getSessionContainer()->active = $formName;

        return $this;
    }

    /**
     * Reset all session data
     *
     * @return self
     */
    public function clear()
    {
        // Create two brand new arrays
        $valid = array();
        $value = array();
        
        // Loop through the formnames, so we can reset their session data
        foreach ($this->fieldsetOrder as $formName) {
            $valid[$formName] = false;
            $value[$formName] = array();
        }

        // Write all default variables to the session
        $this->getSessionContainer()->valid  = $valid;
        $this->getSessionContainer()->value  = $value;
        $this->getSessionContainer()->active = $this->fieldsetOrder[0];
        $this->getSessionContainer()->action = '';

        // Chaining
        return $this;
    }

    /**
     * Returns session persistance container
     * 
     * @return Container
     */
    public function getSessionContainer()
    {
        if (null === $this->sessionContainer) {
            // set default session object
            $this->sessionContainer = new Container('MultipageForm_' . get_class($this->getController()));
        }
        return $this->sessionContainer;
    }

    /**
     * Sets session persistance container
     * 
     * @param Container $container
     * @return self
     */
    public function setSessionContainer(Container $container)
    {
        $this->sessionContainer = $container;
        return $this;
    }
    
    /**
     * Return Post-Redirect-Get pattern activation flag
     * 
     * @return bool
     */
    public function getUsePostRedirectGet()
    {
        return $this->usePostRedirectGet;
    }
    
    /**
     * Set Post-Redirect-Get pattern activation flag
     * 
     * @param bool $usePostRedirectGet
     * @return self
     */
    public function setUsePostRedirectGet($usePostRedirectGet = true)
    {
        $this->usePostRedirectGet = $usePostRedirectGet;
        return $this;
    }
}