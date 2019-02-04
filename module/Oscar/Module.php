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
            '*',
            'dispatch.error',
            array($this, 'onDispatchError'));

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

    public function onDispatchError( $e ){

        $userInfos = $this->getCurrentUserInfo();
        $base = $userInfos['base'];

        if( $e->getParam('exception') instanceof \Exception ){
            $msg = 'exception: ' . $e->getParam('exception')->getMessage();
        } elseif ( is_string($e->getParam('exception'))) {
            $msg = 'error: ' . $e->getParam('exception');
	} else {
		return;
	}	
        $this->getLogger()->error("$base $msg");
    }
    public function onUserLogin( $e ){

        $dbUser = null;

        $this->getLogger()->addInfo(sprintf('Chargement du bdUser avec identity = %s', (string)$e->getIdentity()));

		if( is_string($e->getIdentity()) ){
			$dbUser = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $e->getIdentity()]);
		} else {
			$dbUser = $this->getEntityManager()->getRepository(Authentification::class)->find($e->getIdentity());
		}

		if( $dbUser ) {
            try {
                $dbUser->setDateLogin(new \DateTime());
                $dbUser->setSecret(md5($dbUser->getId() . '#' . time()));
                $this->getEntityManager()->flush($dbUser);
            } catch (\Exception $e) {
                error_log("Mise à jour du dbUser impossible : " . $e->getMessage());
            }

            /** @var PersonService $personService */
            $personService = $this->_serviceManager->get('PersonService');
            try {
                $person = $personService->getPersonByLdapLogin($dbUser->getUsername());
                $str = $person->log();
            } catch (NoResultException $e) {
                $str = $dbUser->getUsername() . ' - DBUSER';
            } catch (NonUniqueResultException $e ){
                throw new OscarException("Votre fiche personne apparaît en double dans la base de données, veuillez contacter l'administrateur pour que le problème soit corrigé.");
            }

            $this->getServiceActivity()->addInfo(sprintf('%s vient de se connecter à l\'application.',
                $str), $dbUser);
        } else {
            error_log("dbUser manquant !");
        }

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

                if( $controller == 'Console')
                    return;

                $action = $match->getParam('action');
                $uri = method_exists($request, 'getRequestUri') ? $request->getRequestUri() : 'console';
                $userInfos = $this->getCurrentUserInfo();
                $base = $userInfos['base'];
                $method = $request->getMethod();
                $contextId = $match->getParam('id', '?');
                $message = sprintf('%s [%s] %s:%s %s', $base, $method, $controller, $action, $uri);
                $this->getLogger()->debug($message);

            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    protected function getCurrentUserInfo(){

        static $userInfos;
        if( $userInfos === null ){
            $ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

            $userContext = $this->getUserContext();

            if( $this->getUserContext()->getLdapUser() ){
                $auth           = 'ldap';
                $login          = $userContext->getLdapUser()->getUsername();
                $displayName    = $userContext->getLdapUser()->getDisplayName();
            }
            elseif ( $userContext->getDbUser() ){
                $auth           = 'bdd';
                $login          = $userContext->getDbUser()->getUsername();
                $displayName    = $userContext->getDbUser()->getDisplayName();
            } else {
                $auth = "no";
                $displayName = 'Anonymous';
                $login = 'visitor';
            }

            $userInfos = [
                'ip' => $ip,
                'auth' => $auth,
                'username' => $login,
                'display' => $displayName,
                'base' => sprintf('%s@%s (%s:%s)', $login, $ip, $auth, $displayName)
            ];
        }
        return $userInfos;
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

            '# ORGANISATIONS',
            'oscar organizations:sync <connectorkey>' => 'Lance la synchronisation des organization depuis les différents connecteurs.',

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
