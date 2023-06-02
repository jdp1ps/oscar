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
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\Person;
use Oscar\Entity\Activity;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ContractDocumentService implements UseOscarConfigurationService, UseEntityManager
{
    use UseOscarConfigurationServiceTrait, UseEntityManagerTrait;

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
        return $this->getOscarConfigurationService()->getDocumentDropLocation();
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
     * @return ContractDocumentRepository
     */
    protected function getContractDocumentRepository()
    {
        return $this->getEntityManager()->getRepository(ContractDocument::class);
    }

    /**
     * @param int $id
     * @param bool $throw
     * @return ContractDocument|null
     * @throws OscarException
     */
    public function getDocument( int $id, bool $throw = false )
    {
        return $this->getContractDocumentRepository()->getDocument($id, $throw);
    }
}
