<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\View\Model\JsonModel;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNote;
use Oscar\Entity\ActivityNoteRepository;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ActivityType;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\DateType;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Repository\TypeDocumentRepository;
use Oscar\Entity\Role;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Formatter\Person\PersonFormatterArray;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Traits\UseSpentService;
use Oscar\Traits\UseSpentServiceTrait;
use UnicaenSignature\Provider\SignaturePrivileges;
use UnicaenSignature\Service\SignatureService;
use UnicaenSignature\Utils\SignatureConstants;

class ProjectGrantApiService implements UseEntityManager, UsePersonService, UseOscarConfigurationService,
                                        UseProjectGrantService, UseLoggerService, UseSpentService, UseServiceContainer
{

    use UseEntityManagerTrait, UsePersonServiceTrait, UseOscarConfigurationServiceTrait, UseProjectGrantServiceTrait, UseLoggerServiceTrait, UseSpentServiceTrait, UseServiceContainerTrait;

    ///////////////////////////////////////////////////////////////////////////////////////////////////////// PERIMETRES
    const PERIMETER_ADMINISTRATION = 'administration';
    const PERIMETER_BUDGET = 'budget';
    const PERIMETER_CORE = 'core';
    const PERIMETER_DOCUMENTS = 'documents';
    const PERIMETER_NOTES = 'notes';
    const PERIMETER_MILESTONES = 'milestones';
    const PERIMETER_ORGANIZATIONS = 'organizations';
    const PERIMETER_PAYMENTS = 'payments';
    const PERIMETER_PERSONS = 'persons';
    const PERIMETER_PROJECT = 'project';
    const PERIMETER_SPENTS = 'spents';
    const PERIMETER_TIMESHEETS = 'timesheets';
    const PERIMETER_WORKPACKAGES = 'workpackages';


    public function getPerimetersKeys(): array
    {
        return [
            self::PERIMETER_ADMINISTRATION,
            self::PERIMETER_BUDGET,
            self::PERIMETER_CORE,
            self::PERIMETER_DOCUMENTS,
            self::PERIMETER_MILESTONES,
            self::PERIMETER_NOTES,
            self::PERIMETER_ORGANIZATIONS,
            self::PERIMETER_PAYMENTS,
            self::PERIMETER_PROJECT,
            self::PERIMETER_PERSONS,
            self::PERIMETER_SPENTS,
            self::PERIMETER_TIMESHEETS,
            self::PERIMETER_WORKPACKAGES,
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////// ENDPOINT
    public function getActivityJson(
        int $activityId,
        ?Url $urlPlugin = null,
        ?OscarUserContext $oscarUserContext = null,
        ?string $perimeters = null
    ): array {
        try {
            $activity = $this->getActivityRepository()->find($activityId);
        } catch (\Exception $exception) {
            throw new OscarException($exception->getMessage());
        }

        if ($perimeters !== null) {
            $perimeters = array_intersect(explode(',', $perimeters), $this->getPerimetersKeys());
        }
        else {
            $perimeters = $this->getPerimetersKeys();
        }

        $credentials = $this->getActivityJsonCredentials($activity, $oscarUserContext, $perimeters);

        $out = [
            'date'        => date('Y-m-d H:i:s'),
            'error'       => null,
            'warnings'    => null,
            'perimeter'   => $perimeters,
            'credentials' => null,
            'datas'       => null
        ];

        if ($credentials['read'] !== true) {
            $out['error'] = 'access denied';
            return $out;
        }
        else {
            $out['credentials'] = $credentials;
            $out['datas'] = $this->getActivityJsonDatas($activity, $urlPlugin, $perimeters);
            foreach ($out['datas'] as $key => $content) {
                if (!array_key_exists($key, $credentials)) {
                    unset($out['datas'][$key]);
                }
                else {
                    if ($credentials[$key]['read'] !== true) {
                        unset($out['datas'][$key]);
                    }
                    else {
                        // Traitement des onglets de documents
                        if ($key === 'documents') {
                            foreach ($credentials[$key]['tabs'] as $tabId => $tabAccess) {
                                if ($tabAccess['read'] !== true) {
                                    unset($out['datas'][$key]['tabs'][$tabId]);
                                }
                            }
                        }
                    }
                }
            }
            return $out;
        }
    }


    protected function getRolesCurrentPersonActivity(OscarUserContext $oscarUserContext, Activity $activity): array
    {
        return $oscarUserContext->getRolesPersonInActivityDeep(
            $oscarUserContext->getCurrentPerson(),
            $activity
        );
    }

    public function getActivityJsonCredentials(
        Activity $activity,
        OscarUserContext $oscarUserContext,
        array $perimeters
    ): array {
        $credentials = [
            'currentPersonId' => $oscarUserContext->getCurrentPersonId() ?: -1,
            'read'            => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_SHOW, $activity),
            'edit'            => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_EDIT, $activity),
        ];

        foreach ($perimeters as $perimeter) {
            switch ($perimeter) {
                case 'administration':
                    $credentials['administration'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::MAINTENANCE_MENU_ADMIN),
                    ];
                    break;

                case 'budget':
                    $credentials['budget'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity),
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity),
                    ];
                    break;

                case 'core':
                    $credentials['core'] = [
                        'read'           => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_PERSON_SHOW,
                            $activity
                        ),
                        'edit'           => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_PERSON_MANAGE,
                            $activity
                        ),
                        'change_project' => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_CHANGE_PROJECT,
                            $activity
                        ),
                        'new_project'    => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_CHANGE_PROJECT,
                            $activity
                        ),
                    ];
                    break;

                case 'documents':

                    $read = $oscarUserContext->hasPrivileges(
                        Privileges::ACTIVITY_DOCUMENT_SHOW,
                        $activity->getProject()
                    );
                    $credentials['documents'] = [
                        'read' => $read
                    ];

                    $entitiesTabs = $this->getContractDocumentRepository()->getTabDocuments();
                    $rolesMerged = $this->getRolesCurrentPersonActivity($oscarUserContext, $activity);
                    $arrayTabs = [];
                    foreach ($entitiesTabs as $tabDocument) {
                        $tabId = $tabDocument->getId();
                        $access = $oscarUserContext->getAccessTabDocument($tabDocument, $rolesMerged);
                        $arrayTabs[$tabId] = [
                            'id'    => $tabDocument->getId(),
                            'label' => $tabDocument->getLabel(),
                            'read'  => $access['read'],
                            'edit'  => $access['write'],
                        ];
                    }

                    $credentials['documents']['tabs'] = $arrayTabs;
                    $credentials['documents']['process_start'] = $oscarUserContext->hasPrivileges(
                        SignaturePrivileges::SIGNATURE_CREATE,
                        $activity
                    );
                    $credentials['documents']['process_manage'] = $oscarUserContext->hasPrivileges(
                        SignaturePrivileges::SIGNATURE_DELETE,
                        $activity
                    );
                    $credentials['documents']['process_admin'] = $oscarUserContext->hasPrivileges(
                        SignaturePrivileges::SIGNATURE_ADMIN,
                        $activity
                    );

                    break;

                case 'notes':
                    $credentials['notes'] = [
                        'read'   => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_NOTES_SHOW, $activity),
                        'edit'   => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_USER, $activity),
                        'manage' => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_NOTES_MANAGE_ADMIN,
                            $activity
                        ),
                    ];
                    break;

                case 'milestones':
                    $credentials['milestones'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_SHOW, $activity),
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity),
                    ];
                    break;

                case 'organizations':
                    $credentials['organizations'] = [
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE, $activity),
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_SHOW, $activity),
                        'show' => $oscarUserContext->hasPrivileges(Privileges::ORGANIZATION_SHOW),
                    ];
                    break;

                case self::PERIMETER_PAYMENTS:
                    $credentials[self::PERIMETER_PAYMENTS] = [
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity),
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity),
                    ];
                    break;
                case 'persons':
                    $credentials['persons'] = [
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity),
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity),
                        'show' => $oscarUserContext->hasPrivileges(Privileges::PERSON_SHOW),
                    ];
                    break;

                case 'project':
                    $credentials['project'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::PROJECT_SHOW, $activity->getProject()),
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_CHANGE_PROJECT, $activity),
                    ];
                    break;
                case self::PERIMETER_SPENTS:
                    $credentials['spents'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity),
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity),
                    ];
                    break;

                case 'timesheets':
                    $credentials['timesheets'] = [
                        'read'     => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity),
                        'validate' => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY,
                            $activity
                        ),
                    ];
                    break;

                case 'workpackages':
                    $credentials['workpackages'] = [
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_SHOW, $activity),
                        'edit' => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_WORKPACKAGE_MANAGE,
                            $activity
                        )
                    ];
                    break;
            }
        }

        return $credentials;
    }

    public function getActivityJsonDatas(
        Activity $activity,
        ?Url $urlPlugin = null,
        ?array $perimeters = null
    ): array {
        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getServiceContainer()->get(OscarUserContext::class);

        $currentUserId = $oscarUserContext->getCurrentPersonId() ?: -1;

        $datas = [
            "api" => "Oscar Activity API"
        ];

        foreach ($perimeters as $perimeter) {
            switch ($perimeter) {
                case self::PERIMETER_ADMINISTRATION:
                    $datas[self::PERIMETER_ADMINISTRATION] = [];
                    break;

                case self::PERIMETER_BUDGET:
                    $datas[self::PERIMETER_BUDGET] = $this->getBudgetActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_CORE:
                    $datas[self::PERIMETER_CORE] = $this->getCoreActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_DOCUMENTS:
                    $datas[self::PERIMETER_DOCUMENTS] = $this->getDocumentsActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_MILESTONES:
                    $datas[self::PERIMETER_MILESTONES] = $this->getMilestonesActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_NOTES:
                    $datas[self::PERIMETER_NOTES] = $this->getNotesActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_ORGANIZATIONS:
                    $datas[self::PERIMETER_ORGANIZATIONS] = $this->getOrganizationsActivity(
                        $activity,
                        $urlPlugin
                    );
                    break;

                case self::PERIMETER_PAYMENTS:
                    $datas[self::PERIMETER_PAYMENTS] = $this->getPaymentsActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_PERSONS:
                    $datas[self::PERIMETER_PERSONS] = $this->getPersonsActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_SPENTS:
                    $datas[self::PERIMETER_SPENTS] = $this->getSpentsActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_TIMESHEETS:
                    $datas[self::PERIMETER_TIMESHEETS] = $this->getTimesheetsActivity($activity, $urlPlugin);
                    break;

                case self::PERIMETER_WORKPACKAGES:
                    $datas[self::PERIMETER_WORKPACKAGES] = $this->getWorkpackagesActivity(
                        $activity,
                        $urlPlugin
                    );
                    break;
            }
        }

        return $datas;
    }

    /**
     * Informations de base de l'activité.
     *
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     */
    public function getCoreActivity(Activity $activity, ?Url $urlPlugin): array
    {
        $types = $this->getProjectGrantService()->getActivityTypeService()->getActivityTypeChain(
            $activity->getActivityType()
        );

        if (count($types) > 0 && $types[0]->getLabel() === 'ROOT') {
            array_shift($types);
        }

        $typesJson = [];
        foreach ($types as $type) {
            $typesJson[] = $type->toJson();
        }

        $project = null;
        if ($activity->getProject() !== null) {
            $project = [
                'id'          => $activity->getProject()->getId(),
                'label'       => $activity->getProject()->getLabel(),
                'acronym'     => $activity->getProject()->getAcronym(),
                'description' => $activity->getProject()->getDescription(),
                'url_show'    => $urlPlugin->fromRoute('project/show', ['id' => $activity->getProject()->getId()])
            ];
        }
        return [
            'id'           => $activity->getId(),
            'label'        => $activity->getLabel(),
            'numOscar'     => $activity->getOscarNum(),
            'status'       => $activity->getStatus(),
            'status_label' => $activity->getStatusLabel(),
            'pfi'          => $activity->getCodeEOTP(),
            'acronym'      => $activity->getAcronym(),
            'project'      => $project,
            'disciplines'  => $activity->getDisciplinesArray(),
            'type'         => $activity->getActivityType() ? (string)$activity->getActivityType() : null,
            'type_chain'   => $typesJson,
            'type_id'      => $activity->getActivityType() ? $activity->getActivityType()->getId() : null,
            'dateStart'    => $this->formatDateTime($activity->getDateStart()),
            'dateEnd'      => $this->formatDateTime($activity->getDateEnd()),
            'dateSigned'   => $this->formatDateTime($activity->getDateSigned()),
            'dateUpdated'  => $this->formatDateTime($activity->getDateUpdated()),
            'dateOpened'   => $this->formatDateTime($activity->getDateOpened()),
            'urls'         => [
                'edit'           => $urlPlugin->fromRoute('contract/edit', ['id' => $activity->getId()]),
                'change_project' => $urlPlugin->fromRoute('contract/moveToProject', ['id' => $activity->getId()]),
                'new_project'    => $urlPlugin->fromRoute('project/new') . '?ids=' . $activity->getId(),
            ]
        ];
    }

    /**
     * Informations générales liées au budget.
     *
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     */
    public function getBudgetActivity(Activity $activity, ?Url $urlPlugin): array
    {
        return [
            'amount'                         => $activity->getAmount(),
            'montant'                        => $activity->getAmount(),
            'currency'                       => $activity->getCurrency()->toJson(),
            'fraisDeGestion'                 => $activity->getFraisDeGestionDisplay(),
            'fraisDeGestionPartHebergeur'    => $activity->getFraisDeGestionPartHebergeurDisplay(),
            'fraisDeGestionPartUnite'        => $activity->getFraisDeGestionPartUniteDisplay(),
            'fraisDeGestionPartGestionnaire' => $activity->getFraisDeGestionPartGestionnaireDisplay(),
            'tva'                            => (string)$activity->getTva(),
            'assietteSubventionnable'        => $activity->getAssietteSubventionnable(),
        ];
    }

    /**
     * Documents de l'activité.
     *
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     */
    public function getDocumentsActivity(Activity $activity, ?Url $urlPlugin = null): array
    {
        // TODO Traitement des documents ici (pour le moment, le composant récupère un URL qui fournie les documents)
        $out = [
            'url'                => $urlPlugin->fromRoute(
                'contractdocument/activity',
                ['activity_id' => $activity->getId()]
            ),
            'url_upload_new_doc' => $urlPlugin->fromRoute(
                'contractdocument/upload',
                ['idactivity' => $activity->getId()]
            ),
            'url_sign_document'  => "/url_sign_document",
            'entities'           => [],
            'typesDocuments'     => [],
            'tabs'               => []
        ];

        /////// DOCUMENTS des ONGLETS
        $entitiesTabs = $this->getContractDocumentRepository()->getTabDocuments();
        $arrayTabs = [];
        foreach ($entitiesTabs as $tabDocument) {
            $tabId = $tabDocument->getId();
            $arrayTabs[$tabId] = $tabDocument->toJson();
            $arrayTabs[$tabId]["documents"] = [];
        }

        /** @var ContractDocument $doc */
        foreach ($activity->getDocuments() as $doc) {
            $process = $doc->getProcess();
            $tabId = $doc->getTabDocument()->getId();

            $allowRead = true;
            $allowManage = true;
            $allowDownload = $allowRead;
            $allowReUpload = $allowManage;
            $allowDelete = $allowManage;
            $allowEdit = $allowManage;

            $process_update_url = null;
            $process_delete_url = null;

            $allowProcessCreate = false;
            $allowProcessDelete = false;
            $allowProcessUpdate = false;

            // Il y a un processus en cours
            if ($process) {
                $allowReUpload = false;

                $allowProcessUpdate = true;

                if ($process->isFinished()) {
                    $allowProcessUpdate = false;
                    $allowDelete = true;
                }
                else {
                    $allowDelete = true;
                }

                if ($allowProcessUpdate) {
                    $process_update_url = $urlPlugin->fromRoute(
                        'contractdocument/process',
                        ['id' => $doc->getId()]
                    );
                }
            }
            else {
                $allowProcessCreate = true;
            }

            // Accès aux fonctionnalités du document.
            $docAdded = $doc->toJson();

            $docAdded['activity'] = $activity->toJson();

            if ($allowRead) {
                $download_url = $urlPlugin->fromRoute('contractdocument/download', ['id' => $doc->getId()]);
            }

            if ($allowDelete) {
                $delete_url = $urlPlugin->fromRoute('contractdocument/delete', ['id' => $doc->getId()]);
            }

            if ($allowEdit) {
                $edit_url = $urlPlugin->fromRoute('contractdocument/edit', ['document_id' => $doc->getId()]);
            }

            if ($allowProcessCreate) {
                $process_create_url = $urlPlugin->fromRoute(
                    'contractdocument/process-create',
                    ['document_id' => $doc->getId()]
                );
            }

            if ($allowProcessDelete) {
                $process_delete_url = $urlPlugin->fromRoute(
                    'contractdocument/process-delete',
                    ['document_id' => $doc->getId()]
                );
            }

            if ($allowReUpload) {
                $reupload_url = $urlPlugin->fromRoute('contractdocument/reupload', [
                    'document_id' => $doc->getId()
                ]);
            }
            $docAdded['uploader'] = $doc->getPerson() ? [
                'id'        => $doc->getPerson()->getId(),
                'firstname' => $doc->getPerson()->getFirstname(),
                'lastname'  => $doc->getPerson()->getLastname(),
            ] : null;
            $docAdded['urlProcessDelete'] = $process_delete_url;
            $docAdded['urlProcessCreate'] = $process_create_url;
            $docAdded['urlProcessUpdate'] = $process_update_url;
            $docAdded['urlDownload'] = $download_url;
            $docAdded['urlReupload'] = $reupload_url;
            $docAdded['urlDelete'] = $delete_url;
            $docAdded['urlEdit'] = $edit_url;

            $arrayTabs[$tabId]["documents"][] = $docAdded;
        }

        $out['tabs'] = $arrayTabs;

        /////// Documents générés
        $generatedDocuments = $this->getOscarConfigurationService()->getConfiguration(
            'generated-documents.activity'
        );
        $generatedDocumentsJson = [];
        foreach ($generatedDocuments as $key => $infos) {
            $generatedDocumentsJson[] = [
                'url'   => $urlPlugin->fromRoute(
                    'contract/generatedocument',
                    ['id' => $activity->getId(), 'doc' => $key]
                ),
                'label' => $infos['label']
            ];
        }
        $out['generatedDocuments'] = $generatedDocumentsJson;

        /////// TYPES de DOCUMENT
        $typesDocuments = [];


        /////// CIRCUITS de SIGNATURE
        $signatureFlowParams = [];
        $typesDocumentsDatas = $this->getTypeDocumentRepository()->getTypes();

        // Signatures disponibles (avec les personnes associées dans le contexte de l'activité)
        $processDatas = [];

        /** @var SignatureService $signatureService */
        $signatureService = $this->getServiceContainer()->get(SignatureService::class);

        foreach ($signatureService->getSignatureFlows(SignatureConstants::FORMAT_DEFAULT, true) as $flow) {
            $flowId = $flow['id'];
            $signatureFlowDatas = $signatureService->createSignatureFlowDatasById(
                "",
                $flowId,
                ['activity_id' => $activity->getId()]
            );
            $processDatas[] = $signatureFlowDatas['signatureflow'];
        }

        // Types de document
        foreach ($typesDocumentsDatas as $typeDocument) {
            $typeDatas = $typeDocument->toArray();
            $typeDatas['flow'] = false;
            $typesDocuments[] = $typeDatas;
        }

        $out['processDatas'] = $processDatas;
        $out['typesDocuments'] = $typesDocuments;
        $out['computedDocuments'] = $generatedDocumentsJson;

        return $out;
    }

    /**
     * Jalons de l'activité.
     *
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getMilestonesActivity(Activity $activity, ?Url $urlPlugin = null): array
    {
        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $activity->getId())->getQuery()->getResult();

        $out = [
            'url'              => $urlPlugin->fromRoute('activitydate', ['idactivity' => $activity->getId()]),
            'urlNotifications' => $urlPlugin->fromRoute('contract/notifications', ['id' => $activity->getId()]),
            'entities'         => [],
        ];

        // Types
        $types = $this->getEntityManager()->getRepository(DateType::class)->findAll();

        $typesArr = [];
        /** @var DateType $type */
        foreach ($types as $type) {
            $typesArr[$type->getId()] = [
                'id'          => $type->getId(),
                'label'       => $type->getLabel(),
                'description' => $type->getDescription(),
                'facet'       => $type->getFacet(),
                'finishable'  => $type->isFinishable()
            ];
        }

        $out['types'] = array_values($typesArr);

        $now = date('Y-m-d');

        /** @var ActivityDate $data */
        foreach ($dates as $data) {
            $date = $data->getDateStartStr();
            $data = [
                'id'        => $data->getId(),
                'past'      => $date < $now,
                'dateStart' => $this->formatDateTime($data->getDateStart()),
                'comment'   => $data->getComment(),
                'type'      => $typesArr[$data->getType()->getId()],
                'type_id'   => $data->getType()->getId(),
                'finished'  => $data->getFinished(),
                //'hasProgression' => $data->getProgressInfo()
            ];
            $out['entities'][] = $data;
        }

        return $out;
    }

    public function getNotesActivity(
        Activity $activity,
        ?Url $urlPlugin = null
    ): array {
        /** @var ActivityNoteRepository $notesActivityRepository */
        $notesActivityRepository = $this->getEntityManager()->getRepository(ActivityNote::class);

        $notes = [];
        foreach ($notesActivityRepository->getNotesActivity($activity->getId()) as $note) {
            $createdBy_id = -1;
            $createdBy_username = "Anonymous";
            $createdBy_firstname = "";
            $createdBy_lastname = "";
            if ($note->getCreatedBy()) {
                $createdBy_id = $note->getCreatedBy()->getId();
                $createdBy_firstname = $note->getCreatedBy()->getFirstname();
                $createdBy_lastname = $note->getCreatedBy()->getLastname();
                $createdBy_username = $note->getCreatedBy()->getFullname();
            }
            $notes[] = [
                'id'          => $note->getId(),
                'content'     => $note->getContent(),
                'dateCreated' => $this->formatDateTime($note->getDateCreated()),
                'dateUpdated' => $this->formatDateTime($note->getDateUpdated()),
                'dateRef'     => $note->getDateUpdated() ?
                    $this->formatDateTime($note->getDateUpdated()) :
                    $this->formatDateTime($note->getDateCreated()),
                'createdBy'   => [
                    'id'        => $createdBy_id,
                    'firstname' => $createdBy_firstname,
                    'lastname'  => $createdBy_lastname,
                    'username'  => $createdBy_username,
                ]
            ];
        }

        return [
            'url'      => $urlPlugin->fromRoute('activity-notes/api') . '?activityid=' . $activity->getId(),
            'entities' => $notes
        ];
    }


    public function getOrganizationsActivity(Activity $activity, ?Url $urlPlugin = null): array
    {
        $entities = [];

        $roles = [];
        /** @var OrganizationRole $role */
        foreach (
            $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy([], ['label' => 'ASC']) as $role
        ) {
            $roles[] = [
                'id'    => $role->getId(),
                'label' => $role->getLabel()
            ];
        }

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
            // rôle supprimé ? manipulation exterieur
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
            }

            $class = get_class($activityOrganization);

            if ($class == ActivityOrganization::class) {
                $context = "activity";
                $contextKey = $activityOrganization->getActivity()->getOscarNum();
            }
            else {
                $context = "project";
                $contextKey = $activityOrganization->getProject()->getAcronym();
            }

            $urlDelete = $urlPlugin->fromRoute(
                $classRoutes[$class] . '/delete',
                ['idenroll' => $activityOrganization->getId()]
            );
            $urlEdit = $urlPlugin->fromRoute(
                $classRoutes[$class] . '/edit',
                ['idenroll' => $activityOrganization->getId()]
            );
            $urlShow = $urlPlugin->fromRoute(
                'organization/show',
                ['id' => $activityOrganization->getOrganization()->getId()]
            );

            $entities[] = [
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
                'enrolled'      => $activityOrganization->getOrganization()->getId(),
                'enrolledLabel' => $activityOrganization->getOrganization()->getFullName(),
                'past'          => !$activityOrganization->isActive(),
                'start'         => $this->formatDateTime($activityOrganization->getDateStart()),
                'end'           => $this->formatDateTime($activityOrganization->getDateEnd())
            ];
        }
        return [
            'roles'    => $roles,
            'entities' => $entities,
            'urlNew'   => $urlPlugin->fromRoute('organizationactivity/new', ['idenroller' => $activity->getId()]),
            'url'      => $urlPlugin->fromRoute('contract/organizations', ['id' => $activity->getId()]),
        ];
    }

    public function getPersonsActivity(Activity $activity, ?Url $urlPlugin = null): array
    {
        $output = [];
        $roles = [];

        foreach (
            $this->getEntityManager()->getRepository(Role::class)->getRolesAvailableForPersonInActivity() as $role
        ) {
            $roles[] = [
                'id'    => $role->getId(),
                'label' => $role->getRoleId()
            ];
        }

        /**
         * @var ActivityPerson|ProjectMember $activityPerson
         */
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            if (get_class($activityPerson) == ActivityPerson::class) {
                $urlDelete = $urlPlugin->fromRoute(
                    'personactivity/delete',
                    ['idenroll' => $activityPerson->getId()]
                );
                $urlEdit = $urlPlugin->fromRoute(
                    'personactivity/edit',
                    ['idenroll' => $activityPerson->getId()]
                );
                $context = "activity";
                $contextKey = $activityPerson->getActivity()->getOscarNum();
                $idEnroller = $activityPerson->getActivity()->getId();
            }
            else {
                $urlDelete = $urlPlugin->fromRoute(
                    'personproject/delete',
                    ['idenroll' => $activityPerson->getId()]
                );
                $urlEdit = $urlPlugin->fromRoute(
                    'personproject/edit',
                    ['idenroll' => $activityPerson->getId()]
                );
                $context = "project";
                $contextKey = $activityPerson->getProject()->getAcronym();
                $idEnroller = $activityPerson->getProject()->getId();
            }
            $urlShow = $urlPlugin->fromRoute(
                'person/show',
                ['id' => $activityPerson->getPerson()->getId()]
            );
            $output[] = [
                'id'            => $activityPerson->getId(),
                'role'          => $activityPerson->getRole(),
                'roleLabel'     => $activityPerson->getRole(),
                'roleId'        => $activityPerson->getRoleObj() ? $activityPerson->getRoleObj()->getId() : "",
                'rolePrincipal' => $activityPerson->isPrincipal(),
                'context'       => $context,
                'contextKey'    => $contextKey,
                'urlShow'       => $urlShow,
                'urlEdit'       => $urlEdit,
                'urlDelete'     => $urlDelete,
                'past'          => $activityPerson->isPast(),
                'enroller'      => $idEnroller,
                'enrollerLabel' => $activity->getLabel(),
                'enrolled'      => $activityPerson->getPerson()->getId(),
                'enrolledLabel' => $activityPerson->getPerson()->getDisplayName(),
                'firstName'     => $activityPerson->getPerson()->getFirstname(),
                'firstname'     => $activityPerson->getPerson()->getFirstname(),
                'lastName'      => $activityPerson->getPerson()->getLastname(),
                'lastname'      => $activityPerson->getPerson()->getLastname(),
                'start'         => $this->formatDateTime($activityPerson->getDateStart()),
                'end'           => $this->formatDateTime($activityPerson->getDateEnd()),
            ];
        }

        return [
            'roles'    => $roles,
            'entities' => $output,
            'urlNew'   => $urlPlugin->fromRoute('personactivity/new', ['idenroller' => $activity->getId()]),
            'url'      => $urlPlugin->fromRoute('contract/persons', ['id' => $activity->getId()]),
        ];
    }

    public function getWorkpackagesActivity(Activity $activity, ?Url $urlPlugin = null): array
    {
        $out = [
            'entities' => [],
            'url'      => $urlPlugin->fromRoute("workpackage/rest", ['idactivity' => $activity->getId()]),
        ];

        /** @var WorkPackage $workPackage */
        foreach ($activity->getWorkPackages() as $workPackage) {
            $wp = $workPackage->toArray();
            $out['entities'][] = $wp;
        }
        return $out;
    }

    /**
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     * @throws OscarException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTimesheetsActivity(
        Activity $activity,
        ?Url $urlPlugin = null
    ): array {
        $out = [
            'enabled'      => false,
            'informations' => ""
        ];

        if (!$activity->getProject()) {
            $out['informations'] = "Cette activité doit avoir un projet";
        }
        elseif (!$activity->getProject()->getAcronym()) {
            $out['informations'] = "Le projet de cette activité doit avoir un acronyme";
        }
        else {
            $out['enabled'] = true;
            $out['url'] = $urlPlugin->fromRoute('contract/timesheet', ['id' => $activity->getId()]);
            $out['urlSynthesis'] = $urlPlugin->fromRoute('timesheet/synthesis') . '?activity_id=' . $activity->getId();

            /** @var TimesheetService $timesheetService */
            $timesheetService = $this->getServiceContainer()->get(TimesheetService::class);

            ////////////////////////////////////////////////////////////////////////////////// Validateurs
            $out['validators'] = [
                'prj' => [],
                'sci' => [],
                'adm' => [],
            ];

//            $out['validatorsDefault'] = [
//                'prj' => [],
//                'sci' => [],
//                'adm' => [],
//            ];

            $personFormatter = new PersonFormatterArray($urlPlugin);

//            /** @var Person $validator */
//            foreach ($timesheetService->getValidatorsPrj($activity, true) as $validator) {
//                $out['validatorsDefault']['prj'][] = $personFormatter->format($validator);
//            }
//
//            foreach ($timesheetService->getValidatorsSci($activity, true) as $validator) {
//                $out['validatorsDefault']['sci'][] = $personFormatter->format($validator);
//            }
//
//            foreach ($timesheetService->getValidatorsAdm($activity, true) as $validator) {
//                $out['validatorsDefault']['adm'][] = $personFormatter->format($validator);
//            }

            /** @var Person $validator */
            foreach ($activity->getValidatorsPrj() as $validator) {
                $out['validators']['prj'][] = $personFormatter->format($validator);
            }

            foreach ($activity->getValidatorsSci() as $validator) {
                $out['validators']['sci'][] = $personFormatter->format($validator);
            }

            foreach ($activity->getValidatorsAdm() as $validator) {
                $out['validators']['adm'][] = $personFormatter->format($validator);
            }

            ////////////////////////////////////////////////////////////////////////////////// Déclarants
            $declarers = [];
            foreach ($activity->getPersonsDeep() as $personActivity) {
                if ($activity->hasDeclarant($personActivity->getPerson())) {
                    $hasDeclaration = $personActivity->getPerson()->hasDeclarationIn($activity);
                    $declarers[$personActivity->getPerson()->getId()] = [
                        'id'             => $personActivity->getPerson()->getId(),
                        'fullname'       => $personActivity->getPerson()->getFullname(),
                        'firstname'      => $personActivity->getPerson()->getFirstname(),
                        'lastname'       => $personActivity->getPerson()->getLastname(),
                        'hasDeclaration' => $hasDeclaration,
                        'url_details'    => $urlPlugin->fromRoute('timesheet/resume')
                            . '?person_id='
                            . $personActivity->getPerson()->getId(),
                    ];
                }
            }
            $out['declarers'] = array_values($declarers);
        }

        return $out;
    }

    /**
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     */
    private function getPaymentsActivity(Activity $activity, ?Url $urlPlugin): array
    {
        $entities = [];

        /** @var ActivityPayment $payment */
        foreach ($activity->getPayments() as $payment) {
            $entities[] = [
                'id'              => $payment->getId(),
                'activity_id'     => $activity->getId(),
                'datePayment'     => $this->formatDateTime($payment->getDatePayment()),
                'datePredicted'   => $this->formatDateTime($payment->getDatePredicted()),
                'amount'          => $payment->getAmount(),
                'rate'            => $payment->getRate(),
                'currency'        => $payment->getCurrency() ? $payment->getCurrency()->asArray() : null,
                'codeTransaction' => $payment->getCodeTransaction(),
                'comment'         => $payment->getComment(),
                'status'          => $payment->getStatus(),
                'statusLabel'     => $payment->getStatusLabel(),
                'late'            => $payment->isLate()
            ];
        }

        return [
            'url'      => $urlPlugin->fromRoute('activitypayment_rest', ['idactivity' => $activity->getId()]),
            'entities' => $entities
        ];
    }

    /**
     * @param Activity $activity
     * @param Url|null $urlPlugin
     * @return array
     */
    private function getSpentsActivity(Activity $activity, ?Url $urlPlugin): array
    {
        $pfis = [$activity->getCodeEOTP()];
        $out = [
            'pfi'     => $pfis,
            'warning' => "",
            'error'   => "",
        ];
        try {
            if (count($pfis) == 0) {
                $out['warning'] = "Aucun numéro financier pour cette activité";
                return $out;
            }
            $out = $this->getSpentService()->getSynthesisDatasPFI(
                $pfis,
                true,
                'basic'
            );
            $out['dateUpdated'] = $activity->getDateTotalSpent();
        } catch (\Exception $e) {
            $msg = "Impossible de charger la synthèse financière pour '$activity'";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            $out['error'] = $msg;
        }
        return $out;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param \DateTime|null $datetime
     * @param string $format
     * @return string|null
     */
    private function formatDateTime(
        ?\DateTime $datetime,
        string $format = 'Y-m-d H:i:s'
    ): ?string {
        if ($datetime === null) {
            return null;
        }
        else {
            return $datetime->format($format);
        }
    }

    /**
     * @return ActivityRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getActivityRepository(): ActivityRepository
    {
        return $this->getEntityManager()->getRepository(Activity::class);
    }

    /**
     * @return ContractDocumentRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getContractDocumentRepository(): ContractDocumentRepository
    {
        return $this->getEntityManager()->getRepository(ContractDocument::class);
    }

    /**
     * @return TypeDocumentRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getTypeDocumentRepository(): TypeDocumentRepository
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class);
    }

    /**
     * @return JsonFormatterService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getJsonFormatterService(Url $urlHelper): JsonFormatterService
    {
        $formatter = $this->getServiceContainer()->get(JsonFormatterService::class);
        $formatter->setUrlHelper($urlHelper);
        return $formatter;
    }
}