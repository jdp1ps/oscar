<?php

namespace Oscar\Controller;

use Oscar\Entity\Person;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OrganizationToJsonConnectorFormatter;
use Oscar\Formatter\PersonToJsonConnectorFormatter;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class ApiController extends AbstractOscarController implements UseOscarUserContextService, UseOscarConfigurationService, UsePersonService, UseLoggerService, UseOrganizationService
{
    use UseOscarUserContextServiceTrait, UseOscarConfigurationServiceTrait, UsePersonServiceTrait, UseLoggerServiceTrait, UseOrganizationServiceTrait;

    /**
     * Gestion des accès aux API : création de compte et configuration
     */
    public function adminManageAccessAction(){

        $this->getOscarUserContextService()->check(Privileges::DROIT_API_ACCESS);
        $apis = [
            'persons' => "Personnes",
            'organizations' => "Organisations",
            'roles' => "Affectations",
            'activities' => "Activités",
        ];


        if( $this->isAjax() ){
            $datas = $this->getOscarConfigurationService()->getEditableConfKey('apiaccess', []);
            switch ($this->getHttpXMethod()) {
                case "GET" :
                    $output = [
                        'datas' => $datas,
                    ];
                    return $this->jsonOutput($output);
                    break;

                case "POST" :
                    $login = $this->params()->fromPost('login');
                    $pass = $this->params()->fromPost('pass');
                    $apis = $this->params()->fromPost('apis');

                    $datas[$login] = ['pass' => $pass, 'apis' => explode(',', $apis)];
                    $this->getOscarConfigurationService()->saveEditableConfKey('apiaccess', $datas);
                    return $this->getResponseOk();

                case "DELETE" :
                    $id = $this->params()->fromQuery('id');
                    if( array_key_exists($id, $datas) ){
                        unset($datas[$id]);
                        $this->getOscarConfigurationService()->saveEditableConfKey('apiaccess', $datas);
                        return $this->getResponseOk();
                    }
                    return $this->getResponseInternalError("Impossible de supprimer cet accès");
                    break;
            }
        }

        return [
            'apis' => $apis
        ];
    }


    /**
     * Contrôle d'accès à l'API. Pour le moment, utilise AuthBasic
     * @param $api
     * @throws OscarException
     */
    protected function checkApiAcces($api){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Oscar');
            header('HTTP/1.0 401 Unauthorized');
            echo "Accès à l'API Oscar limitée";
            exit;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->getLoggerService()->info("[API OSCAR] access /$api from $ip");

        // Vérification accès
        try {
            $apiaccess = $this->getOscarConfigurationService()->getEditableConfKey('apiaccess');

            if( is_array($apiaccess) ){
                $user = $_SERVER['PHP_AUTH_USER'];
                $pass = $_SERVER['PHP_AUTH_PW'];

                if( !array_key_exists($user, $apiaccess) ){
                    $this->getLoggerService()->error("[API OSCAR] Identifiant inconnnu $user.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }

                if( $apiaccess[$user]['pass'] != $pass ){
                    $this->getLoggerService()->error("[API OSCAR] Mot de passe incorrect pour $user.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }

                if( !in_array('persons', $apiaccess[$user]['apis']) ){
                    $this->getLoggerService()->error("[API OSCAR] $user n'a pas accès à l'API $api.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }
            } else {
                $this->getLoggerService()->error("[API OSCAR] L'API oscar n'est pas configurée");
                throw new OscarException("L'accès à l'API Oscar est mal configuré");
            }
        } catch (OscarException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->getLoggerService()->error("[OSCAR API] Erreur inconnue : " . $e->getMessage());
            throw new OscarException("Accès interdit l'API Oscar");
        }

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    ///                         ~ API ~
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function personsAction(){
        $start = microtime(true);

        try {
            $this->checkApiAcces('persons');

            $persons = [];
            $personToJsonFormatter = new PersonToJsonConnectorFormatter();

            /** @var Person $p */
            foreach( $this->getPersonService()->getPersons() as $p ){
                $persons[] = $personToJsonFormatter->format($p);
            }

            $datas = [
                "version"         => OscarVersion::getBuild(),
                "datecreated"     => date('c'),
                'time'            => (microtime(true) - $start),
                'total'           => count($persons),
                'persons'         => $persons
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function personAction(){
        try {
            $start = microtime(true);
            $this->checkApiAcces('persons');
            $personToJsonFormatter = new PersonToJsonConnectorFormatter();
            $uid = $this->params()->fromRoute("id");
            try {
                $person = $this->getPersonService()->getPerson($uid);
            } catch (\Exception $e) {
                return $this->getResponseNotFound(_("Personne non trouvée"));
            }

            $datas = [
                "version"         => OscarVersion::getBuild(),
                "datecreated"     => date('c'),
                'time'            => (microtime(true) - $start),
                "uid"             => $uid,
                "person"          => $personToJsonFormatter->format($person)
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function organizationsAction(){
        try {
            $this->checkApiAcces('organizations');
            $start = microtime(true);
            $organizations = [];
            $organizationToJsonFormatter = new OrganizationToJsonConnectorFormatter();

            /** @var Person $p */
            foreach( $this->getOrganizationService()->getOrganizations() as $o ){
                $organizations[] = $organizationToJsonFormatter->format($o);
            }

            $datas = [
                "version"         => OscarVersion::getBuild(),
                "datecreated"     => date('c'),
                'time'            => (microtime(true) - $start),
                'total'           => count($organizations),
                'organizations'         => $organizations
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function organizationAction(){
        try {
            $this->checkApiAcces('organizations');
            $start = microtime(true);
            $organizationToJsonFormatter = new OrganizationToJsonConnectorFormatter();
            $uid = $this->params()->fromRoute("id");
            try {
                $organization = $this->getOrganizationService()->getOrganization($uid);
            } catch (\Exception $e) {
                return $this->getResponseNotFound(_("Organisation non trouvée"));
            }

            $datas = [
                "version"         => OscarVersion::getBuild(),
                "datecreated"     => date('c'),
                'time'            => (microtime(true) - $start),
                "uid"             => $uid,
                "person"          => $organizationToJsonFormatter->format($organization)
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function helpAction(){
        die("Consultez l'aide technique pour obtenir");
    }

    public function apiAction()
    {
        $action = $this->params()->fromQuery('a');
        $result = [];

        if (!$action) {
            $this->response->setStatusCode(500);
            $result['error'] = 'Mauvais utilisation';
        } else {
            try {
                $projectId = $this->params()->fromQuery('projectId');
                switch ($action) {
                    case 'partners':

                        if ($this->getRequest()->isPost()) {
                            $dateStart = $this->params()->fromPost('dateStart');
                            $dateEnd = $this->params()->fromPost('dateEnd');
                            $role = $this->params()->fromPost('role');
                            $enrolId = $this->params()->fromPost('enrolid');

                            $this->addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd);
                        } elseif ($this->getRequest()->isDelete()) {
                            var_dump($this->getRequest()->getParameters());
                            $projectPartner = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectPartner')->find($this->param('idRole'));
                            $this->getEntityManager()->remove($projectPartner);
//                            throw new \Exception("SUPPRESSION");
                        }

                        $result = $this->getProject($projectId)['partners'];

                        break;

                    case 'members':
                        $result = $this->getProject($projectId)['members'];
                        break;

                    case 'identification':
                        $result = $this->getProject($projectId);
                        break;

                    case 'delete-partner':
                        $result = $this->deleteRole();
                        break;

                    default:
                        throw new \Exception('Unknow API action');
                        break;
                }
            } catch (\Exception $ex) {
                $this->response->setStatusCode(500);
                $result['error'] = $ex->getMessage();
            }
        }

        return new JsonModel($result);
    }

    protected function addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd)
    {
        $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId);
        $organisation = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($enrolId);
        $partner = new ProjectPartner();
        $partner->setProject($project)
            ->setOrganization($organisation)
            ->setDateStart(new \DateTime($dateStart))
            ->setDateEnd(new \DateTime($dateEnd))
            ->setRole($role);
        $this->getEntityManager()->persist($partner);
        $this->getEntityManager()->flush();
    }

    protected function deleteRole()
    {
        $projectId = $this->params()->fromQuery('projectId');

        return ['response' => 'TOTO '.$projectId];
    }

    protected function getProject($projectId)
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->getSingle($projectId)->toArray();
    }

    protected function getProjectMembers($projectId)
    {
        return $this->getProject($projectId)['members'];
    }

    protected function getProjectPartners($projectId)
    {
        return $this->getProject($projectId)['partners'];
    }

    /**
     * @return ViewModel
     */
    public function searchStaffAction()
    {
        $sl = $this->getServiceLocator();
        $search = $this->getRequest()->getQuery()->get('q');

        if (strlen($search) >= 4) {
            $t = $sl->get('PersonnelService');
            /* @var \Application\Service\PersonnelService */
            $result = $t->searchStaff($search);
            return new JsonModel($result);
        } else {
            $response = new Response();
            $response->setStatusCode(400);
            return $response;
        }
    }
}
