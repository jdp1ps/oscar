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
use Oscar\Exception\OscarException;
use Oscar\OscarVersion;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Service\SearchService;
use Oscar\Service\SessionService;
use Oscar\Service\UserParametersService;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\Yaml\Parser;
use UnicaenAuth\Service\UserContext;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

/**
 * Cette classe sert principalement à fournir des accesseurs aux Controlleurs
 * qui l'étende.
 *
 * Class AbstractOscarController
 */
class AbstractOscarController extends AbstractActionController implements UseOscarConfigurationService, UseOscarUserContextService, UseLoggerService, UseEntityManager, UseActivityLogService
{
    use UseOscarConfigurationServiceTrait, UseOscarUserContextServiceTrait, UseLoggerServiceTrait, UseEntityManagerTrait, UseActivityLogServiceTrait;


    /**
     * Retourne le format de la requête sous la forme d'une chaîne.
     * NB:  Pour le moment, sert uniquement à detecter le format JSON.
     *
     * @return string
     */
    public function getRequestFormat() :string
    {
        if( $this->getRequest()->isXmlHttpRequest() )
            $format = 'json';
        else
            $format = $this->params()->fromQuery('format', '');

        return $format;
    }

    public function oscarRest( $default, $get, $post=null, $delete=null)
    {
        $format = $this->params()->fromQuery('format', 'html');

        if ($this->isAjax()) {
            $format = 'json';
        }

        if ($format == 'html') {
            return $default();
        } else {
            $method = $this->getHttpXMethod();
            $fakeAction = $this->params()->fromPost('action', null);

            if( $fakeAction == 'delete' ){
                $method = 'DELETE';
            }

            try {
                switch( $method ){
                    case "GET" :
                        $datas = $get();
                        break;

                    case "POST" :
                        if( !is_callable($post) ) {
                            return $this->getResponseNotImplemented();
                        }
                        $datas = $post();
                        break;

                    case "DELETE" :
                        if( !is_callable($delete) ) {
                            return $this->getResponseNotImplemented();
                        }
                        try {
                            $datas = $delete();
                        }catch (\Exception $e){
                            return $this->getResponseInternalError($e->getMessage());
                        }
                        break;

                    default:
                        return $this->getResponseBadRequest("Mauvaise méthod $method");
                }
                $datas['version'] = OscarVersion::getBuild();
                return $this->jsonOutput($datas);
            } catch (\Exception $e){
                return $this->getResponseInternalError($e->getMessage());
            }
        }
    }

    protected function getYamlConfigPath(){
        $dir = realpath(__DIR__.'/../../../../../config/autoload/');
        $file = $dir.'/oscar-editable.yml';

        if( !file_exists($file) ){
            if( !is_writeable($dir) ){
                throw new OscarException("Impossible d'écrire la configuration dans le dossier $dir");
            }
        }
        else if (!is_writeable($file)) {
            throw new OscarException("Impossible d'écrire le fichier $file");
        }
        return $file;
    }

    protected function getEditableConfRoot(){
        $path = $this->getYamlConfigPath();
        if( file_exists($path) ){
            $parser = new Parser();
            return $parser->parse(file_get_contents($path));
        } else {
            return [];
        }
    }

    protected function getEditableConfKey($key, $default = null){
        $conf = $this->getEditableConfRoot();
        if( array_key_exists($key, $conf) ){
            return $conf[$key];
        } else {
            return $default;
        }
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

    protected function getCurrentPerson(){
        return $this->getOscarUserContextService()->getCurrentPerson();
    }

    protected function ajaxResponse($datas)
    {
        $r = new JsonModel();
        $r->setVariables($datas);

        return $r;
    }

    protected function jsonOutput($datas){
        return $this->ajaxResponse($datas);
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
        $roles = $this->getOscarUserContextService()->getIdentityRoles();

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
    protected function getResponseUnauthorized($message = "Vous n'avez pas les privilèges suffisants")
    {
        return $this->getHttpResponse(Response::STATUS_CODE_401, $message);
    }
}
