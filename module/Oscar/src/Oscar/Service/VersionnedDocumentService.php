<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:35
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Jacksay\PhpFileExtension\Dictonary\ArchiveDictonary;
use Jacksay\PhpFileExtension\Dictonary\DocumentDictionary;
use Jacksay\PhpFileExtension\Dictonary\ImageDictonary;
use Jacksay\PhpFileExtension\Dictonary\OfficeDocumentDictonary;
use Jacksay\PhpFileExtension\Exception\NotFoundExtension;
use Jacksay\PhpFileExtension\PhpFileExtension;
use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use mysql_xdevapi\Exception;
use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Entity\Activity;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Http\Request;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;


class VersionnedDocumentService {

    /** @var EntityManager EntityManager */
    private $entityManager;

    /** @var  Emplaement où sont stoqués les fichiers */
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDocumentsPublished(){
        return $this->getDocuments()->andWhere('d.status = :status')->setParameter('status', AbstractVersionnedDocument::STATUS_PUBLISH);
    }

    /**
     * Retourne tous les documents.
     *
     * @return \Doctrine\ORM\QueryBuilder
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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
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

    public function createDocument( $source, AbstractVersionnedDocument $doc )
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
     * @return TypeDocument[]
     */
    public function getContractDocumentTypes()
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
    }

    /**
     * @return TypeDocument
     */
    public function getContractDocumentType( $idDocumentType )
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->find($idDocumentType);
    }

    /**
     *
     */
    public function getDocument( $id )
    {
        return $this->baseQuery()
            ->where('d.id = :id')
            ->setParameter('id', $id);
    }

    protected function baseQuery()
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