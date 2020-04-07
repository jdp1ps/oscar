<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-06-17 13:51
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Entity\AdministrativeDocument;
use Oscar\Entity\AdministrativeDocumentSection;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityLogService;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;

class AdministrativeDocumentController extends AbstractOscarController implements UseServiceContainer
{

    use UseServiceContainerTrait;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private $versionnedDocumentService;

    /**
     * @return string
     */
    protected function getDropLocation(){
        return $this->getOscarConfigurationService()->getDocumentPublicPath();
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

        $method = $this->getHttpXMethod();
        if( $method == "POST" ){
            $this->getOscarUserContextService()->check(Privileges::ADMINISTRATIVE_DOCUMENT_DELETE);
            $sectionId = $this->params()->fromPost('section_id');
            $section = null;
            if( $sectionId )
                $section = $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->find($sectionId);

            $documentId = $this->params()->fromPost('id');
            /** @var AdministrativeDocument $document */
            $document = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->find($documentId);
            if( !$document ){
                return $this->getResponseInternalError("Document introuvable");
            }

            $documents = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->findBy(['fileName' => $document->getFileName()]);

            foreach ($documents as $document) {
                $document->setSection($section);
            }

            $this->getEntityManager()->flush($documents);
        }

        return [
            'documents' => $this->getAdministrativeDocumentPacked(),
            'sections' => $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->findAll(),
            'moveable' => $this->getOscarUserContextService()->hasPrivileges(Privileges::MAINTENANCE_DOCPUBSEC_MANAGE)
        ];
    }

    public function getAdministrativeDocumentPacked(){
        $query = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->createQueryBuilder('d')
            ->leftJoin('d.section', s)
            ->leftJoin('d.person', 'o')
            ->orderBy('d.fileName', 'ASC')
            ->addOrderBy('d.version', 'DESC')
            ->where('d.status = :status');

        $query->setParameter('status', AbstractVersionnedDocument::STATUS_PUBLISH);

        $output = [];
        $documents = $query->getQuery()->getResult();

        /** @var AdministrativeDocument $document */
        foreach ($documents as $document) {
            $section = $document->getSection() ? $document->getSection()->getLabel() : "";
            if( !array_key_exists($section, $output) ){
                $output[$section] = [];
            }

            $filename = $document->getFileName();
            if( !array_key_exists($filename, $output[$section]) ){
                $output[$section][$filename] = [
                    'main' => $document,
                    'older' => []
                ];
            } else {
                $output[$section][$filename]['older'][] = $document;
            }
        }

        return $output;
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
            $section = null;
            $defaultSection = null;
            $person = $this->getOscarUserContextService()->getCurrentPerson();

            if ($docId) {
                /** @var AdministrativeDocument $doc */
                if ($doc = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->find($docId)) {
                    $docReplaced = $doc->getFileName();
                    $section = $doc->getSection();
                    if( $section )
                        $defaultSection = $section->getId();
                }
            }
            $documentService = $this->getVersionnedDocumentService();

            $response = $documentService->performRequest($this->getRequest(), $docReplaced,
                function (AbstractVersionnedDocument $document) {

                    $this->getActivityLogService()->addUserInfo(
                        sprintf("a déposé le document '%s'",
                            $document->getFileName()), 'AdministrativeDocument',
                        $document->getId()
                    );

                    $this->redirect()->toRoute('administrativedocument');
                },

                function( AdministrativeDocument $document, $datas ) use ($documentService, $section, $person){
                    $sec = null;
                    $secInit = $section ? $section->getId() : null;
                    $secId = null;
                    if( $datas['section_id'] ){
                        $sec = $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->find($datas['section_id']);
                        $secId = $sec->getId();
                    }

                    if( $secInit != $secId ){
                        $docs = $this->getEntityManager()->getRepository(AdministrativeDocument::class)->findBy([
                            'fileName' => $document->getFileName()
                        ]);
                        foreach ($docs as $doc) {
                            $doc->setSection($sec);
                        }
                        $this->getEntityManager()->flush($docs);
                    }


                    $document->setSection($sec);
                    $document->setPerson($person);
                    return $document;
                });
            if( $response && $response['error'] ){
                throw new OscarException($response['error']);
            }
            return [
                "defaultSection" => $defaultSection,
                "sections" => $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->findAll()
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