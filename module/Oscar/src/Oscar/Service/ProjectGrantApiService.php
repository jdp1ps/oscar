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
use Oscar\Entity\ActivityType;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\DateType;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
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
                }
            }
            return $out;
        }
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
                        'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity),
                        'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity),
                    ];
                    break;

                case 'documents':
                    $credentials['documents'] = [
                        'read' => $oscarUserContext->hasPrivileges(
                            Privileges::ACTIVITY_DOCUMENT_SHOW,
                            $activity->getProject()
                        ),
                    ];
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
                    $datas[self::PERIMETER_SPENTS] = $this->getSpentssActivity($activity, $urlPlugin);
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
                'edit' => $urlPlugin->fromRoute('contract/edit', ['id' => $activity->getId()]),
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
            'typesDocuments'     => []
        ];
        return $out;
        // TODO
//        try {
//
//            /** @var Activity $entity */
//            $activity = $this->getActivityService()->getActivityById($id, true);
//
//
//            $out = [];
//
//            $contractDocumentService = $this->get
//            // ID des tabs (onglets pour ranger les documents)
//            $arrayTabs = [];
//            $entitiesTabs = $this->getContractDocumentService()->getContractTabDocuments();
//
//            $rolesMerged = $this->getOscarUserContextService()->getRolesPersonInActivityDeep(
//                $this->getCurrentPerson(),
//                $activity
//            );
//
//            if (!$this->getOscarUserContextService()->getAccessActivityDocument($activity)['read']) {
//                $this->getLoggerService()->error("Accès non authorisé");
//                return $this->getResponseUnauthorized();
//            }
//
//            foreach ($entitiesTabs as $tabDocument) {
//                // Traitement final attendu sur les rôles
//                $access = $this->getOscarUserContextService()->getAccessTabDocument($tabDocument, $rolesMerged);
//                if ($access['read']) {
//                    $tabId = $tabDocument->getId();
//                    $arrayTabs[$tabId] = $tabDocument->toJson();
//                    $arrayTabs[$tabId]["documents"] = [];
//                    $arrayTabs[$tabId]['manage'] = $access['write'] == true;
//                }
//            }
//
//            //Onglet non classé
//            $unclassifiedTab = [
//                "id"        => "unclassified",
//                "label"     => "Non-classés",
//                "manage"    => false,
//                "documents" => []
//            ];
//
//            $allowPrivate = true;
//
//            //Onglet privé
//            $privateTab = [
//                "id"        => "private",
//                "label"     => "Documents privés",
//                "documents" => [],
//                "manage"    => $allowPrivate
//            ];
//
//            $currentPerson = $this->getCurrentPerson();
//            /** @var JsonFormatterService $jsonFormatterService */
//            $jsonFormatterService = $this->getServiceLocator()->get(JsonFormatterService::class);
//            $jsonFormatterService->setUrlHelper($this->url());
//
//            //$documents = $this->getContractDocumentService()->getDocumentsActivity($activity->getId());
//            //Docs reliés à une activité
//            /** @var ContractDocument $doc */
//            foreach ($activity->getDocuments() as $doc) {
//                if (!$this->getOscarUserContextService()->contractDocumentRead($doc)) {
//                    continue;
//                }
//
//                $docAdded = $jsonFormatterService->contractDocument($doc, true);
//
//                if (is_null($doc->getTabDocument())) {
//                    if ($doc->isPrivate() === true) {
//                        // Droits sur les documents privés utilisateur courant associé ou non au document
//                        $personsDoc = $doc->getPersons();
//                        $isPresent = false;
//                        foreach ($personsDoc as $person) {
//                            if ($person === $currentPerson) {
//                                $isPresent = true;
//                            }
//                        }
//
//                        if (true === $isPresent) {
//                            $docAdded['urlDelete'] = $this->url()->fromRoute(
//                                'contractdocument/delete',
//                                ['id' => $doc->getId()]
//                            );
//                            $docAdded['urlDownload'] = $this->url()->fromRoute(
//                                'contractdocument/download',
//                                ['id' => $doc->getId()]
//                            );
//                            $docAdded['urlReupload'] = $this->url()->fromRoute(
//                                'contractdocument/upload',
//                                [
//                                    'idactivity' => $activity->getId(),
//                                    'idtab'      => 'private',
//                                    'id'         => $doc->getId()
//                                ]
//                            );
//                            $docAdded['urlPerson'] = false;
//                        }
//                        $privateTab ["documents"] [] = $docAdded;
//                    }
//                    else {
//                        $unclassifiedTab ["documents"] [] = $docAdded;
//                    }
//                }
//                else {
//                    if (!array_key_exists($doc->getTabDocument()->getId(), $arrayTabs)) {
//                        continue;
//                    }
//                    $arrayTabs[$doc->getTabDocument()->getId()]["documents"] [] = $docAdded;
//                }
//            }
//
//            if ($privateTab && $privateTab['documents']) {
//                $arrayTabs['private'] = $privateTab;
//            }
//
//            $generatedDocuments = $this->getOscarConfigurationService()->getConfiguration(
//                'generated-documents.activity'
//            );
//            $generatedDocumentsJson = [];
//            foreach ($generatedDocuments as $key => $infos) {
//                $generatedDocumentsJson[] = [
//                    'url'   => $this->url()->fromRoute(
//                        'contract/generatedocument',
//                        ['id' => $activity->getId(), 'doc' => $key]
//                    ),
//                    'label' => $infos['label']
//                ];
//            }
//
//            $typesDocuments = [];
//            $signatureFlowParams = [];
//            $typesDocumentsDatas = $this->getActivityService()->getTypesDocuments(false);
//
//            // Signatures disponibles (avec les personnes associées dans le contexte de l'activité)
//            $processDatas = [];
//            $signatureService = $this->getContractDocumentService()->getSignatureService();
//
//            foreach ($signatureService->getSignatureFlows(SignatureConstants::FORMAT_DEFAULT, true) as $flow) {
//                $flowId = $flow['id'];
//                $signatureFlowDatas = $signatureService->createSignatureFlowDatasById(
//                    "",
//                    $flowId,
//                    ['activity_id' => $activity->getId()]
//                );
//                $processDatas[] = $signatureFlowDatas['signatureflow'];
//            }
//
//            // Types de document
//            foreach ($typesDocumentsDatas as $typeDocument) {
//                $typeDatas = $typeDocument->toArray();
//                $typeDatas['flow'] = false;
//                $typesDocuments[] = $typeDatas;
//            }
//
//            $out['process_datas'] = $processDatas;
//            $out['tabsWithDocuments'] = $arrayTabs;
//            $out['typesDocuments'] = $typesDocuments;
//            $out['idCurrentPerson'] = $this->getCurrentPerson() ? $this->getCurrentPerson()->getId() : null;
//            $out['computedDocuments'] = $generatedDocumentsJson;
//
//            return new JsonModel($out);
//        } catch (Exception $e) {
//            return $this->jsonError("Impossible de charger les documents : " . $e->getMessage());
//        }
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
            if ($note->getCreatedBy()) {
                $createdBy_id = $note->getCreatedBy()->getId();
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
                    'id'       => $createdBy_id,
                    'username' => $createdBy_username,
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
                'displayname'   => $activityPerson->getPerson()->getDisplayName(),
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

    public function getTimesheetsActivity(
        Activity $activity,
        ?Url $urlPlugin = null
    ): array {
        $out = [
            'declarers'    => null,
            'validators'   => [
                'prj' => [],
                'sci' => [],
                'adm' => [],
            ],
            'url'          => $urlPlugin->fromRoute('contract/timesheet', ['id' => $activity->getId()]),
            'urlSynthesis' => $urlPlugin->fromRoute(
                    'timesheet/synthesis'
                ) . '?activity_id=' . $activity->getId(),
        ];


        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceContainer()->get(TimesheetService::class);

        ////////////////////////////////////////////////////////////////////////////////// Validateurs
        /** @var Person $validator */
        foreach ($timesheetService->getValidatorsPrj($activity) as $validator) {
            $out['validators']['prj'][] = [
                'id'       => $validator->getId(),
                'fullname' => $validator->getFullname(),
            ];
        }

        foreach ($timesheetService->getValidatorsSci($activity) as $validator) {
            $out['validators']['sci'][] = [
                'id'       => $validator->getId(),
                'fullname' => $validator->getFullname(),
            ];
        }

        foreach ($timesheetService->getValidatorsAdm($activity) as $validator) {
            $out['validators']['adm'][] = [
                'id'       => $validator->getId(),
                'fullname' => $validator->getFullname(),
            ];
        }

        ////////////////////////////////////////////////////////////////////////////////// Déclarants
        $declarers = [];
        foreach ($activity->getPersonsDeep() as $personActivity) {
            if ($activity->hasDeclarant($personActivity->getPerson())) {
                $hasDeclaration = $personActivity->getPerson()->hasDeclarationIn($activity);
                $declarers[$personActivity->getPerson()->getId()] = [
                    'id'             => $personActivity->getPerson()->getId(),
                    'fullname'       => $personActivity->getPerson()->getFullname(),
                    'hasDeclaration' => $hasDeclaration,
                    'url_details'    => $urlPlugin->fromRoute('timesheet/resume')
                        . '?person_id='
                        . $personActivity->getPerson()->getId(),
                ];
            }
        }
        $out['declarers'] = array_values($declarers);

        return $out;
    }

    private function getPaymentsActivity(Activity $activity, ?Url $urlPlugin): array
    {
        $entities = [];

        /** @var ActivityPayment $payment */
        foreach( $activity->getPayments() as $payment ){
            $entities[] = [
                'id' => $payment->getId(),
                'activity_id' => $activity->getId(),
                'datePayment' => $this->formatDateTime($payment->getDatePayment()),
                'datePredicted' => $this->formatDateTime($payment->getDatePredicted()),
                'amount' => $payment->getAmount(),
                'rate' => $payment->getRate(),
                'currency' => $payment->getCurrency() ? $payment->getCurrency()->asArray() : null,
                'codeTransaction' => $payment->getCodeTransaction(),
                'comment' => $payment->getComment(),
                'status' => $payment->getStatus(),
                'statusLabel' => $payment->getStatusLabel(),
                'late' => $payment->isLate()
            ];
        }

        return [
            'url' => $urlPlugin->fromRoute('activitypayment_rest', ['idactivity' => $activity->getId()]),
            'entities' => $entities
        ];
    }

    private function getSpentssActivity(Activity $activity, ?Url $urlPlugin)
    {

        $pfis = [$activity->getCodeEOTP()];
        $out = [
            'pfi' => $pfis
        ];
        try {
            if (count($pfis) == 0) {
                return $this->getResponseInternalError("Pas de numéro financier");
            }
            $out = $this->getSpentService()->getSynthesisDatasPFI(
                $pfis,
                true,
                'basic'
            );
            $out['dateUpdated'] = $activity->getDateTotalSpent();
        } catch (Exception $e) {
            return $this->getResponseInternalError("Impossible de charger les dépenses pour la/les activité(s)");
        }
        return $out;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param \DateTime|null $datetime
     * @param string $format
     * @return string|null
     */
    private
    function formatDateTime(
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

    public
    function getActivityRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Activity');
    }


}