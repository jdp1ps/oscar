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
use UnicaenAuth\Authentication\Adapter\Ldap;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Service\UserContext;
use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
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

        // On capte l'authentification
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            //'ZfcUser\Authentication\Adapter\AdapterChain',
            "*",
            'authenticate.success',
            array($this, 'onUserLogin'),
           100
        );

        // todo Remplacer l'étoile si possible
        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            '*',
            'authentication.ldap.error',
            array($this, 'onAuthentificationError'),
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

    /**
     * @param $event Event
     */
    public function onAuthentificationError($event){
        $msg = preg_replace('/\[0x\d* \((.*)\):/','$1', $event->getParam('result')->getMessages()[1]);
        $this->getServiceManager()->get('Logger')->error($msg);
    }

    public function onUserLogin( $e ){

        $dbUser = null;

		if( is_string($e->getIdentity()) ){
			$dbUser = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $e->getIdentity()]);
		} else {
			$dbUser = $this->getEntityManager()->getRepository(Authentification::class)->find($e->getIdentity());
		}

        try {
            $dbUser->setDateLogin(new \DateTime());
            $dbUser->setSecret(md5($dbUser->getId() . '#' . time()));
            $this->getEntityManager()->flush($dbUser);
        } catch (\Exception $e) {

        }

        /** @var PersonService $personService */
        $personService = $this->_serviceManager->get('PersonService');
        try {
            $person = $personService->getPersonByLdapLogin($dbUser->getUsername());
            $str = $person->log();
        } catch (NoResultException $e) {
            $str = $dbUser->getUsername() . ' - DBUSER';
        }
        $this->getServiceActivity()->addInfo(sprintf('%s vient de se connecter à l\'application.',
            $str), $dbUser);

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

                if( $userContext->getLdapUser() ){
                    $user = $userContext->getLdapUser()->getDisplayName();
                }
                elseif ( $userContext->getDbUser() ){
                    $user = $userContext->getDbUser()->getDisplayName();
                } else {
                    $user = 'Anonymous';
                }

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

            '# CONFIGURATION',
            //'oscar check:config' => 'Évaluation de la configuration',
            'oscar check:authentification <login> <pass>' => "Évaluation de l'authentification",
            //'oscar check:connector' => 'Cré un nouvel utilisateur.',

            '# PERSONNES',
            'oscar persons:sync <connectorkey>' => 'Lance la synchronisation des personnes depuis les différents connecteurs.',

            '# ORGANISATIONS',
            'oscar organizations:sync <connectorkey>' => 'Lance la synchronisation des organization depuis les différents connecteurs.',

            '# ACTIVTÉS',
            'oscar activity:search:build' => "Reconsruction de l'index de recherche",
            'oscar activity:search <expression> <objet>' => "Recherche l'<objet>(activity|person|project) avec l'expression <expression> dans les activités",

            '# IMPORT',
            'oscar authentifications:sync <jsonpath>' => "Charge les comptes d'authentification depuis la source JSON",
            'oscar personsjson:sync <fichier>' => "Charge les personnes depuis le fichier source JSON",
            'oscar activity:sync <fichier>' => "Charge les activités depuis le fichier CSV spécifié",
            ];
    }
}
