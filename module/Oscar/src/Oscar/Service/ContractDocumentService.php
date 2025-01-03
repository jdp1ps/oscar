<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 09:35
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Cocur\Slugify\Slugify;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
use Oscar\Entity\LogActivity;
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
use RuntimeException;
use UnicaenSignature\Service\ProcessServiceAwareTrait;
use UnicaenSignature\Service\SignatureServiceAwareTrait;
use UnicaenSignature\Exception\SignatureException;

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
        throw new RuntimeException('Not implemented');
    }

    public function getActivity(int $activityId)
    {
        try {
            return $this->getEntityManager()->getRepository(Activity::class)->find($activityId);
        } catch (\Exception $exception) {
            throw new OscarException("Impossible de charger l'activité '$activityId'");
        }
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
     * Liste des documents qu'une personne "observe".
     *
     * @param Person $person
     * @return ContractDocument[]
     */
    public function getDocumentsWithSignProcessForUser(Person $person): array
    {
        return $this->getContractDocumentRepository()->getDocumentsWithProcessByPerson($person->getEmail());
    }

    /**
     * @return PhpFileExtension
     */
    protected function getExtensionManager(): PhpFileExtension
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


    public function getPersonDocumentIds(Person $person): array
    {
        $ids = [];

        return $ids;
    }

    /**
     * @throws OscarException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws SignatureException
     */
    public function applySignature(int $documentId, int $flowId, array $flowDt): void
    {
        try {
            $signatureFlow = $this->getSignatureService()->getSignatureFlowById($flowId);
            $document = $this->getDocument($documentId);
            $activity = $document->getActivity();
        } catch (\Exception $e) {
            $msg = "Impossible de charger le processus de signature, données incomplète : " . $e->getMessage();
            $this->getLoggerService()->critical($msg);
            throw new OscarException($msg);
        }

        // écriture des informations pour la signature
        $contextShort = sprintf(
            "Activité %s, '%s' (Projet %s)",
            $activity->getOscarNum(),
            $activity->getLabel(),
            $activity->getAcronym()
        );

        $contextLong = sprintf(
            "Document %s (%s) dans l'activité Oscar %s : '%s' (Projet %s)",
            $document->getFileName(),
            $document->getTypeDocument()->getLabel(),
            $activity->getOscarNum(),
            $activity->getLabel(),
            $activity->getAcronym()
        );

        // On recrée le modèle de données du processus de signature
        // But : Le comparer aux données reçues pour valider
        $signatureFlowDatas = $this->getSignatureService()->createSignatureFlowDatasById(
            "",
            $signatureFlow->getId(),
            [
                'activity_id' => $activity->getId(),
                'context_subject' => $contextShort,
                'context_body' => $contextLong,
            ]
        )['signatureflow'];

        // On commence par ranger les données reçues
        $sortedFlowDatas = [];
        if ($flowDt) {
            foreach ($flowDt['steps'] as $step) {
                $sortedFlowDatas[$step['id']] = $step;
            }
        }

        // Configuration du Flow / Comparaison à la configuration initiale
        foreach ($signatureFlowDatas['steps'] as &$step) {
            if ($step['editable']) {
                if (array_key_exists($step['id'], $sortedFlowDatas)) {
                    $config = $sortedFlowDatas[$step['id']];
                    $recipients = [];
                    foreach ($config['recipients'] as $recipient) {
                        if ($recipient['selected']) {
                            $recipients[] = $recipient;
                        }
                    }
                    if (count($recipients) == 0) {
                        throw new OscarException(sprintf("L'étape '%s' n'a pas de destinataires", $step['label']));
                    }
                    $step['recipients'] = $recipients;

                    $observers = [];
                    foreach ($config['observers'] as $observer) {
                        if ($observer['selected']) {
                            $observers[] = $observer;
                        }
                    }

                    $step['observers'] = $observers;
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

        try {
            // Emplacement du document
            $uploadPath = $this->getOscarConfigurationService()->getDocumentRealpath($document);
            $fileName = $document->getFileName();

            // Destination du fichier
            $destination = $this->getSignatureService()->getSignatureConfigurationService()->getDocumentsLocation()
                . DIRECTORY_SEPARATOR
                . $fileName;

            // Déplacement
            if (!copy($uploadPath, $destination)) {
                $this->getLoggerService()->critical("Impossible de déplacer '$uploadPath' ver '$destination'");
                throw new OscarException(
                    "Un problème est survenu lors de la création de la procédure de signature, inpossible de copier le document"
                );
            }
        } catch (\Exception $e) {
            $path = $this->getSignatureService()->getSignatureConfigurationService()->getDocumentsLocation(false);
            $this->getLoggerService()->critical("ERROR : Fichier de signature '$path' - " . $e->getMessage());
            throw new OscarException($e->getMessage());
        }

        try {
            // Création du processus
            $process = $this->getProcessService()->createUnconfiguredProcess($fileName, $signatureFlow->getId());
            $this->getProcessService()->configureProcess($process, $signatureFlowDatas);
            $document->setProcess($process);
            $this->getEntityManager()->flush();
            $this->getProcessService()->trigger($process, true);
            $this->getActivityLogService()->addUserInfo(
                "a déclenché un circuit de signature pour '$document'",
                LogActivity::CONTEXT_ACTIVITY,
                $activity->getId()
            );
        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
        }
    }

    protected function getNextDocId(): int
    {
        return $this->getContractDocumentRepository()->getLastDocumentId();
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
        string $action
    ): void {
        $destination = $this->getOscarConfigurationService()->getDocumentDropLocation();
        $mimes = $this->getOscarConfigurationService()->getDocumentExtensions();
        $nextId = $this->getNextDocId();

        try {
            $originalFilename = $file['name'];
            $renameOriginal = "$nextId-" . $originalFilename;
            $type = $file['type'];

            $slugify = new Slugify();
            $renamed = sprintf(
                "oscar-%s-%s-%s-%s",
                $activity->getId(),
                1,
                $nextId,
                $slugify->slugify($originalFilename)
            );

            $this->getLoggerService()->info(
                "[document:$action] Upload du fichier '$originalFilename' vers '$destination/$renamed [$type]"
            );

            // upload
            $uploader = new FileUploadStandard();
            $uploader->setDestination($destination)
                ->setFilename($renamed)
                ->setMimesAllowed($mimes);
            $uploader->updoad($file);
            $this->getLoggerService()->info("[document:$action] Upload ok");
        } catch (\Exception $e) {
            $this->getLoggerService()->critical("[document:$action] ERROR : " . $e->getMessage());
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
            ->setFileName($renameOriginal)
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
    }


    public function uploadContractDocumentNewVersion(
        array $file,
        ContractDocument $document,
        \DateTime|null $dateDeposit,
        \DateTime|null $dateSend,
        string $description,
        ?Person $sender
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
        $nextId = $this->getNextDocId();
        try {
            $originalFilename = $file['name'];
            $type = $file['type'];

            $slugify = new Slugify();
            $renamed = sprintf(
                "oscar-%s-%s-%s-%s",
                $activity->getId(),
                $document->getVersion() + 1,
                $nextId,
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
        $newDocument = new ContractDocument();
        $this->getEntityManager()->persist($newDocument);


//        if ($privatePersons) {
//            $this->getLoggerService()->debug("Traitement des accès privés");
//            try {
//                /** @var PersonRepository $personRepository */
//                $personRepository = $this->getEntityManager()->getRepository(Person::class);
//                $persons = $personRepository->getPersonsByIds($privatePersons);
//                foreach ($persons as $p) {
//                    $this->getLoggerService()->debug(" - Ajout de '$p'");
//                    $document->addPerson($p);
//                }
//                $document->setPrivate(true);
//            } catch (\Exception $exception) {
//                $this->getLoggerService()->critical("Create activity document error : " . $exception->getMessage());
//                throw new OscarException("Error lors du chargement des personnes");
//            }
//        }

        $filePath = $uploader->getUploadPath();
        $fileSize = filesize($filePath);
        $fileName = $uploader->getUploadName();
        $fileMime = $uploader->getMime();


        $newDocument
            ->setVersion(1)
            ->setDateUpdoad(new \DateTime())
            ->setFileName($document->getFileName())
            ->setPath($fileName)
            ->setLocation(ContractDocument::LOCATION_LOCAL_FILE)
            ->setFileTypeMime($fileMime)
            ->setFileSize($fileSize)
            ->setPerson($sender)
            ->setTabDocument($document->getTabDocument())
            ->setTypeDocument($document->getTypeDocument())
            ->setVersion($document->getVersion() + 1)
            ->setGrant($activity)
            ->setInformation($description)
            ->setDateDeposit($dateDeposit)
            ->setDateSend($dateSend);

        $this->getEntityManager()->flush();
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

    /**
     * Suppression d'un processus de signature sur le document.
     *
     * @param ContractDocument $contractDocument
     * @return void
     * @throws OscarException
     */
    public function deleteProcess(ContractDocument $contractDocument): void
    {
        $this->getLoggerService()->info("Suppression du processus pour le document '$contractDocument'");
        try {
            if (!$contractDocument->getProcess()) {
                throw new OscarException("Ce document n'a pas de processus");
            }
            $process = $contractDocument->getProcess();
            $contractDocument->setProcess(null);
            $this->getEntityManager()->flush($contractDocument);
            $this->getProcessService()->deleteProcess($process);
            $this->getActivityLogService()->addUserInfo(
                "a annulé le circuit de signature pour '$contractDocument'",
                LogActivity::CONTEXT_ACTIVITY,
                $contractDocument->getActivity()->getId()
            );
        } catch (\Exception $exception) {
            $this->getLoggerService()->critical($exception->getMessage());
            throw new OscarException("Impossible de supprimer le processus : " . $exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws OscarException
     * @throws SignatureException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @return ContractDocument[]
     */
    public function getDocuments(): array
    {
        return $this->getContractDocumentRepository()->getAllDocuments();
    }

    public function getDocumentsArray()
    {
        $documents = $this->getDocuments();
        $out = [];
        foreach ($documents as $document) {
            $out[] = $document->toJson();
        }
        return $out;
    }

    public function getDocumentsGrouped(int $page = 1, int $nbr = 10, ?array $filters = null)
    {
        return $this->getContractDocumentRepository()->getDocumentsGrouped($page, $nbr, $filters);
    }

    public function getDocumentsActivity(int $id)
    {
        return $this->getContractDocumentRepository()->getDocumentsActivity($id);
    }
}
