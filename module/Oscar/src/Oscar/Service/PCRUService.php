<?php


namespace Oscar\Service;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPcruInfosRepository;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\PcruPoleCompetitivite;
use Oscar\Entity\PcruSourceFinancement;
use Oscar\Entity\PcruTypeContract;
use Oscar\Exception\OscarException;
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
    public function activateActivity(Activity $activity, $json = false): void
    {
        if (!$this->getOscarConfigurationService()->getPcruEnabled()) {
            throw new OscarException("Le module PCR n'est pas activé");
        }

        $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
        $pcruInfos = $factory->createNew($activity);
        $validation = $pcruInfos->validation();
        if (count($pcruInfos->getError()) > 0) {
            throw new OscarException("Impossible d'activer PCRU pour cette activité, des données sont manquantes/erronées");
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
        $csvtmp = "/tmp/pcru-preview-csv-$num-" . uniqid() . ".zip";
        $pdftmp = "/tmp/pcru-preview-pdf-$num-" . uniqid() . ".zip";

        // Récupération des données
        $pcruInfos = $this->getPcruInfosActivity($activity);


        if (!$pcruInfos) {
            $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
            $pcruInfos = $factory->createNew($activity);
        }

        $csvFile = new PCRUCvsFile($this);
        $csvFile->addEntry($pcruInfos);
        $csvFile->writeContratsCsv($csvtmp);
        file_put_contents($pdftmp, $csvFile->getDocumentSignedFromPcruInfo($pcruInfos));

        // Création de l'archive
        $zip = new \ZipArchive();
        if ($zip->open($ziptmp, \ZipArchive::CREATE) !== TRUE) {
            throw new OscarException("Impossible de créer l'archive");
        }
        $zip->addFile($csvtmp, 'contrats.csv');
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
                throw new OscarException("Impossible d'ajouter ces donnèes au prochain envoi : Type de données incorrect.");
            }

            try {
            } catch (\Exception $e) {
                $this->logPool("ERROR : " . $e->getMessage());
                throw new OscarException(sprintf("Impossible de préparer %s pour l'envoi : %s", $pcruInfos, $e->getMessage()));
            }
        } else {
            throw new OscarException("Un envoi PCRU est déjà en cours");
        }

        if (!$pcruInfos->isSendable()) {
            $err = "Les données PCRU pour " . $pcruInfos->getNumContratTutelleGestionnaire() . " ne peuvent pas être envoyées : sont statut est " . $pcruInfos->getStatusStr();
            $this->logPool("ERROR : " . $err);
            throw new OscarException($err);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Génération des documents
        $csvFile = new PCRUCvsFile($this);
        $csvFile->readCSV();
        if (!$csvFile->entryExist($pcruInfos)) {
            $csvFile->addEntry($pcruInfos);
        }
        $csvFile->writeContratsCsv();
        $csvFile->writeContratsPDF();

        $pcruInfos->setStatus(ActivityPcruInfos::STATUS_FILE_READY);
        $this->getEntityManager()->flush($pcruInfos);

    }

    public function logPool(string $message): void
    {
        static $logpool = null;
        if ($logpool == null) {
            $logpool = fopen($this->getOscarConfigurationService()->getPcruLogPoolFile(), 'w');
        }
        $msg = sprintf("%s \t%s\n", date('Y-m-d h:i:s'), $message);
        fwrite($logpool, $msg);
    }

    public function __destruct()
    {
        if ($logpool) {
            fclose($logpool);
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


        if (!$pcruInfos) {
            $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
            $pcruInfos = $factory->createNew($activity);
        }

        $headers = ActivityPcruInfoFromActivityFactory::getHeaders();
        $datas = $pcruInfos->toArray();
        $validation = $pcruInfos->validation();
        $documentPath = $this->getOscarConfigurationService()->getDocumentDropLocation() . '/' . $pcruInfos->getDocumentPath();

        return [
            'validations' => $validation,
            'headers' => $headers,
            'datas' => $datas,
            'activity' => $json ? $activity->toArray() : $activity,
            'documentPath' => $documentPath,
            'errors' => $pcruInfos->getError(),
            'status' => $pcruInfos->getStatus()
        ];
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

        if ($withHeader == true)
            fputcsv($buffer, array_keys($this->getDataFactory()->getHeaders()), ';');

        fputcsv($buffer, $infos->toArray(), ";");

        $content = file_get_contents($tmpfile_path);

        fclose($buffer);

        return $content;
    }

    /**
     * @return ActivityPcruInfoFromActivityFactory
     */
    protected function getDataFactory()
    {
        static $factory;
        if ($factory === null) {
            $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
        }
        return $factory;
    }


    public function getDatasFromActivity(Activity $activity, $format = 'array')
    {
        $factory = $this->getDataFactory();
        $pcruInfos = $factory->createNew($activity);
        if ($format == 'array') {
            return $pcruInfos->toArray();
        } elseif ($format == 'object') {
            return $pcruInfos;
        } else {
            throw new OscarException("Impossible de générer les données PCRU pour l'activité '$activity'");
        }

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

    public function getFtpAccess()
    {
        static $conn;
        if ($conn == null) {

            // Configuration FTP
            $config = $this->getConfiguration();

            $conn = ftp_connect($config['host'], $config['port'], $config['timeout']);
            if ($conn == null) {
                throw new OscarException("Impossible de se connecter à " . $config['host']);
            }
        }

        return $conn;
    }

    public function ftpConnect()
    {
        $config = $this->getConfiguration();
        $conn = $this->getFtpAccess();
        if (ftp_login($conn, $config['user'], $config['pass'])) {

        } else {
            throw new OscarException("Echec de l'authentification");
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

    public function upload(): void
    {
        if ($this->hasUploadInProgress()) {
            throw new OscarException("Une soumission PCRU est déjà en attente de traitement.");
        }

        $files = $this->getUploadableFiles(true);

        if (count($files) <= 1) {
            throw new OscarException("Aucune donnèes PCRU en attente.");
        }

        // Lecture du fichier CSV pour tagguer les Informations PCRU à traiter
        $infos = [];
        $contractFile = $this->getOscarConfigurationService()->getPcruContratFile();
        if (!file_exists($contractFile)) {
            throw new OscarException("Erreur PCRU : Les fichiers des contrats est absent.");
        }

        $first = true;
        if (($handle = fopen($contractFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($first) {
                    $first = false;
                    continue;
                }
                $num = $data[3];
                $info = $this->getInfosByNumOscar($data[3]);
                if ($info->getStatus() == ActivityPcruInfos::STATUS_FILE_READY) {
                    $pdf = "$num.pdf";
                    $infos[$pdf] = $info;
                } else {
                    $err = "La contrat " . $data[3] . " n'a pas le bon statut...";
                    $this->logPool($err);
                    throw new OscarException($err);
                }
            }
            fclose($handle);
        }

        $remotePath = 'pcru';
        $local_file = '/tmp/PCRU_MARKER.tmp';
        $remote_file = $remotePath . '/RETOUR-PCRU.OK';
        $pcruResponseFile = $remotePath . '/CONTRACT-PCRU.csv';

        // Fichier à déposer une fois le transfert terminé
        $marker_complete = 'DEPOT-PDF.OK';
        $marker_complete_tmp = '/tmp/' . $marker_complete;
        $marker_complete_remote = $remotePath . DIRECTORY_SEPARATOR . $marker_complete;

        $this->logPool("Connexion FTP...");
        $co = $this->ftpConnect();

        // Mode PASSIVE
        ftp_pasv($co, true);

        // Ouverture du fichier pour écriture
        $handle = fopen($local_file, 'w');

        // On récupère les fichier FTP
        $remoteFiles = ftp_nlist($co, $remotePath);

        if (in_array($remote_file, $remoteFiles)) {
            // Déjà des fichiers envoyés, on regarde si la réponse PCRU est disponible
            if (in_array($pcruResponseFile, $remoteFiles)) {
                // 1.Récupérer la réponse et la traiter
                if (ftp_fget($co, $handle, $remote_file, FTP_ASCII, 0)) {
                    $this->logPool("Ecriture dans le fichier $local_file avec succès");
                    die("TRAITER la REPONSE PCRU");
                    // 2.Supprimer les fichiers
                } else {
                    throw new OscarException("Impossible de traiter le fichier de réponse PCRU, Il y a un problème lors du téléchargement du fichier $remote_file dans $local_file");
                }
            } else {
                $err = "Un envoi PCRU est déjà en attente d'un retour";
                $this->logPool($err);
                throw new OscarException($err);
            }
        } else {
            // Envoi des fichiers
            $this->logPool("Envoi des fichiers via FTP");


            foreach ($files as $info) {

                $filename = $info['name'];
                $filepath = $info['path'];
                $this->logPool("Envoi du contrat $filename");
                $dest = $remotePath . DIRECTORY_SEPARATOR . $filename;
                $stream = fopen($filepath, 'r');

                // Envois des données FTP
                ftp_pasv($co, true);
                if (!ftp_fput($co, $dest, $stream, FTP_ASCII)) {
                    $errors = error_get_last();
                    ftp_close($co);
                    fclose($stream);
                    $err = "Erreur FTP, impossible d'envoyer le fichier $filename : " . $errors['message'];
                    $this->logPool($err);
                    throw new OscarException($err);
                }

                try {
                    if ($filename != $this->getOscarConfigurationService()->getPcruContratFile(false)) {
                        $pcruInfos = $infos[$filename];
                        if (!$pcruInfos) {
                            throw new OscarException("L'objet ActivityPcruInfo ne correspond pas pour $filename");
                        }
                        $this->logPool("Changement d'état pour " . $pcruInfos);
                        $pcruInfos->setStatus(ActivityPcruInfos::STATUS_SEND_PENDING);
                        $this->getEntityManager()->flush($pcruInfos);
                    }
                } catch (\Exception $e) {
                    $this->logPool("Erreur : " . $e->getMessage());
                }

                fclose($stream);
            }

            // Ajout du fichier marker
            $this->logPool("Ajout du marqueur");
            file_put_contents($marker_complete_tmp, "");
            $marker = fopen($marker_complete_tmp, 'r');

            ftp_pasv($co, true);
            if (!ftp_fput($co, $marker_complete_remote, $marker, FTP_BINARY)) {
                $errors = error_get_last();
                $err = "Erreur FTP, impossible d'envoyer le $marker_complete" . $errors['message'];
                $this->logPool($err);
            }
            fclose($marker);

            ftp_close($co);

            $dirWait = $this->getOscarConfigurationService()->getPcruDirectoryForUpload();
            $dirEffective = $this->getOscarConfigurationService()->getPcruDirectoryForUploadEffective();
            $this->logPool("Déplacement du dossier $dirWait vers $dirEffective");

            if (!@rename($dirWait, $dirEffective)) {
                throw new OscarException("Error lors de la création du dossier de traitement après upload");
            }
        }
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
        if ($zip->open($tmpfile, \ZipArchive::CREATE) !== TRUE) {
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
                "Le référenciel des pôles de compétitivité n'est pas disponible (fichier '$fichier' inaccessible ).");
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
                "Le référenciel des types de contrat PCRU n'est pas disponible (fichier '$fichier' inaccessible ).");
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
                "Le référenciel des sources de financement n'est pas disponible (fichier '$fichier' inaccessible ).");
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

}

