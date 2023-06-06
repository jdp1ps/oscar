<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:32
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use Oscar\Constantes\Constantes;
use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ContractDocumentService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Strategy\Upload\conventionSignee;
use Oscar\Strategy\Upload\ServiceContextUpload;
use Oscar\Strategy\Upload\StrategyGedUpload;
use Oscar\Strategy\Upload\StrategyOscarUpload;
use Oscar\Strategy\Upload\StrategyTypeInterface;
use Oscar\Strategy\Upload\TypeGed;
use Oscar\Strategy\Upload\TypeOscar;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\FileSystemUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Psr\Container\ContainerInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Server\Exception\HttpException;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\View\Model\JsonModel;


/**
 * Class ContractDocumentController
 * @package Oscar\Controller
 */
class ContractDocumentController extends AbstractOscarController implements UseServiceContainer
{

    use UseServiceContainerTrait;

    /**
     * @return ProjectGrantService
     */
    public function getActivityService()
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContext()
    {
        return $this->getOscarUserContextService();
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->getServiceContainer();
    }

    /**
     * @return mixed
     */
    public function getNotificationService()
    {
        return $this->getServiceContainer()->get(NotificationService::class);
    }

    public function getActivityLogService(): ActivityLogService
    {
        return $this->getServiceContainer()->get(ActivityLogService::class);
    }


    private $versionnedDocumentService;

    /**
     * Retourne l'emplacement où sont stoqués les documents depuis le fichier
     * de configuration local.php
     *
     * @param ContractDocument|null $document
     * @return mixed|string
     * @throws OscarException
     */
    protected function getDropLocation(ContractDocument $document = null)
    {
        if (!is_null($document)) {
            return $this->getOscarConfigurationService()->getDocumentRealpath($document);
        } else {
            return $this->getOscarConfigurationService()->getDocumentDropLocation();
        }
    }

    /**
     * @return ContractDocumentService
     */
    protected function getContractDocumentService()
    {
        return $this->getServiceContainer()->get(ContractDocumentService::class);
    }

    /**
     * @return VersionnedDocumentService
     * @throws Exception
     * @annotations Retourne le service pour gérer les documents
     */

    protected function getVersionnedDocumentService()
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
    ////////////////////////////////////////////////////////////////////////////


    /**
     * @return UnicaenDoctrinePaginator[]
     * @throws Exception
     */
    public function indexAction()
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
        } else {
            return $this->getResponseUnauthorized("Vous ne pouvez pas supprimer ce document");
        }
    }


    /**
     * Modification document (onglets, TYPE, privé/oui/non ...)
     *
     * @return JsonModel
     * @throws OscarException|\HttpException
     */
    public function changeTypeAction(): JsonModel
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Récup document
            $document = $this->getContractDocumentService()->getDocument($request->getPost('documentId'));
            if( !($this->getOscarUserContextService()->getAccessDocument($document)['write'] === true) ){
                return $this->getResponseUnauthorized("Vous ne pouvez pas modifier ce document");
            }

            $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($request->getPost('type'));
            if (!$type) {
                $this->getResponseBadRequest("Type de document invalide");
            }
            $privateDocument = $request->getPost('private');
            $idsPersons = (trim($request->getPost('persons')) !== "") ? explode(",", $request->getPost('persons')) : [];
            // Passage du doc en non privé ou else -> passage en privé
            if (false === boolval($privateDocument)) {
                $tabDocument = $this->getEntityManager()->getRepository(TabDocument::class)->find(
                    $request->getPost('tabDocument')
                );
                $succesManageDocuments = $this->manageDocsInTab($document, $idsPersons, $tabDocument, $type, false);
                if (false === $succesManageDocuments) {
                    $this->getResponseBadRequest("La gestion des documents associés a échouée !");
                }
            } else {
                $succesManageDocuments = $this->manageDocsInTab($document, $idsPersons, null, $type, true);
                if (false === $succesManageDocuments) {
                    $this->getResponseBadRequest("La gestion des documents associés a échouée !");
                }
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
        $datas = [
            'informations' => '',
            'type' => 0,
            'error' => '',
        ];
        $idActivity = $this->params()->fromRoute('idactivity');
        $activity = $this->getActivityService()->getActivityById($idActivity);
        $idTab = $this->params()->fromRoute('idtab') === "private" ? null : $this->params()->fromRoute('idtab');

        if ($idTab === 'private' && !$this->getOscarUserContextService()->hasPrivileges(
                Privileges::ACTIVITY_DOCUMENT_MANAGE,
                $activity
            )) {
            return $this->getResponseUnauthorized("Vous ne pouvez pas téléverser un document privé");
        } else {
            $tabDocument = $this->getContractDocumentService()->getContractTabDocument($idTab);
            if (!$this->getOscarUserContextService()->getAccessTabDocument($tabDocument)) {
                return $this->getResponseUnauthorized(
                    "Vous n'avez pas les authorisations pour téléverser un document dans cet onglet"
                );
            }
        }

        try {
            // Get ID doc pour remplacement ou ajout
            $docId = $this->params()->fromRoute('id', null);
            // Les injections de service nécessaires pour le service de traitement upload
            $documentService = $this->getVersionnedDocumentService();
            $oscarUserContext = $this->getOscarUserContext();
            $notificationService = $this->getNotificationService();
            $activityLogService = $this->getActivityLogService();
            $oscarConfigurationService = $this->getOscarConfigurationService();
            /** $serviceUpload instanciation */
            $serviceUpload = new ServiceContextUpload
            (
                $this->getRequest(),
                $docId,
                $documentService,
                $datas,
                $idActivity,
                $activity,
                $oscarUserContext,
                $notificationService,
                $activityLogService,
                $oscarConfigurationService
            );
            $processUpload = $serviceUpload->processUpload();
            // IF TRUE =-> POSTS
            if (true === $processUpload) {
                switch ($serviceUpload->getStrategy()->getEtat()) {
                    case true:
                        // Infos juste pour xdebug
                        $infos = $serviceUpload->getStrategy()->getDatas();
                        if ($infos['error']) {
                            $datas['error'] = $infos['error'];
                        } else {
                            $this->redirect()->toRoute(
                                'contract/show',
                                ['id' => $serviceUpload->getStrategy()->getDatas()['activityId']]
                            );
                        }
                        break;
                    default:
                        throw new Exception(
                            "Erreur arrivé dans le cas par défaut switch case ? -> Méthode : " . __METHOD__ . " Fichier : " . __FILE__ . " Ligne : " . __LINE__
                        );
                        break;
                }
            } else {
                throw new Exception("Accès interdit en dehors de la soumission de données");
            }
        } catch (Exception $e) {
            // TODO traiter exception voir avec Jack ce qu'il souhaite/préfère ou pratique habituelle du traitement des exceptions dans Oscar ?
            $this->getLoggerService()->error($e->getMessage());
            return $this->getResponseInternalError($e->getMessage());
        }
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function downloadAction()
    {
        $idDoc = $this->params()->fromRoute('id');
        /** @var ContractDocument $doc */
        $doc = $this->getContractDocumentService()->getDocument($idDoc);

        $activity = $doc->getGrant();

        if( !($this->getOscarUserContextService()->getAccessDocument($doc)['read'] == true) ){
            return $this->getResponseUnauthorized("Vous n'avez pas accès à ce document");
        }

        $sourceDoc = $this->getDropLocation($doc);

        $this->getActivityLogService()->addUserInfo(
            sprintf("a téléchargé le document '%s'", $doc),
            $this->getDefaultContext(),
            $idDoc
        );

        $filename = $doc->getFileName();

        // Utilisation du numéro de version ?
        if ($this->getOscarConfigurationService()->getDocumentUseVersionInName() === true) {
            $version = $doc->getVersion();
            $filename = preg_replace('/(.*)(\.\w*)/', '$1-version-' . $version . '$2', $filename);
        }

        $content = FileSystemUtils::getInstance()->file_get_contents($sourceDoc);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-type: ' . $doc->getFileTypeMime());
        die($content);
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
        $em = $this->getEntityManager()->getRepository(ContractDocument::class);
        $documents = $em->createQueryBuilder('d')->select('d');
        // Params pour la requete de base
        $paramsQuery = [
            'fileName' => $document->getFileName(),
            'grant' => $activity,
        ];
        if (true === $document->isPrivate()) {
            // Documents privés
            $paramsQuery ['private'] = true;
            $documents->where(
                'd.fileName = :fileName 
                AND d.grant = :grant
                AND d.private = :private'
            );
        } else {
            if (!is_null($document->getTabDocument())) {
                $paramsQuery ['tabDocument'] = $document->getTabDocument();
                $documents->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant 
                    AND d.tabDocument = :tabDocument'
                );
            } else {
                // Document version antérieur feature onglets de documents (non classés)
                $paramsQuery ['private'] = false;
                $documents->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant
                    AND d.private = :private'
                );
            }
        }
        $result = $documents->setParameters($paramsQuery)->getQuery()->getResult();

        $destinationFolder = null;
        //2 : On gère le déplacement de doc et la privatisation
        /** @var ContractDocument $doc */
        foreach ($result as $doc) {
            //Passage d'un document en privée
            if (true === $docToPrivate) {
                $pathSource = (!is_null($doc->getTabDocument())) ? $pathDocumentsConfig . 'tab_' . $doc->getTabDocument(
                    )->getId() . '/' . $doc->getPath() : $pathDocumentsConfig;
                $pathDestination = $pathDocumentsConfig . 'private/' . $doc->getPath();
                $destinationFolder = $pathDocumentsConfig . 'private';
                $this->createFolder($destinationFolder);
                //On supprime les tabDocuments
                $doc->setTabDocument(null);
                //On rend le document privé
                $doc->setPrivate(true);
            } else {
                if ($doc->isPrivate()) {
                    $pathSource = $pathDocumentsConfig . 'private/' . $doc->getPath();
                } else {
                    $pathSource = (!is_null(
                        $doc->getTabDocument()
                    )) ? $pathDocumentsConfig . 'tab_' . $doc->getTabDocument()->getId() . '/' . $doc->getPath(
                        ) : $pathDocumentsConfig . '/' . $doc->getPath();
                }
                $pathDestination = $pathDocumentsConfig . 'tab_' . $tabDocument->getId() . '/' . $doc->getPath();
                $destinationFolder = $pathDocumentsConfig . 'tab_' . $tabDocument->getId();
                $this->createFolder($destinationFolder);
                //Passage d'un document dans un onglet
                $doc->setTabDocument($tabDocument);
                $doc->setPrivate(false);
            }
            //on réinitialise les personnes
            foreach ($doc->getPersons() as $person) {
                $doc->removePerson($person);
            }
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
            $this->getEntityManager()->persist($doc);
            $this->getEntityManager()->flush();
            //Déplacement des fichiers dans le bon répertoire
            rename($pathSource, $pathDestination);
        }

        $this->getActivityLogService()->addUserInfo(
            sprintf("a modifié le document '%s' dans l'activité %s.", $document, $document->getGrant()->log()),
            'Activity',
            $activity->getId()
        );
        return $isSuccess;
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

    public function showAction()
    {
        return $this->getResponseNotImplemented();
    }
}
