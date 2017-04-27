<?php
namespace UnicaenAppTest\Controller\Plugin;

use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\Literal as LiteralRoute;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;
use UnicaenAppTest\Controller\Plugin\TestAsset\ContactController;
use UnicaenAppTest\Entity\Ldap\TestAsset\People as PeopleTestAsset;

/**
 * Tests unitaires du plugin d'envoi de mail.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\Controller\Plugin\Mail
 */
class MailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \UnicaenApp\Controller\Plugin\Mail 
     */
    protected $plugin;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject 
     */
    protected $transport;
    
    /**
     * 
     */
    protected function setUp()
    {        
        $this->response = new Response();

        $router = new SimpleRouteStack();
        $router->addRoute('home', LiteralRoute::factory(array(
            'route' => '/',
            'defaults' => array(
                'controller' => 'UnicaenAppTest\Controller\Plugin\TestAsset\ContactController',
            ),
        )));
        $this->router = $router;

        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('home');
        $this->routeMatch = $routeMatch;

        $event = new MvcEvent();
        $event->setRouter($router);
        $event->setResponse($this->response);
        $this->event = $event;

        $this->controller = new ContactController();
        $this->controller->setEvent($event);

        $this->transport = $this->getMock('\Zend\Mail\Transport\Smtp', array('send'));
        
        $transport = $this->transport;
        $this->controller->setPluginManager(new \Zend\Mvc\Controller\PluginManager(new \Zend\ServiceManager\Config(array(
            'factories' => array(
                'mail' => function (\Zend\Mvc\Controller\PluginManager $sm) use ($transport) {
                    return new \UnicaenApp\Controller\Plugin\Mail($transport);
                },
            ),
        ))));
        
        $this->plugin = $this->controller->plugin('mail');
    }
    
    function testCallingInvokeMethodOfPluginReturnsSelf()
    {
        $plugin = $this->plugin;
        $this->assertSame($plugin, $plugin());
    }
    
    function testSendingMailWithDefaultOptions()
    {
        $message = $this->createMessage(true);
        $this->transport
                ->expects($this->once())
                ->method('send')
                ->with($message);
        $msg = $this->plugin->send($message);
        $this->assertSame($msg, $message);
    }
    
    function testSendingMailWithDoNotSendOption()
    {
        $message = $this->createMessage(true);
        $this->plugin->setDoNotSend(true);
        $this->transport
                ->expects($this->never())
                ->method('send');
        $msg = $this->plugin->send($message);
        $this->assertSame($msg, $message);
    }
    
    /**
     * 
     */
    public function provideRedirectionOption()
    {
        return array(
            array(
                false, 
                array('redir1.application@unicaen.fr')
            ),
            array(
                false, 
                array('redir1.application@unicaen.fr', 'redir2.application@unicaen.fr')
            ),
            array(
                true, 
                array('redir1.application@unicaen.fr')
            ),
            array(
                true, 
                array('redir1.application@unicaen.fr', 'redir2.application@unicaen.fr')
            ),
        );
    }
    
    /**
     * @dataProvider provideRedirectionOption
     * @param bool $htmlFormat
     * @param array $redirectTo
     */
    function testSendingMailWithRedirectionList($htmlFormat, $redirectTo)
    {
        $message = $this->createMessage($htmlFormat);
        $this->plugin->setDoNotSend(false)
                     ->setRedirectTo($redirectTo)
                     ->addRedirectTo($addedRedirectTo = array('admin@domain.fr'));
        $this->transport
                ->expects($this->once())
                ->method('send');
        $msg = $this->plugin->send($message);
        
        $redirectTo = array_merge($redirectTo, $addedRedirectTo);
        
        $this->assertInstanceOf(get_class($message), $msg);
        $this->assertNotSame($msg, $message, "Le message original n'a pas été copié");
        $this->assertSame($redirectTo, array_keys(iterator_to_array($msg->getTo())), "To: ne contient pas les adresses de redirection");
        $this->assertEmpty(iterator_to_array($msg->getCc()), "Cc: n'a pas été vidé");
        $this->assertEmpty(iterator_to_array($msg->getBcc()), "Bcc: n'a pas été vidé");
        foreach ($message->getTo() as $addr /* @var $addr \Zend\Mail\Address */) {
            $this->assertContains($addr->getEmail(), $msg->getBodyText(), "L'adresse mail du destinataire original ne figure pas dans le corps du mail");
            $this->assertContains($addr->getName(),  $msg->getBodyText(), "Le nom du destinataire original ne figure pas dans le corps du mail");
        }
    }
    
    function testSendingMailWithEmptyRedirectionListDoesNotRedirect()
    {
        $message = $this->createMessage(true);
        $this->plugin->setDoNotSend(false)->setRedirectTo(array());
        $this->transport
                ->expects($this->once())
                ->method('send');
        $msg = $this->plugin->send($message);
        $this->assertSame($msg, $message);
    }
    
    /**
     * @dataProvider provideRedirectionOption
     * @param bool $htmlFormat
     * @param array $redirectTo
     */
    function testSendingMailWithRedirectionAndDoNotSendOptions($htmlFormat, $redirectTo)
    {
        $message = $this->createMessage($htmlFormat);
        $this->plugin->setRedirectTo($redirectTo)->setDoNotSend(true);
        $this->transport
                ->expects($this->never())
                ->method('send');
        $msg = $this->plugin->send($message);
        $this->assertInstanceOf(get_class($message), $msg);
        $this->assertNotSame($msg, $message);
    }
    
    /**
     * @dataProvider provideRedirectionOption
     * @param bool $htmlFormat
     * @param array $redirectTo
     * @depends testSendingMailWithRedirectionList
     */
    function testSendingMailWithRedirectionListContainingCurrentUserTagAppendsCurrentUserToRedirectionList($htmlFormat, $redirectTo)
    {
        $redirectTo[] = $tag = \UnicaenApp\Controller\Plugin\Mail::CURRENT_USER;
        
        $entity = new \UnicaenApp\Entity\Ldap\People(PeopleTestAsset::$data1);
        
        $message = $this->createMessage($htmlFormat);
        $this->plugin->setIdentity($entity);
        $this->plugin->setDoNotSend(false)->setRedirectTo($redirectTo);
        $this->plugin->send($message);
        
        $this->assertArrayHasKey($entity->getMail(), $this->plugin->getRedirectTo());
        $this->assertArrayNotHasKey($tag, $this->plugin->getRedirectTo());
    }
    
    /**
     * 
     * @param bool $htmlFormat Corps du mail au format HTML ?
     * @return \Zend\Mail\Message
     */
    protected function createMessage($htmlFormat = false)
    {
        $message = new \Zend\Mail\Message();
        
        $message->addTo('send.to@unicaen.fr', "Destinataire")
                ->addCc('copy.to@unicaen.fr', "Copie")
                ->addBcc('bcc.to@unicaen.fr', "Copie cachée")
                ->setFrom('sent.from@unicaen.fr', "Envoyeur")
                ->setSubject("Mail de test");
        
        $template = <<<EOS
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec quis aliquet lectus. Fusce at turpis ac eros facilisis vulputate. %s 
%s
Morbi sed quam eu est consectetur condimentum ut eu erat. %s
Praesent ante ante, ornare eget ultricies vel, porta aliquet ligula. Donec in tellus tellus. Ut at lacus sapien, ut ornare erat. Vivamus quis velit id elit mattis cursus sit amet eu eros. Maecenas augue lacus, dictum nec hendrerit a, iaculis et ante. Praesent sodales risus in purus sollicitudin in malesuada ante condimentum. Donec mattis pellentesque augue, vel tincidunt elit pretium a. Ut imperdiet felis pulvinar diam consectetur vulputate. %s
%s
Mauris auctor ante sed nulla porta posuere. Vestibulum mauris risus, gravida ac vestibulum in, eleifend non risus. %s
Fusce tristique ante nec eros mattis consectetur. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc blandit, nisi nec varius placerat, diam ligula faucibus leo, ut rhoncus nulla nisi malesuada arcu. Nunc congue felis in lacus elementum faucibus. Aliquam bibendum porta pharetra. Mauris dolor urna, posuere vel imperdiet at, dictum quis sem. Donec libero odio, sodales nec dignissim ac, auctor hendrerit felis. Donec accumsan aliquam eros, sed tincidunt eros interdum sit amet. Proin porttitor pharetra elit quis laoreet. %s
%s
EOS;
        if ($htmlFormat) {
            $html = str_replace('%s', '<br />', $template);
            $part = new \Zend\Mime\Part($html);
            $part->type = \Zend\Mime\Mime::TYPE_HTML;
            $body = new \Zend\Mime\Message();
            $body->addPart($part);
            $message->setBody($body);
        }
        else {
            $body = str_replace('%s', '', $template);
            $message->setBody($body);
        }
        
        return $message;
    }
}