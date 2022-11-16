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
    public function getActivityService(){
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContext(){
        return $this->getOscarUserContextService();
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator(){
        return $this->getServiceContainer();
    }

    /**
     * @return mixed
     */
    public function getNotificationService() {
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
     * @return mixed
     */
    protected function getDropLocation(){
        return $this->getContractDocumentService()->getDropLocation();
    }

    /**
     * @return ContractDocumentService
     */
    protected function getContractDocumentService(){
        return $this->getServiceContainer()->get(ContractDocumentService::class);
    }

    /**
     * @return VersionnedDocumentService
     * @throws Exception
     * @annotations Retourne le service pour gérer les documents
     */

    protected function getVersionnedDocumentService(){
        if( null === $this->versionnedDocumentService ){
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
            'documents' => new UnicaenDoctrinePaginator($documents, $page)
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
        $em = $this->getEntityManager()->getRepository(ContractDocument::class);

        /** @var ContractDocument $document */
        $document = $em->find($this->params()->fromRoute('id'));

        if( !$document ){
            $this->getResponseNotFound("Ce document n'existe plus...");
        }

        $activity = $document->getGrant();
        if( !$activity ){
            $this->getResponseInternalError("Ce document n'est plus associé à une activité, faites une demande de suppression auprès de l'administrateur.");
        }

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $activity);

        // Récupération (Attention Cas spécifique documents privés)
        $documents = $em->createQueryBuilder('d')->select('d');
        $paramsQuery = [
            'fileName' => $document->getFileName(),
            'grant' => $document->getGrant(),
        ];
        // Documents privé
        if ($document->isPrivate() === true ){
            $paramsQuery ['private'] = true;
            $documents ->where(
                'd.fileName = :fileName 
                AND d.grant = :grant
                AND d.private = :private'
            );
        }else{
            if(!is_null($document->getTabDocument())){
                $paramsQuery ['tabDocument'] = $document->getTabDocument();
                $documents ->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant 
                    AND d.tabDocument = :tabDocument'
                );
            }else{
                $paramsQuery ['private'] = false;
                $documents ->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant
                    AND d.private = :private'
                );
            }
        }
        $results = $documents->setParameters($paramsQuery)->getQuery()->getResult();
        foreach( $results as $doc ){
            $this->getEntityManager()->remove($doc);
        }
        $this->getEntityManager()->flush();

        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé le document '%s' dans l'activité %s.", $document, $document->getGrant()->log()),
            'Activity',
            $activity->getId()
        );

        $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
    }


    /**
     * Modification document (onglets, TYPE, privé/oui/non ...)
     *
     * @return JsonModel
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws OscarException|\HttpException
     */
    public function changeTypeAction(): JsonModel
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if( $request->isPost() ){
            // Récup document
            /** @var ContractDocument $document */
            $document = $this->getEntityManager()->getRepository(ContractDocument::class)->find($request->getPost('documentId'));
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $document->getGrant());

            // Traitement métier et Posts
            // Type de doc
            $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($request->getPost('type'));
            if( !$type ){
                $this->getResponseBadRequest("Type de document invalide");
            }
            // Privé ou non traitements métiers
            $privateDocument = $request->getPost('private');
            // Si Document non privée
            // Suppression persons éventuelles et affectation à un onglet (vérifier si cet onglet existe comme type).
            if (false === boolval($privateDocument)){
                $document->setPrivate(false);
                foreach ($document->getPersons() as $person){
                    $document->removePerson($person);
                }
                $tabDocument = $this->getEntityManager()->getRepository(TabDocument::class)->find($request->getPost('tabDocument'));
                if( !$tabDocument ){
                    $this->getResponseBadRequest("Onglet de document invalide");
                }
                $document->setTabDocument($tabDocument);

            }else{
                // Cas Document privé
                $document->setPrivate(true);
                $document->setTabDocument(null);
                if (trim($request->getPost('persons')) !== "") {
                    $idsPersons = explode(",", $request->getPost('persons'));
                    if (count($idsPersons) > 0) {
                        foreach ($document->getPersons() as $person){
                            $document->removePerson($person);
                        }
                        foreach ($idsPersons as $idPerson) {
                            $person = $this->getEntityManager()->getRepository(Person::class)->find($idPerson);
                            $document->addPerson($person);
                        }
                    }
                    $document->addPerson($this->getCurrentPerson());
                }else{
                    $document->addPerson($this->getCurrentPerson());
                }
                // TODO Manage documents associés et déplacement physiquement dans les répertoire des documents
                $succesManageDocuments = $this->manageDocsInTab(true, $document);
                if (false === $succesManageDocuments){
                    $this->getResponseBadRequest("La gestion des documents associés a échouée !");
                }
            }
            $document->setTypeDocument($type);
            $this->getEntityManager()->flush();

            return new JsonModel(['response' => 'ok']);
        }
        throw new \HttpException();
    }


    /**
     * Manage le mouvement des docs d'un tab ainsi que les docs (versions)
     * Manage en BD des datas et mouvement des documents dans le répertoire physique ciblé
     *
     * @param bool $docToPrivate
     * @param ContractDocument $document
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function manageDocsInTab(bool $docToPrivate, ContractDocument $document):bool{
        $isSuccess = false;
        $activity = $document->getActivity();

        // 1 : Récupérer le statut du doc (tabId du document s'il était dans un contexte d'un onglet ou déjà tagué isPrivate true)
        $docStatusIsPrivate = $document->isPrivate();

        $em = $this->getEntityManager()->getRepository(ContractDocument::class);
        $documents = $em->createQueryBuilder('d')->select('d');
        $paramsQuery = [
            'fileName' => $document->getFileName(),
            'grant' => $activity,
        ];
        // 2 : Si document était déja en privé on récupère les docs associés
        if (true === $docStatusIsPrivate){
            // Documents privés
                $paramsQuery ['private'] = true;
                $documents ->where(
                    'd.fileName = :fileName 
                AND d.grant = :grant
                AND d.private = :private'
                );
        }else{
            if(!is_null($document->getTabDocument())){
                $paramsQuery ['tabDocument'] = $document->getTabDocument();
                $documents ->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant 
                    AND d.tabDocument = :tabDocument'
                );
            }else{
                $paramsQuery ['private'] = false;
                $documents ->where(
                    'd.fileName = :fileName 
                    AND d.grant = :grant
                    AND d.private = :private'
                );
            }
        }
        // 3 : Document récupérer selon le statut et le nom
        $results = $documents->setParameters($paramsQuery)->getQuery()->getResult();

        // 4 : Mettre à jour les datas des documents et les changer de répertoire
        // TODO mettre a jour les datas et bouger les documents dans le répertoire dédié
        /** @var ContractDocument $doc */
        foreach( $results as $doc ){
            // Souhait de passer ce doc en mode privé
            // Attention ne pas oublier d'affecter personnes et la personne en cours qui fait la modif
            if (true === $docToPrivate){
                $tabId = $document->getTabDocument()->getId();
                if(!is_null($tabId)){
                    // TODO
                    // Déplacer document de l'endroit tab actuel vers rep "private"
                }else{
                    // TODO
                    // Vérifier si il est pas genre pas classé ? documents avant que les onglets soit faits pour le déplacer
                }
                // Manage datas documents
                $doc->setPrivate(true);
                $doc->setTabDocument(null);

            }
        }

        $this->getEntityManager()->flush();
        $this->getActivityLogService()->addUserInfo(
            sprintf("a modifié le document '%s' dans l'activité %s.", $document, $document->getGrant()->log()),
            'Activity',
            $activity->getId()
        );

        return $isSuccess;
    }

    /**
     * Upload de document sur une activité
     * /documents-des-contracts/televerser/:idactivity[/:idtab][/:id]
     *
     * @return array
     * @annotations Procédure générique pour l'envoi des fichiers.
     */
    public function uploadAction() {

        $datas = [
            'informations' => '',
            'type' => 0,
            'error' => '',
        ];
        $idActivity = $this->params()->fromRoute('idactivity');
        $idTab = $this->params()->fromRoute('idtab') === "private"?null:$this->params()->fromRoute('idtab');
        $activity = $this->getActivityService()->getGrant($idActivity);
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $activity);

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
            if(true === $processUpload)
            {
                switch ($serviceUpload->getStrategy()->getEtat()){
                    case true:
                        // Infos juste pour xdebug
                        $infos = $serviceUpload->getStrategy()->getDatas();
                        if( $infos['error'] ){
                            $datas['error'] = $infos['error'];
                        } else {
                            $this->redirect()->toRoute('contract/show', ['id' => $serviceUpload->getStrategy()->getDatas()['activityId']]);
                        }
                        break;
                    default:
                        throw new Exception("Erreur arrivé dans le cas par défaut switch case ? -> Méthode : ". __METHOD__ . " Fichier : " . __FILE__ . " Ligne : " . __LINE__);
                        break;
                }
            }else{
                throw new Exception("Accès interdit en dehors de la soumission de données");
            }
        }catch (Exception $e){
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
        $doc = $this->getContractDocumentService()->getDocument($idDoc)->getQuery()->getSingleResult();


        $activity = $doc->getGrant();
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_SHOW, $activity);


        $fileDir = $this->getDropLocation();
        $this->getActivityLogService()->addUserInfo(sprintf("a téléchargé le document '%s'", $doc), $this->getDefaultContext(), $idDoc);


        $filename = $doc->getFileName();

        // Utilisation du numéro de version ?
        if( $this->getOscarConfigurationService()->getDocumentUseVersionInName() === true ){
            $version = $doc->getVersion();
            $filename = preg_replace('/(.*)(\.\w*)/', '$1-version-'.$version.'$2', $filename);
        }

        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-type: '. $doc->getFileTypeMime());
        readfile($fileDir.'/'.$doc->getPath());
        die();
    }

    public function showAction()
    {
        return $this->getResponseNotImplemented();
    }
}
