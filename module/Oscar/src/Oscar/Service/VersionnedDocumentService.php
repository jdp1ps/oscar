<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:35
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Jacksay\PhpFileExtension\Dictonary\ArchiveDictonary;
use Jacksay\PhpFileExtension\Dictonary\DocumentDictionary;
use Jacksay\PhpFileExtension\Dictonary\ImageDictonary;
use Jacksay\PhpFileExtension\Dictonary\OfficeDocumentDictonary;
use Jacksay\PhpFileExtension\Exception\NotFoundExtension;
use Jacksay\PhpFileExtension\PhpFileExtension;
use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Entity\Person;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Laminas\Http\Request;

class VersionnedDocumentService {

    /** @var EntityManager EntityManager */
    private $entityManager;

    /** @var  Emplacement où sont stoqués les fichiers */
    private $documentHome;

    /** @var  string className */
    private $effectiveClass;

    /** @var  Person */
    private $currentPerson;

    /**
     * VersionnedDocumentService constructor.
     * @param EntityManager $entityManager
     * @param string $documentHome
     * @param string $effectiveClass
     * @param Person $currentPerson
     */
    public function __construct( EntityManager $entityManager, $documentHome, $effectiveClass, Person $currentPerson=null)
    {
        $this->entityManager = $entityManager;
        $this->documentHome = $documentHome;
        $this->effectiveClass = $effectiveClass;
        $this->currentPerson = $currentPerson;

        if( !file_exists($documentHome) ){
            throw new \Exception("Emplacement inconnu.");
        }
        if( !is_writable($documentHome) ){
            throw new \Exception("Droits insuffisant pour écrire dans cet emplacement.");
        }
    }

    /**
     * Retourne la requête pour obtenir les documents publiés.
     *
     * @return QueryBuilder
     */
    public function getDocumentsPublished(){
        return $this->getDocuments()->andWhere('d.status = :status')->setParameter('status', AbstractVersionnedDocument::STATUS_PUBLISH);
    }

    /**
     * Retourne tous les documents.
     *
     * @return QueryBuilder
     */
    public function getDocuments()
    {
        return $this->baseQuery()->addOrderBy('d.dateUpdoad', 'DESC');
    }

    /**
     * @return Person
     */
    public function getCurrentPerson(){
        return $this->currentPerson;
    }


    /**
     * Supprime l'enregistrement.
     *
     * @param $document
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws OscarException
     */
    public function deleteDocument( $document ){

        // Get document if needed
        if( !($document instanceof AbstractVersionnedDocument) ){
            try {
                $doc = $this->getDocument($document)->getQuery()->getSingleResult();
            } catch( \Exception $e ){
                throw new OscarException("Document introuvable");
            }
        } else {
            $doc = $document;
        }

        $documents = $this->getEntityManager()->getRepository($this->effectiveClass)->findBy([
           'fileName' => $doc->getFileName()
        ]);

        foreach( $documents as $d ){
            $d->setStatus(AbstractVersionnedDocument::STATUS_DELETE);
        }
        $this->getEntityManager()->flush();
    }

    public function performRequest(Request $request, $replaceName, \Closure $onComplete, \Closure $hydrate=null){
        $datas = [
            "error" => null
        ];
        // Traitement des données envoyées
        if( $request->isPost() ){
            $datas = $request->getPost()->toArray();
            $datas["error"] = null;
            $file = $request->getFiles('file');
            if( !$file ){
                $lastError = error_get_last();
                $error = "Erreur inconnue";
                if( is_array($lastError) && array_key_exists('message', $lastError) ){
                   $error = $lastError['message'];
                }
                throw new OscarException(sprintf(_('Fichier incorrect : %s'), $error));
            }

            if( $file['error'] != 0 ){
                $errors = [
                    UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille autorisée par le serveur',
                    UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille autorisée par le formulaire',
                    UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé.",
                    UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé.",
                    UPLOAD_ERR_NO_TMP_DIR => "Le dossier temporaire est manquant.",
                    UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                    UPLOAD_ERR_EXTENSION => "Envoi interrompu pour une extension PHP, laquelle ? On n'sait pas trop pour le coup.",
                ];
                $datas['error'] = $errors[$file['error']];
            } else {
                if( $file['size'] <= 0 ){
                    $datas['error'] = "Votre fichier a un poids nul, curieux...";
                } else {
                    $original = $file['tmp_name'];
                    $fileMime = $this->getMime($original);
                    if( in_array($fileMime, ['application/octet-stream; charset=binary', 'application/vnd.ms-office; charset=binary'] )){
                        $fileMime = $file['type'];
                    }

                    $fileExtension = $this->checkMime($fileMime);
                    $fileName = $replaceName ? $replaceName : $file['name'];
                    $fileSize = $file['size'];

                    if( $fileExtension === false ){
                        $datas['error'] = sprintf("Le fichier '%s' est un type de fichier %s (%s) non-supporté dans Oscar.", $fileName, $fileExtension, $fileMime);
                    } else {
                        /** @var AbstractVersionnedDocument $document */
                        $document = new $this->effectiveClass();
                        $document->setVersion(1)
                            ->setDateUpdoad(new \DateTime())
                            ->setFileName($fileName)
                            ->setFileSize($fileSize)
                            ->setFileTypeMime($fileMime)
                            ->setInformation($datas['informations'])
                            ->setPerson($this->getCurrentPerson());
                        if( $hydrate ){
                            $document = $hydrate($document, $request->getPost());
                        }
                        if ( $this->createDocument($file['tmp_name'], $document) ){
                            $onComplete($document);
                        } else {
                            $datas['error'] = "Un problème est survenu lors de la copie du document.";
                        }
                    }
                }
            }
        }
        return $datas;
    }

    /**
     * @return PhpFileExtension
     */
    protected function getExtensionManager()
    {
        static $manager;
        if( $manager === null ){
            $manager = new PhpFileExtension();
            (new DocumentDictionary())->loadExtensions($manager);
            (new ImageDictonary())->loadExtensions($manager);
            (new OfficeDocumentDictonary())->loadExtensions($manager);
            (new ArchiveDictonary())->loadExtensions($manager);

            $manager->addExtension('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'docx')
                ->addExtension('application/vnd.ms-office', 'docx', 'Document Microsoft Office')
                ->addExtension('text/plain', 'txt', 'Fichier texte')
                ->addExtension('text/csv', 'csv', 'Comma Separated Values');

        }
        return $manager;
    }

    /**
     * @param $filePath
     * @return string
     */
    public function getMime( $filePath ){
        static $mimeGetter;
        if( $mimeGetter === null ){
            $mimeGetter = new MimeProvider();
        }
        return $mimeGetter->getMimeType($filePath);
    }

    /**
     * @param $mime
     * @return bool|mixed
     */
    public function checkMime( $mime )
    {
        try {
            return $this->getExtensionManager()->getExtension($mime);
        } catch( NotFoundExtension $e ){
            return false;
        }
    }

    public function getEntityManager(){
        return $this->entityManager;
    }

    /**
     * @param $source
     * @param AbstractVersionnedDocument $doc
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */

    // TODO A supprimer après validation de sa non utilisation au sein d'Oscar à un autre endroit que dans la gestion des documents dans les activités
    public function createDocument( $source, AbstractVersionnedDocument $doc ): bool
    {
        // Récupération de la version
        $exists = $this->getEntityManager()->getRepository($this->effectiveClass)->findBy([
            'fileName' => $doc->getFileName()
        ]);
        $version = 0;
        /** @var AbstractVersionnedDocument $exist */
        foreach( $exists as $exist ){
            $version = max($version, $exist->getVersion());
        }
        $doc->setVersion($version+1);
        $realName = $doc->generatePath();
        $doc->setPath($realName);
        $directoryLocation = $this->documentHome;
        if( @move_uploaded_file($source, $directoryLocation.'/'.$realName) ){
            $this->getEntityManager()->persist($doc);
            $this->getEntityManager()->flush($doc);
            return true;
        } else {
            throw new \Exception("NOT MOVABLE");
            return false;
        }
    }

    /**
     * @param $source
     * @param AbstractVersionnedDocument $doc
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function createDocumentInTab( $source, AbstractVersionnedDocument $doc ): bool
    {
        // Récupération de la version
        $exists = $this->getEntityManager()->getRepository($this->effectiveClass)->findBy(
            [
                'fileName' => $doc->getFileName()
            ]);
        $version = 0;
        /** @var AbstractVersionnedDocument $exist */
        foreach( $exists as $exist ){
            $version = max($version, $exist->getVersion());
        }
        $doc->setVersion($version+1);

        // Init nom de base
        $realName = $doc->generateName();
        $directoryLocation = $this->documentHome;
        $folder = $directoryLocation;
        $doc->setPath($realName);
        if(@move_uploaded_file($source, $folder.'/'.$realName)){
            $this->getEntityManager()->persist($doc);
            $this->getEntityManager()->flush($doc);
            return true;
        } else {
            throw new \Exception("Document non déplaçable -> NOT MOVABLE");
            //return false;
        }
    }


    /**
     * Génère le chemin complet pour le dépôt de document
     * Si le répertoire n'existe il est créé
     *
     * @return string
     * @throws \Exception
     */
    private function createFolder(string $directoryLocation, ?string $tab){
        // Répertoire private
        if ($tab === "private"){
            $folder = $directoryLocation.$tab;
        }elseif (is_null($tab) || trim($tab) === ""){
            // Anciens documents
            $folder = $directoryLocation;
        }else{
            // Nouvelle gestion des répertoires
            $folder = $directoryLocation."tab_".$tab;
        }

        if (!is_dir($folder)){
            if (!mkdir($folder, 0777, true)) {
                throw new \Exception("Impossible de créer le répertoire pour réceptionner le document !" );
            }
        }
        return $folder;
    }

    /**
     * Retourne la liste des types de documents
     *
     * @return TypeDocument[]
     */
    public function getContractDocumentTypes(): array
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
    }

    /**
     * Retourne une entité Type document via son id
     *
     * @param $idDocumentType
     * @return object
     */
    public function getContractDocumentType( $idDocumentType ): object
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->find($idDocumentType);
    }

    /**
     * Retourne l'onglet document entity byId
     *
     * @param string $idTabDocument
     * @return TabDocument|null
     */
    public function getContractTabDocument(string $idTabDocument):?TabDocument
    {
        return $this->getEntityManager()->getRepository(TabDocument::class)->find($idTabDocument);
    }

    /**
     * @param $id
     * @return QueryBuilder
     */
    public function getDocument( $id ): QueryBuilder
    {
        return $this->baseQuery()
            ->where('d.id = :id')
            ->setParameter('id', $id);
    }

    /**
     * @return QueryBuilder
     */
    protected function baseQuery(): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d, p')
            ->from($this->effectiveClass, 'd')
            ->leftJoin('d.person', 'p');
//            ->leftJoin('d.grant', 'g');
    }

    /**
     * @return string
     */
    public function getEffectiveClass(): string
    {
        return $this->effectiveClass;
    }


}
