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
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
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

    public function getActivityJsonCredentials(int $id, OscarUserContext $oscarUserContext): array
    {
        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

        return [
            'read'           => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_SHOW, $activity),
            'edit'           => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_EDIT, $activity),
            'core'           => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity),
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity),
            ],
            'project'        => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::PROJECT_SHOW, $activity->getProject()),
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_CHANGE_PROJECT, $activity),
            ],
            'documents'      => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_SHOW, $activity->getProject()),
            ],
            'persons'        => [
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity),
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity),
                'show' => $oscarUserContext->hasPrivileges(Privileges::PERSON_SHOW),
            ],
            'organizations'  => [
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE, $activity),
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_SHOW, $activity),
                'show' => $oscarUserContext->hasPrivileges(Privileges::ORGANIZATION_SHOW),
            ],
            'milestones'     => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_SHOW, $activity),
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity),
            ],
            'budget'         => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity),
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity),
            ],
            'spents'         => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity),
                'edit' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity),
            ],
            'timesheets'     => [
                'read'     => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity),
                'validate' => $oscarUserContext->hasPrivileges(
                    Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY,
                    $activity
                ),
            ],
            'notes'          => [
                'read'   => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_NOTES_SHOW, $activity),
                'edit'   => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_USER, $activity),
                'manage' => $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_ADMIN, $activity),
            ],
            'administration' => [
                'read' => $oscarUserContext->hasPrivileges(Privileges::MAINTENANCE_MENU_ADMIN),
            ]
        ];
    }

    public function getActivityJson(int $id, ?Url $urlPlugin = null, ?OscarUserContext $oscarUserContext = null): array
    {
        $credentials = $this->getActivityJsonCredentials($id, $oscarUserContext);
        if ($credentials['read'] !== true) {
            return [];
        }
        else {
            $datas = $this->getActivityJsonDatas($id, $urlPlugin);
            $datas['credentials'] = $credentials;
            foreach ($datas['datas'] as $key => $content) {
                if (!array_key_exists($key, $credentials)) {
                    unset($datas['datas'][$key]);
                }
                else {
                    if ($credentials[$key]['read'] !== true) {
                        unset($datas['datas'][$key]);
                    }
                }
            }
            return $datas;
        }
    }


    public function getActivityJsonDatas(
        int $id,
        ?Url $urlPlugin = null,
    ): array {
        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getServiceContainer()->get(OscarUserContext::class);
        $currentUserId = -1;
        if ($oscarUserContext->getCurrentPerson()) {
            $currentUserId = $oscarUserContext->getCurrentPerson()->getId();
        }

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

        // TODO (Récupération du cache)
        $datas = [
            'id'          => $activity->getId(),
            'dateCache'   => $this->formatDateTime($activity->getDateCache()),
            'credentials' => [],
            'datas'       => [
                'core'          => [
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
                    ],
                    'userid'       => $currentUserId,
                ],
                'budget'        => [
                    'amount'                         => $activity->getAmount(),
                    'montant'                        => $activity->getAmount(),
                    'currency'                       => $activity->getCurrency()->toJson(),
                    'fraisDeGestion'                 => $activity->getFraisDeGestionDisplay(),
                    'fraisDeGestionPartHebergeur'    => $activity->getFraisDeGestionPartHebergeurDisplay(),
                    'fraisDeGestionPartUnite'        => $activity->getFraisDeGestionPartUniteDisplay(),
                    'fraisDeGestionPartGestionnaire' => $activity->getFraisDeGestionPartGestionnaireDisplay(),
                    'tva'                            => (string)$activity->getTva(),
                    'assietteSubventionnable'        => $activity->getAssietteSubventionnable(),
                ],
                'persons'       => $this->getPersonsActivity($activity->getId(), $urlPlugin),
                'organizations' => $this->getOrganizationsActivity($activity->getId(), $urlPlugin),
                'milestones'    => $this->getMilestonesActivity($activity->getId(), $urlPlugin),
                'documents'     => $this->getDocumentsActivity($activity->getId(), $urlPlugin),
                'notes'         => $this->getNotesActivity($activity->getId(), $urlPlugin, $oscarUserContext),
            ]
        ];

        return $datas;
    }

    protected function getContractDocumentService(): ContractDocumentService
    {
        return $this->getServiceContainer()->get(ContractDocumentService::class);
    }

    public function getDocumentsActivity(int $id, ?Url $urlPlugin = null): array
    {
        $out = [
            'url'                => $urlPlugin->fromRoute('contractdocument/activity', ['activity_id' => $id]),
            'url_upload_new_doc' => $urlPlugin->fromRoute('contractdocument/upload', ['idactivity' => $id]),
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

    public function getMilestonesActivity(int $id, ?Url $urlPlugin = null): array
    {
        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $activity->getId())->getQuery()->getResult();

        $out = [];
        $now = date('Y-m-d');
        /** @var ActivityDate $data */
        foreach ($dates as $data) {
            $date = $data->getDateStartStr();
            $data = [
                'id'       => $data->getId(),
                'past'     => $date < $now,
                'comment'  => $data->getComment(),
                'type'     => $data->getType()->getLabel(),
                'type_id'  => $data->getType()->getId(),
                'finished' => $data->getFinishState(),
//                'hasProgression' => $data->getProgressInfo()
            ];
//            $data['deletable'] = $deletable;
//            $data['editable'] = $editable;
//            $data['validable'] = $progression;

            $out[] = $data;
        }

        return $out;
    }

    public function getNotesActivity(int $id, ?Url $urlPlugin = null, ?OscarUserContext $oscarUserContext = null): array
    {
        /** @var ActivityNoteRepository $notesActivityRepository */
        $notesActivityRepository = $this->getEntityManager()->getRepository(ActivityNote::class);

        $notes = [];
        foreach ($notesActivityRepository->getNotesActivity($id) as $note) {
            $createdBy_id = -1;
            $createdBy_username = "Anonymous";
            if ($note->getCreatedBy()) {
                $createdBy_id = $note->getCreatedBy()->getId();
                $createdBy_username = $note->getCreatedBy()->getFullname();
            }
            $notes[] = [
                'id'             => $note->getId(),
                'content'        => $note->getContent(),
                'dateCreated'    => $this->formatDateTime($note->getDateCreated()),
                'dateUpdated'    => $this->formatDateTime($note->getDateUpdated()),
                'dateRef'    => $note->getDateUpdated() ?
                    $this->formatDateTime($note->getDateUpdated()) :
                    $this->formatDateTime($note->getDateCreated()),
                'mine'           => $oscarUserContext ? $oscarUserContext->getCurrentPersonId() == $createdBy_id : false,
                'createdBy'      => [
                    'id'       => $createdBy_id,
                    'username' => $createdBy_username,
                ]
            ];
        }

        $out = [
            'url'      => $urlPlugin->fromRoute('activity-notes/api') . '?activityid=' . $id,
            'entities' => $notes
        ];
        return $out;
    }


    public function getOrganizationsActivity(int $id, ?Url $urlPlugin = null): array
    {
        $entities = [];
        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

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

    /**
     * @param int $id
     * @param Url|null $urlPlugin
     * @return array|array[]
     */
    public function getPersonsActivity(int $id, ?Url $urlPlugin = null): array
    {
        $output = [];

        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

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
         * @var ActivityPerson $activityPerson
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