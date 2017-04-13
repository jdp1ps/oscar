<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-06-17 13:51
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Entity\AdministrativeDocument;
use Oscar\Exception\OscarException;
use Oscar\Service\VersionnedDocumentService;

class AdministrativeDocumentController extends AbstractOscarController
{
    private $versionnedDocumentService;

    /**
     * Retourne l'emplacement où sont stoqués les documents depuis le fichier
     * de configuration local.php
     *
     * @return mixed
     */
    protected function getDropLocation(){
        return $this->getServiceLocator()->get('Config')['oscar']['paths']['document_admin_oscar'];
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
                AdministrativeDocument::class
            );
        }
        return $this->versionnedDocumentService;
    }

    /**
     * Liste des tous les documents.
     *
     * @return array
     */
    public function indexAction() {
        return [
            'documents' => $this->getVersionnedDocumentService()->getDocumentsPublished()->getQuery()->getResult()
        ];
    }

    /**
     * Procédure générique pour l'envoi des fichiers.
     *
     * @return array
     */
    public function uploadAction() {
        try {
            $docId = $this->params()->fromQuery('id', null);
            $docReplaced = null;
            if ($docId) {
                if ($doc = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->find($docId)) {
                    $docReplaced = $doc->getFileName();
                }
            }
            $documentService = $this->getVersionnedDocumentService();

            $documentService->performRequest($this->getRequest(), $docReplaced,
                function (AbstractVersionnedDocument $document) {
                    $this->getActivityLogService()->addUserInfo(
                        sprintf("a déposé le document '%s'",
                            $document->getFileName()), 'AdministrativeDocument',
                        $document->getId()
                    );

                    $this->redirect()->toRoute('administrativedocument');
                });
            return [

            ];
        } catch( \Exception $e ){
            throw new OscarException($e->getMessage());
        }
    }

    public function downloadAction() {
        $idDoc = $this->params()->fromRoute('id');

        /** @var AdministrativeDocument $doc */
        $doc = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->find($idDoc);

        $fileDir = $this->getDropLocation() . $doc->getPath();

        $this->getActivityLogService()->addUserInfo(sprintf("a téléchargé le document administratif '%s'", $doc->getPath()), $this->getDefaultContext(), $idDoc);

        header('Content-Disposition: attachment; filename="'.$doc->getVersion().'-'.$doc->getFileName().'"');
        header('Content-type: '. $doc->getFileTypeMime());
        readfile($fileDir);
        die();
    }

    /**
     * @throws OscarException
     */
    public function deleteAction() {
        $idDoc = $this->params()->fromRoute('id');
        try {
            $this->getVersionnedDocumentService()->deleteDocument($idDoc);
        } catch( \Exception $e ){
            throw new OscarException($e->getMessage());
        }
        $this->redirect()->toRoute('administrativedocument');
    }
}