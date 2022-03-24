<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Person;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OrganizationToJsonConnectorFormatter;
use Oscar\Formatter\PersonToJsonConnectorFormatter;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseActivityService;
use Oscar\Traits\UseActivityServiceTrait;
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
class ApiController extends AbstractOscarController implements UseOscarUserContextService, UseOscarConfigurationService, UsePersonService, UseLoggerService, UseOrganizationService, UseActivityService
{
    use UseOscarUserContextServiceTrait, UseOscarConfigurationServiceTrait, UsePersonServiceTrait, UseLoggerServiceTrait, UseOrganizationServiceTrait, UseActivityServiceTrait;

    public function activityTypeAction(){
        $out = $this->baseJsonResponse();
        $out['description'] = "Type d'activité configurées dans Oscar";

        $types = $this->getActivityService()->getActivityTypesTree(true);

        $out['types-activities'] = $types;
        return $this->jsonOutput($out);
    }

    public function activityTypePcruAction(){
        $out = $this->baseJsonResponse();
        $out['description'] = "Type d'activité PCRU";

        $types = $this->getActivityService()->getActivityTypesPcru(true);

        $out['types-activities-pcru'] = $types;
        return $this->jsonOutput($out);
    }

    public function activityAction()
    {
        // Expression recherchée
        $search = $this->params()->fromQuery('s', '');
        $format = $this->params()->fromQuery('f', 'json');
        $output = $this->params()->fromQuery('o', 'simple');

        $response = $this->baseJsonResponse();
        $response['query'] = [
            'search' => $search,
            'format' => $format,
            'output' => $output,
        ];

        if( strlen($search) < 2 ){
            $this->getLoggerService()->warning("Recherche trop courte pour '$search'");
            return $this->getResponseBadRequest(_('Votre recherche doit contenir au moins 3 caractères'));
        }

        $searchResults = $this->getActivityService()->search($search);
        $activities = [];
        /** @var Activity $activity */
        foreach ($this->getActivityService()->getActivitiesByIds($searchResults) as $activity){
            $activities[] = [
                'id' => $activity->getOscarNum(),
                'text' => $activity->getFullLabel()
            ];
        }
        $response['activities'] = $activities;

        return $this->jsonOutput($response);
    }

    /**
     * Gestion des accès aux API : création de compte et configuration
     */
    public function adminManageAccessAction()
    {

        $this->getOscarUserContextService()->check(Privileges::DROIT_API_ACCESS);
        $apis = [
            'persons' => "Personnes",
            'organizations' => "Organisations",
            'roles' => "Affectations",
            'activities' => "Activités",
        ];

        $formats = $this->getOscarConfigurationService()->getApiFormats([]);

        if ($this->isAjax()) {
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
                    $strategies = json_decode($this->params()->fromPost('strategies'), JSON_OBJECT_AS_ARRAY);

                    $datas[$login] = [
                        'pass' => $pass,
                        'apis' => explode(',', $apis),
                        'strategies' => $strategies
                    ];

                    $this->getOscarConfigurationService()->saveEditableConfKey('apiaccess', $datas);
                    return $this->getResponseOk();

                case "DELETE" :
                    $id = $this->params()->fromQuery('id');
                    if (array_key_exists($id, $datas)) {
                        unset($datas[$id]);
                        $this->getOscarConfigurationService()->saveEditableConfKey('apiaccess', $datas);
                        return $this->getResponseOk();
                    }
                    return $this->getResponseInternalError("Impossible de supprimer cet accès");
                    break;
            }
        }

        return [
            'apis' => $apis,
            'formats' => $formats
        ];
    }


    /**
     * Contrôle d'accès à l'API. Pour le moment, utilise AuthBasic
     * @param $api
     * @throws OscarException
     */
    protected function checkApiAcces($api)
    {
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

            if (is_array($apiaccess)) {
                $user = $_SERVER['PHP_AUTH_USER'];
                $pass = $_SERVER['PHP_AUTH_PW'];

                if (!array_key_exists($user, $apiaccess)) {
                    $this->getLoggerService()->error("[API OSCAR] Identifiant inconnnu $user.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }

                if ($apiaccess[$user]['pass'] != $pass) {
                    $this->getLoggerService()->error("[API OSCAR] Mot de passe incorrect pour $user.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }

                if (!in_array('persons', $apiaccess[$user]['apis'])) {
                    $this->getLoggerService()->error("[API OSCAR] $user n'a pas accès à l'API $api.");
                    throw new OscarException("Accès interdit l'API Oscar");
                }
            } else {
                $this->getLoggerService()->error("[API OSCAR] L'API oscar n'est pas configurée");
                throw new OscarException("L'accès à l'API Oscar est mal configuré");
            }

            if (array_key_exists("strategies", $apiaccess[$user])) {
                $stategy = $apiaccess[$user]['strategies'];
            } else {
                $stategy = null;
            }

            return [
                'access' => 'granted',
                'user' => $user,
                'strategies' => $stategy
            ];
        } catch (OscarException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->getLoggerService()->error("[OSCAR API] Erreur inconnue : " . $e->getMessage());
            throw new OscarException("Accès interdit l'API Oscar");
        }
    }


    protected function getStrategy($api)
    {
        $config = $this->getOscarConfigurationService()->getApiFormats();
        $granted = $this->checkApiAcces('persons');


        if (array_key_exists('strategies', $granted) && array_key_exists($api, $granted['strategies']) && ($granted['strategies'][$api] != 'Normal' && $granted['strategies'][$api] != '')) {

            $strategy = $granted['strategies']['persons'];

            if (!array_key_exists('persons', $config)) {
                throw new OscarException("Stratégie de mis en forme mal configurée !");
            }
            if (!array_key_exists($strategy, $config['persons'])) {
                throw new OscarException("Stratégie '$strategy' inconnue !");
            }
            $class = $config['persons'][$strategy];
        } else {
            $class = PersonToJsonConnectorFormatter::class;
        }
        return $class;
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    ///                         ~ API ~
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function personsAction()
    {
        $start = microtime(true);

        try {
            $granted = $this->checkApiAcces('persons');
            $strategy = $this->getStrategy('persons');
            $personToJsonFormatter = new $strategy;
            $persons = [];

            /** @var Person $p */
            foreach ($this->getPersonService()->getPersons() as $p) {
                $persons[] = $personToJsonFormatter->format($p);
            }

            $datas = [
                "version" => OscarVersion::getBuild(),
                "datecreated" => date('c'),
                'time' => (microtime(true) - $start),
                'total' => count($persons),
                'persons' => $persons
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function personAction()
    {
        try {
            $start = microtime(true);
            $granted = $this->checkApiAcces('persons');

            try {
                $config = $this->getOscarConfigurationService()->getConfiguration('api.formats.persons');
            } catch (\Exception $e) {
                $config = [];
            }

            if (array_key_exists('strategies', $granted) && array_key_exists('persons', $granted['strategies']) && $granted['strategies']['persons'] != '') {
                $class = $config[$granted['strategies']['persons']];
                $personToJsonFormatter = new $class;
            } else {
                $personToJsonFormatter = new PersonToJsonConnectorFormatter();
            }
            $uid = $this->params()->fromRoute("id");
            try {
                $person = $this->getPersonService()->getPerson($uid);
            } catch (\Exception $e) {
                return $this->getResponseNotFound(_("Personne non trouvée"));
            }

            $datas = [
                "version" => OscarVersion::getBuild(),
                "datecreated" => date('c'),
                'time' => (microtime(true) - $start),
                "uid" => $uid,
                "person" => $personToJsonFormatter->format($person)
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function organizationsAction()
    {
        try {
            $this->checkApiAcces('organizations');
            $start = microtime(true);
            $organizations = [];
            $organizationToJsonFormatter = new OrganizationToJsonConnectorFormatter();

            /** @var Person $p */
            foreach ($this->getOrganizationService()->getOrganizations() as $o) {
                $organizations[] = $organizationToJsonFormatter->format($o);
            }

            $datas = [
                "version" => OscarVersion::getBuild(),
                "datecreated" => date('c'),
                'time' => (microtime(true) - $start),
                'total' => count($organizations),
                'organizations' => $organizations
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function organizationAction()
    {
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
                "version" => OscarVersion::getBuild(),
                "datecreated" => date('c'),
                'time' => (microtime(true) - $start),
                "uid" => $uid,
                "organization" => $organizationToJsonFormatter->format($organization)
            ];

            return $this->jsonOutput($datas);
        } catch (\Exception $e) {
            return $this->getResponseUnauthorized($e->getMessage());
        }
    }

    public function helpAction()
    {
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

        return ['response' => 'TOTO ' . $projectId];
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
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    ///
    /// REFERENCIEL
    ///
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    public function referencielPcruPoleCompetitiviteAction()
    {
        $poles = [
            "poles" => $this->getActivityService()->getPcruPoleCompetitiviteArray()
        ];
        return $this->jsonOutput($poles);
    }
}
