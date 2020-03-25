<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:32
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

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
use Zend\Http\Request;
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
     * @return \Oscar\Service\OscarUserContext
     */
    public function getOscarUserContext(){
        return $this->getOscarUserContextService();
    }

    /**
     * @return \Psr\Container\ContainerInterface
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
     * @throws \Exception
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


    public function indexAction()
    {
        $documents = $this->getVersionnedDocumentService()->getDocuments();
        $page = $this->params()->fromQuery('page', 1);
        return [
            'documents' => new UnicaenDoctrinePaginator($documents, $page)
        ];
    }

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

        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé le document '%s' dans l'activité %s.", $document, $document->getGrant()->log()),
            'Activity',
            $activity->getId()
        );

        $this->getEntityManager()->flush();
        $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
    }


    public function changeTypeAction(){

        /** @var Request $request */
        $request = $this->getRequest();

        if( $request->isPost() ){
            /** @var ContractDocument $document */
            $document = $this->getEntityManager()->getRepository(ContractDocument::class)->find($request->getPost('documentId'));
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $document->getGrant());
            $type = $this->getEntityManager()->getRepository(TypeDocument::class)->findOneBy(['label' => $request->getPost('type')]);
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
     * @return array
     * @throws OscarException
     * @annotations Procédure générique pour l'envoi des fichiers.
     */
    public function uploadAction() {
        $datas = [
            'informations' => '',
            'type' => 0,
            'error' => '',
        ];
        $idActivity = $this->params()->fromRoute('idactivity');
        $activity = $this->getActivityService()->getGrant($idActivity);
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_MANAGE, $activity);
        
        /**
         * ******************************************
         * Début tests new code
         */
        try {
            //ATTENTION JACK SE SERT DES POSTS POUR SAVOIR SI IL UPLOAD OU PAS... ?
            //Pour info Il faudra récup la section ici (évolution en cours), passer cette section au ServiceContextUpload pour faire traitement et choisir la stratégie adaptée d'upload
            //Exemple récup possible
            //$section = $this->params()->fromQuery('section,' null);
            //Il faudra récupérer le type pour choisir stratégie dans le service serviceUpload pareil que section (évolution prévue sous peu)
            //Exemple récup possible
            //$typeDocument = this->params()->fromQuery('type', null);
            // Get ID pour remplacement ou ajout
            $docId = $this->params()->fromQuery('id', null);
            // Les injecions de service nécessaires pour le service de traitement upload (une factory pourrait être envisagée).
            $documentService = $this->getVersionnedDocumentService();
            $oscarUserContext = $this->getOscarUserContext();
            $notificationService = $this->getNotificationService();
            $activityLogService = $this->getActivityLogService();

            /** new code $serviceUpload instanciation */
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
            $serviceUpload->treatementUpload();

            // IF TRUE -> POSTS
            if(true == $serviceUpload->treatementUpload())
            {
                //echo "Nous avons des posts il a donc eu traitement ! se servir des ETATS de la stratégie pour savoir quoi faire";
                // Nous avons des posts donc nous allons traiter et nous devons aller chercher les infos dont nous avons besoin pour retour
                //var_dump($serviceUpload->getStrategy()->getDatas() );
                die("Avant switch");
                switch ($serviceUpload->getStrategy()->getEtat()){
                    case 3:
                        echo "success !";
                        var_dump($serviceUpload->getStrategy()->getDatas());
                        die("here");
                        $this->redirect()->toRoute('contract/show', ['id' => $serviceUpload->getStrategy()->getDatas()['activityId']]);
                        break;
                    default:
                        echo "pas normal d'arrivé là... !";
                        break;
                }
                //die("stop");
            }

            return [
                'activity' => $activity,
                'data'  => $datas,
                'types' => $this->getContractDocumentService()->getContractDocumentTypes()
            ];

        }catch (\Exception $e){
            die($e->getMessage());
        }
        /**
         * ******************************************
         * Fin nouveau code
         */

        /** Ancien code en cours de reprise */
        try {
            $docId = $this->params()->fromQuery('id', null);
            $docReplaced = null;
            if ($docId) {
                if ($doc = $this->getEntityManager()->getRepository(ContractDocument::class)->find($docId)) {
                    $docReplaced = $doc->getFileName();
                }
            }
            $documentService = $this->getVersionnedDocumentService();

            $datas = $documentService->performRequest($this->getRequest(), $docReplaced,
                function (ContractDocument $document) use( $activity ) {

                    $this->getNotificationService()->generateActivityDocumentUploaded($document);

                    $this->getActivityLogService()->addUserInfo(
                                sprintf("a déposé le document '%s' dans l'activité %s", $document->getFileName(), $activity->log()),
                                'Activity', $activity->getId()
                            );
                    $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
                },
                function( ContractDocument $document, $datas ) use ($activity, $documentService){
                    $document->setGrant($activity)
                        ->setPerson($this->getOscarUserContext()->getCurrentPerson())
                        ->setDateDeposit($datas["dateDeposit"] ? new \DateTime($datas["dateDeposit"]):null)
                        ->setDateSend($datas['dateSend'] ? new \DateTime($datas['dateSend']):null)
                        ->setTypeDocument($documentService->getContractDocumentType($datas['type']));
                    return $document;
                }
            );
            return [
                'activity' => $activity,
                'data'  => $datas,
                'types' => $this->getContractDocumentService()->getContractDocumentTypes()
            ];
        } catch( \Exception $e ){
            throw new OscarException($e->getMessage());
        }

    }

    public function downloadAction()
    {
        $idDoc = $this->params()->fromRoute('id');
        /** @var ContractDocument $doc */
        $doc = $this->getContractDocumentService()->getDocument($idDoc)->getQuery()->getSingleResult();


        $activity = $doc->getGrant();
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_SHOW, $activity);


        $fileDir = $this->getDropLocation();
        $this->getActivityLogService()->addUserInfo(sprintf("a téléchargé le document '%s'", $doc), $this->getDefaultContext(), $idDoc);

        header('Content-Disposition: attachment; filename="'.$doc->getFileName().'"');
        header('Content-type: '. $doc->getFileTypeMime());
        readfile($fileDir.'/'.$doc->getPath());
        die();
    }

    public function showAction()
    {
        return $this->getResponseNotImplemented();
    }
}
