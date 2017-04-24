<?php
namespace UnicaenAppTest\View\Helper;

use UnicaenApp\View\Helper\Messenger;

/**
 * Description of MessengerText
 *
 * @property Messenger $helper Description
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MessengerText extends AbstractTest
{
    protected $helperClass = 'UnicaenApp\View\Helper\Messenger';
    protected $router;
    protected $routeMatch;
    
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }
    
    public function testReturnsSelfWhenInvoked()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
    
    public function getMessages()
    {
        $info    = "Information message";
        $success = "Success message";
        $error   = "Error message";
        
        return array(
            array(
                'severity' => null,
                'messages' => array(
                    'info'    => $info,
                    'success' => $success,
                    'danger'   => $error),
                'expected' => array(
                    'info'    => array($info),
                    'success' => array($success),
                    'danger'   => array($error)),
            ),
            array(
                'severity' => null, 
                'messages' => $messages = array(
                    'info'    => array($info, $info . ' n°2'),
                    'success' => array($success),
                    'danger'   => array($error)),
                'expected' => $messages,
            ),
            array(
                'severity' => 'info', 
                'messages' => $info,
                'expected' => array($info),
            ),
            array(
                'severity' => 'info', 
                'messages' => array($info, $info . ' n°2'),
                'expected' => array($info, $info . ' n°2'),
            ),
            array(
                'severity' => 'info', 
                'messages' => array('info' => array($info, $info . ' n°2')),
                'expected' => array($info, $info . ' n°2'),
            ),
            array(
                'severity' => 'info', 
                'messages' => array('info' => $info),
                'expected' => array($info),
            ),
            array(
                'severity' => 'success', 
                'messages' => array('success' => $success),
                'expected' => array($success),
            ),
            array(
                'severity' => 'danger', 
                'messages' => array('danger' => $error),
                'expected' => array($error),
            ),
        );
    }
    
    /**
     * @dataProvider getMessages
     */
    public function testThrowsExceptionIfNoTitleSpecified($severity, $messages, $expected)
    {
        $this->helper->setMessages($messages);
        $this->assertTrue($this->helper->hasMessages($severity));
        $this->assertEquals($expected, $this->helper->getMessages($severity));
    }
    
    public function testSettingUniqueMessageReplaceExistingMessages()
    {
        $this->helper->setMessages(array(
            'info'  => "Information message.", 
            'danger' => "Error message."));
        
        $this->helper->setMessage(array('info' => $message = "Unique information message."));
        $this->assertEquals(array('info' => array($message)), $this->helper->getMessages());
    }
    
    public function testCanAddMessage()
    {
        $this->helper->setMessages(array(
            'info'  => "Information message.", 
            'danger' => "Error message."));
        
        $this->helper->addMessage("Success message.", 'success');
        $expected = array(
            'info'    => array("Information message."),
            'success' => array("Success message."), 
            'danger'   => array("Error message."));
        $this->assertEquals($expected, $this->helper->getMessages());
        
        $this->helper->addMessage("Another information message.");
        $expected = array(
            'info'    => array("Information message.", "Another information message."),
            'success' => array("Success message."), 
            'danger'   => array("Error message."));
        $this->assertEquals($expected, $this->helper->getMessages());
    }
    
    public function testCanClearMessages()
    {
        $this->helper->setMessages(array(
            'info'    => "Information message.", 
            'success' => "Success message.", 
            'danger'   => "Error message."));
        
        $this->helper->clearMessages('info');
        $expected = array(
            'info'    => array(),
            'success' => array("Success message."), 
            'danger'   => array("Error message."));
        $this->assertEquals($expected, $this->helper->getMessages());
        
        $this->helper->clearMessages();
        $this->assertEmpty($this->helper->getMessages());
    }
    
    public function testCanImportFlashMessages()
    {
        $flashMessenger = $this->getMock('\Zend\View\Helper\FlashMessenger', array('getInfoMessages', 'getSuccessMessages', 'getErrorMessages'));
        $this->helper->getView()->getHelperPluginManager()->setService('flashMessenger', $flashMessenger);
        
        $flashMessenger->expects($this->any())
                       ->method('getInfoMessages')
                       ->will($this->returnValue(array("Information message from FlashMessenger.")));
        $flashMessenger->expects($this->any())
                       ->method('getSuccessMessages')
                       ->will($this->returnValue(array("Success message from FlashMessenger.")));
        $flashMessenger->expects($this->any())
                       ->method('getErrorMessages')
                       ->will($this->returnValue(array("Error message from FlashMessenger.")));
        
        $this->helper->setMessages(array(
            'info'    => "Information message.", 
            'success' => "Success message.", 
            'danger'   => "Error message."));
        
        $this->helper->__invoke(true);
        $expected = array(
            'info'    => array("Information message.", "Information message from FlashMessenger."),
            'success' => array("Success message.", "Success message from FlashMessenger."), 
            'danger'   => array("Error message.", "Error message from FlashMessenger."));
        $this->assertEquals($expected, $this->helper->getMessages());
    }
    
    public function testRenderingReturnsEmptyStringIfNoMessageSpecified()
    {
        $this->assertEquals('', "" . $this->helper);
    }
    
    public function getMessagesAndExpectedScript()
    {
        return array(
            'one-info' => array(
                "Information message.",
                'messenger/one-info.phtml'
            ),
            'two-errors' => array(
                array(
                    'danger' => array("Error message.", "Another error message.")),
                'messenger/two-errors.phtml'
            ),
            'all' => array(
                array(
                    'info'    => "Information message.", 
                    'success' => "Success message.", 
                    'danger'   => "Error message."),
                'messenger/all.phtml'
            ),
        );
    }
    
    /**
     * @dataProvider getMessagesAndExpectedScript
     */
    public function testRenderingReturnsCorrectMarkup($messages, $expectedScript)
    {
        $this->helper->setMessages($messages);
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected($expectedScript), $markup);
    }
    
    /**
     * @expectedException \UnicaenApp\Exception\LogicException
     */
    public function testGettingTemplateForUnknownSeverityThrowsException()
    {
        $this->helper->getTemplate('unknown');
    }
    
    public function testCanCustomizeMarkupTemplate()
    {
        $this->helper->addMessage("Information message.")
                     ->setContainerInnerTemplate('<em>%s</em>');
        $markup = (string) $this->helper;
        $this->assertEquals($this->getExpected('messenger/custom.phtml'), $markup);
    }
}