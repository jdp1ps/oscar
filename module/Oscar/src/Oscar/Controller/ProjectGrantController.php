<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Exception;
use Laminas\Http\Response;
use Oscar\Entity\Activity;
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
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRoles;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\ValidationPeriodRepository;
use Oscar\Exception\OscarException;
use Oscar\Factory\ActivityGantJson;
use Oscar\Form\ActivityInfosPcruForm;
use Oscar\Form\ProjectGrantForm;
use Oscar\Form\SignedDocumentForm;
use Oscar\Formatter\ActivityPaymentFormatter;
use Oscar\Formatter\ActivityToJsonFormatter;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\JSONFormatter;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Hydrator\PcruInfosFormHydrator;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\DocumentFormatterService;
use Oscar\Service\OrganizationService;
use Oscar\Service\ProjectGrantSearchService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\TimesheetService;
use Oscar\Strategy\Activity\ExportDatas;
use Oscar\Traits\UseContractDocumentService;
use Oscar\Traits\UseContractDocumentServiceTrait;
use Oscar\Traits\UseDocumentFormatterServiceTrait;
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
use Oscar\Utils\ArrayUtils;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\Console\View\Renderer;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use UnicaenSignature\Provider\SignaturePrivileges;
use UnicaenSignature\Service\SignatureService;
use UnicaenSignature\Service\SignatureServiceAwareTrait;

/**
 * Controlleur pour les Activités de recherche. Le nom du controlleur est (il
 * faut bien en convenir) boiteux car il correspond à l'ancien nom de l'object
 * 'ProjectGrant'.
 *
 * @package Oscar\Controller
 */
class ProjectGrantController extends AbstractOscarController implements UseNotificationService, UsePersonService,
                                                                        UseServiceContainer, UseProjectService,
                                                                        UseSpentService, UsePCRUService,
                                                                        UseContractDocumentService
{

    use UseNotificationServiceTrait, UsePersonServiceTrait, UseServiceContainerTrait, UseProjectServiceTrait, UseSpentServiceTrait, UsePCRUServiceTrait, UseContractDocumentServiceTrait, UseDocumentFormatterServiceTrait, SignatureServiceAwareTrait;

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

    /** @var ProjectGrantSearchService */
    private ProjectGrantSearchService $projectGrantSearchService;

    public function getProjectGrantSearchService(): ProjectGrantSearchService
    {
        return $this->projectGrantSearchService;
    }

    public function setProjectGrantSearchService(ProjectGrantSearchService $projectGrantSearchService): self
    {
        $this->projectGrantSearchService = $projectGrantSearchService;
        return $this;
    }

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

    /**
     * @url /activites-de-recherche/api
     * @return JsonModel
     */
    public function apiAction()
    {
        // On test les droits de la personne / restrictions
        $person = $this->getCurrentPerson();
        $fullaccess = false; // $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_INDEX);
        if ($fullaccess) {
            $restricted_ids = false;
        }
        else {
            $restricted_ids = array_unique($this->getActivityService()->getActivitiesIdsPerson($person));
        }

        ////////////////////////////////////////////////////////////////////////
        // Paramètres envoyés à l'API
        $q = $this->params()->fromQuery('q', '');
        $page = (int)$this->params()->fromQuery('p', 1);
        $rbp = (int)$this->params()->fromQuery('rbp', 20);
        $sort = $this->params()->fromQuery('t', null);
        $direction = $this->params()->fromQuery('d', 'desc');
        $status = $this->params()->fromQuery('st', '');
        $filters = $this->params()->fromQuery('f', []);

        if (!array_key_exists($sort, $this->getProjectGrantService()->getActivitiesSearchSort())) {
            $sort = 'hit';
        }

        if (!array_key_exists($direction, $this->getProjectGrantService()->getActivitiesSearchDirection())) {
            $direction = 'desc';
        }

        // Contrôle des filtres

        if ($status) {
            $filters[] = 's;' . $status . ';-1';
        }

        // Options des recheches
        $options = [
            'sort'           => $sort,
            'direction'      => $direction,
            'page'           => $page,
            'result_by_page' => $rbp,
            'filters'        => $filters,
            'restricted_ids' => $restricted_ids
        ];

        // Recherche
        $resultSearch = $this->getProjectGrantService()->searchActivities($q, $options);
        $jsonFormatter = new JSONFormatter($this->getOscarUserContextService());

        $datas = [];
        $activityIds = [];

        foreach ($resultSearch['activities'] as $activity) {
            $activityIds[] = $activity->getId();
            $datas[] = $jsonFormatter->format($activity, false);
        }

        $output = [
            'oscar'         => OscarVersion::getBuild(),
            'date'          => date('Y-m-d H:i:s'),
            'code'          => 200,
            'filters_infos' => $resultSearch['filters_infos'],
            'page'          => $resultSearch['page'],
            'resultsByPage' => $resultSearch['result_by_page'],
            'result_total'  => $resultSearch['total'],
            'datas'         => [
                'ids'     => $activityIds,
                'content' => $datas
            ]
        ];

        return $this->ajaxResponse($output);
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
        }
        elseif (count($organizations)) {
            $spot = "organizations";
        }
        else {
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
                        }
                        else {
                            $status = explode(',', $statusTxt);
                        }

                        if (count($status) == 0) {
                            $activityRequest = [];
                        }
                        else {
                            if ($spot == 'global') {
                                $activityRequests = $demandeActiviteRepository->getAll($status);
                            }
                            elseif ($spot == 'organizations') {
                                $activityRequests = $demandeActiviteRepository->getAllForOrganizations(
                                    $organizations,
                                    $status
                                );
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
                    } catch (Exception $e) {
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
                        }
                        elseif ($action == "reject") {
                            $activityRequestService->reject($request, $this->getCurrentPerson());
                        }
                        else {
                            return $this->getResponseBadRequest("Impossible de résoudre l'action '$action'.");
                        }

                        return $this->getResponseOk();
                    } catch (Exception $e) {
                        return $this->getResponseInternalError($e->getMessage());
                    }
            }
            return $this->getResponseBadRequest("MAUVAISE UTILISATION ($method)");
        }

        return [
            'asAdmin'           => $asAdmin,
            'rolesPerson'       => $this->getPersonService()->getAvailableRolesPersonActivity(
                OscarFormatterConst::FORMAT_ARRAY_ID_VALUE
            ),
            'rolesOrganisation' => $this->getOrganizationService()->getAvailableRolesOrganisationActivity(
                OscarFormatterConst::FORMAT_ARRAY_ID_VALUE
            )
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
                $filepath = $this->getOscarConfigurationService()->getConfiguration(
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
            }
            else {
                $files = $demande->getFiles();
                $newFiles = [];
                foreach ($files as $file) {
                    if ($file['file'] == $rdlFile) {
                        @unlink(
                            $this->getOscarConfigurationService()->getConfiguration(
                                'paths.document_request'
                            ) . '/' . $file['file']
                        );
                    }
                    else {
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
                        }
                        else {
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
                                'allowNew'         => count($lockMessage) == 0,
                                'activityRequests' => $demandes,
                                'total'            => count($demandes),
                                'demandeur'        => (string)$this->getCurrentPerson(),
                                'demandeur_id'     => $this->getCurrentPerson()->getId(),
                                'organisations'    => $organizations,
                                'lockMessages'     => $lockMessage
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
                            "id"              => $idDemande,
                            "label"           => strip_tags(trim($this->params()->fromPost('label'))),
                            "description"     => strip_tags(trim($this->params()->fromPost('description'))),
                            "amount"          => floatval(str_replace(',', '.', $this->params()->fromPost('amount'))),
                            "dateStart"       => $this->params()->fromPost('dateStart'),
                            "dateEnd"         => $this->params()->fromPost('dateEnd'),
                            "organisation_id" => $this->params()->fromPost('organisation_id')
                        ];

                        // Création ou Mise à jour
                        if ($datas['id']) {
                            $activityRequest = $activityRequestService->getActivityRequest($datas['id']);
                        }
                        else {
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
                        }
                        else {
                            $organization = null;
                        }

                        if ($datas['dateStart'] && $datas['dateStart'] != "null") {
                            $datas['dateStart'] = new \DateTime($datas['dateStart']);
                        }
                        else {
                            $datas['dateStart'] = null;
                        }
                        if ($datas['dateEnd'] && $datas['dateEnd'] != "null") {
                            $datas['dateEnd'] = new \DateTime($datas['dateEnd']);
                        }
                        else {
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
                                    }
                                    else {
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
                            } catch (Exception $e) {
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
            'label'       => true,
            'description' => true,
            'documents'   => true
        ];

        return [
            'demandeur'     => $demandeur,
            'form'          => $usedFileds,
            'organizations' => $organizations,
            'lockMessage'   => $lockMessage
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
        $baseDatas = $this->getProjectGrantService()->getBaseDataTemplate();
        $activity = $this->getProjectGrantService()->getGrant($id);
        $documentDatas = $activity->documentDatas($baseDatas);
        $documentDatas["type-full"] = $this->getActivityTypeService()->getActivityTypeChainFormatted(
            $activity->getActivityType()
        );

        ksort($documentDatas);

        if ($doc == "dump") {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>DUMP des données</title>
            </head>
            <body>
            <table border='1'>
                <?php
                foreach ($documentDatas as $key => $value): ?>
                    <tr>
                        <?php
                        if (is_array($value)): ?>
                            <th><?= $key ?></th>
                            <td><small>[LIST]</small></td>
                            <td><?= ArrayUtils::implode(", ", $value) ?></td>
                        <?php
                        else: ?>
                            <th><?= $key ?></th>
                            <td><small>STRING</small></td>
                            <td><code><?= $value ?></code></td>
                        <?php
                        endif; ?>
                    </tr>
                <?php
                endforeach; ?>
            </table>
            </body>
            </html>
            <?php
            die();
        }

        $configDocuments = $this->getOscarConfigurationService()->getConfiguration('generated-documents.activity');
        if (!array_key_exists($doc, $configDocuments)) {
            throw new OscarException("Modèle de document non disponible (problème de configuration");
        }
        $config = $configDocuments[$doc];

        //setOutputEscapingEnabled(true);
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($config['template']);

        foreach ($documentDatas as $key => $value) {
            if (is_array($value)) {
            }
            else {
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
        } catch (Exception $e) {
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
     * @return Response
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
                $this->getActivityService()->getGearmanJobLauncherService()->triggerUpdateNotificationActivity(
                    $projectGrant
                );
                $this->getActivityService()->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity(
                    $projectGrant
                );
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel(
            [
                'numerotationKeys'   => $numerotationKeys,
                'hidden'             => $hidden,
                'form'               => $form,
                'activity'           => $projectGrant,
                'numbers_keys'       => $numerotationKeys,
                'allowNodeSelection' => $this->getOscarConfigurationService()->isAllowNodeSelection(),
                "tree"               => $this->getPersonService()->getProjectGrantService()->getActivityTypesTree()
            ]
        );
        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * @return Response
     */
    public function duplicateAction()
    {
        $options = [
            'organizations' => $this->params()->fromQuery('keeporganizations', false),
            'persons'       => $this->params()->fromQuery('keeppersons', false),
            'milestones'    => $this->params()->fromQuery('keepmilestones', false),
            'workpackages'  => $this->params()->fromQuery('keepworkpackage', false),
            'admdata'       => $this->params()->fromQuery('keepadmdata', false),
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
        } catch (Exception $e) {
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
        $this->getNotificationService()->updateNotificationsActivity($entity);
        $this->flashMessenger()->addSuccessMessage('Les notifications ont été mises à jour');
        return $this->redirect()->toRoute('contract/notifications', ['id' => $entity->getId()]);
    }

    /**
     * Suppression d'une activité de recherche
     *
     * @return Response
     */
    public function deleteAction(): Response
    {
        try {
            $activity = $this->getActivityFromRoute();

            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_DELETE,
                $activity
            );

            $this->getLoggerService()->info(sprintf('Suppression de %s - %s', $activity, $activity->getId()));

            $project = $activity->getProject();

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Récupération des informations annexes
            foreach ($activity->getPersons() as $activityPerson) {
                $this->getPersonService()->personActivityRemove($activityPerson);
            }

            foreach ($activity->getOrganizations() as $activityOrganization) {
                $this->getActivityService()->activityOrganizationRemove($activityOrganization);
            }

            // On supprime les créneaux
            try {
                $this->getTimesheetService()->removeTimesheetActivity($activity);
            } catch (Exception $e) {
                throw new OscarException(
                    "Impossible de supprimer les créneaux pour cette activité : " . $e->getMessage()
                );
            }

            // Suppression des notifications
            $this->getNotificationService()->deleteNotificationActivityById($activity->getId());

            // Suppression des documents
            $documents = $activity->getDocuments();
            if (count($documents)) {
                $documentPathRoot = $this->getOscarConfigurationService()->getDocumentDropLocation();

                /** @var ContractDocument $d */
                foreach ($documents as $d) {
                    $path = $documentPathRoot . '/' . $d->getPath();
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    $this->getLoggerService()->info("Suppression du document '$d'");
                    $this->getLoggerService()->info("Fichier '$path'");
                    $this->getEntityManager()->remove($d);
                }
                $this->getEntityManager()->flush();
            }


            try {
                $this->getActivityService()->searchDelete($activity->getId());
            } catch (Exception $e) {
            }

            $this->getEntityManager()->remove($activity);
            $this->getEntityManager()->flush();

            if (!$project) {
                $this->redirect()->toRoute('contract/advancedsearch');
            }
            else {
                $this->getEntityManager()->refresh($project);

                return $this->redirect()->toRoute(
                    'project/show',
                    ['id' => $activity->getProject()->getId()]
                );
            }
        } catch (Exception $e) {
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
        }
        else {
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
            }
            else {
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
            if (!$paramID) {
                return $this->getResponseBadRequest();
            }
        }
        else {
            return $this->getResponseUnauthorized();
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
        }
        else {
            $downloader->downloadCSV($csvPath);
        }
        die();
    }

    public function gantAction()
    {
        $format = $this->params()->fromQuery('format', 'html');
        $ids = $this->params()->fromQuery('ids', '');

        if ($this->isAjax() || $format == 'json') {
            switch ($this->getHttpXMethod()) {
                case 'GET' :
                    $out = $this->baseJsonResponse();
                    $out['activities'] = [];
                    $activities = $this->getActivityService()->getActivitiesByIds(explode(',', $ids));
                    $format = new ActivityGantJson();
                    $out['activities'] = $format->formatAll($activities);
                    return $this->jsonOutput($out);

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
                'form'             => $form,
                'organization'     => $organization,
                'hidden'           => $hidden,
                'activity'         => $projectGrant,
                'project'          => null,
                'numerotationKeys' => $numerotationKeys,
                'numbers_keys'     => $numerotationKeys
            ]
        );

        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    public function myRoleAction()
    {
        $activity = $this->getActivityFromRoute('activity_id');

        $out = [
            "id"       => $activity->getId(),
            "activity" => "$activity",
            "roles"    => $this->getOscarUserContextService()->getRolesPersonInActivityDeep(
                $this->getCurrentPerson(),
                $activity
            )
        ];
        return $this->jsonOutput($out);
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
        }
        else {
            $project = null;
        }

        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_CREATE)) {
            if (!$this->getOscarUserContextService()->hasPrivilegeInOrganizations(
                Privileges::ACTIVITY_CREATE
            )) {
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
            }
            else {
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
                            'role'         => $rolesOrganizations[$idRole]
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
                try {
                    $this->getActivityService()->jobSearchUpdate($projectGrant);
                } catch (Exception $e) {
                    $this->getLoggerService()->error($e->getMessage());
                }

                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel(
            [
                'withOrganization'   => $withOrganization,
                'errorRoles'         => $errorRoles,
                'organizationRoles'  => $rolesOrganizations,
                'form'               => $form,
                'hidden'             => $hidden,
                'activity'           => $projectGrant,
                'project'            => $project,
                'numerotationKeys'   => $numerotationKeys,
                'numbers_keys'       => $numerotationKeys,
                'allowNodeSelection' => $this->getOscarConfigurationService()->isAllowNodeSelection(),
                "tree"               => $this->getPersonService()->getProjectGrantService()->getActivityTypesTree()
            ]
        );

        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * Affiche les documents pour une activité de recherche, retour JSON.
     * /activites-de-recherche/documents-json/id
     *
     * @throws OscarException
     */
    public function documentsJsonAction()
    {
        $id = $this->params()->fromRoute('id');
        $ui = $this->params()->fromQuery('ui');

        /** @var Activity $entity */
        $entity = $this->getActivityService()->getActivityById($id, true);

        $out = $this->baseJsonResponse();

        // ID des tabs (onglets pour ranger les documents)
        $arrayTabs = [];
        $entitiesTabs = $this->getContractDocumentService()->getContractTabDocuments();

        $roles = $this->getOscarUserContextService()->getRolesPersonInActivity($this->getCurrentPerson(), $entity);
        $rolesAppli = $this->getOscarUserContextService()->getBaseRoleId();
        $rolesMerged = array_merge($roles, $rolesAppli);

        $allowSign = $this->getOscarUserContextService()->hasPrivileges(SignaturePrivileges::SIGNATURE_CREATE, $entity);

        if ($this->getOscarUserContextService()->getAccessActivityDocument($entity)['read'] != true) {
            return $this->getResponseUnauthorized();
        }

        /** @var TabDocument $tabDocument */
        foreach ($entitiesTabs as $tabDocument) {
            // Traitement final attendu sur les rôles
            $access = $this->getOscarUserContextService()->getAccessTabDocument($tabDocument, $rolesMerged);
            if ($access['read']) {
                $tabId = $tabDocument->getId();
                $arrayTabs[$tabId] = $tabDocument->toJson();
                $arrayTabs[$tabId]["documents"] = [];
                $arrayTabs[$tabId]['manage'] = $access['write'] == true;
            }
        }

        //Onglet non classé
        $unclassifiedTab = [
            "id"        => "unclassified",
            "label"     => "Non-classés",
            "manage"    => false,
            "documents" => []
        ];

        $allowPrivate = true;
        //Onglet privé
        $privateTab = [
            "id"        => "private",
            "label"     => "Documents privés",
            "documents" => [],
            "manage"    => $allowPrivate
        ];

        $currentPerson = $this->getCurrentPerson();
        //Docs reliés à une activité
        /** @var ContractDocument $doc */
        foreach ($entity->getDocuments() as $doc) {
            if (!$this->getOscarUserContextService()->contractDocumentRead($doc)) {
                continue;
            }
            $process = $doc->getProcess();
            $manageProcess = false;
            if ($this->getOscarUserContextService()->hasPrivileges(SignaturePrivileges::SIGNATURE_ADMIN)) {
                if ($doc->getProcess()) {
                    $manageProcess = $this->url()->fromRoute(
                        'contractdocument/process',
                        ['id' => $doc->getId()]
                    );
                }
                else {
                    $manageProcess = null;
                }
            }

            $manage = $this->getOscarUserContextService()->contractDocumentWrite($doc);
            $processTriggerable = ($doc->getProcess() == null && $allowSign);

            $docAdded = $doc->toJson();
            $docAdded['manage_process'] = $manageProcess;
            $docAdded['process_triggerable'] = $processTriggerable;


            if (is_null($doc->getTabDocument())) {
                if ($doc->isPrivate() === true) {
                    // Droits sur les documents privés utilisateur courant associé ou non au document
                    $personsDoc = $doc->getPersons();
                    $isPresent = false;
                    foreach ($personsDoc as $person) {
                        if ($person === $currentPerson) {
                            $isPresent = true;
                        }
                    }

                    if (true === $isPresent) {
                        $docAdded['urlDelete'] = $this->url()->fromRoute(
                            'contractdocument/delete',
                            ['id' => $doc->getId()]
                        );
                        $docAdded['urlDownload'] = $this->url()->fromRoute(
                            'contractdocument/download',
                            ['id' => $doc->getId()]
                        );
                        $docAdded['urlReupload'] = $this->url()->fromRoute(
                            'contractdocument/upload',
                            [
                                'idactivity' => $entity->getId(),
                                'idtab'      => 'private',
                                'id'         => $doc->getId()
                            ]
                        );
                        $docAdded['urlPerson'] = false;
                    }
                    $privateTab ["documents"] [] = $docAdded;
                }
                else {
                    $unclassifiedTab ["documents"] [] = $docAdded;
                }
            }
            else {
                if (!array_key_exists($doc->getTabDocument()->getId(), $arrayTabs)) {
                    continue;
                }
                if (array_key_exists($doc->getTabDocument()->getId(), $arrayTabs)) {
                    $docAdded['urlDownload'] = $this->url()->fromRoute(
                        'contractdocument/download',
                        ['id' => $doc->getId()]
                    );
                    if ($arrayTabs[$doc->getTabDocument()->getId()]['manage']) {
//                    if ($doc->getTabDocument()->isManage($rolesMerged)) {
                        $docAdded['urlDelete'] = $this->url()->fromRoute(
                            'contractdocument/delete',
                            ['id' => $doc->getId()]
                        );
                        $docAdded['urlReupload'] = $this->url()->fromRoute(
                            'contractdocument/upload',
                            [
                                'idactivity' => $entity->getId(),
                                'idtab'      => $doc->getTabDocument()->getId(),
                                'id'         => $doc->getId()
                            ]
                        );
                        $docAdded['urlPerson'] = false;
                    }
                    $arrayTabs[$doc->getTabDocument()->getId()]["documents"] [] = $docAdded;
                }
            }
        } // End boucle

        if ($privateTab && $privateTab['documents']) {
            $arrayTabs['private'] = $privateTab;
        }

        $generatedDocuments = $this->getOscarConfigurationService()->getConfiguration(
            'generated-documents.activity'
        );
        $generatedDocumentsJson = [];
        foreach ($generatedDocuments as $key => $infos) {
            $generatedDocumentsJson[] = [
                'url'   => $this->url()->fromRoute(
                    'contract/generatedocument',
                    ['id' => $entity->getId(), 'doc' => $key]
                ),
                'label' => $infos['label']
            ];
        }

        $typesDocuments = [];
        $signatureFlowParams = [];
        $typesDocumentsDatas = $this->getActivityService()->getTypesDocuments(false);

        // Signatures disponibles (avec les personnes associées dans le contexte de l'activité)
        $processDatas = [];
        foreach ($this->getSignatureService()->getSignatureFlows() as $flow) {
            $flowId = $flow['id'];
            $signatureFlowDatas = $this->getSignatureService()->createSignatureFlowDatasById(
                "",
                $flowId,
                ['activity_id' => $entity->getId()]
            );

            $processDatas[] = $signatureFlowDatas['signatureflow'];
        }

        // Types de document
        foreach ($typesDocumentsDatas as $typeDocument) {
            $typeDatas = $typeDocument->toArray();
            $typeDatas['flow'] = false;
            $typesDocuments[] = $typeDatas;
        }

        $out['process_datas'] = $processDatas;
        $out['tabsWithDocuments'] = $arrayTabs;
        $out['typesDocuments'] = $typesDocuments;
        $out['idCurrentPerson'] = $this->getCurrentPerson()->getId();
        $out['computedDocuments'] = $generatedDocumentsJson;

        return new JsonModel($out);
    }

    /**
     * @return array
     * @throws OscarException
     */
    public function notificationsAction(): array
    {
        /** @var Activity $entity */
        $entity = $this->getActivityFromRoute();

        // Check access
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_NOTIFICATIONS_SHOW, $entity);

        $notificationService = $this->getNotificationService();

        $notificationJson = [];
        foreach ($notificationService->notificationsActivity($entity) as $n) {
            $notification = $n->toArray();
            $notification['persons'] = [];
            foreach ($n->getPersons() as $personNotification) {
                $notification['persons'][] = [
                    'person' => (string)$personNotification->getPerson(),
                    'read'   => $personNotification->getRead() ? $personNotification->getRead()->format(
                        'Y-m-d'
                    ) : false,
                ];
            }
            $notificationJson[] = $notification;
        }

        return [
            'activity'      => $entity,
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
                }
                else {
                    return [
                        'activity' => $entity,
                        'json'     => $this->getActivityService()->getActivityJson(
                            $id,
                            $this->getOscarUserContextService()
                        )
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

        $datas = [
            'lines'    => $lines,
            'masses'   => $masses,
            'years'    => $years,
            'totaux'   => $totaux,
            'values'   => $values,
            'activity' => $entity
        ];

        try {
            $this->getDocumentFormatterService()->buildAndDownload(
                $this->getOscarConfigurationService()->getEstimatedSpentActivityTemplate(),
                $datas,
                $format,
                'depenses-previsionnelles-' . $entity->getOscarNum(),
                DocumentFormatterService::PDF_ORIENTATION_PORTRAIT
            );
        } catch (Exception $e) {
            throw new OscarException(
                "Impossible de générer le document d'estimation des dépenses : " . $e->getMessage()
            );
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
        }
        elseif ($method == 'POST') {
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
                    }
                    else {
                        if (array_key_exists($compte, $out) && array_key_exists($year, $out[$compte])) {
                            $this->getEntityManager()->remove($out[$compte][$year]);
                        }
                    }
                }
            }
            try {
                $this->getEntityManager()->flush();
            } catch (Exception $e) {
                return $this->getResponseInternalError(
                    "Impossible d'enregistrer le budget prévisionnel : " . $e->getMessage()
                );
            }
            return $this->getResponseOk();
        }

        return [
            'activity' => $entity,
            'lines'    => $lines,
            'masses'   => $masses,
            'years'    => $years,
            'types'    => $types,
            'values'   => $values,
        ];
    }

    /**
     * Retourne les données de synthèse des dépenses d'une activité de recherche.
     *
     * @return Response|JsonModel
     * @throws Exception
     */
    public function spentSynthesisActivityAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        // Multiple ids
        $ids = explode(",", $id);

        try {
            $masses = $this->getOscarConfigurationService()->getMasses();
            $pfis = [];

            $lastUpdate = null;

            foreach ($ids as $id) {
                /** @var Activity $entity */
                $entity = $this->getActivityService()->getActivityById($id, true);
                $this->getOscarUserContextService()->check(Privileges::DEPENSE_SHOW, $entity);
                $pfis[] = $entity->getCodeEOTP();
                if ($lastUpdate == null || $entity->getDateTotalSpent() > $lastUpdate) {
                    $lastUpdate = $entity->getDateTotalSpent();
                }
            }

            // Check access
            $pfis = array_unique($pfis);

            // Method
            $method = $this->getHttpXMethod();

            if ($method == 'POST') {
                // Vérifiaction des droits d'accès
                $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE);

                // Récupération des affectations
                $postedAffectations = $this->params()->fromPost('affectation');

                try {
                    $this->getSpentService()->updateAffectation($postedAffectations);
                } catch (Exception $e) {
                    return $this->getResponseInternalError($e->getMessage());
                }
                return $this->getResponseOk("Affectation des comptes terminée");
            }

            if (count($pfis) == 0) {
                return $this->getResponseInternalError("Pas de numéro financier");
            }

            $out = $this->baseJsonResponse();
            $out['error'] = null; // Affiche les erreurs survenue lors de la récupération/synchronisation des données
            $out['warning'] = null; // Affiche les avertissements

            // Construction des données de dépense
            $out['masses'] = $masses;
            $out['dateUpdated'] = $entity->getDateTotalSpent();
            $out['synthesis'] = $this->getSpentService()->getSynthesisDatasPFI(
                $pfis,
                $this->getOscarUserContextService()->hasPrivileges(
                    Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE
                ),
                'basic'
            );
        } catch (Exception $e) {
            return $this->getResponseInternalError("Impossible de charger les dépenses pour la/les activité(s)");
        }


        return $this->jsonOutput($out);
    }

    /**
     * activites-de-recherche/fiche-detaillee/idActivité
     *
     * @return array
     * @throws OscarException
     * @throws NoResultException
     * @throws NonUniqueResultException|\Doctrine\ORM\Exception\NotSupported
     */
    public function showAction(): array
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        if (!$entity) {
            throw new OscarException("Cette activité n'existe plus/pas");
        }

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

        //$documentTypes = $this->getActivityService()->getTypesDocuments();

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
            } catch (Exception $e) {
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


            'declarations'   => $declarations,

            // Jeton de sécurité
            'tokenValue'     => "", // $this->getOscarUserContextService()->getTokenValue(true),

            // Personnes pouvant voir cette activité
            'involvedPerson' => $involvedPersonsJSON,

            'rolesOrganizations' => $rolesOrganizations,
            'rolesPersons'       => $rolesPersons,

            // Notifications précalculées
            'notifications'      => $this->getEntityManager()->getRepository(Notification::class)
                ->findBy(['object' => Notification::OBJECT_ACTIVITY, 'objectId' => $activity->getId()]),


            //'documentTypes' => json_encode($documentTypes),
            'activityTypeChain'  => $activityTypeChain,
            'traces'             => $this->getActivityLogService()->activityActivities($id)->getQuery()->getResult(),
        ];
    }

    public function personsAccessDeepAction()
    {
        $activityId = $this->params()->fromRoute('id');
        $activity = $this->getActivityService()->getActivityById($activityId);
        $access = $this->getActivityService()->getPersonsAccessDeeper($activity);
        die("ACCES dans '$activity'");
    }

    public function spentListAction()
    {
        $action = $this->params()->fromPost('action', null);
        $activity = $this->getActivityService()->getActivityById($this->params()->fromRoute('id'));
        $this->getOscarUserContextService()->check(Privileges::DEPENSE_DETAILS, $activity);
        $msg = "";
        $error = "";


        if ($action && $action == 'update') {
            $this->getOscarUserContextService()->check(Privileges::DEPENSE_SYNC, $activity);
            try {
                $msg = $this->getSpentService()->syncSpentsByEOTP($activity->getCodeEOTP());
            } catch (Exception $e) {
                $error = "Impossible de mettre à jour les dépenses : " . $e->getMessage();
            }
        }
        $spents = $this->getSpentService()->getGroupedSpentsDatas($activity->getCodeEOTP());
        return [
            'masses'   => $this->getOscarConfigurationService()->getMasses(),
            'activity' => $activity,
            'spents'   => $spents,
            'msg'      => $msg,
            'error'    => $error
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
            /** @var \Laminas\Http\Request $request */
            $request = $this->getRequest();
            if ($request->isPost()) {
                try {
                    $project = $this->getProjectService()->getProject($request->getPost('project_id'));
                } catch (Exception $e) {
                }

                if ($entity->getProject()) {
                    $entity->getProject()->touch();
                }
                $entity->setProject($project);
                $entity->touch();
                $this->getEntityManager()->flush();
                // Update project index
                $this->getActivityService()->getGearmanJobLauncherService()->triggerUpdateNotificationActivity(
                    $entity
                );
                $this->getActivityService()->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity(
                    $entity
                );
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $entity->getId()]
                );
            }
            $view = new ViewModel(['activity' => $entity]);
            $view->setTemplate('/oscar/project/project-selector.phtml');

            return $view;
        }
        else {
            throw new Exception(sprintf("L'activité n'existe pas"));
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
        // Récupération de l'activité
        $activity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PERSON_SHOW, $activity);
        $manage = $this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity);
        $out = [
            'persons' => [],
            'manage'  => $manage,
            'urlNew'  => $this->url()->fromRoute('personactivity/new', ['idenroller' => $activity->getId()]),
            'roles'   => $this->getOscarUserContextService()->getRolesPersonsInActivityArray()
        ];


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
                $contextKey = $activityPerson->getActivity()->getOscarNum();
                $idEnroller = $activityPerson->getActivity()->getId();
            }
            else {
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
                $contextKey = $activityPerson->getProject()->getAcronym();
                $idEnroller = $activityPerson->getProject()->getId();
            }
            $urlShow = false;
            if ($showable) {
                $urlShow = $showable ? $this->url()->fromRoute(
                    'person/show',
                    ['id' => $activityPerson->getPerson()->getId()]
                ) : false;
            }

            $out['persons'][] = [
                'id'            => $activityPerson->getId(),
                'role'          => $activityPerson->getRole(),
                'roleLabel'     => $activityPerson->getRole(),
                'roleId'        => $activityPerson->getRoleObj() ? $activityPerson->getRoleObj()->getId() : "",
                'rolePrincipal' => $activityPerson->isPrincipal(),
                'urlDelete'     => $urlDelete,
                'context'       => $context,
                'contextKey'    => $contextKey,
                'urlEdit'       => $urlEdit,
                'urlShow'       => $urlShow,
                'past'          => $activityPerson->isPast(),
                'enroller'      => $idEnroller,
                'enrollerLabel' => $activity->getLabel(),
                'editable'      => $editable,
                'deletable'     => $deletable,
                'enrolled'      => $activityPerson->getPerson()->getId(),
                'enrolledLabel' => $activityPerson->getPerson()->getDisplayName(),
                'start'         => DateTimeUtils::toStr($activityPerson->getDateStart(), 'Y-m-d'),
                'end'           => DateTimeUtils::toStr($activityPerson->getDateEnd(), 'Y-m-d'),
            ];
        }

        return $this->ajaxResponse($out);
    }

    public function organizationsAction()
    {
        $activityId = $this->params()->fromRoute('id');
        $activity = $this->getActivityService()->getActivityById($activityId);

        $this->getOscarUserContextService()->check(
            Privileges::ACTIVITY_ORGANIZATION_SHOW,
            $activity
        );

        $manage = $this->getOscarUserContextService()->hasPrivileges(
            Privileges::ACTIVITY_ORGANIZATION_MANAGE,
            $activity
        );

        $out = [
            'organizations' => [],
            'roles'         => $this->getOscarUserContextService()->getRolesOrganizationInActivityArray(),
            'urlNew'        => $this->url()->fromRoute(
                'organizationactivity/new', ['idenroller' => $activity->getId()]
            ),
            'manage'        => $manage
        ];

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
            ActivityPerson::class       => 'personactivity',
            ProjectMember::class        => 'personproject',
            ProjectPartner::class       => 'organizationproject'
        ];

        /**
         * @var ActivityOrganization $activityOrganization
         */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            // Cas particulier (affectation sans rôle)
            // rôle supprimé ? manipulation extérieur
            if (!$activityOrganization->getRoleObj()) {
                $this->getLoggerService()->warning(
                    sprintf(
                        "L'organisation '%s' n'a pas d'objet rôle sur '%s'",
                        $activityOrganization->getOrganization(),
                        $activityOrganization->getEnroller()
                    )
                );
                $roleId = 0;
                $roleprincipal = false;
                $rolelabel = "Rôle inconnu";
                $role = null;
            }
            else {
                $roleId = $activityOrganization->getRoleObj()->getId();
                $roleprincipal = $activityOrganization->getRoleObj()->isPrincipal();
                $rolelabel = $activityOrganization->getRoleObj()->getRoleId();
                $role = $activityOrganization->getRoleObj();
            }

            $class = get_class($activityOrganization);

            if ($class == ActivityOrganization::class) {
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
                $contextKey = $activityOrganization->getActivity()->getOscarNum();
            }
            else {
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
                $contextKey = $activityOrganization->getProject()->getAcronym();
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

            $out['organizations'][] = [
                'id'            => $activityOrganization->getId(),
                'roleId'        => $roleId,
                'role'          => $rolelabel,
                'roleLabel'     => $rolelabel,
                'rolePrincipal' => $roleprincipal,
                'urlDelete'     => $urlDelete,
                'context'       => $context,
                'contextKey'    => $contextKey,
                'urlEdit'       => $urlEdit,
                'urlShow'       => $urlShow,
                'enroller'      => $activity->getId(),
                'enrollerLabel' => (string)$activity,
                'editable'      => $editable,
                'deletable'     => $deletable,
                'enrolled'      => $activityOrganization->getOrganization()->getId(),
                'enrolledLabel' => $activityOrganization->getOrganization()->getFullName(),
                'past'          => !$activityOrganization->isActive(),
                'start'         => DateTimeUtils::toStr($activityOrganization->getDateStart(), 'Y-m-d'),
                'end'           => DateTimeUtils::toStr($activityOrganization->getDateEnd(), 'Y-m-d')
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
            'persons'   => $persons
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

    /**
     * @return array|ViewModel
     * @throws OscarException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function activitiesOrganizationsAction(): array|ViewModel
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
     * @return array
     */
    public function searchActivityAction(): array|ViewModel|Response
    {
        $search = $this->params()->fromQuery('q', "");
        $options = [];

        try {
            $activities = $this->getProjectGrantService()->searchActivities($search, $options);
        } catch (Exception $e) {
            return $this->getResponseBadRequest($e->getMessage());
        }

        $view = new ViewModel(
            [
                'search'     => $search,
                'activities' => $activities,
            ]
        );
        $view->setTemplate('oscar/activity/search.phtml');
        return $view;
    }

    /**
     * Recherche avancée des activités.
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return ViewModel
     * @throws Exception
     */
    public function applyAdvancedSearch(): ViewModel
    {
        $datas = $this->getProjectGrantSearchService()->searchFromRequest(
            $this->getRequest(),
            $this->getOrganizationPerimeter()
        );
        if( $this->isAjax() || $this->getRequest()->getQuery('f') === 'json' ){
            $activities = [];
            foreach ($datas['activities'] as $activity) {
                $activities[] = $activity->toArray();
            }
            $datas['activities'] = $activities;
            $view = new JsonModel($datas);
        } else {
            $view = new ViewModel(
                $datas
            );
            $view->setTemplate('oscar/project-grant/advanced-search.phtml');
        }
        return $view;
    }

    /**
     * Nouveau système de recherche des activités.
     *
     * @return array|ViewModel
     * @throws Exception
     */
    public function advancedSearchAction(): array|ViewModel
    {
        return $this->applyAdvancedSearch();
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
                'entities'     => $activities,
                'filterLabel'  => "débutant prochainement",
                'datePrefix'   => "Débute",
                'getDateGroup' => 'getDateStart',
                'persons'      => $persons,

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
                'entities'     => $activities,
                'filterLabel'  => "se terminant bientôt",
                'datePrefix'   => "Se termine",
                'getDateGroup' => 'getDateEnd',
                'persons'      => $persons,
            ]
        );
        $view->setTemplate('oscar/activity/list-view.phtml');

        return $view;
    }

    /**
     * Affiche la liste des activités soumises à un processus PCRU.
     *
     * @return array
     */
    public function pcruListAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_PCRU_LIST);
        $accessUpload = $this->getOscarUserContextService()->hasPrivileges(Privileges::MAINTENANCE_PCRU_UPLOAD);
        $pcruInfos = $this->getProjectGrantService()->getPCRUService()->getPcruInfos();
        $methods = $this->getHttpXMethod();

        if ($methods == 'GET') {
            $action = $this->params()->fromQuery('a');

            // Recherche des activités
            if ($action == 'search') {
                $search = $this->params()->fromQuery('search');
                $idsActivities = $this->getProjectGrantService()->search($search);
                $activities = [];
                /** @var Activity $activity */
                foreach ($this->getProjectGrantService()->getActivitiesByIds($idsActivities) as $activity) {
                    $a = $activity->toArray();
                    $a['pcru'] = [];
                    $a['pcruenable'] = false;
                    $activities[] = $a;
                }
                return $this->jsonOutput(["activities" => $activities]);
            }

            // Aperçu PCRU
            if ($action == 'preview') {
                $activity_id = $this->params()->fromQuery('activity_id');
                $activity = $this->getProjectGrantService()->getActivityById($activity_id);
                $preview = $this->getProjectGrantService()->getPCRUService()->getPreview($activity);
                return $this->jsonOutput(["preview" => $preview]);
            }

            if ($action == 'download') {
                $pcru = $this->getProjectGrantService()->getPCRUService()->downloadPCRUSendableFile();
            }
        }
        elseif ($methods == "POST") {
            $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_PCRU_UPLOAD);
            $action = $this->params()->fromPost('action');
            if ($action == 'upload') {
                $this->getProjectGrantService()->getPCRUService()->upload();
                $this->redirect()->toRoute('contract/pcru-list');
            }
        }

        return [
            'downloadable' => $this->getProjectGrantService()->getPCRUService()->hasDownload(),
            'uploadable'   => !$this->getProjectGrantService()->getPCRUService()->hasUploadInProgress(
                ) && $accessUpload,
            'pcruInfos'    => $pcruInfos
        ];
    }

    public function timesheetAction()
    {
        /** @var Activity $activity */
        $activity = $this->getActivityFromRoute();

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity);

        if ($this->isAjax()) {
            $action = $this->getRequest()->getQuery()->get('a', null);

            if ($this->getRequest()->isDelete() || $action == 'd') {
                $this->getOscarUserContextService()->check(Privileges::ACTIVITY_EDIT, $activity);
                $person_id = $this->getRequest()->getQuery()->get('p');
                $where = $this->getRequest()->getQuery()->get('w');
                try {
                    $this->getTimesheetService()->removeValidatorActivity($person_id, $activity->getId(), $where);
                } catch (Exception $e) {
                    return $this->getResponseInternalError($e->getMessage());
                }
            }

            if ($this->getRequest()->isPost() && $action != 'd') {
                //
                $this->getOscarUserContextService()->check(Privileges::ACTIVITY_EDIT, $activity);
                $person_id = $this->getRequest()->getPost()->get('person_id');
                $where = $this->getRequest()->getPost()->get('where');
                try {
                    $this->getTimesheetService()->addValidatorActivity($person_id, $activity->getId(), $where);
                } catch (Exception $e) {
                    return $this->getResponseInternalError($e->getMessage());
                }
            }

            $response = $this->baseJsonResponse();
            $response['workpackages'] = $this->getTimesheetService()->getDatasActivityWorkpackages($activity);
            $response['validators'] = $this->getTimesheetService()->getDatasValidatorsActivity($activity);
            $response['members'] = $this->getTimesheetService()->getDatasActivityMembers(
                $activity,
                $this->getOscarUserContextService()->hasPrivileges(Privileges::PERSON_SHOW),
                $this->url()
            );
            $response['validations'] = $this->getTimesheetService()->getDatasActivityValidations($activity);
            $response['validators_editable'] = $this->getOscarUserContextService()->hasPrivileges(
                Privileges::ACTIVITY_EDIT,
                $activity
            );

            return $this->jsonOutput($response);
        }

        $timesheetRepport = null;
        if ($activity->isTimesheetAllowed()) {
            $timesheetRepport = $this->getTimesheetService()->getSynthesisActivityPeriods(
                $activity->getDateStartStr('Y-m'),
                $activity->getDateEndStr('Y-m'),
                $activity->getId()
            );
        }

        return [
            'activity'         => $activity,
            'timesheetAllow'   => $activity->isTimesheetAllowed(),
            'timesheetRepport' => $timesheetRepport
        ];
    }

    /**
     * Gestion/récapitulatif des informations PCRU
     *
     * @return array|Response
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function pcruInfosAction()
    {
        /** @var Activity $activity */
        $activity = $this->getActivityFromRoute();

        // Accès
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PCRU, $activity);

        if ($this->params()->fromQuery("a") == "reset") {
            $this->getProjectGrantService()->getPCRUService()->resetTmpPcruInfos($activity);
            $this->redirect()->toRoute('contract/pcru-infos', ['id' => $activity->getId()]);
        }

        if ($this->params()->fromQuery("a") == "activate") {
            // Formulaire
            $form = new ActivityInfosPcruForm($this->getProjectGrantService(), $activity);
            $preview = $this->getProjectGrantService()->getPCRUService()->getPreview($activity);
            $pcruInfos = $preview['infos'];
            $form->init();
            $form->bind($pcruInfos);

            if ($this->getRequest()->getMethod() == "POST") {
                $posted = $this->getRequest()->getPost();
                $form->setData($posted);
                if ($form->isValid()) {
                    $this->getProjectGrantService()->getPCRUService()->activateActivity($activity, $pcruInfos);
                    return $this->redirect()->toRoute('contract/pcru-infos', ['id' => $activity->getId()]);
                }
                else {
                }
            }

            $preview['form'] = $form;
            $preview['activity'] = $activity;
            $view = new ViewModel($preview);
            $view->setTemplate('oscar/activity/pcruinfos-form.phtml');

            return $view;
        }

        if ($this->params()->fromQuery("a") == "edit") {
            // Formulaire
            $form = new ActivityInfosPcruForm($this->getProjectGrantService(), $activity);
            $preview = $this->getProjectGrantService()->getPCRUService()->getPreview($activity);
            $pcruInfos = $preview['infos'];
            $form->init();
            $form->bind($pcruInfos);

            if ($this->getRequest()->getMethod() == "POST") {
                $posted = $this->getRequest()->getPost();
                $form->setData($posted);
                if ($form->isValid()) {
                    $this->getProjectGrantService()->getPCRUService()->updatePcruInfos($activity, $pcruInfos);
                    return $this->redirect()->toRoute('contract/pcru-infos', ['id' => $activity->getId()]);
                }
                else {
                }
            }

            $preview['form'] = $form;
            $preview['mode'] = "edit";
            $preview['activity'] = $activity;
            $view = new ViewModel($preview);
            $view->setTemplate('oscar/activity/pcruinfos-form.phtml');

            return $view;
        }

        $method = $this->getHttpXMethod();

        if ($method == 'POST') {
            $action = $this->params()->fromPost('action');
            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PCRU_ACTIVATE, $activity);
            switch ($action) {
                case 'remove-waiting';
                    $idActivityPcruInfo = intval($this->params()->fromPost('activitypcruinfo_id'));
                    $this->getProjectGrantService()->getPCRUService()->removeWaiting($idActivityPcruInfo);
                    $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);

                case 'add-pool':
                    $this->getProjectGrantService()->getPCRUService()->addToPool($activity);
                    break;

                case 'download-pcru':
                    $this->getProjectGrantService()->getPCRUService()->downloadOne($activity);
                    break;
            }
            return $this->redirect()->toRoute('contract/pcru-infos', ['id' => $activity->getId()]);
        }

        $return = $this->getProjectGrantService()->getPCRUService()->getPreview($activity);

        $return['contratSignedType'] = $this->getOscarConfigurationService()
            ->getOptionalConfiguration('pcru_contrat_type', "Contrat Version Définitive Signée");

        /** @var ActivityPcruInfos $pcruInfos */
        $pcruInfos = $return['infos'];

        if ($pcruInfos->isWaiting()) {
            $return['deletable'] = true;
            $return['activitypcruinfo_id'] = $pcruInfos->getId();
        }

        $return['poolopen'] = $this->getProjectGrantService()->getPCRUService()->isPoolOpen();

        return $return;
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
