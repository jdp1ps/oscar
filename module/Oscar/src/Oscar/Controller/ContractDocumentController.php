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
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\FileSystemUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Psr\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\Json\Server\Exception\HttpException;
use Laminas\View\Model\JsonModel;


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
        }
        else {
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
        $action = $this->params()->fromPost('action');
        $idActivity = $this->params()->fromRoute('idactivity');
        $json = $this->params()->fromPost('data');
        $documentDatas = json_decode($json, true);
        $this->getLoggerService()->debug(json_encode($documentDatas));

        $dateDeposit = $documentDatas['dateDeposit'];
        if ($dateDeposit) {
            $dateDeposit = new \DateTime($dateDeposit);
        } else {
            $dateDeposit = null;
        }
        $dateSend = $this->params()->fromPost('dateSend');
        if ($dateSend) {
            $dateSend = new \DateTime($dateSend);
        } else {
            $dateSend = null;
        }
        $informations = $documentDatas['informations'];

        $this->getLoggerService()->debug(
            " > " .
            'DateDeposit: ' . ($dateDeposit ? $dateDeposit->format('Y-m-d') : "nop") .
            ' - DateSend: ' . ($dateSend ? $dateSend->format('Y-m-d') : "nop") .
            ' - Informations: ' . $informations

        );

        if( $action == 'edit' ){
            $activity = $this->getActivityService()->getActivityById($idActivity);
            // TODO : check des droits d'accès
            $document = $this->getContractDocumentService()->getDocument($documentDatas['id']);
            if( !$document->getTypeDocument()->getSignatureFlow() ){
                $document->setTypeDocument($this->getContractDocumentService()->getContractDocumentType($documentDatas['category']['id']));
            }
            $document->setTabDocument($this->getContractDocumentService()->getContractTabDocument($documentDatas['tabDocument']['id']));
            $document->setDateDeposit($dateDeposit);
            $document->setDateSend($dateSend);
            $document->setInformation($informations);
            $this->getEntityManager()->flush();

            return new JsonModel(['message' => 'ok']);
        }

        $activity = $this->getActivityService()->getActivityById($idActivity);
        $idTab = $documentDatas['tabDocument']['id'];
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

        // Récupération des informations
        $tabDocument = $this->getContractDocumentService()->getContractTabDocument($idTab);
        if (!$this->getOscarUserContextService()->getAccessTabDocument($tabDocument)) {
            return $this->getResponseUnauthorized(
                "Vous n'avez pas les authorisations pour téléverser un document dans cet onglet"
            );
        }

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
                $informations,
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
