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
use Laminas\EventManager\Event;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\ContractType;
use Oscar\Entity\Person;
use Oscar\Entity\Activity;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRepository;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\FileSystemUtils;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ContractDocumentService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService,
                                         UseActivityLogService
{
    use UseOscarConfigurationServiceTrait, UseEntityManagerTrait, UseLoggerServiceTrait, UseActivityLogServiceTrait;

    /**
     * @param Activity $grant
     * @param Person $person
     * @param string $FileName
     * @param string $information
     * @param integer|null $centaureId
     */
    public function newDocument(Activity $grant, Person $person, $FileName, $information, $centaureId = null)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @param Event $evt
     * @return void
     */
    public function onSignatureChange($evt): void
    {
        $idSignature = $evt->getParam('id', null);
        if ($idSignature) {
            $this->getLoggerService()->info("UPDATE triggered 'signature:status' on '$idSignature'");
        } else {
            $this->getLoggerService()->error("Can't trigger 'signature:status', no ID given");
        }
    }

    /**
     * @return PhpFileExtension
     */
    protected function getExtensionManager()
    {
        static $manager;
        if ($manager === null) {
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
    public function getMime($filePath)
    {
        static $mimeGetter;
        if ($mimeGetter === null) {
            $mimeGetter = new MimeProvider();
        }
        return $mimeGetter->getMimeType($filePath);
    }

    /**
     * @param $mime
     * @return bool|mixed
     */
    public function checkMime($mime)
    {
        try {
            return $this->getExtensionManager()->getExtension($mime);
        } catch (NotFoundExtension $e) {
            return false;
        }
    }

    /**
     * Retourne l'emplacement où sont stoqués les documents depuis le fichier
     * de configuration local.php
     *
     * @return mixed
     */
    public function getDropLocation()
    {
        return $this->getOscarConfigurationService()->getDocumentDropLocation();
    }

    /**
     * @param ContractDocument $contractDocument
     * @throws OscarException
     */
    public function deleteDocument(ContractDocument $contractDocument): void
    {
        $this->getLoggerService()->debug("# Suppression du document '$contractDocument'...");

        // Nom du document
        $documentName = $contractDocument->getFileName();
        $documentActivity = $contractDocument->getActivity();

        // Path document pour déplacement
        $documentLocation = $this->getOscarConfigurationService()->getDocumentDropLocation();
        $this->getLoggerService()->debug(" - Dossier : " . $documentLocation);

        // Récupération du document et des ces différentes versions
        $documents = $this->getContractDocumentRepository()->getDocumentsForFilenameAndActivity($contractDocument);
        $this->getLoggerService()->debug(
            " - Le documents et ces versions implique " . count($documents) . " document(s)"
        );


        if (count($documents) == 0) {
            throw new OscarException("Ce document n'existe plus");
        }

        foreach ($documents as $document) {
            $documentPath = $documentLocation . DIRECTORY_SEPARATOR . $document->generatePath();

            // Suppression du fichier
            try {
                FileSystemUtils::getInstance()->unlink($documentPath);
            } catch (\Exception $exception) {
                $this->getLoggerService()->error(
                    "Suppression du fichier '$documentPath' impossible : " . $exception->getMessage()
                );
            }

            // Suppression de l'enregistrement
            try {
                $this->getEntityManager()->remove($document);
            } catch (\Exception $exception) {
                $this->getLoggerService()->error(
                    "Suppression de l'enregistrement DB du document impossible (remove) : " . $exception->getMessage()
                );
                throw $exception;
            }

            try {
                $this->getEntityManager()->flush();
            } catch (\Exception $exception) {
                $this->getLoggerService()->error(
                    "Suppression de l'enregistrement DB du document impossible (flush) : " . $exception->getMessage()
                );
                throw $exception;
            }
        }

        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a supprimé le document '%s' dans l'activité %s.",
                $documentName,
                $documentActivity->log()
            ),
            'Activity',
            $documentActivity->getId()
        );
    }

    /**
     * @param $source
     * @param ContractDocument $doc
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createDocument($source, ContractDocument $doc)
    {
        // Récupération de la version
        $exists = $this->getEntityManager()->getRepository(ContractDocument::class)->findBy(
            [
                'fileName' => $doc->getFileName(),
                'grant' => $doc->getGrant(),
            ]
        );
        $doc->setVersion(count($exists) + 1);
        $realName = $doc->generatePath();
        $doc->setPath($realName);
        $directoryLocation = $this->getDropLocation();

        if (@move_uploaded_file($source, $directoryLocation . $realName)) {
            $this->getEntityManager()->persist($doc);
            $this->getEntityManager()->flush($doc);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $typeDocumentId
     * @param int $tabDocumentId
     */
    public function migrateDocumentsTypeToTab(int $typeDocumentId, int $tabDocumentId): void
    {
        $this->getContractDocumentRepository()->migrateUntabledDocument($typeDocumentId, $tabDocumentId);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// REPOSITORY
    public function getContractDocumentRepository(): ContractDocumentRepository
    {
        return $this->getEntityManager()->getRepository(ContractDocument::class);
    }

    /**
     * @return TypeDocument[]
     */
    public function getContractDocumentTypes(): array
    {
        return $this->getContractDocumentRepository()->getTypes();
    }

    /**
     * @return TabDocument[]
     */
    public function getContractTabDocuments(): array
    {
        return $this->getContractDocumentRepository()->getTabDocuments();
    }


    public function getContractTabDocument(int $id): ?TabDocument
    {
        return $this->getContractDocumentRepository()->getTabDocumentById($id);
    }

    /**
     * @return TypeDocument
     */
    public function getContractDocumentType(int $idDocumentType): TypeDocument
    {
        return $this->getContractDocumentRepository()->getType($idDocumentType);
    }


    /**
     * @param int $id
     * @param bool $throw
     * @return ContractDocument|null
     * @throws OscarException
     */
    public function getDocument(int $id, bool $throw = false)
    {
        return $this->getContractDocumentRepository()->getDocument($id, $throw);
    }
}
