<?php

namespace Oscar\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ContractDocumentService;
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
     * @return UnicaenDoctrinePaginator[]
     * @throws Exception
     */
    public function indexAction() :array
    {
        $documents = $this->getVersionnedDocumentService()->getDocuments();
        $page = $this->params()->fromQuery('page', 1);
        return [
            'documents' => new UnicaenDoctrinePaginator($documents, $page),
        ];
    }

    /**
     * Suppression d'un document
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction()
    {
        $document = $this->getContractDocumentService()->getDocument(
            $this->params()->fromRoute('id'),
            true
        );

        $access = $this->getOscarUserContextService()->getAccessDocument($document);

        if ($access['write'] === true) {
            $activity = $document->getActivity();
            try {
                $this->getContractDocumentService()->deleteDocument($document);
            } catch (Exception $exception) {
                throw new OscarException("Impossible de supprimer le document : " . $exception->getMessage());
            }
            $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
        }
        else {
            return $this->getResponseUnauthorized("Vous ne pouvez pas supprimer ce document");
        }
    }


    /**
     * Modification document (onglets, TYPE, privé/oui/non ...)
     *
     * @return JsonModel
     * @throws OscarException|\HttpException
     */
    public function changeTypeAction()
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
     * Upload de document sur une activité
     * /documents-des-contracts/televerser/:idactivity[/:idtab][/:id]
     *
     * @return array
     * @annotations Procédure générique pour l'envoi des fichiers.
     */
    public function uploadAction()
    {
        // Récupération des données envoyées
        $action = $this->params()->fromPost('action');
        $idActivity = $this->params()->fromRoute('idactivity');
        $activity = $this->getActivityService()->getActivityById($idActivity);
        $json = $this->params()->fromPost('data');
        $documentDatas = json_decode($json, true);
        $documentId = $documentDatas['id'];

        $this->getLoggerService()->info("[document:$action] from " . strval($this->getCurrentPerson()));

        // Check des droits
        $document = null;
        if ($documentId > 0) {
            try {
                $document = $this->getContractDocumentService()->getDocument($documentId, true);
            } catch (Exception $e) {
                $this->getLoggerService()->error($e->getMessage());
                return $this->jsonError($e->getMessage());
            }
            if (!$this->getOscarUserContextService()->getAccessDocument($document)) {
                return $this->jsonError("Vous n'avez pas les droits pour gérer ce document");
            }
        }

        $idTab = $documentDatas['tabDocument']['id'];
        $tabDocument = $this->getContractDocumentService()->getContractTabDocument($idTab);
        if (!$this->getOscarUserContextService()->getAccessTabDocument($tabDocument)) {
            return $this->getResponseUnauthorized(
                "Vous n'avez pas les authorisations pour téléverser un document dans cet onglet"
            );
        }

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
                $this->getContractDocumentService()->uploadContractDocument(
                    $_FILES['file'],
                    $activity,
                    $tabDocument,
                    $this->getContractDocumentService()->getContractDocumentType($idType),
                    $this->getCurrentPerson(),
                    $privatePersons,
                    $dateDeposit,
                    $dateSend,
                    $information,
                    $url
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
            $activity = $this->getActivityService()->getActivityById($idActivity);
            // TODO : check des droits d'accès
            $document = $this->getContractDocumentService()->getDocument($documentDatas['id']);
            if (!$document->getTypeDocument()->getSignatureFlow()) {
                $document->setTypeDocument(
                    $this->getContractDocumentService()->getContractDocumentType($documentDatas['category']['id'])
                );
            }
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
            $flow = $this->params()->fromPost('flow');
            $flowDt = null;
            if ($flow != 'false') {
                // DISABLED
                // $flowDt = json_decode($flow, true);
            }
            $this->getContractDocumentService()->uploadContractDocument(
                $_FILES['file'],
                $activity,
                $tabDocument,
                $this->getContractDocumentService()->getContractDocumentType($idType),
                $this->getCurrentPerson(),
                $privatePersons,
                $dateDeposit,
                $dateSend,
                $information,
                $url,
                $flowDt
            );
            return new JsonModel([
                                     'response' => 'ok'
                                 ]);
        } catch (Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            return $this->jsonError($e->getMessage());
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
     * @return \Laminas\Http\Response|JsonModel
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
