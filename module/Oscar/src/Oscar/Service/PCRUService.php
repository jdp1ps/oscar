<?php


namespace Oscar\Service;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPcruInfosRepository;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Organization;
use Oscar\Entity\PcruPoleCompetitivite;
use Oscar\Entity\PcruSourceFinancement;
use Oscar\Entity\PcruTypeContract;
use Oscar\Exception\OscarException;
use Oscar\Exception\OscarPCRUException;
use Oscar\Factory\ActivityPcruInfoFromActivityFactory;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\PCRUCvsFile;
use Oscar\Validator\PCRUPartnerValidator;
use Oscar\Validator\PCRUUnitValidator;
use Oscar\Validator\PCRUValidator;
use PHPUnit\Exception;
use Symfony\Component\Console\Style\SymfonyStyle;

class PCRUService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager, UseServiceContainer
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait, UseServiceContainerTrait;

    /// 0. Services et Paramètres

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService()
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    /**
     * @return ActivityPcruInfosRepository
     */
    public function getActivityPCRUInfoRepository()
    {
        return $this->getEntityManager()->getRepository(ActivityPcruInfos::class);
    }


    /**
     * / Paramètres
     * 'files_path' => __DIR__.'/../../tmp',
     * 'filename_contrats' => 'contrat.csv',
     * 'filename_partenaires' => 'partenaire.csv',
     */
    public function getDirectoryForUpload(): string
    {
        return $this->getOscarConfigurationService()->getPcruDirectoryForUpload();
    }

    /**
     * @param bool $withPath
     * @return string
     * @throws OscarException
     */
    public function getContratFile($withPath = true): string
    {
        return $this->getOscarConfigurationService()->getPcruContratFile($withPath);
    }

    /**
     * @param bool $withPath
     * @return string
     * @throws OscarException
     */
    public function getPartenaireFile($withPath = true): string
    {
        return $this->getOscarConfigurationService()->getPcruPartenaireFile($withPath);
    }

    public function getParenairesHeaders(): array
    {
        return [
            "LibelleCourt",
            "LibelleLong",
            "Siren",
            "Siret",
            "TvaIntra",
            "Duns",
            "TypeEtablissement",
            "Adresse",
            "CodePostal",
            "Ville",
            "Pays",
        ];
    }

    public function getPartenaireData(Organization $organization): array
    {
        return [
            $organization->getShortName(),
            $organization->getFullName(),
            "",
            $organization->getSiret(),
            $organization->getTvaintra(),
            $organization->getDuns(),
            "1",
            $organization->getStreet1(),
            $organization->getZipCode(),
            $organization->getCity(),
            $organization->getCountry()
        ];
    }

    public function getOrganizationByCodePCRU($codePcru): ?Organization
    {
        return $this->getEntityManager()->getRepository(Organization::class)->getOrganizationByCodePCRU($codePcru);
    }

    /////////////////////////// Gestion des étapes
    ///
    const STEP_0_NOTHING_TO_DO = 'STEP_0_NOTHING_TO_DO';
    const STEP_1_CONTRACT_CSV_READY_TO_SEND = 'STEP_1_CONTRACT_CSV_READY_TO_SEND';
    const STEP_2_CONTRACT_CSV_WAIT_PCRU_RESPONSE = 'STEP_2_CONTRACT_CSV_WAIT_PCRU_RESPONSE';
    const STEP_3_CONTRACT_CSV_ERROR_PCRU_RESPONSE = 'STEP_3_CONTRACT_CSV_ERROR_PCRU_RESPONSE';
    const STEP_4_CONTRACT_CSV_OK_PCRU_RESPONSE = 'STEP_4_CONTRACT_CSV_OK_PCRU_RESPONSE';
    const STEP_5_CONTRACT_PDF_READY_TO_SEND = 'STEP_5_CONTRACT_PDF_READY_TO_SEND';
    const STEP_6_CONTRACT_PDF_WAIT_PCRU_RESPONSE = 'STEP_6_CONTRACT_PDF_WAIT_PCRU_RESPONSE';

    public function getCurrentStep() :string
    {
        var_dump($this->getDirectoryForUpload());
        die();
    }

    public function process() :void
    {

    }

    ///

    /// 1. Récupération des donnèes PCRU

    /**
     * @param null $settings
     * @return ActivityPcruInfos[]
     */
    public function getPcruInfos($settings = null): array
    {
        return $this->getActivityPCRUInfoRepository()->findAll();
    }

    /**
     * Activation de PCRU pour une activité de recherche.
     *
     * @param Activity $activity
     * @param false $json
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function activateActivity(Activity $activity, ?ActivityPcruInfos $pcruInfos = null, $json = false): void
    {
        if (!$this->getOscarConfigurationService()->getPcruEnabled()) {
            throw new OscarException("Le module PCR n'est pas activé");
        }

        if ($pcruInfos == null) {
            $factory = new ActivityPcruInfoFromActivityFactory(
                $this->getOscarConfigurationService(),
                $this->getEntityManager()
            );
            $pcruInfos = $factory->createNew($activity);
        }

        // Contrôle des informations
        $pcruValidation = new PCRUValidator($this->getOscarConfigurationService(), $this->getEntityManager());
        $validation = $pcruValidation->validate($pcruInfos);
        if (count($pcruValidation->getErrors()) > 0) {
            throw new OscarException(
                "Impossible d'activer PCRU pour cette activité, des données sont manquantes/erronées"
            );
        }

        try {
            $pcruInfos->setStatus(ActivityPcruInfos::STATUS_SEND_READY);
            $this->getEntityManager()->persist($pcruInfos);
            $this->getEntityManager()->flush($pcruInfos);
        } catch (UniqueConstraintViolationException $e) {
            throw new OscarException("Les donnèes PCRU de cette activité existent déjà");
        }

        if ($this->isPoolOpen()) {
            $this->addToPool($pcruInfos);
        }
    }


    public function updatePcruInfos(Activity $activity, ?ActivityPcruInfos $pcruInfos = null, $json = false): void
    {
        if (!$this->getOscarConfigurationService()->getPcruEnabled()) {
            throw new OscarException("Le module PCR n'est pas activé");
        }

        if ($pcruInfos == null) {
            $factory = new ActivityPcruInfoFromActivityFactory(
                $this->getOscarConfigurationService(),
                $this->getEntityManager()
            );
            $pcruInfos = $factory->createNew($activity);
        }

        try {
            $pcruInfos->setStatus(ActivityPcruInfos::STATUS_DRAFT);
            $pcruValidation = new PCRUValidator($this->getOscarConfigurationService(), $this->getEntityManager());
            $validation = $pcruValidation->validate($pcruInfos);

            $this->getEntityManager()->persist($pcruInfos);
            $this->getEntityManager()->flush($pcruInfos);
        } catch (UniqueConstraintViolationException $e) {
            throw new OscarException("Les donnèes PCRU de cette activité existent déjà");
        } catch (\Exception $e) {
            throw new OscarException(sprintf("Une erreur est survenue lors de l'enregistrement des données PCRU : %s", $e->getMessage()));
        }
    }

    /**
     * @return bool
     */
    public function isPoolOpen(): bool
    {
        $lockfile = $this->getOscarConfigurationService()->getPcruPoolLockFile();
        return !file_exists($lockfile);
    }

    /**
     * Télécharge un aperçu des documents PCRU pour l'activité donnée.
     *
     * @param Activity $activity
     */
    public function downloadOne(Activity $activity): void
    {
        $num = $activity->getOscarNum();

        $ziptmp = "/tmp/pcru-preview-zip-$num-" . uniqid() . ".zip";
        $csvtmp = "/tmp/pcru-preview-csv-$num-" . uniqid() . ".csv";
        $pdftmp = "/tmp/pcru-preview-pdf-$num-" . uniqid() . ".pdf";
        $orgtmp = "/tmp/pcru-preview-org-$num-" . uniqid() . ".csv";

        // Récupération des données
        $pcruInfos = $this->getPcruInfosActivity($activity);


        if (!$pcruInfos) {
            $factory = new ActivityPcruInfoFromActivityFactory(
                $this->getOscarConfigurationService(),
                $this->getEntityManager()
            );
            $pcruInfos = $factory->createNew($activity);
        }

        $csvFile = new PCRUCvsFile($this);
        $csvFile->addEntry($pcruInfos);
        $csvFile->writeContratsCsv($csvtmp, $this->getEntityManager());
        $csvFile->writePartenairesCSV($orgtmp);

        file_put_contents($pdftmp, $csvFile->getDocumentSignedFromPcruInfo($pcruInfos));

        // Création de l'archive
        $zip = new \ZipArchive();
        if ($zip->open($ziptmp, \ZipArchive::CREATE) !== true) {
            throw new OscarPCRUException("Impossible de créer l'archive");
        }
        $zip->addFile($csvtmp, $this->getOscarConfigurationService()->getPcruContratFile(false));
        if (file_exists($orgtmp)) {
            $zip->addFile($orgtmp, $this->getOscarConfigurationService()->getPcruPartenaireFile(false));
        }
        $zip->addFile($pdftmp, $num . '.pdf');
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="oscar-pcru-preview-' . $num . '.zip"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($ziptmp);

        unlink($csvtmp);
        unlink($pdftmp);
        unlink($ziptmp);
        exit;
    }

    /**
     * Suppression d'un contrat en attente.
     *
     * @param int $idActivityPcruInfo
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeWaiting(int $idActivityPcruInfo): void
    {
        // Récupération du PCRUInfo et vérifier l'état
        /** @var ActivityPcruInfos $pcruInfo */
        $pcruInfo = $this->getActivityPCRUInfoRepository()->find($idActivityPcruInfo);

        $this->getLoggerService()->debug("Suppression PCRU (en attente)");

        if ($pcruInfo == null) {
            throw new OscarException("Informations PCRU introuvable (id = $idActivityPcruInfo)");
        }

        if (!$pcruInfo->isWaiting()) {
            throw new OscarException(
                "Impossible de réinitialiser des informations PCRU qui ne sont pas en attente d'envoi"
            );
        }

        // Retirer du CSV
        $csvFile = new PCRUCvsFile($this);
        $csvFile->readCSV();
        $csvFile->purgeFiles();
        $csvFile->remove($pcruInfo);
        $this->getEntityManager()->remove($pcruInfo);
        $this->getEntityManager()->flush();
        $csvFile->generateFiles();
    }

    /**
     * Ajoute une activité à la file d'attente du prochain envoi PCRU.
     *
     * @param $activityOrPcruInfos
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addToPool($activityOrPcruInfos): void
    {
        if ($this->isPoolOpen()) {
            $this->logPool("Ajout $activityOrPcruInfos à la pile d'envoi");
            // Récupération des données PCRU
            $pcruInfos = null;
            if (get_class($activityOrPcruInfos) == ActivityPcruInfos::class) {
                $pcruInfos = $activityOrPcruInfos;
            } elseif (get_class($activityOrPcruInfos) == Activity::class) {
                $pcruInfos = $this->getPcruInfosActivity($activityOrPcruInfos);
            } else {
                $this->logPool("ERROR : type de données");
                throw new OscarPCRUException(
                    "Impossible d'ajouter ces donnèes au prochain envoi : Type de données incorrect."
                );
            }

            try {
            } catch (\Exception $e) {
                $this->logPool("ERROR : " . $e->getMessage());
                throw new OscarPCRUException(
                    sprintf("Impossible de préparer %s pour l'envoi : %s", $pcruInfos, $e->getMessage())
                );
            }
        } else {
            throw new OscarPCRUException("Un envoi PCRU est déjà en cours");
        }

        if (!$pcruInfos->isSendable()) {
            $err = "Les données PCRU pour " . $pcruInfos->getNumContratTutelleGestionnaire(
                ) . " ne peuvent pas être envoyées : sont statut est " . $pcruInfos->getStatusStr();
            $this->logPool("ERROR : " . $err);
            throw new OscarPCRUException($err);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Génération des documents
        $csvFile = new PCRUCvsFile($this);
        $csvFile->readCSV();
        if (!$csvFile->entryExist($pcruInfos)) {
            $csvFile->addEntry($pcruInfos);
        }
        $csvFile->writeContratsCsv();
        $csvFile->writePartenairesCSV();
        $csvFile->writeContratsPDF();
        $pcruInfos->setStatus(ActivityPcruInfos::STATUS_FILE_READY);
        $this->getEntityManager()->flush($pcruInfos);
    }

    private $_logpool;

    public function logPool(string $message): void
    {
        if ($this->_logpool == null) {
            $this->_logpool = fopen($this->getOscarConfigurationService()->getPcruLogPoolFile(), 'w');
        }
        $msg = sprintf("%s \t%s\n", date('Y-m-d h:i:s'), $message);
        fwrite($this->_logpool, $msg);
    }

    public function __destruct()
    {
        if ($this->_logpool) {
            fclose($this->_logpool);
        }
    }

    /**
     * Retourne les informations PCRU de l'activité.
     *
     * @param Activity $activity
     * @return ActivityPcruInfos|null
     */
    public function getPcruInfosActivity(Activity $activity): ?ActivityPcruInfos
    {
        try {
            return $this->getActivityPCRUInfoRepository()->getInfoActivity($activity->getId());
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Retourne les informations PCRU à partir du numéro Oscar.
     *
     * @param $numOscar
     * @return ActivityPcruInfos
     */
    public function getInfosByNumOscar($numOscar): ?ActivityPcruInfos
    {
        return $this->getActivityPCRUInfoRepository()->findOneBy(['numContratTutelleGestionnaire' => $numOscar]);
    }

    public function resetTmpPcruInfos(Activity $activity): void
    {
        $pcruInfos = $this->getPcruInfosActivity($activity);
        if ($pcruInfos) {
            $this->getEntityManager()->remove($pcruInfos);
            $this->getEntityManager()->flush();
        }
    }

    public function getContratPath(Activity $activity): string
    {
    }

    /**
     * Retourne le type de document utilisé pour désigner les contrat définitif signé.
     *
     * @return string
     */
    public function getContractSignedType(): string
    {
    }


    /**
     * Affichage des donnèes PCRU :
     *  - Si elles existent déjà, les donnèes affichées sont celle enregistrées en base de données.
     *  - Sinon, des donnèes temporaires sont créées pour l'affichage uniquement.
     *
     * @param Activity $activity
     * @param false $json
     * @return array
     * @throws OscarException
     */
    public function getPreview(Activity $activity, $json = false): array
    {
        if (!$this->getOscarConfigurationService()->getPcruEnabled()) {
            throw new OscarException("Le module PCR n'est pas activé");
        }


        // Récupération des données
        $pcruInfos = $this->getPcruInfosActivity($activity);
        $preview = false;
        $factory = new ActivityPcruInfoFromActivityFactory(
            $this->getOscarConfigurationService(),
            $this->getEntityManager()
        );

        if (!$pcruInfos) {
            $preview = true;

            $pcruInfos = $factory->createNew($activity);
        }

        $headers = $factory->getHeaders();
        $datas = $pcruInfos->toArray($this->getEntityManager());

        $pcruValidation = new PCRUValidator($this->getOscarConfigurationService(), $this->getEntityManager());
        $validation = $pcruValidation->validate($pcruInfos);
        $documentPath = "";
        if( $pcruInfos->getDocumentId() ){
            $documentPath = $this->getDocumentPath($pcruInfos->getDocumentId());
        }

        // Contrôle des organisations
        $validatorPartner = new PCRUPartnerValidator();
        foreach ($activity->getOrganizationsWithOneRoleIn($this->getOscarConfigurationService()->getPcruPartnerRoles()) as $partner) {
            if( !$validatorPartner->isValid($partner) ){
                $msg = implode(',', $validatorPartner->getMessages());
                $pcruInfos->addError(sprintf($msg, $partner));
            }
        }

        $validatorUnit = new PCRUUnitValidator();
        $unitValid = false;

        foreach ($activity->getOrganizationsWithOneRoleIn($this->getOscarConfigurationService()->getPcruUnitRoles()) as $partner) {
            if( !$validatorUnit->isValid($partner) ){
                $msg = implode(',', $validatorUnit->getMessages());
                $pcruInfos->addWarning(sprintf($msg, $partner));
            } else {
                echo "OK";
                $unitValid = true;
            }
        }
        if( $unitValid === false ){
            $pcruInfos->addError("Aucune unité valide");
        }

        return [
            'validations' => $validation,
            'headers' => $headers,
            'datas' => $datas,
            'activity' => $json ? $activity->toArray() : $activity,
            'documentPath' => $documentPath,
            'infos' => $pcruInfos,
            'errors' => $pcruInfos->getError(),
            'warnings' => $pcruInfos->getWarnings(),
            'status' => $pcruInfos->getStatus(),
            'preview' => $preview
        ];
    }

    public function getDocumentPath($documentId): string
    {
        $baseLocation = $this->getOscarConfigurationService()->getDocumentDropLocation();
        /** @var ContractDocument $document */
        $document = $this->getEntityManager()->getRepository(ContractDocument::class)->find($documentId);
        return $baseLocation . $document->getPath();
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private $pcruDepotStrategy;

    public function generateFileContentForActivity($numOscar, $withHeader = false)
    {
        // Récupération de l'activité
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->getEntityManager()->getRepository(Activity::class);

        $activity = $activityRepository->getActivityByNumOscar($numOscar, true);

        $infos = $this->getDataFactory()->createNew($activity);

        $buffer = tmpfile();
        $tmpfile_path = stream_get_meta_data($buffer)['uri'];

        if ($withHeader == true) {
            fputcsv($buffer, array_keys($this->getDataFactory()->getHeaders()), ';');
        }

        fputcsv($buffer, $infos->toArray($this->getEntityManager()), ";");

        $content = file_get_contents($tmpfile_path);

        fclose($buffer);

        return $content;
    }

    /**
     * Récupération des partenaires éligibles PCRU.
     *
     * @param Activity $activity
     * @return array
     */
    public function getActivityPartenaires(Activity $activity): array
    {
        $out = [];
        $out[""] = "Aucun";
        /** @var ActivityOrganization $org */
        foreach ($activity->getOrganizationsDeep() as $org) {
            if ($org->getOrganization()->getCodePcru()) {
                $out[$org->getOrganization()->getCodePcru()] = $org->getOrganization()->__toString();
            }
        }

        return $out;
    }

    /**
     * Récupération des partenaires éligibles PCRU.
     *
     * @param Activity $activity
     * @return array
     */
    public function getResponsableScientifiques(Activity $activity): array
    {
        $roleStr = $this->getOscarConfigurationService()->getPcruInChargeRole();
        $out = [];
        /** @var ActivityPerson $per */
        foreach ($activity->getPersonsDeep() as $per) {
            if ($per->getRoleObj()->getRoleId() == $roleStr) {
                $out[$per->getPerson()->getFullname()] = $per->getPerson()->getFullname();
            }
        }

        return $out;
    }

    /**
     * @return ActivityPcruInfoFromActivityFactory
     */
    protected function getDataFactory()
    {
        static $factory;
        if ($factory === null) {
            $factory = new ActivityPcruInfoFromActivityFactory(
                $this->getOscarConfigurationService(),
                $this->getEntityManager()
            );
        }
        return $factory;
    }

    public function getHeaders(){
        return $this->getDataFactory()->getHeaders();
    }

    protected function getPCRUDepotStrategy()
    {
        if ($this->pcruDepotStrategy === null) {
            // Récupération de la configuration PCRU dans la configuration Oscar
        }
    }

    protected function getConfiguration()
    {
        return $this->getOscarConfigurationService()->getPcruFtpInfos();
    }

    /**
     * Connexion au serveur FTP PCRU.
     *
     * @return false|resource
     * @throws OscarPCRUException
     */
    public function getFtpAccess()
    {
        static $conn;
        if ($conn == null) {
            // Configuration FTP
            $config = $this->getConfiguration();
            $this->log("FTP access to '" . $config['host'].":".$config['port']."'");
            $this->logPool("Connexion '".$config['host'].":".$config['port']."'");
            $conn = ssh2_connect($config['host'], $config['port']);
            if ($conn == null) {
                $err = error_get_last()['message'];
                $this->logPool("Erreur : '". $err . "'");
                $this->error("Can't connect to '".$config['host']."') : $err");
                throw new \Exception("Impossible de se connecter au serveur FTP");
            } else {
                $this->log("FTP access OK");
                $this->logPool("Success");
            }
        }

        return $conn;
    }

    /**
     * Authentification au serveur FTP PCRU.
     *
     * @return false|resource
     * @throws OscarPCRUException
     */
    public function ftpConnect()
    {
        $config = $this->getConfiguration();
        $conn = $this->getFtpAccess();
        $this->log("FTP Authentification with '".$config['user']."'");
        $this->logPool("FTP Authentification '".$config['user']."' / '".$config['pass']."'");
        if (!ssh2_auth_password($conn, $config['user'], $config['pass'])) {
            $err = error_get_last();
            $this->logPool("Error : '$err'");
            $this->error("Authentification fail : '$err'" );
            throw new OscarException("Echec de l'authentification");
        } else {
            $this->log("FTP Authentification OK");
            $this->logPool("Authentification OK");
        }
        return $conn;
    }

    /**
     * Retourne la liste des fichiers "en attente".
     *
     * @param bool $ignoreLogFile
     * @return array
     * @throws OscarException
     */
    public function getUploadableFiles($ignoreLogFile = true)
    {
        $files = [];
        $path = $this->getOscarConfigurationService()->getPcruDirectoryForUpload();

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if ($entry == basename($this->getOscarConfigurationService()->getPcruLogPoolFile())) {
                    continue;
                }
                $filepath = $path . DIRECTORY_SEPARATOR . $entry;
                if (!is_dir($filepath)) {
                    $files[] = [
                        'path' => $filepath,
                        'name' => $entry
                    ];
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * Retourne TRUE si des données en attente sont disponibles.
     *
     * @return bool
     * @throws OscarException
     */
    public function hasDownload()
    {
        return file_exists($this->getOscarConfigurationService()->getPcruContratFile());
    }

    /**
     * Retourne TRUE si des données envoyées sont en attente d'un retour.
     *
     * @return bool
     */
    public function hasUploadInProgress()
    {
        $path = $this->getOscarConfigurationService()->getPcruDirectoryForUploadEffective();
        return file_exists($path);
    }

    protected function sftp_put_file($sftp_ressource, $file) :void
    {
        var_dump(is_readable($file));
        $filename = basename($file);
        $data = file_get_contents($file);
        $this->sftp_put_file_data($sftp_ressource, $filename, $data);
    }

    protected function sftp_put_file_data($sftp_ressource, string $filename, string $data ) :void
    {
        $writer = fopen('ssh2.sftp://' . intval($sftp_ressource) . '/Echanges/' . $filename, 'w');
        if( fwrite($writer, $data) ){
            $this->logPool(" - OK write '$filename'");
        } else {
            $errors = error_get_last();
            $err = 'Erreur inconnue';
            if( is_array($errors) ){
                $err = $errors['message'];
            }
            $this->logPool(" - ERROR write '$filename' : $err with '$data'");
            throw new \Exception($err);
        }
        fclose($writer);
    }

    /**
     * Chargement du fichier distant.
     *
     * @param $sftp_ressource
     * @param $filename
     * @return string
     * @throws \Exception
     */
    protected function sftp_get_file($sftp_ressource, $filename) :string
    {
        $fileURI = 'ssh2.sftp://' . intval($sftp_ressource) . '/Echanges/' . $filename;
        $this->log("GET FILE '$filename' ($fileURI)");
        $reader = @fopen($fileURI, 'r');
        if( !$reader ){
            $this->log("File not found");
            throw new \Exception("Can't open '$filename'");
        }
        if( ($content = fread($reader, filesize($fileURI))) ){
            return $content;
        } else {
            $errors = error_get_last();
            $err = 'Erreur inconnue';
            if( is_array($errors) ){
                $err = $errors['message'];
            }
            $this->error("ERROR FILE READ : '$err'");
            throw new \Exception($err);
        }
        fclose($reader);
        return "";
    }

    protected function sftp_remove_file($sftp_ressource, $filename) :string
    {
        $fileURI = 'ssh2.sftp://' . intval($sftp_ressource) . '/Echanges/' . $filename;
        $this->log("REMOVE FILE '$filename' ($fileURI)");
        if( unlink($fileURI) ){
            $this->log("REMOVE OK");
            return "";
        } else {
            $this->error("REMOVE FAIL");
        }
        return "";
    }

    const PCRU_ERRORS_JSON_FILE = 'DEPOT-CSV.ERRORS.json';


    public function checkLocalErrors() :void
    {
        $localErrors = $this->getActivityPCRUInfoRepository()->getPcruInfoActivityInError();
        /** @var ActivityPcruInfos $info */
        foreach ($localErrors as $info){
            echo " - " . $info->getStatus() . "\n";
        }
        die();
    }

    /**
     * Contrôle la présence d'un fichier d'erreur, le traite avant de le supprimer.
     *
     * @param $ftp_ressource
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkRemoteErrors($ftp_ressource) :void
    {
        $this->log("Check for remote errors...");
        try {
            $content_get = $this->sftp_get_file($ftp_ressource, self::PCRU_ERRORS_JSON_FILE);
        } catch (\Exception $e) {
            throw new \Exception("NO_ERROR");
        }

        $this->log("Remote error result found...");
        // Patch caractère vide
        $pos = strpos($content_get, '﻿');
        if( $pos === 0 ){
            $this->log("ZWNBSP patch apply");
            $content_get = substr($content_get, 3);
        }

        $json_errors = json_decode($content_get);
        if( $json_errors === null ){
            $err = json_last_error_msg();
            $this->error("PARSE JSON FAIL : $err");
            throw new \Exception("JSON ERROR '$err' dans '$content_get'");
        } else {
            foreach($json_errors->contratsWithErrors as $error){
                $activityOscarId = $error->id;
                $errors = implode($error->errors, ", ");
                $this->log("Activity mark as error '$activityOscarId' : '$errors'");
                $pcruinfos = $this->getActivityPCRUInfoRepository()->getPcruInfoActivityFromOscarId($activityOscarId);
                $pcruinfos->setErrorsRemote($errors)
                    ->setStatus(ActivityPcruInfos::STATUS_ERROR_DATA);
                $this->getEntityManager()->flush();
            }
        }
        $this->sftp_remove_file($ftp_ressource,self::PCRU_ERRORS_JSON_FILE);
        $this->sftp_remove_file($ftp_ressource,$this->getOscarConfigurationService()->getPcruContratFile());
        $this->sftp_remove_file($ftp_ressource,$this->getOscarConfigurationService()->getPcruPartenaireFile());

        throw new \Exception("JSON ERROR");
    }

    public function upload(): array
    {
        $output = [
            "message" => "",
            "errors" => [],
            "logs" => [],
        ];

        $pcruActivities = $this->getActivityPCRUInfoRepository()->getPcruInfoActivityUnDone();

        if( count($pcruActivities) == 0 ){
            $output['message'] = "Rien à faire";
            return $output;
        }

        $status_global = null;
        /** @var ActivityPcruInfos $pcruInfoActivity */
        foreach ($pcruActivities as $pcruInfoActivity) {
            if( $status_global != null ) continue;
            if( $pcruInfoActivity->getStatus() === ActivityPcruInfos::STATUS_ERROR_DATA ){
                $output['message'] = "Il y'a des erreurs dans le dernier envoi, merci de les corriger";
                return $output;
            }
            if( $pcruInfoActivity->getStatus() === ActivityPcruInfos::STATUS_SEND_PENDING ){
                $status_global = ActivityPcruInfos::STATUS_SEND_PENDING;
            }
        }

        $co = $this->ftpConnect();
        $sftp = ssh2_sftp($co);

        switch($status_global){
            case ActivityPcruInfos::STATUS_SEND_PENDING;
            // TRAITEMENT du RETOUR
                try {
                    $this->checkRemoteErrors($sftp);
                } catch (\Exception $e) {
                    if( $e->getMessage() != 'NO_ERROR' ){
                        die("Il y'a des erreurs");
                    }
                    else {

                    }
                }

        }

        return $output;


        die();

        if ($this->hasUploadInProgress()) {
            throw new OscarPCRUException("Une soumission PCRU est déjà en attente de traitement.");
        }

        $files = $this->getUploadableFiles(true);

        if (count($files) <= 1) {
            throw new OscarPCRUException("Aucune donnèes PCRU en attente.");
        }

        $remotePath = 'Echanges';

        // Lecture du fichier CSV pour tagguer les Informations PCRU à traiter
        $infos = [];
        $contractFile = $this->getOscarConfigurationService()->getPcruContratFile();
        if (!file_exists($contractFile)) {
            throw new OscarPCRUException("Erreur PCRU : Les fichiers des contrats sont absents.");
        }

        $first = true;
        if (($handle = fopen($contractFile, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                if ($first) {
                    $first = false;
                    continue;
                }
                $num = $data[3];
                $info = $this->getInfosByNumOscar($data[3]);
                if ($info->getStatus() == ActivityPcruInfos::STATUS_FILE_READY) {
                    $info->setStatus(ActivityPcruInfos::STATUS_SEND_PENDING);
                    $this->getEntityManager()->flush($info);
                    $pdf = "$num.pdf";
                    $infos[$pdf] = $info;
                } else {
                    $err = "Erreur PCRU : Le contrat " . $data[3] . " n'a pas le bon statut...";
                    $this->logPool($err);
                    $this->getLoggerService()->throwAdvancedLoggedError($err, "");
                    throw new OscarException($err);
                }
            }
            fclose($handle);
        }

        $local_file = '/tmp/PCRU_MARKER.tmp';
        $remote_file = $remotePath . DIRECTORY_SEPARATOR . 'RETOUR-PCRU.OK';
        $pcruResponseFile = $remotePath . DIRECTORY_SEPARATOR . 'CONTRACT-PCRU.csv';
        
        ////////////////////////////////////////// ETAPE 1
        $this->logPool("Envoi ETAPE 1 (Fichiers de base)");

        $fileContractLocal = $this->getOscarConfigurationService()->getPcruContratFile(true);
        //$filePartners = $this->getOscarConfigurationService()->getPcruPartenaireFile(true);

        $this->sftp_put_file($sftp, $fileContractLocal);
        //$this->sftp_put_file($sftp, $filePartners);

        // création du marqueur
        $this->sftp_put_file_data($sftp, $this->getOscarConfigurationService()->getPcruSendCsvOkFile(), "done");


    }

    /**
     * Génère les donnèes PCRU à partir de l'activité
     */
    public function generatePcruInfo(Activity $activity, $auto = false)
    {
        return ActivityPcruInfoFromActivityFactory::createNew($activity);
    }

    /**
     * Lecture du fichier PCRU
     * @param $path
     * @return PCRUCvsFile
     * @throws OscarException
     */
    public function readPcruCSV($path)
    {
        $pcruCsv = PCRUCvsFile::create($this)->setPath($path)->read();
        return $pcruCsv;
    }

    /**
     * Génération d'un fichier PCRU friendly pour l'envois des informations contractuel
     * via FTP.
     */
    public function generatePcruFiles($path = null, ?SymfonyStyle $io = null): PCRUCvsFile
    {
        // TODO Tester si des fichiers sont déjà en cours d'envoi 'CONTRAT.OK'

        $path == null ? $this->getOscarConfigurationService()->getPcruDirectoryForUpload() : $path;
        $pcruFiles = PCRUCvsFile::create($this);
        $pcruFiles->generateFiles();

        return $pcruFiles;
    }

    public function allReadyToGo()
    {
    }

    /**
     * Téléchargement d'une archive ZIP avec les fichiers en attente d'envoi.
     *
     * @throws OscarException
     */
    public function downloadPCRUSendableFile()
    {
        $files = $this->getUploadableFiles(false);

        $filename = "oscar-pcru-" . date('Ymd_His') . '.zip';
        $tmpfile = '/tmp/' . uniqid() . $filename;
        $zip = new \ZipArchive();
        if ($zip->open($tmpfile, \ZipArchive::CREATE) !== true) {
            throw new OscarException("Impossible de créer l'archive");
        }

        foreach ($files as $info) {
            $zip->addFile($info['path'], $info['name']);
        }

        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($tmpfile);
        exit;
    }

    /**
     * Retourne la liste des activités dont les données PCRU sont éligibles
     * @return ActivityPcruInfos[]
     */
    public function getActivitiesAvailable()
    {
        $pcruInfos = $this->getEntityManager()->getRepository(ActivityPcruInfos::class)->findAll();
        return $pcruInfos;
    }

    ///////////////////////////////////////////////////////////////////////////////
    ///
    /// MISE à JOUR des REFERENCIELS
    ///
    ///////////////////////////////////////////////////////////////////////////////
    public function updatePoleCompetitivite()
    {
        // Récupération du référenciel
        $fichier = $this->getOscarConfigurationService()->getConfiguration('pcru.polecompetitivite');
        if (!file_exists($fichier)) {
            throw new OscarException(
                "Le référenciel des pôles de compétitivité n'est pas disponible (fichier '$fichier' inaccessible )."
            );
        }
        $polesCnrs = json_decode(file_get_contents($fichier));

        // Récupération des Pôles présents en BDD
        $poles = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class)->findAll();
        $exist = [];
        /** @var PcruPoleCompetitivite $p */
        foreach ($poles as $p) {
            $exist[] = $p->getLabel();
        }

        // Comparaison
        foreach ($polesCnrs as $poleSource) {
            if (!in_array($poleSource, $exist)) {
                // Création du pôle manquant
                $poleC = new PcruPoleCompetitivite();
                $this->getEntityManager()->persist($poleC);
                $poleC->setLabel($poleSource);
                $this->getEntityManager()->flush($poleC);
            }
        }

        return true;
    }

    public function updateTypeContrat()
    {
        // Récupération du référenciel
        $fichier = $this->getOscarConfigurationService()->getConfiguration('pcru.contracttype');
        if (!file_exists($fichier)) {
            throw new OscarException(
                "Le référenciel des types de contrat PCRU n'est pas disponible (fichier '$fichier' inaccessible )."
            );
        }
        $referenciel = json_decode(file_get_contents($fichier));

        // Récupération des Pôles présents en BDD
        $exists = $this->getEntityManager()->getRepository(PcruTypeContract::class)->findAll();
        $exist = [];
        /** @var PcruTypeContract $p */
        foreach ($exists as $p) {
            $exist[] = $p->getLabel();
        }

        // Comparaison
        /** @var PcruTypeContract $type */
        foreach ($referenciel as $type) {
            if (!in_array($type, $exist)) {
                // Création du pôle manquant
                $newType = new PcruTypeContract();
                $this->getEntityManager()->persist($newType);
                $newType->setLabel($type);
                $this->getEntityManager()->flush($newType);
            }
        }

        return true;
    }

    public function updateSourcesFinancement()
    {
        // Récupération du référenciel
        $fichier = $this->getOscarConfigurationService()->getConfiguration('pcru.sourcefinancement');
        if (!file_exists($fichier)) {
            throw new OscarException(
                "Le référenciel des sources de financement n'est pas disponible (fichier '$fichier' inaccessible )."
            );
        }
        $sources = json_decode(file_get_contents($fichier));

        // Récupération des Pôles présents en BDD
        $sourcesFinancement = $this->getEntityManager()->getRepository(PcruSourceFinancement::class)->findAll();
        $exist = [];
        /** @var PcruSourceFinancement $s */
        foreach ($sourcesFinancement as $s) {
            $exist[] = $s->getLabel();
        }

        // Comparaison
        foreach ($sources as $source) {
            if (!in_array($source, $exist)) {
                // Création du pôle manquant
                $sourceF = new PcruSourceFinancement();
                $this->getEntityManager()->persist($sourceF);
                $sourceF->setLabel($source);
                $this->getEntityManager()->flush($sourceF);
            }
        }

        return true;
    }

    protected function log( string $msg ): void
    {
        $this->getLoggerService()->info(sprintf('[process pcru - info] %s', $msg), []);
    }

    protected function error( string $msg ): void
    {
        $this->getLoggerService()->error(sprintf('[process pcru - erro] %s', $msg), []);
    }
}

