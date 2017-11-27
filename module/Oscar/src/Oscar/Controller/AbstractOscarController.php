<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 10:40
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oscar\Entity\LogActivity;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\Person;
use Oscar\Entity\ProjectGrantRepository;
use Oscar\Entity\ProjectRepository;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Service\SearchService;
use UnicaenAuth\Service\UserContext;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Cette classe sert principalement à fournir des accesseurs aux Controlleurs
 * qui l'étende.
 *
 * Class AbstractOscarController
 */
class AbstractOscarController extends AbstractActionController
{
    protected function getConfiguration($key){
        $config = $this->getServiceLocator()->get('Config');
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    throw new \Exception("Clef $path absente dans la configuration");
                }
                $config = $config[$path];
            }
        }
        return $config;
    }

    protected function checkToken(){

    }

    protected function getHttpXMethod(){
        /** @var Request $request */
        $request = $this->getRequest();
        $method = $request->getMethod();
        if( $request->getHeaders()->get('X-Http-Method-Override') ){
            $method = $request->getHeaders()->get('X-Http-Method-Override')->getFieldValue();
        }
        return $method;
    }

    /**
     * @return bool
     */
    protected function isAjax(){
        /** @var Request $request */
        $request = $this->getRequest();
        return $request->isXmlHttpRequest();
    }

    protected function getDefaultContext()
    {
        return $this->params()->fromRoute('controller').':'.$this->params()->fromRoute('action');
    }

    private $_currentPerson;
    protected function getCurrentPerson(){
        if( $this->_currentPerson === null ){
            $this->_currentPerson = "intruder";

            $this->_currentPerson = $this->getOscarUserContext()->getCurrentPerson();
        }
        return $this->_currentPerson;
    }

    protected function ajaxResponse($datas)
    {
        $r = new JsonModel();
        $r->setVariables($datas);

        return $r;
    }

    protected function getHttpResponse($code, $content = null)
    {
        $response = new Response();
        $response->setStatusCode($code);
        if ($content !== null) {
            $response->setContent($content);
        } else {
            $response->setContent($response->getReasonPhrase());
        }

        return $response;
    }

    protected function isAllow($role)
    {
        $roles = $this->getUserContext()->getIdentityRoles();

        return isset($roles[$role]);
    }

    protected function getResponseNotImplemented($message = null)
    {
        return $this->getHttpResponse(Response::STATUS_CODE_501, $message);
    }
    protected function getResponseNotFound($message = null)
    {
        return $this->getHttpResponse(Response::STATUS_CODE_404, $message);
    }
    protected function getResponseOk($message = null)
    {
        return $this->getHttpResponse(Response::STATUS_CODE_200, $message);
    }
    protected function getResponseInternalError($message = null)
    {
        return $this->getHttpResponse(Response::STATUS_CODE_500, $message);
    }
    protected function getResponseBadRequest($message = null)
    {
        return $this->getHttpResponse(Response::STATUS_CODE_400, $message);
    }
    protected function getResponseDeprecated($message = null)
    {
        return $this->getResponseBadRequest("Cette action est 'dépréciée.");
    }


    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceLocator()->get('logger');
    }
    
    /**
     * @return \Oscar\Service\AccessResolverService
     */
    protected function getAccessResolverService()
    {
        return $this->getServiceLocator()->get('AccessResolverService');
    }

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
    }

    /***
     * @return SearchService
     */
    protected function getSearchProjectService()
    {
        return $this->getServiceLocator()->get('Search');
    }

    /**
     * @return ProjectService
     */
    protected function getProjectService()
    {
        return $this->getServiceLocator()->get('ProjectService');
    }

    /**
     * @return ProjectGrantService
     */
    protected function getActivityService()
    {
        return $this->getServiceLocator()->get('ProjectGrantService');
    }

    /**
     * @return ActivityTypeService
     */
    protected function getActivityTypeService()
    {
        return $this->getServiceLocator()->get('ActivityTypeService');
    }

    /**
     * @return UserContext
     */
    protected function getUserContext()
    {
        return $this->getServiceLocator()->get('authUserContext');
    }

    /**
     * @return PersonService
     */
    protected function getPersonService()
    {
        return $this->getServiceLocator()->get('PersonService');
    }

    /**
     * @return OrganizationService
     */
    protected function getOrganizationService()
    {
        return $this->getServiceLocator()->get('OrganizationService');
    }

    /**
     * @return ActivityLogService
     */
    protected function getActivityLogService()
    {
        return $this->getServiceLocator()->get('ActivityLogService');
    }

    /**
     * @return ProjectGrantService
     */
    protected function getProjectGrantService()
    {
        return $this->getServiceLocator()->get('ProjectGrantService');
    }

    /**
     * @return \UnicaenAuth\Entity\Ldap\People
     */
    protected function getLdapUser()
    {
        return $this->getUserContext()->getLdapUser();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Repositories
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return ProjectRepository
     */
    protected function getProjectRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    /**
     * @return ProjectGrantRepository
     */
    protected function getGrantRepository()
    {
        return $this->getEntityManager()->getRepository('\Oscar\Entity\ProjectGrant');
    }

    /**
     * @return ActivityLogRepository
     */
    protected function getActivity()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Activity');
    }


    protected function log($message=null)
    {
        if ($message === null) {
            $controller = $this->params()->fromRoute('controller');
            $method = $this->params()->fromRoute('action');
            $user = 'Unknow User';
            if ($this->getUserContext()->getLdapUser()) {
                $user = $this->getUserContext()->getLdapUser()->getDisplayName();
            }
            elseif ($this->getUserContext()->getDbUser()) {
                $user = $this->getUserContext()->getDbUser()->getUsername();
            }

            $message = $controller.'::'.$method . ' by ' . $user;
        }
        $this->addActivity($message);
    }

    protected function addActivity(
        $message,
        $level = LogActivity::DEFAULT_LEVEL,
        $type = LogActivity::TYPE_DEBUG,
        $context = LogActivity::DEFAULT_CONTEXT,
        $contextId = LogActivity::DEFAULT_CONTEXTID,
        $userId = LogActivity::DEFAULT_USER,
        array $data = null)
    {
        $this->getActivity()->addActivity($message, $level, $type, $context, $contextId, $userId, $data);
    }
}
