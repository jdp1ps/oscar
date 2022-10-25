<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:35
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Jacksay\PhpFileExtension\Dictonary\ArchiveDictonary;
use Jacksay\PhpFileExtension\Dictonary\DocumentDictionary;
use Jacksay\PhpFileExtension\Dictonary\ImageDictonary;
use Jacksay\PhpFileExtension\Dictonary\OfficeDocumentDictonary;
use Jacksay\PhpFileExtension\Exception\NotFoundExtension;
use Jacksay\PhpFileExtension\PhpFileExtension;
use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Entity\Activity;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ContractDocumentService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @param Activity $grant
     * @param Person $person
     * @param string $FileName
     * @param string $information
     * @param integer|null $centaureId
     */
    public function newDocument(Activity $grant, Person $person, $FileName, $information, $centaureId=null ){
        throw new \RuntimeException('Not implemented');
    }

    /**
     *
     */
    public function getDocuments()
    {
        return $this->baseQuery()->addOrderBy('d.dateUpdoad', 'DESC');
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

    /**
     * Retourne l'emplacement où sont stoqués les documents depuis le fichier
     * de configuration local.php
     *
     * @return mixed
     */
    public function getDropLocation(){
        static $doclocation;
        if( $doclocation == null ){
            $conf = realpath($this->getServiceLocator()->get('Config')['oscar']['paths']['document_oscar']);
            if( !file_exists($conf) || !is_writable($conf) ){
                throw new OscarException("L'emplacement des documents n'est pas un dossier accessible en écriture");
            }
            $doclocation = $conf.'/';
        }
        return $doclocation;
    }

    public function createDocument( $source, ContractDocument $doc )
    {
        // Récupération de la version
        $exists = $this->getEntityManager()->getRepository(ContractDocument::class)->findBy([
            'fileName' => $doc->getFileName(),
            'grant' => $doc->getGrant(),
        ]);
        $doc->setVersion(count($exists)+1);
        $realName = $doc->generatePath();
        $doc->setPath($realName);
        $directoryLocation = $this->getDropLocation();

        if( @move_uploaded_file($source, $directoryLocation.$realName) ){
            $this->getEntityManager()->persist($doc);
            $this->getEntityManager()->flush($doc);
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return TypeDocument[]
     */
    public function getContractDocumentTypes(): array
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
    }

    /**
     * @return TabDocument[]
     */
    public function getContractTabDocuments(): array
    {
        return $this->getEntityManager()->getRepository(TabDocument::class)->findAll();
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
            ->select('d, p, g')
            ->from(ContractDocument::class, 'd')
            ->leftJoin('d.person', 'p')
            ->leftJoin('d.grant', 'g');
    }

}
