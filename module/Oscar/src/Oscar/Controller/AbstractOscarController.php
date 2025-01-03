<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 10:40
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Oscar\Exception\OscarException;
use Oscar\OscarVersion;
use Oscar\Service\SearchService;
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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Parser;
use UnicaenAuth\Service\UserContext;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\JsonModel;

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
     * @throws OscarException
     */
    protected function getService(ContainerInterface $container, string $id){
        try {
            return $container->get($id);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $this->getLoggerService()->critical("Service Not Found '$id' : " . $e->getMessage());
            throw new OscarException("Erreur de service critique '$id'");
        }
    }
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

    /**
     * @return FlashMessenger
     */
    protected function getFlashMessenger(): FlashMessenger
    {
        return $this->getPluginManager()->get('flashmessenger');
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

    protected function baseJsonResponse(){
        return [
            'version' => OscarVersion::getBuild()
        ];
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
        if( file_exists($path) ) {
            $contentFile = file_get_contents($path);
            if ($contentFile) {
                $parser = new Parser();
                return $parser->parse(file_get_contents($path));
            }
        }
        return [];
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

    /**
     * @param string $error
     * @param \Exception $exception
     * @return Response
     */
    protected function jsonErrorLogged( string $error , \Exception $exception ) :Response
    {
        $this->getLoggerService()->error(sprintf("ERROR '%s' : %s", $error, $exception->getMessage()));
        return $this->jsonError($error);
    }

    protected function jsonError( string $msg ) :Response
    {
        return $this->getResponseInternalError($msg);
    }

    protected function getPutDataJson() :Parameters
    {
        $params = new Parameters();
        $json = json_decode($this->getRequest()->getContent(), true);
        if( !$json ){
            throw new OscarException("Aucune données JSON");
        }
        foreach ($json as $key=>$value) {
            $params->set($key, $value);
        }
        return $params;

    }

    protected function getJsonPosted()
    {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE);
        return $input;
    }
}
