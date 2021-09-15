<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRequest;
use Oscar\Entity\ActivityRequestRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\EstimatedSpentLine;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Notification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\SpentLine;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\ValidationPeriodRepository;
use Oscar\Exception\OscarException;
use Oscar\Factory\ActivityPcruInfoFromActivityFactory;
use Oscar\Form\ActivityInfosPcruForm;
use Oscar\Form\PcruInfosForm;
use Oscar\Form\ProjectGrantForm;
use Oscar\Formatter\ActivityPaymentFormatter;
use Oscar\Formatter\ActivityToJsonFormatter;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\JSONFormatter;
use Oscar\Formatter\Spent\EstimatedSpentActivityHTMLFormater;
use Oscar\Formatter\Spent\EstimatedSpentActivityPDFFormater;
use Oscar\Formatter\TimesheetActivityPeriodHtmlFormatter;
use Oscar\Hydrator\ActivityInfosPCRUFormHydrator;
use Oscar\Hydrator\PcruInfosFormHydrator;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\TimesheetService;
use Oscar\Strategy\Activity\ExportDatas;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UsePCRUService;
use Oscar\Traits\UsePCRUServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseProjectServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Traits\UseSpentService;
use Oscar\Traits\UseSpentServiceTrait;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Psr\Log\LogLevel;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Console\View\Renderer;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * Controlleur pour les Activités de recherche. Le nom du controlleur est (il
 * faut bien en convenir) boiteux car il correspond à l'ancien nom de l'object
 * 'ProjectGrant'.
 *
 * @package Oscar\Controller
 */
class ProjectGrantController extends AbstractOscarController implements UseNotificationService, UsePersonService,
                                                                        UseServiceContainer, UseProjectService,
                                                                        UseSpentService, UsePCRUService
{

    use UseNotificationServiceTrait, UsePersonServiceTrait, UseServiceContainerTrait, UseProjectServiceTrait, UseSpentServiceTrait, UsePCRUServiceTrait;

    /** @var ActivityRequestService */
    private $activityRequestService;

    /** @var OrganizationService */
    private $organizationService;

    /** @var ProjectGrantService */
    private $activityService;

    /** @var ActivityTypeService */
    private $activityTypeService;

    /** @var TimesheetService */
    private $timesheetService;

    /**
     * @return TimesheetService
     */
    public function getTimesheetService(): TimesheetService
    {
        return $this->timesheetService;
    }

    /**
     * @param TimesheetService $timesheetService
     */
    public function setTimesheetService(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->activityService;
    }

    /**
     * @return ActivityTypeService
     */
    public function getActivityTypeService(): ActivityTypeService
    {
        return $this->activityTypeService;
    }

    /**
     * @param ActivityTypeService $activityTypeService
     */
    public function setActivityTypeService(ActivityTypeService $activityTypeService): void
    {
        $this->activityTypeService = $activityTypeService;
    }

    /**
     * @return ProjectGrantService
     */
    public function getActivityService(): ProjectGrantService
    {
        return $this->activityService;
    }

    /**
     * @param ProjectGrantService $activityService
     */
    public function setActivityService(ProjectGrantService $activityService): void
    {
        $this->activityService = $activityService;
    }

    /**
     * @return OrganizationService
     */
    public function getOrganizationService(): OrganizationService
    {
        return $this->organizationService;
    }

    /**
     * @param OrganizationService $organizationService
     */
    public function setOrganizationService(OrganizationService $organizationService): void
    {
        $this->organizationService = $organizationService;
    }

    /**
     * @return ActivityRequestService
     */
    public function getActivityRequestService(): ActivityRequestService
    {
        return $this->activityRequestService;
    }

    /**
     * @param ActivityRequestService $activityRequestService
     */
    public function setActivityRequestService(ActivityRequestService $activityRequestService): void
    {
        $this->activityRequestService = $activityRequestService;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function apiUiAction()
    {
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_INDEX);
        return [];
    }

    public function debugAction()
    {
        var_dump($this->getConfiguration('oscar.editable'));
        /*$customNum = $this->getActivityService()->getCustomNum();
        var_dump($customNum);*/
        die("DEBUG END");
    }

    /**
     * @url /activites-de-recherche/api
     * @return JsonModel
     */
    public function apiAction()
    {
        // On test les droits de la personne
        // On test les droits de la personne
        $person = $this->getCurrentPerson();

        ////////////////////////////////////////////////////////////////////////
        // Paramètres envoyés à l'API
        $q = $this->params()->fromQuery('q', '');
        $page = (int)$this->params()->fromQuery('p', 1);
        $rbp = (int)$this->params()->fromQuery('rbp', 20);


        // IDS des activités de la personne
        $idsPerson = array_unique($this->getActivityService()->getActivitiesIdsPerson($person));


        if (!$q) {
            $activityIds = $idsPerson;
            $totalQuery = count($activityIds);
        } else {
            $activityIds = array_intersect($this->getActivityService()->search($q), $idsPerson);
            $totalQuery = count($activityIds);
        }


        $totalPages = ceil($totalQuery / $rbp);
        $error = null;
        ////////////////////////////////////////////////////////////////////////

        if ($page > $totalPages) {
            $error = "La page demandé dépasse des résultats possibles";
        }

        // Formatteur > JSON
        $jsonFormatter = new JSONFormatter($this->getOscarUserContextService());

        // Récupération des activités effective
        $activities = $this->getActivityService()->getActivitiesByIds($activityIds, $page, $rbp);
        $totalQueryPage = count($activities);

        // Réponse
        $datas = [];

        // Mise en forme
        foreach ($activities as $activity) {
            $datas[] = $jsonFormatter->format($activity, false);
        }

        return $this->ajaxResponse(
            [
                'oscar' => OscarVersion::getBuild(),
                'date' => date('Y-m-d H:i:s'),
                'code' => 200,
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
            ]
        );
    }

    public function adminDemandeAction()
    {
        /** @var Person $demandeur */
        $demandeur = $this->getOscarUserContextService()->getCurrentPerson();

        if (!$demandeur) {
            throw new OscarException(_('Oscar ne vous connait pas.'));
        }

        $organizations = $this->getOscarUserContextService()->getOrganizationsWithPrivilege(
            Privileges::ACTIVITY_REQUEST_MANAGE
        );
        $asAdmin = $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_REQUEST_ADMIN);
        $spot = null;

        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_REQUEST_MANAGE)) {
            $spot = "global";
        } elseif (count($organizations)) {
            $spot = "organizations";
        } else {
            throw new UnAuthorizedException("Vous n'avez pas l'autorisation d'accéder à ces informations");
        }

        $dl = $this->params()->fromQuery('dl');
        if ($dl) {
            /** @var ActivityRequestService $activityRequestService */
            $activityRequestService = $this->getActivityRequestService();

            /** @var ActivityRequest $request */
            $demande = $activityRequestService->getActivityRequest($this->params()->fromQuery('id'));

            $fileInfo = $demande->getFileInfosByFile($dl);
            $filepath = $this->getOscarConfigurationService()->getCOnfiguration(
                    'paths.document_request'
                ) . '/' . $fileInfo['file'];
            $filename = $fileInfo['name'];
            $filetype = $fileInfo['type'];
            $size = filesize($filepath);
            $content = file_get_contents($filepath);

            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Length: ' . $size);
            header('Content-type: ' . $filetype);
            echo $content;
            die();
        }

        if ($this->isAjax()) {
            $method = $this->getHttpXMethod();
            switch ($method) {
                case "GET":
                    try {
                        /** @var ActivityRequestRepository $demandeActiviteRepository */
                        $demandeActiviteRepository = $this->getEntityManager()->getRepository(ActivityRequest::class);

                        $statusTxt = $this->params()->fromQuery('status', '');
                        if (trim($statusTxt) == '') {
                            $status = [];
                        } else {
                            $status = explode(',', $statusTxt);
                        }

                        if (count($status) == 0) {
                            $activityRequest = [];
                        } else {
                            if ($spot == 'global') {
                                $activityRequests = $demandeActiviteRepository->getAll($status);
                            } elseif ($spot == 'organizations') {
                                $activityRequests = $demandeActiviteRepository->getAllForOrganizations(
                                    $organizations,
                                    $status
                                );
                            } else {
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
                    } catch (\Exception $e) {
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    break;

                case "POST":
                    try {
                        $action = $this->params()->fromPost('action');
                        $rolePerson = $this->params()->fromPost('personRoleId');
                        $roleOrganisation = $this->params()->fromPost('organisationRoleId');

                        /** @var ActivityRequestService $requestActivityService */
                        $activityRequestService = $this->getActivityRequestService();

                        /** @var ActivityRequest $request */
                        $request = $activityRequestService->getActivityRequest($this->params()->fromPost('id'));

                        if ($spot == 'organizations') {
                            if (!in_array($request->getOrganisation(), $organizations)) {
                                throw new UnAuthorizedException(
                                    "Vous n'avez pas les droits suffisants pour valider cette demande."
                                );
                            }
                        }

                        if ($action == "valid") {
                            $personData = [
                                'roleid' => $rolePerson,
                            ];

                            $organisationData = [
                                'roleid' => $roleOrganisation,
                            ];

                            $activityRequestService->valid(
                                $request,
                                $this->getCurrentPerson(),
                                $personData,
                                $organisationData
                            );
                        } elseif ($action == "reject") {
                            $activityRequestService->reject($request, $this->getCurrentPerson());
                        } else {
                            return $this->getResponseBadRequest("Impossible de résoudre l'action '$action'.");
                        }

                        return $this->getResponseOk();
                    } catch (\Exception $e) {
                        return $this->getResponseInternalError($e->getMessage());
                    }
            }
            return $this->getResponseBadRequest("MAUVAISE UTILISATION ($method)");
        }


        $jsonFormatter = new JSONFormatter($this->getOscarUserContextService());


        return [
            'asAdmin' => $asAdmin,
            'rolesPerson' => $this->getPersonService()->getAvailableRolesPersonActivity(),
            'rolesOrganisation' => $jsonFormatter->objectsCollectionToJson(
                $this->getOrganizationService()->getAvailableRolesOrganisationActivity()
            ),
        ];
    }

    public function requestForAction()
    {
        /** @var Person $demandeur */
        $demandeur = $this->getOscarUserContextService()->getCurrentPerson();

        if (!$demandeur) {
            throw new OscarException(_('Oscar ne vous connait pas.'));
        }

        if (!($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_REQUEST) ||
            $this->getOscarUserContextService()->hasPrivilegeInOrganizations(Privileges::ACTIVITY_REQUEST))) {
            throw new UnAuthorizedException('Droits insuffisants');
        }


        /** @var Organization[] $organizationsPerson */
        $organizationsPerson = $this->getPersonService()->getPersonOrganizations($demandeur);

        //// CONFIGURATION
        $dest = $this->getOscarConfigurationService()->getConfiguration(
            'paths.document_request'
        );    // Emplacement des documents
        $organizations = [];
        $lockMessage = [];

        /** @var ActivityRequestService $activityRequestService */
        $activityRequestService = $this->getActivityRequestService();

        $dlFile = $this->params()->fromQuery("dl", null);
        $rdlFile = $this->params()->fromQuery("rdl", null);

        if ($dlFile || $rdlFile) {
            $idRequest = $this->params()->fromQuery("id");
            $demande = $activityRequestService->getActivityRequest($idRequest);

            // todo REVOIR CETTE PARTIE

            if ($dlFile) {
                $fileInfo = $demande->getFileInfosByFile($dlFile);
                $filepath = $this->getOscarConfigurationService()->getCOnfiguration(
                        'paths.document_request'
                    ) . '/' . $fileInfo['file'];
                $filename = $fileInfo['name'];
                $filetype = $fileInfo['type'];
                $size = filesize($filepath);
                $content = file_get_contents($filepath);
                // todo test d'accès
                header('Content-Disposition: attachment; filename=' . $filename);
                header('Content-Length: ' . $size);
                header('Content-type: ' . $filetype);
                echo $content;
                die();
            } else {
                $files = $demande->getFiles();
                $newFiles = [];
                foreach ($files as $file) {
                    if ($file['file'] == $rdlFile) {
                        @unlink(
                            $this->getOscarConfigurationService()->getCOnfiguration(
                                'paths.document_request'
                            ) . '/' . $file['file']
                        );
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
        foreach ($organizationsPerson as $o) {
            $organizations[$o->getId()] = (string)$o;
        }

        $method = $this->getHttpXMethod();

        if ($this->isAjax()) {
            $action = $this->params()->fromPost('action', null);
            $idDemande = $this->params()->fromPost("id", null);

            try {
                switch ($method) {
                    case "GET" :
                        $limit = $this->getOscarConfigurationService()->getActivityRequestLimit();

                        $statusTxt = $this->params()->fromQuery('status', '');
                        if (trim($statusTxt) == '') {
                            $status = [];
                        } else {
                            $status = explode(',', $statusTxt);
                        }

                        $demandes = $activityRequestService->getActivityRequestPerson(
                            $this->getCurrentPerson(),
                            'json',
                            $status
                        );

                        if ($limit > 0 && count($demandes) >= $limit) {
                            $lockMessage[] = "Vous avez atteint la limite des demandes autorisées.";
                        }

                        return $this->jsonOutput(
                            [
                                'allowNew' => count($lockMessage) == 0,
                                'activityRequests' => $demandes,
                                'total' => count($demandes),
                                'demandeur' => (string)$this->getCurrentPerson(),
                                'demandeur_id' => $this->getCurrentPerson()->getId(),
                                'organisations' => $organizations,
                                'lockMessages' => $lockMessage
                            ]
                        );

                    case "DELETE":
                        $idDemande = $this->params()->fromQuery('id');
                        $requestActivity = $activityRequestService->getActivityRequest($idDemande);
                        $activityRequestService->deleteActivityRequest($requestActivity);
                        return $this->getResponseOk("Suppression de la demande terminée");

                    case "POST":
                        switch ($action) {
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
                        if ($datas['id']) {
                            $activityRequest = $activityRequestService->getActivityRequest($datas['id']);
                        } else {
                            $activityRequest = new ActivityRequest();
                            $this->getEntityManager()->persist($activityRequest);
                        }

                        if ($activityRequest->getStatus() != ActivityRequest::STATUS_DRAFT) {
                            throw new OscarException("Vous ne pouvez pas modifier une demande en cours de traitement");
                        }

                        if ($datas['organisation_id']) {
                            $organization = $this->getEntityManager()->getRepository(Organization::class)->find(
                                $datas['organisation_id']
                            );
                        } else {
                            $organization = null;
                        }

                        if ($datas['dateStart'] && $datas['dateStart'] != "null") {
                            $datas['dateStart'] = new \DateTime($datas['dateStart']);
                        } else {
                            $datas['dateStart'] = null;
                        }
                        if ($datas['dateEnd'] && $datas['dateEnd'] != "null") {
                            $datas['dateEnd'] = new \DateTime($datas['dateEnd']);
                        } else {
                            $datas['dateEnd'] = null;
                        }

                        if ($_FILES) {
                            $datas['files'] = [];
                            $nbr = count($_FILES['files']['tmp_name']);
                            for ($i = 0; $i < $nbr; $i++) {
                                $size = $_FILES['files']['size'][$i];
                                $type = $_FILES['files']['type'][$i];
                                $name = $_FILES['files']['name'][$i];
                                $filepathname = date('Y-m-d_H:i:s') . '-' . md5(rand(0, 10000));
                                $filepath = $dest . '/' . $filepathname;
                                if ($size > 0) {
                                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $filepath)) {
                                        $datas['files'][] = [
                                            'name' => $name,
                                            'type' => $type,
                                            'size' => $size,
                                            'file' => $filepathname
                                        ];
                                    } else {
                                        throw new OscarException(
                                            "Impossible de téléverser votre fichier $name." . error_get_last()
                                        );
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
                            } catch (\Exception $e) {
                                $this->getLoggerService()->error(
                                    "Impossible d'enregistrer la demande d'activité : " . $e->getMessage()
                                );
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

    public function jsonApiAction()
    {
        // Accès global au activité
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_SHOW);

        $activity = $this->getActivityFromRoute();
        $formatter = new ActivityToJsonFormatter();
        $json = $formatter->format($activity);
        return $this->jsonOutput($json);
        die("TODO : $activity");
    }

    /**
     * Génération automatique de documents.
     *
     * @throws OscarException
     */
    public function generatedDocumentAction()
    {
        $id = $this->params()->fromRoute('id');
        $doc = $this->params()->fromRoute('doc');

        if ($doc == "dump") {
            echo "<table border='1'>";
            $activity = $this->getProjectGrantService()->getGrant($id);
            foreach ($activity->documentDatas() as $key => $value) {
                echo "<tr>";
                if (is_array($value)) {
                    echo "<th>$key</th><td><small>[LIST]</small></td><td>" . implode(", ", $value) . "</td>";
                } else {
                    echo "<th>$key</th><td><small>STRING</small></td><td><code>" . $value . "</code></td>";
                }
                echo "</tr>";
            }
            die("</table>");
        }

        $configDocuments = $this->getOscarConfigurationService()->getConfiguration('generated-documents.activity');
        if (!array_key_exists($doc, $configDocuments)) {
            throw new OscarException("Modèle de document non disponible (problème de configuration");
        }
        $config = $configDocuments[$doc];

        $activity = $this->getProjectGrantService()->getGrant($id);

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($config['template']);
        $documentDatas = $activity->documentDatas();

        foreach ($documentDatas as $key => $value) {
            if (is_array($value)) {
            } else {
                $templateProcessor->setValue($key, $value);
            }
        }

        // versements
        try {
            $versementsPrevus = $documentDatas['versementPrevuMontant'];
            $versementsPrevusDate = $documentDatas['versementPrevuDate'];
            if (count($versementsPrevus)) {
                $templateProcessor->cloneRow('versementPrevuMontant', count($versementsPrevus));
                for ($i = 0; $i < count($versementsPrevus); $i++) {
                    $templateProcessor->setValue('versementPrevuMontant#' . ($i + 1), $versementsPrevus[$i]);
                    $templateProcessor->setValue('versementPrevuDate#' . ($i + 1), $versementsPrevusDate[$i]);
                }
            }
        } catch (\Exception $e) {
            $this->getLoggerService()->warning("Le template $doc ne contient pas de variable $key");
        }

        $filename = 'oscar-' . $activity->getOscarNum() . '-' . $doc . '.docx';
        $filelocation = '/tmp/' . $filename;
        $templateProcessor->saveAs($filelocation);


        header('Content-Disposition: attachment; filename=' . $filename);
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
        $numerotationKeys = $this->getEditableConfKey('numerotation', []);
        $numerotationEditable = $this->getOscarConfigurationService()->getNumerotationEditable();
        $projectGrant = $this->getProjectGrantService()->getGrant($id);
        $hidden = $this->getOscarConfigurationService()->getConfiguration('activity_hidden_fields');

        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        $form = new ProjectGrantForm();
        $form->setServiceContainer($this->getServiceContainer());
        $form->setNumbers($numerotationKeys, $numerotationEditable);
        // TODO Transmettre les services au formulaire

        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        $form->init();
        $form->bind($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->flush($projectGrant);
                $this->getActivityService()->jobSearchUpdate($projectGrant);
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel(
            [
                'numerotationKeys' => $numerotationKeys,
                'hidden' => $hidden,
                'form' => $form,
                'activity' => $projectGrant,
                'numbers_keys' => $numerotationKeys
            ]
        );
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
            'admdata' => $this->params()->fromQuery('keepadmdata', false),
        ];

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_DUPLICATE);

        try {
            $id = $this->params()->fromRoute('id');
            $projectGrant = $this->getProjectGrantService()->getGrant($id);
            $duplicated = $this->getActivityService()->duplicate($projectGrant, $options);
            $this->redirect()->toRoute(
                'contract/edit',
                ['id' => $duplicated->getId()]
            );
        } catch (\Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            throw new OscarException($e->getMessage());
        }
    }

    /**
     * Création d'un nouveau projet à partir de l'activité.
     */
    public function makeProjectAction()
    {
        $activity = $this->getActivityFromRoute();

        // Contrôle des droits
        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::PROJECT_CREATE)) {
            if (!$this->getOscarUserContextService()->hasPrivilegeInOrganizations(Privileges::PROJECT_CREATE)) {
            }
        }
        $this->getOscarUserContextService()->checkWithorganizationDeep(Privileges::PROJECT_CREATE);

        die("ICI");

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
            throw new OscarException(
                sprintf(
                    "Impossible de charger l'activité '%s'",
                    $id
                )
            );
        }
        return $activity;
    }

    public function exportJSONAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $ids = $this->params()->fromPost('ids', null);

        if ($id == null && $ids == null) {
            return $this->getResponseInternalError("Données d'exportation incomplètes.");
        }

        $json = [];

        if ($id) {
            $activity = $this->getActivityFromRoute();
            $json[] = $this->getActivityService()->exportJson($activity);
        }

        if ($ids) {
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

        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-type: application/json');
        die(json_encode($json));
    }

    public function generateNotificationsAction()
    {
        $entity = $this->getActivityFromRoute();
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_NOTIFICATIONS_GENERATE, $entity);
        $this->getNotificationService()->purgeNotificationsActivity($entity);
        $this->getNotificationService()->generateNotificationsForActivity($entity);
        // die("ICI");
        $this->flashMessenger()->addSuccessMessage('Les notifications ont été mises à jour');
        return $this->redirect()->toRoute('contract/notifications', ['id' => $entity->getId()]);
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        try {
            $projectGrant = $this->getActivityFromRoute();
            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_DELETE,
                $projectGrant
            );
            $project = $projectGrant->getProject();
            $this->getLoggerService()->info(sprintf('Suppression de %s - %s', $projectGrant, $projectGrant->getId()));
            $activity_id = $projectGrant->getId();
            try {
                $this->getActivityService()->searchDelete($activity_id);
            } catch (\Exception $e) {
            }
            $this->getEntityManager()->remove($projectGrant);

            $this->getEntityManager()->flush();

            if (!$project) {
                $this->redirect()->toRoute('contract/advancedsearch');
            } else {
                $this->getEntityManager()->refresh($project);

                $this->redirect()->toRoute(
                    'project/show',
                    ['id' => $projectGrant->getProject()->getId()]
                );
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

        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {
            // Croisement
            $this->organizationsPerimeter = $this->getOscarUserContextService()->getOrganisationsPersonPrincipal(
                $this->getOscarUserContextService()->getCurrentPerson(),
                true
            );
            if ($this->getOrganizationPerimeter()) {
                $organizations = $this->getOrganizationPerimeter();
            } else {
                throw new UnAuthorizedException('Droits insuffisants');
            }
        }

        $ids = explode(',', $paramID);

        $payments = $this->getProjectGrantService()->getPaymentsByActivityId(
            $ids,
            $organizations
        );

        $formatter = new ActivityPaymentFormatter();

        $options = $this->getOscarConfigurationService()->getPayementsConfig();

        $formatter->setRolesOrganizations($options['organizations']);
        $formatter->setRolesPerson($options['persons']);
        $formatter->setSeparator($options['separator']);

        $csv = [];

        // Fichier temporaire
        $filename = uniqid('oscar_export_activities_payment_') . '.csv';
        $filePath = '/tmp/' . $filename;

        $handler = fopen($filePath, 'w');


        $delimiter = "\t";
        $enclosure = "\"";
        fputcsv($handler, $formatter->csvHeaders(), $delimiter);

        /** @var ActivityPayment $payment */
        foreach ($payments as $payment) {
            fputcsv($handler, $formatter->format($payment), $delimiter);
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

        // Utilisé pour contrôler le périmètre d'utilisation pour les exports Hors Rôle Applicatif
        $perimeter = $this->params()->fromQuery('perimeter', '');

        // Champs demandés par l'utilisateur
        $fields = $this->params()->fromPost('fields', null);

        // Format
        $format = $this->params()->fromPost('format', 'csv');

        $delimiter = "\t";

        // Récupération des IDS
        if ($request->isPost()) {
            $paramID = $this->params()->fromPost('ids', '');
        } else {
            $paramID = $this->params()->fromQuery('ids', '');
        };

        $datas = new ExportDatas($this->getProjectGrantService(), $this->getOscarUserContextService());
        $dt = $datas->output($paramID, $fields, $perimeter);
        $csv = uniqid('oscar_export_activities_') . '.csv';
        $csvPath = sprintf('/tmp/%s', $csv);
        $handler = fopen($csvPath, 'w');

        fputcsv($handler, $dt['headers'], $delimiter);

        foreach ($dt['datas'] as $data) {
            fputcsv($handler, $data, $delimiter);
        }

        $downloader = new CSVDownloader();

        if ($format == "xls") {
            $downloader->downloadCSVToExcel($csvPath);
        } else {
            $downloader->downloadCSV($csvPath);
        }
        die();
    }

    public function gantAction()
    {
        $format = $this->params()->fromQuery('format', 'html');

        if ($this->isAjax() || $format == 'json') {
            switch ($this->getHttpXMethod()) {
                case 'GET' :
                    die("DONNÉES");

                default:
                    return $this->getResponseBadRequest("Méthode inconnue");
            }
        }
        return [];
    }

    public function newInStructureAction()
    {
        // Check de l'accès
        $organization = $this->getOrganizationService()->getOrganization($this->params()->fromRoute('organizationid'));
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_EDIT, $organization);

        $hidden = $this->getOscarConfigurationService()->getConfiguration('activity_hidden_fields');

        $projectGrant = new Activity();


        $numerotationKeys = $this->getEditableConfKey('numerotation', []);
        $numerotationEditable = $this->getOscarConfigurationService()->getNumerotationEditable();

        $form = new ProjectGrantForm();
        $form->setServiceContainer($this->getServiceContainer());
        $form->setNumbers($numerotationKeys, $numerotationEditable);
        $form->init();
        ///////////////////////////////////////////////////////////////
        // TODO Transmettre les service au ProjectGrantForm
        // $form->setServiceLocator($this->getServiceLocator());
        ///////////////////////////////////////////////////////////////

        $form->setObject($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->getHydrator()->hydrate(
                $request->getPost()->toArray(),
                $projectGrant
            );

            if ($form->isValid()) {
                if ($projectGrant->getId()) {
                    $projectGrant->setDateUpdated(new \DateTime());
                }
                $this->getEntityManager()->persist($projectGrant);
                $this->getEntityManager()->flush($projectGrant);

                if (!$projectGrant->hasOrganization($organization)) {
                    // TODO récupération du rôle par défaut

                    $defaultRole = 'Laboratoire';
                    $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->findOneBy(
                        ['label' => $defaultRole]
                    );
                    if (!$role) {
                        throw new OscarException("Le rôle à utiliser n'est pas configurer");
                    }

                    $projectOrganization = new ActivityOrganization();
                    $this->getEntityManager()->persist($projectOrganization);
                    $projectOrganization->setOrganization($organization)
                        ->setActivity($projectGrant)
                        ->setRoleObj($role);

                    $this->getEntityManager()->flush($projectOrganization);
                }


                // Mise à jour de l'index de recherche
                $this->getActivityService()->jobSearchUpdate($projectGrant);
                $this->getActivityLogService()->addUserInfo("a créé l'activité ", 'Activity', $projectGrant->getId());


                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel(
            [
                'form' => $form,
                'organization' => $organization,
                'hidden' => $hidden,
                'activity' => $projectGrant,
                'project' => null,
                'numerotationKeys' => $numerotationKeys,
                'numbers_keys' => $numerotationKeys
            ]
        );

        $view->setTemplate('oscar/project-grant/form');

        return $view;
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


        $withOrganization = false;
        $rolesOrganizations = null;

        $hidden = $this->getOscarConfigurationService()->getConfiguration('activity_hidden_fields');

        // Contrôle des droits
        if ($projectId) {
            $project = $this->getProjectService()->getProject($projectId);
            $this->getOscarUserContextService()->check(Privileges::PROJECT_ACTIVITY_ADD, $project);
        } else {
            $project = null;
        }

        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_CREATE)) {
            if (!$this->getOscarUserContextService()->hasPrivilegeInOrganizations(Privileges::ACTIVITY_CREATE)) {
                throw new UnAuthorizedException(_("Vous n'avez pas les droits pour créer une nouvelle activité"));
            }
            $organisationsUser = $this->getOscarUserContextService()->getCurrentUserOrganisationWithPrivilege(
                Privileges::ACTIVITY_CREATE
            );
            if (count($organisationsUser)) {
                $withOrganization = $organisationsUser;
                $rolesOrganizations = [];
                /** @var OrganizationRole $role */
                foreach (
                    $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy(
                        ['principal' => true]
                    ) as $role
                ) {
                    if ($role->isPrincipal()) {
                        $rolesOrganizations[$role->getId()] = $role;
                    }
                }
                $rolesOrganizations[''] = 'Pas de rôle pour cette organisation';
            } else {
                return $this->getResponseInternalError("Données sur les organisations incohérentes");
            }
        }

        $errorRoles = "";
        $projectGrant = new Activity();
        $projectGrant->setProject($project);

        $numerotationKeys = $this->getEditableConfKey('numerotation', []);
        $numerotationEditable = $this->getOscarConfigurationService()->getNumerotationEditable();

        $form = new ProjectGrantForm();
        $form->setServiceContainer($this->getServiceContainer());
        $form->addOrganizationsLeader($withOrganization, $rolesOrganizations);
        $form->setNumbers($numerotationKeys, $numerotationEditable);
        $form->init();
        ///////////////////////////////////////////////////////////////
        // TODO Transmettre les service au ProjectGrantForm
        // $form->setServiceLocator($this->getServiceLocator());
        ///////////////////////////////////////////////////////////////

        $form->setObject($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $validOrganizationForm = true;

            if ($withOrganization) {
                $validOrganizationForm = false;
                $postedOrganizationsRoles = $this->params()->fromPost('roles');
                $organizationsDatas = [];

                foreach ($postedOrganizationsRoles as $idOrganization => $idRole) {
                    if (!array_key_exists($idOrganization, $withOrganization)) {
                        return $this->getResponseBadRequest("Erreur de transmission des données pour l'organisation");
                    }
                    if ($idRole) {
                        if (!array_key_exists($idRole, $rolesOrganizations)) {
                            return $this->getResponseBadRequest("Erreur de transmission des données pour le rôle");
                        }
                        $organizationsDatas[] = [
                            'organization' => $withOrganization[$idOrganization],
                            'role' => $rolesOrganizations[$idRole]
                        ];
                        $validOrganizationForm = true;
                    }
                }
                if (!$validOrganizationForm) {
                    $errorRoles = "Vous devez selectionner un rôle d'organisation";
                }
            }

            $form->setData($request->getPost());
            $form->getHydrator()->hydrate(
                $request->getPost()->toArray(),
                $projectGrant
            );


            if ($form->isValid() && $validOrganizationForm) {
                if ($projectGrant->getId()) {
                    $projectGrant->setDateUpdated(new \DateTime());
                }
                $this->getEntityManager()->persist($projectGrant);
                if ($project) {
                    $project->touch();
                }

                $this->getEntityManager()->flush($projectGrant);

                if ($organizationsDatas) {
                    foreach ($organizationsDatas as $organizationsData) {
                        $this->getActivityService()->organizationActivityAdd(
                            $organizationsData['organization'],
                            $projectGrant,
                            $organizationsData['role']
                        );
                    }
                }

                // Mise à jour de l'index de recherche
                $this->getActivityService()->jobSearchUpdate($projectGrant);

                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel(
            [
                'withOrganization' => $withOrganization,
                'errorRoles' => $errorRoles,
                'organizationRoles' => $rolesOrganizations,
                'form' => $form,
                'hidden' => $hidden,
                'activity' => $projectGrant,
                'project' => $project,
                'numerotationKeys' => $numerotationKeys,
                'numbers_keys' => $numerotationKeys
            ]
        );

        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * Fiche pour une activité de recherche.
     */
    public function documentsJsonAction()
    {
        $id = $this->params()->fromRoute('id');
        $ui = $this->params()->fromQuery('ui');

        /** @var Activity $entity */
        $entity = $this->getActivityService()->getActivityById($id, true);

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_DOCUMENT_SHOW, $entity);
        $deletable = $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $uploadable = $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $personShow = $this->getOscarUserContextService()->hasPrivileges(Privileges::PERSON_SHOW);

        $out = $this->baseJsonResponse();
        $datas = [];

        /** @var ContractDocument $doc */
        foreach ($entity->getDocuments() as $doc) {
            $docDt = $doc->toJson(
                [
                    'urlDelete' => $deletable ?
                        $this->url()->fromRoute('contractdocument/delete', ['id' => $doc->getId()])
                        : false,
                    'urlDownload' => $this->url()->fromRoute('contractdocument/download', ['id' => $doc->getId()]),
                    'urlReupload' => $uploadable ?
                        $this->url()->fromRoute(
                            'contractdocument/upload',
                            ['idactivity' => $entity->getId()]
                        ) . "?id=" . $doc->getId()
                        : false,
                    'urlPerson' => $personShow && $doc->getPerson() ? $this->url()->fromRoute(
                        'person/show',
                        ['id' => $doc->getPerson()->getId()]
                    ) : false,
                ]
            );
            $datas[] = $docDt;
        }
        $out['datas'] = $datas;

        return new JsonModel($out);
    }

    public function notificationsAction()
    {
        /** @var Activity $entity */
        $entity = $this->getActivityFromRoute();

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_NOTIFICATIONS_SHOW, $entity);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getNotificationService();

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

    public function show2Action()
    {
        $method = $this->getHttpXMethod();

        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_SHOW, $entity);

        switch ($method) {
            case 'GET' :
                if ($this->isAjax()) {
                    return $this->getResponseOk('RETOUR AJAX');
                } else {
                    return [
                        'activity' => $entity,
                        'json' => $this->getActivityService()->getActivityJson($id, $this->getOscarUserContextService())
                    ];
                }
                break;
            default :
                return $this->getResponseBadRequest('Bad Method ' . $method);
        }
    }

    public function estimatedSpentExportAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        // Format
        $format = $this->params()->fromQuery('format', 'pdf');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_ESTIMATEDSPENT_SHOW, $entity);

        // Services
        $spentService = $this->getSpentService();

        // Datas visualization
        $lines = $spentService->getLinesByMasse();
        $masses = $spentService->getMasses();
        $years = $spentService->getYearsListActivity($entity);
        $values = $out = $spentService->getPrevisionnalSpentsActivity($entity, true);

        $totaux = [
            "years" => [],
            "lines" => [],
            'total' => 0.0
        ];

        foreach ($years as $year) {
            $totaux['years'][$year] = 0.0;
        }

        foreach ($masses as $masse => $label) {
            $code = $masse;
            $totaux['lines'][$code] = [
                'total' => 0.0
            ];
            foreach ($years as $year) {
                $totaux['lines'][$code][$year] = 0.0;
            }
        }

        //var_dump($totaux); die();

        foreach ($lines as $line) {
            $masse = $line['annexe'];
            //echo $masse."\n";

            $code = $line['code'];

            $totaux['lines'][$code] = [
                'total' => 0.0
            ];

            foreach ($years as $year) {
                $totaux['lines'][$code][$year] = 0.0;
                if (array_key_exists($code, $values) && array_key_exists($year, $values[$code])) {
                    //echo "$code>$year>$masse : " . $values[$code][$year]." <br>";
                    $value = $values[$code][$year];
                    $totaux['lines'][$code][$year] += $value;
                    $totaux['lines'][$code]['total'] += $value;
                    $totaux['lines']['total'] += $value;
                    $totaux['years'][$year] += $value;
                    $totaux['total'] += $value;

                    $totaux['lines'][$masse]['total'] += $value;
                    $totaux['lines'][$masse][$year] += $value;
                    $totaux['years'][$year] += $value;
                }
            }
        }

        if ($format == 'pdf') {
            $formatter = new EstimatedSpentActivityPDFFormater(
                $this->getOscarConfigurationService()->getEstimatedSpentActivityTemplate(),
                $this->getViewRenderer(),
                [
                    'lines' => $lines,
                    'masses' => $masses,
                    'years' => $years,
                    'totaux' => $totaux,
                    'values' => $values,
                    'activity' => $entity
                ]
            );
            $formatter->format(['download' => true]);
            die();
        } else {
            if ($format == "html") {
                //
                $formatter = new EstimatedSpentActivityHTMLFormater(
                    $this->getOscarConfigurationService()->getEstimatedSpentActivityTemplate(),
                    $this->getViewRenderer(),
                    [
                        'lines' => $lines,
                        'masses' => $masses,
                        'years' => $years,
                        'totaux' => $totaux,
                        'values' => $values,
                        'activity' => $entity
                    ]
                );
                die($formatter->format());
            } else {
                throw new OscarException("Format non-pris en charge");
            }
        }
    }

    /**
     * On pourrait déplacer dans la factory idoine...
     * @return Renderer
     */
    public function getViewRenderer(): PhpRenderer
    {
        return $this->getServiceContainer()->get('ViewRenderer');
    }

    /**
     * Détail des dépenses pour une activité de recherche
     */
    public function estimatedSpentAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_ESTIMATEDSPENT_SHOW, $entity);

        // PFI
        $pfi = $entity->getCodeEOTP();

        // Method
        $method = $this->getHttpXMethod();

        // Services
        $spentService = $this->getSpentService();

        // Datas visualization
        $lines = $spentService->getLinesByMasse();
        $masses = $spentService->getMasses();
        $types = $spentService->getTypesTree();
        $years = $spentService->getYearsListActivity($entity);

        if ($method == 'GET') {
            // Get Value
            $values = $out = $spentService->getPrevisionnalSpentsActivity($entity, true);

            foreach ($masses as $masseCode => $masse) {
                if (!array_key_exists($masseCode, $values)) {
                    $values[$masseCode] = [];
                }

                foreach ($years as $year) {
                    if (!array_key_exists($year, $values[$masseCode])) {
                        $values[$masseCode][$year] = 0.0;
                    }
                }
            }

            /** @var SpentTypeGroup $spent */
            foreach ($this->getSpentService()->getAllArray() as $spent) {
                if (!$spent['annexe']) {
                    continue;
                }
                $compte = (string)$spent['code'];
                if (!array_key_exists($compte, $values)) {
                    $values[$compte] = [];
                }

                foreach ($years as $year) {
                    if (!array_key_exists($year, $values[$compte])) {
                        $values[$compte][$year] = 0.0;
                    }
                }
            }
        } elseif ($method == 'POST') {
            $masses = $_POST['masses'];
            $previsionnals = $_POST['previsionnel'];

            // Récupération du prévisionnel existant
            $out = $spentService->getPrevisionnalSpentsActivity($entity);

            foreach ($previsionnals as $compte => $compteDatas) {
                foreach ($compteDatas as $year => $amount) {
                    $this->getLoggerService()->notice("$compte ($year) : $amount");
                    $amount = (float)$amount;

                    if ($amount > 0) {
                        if (!array_key_exists($compte, $out)) {
                            $out[$compte] = [];
                        }
                        if (!array_key_exists($year, $out[$compte])) {
                            $out[$compte][$year] = new EstimatedSpentLine();
                            $this->getEntityManager()->persist($out[$compte][$year]);
                        }
                        $out[$compte][$year]->setAmount($amount);
                        $out[$compte][$year]->setYear($year);
                        $out[$compte][$year]->setActivity($entity);
                        $out[$compte][$year]->setAccount($compte);
                    } else {
                        if (array_key_exists($compte, $out) && array_key_exists($year, $out[$compte])) {
                            $this->getEntityManager()->remove($out[$compte][$year]);
                        }
                    }
                }
            }
            try {
                $this->getEntityManager()->flush();
            } catch (\Exception $e) {
                return $this->getResponseInternalError(
                    "Impossible d'enregistrer le budget prévisionnel : " . $e->getMessage()
                );
            }
            return $this->getResponseOk();
        }

        return [
            'activity' => $entity,
            'lines' => $lines,
            'masses' => $masses,
            'years' => $years,
            'types' => $types,
            'values' => $values,
        ];
    }

    /**
     * Retourne les données de synthèse des dépenses d'une activité de recherche.
     *
     * @return \Zend\Http\Response|JsonModel
     * @throws \Exception
     */
    public function spentSynthesisActivityAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContextService()->check(Privileges::DEPENSE_SHOW, $entity);

        //
        $masses = $this->getOscarConfigurationService()->getMasses();

        // Method
        $method = $this->getHttpXMethod();

        if ($method == 'POST') {
            // Vérifiaction des droits d'accès
            $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE);

            // Récupération des affectations
            $postedAffectations = $this->params()->fromPost('affectation');

            try {
                $this->getSpentService()->updateAffectation($postedAffectations);
            } catch (\Exception $e) {
                return $this->getResponseInternalError($e->getMessage());
            }
            return $this->getResponseOk("Affectation des comptes terminée");
        }

        $pfi = $entity->getCodeEOTP();

        if (!$pfi) {
            return $this->getResponseInternalError("Cette activité n'a pas de PFI");
        }

        $out = $this->baseJsonResponse();
        $out['error'] = null; // Affiche les erreurs survenue lors de la récupération/synchronisation des données
        $out['warning'] = null; // Affiche les avertissements

        if ($this->getOscarConfigurationService()->getAutoUpdateSpent()) {
            if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::DEPENSE_SYNC, $entity)) {
                $out['warning'] = "Vous n'êtes pas autorisé à mettre à jour les dépenses, les données peuvent ne pas être à jour";
            } else {
                try {
                    $this->spentService->syncSpentsByEOTP($pfi);
                } catch (\Exception $e) {
                    $out['error'] = $e->getMessage();
                }
            }
        }

        // Construction des données de dépense
        $out['masses'] = $masses;
        $out['dateUpdated'] = $entity->getDateTotalSpent();
        $out['synthesis'] = $this->getSpentService()->getSynthesisDatasPFI(
            $pfi,
            $this->getOscarUserContextService()->hasPrivileges(
                Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE
            )
        );

        return $this->jsonOutput($out);
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
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_SHOW, $entity);

        $rolesOrganizations = $this->getOscarUserContextService()->getRolesOrganizationInActivity();

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

        $rolesPersons = $this->getOscarUserContextService()->getAllRoleIdPersonInActivity();

        $activityTypeChain = $this->getActivityTypeService()->getActivityTypeChain($entity->getActivityType());

        $documentTypes = $this->getActivityService()->getTypesDocuments();

        $activity = $this->getProjectGrantService()->getGrant($id);

        $involvedPersons = null;
        $involvedPersonsJSON = null;
        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_PERSON_ACCESS, $activity)) {
            try {
                $involved = $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(
                    Privileges::ACTIVITY_SHOW,
                    $activity,
                    true
                );
                foreach ($involved as $p) {
                    $involvedPersons[] = $p->toJson();
                }
                $involvedPersonsJSON = json_encode($involvedPersons);
            } catch (\Exception $e) {
                $this->getLoggerService()->error($e->getMessage());
            }
        }

        $currencies = [];
        /** @var Currency $currency */
        foreach ($this->getEntityManager()->getRepository(Currency::class)->findAll() as $currency) {
            $currencies[] = $currency->asArray();
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getTimesheetService();

        return [
            'generatedDocuments' => $this->getOscarConfigurationService()->getConfiguration(
                'generated-documents.activity'
            ),

            'pcruEnabled' => $this->getOscarConfigurationService()->getPcruEnabled(),

            'entity' => $activity,

            'currencies' => $currencies,

            'validatorsPrj' => $timesheetService->getValidatorsPrj($activity),
            'validatorsSci' => $timesheetService->getValidatorsSci($activity),
            'validatorsAdm' => $timesheetService->getValidatorsAdm($activity),


            'declarations' => $declarations,

            // Jeton de sécurité
            'tokenValue' => "", // $this->getOscarUserContextService()->getTokenValue(true),

            // Personnes pouvant voir cette activité
            'involvedPerson' => $involvedPersonsJSON,

            'rolesOrganizations' => $rolesOrganizations,
            'rolesPersons' => $rolesPersons,

            // Notifications précalculées
            'notifications' => $this->getEntityManager()->getRepository(Notification::class)
                ->findBy(['object' => Notification::OBJECT_ACTIVITY, 'objectId' => $activity->getId()]),


            'documentTypes' => json_encode($documentTypes),
            'activityTypeChain' => $activityTypeChain,
            'traces' => $this->getActivityLogService()->activityActivities($id)->getQuery()->getResult(),
        ];
    }

    public function spentListAction()
    {
        $action = $this->params()->fromPost('action', null);
        $activity = $this->getActivityService()->getActivityById($this->params()->fromRoute('id'));
        $this->getOscarUserContextService()->check(Privileges::DEPENSE_DETAILS, $activity);
        $msg = "";
        $error = "";

        $spents = $this->getSpentService()->getGroupedSpentsDatas($activity->getCodeEOTP());


        if ($action && $action == 'update') {
            $this->getOscarUserContextService()->check(Privileges::DEPENSE_SYNC, $activity);
            try {
                $msg = $this->getSpentService()->syncSpentsByEOTP($activity->getCodeEOTP());
                $spents = $this->getSpentService()->getSpentsByPFI($activity->getCodeEOTP());
            } catch (\Exception $e) {
                $error = "Impossible de mettre à jour les dépenses : " . $e->getMessage();
            }
        }
        return [
            'masses' => $this->getOscarConfigurationService()->getMasses(),
            'activity' => $activity,
            'spents' => $spents,
            'msg' => $msg,
            'error' => $error
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
                try {
                    $project = $this->getProjectService()->getProject($request->getPost('project_id'));
                } catch (\Exception $e) {
                }

                if ($entity->getProject()) {
                    $entity->getProject()->touch();
                }
                $entity->setProject($project);
                $entity->touch();
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $entity->getId()]
                );
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
        // Récupération de l'activités
        $activity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));


        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PERSON_SHOW, $activity);

        $out = [];

        $editableA = $deletableA = $this->getOscarUserContextService()->hasPrivileges(
            Privileges::ACTIVITY_PERSON_MANAGE,
            $activity
        );
        $editableP = $deletableP = $this->getOscarUserContextService()->hasPrivileges(
            Privileges::PROJECT_PERSON_MANAGE,
            $activity->getProject()
        );
        $showable = $this->getOscarUserContextService()->hasPrivileges(Privileges::PERSON_SHOW);

        /**
         * @var ActivityPerson $activityPerson
         */
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            if (get_class($activityPerson) == ActivityPerson::class) {
                $urlDelete = $deletableA ? $this->url()->fromRoute(
                    'personactivity/delete',
                    ['idenroll' => $activityPerson->getId()]
                ) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute(
                    'personactivity/edit',
                    ['idenroll' => $activityPerson->getId()]
                ) : false;
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
                $idEnroller = $activityPerson->getActivity()->getId();
            } else {
                $urlDelete = $deletableA ? $this->url()->fromRoute(
                    'personproject/delete',
                    ['idenroll' => $activityPerson->getId()]
                ) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute(
                    'personproject/edit',
                    ['idenroll' => $activityPerson->getId()]
                ) : false;
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
                $idEnroller = $activityPerson->getProject()->getId();
            }
            $urlShow = false;
            if ($showable) {
                $urlShow = $showable ? $this->url()->fromRoute(
                    'person/show',
                    ['id' => $activityPerson->getPerson()->getId()]
                ) : false;
            }

            $out[] = [
                'id' => $activityPerson->getId(),
                'role' => $activityPerson->getRole(),
                'roleLabel' => $activityPerson->getRole(),
                'roleId' => $activityPerson->getRoleObj() ? $activityPerson->getRoleObj()->getId() : "",
                'rolePrincipal' => $activityPerson->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'urlShow' => $urlShow,
                'enroller' => $idEnroller,
                'enrollerLabel' => $activity->getLabel(),
                'editable' => $editable,
                'deletable' => $deletable,
                'enrolled' => $activityPerson->getPerson()->getId(),
                'enrolledLabel' => $activityPerson->getPerson()->getDisplayName(),
                'start' => DateTimeUtils::toStr($activityPerson->getDateStart(), 'Y-m-d'),
                'end' => DateTimeUtils::toStr($activityPerson->getDateEnd(), 'Y-m-d'),
            ];
        }

        return $this->ajaxResponse($out);
    }

    public function organizationsAction()
    {
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('id'));
        $this->getOscarUserContextService()->check(
            Privileges::ACTIVITY_PERSON_SHOW,
            $activity
        );
        $out = [];

        $editableA = $deletableA = $this->getOscarUserContextService()->hasPrivileges(
            Privileges::ACTIVITY_ORGANIZATION_MANAGE,
            $activity
        );
        $editableP = $deletableP = $this->getOscarUserContextService()->hasPrivileges(
            Privileges::PROJECT_ORGANIZATION_MANAGE,
            $activity->getProject()
        );

        $showable = $this->getOscarUserContextService()->hasPrivileges(Privileges::ORGANIZATION_SHOW);

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

            $urlDelete = $deletableA ? $this->url()->fromRoute(
                $classRoutes[$class] . '/delete',
                ['idenroll' => $activityOrganization->getId()]
            ) : false;
            $urlEdit = $editableA ? $this->url()->fromRoute(
                $classRoutes[$class] . '/edit',
                ['idenroll' => $activityOrganization->getId()]
            ) : false;

            $urlShow = $showable ? $this->url()->fromRoute(
                'organization/show',
                ['id' => $activityOrganization->getOrganization()->getId()]
            ) : false;

            $out[] = [
                'id' => $activityOrganization->getId(),
                'roleId' => $activityOrganization->getRoleObj()->getId(),
                'role' => $activityOrganization->getRole(),
                'roleLabel' => $activityOrganization->getRole(),
                'rolePrincipal' => $activityOrganization->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'urlShow' => $urlShow,
                'enroller' => $activity->getId(),
                'enrollerLabel' => (string)$activity,
                'editable' => $editable,
                'deletable' => $deletable,
//                'hash' => (string)$activityOrganization,
                'enrolled' => $activityOrganization->getOrganization()->getId(),
                'enrolledLabel' => $activityOrganization->getOrganization()->getFullName(),
                'start' => DateTimeUtils::toStr($activityOrganization->getDateStart(), 'Y-m-d'),
                'end' => DateTimeUtils::toStr($activityOrganization->getDateEnd(), 'Y-m-d')
            ];
        }
        return $this->jsonOutput($out);
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
            'contracts' => $paginator = new UnicaenDoctrinePaginator(
                $qb, $page,
                20
            ),
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
            foreach (
                $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')->where(
                    'p.id IN (:persons)'
                )->setParameter(
                    'persons',
                    $filterPersons
                )->getQuery()->getResult() as $p
            ) {
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
        $this->organizationsPerimeter = $this->getOscarUserContextService()->getOrganisationsPersonPrincipal(
            $this->getOscarUserContextService()->getCurrentPerson(),
            true
        );
        if (count($this->organizationsPerimeter) <= 0) {
            throw new UnAuthorizedException();
        }

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

            if ($search === null) {
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
                    $include = array_intersect(
                        $include,
                        $this->getOrganizationPerimeter()
                    );
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
                'om' => "Impliquant une des organisations",
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
                'ds' => 'Ayant pour discipline',

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
            $sortDirection = $this->params()->fromQuery('sortDirection', 'desc');
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
            $filterOrganizations = [];

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
                ->where(
                    '((o1.organization = :id AND o1.roleObj = :roleObj) OR (o2.organization = :id AND o2.roleObj = :roleObj))'
                );

            $queryOrganisations = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('(o1.organization in(:ids) OR o2.organization IN(:ids))');


            // Paramètres de la requête finale
            $parameters = [];

            $projectIds = [];


            if (!$search && count($criteria) === 0) {
                $ids = [];
                if ($include) {
                    $organizationsPerimeterIds = implode(',', $include);
                    $qb->andWhere(
                        'p1.organization IN('
                        . $organizationsPerimeterIds
                        . ') OR p2.organization IN('
                        . $organizationsPerimeterIds
                        . ')'
                    );
                }
            } else {
                if ($search) {
                    $oscarNumSeparator = $this->getOscarConfigurationService()->getConfiguration("oscar_num_separator");

                    // La saisie est un PFI
                    if (preg_match($this->getOscarConfigurationService()->getValidationPFI(), $search)) {
                        $parameters['search'] = $search;
                        $qb->andWhere('c.codeEOTP = :search');
                    } elseif (preg_match('/(.*)=(.*)/', $search, $result)) {
                        $key = $result[1];
                        $value = $result[2];
                        $qb->andWhere('c.numbers LIKE :numbersearch');
                        $parameters['numbersearch'] = '%"' . $key . '";s:%:"%' . $value . '%";%';
                    } else {
                        // La saisie est un numéro SAIC
                        if (preg_match("/^[0-9]{4}SAIC.*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.centaureNumConvention LIKE :search');
                        } // La saisie est un numéro OSCAR©
                        elseif (preg_match("/^[0-9]{4}" . $oscarNumSeparator . ".*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.oscarNum LIKE :search');
                        } // Saisie 'libre'
                        else {
                            try {
                                $filterIds = $this->getActivityService()->search($search);
                            } catch (\Zend_Search_Lucene_Exception $e) {
                                if (stripos($e->getMessage(), 'non-wildcard') > 0) {
                                    $error = "Les motifs de recherche doivent commencer par au moins 3 caractères non-wildcard.";
                                } else {
                                    $error = "Motif de recherche incorrecte : " . $e->getMessage();
                                }
                                $filterIds = [];
                            } catch (BadRequest400Exception $e) {
                                $error = "Expression de recherche incorrecte";
                            }
                            if ($projectview == 'on') {
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
                    // Personne (plusieurs)
                    case 'pm' :
                        $value1 = explode(',', $params[1]);
                        $crit['val1'] = $value1;
                        $personsQuery = $this->getEntityManager()->createQueryBuilder()
                            ->select('pr')
                            ->from(Person::class, 'pr')
                            ->where('pr.id IN(:idsPersons)');

                        foreach (
                            $personsQuery->getQuery()->setParameter(
                                'idsPersons',
                                $value1
                            )->getResult() as $person
                        ) {
                            $filterPersons[$person->getId()] = (string)$person;
                        }

                        $ids = array_keys(
                            $queryPersons->setParameter(
                                'ids',
                                $value1
                            )->getQuery()->getArrayResult()
                        );
                        break;
                    // Organisations (plusieurs)
                    case 'om' :


                        $value1 = explode(',', $params[1]);
                        $crit['val1'] = $value1;
                        $organisationsRequire = $this->getOrganizationService()->getOrganizationsByIds($value1);

                        /** @var Organization $organisation */
                        foreach ($organisationsRequire as $organisation) {
                            $filterOrganizations[$organisation->getId()] = (string)$organisation;
                        }

                        $ids = array_keys(
                            $queryOrganisations->setParameter(
                                'ids',
                                $value1
                            )->getQuery()->getArrayResult()
                        );

                        break;
                    case 'ap' :
                    case 'sp' :
                        try {
                            $personsId[] = $value1;
                            $person = $this->getPersonService()->getPerson($value1);
                            $persons[$person->getId()] = $person;
                            $crit['val1Label'] = $person->getDisplayName();
                            $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContextService()->getAllRoleIdPerson(
                            )[$value2] : '';
                            $query = $queryPersonNoRole;
                            if ($value2 >= 0) {
                                $queryParam['roleObj'] = $this->getEntityManager()->getRepository(Role::class)->find(
                                    $value2
                                );
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
                            $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContextService(
                            )->getRolesOrganizationInActivity()[$value2] : '';
                            if ($value2 > 0) {
                                $roleOrganisation = $this->getEntityManager()->getRepository(
                                    OrganizationRole::class
                                )->find($value2);
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


                    case 'at' :
                        if (!isset($parameters['withtype'])) {
                            $parameters['withtype'] = [];
                            $qb->andWhere('c.activityType IN (:withtype)');
                        }
                        $parameters['withtype'] = array_merge(
                            $parameters['withtype'],
                            $this->getActivityTypeService()->getTypeIdsInside($value1)
                        );
                        break;
                    case 'st' :
                        if (!isset($parameters['withouttype'])) {
                            $parameters['withouttype'] = [];
                            $qb->andWhere('c.activityType NOT IN (:withouttype)');
                        }
                        $parameters['withouttype'] = array_merge(
                            $parameters['withouttype'],
                            $this->getActivityTypeService()->getTypeIdsInside($value1)
                        );
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
                        if ($params[1]) {
                            if (!isset($parameters['countries'])) {
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
                    case 'ds' :
                        $qb->andWhere('dis.id = :discipline');
                        $parameters['discipline'] = $value1;
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
                if ($type == 'ap' || $type == 'ao' || $type == 'pm' || $type == 'om') {
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
                if ($projectIds) {
                    $qb->andWhere('c.id IN(:ids) OR pr.id IN(:projectIds)');
                    $parameters['projectIds'] = $projectIds;
                } else {
                    $qb->andWhere('c.id IN(:ids)');
                }


                $parameters['ids'] = $filterIds;
            } elseif ($projectIds) {
                $qb->andWhere('pr.id IN(:projectIds)');
                $parameters['projectIds'] = $projectIds;
            }

            $qb->setParameters($parameters);


            // FILTRE STATIC SUR LES ORGA
            if ($this->getOrganizationPerimeter()) {
                $organizationsPerimeterIds = implode(',', $include);

                $qb->andWhere(
                    'p1.organization IN('
                    . $organizationsPerimeterIds
                    . ') OR p2.organization IN('
                    . $organizationsPerimeterIds
                    . ')'
                );
            }


            $activities = null;


            if ($startEmpty === false) {
                if ($projectview == 'on') {
                    $qbIds = $qb->select('DISTINCT c.id');
                    $idsExport = array_map('current', $qbIds->getQuery()->getResult());

                    $qbIds = $qb->select('DISTINCT pr.id');
                    $ids = array_map('current', $qbIds->getQuery()->getResult());
                } else {
                    $qbIds = $qb->select('DISTINCT c.id');
                    $ids = array_map('current', $qbIds->getQuery()->getResult());
                    $idsExport = $ids;
                }

                if ($projectview == 'on') {
                    $qb->select('pr');
                } else {
                    $qb->select('c, pr, m1, p1, m2, p2, d1, t1, orga1, orga2, pers1, pers2, dis');
                    $qb->orderBy('c.' . $sort, $sortDirection);
                    if ($sortIgnoreNull) {
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

            $view = new ViewModel(
                [
                    'projectview' => $projectview,
                    'exportIds' => implode(',', $idsExport),
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
                    'filterOrganizations' => $filterOrganizations,
                    'include' => $include,
                    'organizationsPerimeter' => $this->getOrganizationPerimeter(),
                    'sort' => $sort,
                    'sortCriteria' => $sortCriteria,
                    'sortDirection' => $sortDirection,
                    'sortIgnoreNull' => $sortIgnoreNull,
                    'types' => $this->getActivityTypeService()->getActivityTypes(true),
                    'disciplines' => $this->getActivityService()->getDisciplines(),
                ]
            );
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


        if ($projectview == 'on') {
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
        $activities = $qb->orderBy(
            'c.dateStart',
            'DESC'
        )->getQuery()->getResult();

        $view = new ViewModel(
            [
                'entities' => $activities,
                'filterLabel' => "débutant prochainement",
                'datePrefix' => "Débute",
                'getDateGroup' => 'getDateStart',
                'persons' => $persons,

            ]
        );

        $view->setTemplate('oscar/activity/list-view.phtml');
        return $view;
    }

    public function almostDoneAction()
    {
        $qb = $this->getActivityService()->getActivityAlmostDone();
        $persons = $this->filterPersons($qb);
        $activities = $qb->orderBy('c.dateEnd')->getQuery()->getResult();

        $view = new ViewModel(
            [
                'entities' => $activities,
                'filterLabel' => "se terminant bientôt",
                'datePrefix' => "Se termine",
                'getDateGroup' => 'getDateEnd',
                'persons' => $persons,
            ]
        );
        $view->setTemplate('oscar/activity/list-view.phtml');

        return $view;
    }

    public function mergeAction()
    {
        return $this->getResponseNotImplemented();
    }

    public function newForStructure()
    {
        die("EN COURS");
    }
    ////////////////////////////////////////////////////////////////////////////
}
