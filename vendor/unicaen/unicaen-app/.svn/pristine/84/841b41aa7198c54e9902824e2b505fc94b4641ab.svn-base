<?php
namespace UnicaenApp;

use Zend\EventManager\EventInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Validator\AbstractValidator;
use Zend\Mvc\I18n\Translator;
use Locale;
use UnicaenApp\Mvc\View\Http\ExceptionStrategy;
use UnicaenApp\Mvc\Listener\ModalListener;

define('__VENDOR_DIR__', dirname(dirname(__DIR__)));

/**
 * Point d'entrée du module.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Module implements 
        BootstrapListenerInterface, 
        ConfigProviderInterface, 
        ControllerPluginProviderInterface,
        ServiceProviderInterface
{

    /**
     * 
     * @return array
     * @see ConfigProviderInterface
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * 
     * @return array
     * @see AutoloaderProviderInterface
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * This method is called once the MVC bootstrapping is complete,
     * after the "loadModule.post" event, once $application->bootstrap() is called.
     * 
     * @param EventInterface $e
     * @see BootstrapListenerInterface
     */
    public function onBootstrap(EventInterface $e) /* @var $e \Zend\Mvc\MvcEvent */
    {
        /* @var $application \Zend\Mvc\Application */
        $application = $e->getApplication();
        /* @var $services ServiceManager */
        $services    = $application->getServiceManager();
        
        $this->bootstrapSession($e);

        // la locale par défaut est celle du service de traduction
        $translator = $services->get('translator');
        Locale::setDefault($translator->getLocale());
        
        AbstractValidator::setDefaultTranslator(new Translator($translator));
                
        $eventManager = $application->getEventManager();
        $viewManager  = $services->get('view_manager');
        
        $exceptionStrategy = new ExceptionStrategy();
        $exceptionStrategy->setDisplayExceptions($viewManager->getExceptionStrategy()->displayExceptions());
        $exceptionStrategy->attach($eventManager);
        
        /* @var $vhm HelperPluginManager */
        $vhm = $services->get('view_helper_manager');
        
        /* @var $nvh Navigation */
        $nvh = $vhm->get('navigation');
        // Déclaration des plugins maison pour l'aide de vue de navigation
        $invokables = array(
            'menuPrincipal'  => 'UnicaenApp\View\Helper\Navigation\MenuPrincipal',
            'menuSecondaire' => 'UnicaenApp\View\Helper\Navigation\MenuSecondaire',
            'menuContextuel' => 'UnicaenApp\View\Helper\Navigation\MenuContextuel',
            'menuPiedDePage' => 'UnicaenApp\View\Helper\Navigation\MenuPiedDePage',
            'filAriane'      => 'UnicaenApp\View\Helper\Navigation\FilAriane',
            'plan'           => 'UnicaenApp\View\Helper\Navigation\Plan',
        );
        foreach ($invokables as $key => $value) {
            $nvh->getPluginManager()->setInvokableClass($key, $value);
        }
        
        $eventManager->attach(new ModalListener());
        
        $this->appendSessionRefreshJs($e);
    }

    /**
     * Init session manager.
     * 
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function bootstrapSession($e)
    {
        $session = $e->getApplication()->getServiceManager()->get('Zend\Session\SessionManager');
        $session->start();

        $container = new \Zend\Session\Container('initialized');
        if (!isset($container->init)) {
             $session->regenerateId(true);
             $container->init = 1;
        }
    }



    /**
     * Ajoute au rendu de toutes les vues une ligne de Javascript (en inline)
     * appelant la fonction de rafraîchissement périodique de la session de l'utilisateur.
     * 
     * @param EventInterface $e
     */
    public function appendSessionRefreshJs(EventInterface $e)
    {
        if ($e->getRequest() instanceof ConsoleRequest) {
            return;
        }
        
        $sm     = $e->getApplication()->getServiceManager();
        $period = $sm->get('unicaen-app_module_options')->getSessionRefreshPeriod();
        if ($period <= 0) {
            return;
        }
        
        $basePathHelper = $sm->get('viewhelpermanager')->get('BasePath');
        $url            = $basePathHelper() . "/refresh-session";
        $js             = "$(function() { refreshSession('$url', $period); });";
        
        $sm->get('viewhelpermanager')->get('InlineScript')->offsetSetScript(8,$js);
    }
    
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'ldapPeopleService'    => 'UnicaenApp\Controller\Plugin\LdapPeopleServiceFactory',
                'ldapStructureService' => 'UnicaenApp\Controller\Plugin\LdapStructureServiceFactory',
                'ldapGroupService'     => 'UnicaenApp\Controller\Plugin\LdapGroupServiceFactory',
                'mail'                 => 'UnicaenApp\Controller\Plugin\MailFactory',
                'appInfos'             => 'UnicaenApp\Controller\Plugin\AppInfosFactory',
            ),
            'invokables' => array(
                'multipageForm'         => 'UnicaenApp\Controller\Plugin\MultipageForm',
                'modalInnerViewModel'   => 'UnicaenApp\Controller\Plugin\ModalInnerViewModel',
                'popoverInnerViewModel' => 'UnicaenApp\Controller\Plugin\PopoverInnerViewModel',
                'uploader'              => 'UnicaenApp\Controller\Plugin\Upload\UploaderPlugin',
                'confirm'               => 'UnicaenApp\Controller\Plugin\ConfirmPlugin',
                'messenger'             => 'UnicaenApp\Controller\Plugin\MessengerPlugin',
            ),
        );
    }

    /**
     * 
     * @return array
     * @see ServiceProviderInterface
     */
    public function getServiceConfig()
    {
        return [];
    }

}
