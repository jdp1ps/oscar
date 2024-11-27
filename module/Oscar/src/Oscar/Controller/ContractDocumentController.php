<?php

namespace Oscar\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ContractDocumentService;
use Oscar\Service\JsonFormatterService;
use Oscar\Service\NotificationService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Traits\UseJsonFormatterService;
use Oscar\Traits\UseJsonFormatterServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\FileSystemUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Psr\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;
use UnicaenSignature\Provider\SignaturePrivileges;
use UnicaenSignature\Utils\SignatureConstants;


/**
 * Class ContractDocumentController
 * @package Oscar\Controller
 */
class ContractDocumentController extends AbstractOscarController implements UseServiceContainer, UseJsonFormatterService
{

    use UseServiceContainerTrait, UseJsonFormatterServiceTrait;

    //////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES ACCESS

    /**
     * @return ProjectGrantService
     * @throws OscarException
     */
    public function getActivityService(): ProjectGrantService
    {
        return $this->getService($this->getServiceContainer(), ProjectGrantService::class);
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator(): ContainerInterface
    {
        return $this->getServiceContainer();
    }

    /**
     * @return NotificationService
     * @throws OscarException
     */
    public function getNotificationService(): NotificationService
    {
        return $this->getService($this->getServiceContainer(), NotificationService::class);
    }

    /**
     * @return ActivityLogService
     * @throws OscarException
     */
    public function getActivityLogService(): ActivityLogService
    {
        return $this->getService($this->getServiceContainer(), ActivityLogService::class);
    }

    /**
     * @return ContractDocumentService
     * @throws OscarException
     */
    protected function getContractDocumentService()
    {
        return $this->getService($this->getServiceContainer(), ContractDocumentService::class);
    }

    /**
     * @return VersionnedDocumentService
     * @throws Exception
     * @annotations Retourne le service pour gérer les documents
     */

    private ?VersionnedDocumentService $versionnedDocumentService = null;

    /**
     * @throws OscarException
     */
    protected function getVersionnedDocumentService(): VersionnedDocumentService
    {
        if (null === $this->versionnedDocumentService) {
            $this->versionnedDocumentService = new VersionnedDocumentService(
                $this->getEntityManager(),
                $this->getDropLocation(),
                ContractDocument::class
            );
        }
        return $this->versionnedDocumentService;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function searchAction()
    {
        $format = $this->getRequest()->getQuery('f', 'ui');
        if ($format == 'json') {
            $search = $this->getRequest()->getQuery('s', '');
            $person_app_roles = $this->getOscarUserContextService()->getCurrentRolesApplication();
            var_dump($person_app_roles);
//            $documents_ids = $this->getContractDocumentService()->getPersonDocumentIds($this->getCurrentPerson());
            die("FORMAT");
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////// USEFULL INFOS

    /**
     * Retourne l'emplacement où sont stoqués les documents depuis le fichier
     * de configuration local.php
     *
     * @param ContractDocument|null $document
     * @throws OscarException
     */
    protected function getDropLocation(ContractDocument $document = null): string
    {
        if (!is_null($document)) {
            return $this->getOscarConfigurationService()->getDocumentRealpath($document);
        }
        else {
            return $this->getOscarConfigurationService()->getDocumentDropLocation();
        }
    }


    ////////////////////////////////////////////////////////////////////////////


    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $format = $this->getRequest()->getQuery('f', 'ui');
        if ($format == 'json') {
            try {
                $out = $this->baseJsonResponse();
                $out['page'] = $page = intval($this->params()->fromQuery('page', 1));
                $out['search'] = $filterActivity = $this->params()->fromQuery('s', null);
                $out['type'] = $filterType = $this->params()->fromQuery('type', null);
                $out['sign'] = $filterSign = $this->params()->fromQuery('sign', null);


                // ID des tabs (onglets pour ranger les documents)
                $arrayTabs = [];
                $entitiesTabs = $this->getContractDocumentService()->getContractTabDocuments();

                foreach ($entitiesTabs as $tabDocument) {
                    $tabId = $tabDocument->getId();
                    $arrayTabs[$tabId] = $tabDocument->toJson();
                    $arrayTabs[$tabId]["documents"] = [];
                    $arrayTabs[$tabId]['manage'] = true;
                }

                $currentPerson = $this->getCurrentPerson();

                /** @var JsonFormatterService $jsonFormatterService */
                $jsonFormatterService = $this->getServiceLocator()->get(JsonFormatterService::class);
                $jsonFormatterService->setUrlHelper($this->url());

                $filters = [
                    'type' => intval($filterType) ?: null,
                    'sign' => $filterSign == "" ? null : $filterSign == "1",
                ];

                if ($filterActivity) {
                    $ids = $this->getActivityService()->search($filterActivity);
                    $filters['activity_ids'] = $ids;
                }

                $datas = $this->getContractDocumentService()->getDocumentsGrouped($page, 50, $filters);
                $documents = $datas['documents'];

                $out['total'] = $datas['total'];
                $out['total_pages'] = $datas['total_pages'];
                $out['total_current_page'] = $datas['total_current_page'];
                $out['documents'] = [];

                /** @var ContractDocument $doc */
                foreach ($documents as $doc) {
                    $docAdded = $jsonFormatterService->contractDocument($doc, true);
                    $out['documents'][] = $docAdded;
                } // End boucle

                $typesDocuments = [];
                $typesDocumentsDatas = $this->getActivityService()->getTypesDocuments(false);

                // Signatures disponibles (avec les personnes associées dans le contexte de l'activité)
                $processDatas = [];
                $signatureService = $this->getContractDocumentService()->getSignatureService();

                // Types de document
                foreach ($typesDocumentsDatas as $typeDocument) {
                    $typeDatas = $typeDocument->toArray();
                    $typeDatas['flow'] = false;
                    $typesDocuments[] = $typeDatas;
                }

                $out['process_datas'] = $processDatas;
                $out['tabsWithDocuments'] = $arrayTabs;
                $out['typesDocuments'] = $typesDocuments;
                $out['idCurrentPerson'] = $this->getCurrentPerson() ? $this->getCurrentPerson()->getId() : null;

                return new JsonModel($out);
            } catch (Exception $e) {
                return $this->jsonError("Impossible de charger les documents : " . $e->getMessage());
            }
        }
        else {
            return [];
        }
    }

    /**
     * Suppression d'un document
     *
     * @return Response|Redirect
     * @throws OscarException
     */
    public function deleteAction(): Response|Redirect
    {
        $document = $this->getContractDocumentService()->getDocument(
            $this->params()->fromRoute('id'),
            true
        );

        $access = $this->getOscarUserContextService()->getAccessDocument($document);

        if ($access['write'] === true) {
            try {
                $this->getContractDocumentService()->deleteDocument($document);
                return $this->getResponseOk("Document supprimé");
            } catch (Exception $exception) {
                throw new OscarException("Impossible de supprimer le document : " . $exception->getMessage());
            }
            return $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
        }
        else {
            return $this->getResponseUnauthorized("Vous ne pouvez pas supprimer ce document");
        }
    }


    /**
     * Modification document (onglets, TYPE, privé/oui/non ...)
     *
     * @return JsonModel
     */
    public function changeTypeAction(): Response|JsonModel
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $document = $this->getContractDocumentService()->getDocument($request->getPost('documentId'));

            if (!($this->getOscarUserContextService()->getAccessDocument($document)['write'] === true)) {
                return $this->getResponseUnauthorized("Vous ne pouvez pas modifier ce document");
            }

            // Gestion de l'onglet
            $tabDest = $document->getTabDocument();
            $tabDestId = intval($request->getPost()->get('tabDocument'));
            if ($tabDestId) {
                $tabDest = $this->getContractDocumentService()->getContractTabDocument($tabDestId);
                if ($tabDestId != $document->getTabDocument()->getId()) {
                    // On regarde si on a le droit d'accès à l'onglet
                    if ($this->getOscarUserContextService()->getAccessTabDocument($tabDest)['write'] === true) {
                        $document->setTabDocument($tabDest);
                    }
                    else {
                        return $this->getResponseUnauthorized("Vous n'avez pas accès à l'onglet de destination");
                    }
                }
            }

            // Type de document
            $type = $this->getContractDocumentService()->getContractDocumentType($request->getPost('type'));
            if (!$type) {
                return $this->getResponseBadRequest("Type de document invalide");
            }

            // Privé
            $privateDocument = (bool)$request->getPost('private', false);

            // Personnes
            $idsPersons = (trim($request->getPost('persons')) !== "") ? explode(",", $request->getPost('persons')) : [];

            // Traitement
            $succesManageDocuments = $this->manageDocsInTab($document, $idsPersons, $tabDest, $type, $privateDocument);
            if (!$succesManageDocuments) {
                return $this->getResponseBadRequest("Impossible de modifier le document");
            }

            return new JsonModel(['response' => 'ok']);
        }
        throw new \HttpException();
    }

    /**
     * @param string $field_name
     * @return ContractDocument
     * @throws OscarException
     */
    protected function getDocumentFromPostedId(string $field_name = 'document_id'): ContractDocument
    {
        $documentId = intval($this->params()->fromPost($field_name, 0));
        if (!$documentId) {
            $msg = "Paramètre d'identifiant de document non trouvé";
            $this->getLoggerService()->warning($msg);
            throw new OscarException($msg);
        }
        return $this->getContractDocumentService()->getDocument($documentId);
    }

    /**
     * @param string $field_name
     * @return ContractDocument
     * @throws OscarException
     */
    protected function getDocumentFromRouteId(string $field_name = 'document_id'): ContractDocument
    {
        $documentId = intval($this->params()->fromRoute($field_name, 0));
        if (!$documentId) {
            $msg = "Paramètre d'identifiant de document non trouvé";
            $this->getLoggerService()->warning($msg);
            throw new OscarException($msg);
        }
        return $this->getContractDocumentService()->getDocument($documentId);
    }

    /**
     * @param string $field_name
     * @return Activity
     * @throws OscarException
     */
    protected function getActivityFromRouteId(string $field_name = 'idactivity'): Activity
    {
        $activityId = intval($this->params()->fromRoute($field_name, 0));
        if (!$activityId) {
            $msg = "Paramètre d'identifiant d'activité non trouvé";
            $this->getLoggerService()->warning($msg);
            throw new OscarException($msg);
        }
        return $this->getContractDocumentService()->getActivity($activityId);
    }

    public function editAction()
    {
        $document = $this->getDocumentFromRouteId();
        return $this->saveDocument('edit', $document, $document->getActivity());
    }

    public function reuploadAction()
    {
        $document = $this->getDocumentFromRouteId();
        return $this->saveDocument('version', $document, $document->getActivity());
    }

    /**
     * Upload de document sur une activité
     * /documents-des-contracts/televerser/:idactivity[/:idtab][/:id]
     *
     * @return JsonModel|Response
     * @annotations Procédure générique pour l'envoi des fichiers.
     * @throws OscarException
     */
    public function uploadAction()
    {
        $activity = $this->getActivityFromRouteId();
        return $this->saveDocument('new', null, $activity);
    }


    protected function saveDocument(string $action, ?ContractDocument $document, Activity $activity)
    {
        $this->getLoggerService()->debug("saveDocument");
        try {
            // Récupération des données envoyées
            $json = $this->params()->fromPost('data');
            $documentDatas = json_decode($json, true);


            if ($action == 'new') {
                $rolesInActivity = $this->getOscarUserContextService()->getRolesPersonInActivityDeep(
                    $this->getCurrentPerson(),
                    $activity
                );
                $tabDocument = $this->getContractDocumentService()->getContractTabDocument(
                    $documentDatas['tabDocument']['id']
                );
                $accessTab = $this->getOscarUserContextService()->getAccessTabDocument($tabDocument, $rolesInActivity);
                if (!$accessTab['write']) {
                    return $this->jsonError("Vous ne pouvez pas uploader dans cet onglet");
                }
            }
            else {
                $tabDocument = $document->getTabDocument();
                $activity = $document->getActivity();
                if (!$this->getOscarUserContextService()->getAccessDocument($document)) {
                    return $this->jsonError("Vous n'avez pas les droits pour gérer ce document");
                }
            }

            $this->getLoggerService()->info("[document:$action] from " . strval($this->getCurrentPerson()));

            $dateDeposit = $documentDatas['dateDeposit'];
            if ($dateDeposit) {
                $dateDeposit = new \DateTime($dateDeposit);
            }
            else {
                $dateDeposit = null;
            }
            $dateSend = $documentDatas['dateSend'];
            if ($dateSend) {
                $dateSend = new \DateTime($dateSend);
            }
            else {
                $dateSend = null;
            }
            $information = $documentDatas['information'];

            $private = $documentDatas['tabDocument']['private'];
            $idType = $documentDatas['category']['id'];
            $url = $documentDatas['location'] == 'url';
            $persons = $documentDatas['persons'];
            $privatePersons = [];


            // Document privé
            if ($private) {
                if (!$this->getOscarUserContextService()->hasPrivileges(
                    Privileges::ACTIVITY_DOCUMENT_MANAGE,
                    $activity
                )) {
                    return $this->getResponseUnauthorized("Vous ne pouvez pas téléverser un document privé");
                }
                foreach ($persons as $personId) {
                    $personId = intval($personId);
                    if ($personId) {
                        $privatePersons[] = $personId;
                    }
                }
            }

            if ($action == 'version') {
                try {
                    $this->getContractDocumentService()->uploadContractDocumentNewVersion(
                        $_FILES['file'],
                        $document,
                        $dateDeposit,
                        $dateSend,
                        $information,
                        $this->getCurrentPerson()
                    );

                    return new JsonModel([
                                             'response' => 'ok'
                                         ]);
                } catch (Exception $e) {
                    $this->getLoggerService()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }
            }

            if ($action == 'edit') {
                $document->setTypeDocument(
                    $this->getContractDocumentService()->getContractDocumentType($documentDatas['category']['id'])
                );

                $document->setTabDocument(
                    $this->getContractDocumentService()->getContractTabDocument($documentDatas['tabDocument']['id'])
                );
                $document->setDateDeposit($dateDeposit);
                $document->setDateSend($dateSend);
                $document->setInformation($information);
                $this->getEntityManager()->flush();

                return new JsonModel(['message' => 'ok']);
            }

            try {
                if( !$idType ){
                    throw new Exception("Aucun type de document selectionné");
                }
                $typeDocument = $this->getContractDocumentService()->getContractDocumentType($idType);
            } catch (Exception $e) {
                return $this->jsonError($e->getMessage());
            }

            try {
                $flowDt = null;

                $this->getContractDocumentService()->uploadContractDocument(
                    $_FILES['file'],
                    $activity,
                    $tabDocument,
                    $typeDocument,
                    $this->getCurrentPerson(),
                    $privatePersons,
                    $dateDeposit,
                    $dateSend,
                    $information,
                    $url,
                    'new'
                );
                return new JsonModel([
                                         'response' => 'ok'
                                     ]);
            } catch (Exception $e) {
                $this->getLoggerService()->error($e->getMessage());
                return $this->jsonError($e->getMessage());
            }
            return $this->jsonError("Non-implémenté");
        } catch (Exception $exception) {
            return $this->jsonError($exception->getMessage());
        }
    }

    public function activityAction(): JsonModel|Response
    {
        try {
            $id = $this->params()->fromRoute('activity_id');

            /** @var Activity $entity */
            $activity = $this->getActivityService()->getActivityById($id, true);

            $out = $this->baseJsonResponse();

            // ID des tabs (onglets pour ranger les documents)
            $arrayTabs = [];
            $entitiesTabs = $this->getContractDocumentService()->getContractTabDocuments();

            $rolesMerged = $this->getOscarUserContextService()->getRolesPersonInActivityDeep(
                $this->getCurrentPerson(),
                $activity
            );

            if (!$this->getOscarUserContextService()->getAccessActivityDocument($activity)['read']) {
                $this->getLoggerService()->error("Accès non authorisé");
                return $this->getResponseUnauthorized();
            }

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
            /** @var JsonFormatterService $jsonFormatterService */
            $jsonFormatterService = $this->getServiceLocator()->get(JsonFormatterService::class);
            $jsonFormatterService->setUrlHelper($this->url());

            //$documents = $this->getContractDocumentService()->getDocumentsActivity($activity->getId());
            //Docs reliés à une activité
            /** @var ContractDocument $doc */
            foreach ($activity->getDocuments() as $doc) {
                if (!$this->getOscarUserContextService()->contractDocumentRead($doc)) {
                    continue;
                }

                $docAdded = $jsonFormatterService->contractDocument($doc, true);

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
                                    'idactivity' => $activity->getId(),
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
                    $arrayTabs[$doc->getTabDocument()->getId()]["documents"] [] = $docAdded;
                }
            }

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
                        ['id' => $activity->getId(), 'doc' => $key]
                    ),
                    'label' => $infos['label']
                ];
            }

            $typesDocuments = [];
            $signatureFlowParams = [];
            $typesDocumentsDatas = $this->getActivityService()->getTypesDocuments(false);

            // Signatures disponibles (avec les personnes associées dans le contexte de l'activité)
            $processDatas = [];
            $signatureService = $this->getContractDocumentService()->getSignatureService();

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

            $out['process_datas'] = $processDatas;
            $out['tabsWithDocuments'] = $arrayTabs;
            $out['typesDocuments'] = $typesDocuments;
            $out['idCurrentPerson'] = $this->getCurrentPerson() ? $this->getCurrentPerson()->getId() : null;
            $out['computedDocuments'] = $generatedDocumentsJson;

            return new JsonModel($out);
        } catch (Exception $e) {
            return $this->jsonError("Impossible de charger les documents : " . $e->getMessage());
        }
    }

    /**
     * Liste des documents suivis (présent en tant qu'observateur d'un processus de signature)
     *
     * @return void
     */
    public function documentsObservedAction()
    {
        $this->getLoggerService()->debug("documentsObservedAction()");
        $this->getJsonFormatterService()->setUrlHelper($this->url());
        $person = $this->getCurrentPerson();
        if ($this->isAjax() || $this->params()->fromQuery('f', null) == 'json') {
            $this->getLoggerService()->debug("Chargement des documents observés");
            try {
                $documents = $this->getContractDocumentService()->getDocumentsWithSignProcessForUser($person);
                $output = $this->baseJsonResponse();
                $output['documents'] = $this->getJsonFormatterService()->contractDocuments($documents);
                return $this->jsonOutput($output);
            } catch (Exception $e) {
                $msg = "Impossible d'afficher le suivi des signatures";
                $this->getLoggerService()->error($msg . " : " . $e->getMessage());
                return $this->jsonError($msg);
            }
        }
        return [];
    }

    /**
     * Annulation d'un processus de signature.
     *
     * @return Response|JsonModel
     */
    public function processDeleteAction()
    {
        try {
            $document = $this->getDocumentFromRouteId();
            $activity = $document->getActivity();
            $this->getOscarUserContextService()->check(SignaturePrivileges::SIGNATURE_DELETE, $activity);
            $this->getContractDocumentService()->deleteProcess($document);
            return $this->jsonOutput(['response' => 'ok']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Déclenchement d'un processus de signature.
     *
     * @return Response|JsonModel
     */
    public function processCreateAction()
    {
        try {
            $document = $this->getDocumentFromRouteId();
            $activity = $document->getActivity();
            $this->getOscarUserContextService()->check(SignaturePrivileges::SIGNATURE_CREATE, $activity);
            $flowDatas = json_decode($this->params()->fromPost('flow_datas'), true);
            if (!$flowDatas) {
                throw new OscarException("Impossible de traiter les données de signature");
            }
            $this->getContractDocumentService()->applySignature($document->getId(), $flowDatas['id'], $flowDatas);

            return new JsonModel(['response' => 'ok']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    public function processUpdateAction()
    {
    }

    public function signDocumentAction()
    {
        try {
            // DOCUMENT et FLOWDT
            try {
                $document_id = $this->params()->fromPost('document_id', null);
                if ($document_id == null || $document_id <= 0) {
                    throw new OscarException("ID de document non transmis");
                }
                $document = $this->getContractDocumentService()->getDocument($document_id);
            } catch (Exception $e) {
                throw new OscarException("Impossible de trouver le document");
            }
            $activity = $this->getActivityService()->getActivityById($document->getActivity()->getId());

            // FLOW

            $flowDtPosted = $this->params()->fromPost('flow_datas', null);
            if ($flowDtPosted == null) {
                throw new OscarException("Aucune donnée de signature envoyée");
            }
            $flowDt = json_decode($flowDtPosted, true);

            if (!$flowDt) {
                throw new OscarException("Les données de signature envoyées sont invalides");
            }

            $this->getContractDocumentService()->applySignature($document->getId(), $flowDt['id'], $flowDt);

            return new JsonModel(['response' => 'ok']);
        } catch (Exception $e) {
            $this->getLoggerService()->critical("Erreur signature : " . $e->getMessage());
            return $this->jsonError(
                "Un problème est survenu lors de la soumission pour signature : " . $e->getMessage()
            );
        }
    }

    /**
     * Téléchargement d'un document.
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function downloadAction()
    {
        $idDoc = $this->params()->fromRoute('id');
        /** @var ContractDocument $doc */
        $doc = $this->getContractDocumentService()->getDocument($idDoc);

        if (!$this->getOscarUserContextService()->contractDocumentRead($doc)) {
            return $this->getResponseUnauthorized("Vous n'avez pas accès à ce document");
        }

        $sourceDoc = $this->getDropLocation($doc);

        $this->getActivityLogService()->addUserInfo(
            sprintf("a téléchargé le document '%s'", $doc),
            $this->getDefaultContext(),
            $idDoc
        );

        try {
            $content = FileSystemUtils::getInstance()->file_get_contents($sourceDoc);
            //header('Content-Disposition: attachment; filename="' . $filename . '"');
            //header('Content-type: ' . $doc->getFileTypeMime());
            header('Content-Type: ' . $doc->getFileTypeMime());
            header('Content-Transfer-Encoding: Binary');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($sourceDoc));
            readfile($sourceDoc);
        } catch (Exception $e) {
            $this->getLoggerService()->error("Téléchargement du fichier impossible : " . $e->getMessage());
            throw new OscarException("Ce fichier est indisponible sur le serveur");
        }
    }

    /**
     * Manage le mouvement des docs d'un tab ainsi que les docs (versions)
     * Manage en BD des datas et mouvement des documents dans le répertoire physique ciblé
     *
     * @param ContractDocument $document
     * @param array $persons
     * @param bool $docToPrivate
     * @return bool
     * @throws OscarException
     */
    private function manageDocsInTab(
        ContractDocument $document,
        array $persons,
        ?TabDocument $tabDocument,
        ?TypeDocument $type,
        bool $docToPrivate
    ): bool {
        $isSuccess = false;
        $activity = $document->getActivity();
        $pathDocumentsConfig = $this->getOscarConfigurationService()->getDocumentDropLocation();

        // 1 : On va chercher toutes les versions d'un même document
        $documents = $this->getContractDocumentService()->getContractDocumentRepository(
        )->getDocumentsForFilenameAndActivity(
            $document
        );


        $destinationFolder = null;
        //2 : On gère le déplacement de doc et la privatisation
        /** @var ContractDocument $doc */
        foreach ($documents as $doc) {
            //Passage d'un document en privée
            $doc->setPrivate($docToPrivate);
            $doc->setTabDocument($tabDocument);

            //on réinitialise les personnes
            $doc->getPersons()->clear();

            //On ajoute les personnes demandées
            if (count($persons) > 0) {
                foreach ($persons as $idPerson) {
                    $person = $this->getEntityManager()->getRepository(Person::class)->find($idPerson);
                    $doc->addPerson($person);
                }
            }
            //Ajoute l'utilisateur courant
            $doc->addPerson($this->getCurrentPerson());
            $doc->setTypeDocument($type);
        }
        $this->getEntityManager()->flush();

        $this->getActivityLogService()->addUserInfo(
            sprintf("a modifié le document '%s' dans l'activité %s.", $document, $document->getGrant()->log()),
            'Activity',
            $activity->getId()
        );
        return true;
    }

    /**
     * @return Response|JsonModel
     */
    public function processAction()
    {
        try {
            $docId = $this->params()->fromRoute('id');
            $doc = $this->getContractDocumentService()->getDocument($docId, true);
            // TODO check access
            if ($doc->getProcess() && $doc->getProcess()->isInProgress()) {
                $this->getContractDocumentService()->getProcessService()->trigger($doc->getProcess());
            }
            return $this->jsonOutput(['response' => 'ok']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Création du répertoire si celui-ci n'existe pas
     *
     * @param string $folder
     * @return void
     */
    private function createFolder(string $folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder);
        }
    }
}
