<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:32
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ContractDocumentService;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\Request;
use Zend\Json\Server\Exception\HttpException;
use Zend\View\Model\JsonModel;


/**
 * Class ContractDocumentController
 * @package Oscar\Controller
 */
class ContractDocumentController extends AbstractOscarController
{

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
        return $this->getServiceLocator()->get('ContractDocumentService');
    }

    /**
     * Retourne le service pour gérer les documents.
     *
     * @return VersionnedDocumentService
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
     * Procédure générique pour l'envoi des fichiers.
     *
     * @return array
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
                'types' => $this->getServiceLocator()->get('ContractDocumentService')->getContractDocumentTypes()
            ];
        } catch( \Exception $e ){
            throw new OscarException($e->getMessage());
        }

    }

    public function downloadAction()
    {
        $idDoc = $this->params()->fromRoute('id');
        /** @var ContractDocument $doc */
        $doc = $this->getServiceLocator()->get('ContractDocumentService')->getDocument($idDoc)->getQuery()->getSingleResult();


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

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return ContractDocumentService
     */
    protected function contractDocumentService()
    {
        return $this->getServiceLocator()->get('ContractDocumentService');
    }
}
