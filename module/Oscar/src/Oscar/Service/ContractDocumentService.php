<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:35
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Cocur\Slugify\Slugify;
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
use Oscar\Entity\Person;
use Oscar\Entity\Activity;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Strategy\Upload\FileUploadStandard;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\FileSystemUtils;
use UnicaenSignature\Service\ProcessServiceAwareTrait;
use UnicaenSignature\Service\SignatureServiceAwareTrait;

class ContractDocumentService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService,
                                         UseActivityLogService
{
    use UseOscarConfigurationServiceTrait, UseEntityManagerTrait, UseLoggerServiceTrait,
        UseActivityLogServiceTrait, SignatureServiceAwareTrait, ProcessServiceAwareTrait;

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
        }
        else {
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
     * @param array $file
     * @param Activity $activity
     * @param TabDocument $tabDocument
     * @param TypeDocument $typeDocument
     * @param Person|null $sender
     * @param array $privatePersons
     * @param \DateTime|null $dateDeposit
     * @param \DateTime|null $dateSend
     * @param string $description
     * @param bool $url
     * @return void
     * @throws OscarException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function uploadContractDocument(
        array $file,
        Activity $activity,
        TabDocument $tabDocument,
        TypeDocument $typeDocument,
        ?Person $sender,
        array $privatePersons,
        \DateTime|null $dateDeposit,
        \DateTime|null $dateSend,
        string $description,
        bool $url,
        ?array $flowDt = null,
    ): void {
        $destination = $this->getOscarConfigurationService()->getDocumentDropLocation();
        $mimes = $this->getOscarConfigurationService()->getDocumentExtensions();
        $signatureFlowDatas = null;

        $this->getLoggerService()->debug("DUMP: " . print_r(json_encode($flowDt, JSON_PRETTY_PRINT), true));

        if ($typeDocument->getSignatureFlow()) {
            $signatureFlow = $typeDocument->getSignatureFlow();

            $signatureFlowDatas = $this->getSignatureService()->createSignatureFlowDatasById(
                "",
                $signatureFlow->getId(),
                ['activity_id' => $activity->getId()]
            )['signatureflow'];

            $sortedFlowDatas = [];
            if ($flowDt) {
                foreach ($flowDt['steps'] as $step) {
                    $sortedFlowDatas[$step['id']] = $step;
                }
            }

            // Configuration du Flow
            foreach ($signatureFlowDatas['steps'] as &$step) {
                if ($step['editable']) {
                    // Customisation des destinataires
                    $this->getLoggerService()->info(
                        sprintf("Traitement de l'étape [%s]%s", $step['id'], $step['label'])
                    );
                    if (array_key_exists($step['id'], $sortedFlowDatas)) {
                        $config = $sortedFlowDatas[$step['id']];
                        $this->getLoggerService()->debug(
                            sprintf("Configuration de l'étape [%s]%s > %s", $step['id'], $step['label'], print_r($config, true))
                        );
                        $recipients = [];
                        foreach ($config['recipients'] as $recipient) {
                            $this->getLoggerService()->debug(
                                sprintf("Destinataire %s : %s", $recipient['email'], $recipient['selected'] ? 'OUI' : 'non')
                            );
                            if ($recipient['selected']) {
                                $recipients[] = $recipient;
                            }
                        }
                        if (count($recipients) == 0) {
                            throw new OscarException(sprintf("L'étape '%s' n'a pas de destinataires", $step['label']));
                        }
                        $step['recipients'] = $recipients;
                    }
                    else {
                        $this->getLoggerService()->warning('Aucune configuration reçu pour cette étape');
                    }
                }
                else {
                    $this->getLoggerService()->debug(
                        sprintf("L'étape [%s]%s n'est pas éditable", $step['id'], $step['label'])
                    );
                }
            }

            if ($signatureFlowDatas['missing_recipients']) {
                throw new OscarException("Ils manquent des destinataires pour déclencher le processus de signature");
            }
        }

        try {
            $originalFilename = $file['name'];
            $type = $file['type'];

            $slugify = new Slugify();
            $renamed = sprintf(
                "oscar-%s-%s-%s-%s",
                $activity->getId(),
                1,
                substr(uniqid("", true), 0, 4),
                $slugify->slugify($originalFilename)
            );
            $this->getLoggerService()->info(
                "Upload du fichier '$originalFilename' vers '$destination/$renamed [$type]"
            );

            // upload
            $uploader = new FileUploadStandard();
            $uploader->setDestination($destination)
                ->setFilename($renamed)
                ->setMimesAllowed($mimes);
            $uploader->updoad($file);
            $this->getLoggerService()->info("Upload ok");
        } catch (\Exception $e) {
            $this->getLoggerService()->critical("UPLOAD ERROR : " . $e->getMessage());
            throw new OscarException('Impossible de téléverser votre fichier : ' . $e->getMessage());
        }

        // Création du document
        $document = new ContractDocument();
        $this->getEntityManager()->persist($document);


        if ($privatePersons) {
            $this->getLoggerService()->debug("Traitement des accès privés");
            try {
                /** @var PersonRepository $personRepository */
                $personRepository = $this->getEntityManager()->getRepository(Person::class);
                $persons = $personRepository->getPersonsByIds($privatePersons);
                foreach ($persons as $p) {
                    $this->getLoggerService()->debug(" - Ajout de '$p'");
                    $document->addPerson($p);
                }
                $document->setPrivate(true);
            } catch (\Exception $exception) {
                $this->getLoggerService()->critical("Create activity document error : " . $exception->getMessage());
                throw new OscarException("Error lors du chargement des personnes");
            }
        }

        $filePath = $uploader->getUploadPath();
        $fileSize = filesize($filePath);
        $fileName = $uploader->getUploadName();
        $fileMime = $uploader->getMime();


        $document
            ->setVersion(1)
            ->setDateUpdoad(new \DateTime())
            ->setFileName($fileName)
            ->setPath($fileName)
            ->setLocation(ContractDocument::LOCATION_LOCAL_FILE)
            ->setFileTypeMime($fileMime)
            ->setFileSize($fileSize)
            ->setPerson($sender)
            ->setTabDocument($tabDocument)
            ->setTypeDocument($typeDocument)
            ->setGrant($activity)
            ->setInformation($description)
            ->setDateDeposit($dateDeposit)
            ->setDateSend($dateSend);

        $this->getEntityManager()->flush();

        if ($typeDocument->getSignatureFlow()) {
            $uploadPath = $uploader->getUploadPath();

            // déplacement du fichier
            $destination = $this->getSignatureService()->getSignatureConfigurationService()->getDocumentsLocation()
                . DIRECTORY_SEPARATOR
                . $fileName;
            $this->getLoggerService()->info("copy '$uploadPath' vers '$destination'");
            if (!copy($uploadPath, $destination)) {
                $this->getLoggerService()->critical("Impossible de déplacer '$uploadPath' ver '$destination'");
                throw new OscarException(
                    "Un problème est survenu lors de la création de la procédure de signature, inpossible de copier le document"
                );
            }

            // Création du processus
            $this->getLoggerService()->debug("Traitement du processus de signature pour '$uploadPath'");
            $signatureFlow = $typeDocument->getSignatureFlow();
            $process = $this->getProcessService()->createUnconfiguredProcess($fileName, $signatureFlow->getId());

            $this->getProcessService()->configureProcess($process, $signatureFlowDatas);
            $document->setProcess($process);
            $this->getEntityManager()->flush();

            $this->getProcessService()->trigger($process, true);
        }
    }


    public function uploadContractDocumentNewVersion(
        array $file,
        ContractDocument $document,
        \DateTime|null $dateDeposit,
        \DateTime|null $dateSend,
        string $description
    ): void {
        $destination = $this->getOscarConfigurationService()->getDocumentDropLocation();
        $mimes = $this->getOscarConfigurationService()->getDocumentExtensions();
        $signatureFlowDatas = null;

        if ($document->getProcess()) {
            throw new OscarException(
                "Vous ne pouvez pas uploader une nouvelle version de document s'il est engagé dans une procédure de signature."
            );
        }

        $activity = $document->getActivity();

        try {
            $originalFilename = $file['name'];
            $type = $file['type'];

            $slugify = new Slugify();
            $renamed = sprintf(
                "oscar-%s-%s-%s-%s",
                $activity->getId(),
                1,
                substr(uniqid("", true), 0, 4),
                $slugify->slugify($originalFilename)
            );
            $this->getLoggerService()->info(
                "Upload du fichier '$originalFilename' vers '$destination/$renamed [$type]"
            );

            // upload
            $uploader = new FileUploadStandard();
            $uploader->setDestination($destination)
                ->setFilename($renamed)
                ->setMimesAllowed($mimes);
            $uploader->updoad($file);
            $this->getLoggerService()->info("Upload ok");
        } catch (\Exception $e) {
            $this->getLoggerService()->critical("UPLOAD ERROR : " . $e->getMessage());
            throw new OscarException('Impossible de téléverser votre fichier : ' . $e->getMessage());
        }

        // Création du document
        $document = new ContractDocument();
        $this->getEntityManager()->persist($document);


        if ($privatePersons) {
            $this->getLoggerService()->debug("Traitement des accès privés");
            try {
                /** @var PersonRepository $personRepository */
                $personRepository = $this->getEntityManager()->getRepository(Person::class);
                $persons = $personRepository->getPersonsByIds($privatePersons);
                foreach ($persons as $p) {
                    $this->getLoggerService()->debug(" - Ajout de '$p'");
                    $document->addPerson($p);
                }
                $document->setPrivate(true);
            } catch (\Exception $exception) {
                $this->getLoggerService()->critical("Create activity document error : " . $exception->getMessage());
                throw new OscarException("Error lors du chargement des personnes");
            }
        }

        $filePath = $uploader->getUploadPath();
        $fileSize = filesize($filePath);
        $fileName = $uploader->getUploadName();
        $fileMime = $uploader->getMime();


        $document
            ->setVersion(1)
            ->setDateUpdoad(new \DateTime())
            ->setFileName($fileName)
            ->setPath($fileName)
            ->setLocation(ContractDocument::LOCATION_LOCAL_FILE)
            ->setFileTypeMime($fileMime)
            ->setFileSize($fileSize)
            ->setPerson($sender)
            ->setTabDocument($tabDocument)
            ->setTypeDocument($typeDocument)
            ->setGrant($activity)
            ->setInformation($description)
            ->setDateDeposit($dateDeposit)
            ->setDateSend($dateSend);

        $this->getEntityManager()->flush();

        if ($typeDocument->getSignatureFlow()) {
            $uploadPath = $uploader->getUploadPath();

            // déplacement du fichier
            $destination = $this->getSignatureService()->getSignatureConfigurationService()->getDocumentsLocation()
                . DIRECTORY_SEPARATOR
                . $fileName;
            $this->getLoggerService()->info("copy '$uploadPath' vers '$destination'");
            if (!copy($uploadPath, $destination)) {
                $this->getLoggerService()->critical("Impossible de déplacer '$uploadPath' ver '$destination'");
                throw new OscarException(
                    "Un problème est survenu lors de la création de la procédure de signature, inpossible de copier le document"
                );
            }

            // Création du processus
            $this->getLoggerService()->debug("Traitement du processus de signature pour '$uploadPath'");
            $signatureFlow = $typeDocument->getSignatureFlow();
            $process = $this->getProcessService()->createUnconfiguredProcess($fileName, $signatureFlow->getId());

            $this->getProcessService()->configureProcess($process, $signatureFlowDatas);
            $document->setProcess($process);
            $this->getEntityManager()->flush();

            $this->getProcessService()->trigger($process, true);
        }
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
            $documentPath = $documentLocation . DIRECTORY_SEPARATOR . $document->getPath();

            // Suppression du fichier
            try {
                FileSystemUtils::getInstance()->unlink($documentPath);
            } catch (\Exception $exception) {
                $this->getLoggerService()->error(
                    "Suppression du fichier '$documentPath' impossible : " . $exception->getMessage()
                );
            }

            $process = $document->getProcess();

            // Suppression de l'enregistrement
            try {
                $this->getEntityManager()->remove($document);
            } catch (\Exception $exception) {
                $this->getLoggerService()->error(
                    "Suppression de l'enregistrement DB du document impossible (remove) : " . $exception->getMessage()
                );
                throw $exception;
            }

            // Suppression de l'enregistrement
            if ($process) {
                try {
                    $this->getProcessService()->deleteProcess($process);
                } catch (\Exception $exception) {
                    $this->getLoggerService()->error(
                        "Suppression du processus '$process' associé au document impossible : " . $exception->getMessage(
                        )
                    );
                    throw $exception;
                }
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
                'grant'    => $doc->getGrant(),
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
        }
        else {
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

    public function processSigned(array $params): void
    {
        $process = $this->getProcessService()->getProcessById($params['id']);
        $document = $this->getContractDocumentRepository()->getDocumentByProcessId($process->getId());

        $doc_content = $this->getProcessService()->getProcessDocumentDatas($process)['datas'];
        if ($doc_content) {
            $destination = $this->getDropLocation()
                . DIRECTORY_SEPARATOR
                . $document->getPath();
            if (!file_put_contents($destination, $doc_content)) {
                $this->getLoggerService()->critical("Impossible d'envoyer les données dans le fichier");
                throw new OscarException("Impossible d'envoyer les données dans le fichier");
            }
        }
        else {
            $this->getLoggerService()->critical("Le fichier du process est vide/indisponible");
            throw new OscarException("Le fichier du process est vide/indisponible");
        }
    }
}
