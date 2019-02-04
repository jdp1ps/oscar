<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRequest;
use Oscar\Entity\ActivityRequestRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Notification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\ValidationPeriodRepository;
use Oscar\Exception\OscarException;
use Oscar\Form\ProjectGrantForm;
use Oscar\Formatter\ActivityPaymentFormatter;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\JSONFormatter;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\NotificationService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\PhpEnvironment\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controlleur pour les Activités de recherche. Le nom du controlleur est (il
 * faut bien en convenir) boiteux car il correspond à l'ancien nom de l'object
 * 'ProjectGrant'.
 *
 * @package Oscar\Controller
 */
class ProjectGrantController extends AbstractOscarController
{
    public function apiUiAction(){
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_INDEX);
        return [];
    }

    /**
     * @url /activites-de-recherche/api
     * @return JsonModel
     */
    public function apiAction(){

        // On test les droits de la personne
        $person = $this->getCurrentPerson();

        ////////////////////////////////////////////////////////////////////////
        // Paramètres envoyés à l'API
        $q = $this->params()->fromQuery('q', '');
        $page = (int) $this->params()->fromQuery('p', 1);
        $rbp = (int) $this->params()->fromQuery('rbp', 10);


        // IDS des activités de la personne
        $idsPerson = array_unique($this->getActivityService()->getActivitiesIdsPerson($person));


        if( !$q ){
            $activityIds = $idsPerson;
            $totalQuery = count($activityIds);
        }
        else {
            $activityIds = array_intersect($this->getActivityService()->search($q), $idsPerson);
            $totalQuery = count($activityIds);
        }



        $totalPages = ceil($totalQuery / $rbp);
        $error = null;
        ////////////////////////////////////////////////////////////////////////

        if( $page > $totalPages ){
            $error = "La page demandé dépasse des résultats possibles";
        }

        // Formatteur > JSON
        $jsonFormatter = new JSONFormatter($this->getOscarUserContext());

        // Récupération des activités effective
        $activities = $this->getActivityService()->getActivitiesByIds($activityIds, $page, $rbp);
        $totalQueryPage = count($activities);

        // Réponse
        $datas = [];

        // Mise en forme
        foreach ($activities as $activity) {
            $datas[] = $jsonFormatter->format($activity, false);
        }

        return $this->ajaxResponse([
            'oscar' => OscarVersion::getBuild(),
            'date'  => date('Y-m-d H:i:s'),
            'code'  => 200,
            'totalResultQuery' => $totalQuery,
            'totalResultPage' => $totalQueryPage,
            'totalPages' => $totalPages,
            'page' => $page,
            'error' => $error,
            'resultByPage' => $rbp,
            'datas' => [
                'ids' => $activityIds,
                'content' => $datas
            ]
        ]);
    }

    public function adminDemandeAction()
    {
        /** @var Person $demandeur */
        $demandeur = $this->getOscarUserContext()->getCurrentPerson();

        if( !$demandeur ){
            throw new OscarException(_('Oscar ne vous connait pas.'));
        }

        $organizations  = $this->getOscarUserContext()->getOrganizationsWithPrivilege(Privileges::ACTIVITY_REQUEST_MANAGE);
        $asAdmin        = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_REQUEST_ADMIN);
        $spot = null;

        if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_REQUEST_MANAGE) ){
            $spot = "global";
        }
        elseif (count($organizations)) {
            $spot = "organizations";
        } else {
            throw new UnAuthorizedException("Vous n'avez pas l'autorisation d'accéder à ces informations");
        }

        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch ($method) {
                case "GET":
                    try {
                        /** @var ActivityRequestRepository $demandeActiviteRepository */
                        $demandeActiviteRepository = $this->getEntityManager()->getRepository(ActivityRequest::class);

                        $statusTxt = $this->params()->fromQuery('status', '');
                        if( trim($statusTxt) == '' ){
                            $status = [];
                        } else {
                            $status = explode(',', $statusTxt);
                        }

                        if( count($status) == 0 ){
                            $activityRequest = [];
                        } else {
                            if( $spot == 'global'){
                                $activityRequests = $demandeActiviteRepository->getAll($status);
                            }
                            elseif ($spot == 'organizations') {
                                $activityRequests = $demandeActiviteRepository->getAllForOrganizations($organizations, $status);
                            }
                            else {
                                return $this->getResponseBadRequest('Mauvais contexte !');
                            }
                        }

                        $datas = [
                            'activityRequests' => []
                        ];
                        /** @var ActivityRequest $activityRequest */
                        foreach ($activityRequests as $activityRequest) {
                            $datas['activityRequests'][] = $activityRequest->toJson();
                        }

                        return $this->jsonOutput($datas);
                    } catch (\Exception $e){
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    break;

                case "POST":
                    try {
                        $action = $this->params()->fromPost('action');
                        $rolePerson = $this->params()->fromPost('personRoleId');
                        $roleOrganisation = $this->params()->fromPost('organisationRoleId');

                        /** @var ActivityRequestService $requestActivityService */
                        $activityRequestService = $this->getServiceLocator()->get("ActivityRequestService");

                        /** @var ActivityRequest $request */
                        $request = $activityRequestService->getActivityRequest($this->params()->fromPost('id'));

                        if( $spot == 'organizations'){
                            if( !in_array($request->getOrganisation(), $organizations) ){
                                throw new UnAuthorizedException("Vous n'avez pas les droits suffisants pour valider cette demande.");
                            }
                        }

                        if( $action == "valid" ){
                            $personData = [
                                'roleid' => $rolePerson,
                            ];

                            $organisationData = [
                                'roleid' => $roleOrganisation,
                            ];

                            $activityRequestService->valid($request, $this->getCurrentPerson(), $personData, $organisationData);
                        }
                        elseif ($action == "reject") {
                            $activityRequestService->reject($request, $this->getCurrentPerson());
                        }
                        else {
                            return $this->getResponseBadRequest("Impossible de résoudre l'action '$action'.");
                        }

                        return $this->getResponseOk();

                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }
            }
            return $this->getResponseBadRequest("MAUVAISE UTILISATION ($method)");
        }



        $jsonFormatter = new JSONFormatter($this->getOscarUserContext());


        return [
            'asAdmin' => $asAdmin,
            'rolesPerson' => $this->getPersonService()->getAvailableRolesPersonActivity(),
            'rolesOrganisation' => $jsonFormatter->objectsCollectionToJson($this->getOrganizationService()->getAvailableRolesOrganisationActivity()),
        ];
    }

    public function requestForAction()
    {
        /** @var Person $demandeur */
        $demandeur = $this->getOscarUserContext()->getCurrentPerson();

        if( !$demandeur ){
            throw new OscarException(_('Oscar ne vous connait pas.'));
        }

        if( !($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_REQUEST) ||
            $this->getOscarUserContext()->hasPrivilegeInOrganizations(Privileges::ACTIVITY_REQUEST)) ){
            throw new UnAuthorizedException('Droits insuffisants');
        }



        /** @var Organization[] $organizationsPerson */
        $organizationsPerson = $this->getPersonService()->getPersonOrganizations($demandeur);

        //// CONFIGURATION
        $dest = $this->getConfiguration('oscar.paths.document_request');    // Emplacement des documents
        $organizations = [];
        $lockMessage = [];

        /** @var ActivityRequestService $activityRequestService */
        $activityRequestService = $this->getServiceLocator()->get('ActivityRequestService');

        $dlFile = $this->params()->fromQuery("dl", null);
        $rdlFile = $this->params()->fromQuery("rdl", null);

        if( $dlFile || $rdlFile ){
            $idRequest = $this->params()->fromQuery("id");
            $demande = $activityRequestService->getActivityRequest($idRequest);

            // todo REVOIR CETTE PARTIE

            if( $dlFile ) {
                $fileInfo = $demande->getFileInfosByFile($dlFile);
                $filepath = $this->getServiceLocator()->get('OscarConfig')->getCOnfiguration('paths.document_request').'/'.$fileInfo['file'];
                $filename = $fileInfo['name'];
                $filetype = $fileInfo['type'];
                $size = filesize($filepath);
                $content = file_get_contents($filepath);
                // todo test d'accès
                header('Content-Disposition: attachment; filename=' . $filename);
                header('Content-Length: ' . $size);
                header('Content-type: '.$filetype);
                echo $content;
                die();
            } else {
                $files = $demande->getFiles();
                $newFiles = [];
                foreach ($files as $file) {
                    if( $file['file'] == $rdlFile ){
                        @unlink($this->getServiceLocator()->get('OscarConfig')->getCOnfiguration('paths.document_request').'/'. $file['file']);
                    } else {
                        $newFiles[] = $file;
                    }
                }
                $demande->setFiles($newFiles);
                $this->getEntityManager()->flush($demande);
                return $this->getResponseOk("Fichier supprimé");
            }
        }

        /** @var Organization $o */
        foreach ($organizationsPerson as $o ){
            $organizations[$o->getId()] = (string) $o;
        }

        $method = $this->getHttpXMethod();

        if( $this->isAjax() ){

            $action = $this->params()->fromPost('action', null);
            $idDemande = $this->params()->fromPost("id", null);

            try {
                switch ($method) {
                    case "GET" :
                        $limit = 5;

                        $statusTxt = $this->params()->fromQuery('status', '');
                        if( trim($statusTxt) == '' ){
                            $status = [];
                        } else {
                            $status = explode(',', $statusTxt);
                        }

                        $demandes = $activityRequestService->getActivityRequestPerson($this->getCurrentPerson(), 'json', $status);

                        if( count($demandes) >= $limit ){
                            $lockMessage[] = "Vous avez atteint la limite des demandes autorisées.";
                        }

                        return $this->jsonOutput([
                            'allowNew' => count($lockMessage) == 0,
                            'activityRequests' => $demandes,
                            'total' => count($demandes),
                            'demandeur' => (string) $this->getCurrentPerson(),
                            'demandeur_id' => $this->getCurrentPerson()->getId(),
                            'organisations' => $organizations,
                            'lockMessages' => $lockMessage
                        ]);

                    case "DELETE":
                        $idDemande = $this->params()->fromQuery('id');
                        $requestActivity = $activityRequestService->getActivityRequest($idDemande);
                        $activityRequestService->deleteActivityRequest($requestActivity);
                        return $this->getResponseOk("Suppression de la demande terminée");

                    case "POST":
                        switch( $action ){
                            case 'send' :
                                $demande = $activityRequestService->getActivityRequest($idDemande);
                                $activityRequestService->sendActivityRequest($demande, $this->getCurrentPerson());
                                return $this->getResponseOk();
                        }

                        $datas = [
                            "id" => $idDemande,
                            "label" => strip_tags(trim($this->params()->fromPost('label'))),
                            "description" => strip_tags(trim($this->params()->fromPost('description'))),
                            "amount" => floatval(str_replace(',', '.', $this->params()->fromPost('amount'))),
                            "dateStart" => $this->params()->fromPost('dateStart'),
                            "dateEnd" => $this->params()->fromPost('dateEnd'),
                            "organisation_id" => $this->params()->fromPost('organisation_id')
                        ];

                        // Création ou Mise à jour
                        if( $datas['id'] ){
                            $activityRequest = $activityRequestService->getActivityRequest($datas['id']);
                        } else {
                            $activityRequest = new ActivityRequest();
                            $this->getEntityManager()->persist($activityRequest);
                        }

                        if( $activityRequest->getStatus() != ActivityRequest::STATUS_DRAFT ){
                            throw new OscarException("Vous ne pouvez pas modifier une demande en cours de traitement");
                        }

                        if( $datas['organisation_id'] ){
                            $organization = $this->getEntityManager()->getRepository(Organization::class)->find($datas['organisation_id']);
                        } else {
                            $organization = null;
                        }

                        if( $datas['dateStart'] && $datas['dateStart'] != "null" ){
                            $datas['dateStart'] = new \DateTime($datas['dateStart']);
                        } else {
                            $datas['dateStart'] = null;
                        }
                        if( $datas['dateEnd'] && $datas['dateEnd'] != "null" ){
                            $datas['dateEnd'] = new \DateTime($datas['dateEnd']);
                        } else {
                            $datas['dateEnd'] = null;
                        }

                        if( $_FILES ){
                            $datas['files'] = [];
                            $nbr = count($_FILES['files']['tmp_name']);
                            for( $i=0; $i<$nbr; $i++ ){
                                $size = $_FILES['files']['size'][$i];
                                $type = $_FILES['files']['type'][$i];
                                $name = $_FILES['files']['name'][$i];
                                $filepathname = date('Y-m-d_H:i:s').'-'.md5(rand(0,10000));
                                $filepath = $dest .'/' . $filepathname;
                                if( $size > 0 ){
                                    if( move_uploaded_file($_FILES['files']['tmp_name'][$i], $filepath) ){
                                        $datas['files'][] = [
                                            'name' => $name,
                                            'type' => $type,
                                            'size' => $size,
                                            'file' => $filepathname
                                        ];
                                    } else {
                                        throw new OscarException("Impossible de téléverser votre fichier $name." . error_get_last());
                                    }
                                }
                            }

                            $datas['files'] = array_merge($datas['files'], $activityRequest->getFiles());

                            try {


                                $activityRequest->setLabel($datas['label'])
                                    ->setCreatedBy($this->getCurrentPerson())
                                    ->setDescription($datas['description'])
                                    ->setOrganisation($organization)
                                    ->setDateStart($datas['dateStart'])
                                    ->setDateEnd($datas['dateEnd'])
                                    ->setAmount($datas['amount'])
                                    ->setFiles($datas['files']);

                                $this->getEntityManager()->flush();

                                return [
                                    'success' => "Votre demande a bien été envoyée"
                                ];

                            } catch (\Exception $e ){
                                $this->getLogger()->error("Impossible d'enregistrer la demande d'activité : " . $e->getMessage());
                                throw new OscarException("Impossible d'enregistrer le demande : " . $e->getMessage());
                            }
                        }


                }
            } catch (OscarException $e) {
                return $this->getResponseInternalError($e->getMessage());
            }
        }

        $usedFileds = [
            'label' => true,
            'description' => true,
            'documents' => true
        ];

        return [
            'demandeur' => $demandeur,
            'form' => $usedFileds,
            'organizations' => $organizations,
            'lockMessage' => $lockMessage
        ];
    }



    /**
     * Génération automatique de documents.
     *
     * @throws OscarException
     */
    public function generatedDocumentAction(){
        $id = $this->params()->fromRoute('id');
        $doc = $this->params()->fromRoute('doc');

        if( $doc == "dump" ){
            echo "<table border='1'>";
            $activity = $this->getProjectGrantService()->getGrant($id);
            foreach ($activity->documentDatas() as $key=>$value) {
                echo "<tr>";
                if( is_array($value) ){
                    echo "<th>$key</th><td><small>[LIST]</small></td><td>" . implode(", ", $value) . "</td>";
                } else {
                    echo "<th>$key</th><td><small>STRING</small></td><td><code>" . $value . "</code></td>";
                }
                echo "</tr>";
            }
            die("</table>");
        }

        $configDocuments = $this->getConfiguration('oscar.generated-documents.activity');
        if( !array_key_exists($doc, $configDocuments) ){
             throw new OscarException("Modèle de document non disponible (problème de configuration");
        }
        $config = $configDocuments[$doc];

        $activity = $this->getProjectGrantService()->getGrant($id);

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($config['template']);

        foreach ($activity->documentDatas() as $key=>$value) {
            $templateProcessor->setValue($key, $value);
        }

        $filename = 'oscar-' . $activity->getOscarNum().'-' . $doc. '.docx';
        $filelocation = '/tmp/' . $filename;
        $templateProcessor->saveAs($filelocation);


        header('Content-Disposition: attachment; filename='.$filename);
        header('Content-Length: ' . filesize($filelocation));
        header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        echo file_get_contents($filelocation);
        unlink($filelocation);
        die();
    }

    ////////////////////////////////////////////////////////////////////////////
    // ACTIONS
    ////////////////////////////////////////////////////////////////////////////
      /**
     * @return \Zend\Http\Response
     */
    public function editAction()
    {


        $id = $this->params()->fromRoute('id');
        $projectGrant = $this->getProjectGrantService()->getGrant($id);
        $hidden = $this->getConfiguration('oscar.activity_hidden_fields');


        $form = new ProjectGrantForm();
        $form->setServiceLocator($this->getServiceLocator());
        $form->init();
        $form->bind($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->flush($projectGrant);
                $this->getActivityService()->searchUpdate($projectGrant);
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel([
            'hidden' => $hidden,
            'form' => $form,
            'activity' => $projectGrant,
            'numbers_keys' => $keys = $this->getActivityService()->getDistinctNumbersKey()
        ]);
        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function duplicateAction()
    {
        $options = [
            'organizations' => $this->params()->fromQuery('keeporganizations', false),
            'persons' => $this->params()->fromQuery('keeppersons', false),
            'milestones' => $this->params()->fromQuery('keepmilestones', false),
            'workpackages' => $this->params()->fromQuery('keepworkpackage', false),
        ];

        try {
            $id = $this->params()->fromRoute('id');
            $projectGrant = $this->getProjectGrantService()->getGrant($id);
            $duplicated = $this->getActivityService()->duplicate($projectGrant, $options);
            $this->redirect()->toRoute('contract/edit',
                ['id' => $duplicated->getId()]);

        } catch (\Exception $e) {
            die("<pre>ERROR\n : " . $e->getTraceAsString());
        }
    }

    /**
     * Création d'un nouveau projet à partir de l'activité.
     */
    public function makeProjectAction()
    {
        $activity = $this->getActivityFromRoute();

        // Contrôle des droits
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_CHANGE_PROJECT,
            $activity);

        // Création du projet
        $project = new Project();
        $this->getEntityManager()->persist($project);
        $project->setLabel($activity->getLabel())->setAcronym('');

        // Mise à jour de l'activité
        $activity->setProject($project);

        // Sauvegarde
        $this->getEntityManager()->flush();
        $this->getProjectService()->searchUpdate($project);

        // Reroutage
        $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);
    }

    /**
     * @param string $fieldName
     * @return null|Activity
     */
    private function getActivityFromRoute($fieldName = 'id')
    {
        $id = $this->params()->fromRoute($fieldName);
        if (!($activity = $this->getEntityManager()->getRepository(Activity::class)->find($id))) {
            throw new OscarException(sprintf("Impossible de charger l'activité '%s'",
                $id));
        }
        return $activity;
    }


    public function exportJSONAction(){

        $id = $this->params()->fromRoute('id', null);
        $ids = $this->params()->fromPost('ids', null);

        if( $id == null && $ids == null ){
            return $this->getResponseInternalError("Données d'exportation incomplètes.");
        }

        $json = [];

        if( $id ){
            $activity = $this->getActivityFromRoute();
            $json[] = $this->getActivityService()->exportJson($activity);
        }

        if( $ids ){
            $ids = explode(',', $ids);
            $result = $this->getEntityManager()->createQueryBuilder()->select('a')
                ->from(Activity::class, 'a')
                ->where('a.id IN(:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();
            foreach ($result as $activity) {
                $json[] = $this->getActivityService()->exportJson($activity);
            }
        }

        $filename = 'activity-json.json';

        header('Content-Disposition: attachment; filename='.$filename);
        header('Content-type: application/json');
        die(json_encode($json));
    }


    public function generateNotificationsAction(){

        $entity = $this->getActivityFromRoute();

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_NOTIFICATIONS_GENERATE, $entity);

        $this->flashMessenger()->addSuccessMessage('Les notifications ont été mises à jour');

        /** @var NotificationService $serviceNotification */
        $serviceNotification = $this->getServiceLocator()->get('NotificationService');

        $serviceNotification->generateNotificationsForActivity($entity);

        return $this->redirect()->toRoute('contract/notifications', ['id' => $entity->getId()]);
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        try {
            $projectGrant = $this->getActivityFromRoute();
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_DELETE,
                $projectGrant);
            $project = $projectGrant->getProject();
            $this->getLogger()->info(sprintf('Suppression de %s - %s', $projectGrant, $projectGrant->getId()));
            $activity_id = $projectGrant->getId();
            try {
                $this->getActivityService()->searchDelete($activity_id);
            } catch ( \Exception $e ) {}
            $this->getEntityManager()->remove($projectGrant);

            $this->getEntityManager()->flush();

            if (!$project) {
                $this->redirect()->toRoute('contract/advancedsearch');
            } else {
                $this->getEntityManager()->refresh($project);

                $this->redirect()->toRoute('project/show',
                    ['id' => $projectGrant->getProject()->getId()]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function csvPaymentsAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $organizations = null;

        // Récupération des Id des activités
        if ($request->isPost()) {
            $paramID = $this->params()->fromPost('ids', '');
        } else {
            $paramID = $this->params()->fromQuery('ids', '');
        }


        if (!$paramID) {
            return $this->getResponseBadRequest();
        }

        if (!$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {
            // Croisement
            $this->organizationsPerimeter = $this->getOscarUserContext()->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
                true);
            if ($this->getOrganizationPerimeter()) {
                $organizations = $this->getOrganizationPerimeter();
            } else {
                throw new UnAuthorizedException('Droits insuffisants');
            }
        }

        $ids = explode(',', $paramID);

        $payments = $this->getProjectGrantService()->getPaymentsByActivityId($ids,
            $organizations);

        $formatter = new ActivityPaymentFormatter();
        $formatter->setRolesOrganizations($this->getConfiguration('oscar.export.payments.organizations'));
        $formatter->setRolesPerson($this->getConfiguration('oscar.export.payments.persons'));
        $formatter->setSeparator($this->getConfiguration('oscar.export.payments.separator'));

        $csv = [];

        // Fichier temporaire
        $filename = uniqid('oscar_export_activities_payment_') . '.csv';
        $filePath = '/tmp/'.$filename;

        $handler = fopen($filePath, 'w');

        fputcsv($handler, $formatter->csvHeaders());

        /** @var ActivityPayment $payment */
        foreach ($payments as $payment) {
            fputcsv($handler, $formatter->format($payment));
        }

        fclose($handler);

        $downloader = new CSVDownloader();
        $downloader->downloadCSVToExcel($filePath);
        die();
    }


    /** Export les données en CSV. */
    public function csvAction()
    {

        /** @var Request $request */
        $request = $this->getRequest();

        $perimeter = $this->params()->fromQuery('perimeter', '');
        $fields = $this->params()->fromPost('fields', null);
        $format = $this->params()->fromPost('format', 'csv');

        $qb = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a');

        $parameters = [];

        if ($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {

        } else {
            $this->organizationsPerimeter = $this->getOscarUserContext()
                ->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
                true);

            $qb->leftJoin('a.project', 'pr')
                ->leftJoin('pr.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->where('o1.organization IN(:perimeter) OR o2.organization IN(:perimeter)');

            $parameters = [
                'perimeter' => $this->organizationsPerimeter
            ];
        }

        // NOUVELLE VERSION
        if ($request->isPost()) {
            $paramID = $this->params()->fromPost('ids', '');
        } else {
            $paramID = $this->params()->fromQuery('ids', '');
        }

        if ($paramID) {
            $ids = explode(',', $paramID);
            $qb->andWhere('a.id IN (:ids)');
            $parameters['ids'] = $ids;
        }

        $entities = $qb->getQuery()->setParameters($parameters)->getResult();

        if (!count($entities)) {
            return $this->getResponseBadRequest("Aucun résultat à exporter");
        }

        $keep = true;
        if( $fields ){
            $keep = explode(',', $fields);
        }

        $columns = [];

        // Fichier temporaire
        $csv = uniqid('oscar_export_activities_') . '.csv';
        $handler = fopen('/tmp/' . $csv, 'w');
        $headers = [];

        foreach(Activity::csvHeaders() as $header){
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
        }

        $rolesOrganizationsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('r.label')
            ->from(OrganizationRole::class, 'r')
            ->getQuery()
            ->getResult();
        $rolesOrganisations = [];

        foreach( $rolesOrganizationsQuery as $role ){
            $header = $role['label'];
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesOrganisations[$header] = [];
        }

        $rolesOrga = $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray();
        $rolesPersons = [];

        foreach( $rolesOrga as $role ){
            $header = $role;
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesPersons[$role] = [];
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // --- JALONS
        // Récupération des différents types de jalons
        $jalonsQuery = $this->getEntityManager()->getRepository(DateType::class)->findAll();
        $jalons = [];

        /** @var DateType $jalon */
        foreach ($jalonsQuery as $jalon) {
            $jalons[$jalon->getLabel()] = [];

            $header = $jalon->getLabel();
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $jalons[$header] = [];
        }

        fputcsv($handler, $headers);

        /** @var Activity $entity */
        foreach ($entities as $entity) {
            $datas = [];
            $rolesCurrent = $rolesOrganisations;
            $rolesPersonsCurrent = $rolesPersons;
            $jalonsCurrent = $jalons;

            if ($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT,
                $entity)
            ) {
                foreach( $entity->getOrganizationsDeep() as $org ){
                     $rolesCurrent[$org->getRole()][] = (string)$org->getOrganization();
                }

                foreach( $entity->getPersonsDeep() as $per ){
                     $rolesPersonsCurrent[$per->getRole()][] = (string)$per->getPerson();
                }
                /** @var ActivityDate $mil */
                foreach( $entity->getMilestones() as $mil ){

                    $jalonsCurrent[$mil->getType()->getLabel()][] = $mil->getDateStart() ?
                        $mil->getDateStart()->format('Y-m-d') :
                        'nop';
                }

                foreach ( $entity->csv() as $col=>$value ){
                    if( $columns[$col] === true )
                        $datas[] = $value;
                }

                foreach( $rolesCurrent as $role=>$organisations ){
                    if( $columns[$role] === true )
                        $datas[] = $organisations ? implode('|', array_unique($organisations)) : ' ';
                }

                foreach( $rolesPersonsCurrent as $role=>$persons ){
                    if( $columns[$role] === true )
                        $datas[] = $persons ? implode('|', array_unique($persons)) : ' ';
                }

                foreach( $jalonsCurrent as $jalon2=>$date ){
                    if( $columns[$jalon2] === true )
                        $datas[] = $date ? implode('|', array_unique($date)) : ' ';
                }
                fputcsv($handler, $datas);
            }
        }
        fclose($handler);

        $downloader = new CSVDownloader();

        $csvPath = sprintf('/tmp/%s', $csv);

        if( $format == "xls" ){
            $downloader->downloadCSVToExcel($csvPath);
        } else {
            $downloader->downloadCSV($csvPath);
        }
        die();
    }

    /**
     * Nouvelle activité de recherche.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        // Récupération du projet (si précisé)
        $projectId = $this->params()->fromRoute('projectid', null);

        $hidden = $this->getConfiguration('oscar.activity_hidden_fields');

        // Contrôle des droits
        if ($projectId) {
            $project = $this->getProjectService()->getProject($projectId);
            $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_EDIT,
                $project);
        } else {
            $project = null;
            $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EDIT);
        }

        $projectGrant = new Activity();
        $projectGrant->setProject($project);
        $form = new ProjectGrantForm();
        $form->setServiceLocator($this->getServiceLocator());
        $form->init();
        $form->setObject($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->getHydrator()->hydrate($request->getPost()->toArray(),
                $projectGrant);

            if ($form->isValid()) {
                if ($projectGrant->getId()) {
                    $projectGrant->setDateUpdated(new \DateTime());
                }
                $this->getEntityManager()->persist($projectGrant);
                if ($project) {
                    $project->touch();
                }
                $this->getEntityManager()->flush($projectGrant);

                // Mise à jour de l'index de recherche
                $this->getActivityService()->searchUpdate($projectGrant);

                $this->redirect()->toRoute('contract/show',
                    ['id' => $projectGrant->getId()]);
            }
        }

        $view = new ViewModel([
            'form' => $form,
            'hidden' => $hidden,
            'activity' => $projectGrant,
            'project' => $project,
        ]);

        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * Fiche pour une activité de recherche.
     */
    public function documentsJsonAction()
    {
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_SHOW,
            $entity);
        $deletable = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $uploadable = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $personShow = $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_SHOW);

        $out = [];
        /** @var ContractDocument $doc */
        foreach ($entity->getDocuments() as $doc) {
            $docDt = $doc->toJson([
                'urlDelete' => $deletable ?
                    $this->url()->fromRoute('contractdocument/delete',['id' => $doc->getId()])
                    : false,
                'urlDownload' => $this->url()->fromRoute('contractdocument/download', ['id' => $doc->getId()]),
                'urlReupload' => $this->url()->fromRoute('contractdocument/upload',
                        ['idactivity' => $entity->getId()]) . "?id=" . $doc->getId(),
                'urlPerson' => $personShow && $doc->getPerson() ? $this->url()->fromRoute('person/show',
                    ['id' => $doc->getPerson()->getId()]) : false,
            ]);
            $out[] = $docDt;
        }

        return new JsonModel($out);
    }

    public function notificationsAction(){

        /** @var Activity $entity */
        $entity = $this->getActivityFromRoute();

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_NOTIFICATIONS_SHOW, $entity);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        $notificationJson = [];
        foreach ($notificationService->notificationsActivity($entity) as $n) {
            $notification = $n->toArray();
            $notification['persons'] = [];
            foreach ($n->getPersons() as $personNotification) {
                $notification['persons'][] = [
                    'person' => (string)$personNotification->getPerson(),
                    'read' => $personNotification->getRead() ? $personNotification->getRead()->format('Y-m-d') : false,
                ];
            }
            $notificationJson[] = $notification;
        }

        return [
            'activity' => $entity,
            'notifications' => $notificationJson
        ];
    }

    public function show2Action(){
        $method = $this->getHttpXMethod();

        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_SHOW, $entity);

        switch ($method) {
            case 'GET' :
                if( $this->isAjax() )
                    return $this->getResponseOk('RETOUR AJAX');
                else
                    return [
                        'activity' => $entity,
                        'json' => $this->getActivityService()->getActivityJson($id, $this->getOscarUserContext())
                    ];
                break;
            default :
                return $this->getResponseBadRequest('Bad Method ' . $method);
        }
    }

    /**
     * Fiche pour une activité de recherche.
     */
    public function showAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_SHOW, $entity);


        //////////////////////////////////////////////////////////// Passage WTF
        // Si l'on supprime le bloc suivant, l'affichage des partenaires
        // part en sucette sur certaines activités... on en est là...
        /** @var ActivityOrganization $o */
        foreach ($entity->getOrganizations() as $o) {
            // $orgas[] = $o->getOrganization()->displayName();
        }
        ////////////////////////////////////////////////////////////////////////
        ///
        ///
        /// DECLARATIONS
        /** @var ValidationPeriodRepository $pvRepo */
        $pvRepo = $this->getEntityManager()->getRepository(ValidationPeriod::class);
        $declarations = $pvRepo->getValidationPeriodsByActivity($entity);


        $activityTypeChain = $this->getActivityTypeService()->getActivityTypeChain($entity->getActivityType());

        $documentTypes = [];

        /** @var TypeDocument $type */
        foreach ($this->getEntityManager()->getRepository(TypeDocument::class)->findAll() as $type) {
            $documentTypes[$type->getId()] = $type->getLabel();
        }

        $activity = $this->getProjectGrantService()->getGrant($id);

        $involvedPersons = null; $involvedPersonsJSON = null;
        if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PERSON_ACCESS, $activity) ){
            try {
                $involved = $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_SHOW, $activity, true);
                foreach ($involved as $p){
                    $involvedPersons[] = $p->toJson();
                }
                $involvedPersonsJSON = json_encode($involvedPersons);
            } catch ( \Exception $e ){
                $this->log($e->getMessage());
            }
        }

        $currencies = [];
        /** @var Currency $currency */
        foreach( $this->getEntityManager()->getRepository(Currency::class)->findAll() as $currency ){
            $currencies[] = $currency->asArray();
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        return [
            'generatedDocuments' => $this->getConfiguration('oscar.generated-documents.activity'),
            'entity' => $activity,

            'currencies' => $currencies,

            'validatorsPrj' => $timesheetService->getValidatorsPrj($activity),
            'validatorsSci' => $timesheetService->getValidatorsSci($activity),
            'validatorsAdm' => $timesheetService->getValidatorsAdm($activity),


            'declarations' => $declarations,

            // Jeton de sécurité
            'tokenValue' => $this->getOscarUserContext()->getTokenValue(true),

            // Personnes pouvant voir cette activité
            'involvedPerson' => $involvedPersonsJSON,

            // Notifications précalculées
            'notifications' => $this->getEntityManager()->getRepository(Notification::class)
                ->findBy(['object' => Notification::OBJECT_ACTIVITY, 'objectId' => $activity->getId()]),


            'documentTypes' => json_encode($documentTypes),
            'activityTypeChain' => $activityTypeChain,
            'traces' => $this->getActivityLogService()->activityActivities($id)->getQuery()->getResult(),
        ];
    }

    /**
     * Procédure pour modifier le projet d'un activité de recherche.
     */
    public function changeProjectAction()
    {
        /** @var Activity $entity */
        $entity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));

        if ($entity) {
            /** @var \Zend\Http\Request $request */
            $request = $this->getRequest();
            if ($request->isPost()) {
                $project = $this->getProjectService()->getProject($request->getPost('project_id'));
                if (!$project) {
                    throw new \Exception('Aucun projet ne correspond');
                }
                if ($entity->getProject()) {
                    $entity->getProject()->touch();
                }
                $entity->setProject($project);
                $entity->touch();
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('contract/show',
                    ['id' => $entity->getId()]);
            }
            $view = new ViewModel(['activity' => $entity]);
            $view->setTemplate('/oscar/project/project-selector.phtml');

            return $view;
        } else {
            throw new \Exception(sprintf("L'activité n'existe pas"));
        }
    }

    /**
     * Expérimentation pour afficher l'activité sous une forme plus graphique.
     */
    public function visualizationAction()
    {
        return [
            'entity' => $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'))
        ];
    }

    /**
     * Expérimentation pour afficher l'activité sous une forme plus graphique.
     */
    public function personsAction()
    {

        $activity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PERSON_SHOW,
            $activity);
        $out = [];

        $editableA = $deletableA = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE,
            $activity);
        $editableP = $deletableP = $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_PERSON_MANAGE,
            $activity->getProject());

        /**
         * @var ActivityPerson $activityPerson
         */
        foreach ($activity->getPersonsDeep() as $activityPerson) {


            if (get_class($activityPerson) == ActivityPerson::class) {
                $urlDelete = $deletableA ? $this->url()->fromRoute('personactivity/delete',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute('personactivity/edit',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
            } else {
                $urlDelete = $deletableA ? $this->url()->fromRoute('personproject/delete',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute('personproject/edit',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
            }

            $out[] = [
                'id' => $activityPerson->getId(),
                'role' => $activityPerson->getRole(),
                'roleLabel' => $activityPerson->getRole(),
                'rolePrincipal' => $activityPerson->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'enroller' => $activity->getId(),
                'enrollerLabel' => $activity->getLabel(),
                'editable' => $editable,
                'deletable' => $deletable,
                'enrolled' => $activityPerson->getPerson()->getId(),
                'enrolledLabel' => $activityPerson->getPerson()->getDisplayName(),
                'start' => $activityPerson->getDateStart(),
                'end' => $activityPerson->getDateEnd()
            ];
        }

        echo json_encode($out);
        die();
    }

    public function organizationsAction()
    {

        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('id'));
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PERSON_SHOW,
            $activity);
        $out = [];

        $editableA = $deletableA = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE,
            $activity);
        $editableP = $deletableP = $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_ORGANIZATION_MANAGE,
            $activity->getProject());

        $classRoutes = [
            ActivityOrganization::class => 'organizationactivity',
            ActivityPerson::class => 'personactivity',
            ProjectMember::class => 'personproject',
            ProjectPartner::class => 'organizationproject'
        ];

        /**
         * @var ActivityOrganization $activityOrganization
         */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {

            $class = get_class($activityOrganization);

            if ($class == ActivityOrganization::class || get_class($activityOrganization) == $class) {
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
            } else {
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
            }

            $urlDelete = $deletableA ? $this->url()->fromRoute($classRoutes[$class] . '/delete',
                ['idenroll' => $activityOrganization->getId()]) : false;
            $urlEdit = $editableA ? $this->url()->fromRoute($classRoutes[$class] . '/edit',
                ['idenroll' => $activityOrganization->getId()]) : false;

            $out[] = [
                'id' => $activityOrganization->getId(),
                'role' => $activityOrganization->getRole(),
                'roleLabel' => $activityOrganization->getRole(),
                'rolePrincipal' => $activityOrganization->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'enroller' => $activity->getId(),
                'enrollerLabel' => (string)$activity,
                'editable' => $editable,
                'deletable' => $deletable,
//                'hash' => (string)$activityOrganization,
                'enrolled' => $activityOrganization->getOrganization()->getId(),
                'enrolledLabel' => $activityOrganization->getOrganization()->getFullName(),
                'start' => $activityOrganization->getDateStart(),
                'end' => $activityOrganization->getDateEnd()
            ];
        }

        echo json_encode($out);
        die();
    }

    /**
     * Retourne la liste des activités de recherche sans projets.
     */
    public function orphansAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->where('c.project IS NULL')
            ->orderBy('c.dateCreated', 'DESC');


        $persons = $this->filterPersons($qb);

        return [
            'contracts' => $paginator = new UnicaenDoctrinePaginator($qb, $page,
                20),
            'persons' => $persons
        ];
    }

    /**
     * @deprecated
     */
    protected function filterPersons(&$qb)
    {
        // Persons
        $persons = [];
        $filterPersons = $this->params()->fromQuery('persons', []);
        $page = $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');

        if (count($filterPersons)) {
            foreach ($this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')->where('p.id IN (:persons)')->setParameter('persons',
                $filterPersons)->getQuery()->getResult() as $p) {
                $persons[] = $p;
            }
            $qb->innerJoin('c.persons', 'm')
                ->leftJoin('m.person', 'p')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'pm')
                ->leftJoin('pm.person', 'p2')
                ->andWhere('p.id in (:personIds) OR p2.id IN (:personIds)')
                ->setParameter('personIds', $filterPersons);
        }

        return $persons;
    }


    private $organizationsPerimeter;

    private function getOrganizationPerimeter()
    {
        return $this->organizationsPerimeter;
    }

    public function activitiesOrganizationsAction()
    {
        $this->organizationsPerimeter = $this->getOscarUserContext()->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
            true);

        return $this->advancedSearchAction();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return ViewModel
     * @throws \Exception
     */
    public function applyAdvancedSearch($qb)
    {
        try {
            $page = $this->params()->fromQuery('page', 1);
            $search = $this->params()->fromQuery('q', null);
            $include = null;
            $error = "";

            if( $search === null ){
                $startEmpty = true;
            } else {
                $startEmpty = false;
            }

            if ($this->getOrganizationPerimeter()) {

                $include = $this->params()->fromQuery('include', null);
                if ($include) {
                    foreach ($include as $index => $value) {
                        $include[$index] = intval($value);
                    }
                    $include = array_intersect($include,
                        $this->getOrganizationPerimeter());
                } else {
                    $include = $this->getOrganizationPerimeter();
                }
            }

            // Type de recherche supportée
            $filtersType = [
                'ap' => "Impliquant la personne",
                'sp' => "N'impliquant pas la personne",
                'pm' => "Impliquant une de ces personnes",
                'ao' => "Impliquant l'organisation",
                'so' => "N'impliquant pas l'organisation",
                'as' => 'Ayant le statut',
                'ss' => 'N\'ayant pas le statut',
                'cnt' => "Pays (d'une organisation)",
                'af' => 'Ayant comme incidence financière',
                'sf' => 'N\'ayant pas comme incidence financière',
                'mp' => 'Montant prévu',
                'at' => 'est de type',
                'st' => 'n\'est pas de type',
                'add' => 'Date de début',
                'adf' => 'Date de fin',
                'adc' => 'Date de création',
                'adm' => 'Date de dernière mise à jour',
                'ads' => 'Date de signature',
                'adp' => 'Date d\'ouverture du PFI dans SIFAC',
                'pp' => 'Activités sans projet',

                // Ajout d'un filtre sur les jalons
                'aj' => 'Ayant le jalon'

            ];

            // Correspondance des champs de type date
            $dateFields = [
                'add' => 'dateStart',
                'adc' => 'dateCreated',
                'adf' => 'dateEnd',
                'adm' => 'dateUpdated',
                'ads' => 'dateSigned',
                'adp' => 'dateOpened',
            ];

            // Critères de trie
            $sortCriteria = [
                'dateCreated' => 'Date de création',
                'dateStart' => 'Date début',
                'dateEnd' => 'Date fin',
                'dateUpdated' => 'Date de mise à jour',
                'dateSigned' => 'Date de signature',
                'dateOpened' => "Date d'ouverture du PFI dans SIFAC",
            ];

            $milestonesCriterias = [

            ];

            $jalonsFilters = [];
            $jalons = $this->getEntityManager()->getRepository(DateType::class)->findAll();
            /** @var DateType $jalon */
            foreach ($jalons as $jalon) {
                $jalonsFilters[] = [
                  'id' => $jalon->getId(),
                  'label' => $jalon->getLabel(),
                  'finishable' => $jalon->isFinishable()
                ];
            }

            // Trie
            $sortDirections = [
                'desc' => 'Décroissant',
                'asc' => 'Croissant'
            ];

            $sort = $this->params()->fromQuery('sort', 'dateUpdated');
            $sortIgnoreNull = $this->params()->fromQuery('sortIgnoreNull', null);
            $sortDirection = $this->params()->fromQuery('sortDirection','desc');
            $projectview = $this->params()->fromQuery('projectview', '');

            // Récupération des critères GET
            $criteria = $this->params()->fromQuery('criteria', []);

            // Critères rangés (pour les réafficher)
            $criterias = [];

            $personsId = [];
            $organizationId = [];

            // Liste des IDS à prendre en compte dans le requète finale
            $ids = null;

            // Liste des IDS à exclure de la requète final
            $notIds = null;

            // Variables temporaires pour stoquer les ids
            $filterIds = null;
            $filterNotIds = [];
            $filterStatus = [];
            $filterNoStatus = [];
            $filterPersons = [];

            $organizations = [];
            $persons = [];


            $queryPersons = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('m1.person in(:ids) OR m2.person in (:ids)');

            // QueryBuilder utilisés pour récupérer les IDS des activités pour
            // les filtres de personne avec ou sans rôle, idem pour les
            // organisations.
            $queryPersonNoRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('(m1.person = :id OR m2.person = :id)');

            $queryPersonRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('((m1.person = :id AND m1.roleObj = :roleObj) OR (m2.person = :id AND m2.roleObj = :roleObj))');

            $queryOrganisationNoRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('(o1.organization = :id OR o2.organization = :id)');

            $queryOrganisationRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('((o1.organization = :id AND o1.roleObj = :roleObj) OR (o2.organization = :id AND o2.roleObj = :roleObj))');

            // Paramètres de la requête finale
            $parameters = [];

            $projectIds = [];


            if (!$search && count($criteria) === 0) {
                $ids = [];
                if ($include) {
                    $organizationsPerimeterIds = implode(',', $include);
                    $qb->andWhere('p1.organization IN('
                            . $organizationsPerimeterIds
                            . ') OR p2.organization IN('
                            . $organizationsPerimeterIds
                            . ')');
                }
            } else {
                if ($search) {

                    $oscarNumSeparator = $this->getConfiguration("oscar.oscar_num_separator");

                    // La saisie est un PFI
                    if (preg_match($this->getServiceLocator()->get("Config")['oscar']['validation']['pfi'], $search)) {
                        $parameters['search'] = $search;
                        $qb->andWhere('c.codeEOTP = :search');
                    } elseif (preg_match('/(.*)=(.*)/', $search, $result)) {
                        $key = $result[1];
                        $value = $result[2];
                        $qb->andWhere('c.numbers LIKE :numbersearch');
                        $parameters['numbersearch'] = '%"' . $key . '";s:%:"' . $value . '";%';
                    } else {
                        // La saisie est un numéro SAIC
                        if (preg_match("/^[0-9]{4}SAIC.*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.centaureNumConvention LIKE :search');
                        }
                        // La saisie est un numéro OSCAR©
                        elseif (preg_match("/^[0-9]{4}".$oscarNumSeparator.".*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.oscarNum LIKE :search');
                        } // Saisie 'libre'
                        else {
                            try {
                                $filterIds = $this->getActivityService()->search($search);
                            } catch (\Zend_Search_Lucene_Exception $e ){
                                if( stripos($e->getMessage(), 'non-wildcard') > 0 ){
                                    $error = "Les motifs de recherche doivent commencer par au moins 3 caractères non-wildcard.";
                                } else {
                                    $error = "Motif de recherche incorrecte : " . $e->getMessage();
                                }
                                $filterIds = [];

                            }
                            if( $projectview == 'on' ){
                                $projectIds = $this->getActivityService()->getProjectsIdsSearch($search);

                            }

                            }
                        }
                    }
                }


                // Analyse des critères de recherche
                foreach ($criteria as $c) {

                    // Découpage et récupération des critères de filtre
                    $params = explode(';', $c);
                    $type = $params[0];

                    $crit = [
                        'type' => $type
                    ];

                    $value1 = intval($params[1]);
                    $value2 = intval($params[2]);

                    $crit['val1'] = $value1;
                    $crit['val2'] = $value2;

                    $queryParam = [
                        'id' => $value1
                    ];

                    $filterKey = uniqid('filter_');

                    switch ($type) {
                        case 'vp':
                            $qb->addSelect('c.payments', 'p');
                            break;
                        case 'mp':
                            $clause = [];

                            if ($value1) {
                                $clause[] = 'c.amount >= :amountMin';
                                $parameters['amountMin'] = $value1;
                            }
                            if ($value2) {
                                $clause[] = 'c.amount <= :amountMax';
                                $parameters['amountMax'] = $value2;
                            }

                            if (!$value1 && !$value2) {
                                $crit['error'] = 'Plage numérique farfelue...';
                            } else {
                                $qb->andWhere(implode(' AND ', $clause));
                            }
                            break;
                        case 'pp' :
                            $qb->andWhere('c.project IS NULL');
                            break;
                        // Personne
                        case 'pm' :
                            $value1 = explode(',', $params[1]);
                            $crit['val1'] = $value1;
                            $personsQuery = $this->getEntityManager()->createQueryBuilder()
                                ->select('pr')
                                ->from(Person::class, 'pr')
                                ->where('pr.id IN(:idsPersons)');

                            foreach ($personsQuery->getQuery()->setParameter('idsPersons',
                                $value1)->getResult() as $person) {
                                $filterPersons[$person->getId()] = (string)$person;
                            }

                            $ids = array_keys($queryPersons->setParameter('ids',
                                $value1)->getQuery()->getArrayResult());
                            break;
                        case 'ap' :
                        case 'sp' :
                            try {
                                $personsId[] = $value1;
                                $person = $this->getPersonService()->getPerson($value1);
                                $persons[$person->getId()] = $person;
                                $crit['val1Label'] = $person->getDisplayName();
                                $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContext()->getAllRoleIdPerson()[$value2] : '';
                                $query = $queryPersonNoRole;
                                if ($value2 >= 0) {
                                    $queryParam['roleObj'] = $this->getEntityManager()->getRepository(Role::class)->find($value2);
                                    $query = $queryPersonRole;
                                }
                                $ids = array_keys($query->setParameters($queryParam)->getQuery()->getArrayResult());
                            } catch (\Exception $e) {
                                $crit['error'] = "Impossible de filtrer sur la personne";
                            }
                            break;

                        case 'ao' :
                        case 'so' :
                            $organizationId[] = $value1;
                            try {
                                $organization = $this->getOrganizationService()->getOrganization($value1);
                                $organizations[$organization->getId()] = $organization;
                                $crit['val1Label'] = (string)$organization;
                                $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContext()->getRolesOrganizationInActivity()[$value2] : '';
                                if ($value2 > 0) {
                                    $roleOrganisation = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($value2);
                                    $queryParam['roleObj'] = $roleOrganisation;
                                    $query = $queryOrganisationRole;
                                } else {
                                    $query = $queryOrganisationNoRole;
                                }
                                $ids = array_keys($query->setParameters($queryParam)->getQuery()->getArrayResult());

                            } catch (\Exception $e) {
                                $crit['error'] = "Impossible de filtrer sur l'organisation (" . $e->getMessage() . ")";
                            }
                            break;

                        // Filtre sur le statut de l'activité
                        case 'as' :
                            if (!isset($parameters['withstatus'])) {
                                $parameters['withstatus'] = [];
                            }
                            $parameters['withstatus'][] = $value1;
                            $qb->andWhere('c.status IN (:withstatus)');
                            break;
                        case 'ss' :
                            if (!isset($parameters['withoutstatus'])) {
                                $parameters['withoutstatus'] = [];
                            }
                            $parameters['withoutstatus'][] = $value1;
                            $qb->andWhere('c.status NOT IN (:withoutstatus)');
                            break;

                        // Filtre sur le type de l'activité
                        case 'at' :
                            if (!isset($parameters['withtype'])) {
                                $parameters['withtype'] = [];
                                $qb->andWhere('c.activityType IN (:withtype)');
                            }
                            $parameters['withtype'] = array_merge($parameters['withtype'],
                                $this->getActivityTypeService()->getTypeIdsInside($value1));
                            break;
                        case 'st' :
                            if (!isset($parameters['withouttype'])) {
                                $parameters['withouttype'] = [];
                                $qb->andWhere('c.activityType NOT IN (:withouttype)');
                            }
                            $parameters['withouttype'] = array_merge($parameters['withouttype'],
                                $this->getActivityTypeService()->getTypeIdsInside($value1));
                            break;
                        // Filtre sur la/les incidences financière
                        case 'af' :
                            if (!isset($parameters['withfinancial'])) {
                                $parameters['withfinancial'] = [];
                                $qb->andWhere('c.financialImpact IN (:withfinancial)');
                            }
                            $parameters['withfinancial'][] = Activity::getFinancialImpactValues()[$value1];
                            break;
                        case 'sf' :
                            if (!isset($parameters['withoutfinancial'])) {
                                $parameters['withoutfinancial'] = [];
                                $qb->andWhere('c.financialImpact NOT IN (:withoutfinancial)');
                            }
                            $parameters['withoutfinancial'][] = Activity::getFinancialImpactValues()[$value1];
                            break;
                        case 'cnt' :
                            if( $params[1] ){
                                if( !isset($parameters['countries']) ){
                                    $parameters['countries'] = [];
                                }
                                $value1 = $crit['val1'] = explode(',', $params[1]);
                                $qb->andWhere('orga1.country IN (:countries) OR orga2.country IN (:countries)');
                                $parameters['countries'] = $value1;
                            }
                            break;
                        case 'aj':
                            $filterIds = $this->getActivityService()->getActivityIdsByJalon($crit['val1']);
                            break;
                        case 'add' :
                        case 'adf' :
                        case 'adm' :
                        case 'adc' :
                        case 'ads' :
                        case 'adp' :
                            $field = $dateFields[$type];

                            $start = DateTimeUtils::toDatetime($params[1]);
                            $end = DateTimeUtils::toDatetime($params[2]);
                            $value1 = $start ? $start->format('Y-m-d') : '';
                            $value2 = $end ? $end->format('Y-m-d') : '';
                            $crit['val1'] = $value1;
                            $crit['val2'] = $value2;
                            $clause = [];

                            if ($value1) {
                                $clause[] = 'c.' . $field . ' >= :' . $filterKey . 'start';
                                $parameters[$filterKey . 'start'] = $value1;
                            }
                            if ($value2) {
                                $clause[] = 'c.' . $field . ' <= :' . $filterKey . 'end';
                                $parameters[$filterKey . 'end'] = $value2;
                            }

                            if ($clause) {
                                $qb->andWhere(implode(' AND ', $clause));
                            } else {
                                $crit['error'] = 'Plage de date invalide';
                            }
                            break;
                    }
                    $criterias[] = $crit;
                    if ($type == 'ap' || $type == 'ao' || $type == 'pm' ) {

                        if ($filterIds === null) {
                            $filterIds = $ids;
                        } else {
                            $filterIds = array_intersect($filterIds, $ids);
                        }
                    }
                    if ($type == "sp" || $type == 'so') {
                        $filterNotIds = array_merge($filterNotIds, $ids);
                    }
                }


                if ($filterNotIds) {
                    $qb->andWhere('c.id NOT IN(:not)');
                    $parameters['not'] = $filterNotIds;
                }

                if ($filterIds !== null) {
                    if  ($projectIds) {

                        $qb->andWhere('c.id IN(:ids) OR pr.id IN(:projectIds)');
                        $parameters['projectIds'] = $projectIds;
                    } else {
                        $qb->andWhere('c.id IN(:ids)');
                    }



                    $parameters['ids'] = $filterIds;
                } elseif  ($projectIds) {

                    $qb->andWhere('pr.id IN(:projectIds)');
                    $parameters['projectIds'] = $projectIds;
                }

                $qb->setParameters($parameters);


                // FILTRE STATIC SUR LES ORGA
                if ($this->getOrganizationPerimeter()) {

                    $organizationsPerimeterIds = implode(',', $include);

                    $qb->andWhere('p1.organization IN('
                        . $organizationsPerimeterIds
                        . ') OR p2.organization IN('
                        . $organizationsPerimeterIds
                        . ')');
                }


            $activities = null;
            if( $startEmpty === false ) {

                if( $projectview == 'on' ){
                    $qbIds = $qb->select('DISTINCT pr.id');
                } else {
                    $qbIds = $qb->select('DISTINCT c.id');
                }

                $ids = array_map('current', $qbIds->getQuery()->getResult());


                if ( $projectview == 'on' ) {
                   $qb->select('pr');

                } else {
                    $qb->select('c, pr, m1, p1, m2, p2, d1, t1, orga1, orga2, pers1, pers2, dis');
                    $qb->orderBy('c.' . $sort, $sortDirection);
                    if( $sortIgnoreNull ){
                        $qb->andWhere('c.' . $sort . ' IS NOT NULL');
                    }
                }
                $activities = new UnicaenDoctrinePaginator($qb, $page);
            }

            if ($this->getRequest()->isXmlHttpRequest()) {
                $json = [
                    'datas' => []
                ];
                /** @var Activity $activity */
                foreach ($activities as $activity) {
                    $json['datas'][] = $activity->toJson();
                }

                return $this->ajaxResponse($json);
            }

            $view = new ViewModel([
                'projectview' => $projectview,
                'exportIds' => implode(',', $ids),
                'filtersType' => $filtersType,
                'error' => $error,
                'criteria' => $criterias,
                'countries' => $this->getOrganizationService()->getCountriesList(),
                'fieldsCSV' => $this->getActivityService()->getFieldsCSV(),
                'persons' => $persons,
                'filterJalons' => $jalonsFilters,
                'activities' => $activities,
                'search' => $search,
                'filterPersons' => $filterPersons,
                'include' => $include,
                'organizationsPerimeter' => $this->getOrganizationPerimeter(),
                'sort' => $sort,
                'sortCriteria' => $sortCriteria,
                'sortDirection' => $sortDirection,
                'sortIgnoreNull' => $sortIgnoreNull,
                'types' => $this->getActivityTypeService()->getActivityTypes(true),
            ]);
            $view->setTemplate('oscar/project-grant/advanced-search.phtml');
            return $view;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Nouveau système de recherche des activités.
     *
     * @return array
     */
    public function advancedSearchAction()
    {

        // Requêtes de base

        $projectview = $this->params()->fromQuery('projectview', '');


        if( $projectview == 'on') {

            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('pr')
                ->from(Project::class, 'pr')
                ->leftJoin('pr.grants', 'c')
                ->leftJoin('c.persons', 'm1')
                ->leftJoin('m1.person', 'pers1')
                ->leftJoin('c.disciplines', 'dis')
                ->leftJoin('c.activityType', 't1')
                ->leftJoin('c.organizations', 'p1')
                ->leftJoin('p1.organization', 'orga1')
                ->leftJoin('c.documents', 'd1')
                ->leftJoin('pr.members', 'm2')
                ->leftJoin('pr.partners', 'p2')
                ->leftJoin('m2.person', 'pers2')
                ->leftJoin('p2.organization', 'orga2');
        } else {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('c')
                ->from(Activity::class, 'c')
                ->leftJoin('c.persons', 'm1')
                ->leftJoin('m1.person', 'pers1')
                ->leftJoin('c.disciplines', 'dis')
                ->leftJoin('c.activityType', 't1')
                ->leftJoin('c.organizations', 'p1')
                ->leftJoin('p1.organization', 'orga1')
                ->leftJoin('c.documents', 'd1')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'm2')
                ->leftJoin('pr.partners', 'p2')
                ->leftJoin('m2.person', 'pers2')
                ->leftJoin('p2.organization', 'orga2');
        }
        return $this->applyAdvancedSearch($qb);
    }

    /**
     * Liste des activités de recherche.
     */
    public function indexAction()
    {
        die("DEPRECATED");
        return $this->getResponseDeprecated();
    }

    public function almostStartAction()
    {
        $qb = $this->getActivityService()->getActivityBeginsSoon();
        $persons = $this->filterPersons($qb);
        $activities = $qb->orderBy('c.dateStart',
            'DESC')->getQuery()->getResult();

        $view = new ViewModel([
            'entities' => $activities,
            'filterLabel' => "débutant prochainement",
            'datePrefix' => "Débute",
            'getDateGroup' => 'getDateStart',
            'persons' => $persons,

        ]);

        $view->setTemplate('oscar/activity/list-view.phtml');
        return $view;
    }

    public function almostDoneAction()
    {
        $qb = $this->getActivityService()->getActivityAlmostDone();
        $persons = $this->filterPersons($qb);
        $activities = $qb->orderBy('c.dateEnd')->getQuery()->getResult();

        $view = new ViewModel([
            'entities' => $activities,
            'filterLabel' => "se terminant bientôt",
            'datePrefix' => "Se termine",
            'getDateGroup' => 'getDateEnd',
            'persons' => $persons,
        ]);
        $view->setTemplate('oscar/activity/list-view.phtml');

        return $view;
    }

    public function mergeAction()
    {
        return $this->getResponseNotImplemented();
    }
    ////////////////////////////////////////////////////////////////////////////
}
