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

        // Récupération
        $documents = $em->createQueryBuilder('d')
            ->select('d')
            ->where('d.fileName = :fileName AND d.grant = :grant')
            ->setParameters([
                'fileName' => $document->getFileName(),
                'grant' => $document->getGrant()
            ])
            ->getQuery()->getResult();

        foreach( $documents as $doc ){
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
     * Modification type de document
     *
     * @return JsonModel
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \HttpException
     */
    public function changeTypeAction(){

        /** @var Request $request */
        $request = $this->getRequest();
        dd($request->getContent());


        if( $request->isPost() ){
            /** @var ContractDocument $document */
            $document = $this->getEntityManager()->getRepository(ContractDocument::class)->find($request->getPost('documentId'));
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $document->getGrant());
            $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($request->getPost('type'));
            if( !$type ){
                $this->getResponseBadRequest("Type de document invalide");
            } else {
                $document->setTypeDocument($type);
                $this->getEntityManager()->flush();
                $response = new JsonModel(['response' => 'ok']);
            }
            return $response;
        }

        throw new \HttpException();

    }

    /**
     * Upload de document sur une activité
     *
     * /documents-des-contracts/televerser/idActivité/idDocument/idTab
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
        $idTab = $this->params()->fromRoute('idtab');
        $activity = $this->getActivityService()->getGrant($idActivity);
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $activity);

        try {
            //ATTENTION JACK SE SERT DES POSTS POUR SAVOIR SI LE CONTEXTE EST UPLOAD OU PAS
            //Pour info, il faudra récup la section ici (évolution en cours), passer cette section au ServiceContextUpload pour faire traitement et choisir la stratégie adaptée d'upload
            //Exemple récup possible
            //$section = $this->params()->fromQuery('section,' null);
            //Il faudra récupérer le type pour choisir stratégie dans le service serviceUpload pareil que section (évolution prévue sous peu)
            //Exemple récup possible
            //$typeDocument = this->params()->fromQuery('type', null);
            // Get ID pour remplacement ou ajout
            $docId = $this->params()->fromQuery('id', null);
            // Les injections de service nécessaires pour le service de traitement upload
            $documentService = $this->getVersionnedDocumentService();
            $oscarUserContext = $this->getOscarUserContext();
            $notificationService = $this->getNotificationService();
            $activityLogService = $this->getActivityLogService();
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
                $activityLogService
            );
            $processUpload = $serviceUpload->processUpload();
            // IF TRUE =-> POSTS
            if(true == $processUpload)
            {
                // Le retour bool true indique que nous avons des posts donc nous allons traiter et nous devons aller chercher les infos dont nous avons besoin pour retour
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
            // Affichage template par défaut upload new doc -> view/oscar/contract-document/upload.phtml
            // Ancien code version formulaire Zend
            /*return [
                'activity' => $activity,
                'data'  => $datas,
                'types' => $this->getContractDocumentService()->getContractDocumentTypes(),
                'tabs' => $this->getContractDocumentService()->getContractTabDocuments(),
            ];*/

        }catch (Exception $e){
            // TODO traiter exception voir avec Jack ce qu'il souhaite/préfère ou pratique habituelle du traitement des exceptions dans Oscar ?
            $this->getLoggerService()->error($e->getMessage());
            die($e->getMessage());
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
