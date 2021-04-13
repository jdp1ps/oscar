<?php


namespace Oscar\Service;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityRepository;
use Oscar\Exception\OscarException;
use Oscar\Factory\ActivityPcruInfoFromActivityFactory;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;

class PCRUService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;

    private $pcruDepotStrategy;

    public function generateFileContentForActivity( $numOscar, $withHeader = false )
    {
        // Récupération de l'activité
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->getEntityManager()->getRepository(Activity::class);

        $activity = $activityRepository->getActivityByNumOscar($numOscar, true);

        $infos = $this->getDataFactory()->createNew($activity);

        $buffer = tmpfile();
        $tmpfile_path = stream_get_meta_data($buffer)['uri'];

        if( $withHeader == true)
            fputcsv($buffer, array_keys($this->getDataFactory()->getHeaders()), ';');

        fputcsv($buffer, $infos->toArray(), ";");

        $content = file_get_contents($tmpfile_path);

        fclose($buffer);

        return $content;
    }

    /**
     * @return ActivityPcruInfoFromActivityFactory
     */
    protected function getDataFactory() {
        static $factory;
        if( $factory === null ){
            $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
        }
        return $factory;
    }


    public function getDatasFromActivity( Activity $activity, $format = 'array' )
    {
        $factory = new ActivityPcruInfoFromActivityFactory($this->getOscarConfigurationService(), $this->getEntityManager());
        $pcruInfos = $factory->createNew($activity);
        if( $format == 'array' ){
            return $pcruInfos->toArray();
        }
        elseif ( $format == 'object' ){
            return $pcruInfos;
        }
        else {
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

    public function upload()
    {
        $co = $this->ftpConnect();

        // Mode PASSIVE
        ftp_pasv($co, true);

        // TODO Fichier à envoyer
        $file = '/tmp/uploaded_file.txt';
        if( !file_exists($file) ){
            throw new OscarException("Erreur PCRU, le fichier '$file' à envoyer n'existe pas");
        }
        $dest = 'pcru/test_oscar.txt';
        $io = fopen($file, 'r');

        // Envois des données FTP
        if ( !ftp_fput($co, $dest, $io, FTP_ASCII) ){
            $errors = error_get_last();
            ftp_close($co);
            fclose($io);
            throw new OscarException("Erreur FTP, impossible d'envoyer le fichier $file : " . $errors['message']);
        }
        ftp_close($co);
        fclose($io);
        return true;
    }

    /**
     * Génère les donnèes PCRU à partir de l'activité
     */
    public function generatePcruInfo(Activity $activity, $auto=false)
    {
        return ActivityPcruInfoFromActivityFactory::createNew($activity);
    }

    /**
     * Génération d'un fichier PCRU friendly pour l'envois des informations contractuel
     * via FTP.
     */
    public function generatePcruCsvInfoFile()
    {

    }

    /**
     * Génère un tableau de données des données PCRU de l'activité.
     */
    public function generatePcruCsvInfoFromActivity()
    {

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



}

