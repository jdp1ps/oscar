<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oscar;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Monolog\Logger;
use Oscar\Entity\LogActivity;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\Authentification;
use Oscar\Exception\OscarException;
use Oscar\Service\ActivityLogService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use UnicaenAuth\Authentication\Adapter\Ldap;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Provider\Identity\ChainEvent;
use UnicaenAuth\Service\UserContext;
use Zend\Authentication\Result;
use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use ZfcUser\Authentication\Adapter\AdapterChainEvent;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    protected $_serviceManager;

    protected function getServiceManager()
    {
        return $this->_serviceManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
    }

    /**
     * @return UserContext
     */
    protected function getUserContext()
    {
        return $this->getServiceManager()->get('authUserContext');
    }

    /**
     * @return ActivityLogService
     */
    protected function getServiceActivity()
    {
        return $this->getServiceManager()->get('ActivityLogService');
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceManager()->get('Logger');
    }

    /**
     * @return ActivityLogRepository
     */
    protected function getActivityRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Activity');
    }

    // FIX : ZendFramework 3
    public function init(ModuleManager $manager){

        $eventManager = $manager->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();


        //$sharedEventManager->attach('*', UserAuthenticatedEvent::PRE_PERSIST, [$this, 'onStar'], 200);
        $sharedEventManager->attach('*', '*', [$this, 'onDispatch'], 100);

        // ERREURS
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError'], 100);
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_RENDER_ERROR, [$this, 'onError'], 100);

        // Route
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], 100);
    }

    public function onDispatch( Event $event )
    {
        static $handler;

        if( $handler === null ){
            $myfile = fopen("/tmp/oscar-debug.log", "a") or die("Unable to open file!");
        }

        if( $event->getName() == 'authentication.ldap.error'){
            fwrite($myfile, sprintf("%s\t%s\n", date('Y-m-d H:i:s'), "LDAP authentification fail for : " . $event->getParam('identity', 'undefined user')) );

            /** @var Result $result */
            $result = $event->getParam('result', []);
            if( $result ){
                foreach ($result->getMessages() as $m) {
                    fwrite($myfile, sprintf("%s\t%s\n", date('Y-m-d H:i:s'), $m ));

                }
            }
        } else {

            fwrite($myfile, sprintf("%s\t[def]%s\n", date('Y-m-d H:i:s'), $event->getName()) );
        }
    }

    protected function logError( $msg, $person="utilisateurX" ){
        static $handler, $person;

        if( $handler === null ){
            $myfile = fopen("/tmp/oscar-debug.log", "a") or die("Unable to open file!");
        }
        fwrite($myfile, sprintf("%s\t%s", date('Y-m-d H:i:s'), $msg));
    }

    public function onUserLogin( Event $event){
        /** @var Logger $logger */
        $msg = "Evt Manager " . $event->getName() ."\n";
        $this->error($msg);
    }

    public function onRoute( MvcEvent $mvcEvent ){
//        die();
//        /** @var Logger $logger */
//        $logger = $mvcEvent->getApplication()->getServiceManager()->get('Logger');
//        $time = date('Y-m-d H:i:s');
//
//        if (php_sapi_name() == "cli") {
//            // Do not execute HTTPS redirect in console mode.
//            $person = "~CLI";
//            $uri = "\$command-line";
//        } else {
//            /** @var OscarUserContext $oscarUserContext */
//            $oscarUserContext = $mvcEvent->getApplication()->getServiceManager()->get(OscarUserContext::class);
//            $person = $oscarUserContext->getCurrentUserLog();
//            $uri = $mvcEvent->getRequest()->getUri();
//        }
//        $msg = sprintf("%s access by %s to %s", $time, $person, $uri);
//        $logger->error($msg);
    }



    public function onError(MvcEvent $event) {
        /** @var Logger $logger */
        $logger = $event->getApplication()->getServiceManager()->get('Logger');

        /** @var \Exception $exception */
        $exception = $event->getParam('exception');
        if( $exception != null ){
            $exceptionName = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = $exception->getTraceAsString();
        }

        $errorMessage = $event->getError();
        $controllerName = $event->getController();

        $logger->error($controllerName." : " . $errorMessage ."\n--- \n " . $trace);
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleBanner(AdapterInterface $console)
    {
        return "OSCAR";
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            '# AUTHENTIFICATION',
            'oscar auth:promote <login> [<role>]' => 'Ajoute Un rôle <role> au compte <login>, si le role n\'est pas précisé, une liste de choix s\'affiche',
            'oscar auth:pass <login> [--ldap]' => 'Mets à jour le mot de passe du compte <login>, l\'option --ldap permet de déléger le contrôle du mot de passe à ldap' ,
            'oscar auth:add' => 'Cré un nouvel utilisateur.',
            'oscar auth:list' => 'Liste des utilisateurs.',
            'oscar auth:info <login> [--org] [--act]' => "Affiche les détails du compte. L'option --org affiche les organisations où la personne est identifiée avec un rôle, l'option --act fait la même chose au niveau activité.",

            '# CONFIGURATION',
            'oscar test:config' => 'Évaluation de la configuration',
            'oscar test:mailer' => 'Lance le test du mailer',
//            'oscar check:authentification <login> <pass>' => "Évaluation de l'authentification",
            //'oscar check:connector' => 'Cré un nouvel utilisateur.',

            '# PERSONNES',
            'oscar persons:sync <connectorkey>' => 'Lance la synchronisation des personnes depuis les différents connecteurs.',
            'oscar persons:search:connector <connector> <value>' => 'Recherche les personnes ayant pour le connecteur <connector> la valeur <value>',
            'oscar persons:search:build' => 'Reconstruction de l\'index de recherche (ElasticSearch)' ,
            'oscar person:search <expression>' => 'Lance une recherche sur les personnes' ,

            '# ORGANISATIONS',
            'oscar organizations:search:build' => 'Reconstruction de l\'index de recherche (ElasticSearch)',
            'oscar organizations:sync <connectorkey>' => 'Lance la synchronisation des organizations depuis les différents connecteurs.',

            '# NOTIFICATIONS',
            'oscar notifications:generate <tagid>' => 'Génère les notifications pour l\'activité, la valeur \'all\' permet de générer les notifications pour toutes les activités',
            'oscar notifications:mails:persons' => 'Génère les mails pour les personnes ayant des notifications non-lues et les envois',
            'oscar notifications:mails:person <idperson> [-f|--force]' => 'Génère les mails pour la personne ayant des notifications non-lues et les envois',

            '# ACTIVTÉS',
            'oscar activity:search:build' => "Reconsruction de l'index de recherche",
            'oscar activity:search <expression> <objet>' => "Recherche l'<objet>(activity|person|project) avec l'expression <expression> dans les activités",

            '# IMPORT',
            'oscar authentifications:sync <jsonpath>' => "Charge les comptes d'authentification depuis la source JSON",
            'oscar personsjson:sync <fichier>' => "Charge les personnes depuis le fichier source JSON",
            'oscar activity:csvtojson <fichier> <config> [-f|--force] [--cp] [--co] [--cpr] [--cor]' => "Converti les données CSV au format JSON à partir de la configuration",
            'oscar organizationsjson:sync <fichier>' => "Charge les organisations depuis le fichier source JSON",
            'oscar activity:sync <fichier>' => "Charge les activités depuis le fichier CSV spécifié",

            '# MAINTENANCE',
            'oscar patch checkPrivilegesJSON'   => "Mise à jour automatique de la liste des privilèges",
            'oscar patch fixSequenceAutoNum'    => "Recalcule les sequencesID des tables",
            'oscar version'    => "Affiche la version courante de Oscar",

        ];
    }
}
