<?php
namespace UnicaenAppTest\Controller\Plugin\MultipageForm;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\MultipageFormNav;
use UnicaenApp\Controller\Plugin\MultipageForm;
use UnicaenAppTest\Controller\Plugin\TestAsset\ContactController;
use UnicaenAppTest\Form\TestAsset\ContactMultipageForm;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;
use Zend\ServiceManager\Config;
use Zend\Stdlib\Parameters;
use Zend\Mvc\Controller\PluginManager;

/**
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see MultipageForm
 * @see ContactController
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    protected static $stepFieldsets = array(
        // stepIndex => fieldsetName
        1 => ContactMultipageForm::FIELDSET_1_NAME,
        2 => ContactMultipageForm::FIELDSET_2_NAME,
        3 => ContactMultipageForm::FIELDSET_3_NAME,
    );
    public $controller;
    public $event;
    public $routeMatch;
    public $request;
    public $response;
    public $router;

    /**
     * @var MultipageForm
     */
    protected $plugin;

    /**
     * @var ContactMultipageForm
     */
    protected $form;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $options = array(
            'route'    => '/contact/:action',
            'defaults' => array(
                'controller' => 'UnicaenAppTest\Controller\Plugin\TestAsset\ContactController',
            ),
        );
        $this->router = new SimpleRouteStack();
        $this->router->addRoute('contact', \Zend\Mvc\Router\Http\Segment::factory($options));

        $this->controller = new ContactController();
        $this->routeMatch = new RouteMatch(array('controller' => 'contact', 'action'     => 'index'));
        $this->request    = new Request();
        $this->response   = new Response();
        $this->event      = new MvcEvent();

        $this->event->setRequest($this->request);
        $this->event->setResponse($this->response);
        $this->event->setRouteMatch($this->routeMatch);
        $this->event->setRouter($this->router);

        $this->routeMatch->setMatchedRouteName('contact');

        $this->controller->setEvent($this->event);
        $this->controller->setPluginManager(new PluginManager(new Config(array(
            'invokables' => array(
                'multipageForm' => '\UnicaenApp\Controller\Plugin\MultipageForm',
            ),
        ))));

        $this->plugin = $this->controller->plugin('multipageForm');
    }

    protected function dispatchGetRequestOnStep($fieldsetName)
    {
        $this->request = new Request();
        $this->routeMatch->setParam('action', 'ajouter-' . $fieldsetName);
        return $this->controller->dispatch($this->request, $this->response);
    }

    protected function dispatchGetRequestOnAction($actionName)
    {
        $this->request = new Request();
        $this->routeMatch->setParam('action', $actionName);
        return $this->controller->dispatch($this->request, $this->response);
    }

    protected function dispatchPostRequestOnStep($fieldsetName, $validPostData = true, $submitName = MultipageFormNav::NEXT)
    {
        $post = $this->getForm()->createSamplePostDataForFieldset($fieldsetName, $validPostData, $submitName);
        $this->request = new Request();
        $this->request->setMethod(Request::METHOD_POST)
                ->setPost(new Parameters($post));
        $this->routeMatch->setParam('action', 'ajouter-' . $fieldsetName);
        return $this->controller->dispatch($this->request, $this->response);
    }

    protected function dispatchPostRequestOnAction($actionName, $submitName = MultipageFormNav::NEXT)
    {
        $post = array(MultipageFormNav::NAME => array($submitName => $submitName));
        $this->request = new Request();
        $this->request->setMethod(Request::METHOD_POST)
                ->setPost(new Parameters($post));
        $this->routeMatch->setParam('action', $actionName);
        return $this->controller->dispatch($this->request, $this->response);
    }

    protected function assertIsRedirectResponse($result, $stepIndex)
    {
        $redirectUris = array(
            1 => '/contact/ajouter-identite',
            2 => '/contact/ajouter-adresse',
            3 => '/contact/ajouter-message',
        );
        $this->assertInstanceOf('\Zend\Http\Response', $result);
        $this->assertTrue($result->isRedirect());
        $this->assertTrue($result->getHeaders()->has('Location'));
        $this->assertEquals($redirectUris[$stepIndex], $result->getHeaders()->get('Location')->getUri());
    }

    protected function assertIsRedirectResponseToCancelAction($result)
    {
        $this->assertInstanceOf('\Zend\Http\Response', $result);
        $this->assertTrue($result->isRedirect());
        $this->assertTrue($result->getHeaders()->has('Location'));
        $this->assertEquals('/contact/ajouter-annuler', $result->getHeaders()->get('Location')->getUri());
    }

    protected function assertIsRedirectResponseToConfirmAction($result)
    {
        $this->assertInstanceOf('\Zend\Http\Response', $result);
        $this->assertTrue($result->isRedirect());
        $this->assertTrue($result->getHeaders()->has('Location'));
        $this->assertEquals('/contact/ajouter-confirmer', $result->getHeaders()->get('Location')->getUri());
    }

    protected function assertIsRedirectResponseToFinalAction($result)
    {
        $this->assertInstanceOf('\Zend\Http\Response', $result);
        $this->assertTrue($result->isRedirect());
        $this->assertTrue($result->getHeaders()->has('Location'));
        $this->assertEquals('/contact/ajouter-enregistrer', $result->getHeaders()->get('Location')->getUri());
    }

    protected function assertIsArrayResponse($result, $stepIndex, $withForm = false)
    {
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('stepCount', $result);
        $this->assertArrayHasKey('stepIndex', $result);
        $this->assertEquals(3, $result['stepCount']);
        $this->assertEquals($stepIndex, $result['stepIndex']);
        if ($withForm) {
            $this->assertArrayHasKey('form', $result);
            $this->assertInstanceOf('Zend\Form\Form', $fs = $result['form']); /* @var $fs Form */
        }
        else {
            $classes = array(
                1 => '\UnicaenAppTest\Form\TestAsset\IdentiteFieldset',
                2 => '\UnicaenAppTest\Form\TestAsset\AdresseFieldset',
                3 => '\UnicaenAppTest\Form\TestAsset\MessageFieldset',
            );
            $this->assertArrayHasKey('fieldset', $result);
            $this->assertInstanceOf($classes[$stepIndex], $fs = $result['fieldset']); /* @var $fs Fieldset */
        }
    }

    /**
     * @return ContactMultipageForm
     */
    protected function getForm()
    {
        return $this->controller->getForm();
    }
}