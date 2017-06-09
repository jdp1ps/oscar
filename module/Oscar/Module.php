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
use Doctrine\ORM\NoResultException;
use Oscar\Entity\LogActivity;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\Authentification;
use Oscar\Service\ActivityLogService;
use Oscar\Service\PersonService;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Service\UserContext;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;

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
     * @return ActivityLogRepository
     */
    protected function getActivityRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Activity');
    }



    public function onBootstrap(MvcEvent $e)
    {
        $this->_serviceManager = $e->getApplication()->getServiceManager();

        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
                'UnicaenAuth\Service\User',
                UserAuthenticatedEvent::PRE_PERSIST,
                array($this, 'onUserLogin'),
                100);

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            '*',
            'ldap.error',
            function($data) {
                /*var_dump($data->getTarget());
                echo "DATA : " . $data->getName();*/
            },
            100);

        // Envoi des erreurs dans les LOGS
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            '*', 'dispatch.error', function( $e ){
            if( $e->getParam('exception') instanceof \Exception )
                $e->getApplication()->getServiceManager()->get('Logger')->error($e->getParam('exception')->getMessage());
        });

        // Log des accès
        $e->getApplication()->getEventManager()->attach('*', function ($e) {
            $this->trapEvent($e);
        });
    }

    public function onUserLogin( UserAuthenticatedEvent $e ){
        $user = $e->getDbUser();
        try {
            $user->setDateLogin(new \DateTime());
            $user->setSecret(md5($user->getId().'#'.time()));
            $this->getEntityManager()->flush($user);
        } catch( \Exception $e ){}

        /** @var PersonService $personService */
        $personService = $this->_serviceManager->get('PersonService');
        try {
            $person = $personService->getPersonByLdapLogin($user->getUsername());
            $str = $person->log();
        } catch( NoResultException $e ){
            $str = $user->getUsername(). ' - DBUSER';
        }
        $this->getServiceActivity()->addInfo(sprintf('%s vient de se connecter à l\'application.', $str), $user);
    }

    protected function trapEvent($event)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $sm = $event->getApplication()->getServiceManager();

        if ($event->getName() === 'route') {
            try {
                $sm = $event->getApplication()->getServiceManager();

                /** @var UserContext */
                $userContext = $sm->get('authUserContext');

                /** @var EntityManager $entityManager */
                $entityManager = $sm->get('doctrine.entitymanager.orm_default');

                /** @var ActivityLogRepository $activity */
                $activity = $entityManager->getRepository('Oscar\Entity\LogActivity');

                /** @var RouteMatch $match */
                $match = $event->getParams()['route-match'];

                $controller = $match->getParam('controller');
                $action = $match->getParam('action');
                $uri = method_exists($request, 'getRequestUri') ? $request->getRequestUri() : 'console';
                $ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
                $user = $userContext->getLdapUser() ? $userContext->getLdapUser()->getDisplayName() : 'Anonymous';
                $userid = $userContext->getDbUser() ? $userContext->getDbUser()->getid() : -1;
                $contextId = $match->getParam('id', '?');
                $message = sprintf('%s@%s:(%s) %s:%s %s', $ip, $userid, $user, $controller, $action, $uri);
                $sm->get('Logger')->info($message);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
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
        return "
     `:+sssso/-`       `-/osssso//.       `-/ossss+:.        :////:        /////////-.`    `.`          `.`    ```.                   `....`
   :hNMMNmmmNMMmo`    -dMMNmmmmNNMo     -yNMMNmmmmNNM       /MMMMMM/       MMMMNNNNMMMmo    :-         `:-   ----::                 .-:....::`
  oMMMN+.  `-hMMMm.   mMMM:`   .-/:    +NMMNs-`   .-+      .NMMNmMMN.      MMMMs..-+MMMM/   -:`       .:-       `:-                .:.     `:-
 -MMMM/       dMMMh   mMMMho/--.`     -MMMM+              `dMMM::MMMd`     MMMMo   -MMMM:   .:.      -:.        -:`               .:.       ::
 oMMMM        oMMMM   .ymMMMMMNmdy-   oMMMM               yMMMo  oMMMy     MMMMmdddNMNy:     ::    `-:`         :-                ::       `:-
 /MMMM.       yMMMm     .-/+shdMMMN-  /MMMM.             +MMMN/::/NMMM+    MMMMmyhmMMNh-     -:`  `::`         .:.               .:.       -:`
 `mMMMh`     :MMMM/   :`      `yMMMo   dMMMd.       `   -NMMMMMMMMMMMMN-   MMMMo  `oMMMN/    `:- .:-           ::                .:.      .:.
  .yMMMms++ohNMMm/    MNdyo+++sNMMm.   `sMMMNho+++shN  `mMMM/....../MMMm`  MMMMo    +MMMN:    ::.:-        ```.:-```      `-`     ::`   `-:.
    -ohmNMMMNdy/`     shdmNMMMNmh+`      .+ydNMMMNmhs  oddds        sdddo  dddd/     /dddh.   .--.        `--------.      --      `.-::--.`
    ";
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            '# AUTHENTIFICATION',
            'oscar auth:promote <login> <role>' => 'Ajoute Un rôle <role> au compte <login>',
            'oscar auth:pass <login> <newpass>' => 'Mets à jour le mot de passe du compte <login>',
            'oscar auth:add <login> <email> <pass> <displayname>' => 'Cré un nouvel utilisateur.',

            '# PERSONNES',
            'oscar persons:sync <connectorkey>' => 'Lance la synchronisation des personnes depuis les différents connecteurs.',

            '# ORGANISATIONS',
            'oscar organizations:sync <connectorkey>' => 'Lance la synchronisation des organization depuis les différents connecteurs.',

            '# ACTIVTÉS',
            'oscar activity:search:build' => "Reconsruction de l'index de recherche",
            'oscar activity:search <expression> <objet>' => "Recherche l'<objet>(activity|person|project) avec l'expression <expression> dans les activités"
            ];
    }
}
