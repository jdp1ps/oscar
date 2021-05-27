<?php


namespace Oscar\Utils;


use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ContractDocument;
use Oscar\Exception\OscarException;
use Oscar\Factory\ActivityPcruInfoFromActivityFactory;
use Oscar\Service\PCRUService;
use Symfony\Component\Console\Style\SymfonyStyle;

class PCRUCvsFile
{
    private $path;
    private $datas;
    private $pcruService;
    private $logs;

    /**
     * PCRUCvsFile constructor.
     * @param $path
     */
    public function __construct(PCRUService $pcruService, $directory = null)
    {
        $this->pcruService = $pcruService;
        $this->path = $directory == null ? $pcruService->getOscarConfigurationService()->getPcruDirectoryForUpload() : $directory;
        $this->datas = [];
        $this->logs = [];
    }

    /**
     * Ajoute l'entrée PCRU au cache.
     *
     * @param ActivityPcruInfos $info
     * @return $this
     */
    public function addEntry(ActivityPcruInfos $info): self
    {
        $this->datas[$info->getNumContratTutelleGestionnaire()] = $info;
        return $this;
    }

    /**
     * @return ActivityPcruInfos[]
     */
    public function getEntries(): array
    {
        return $this->datas;
    }

    /**
     * Retourne TRUE si la données PCRU est déjà référencée.
     *
     * @param ActivityPcruInfos $info
     * @return bool
     */
    public function entryExist(ActivityPcruInfos $info): bool
    {
        return array_key_exists($info->getNumContratTutelleGestionnaire(), $this->datas);
    }

    public function read(): self
    {
        return $this->readCSV()->readBDD();
    }

    /**
     * Extraction de la base de données des donnèes PCRU à envoyée.
     */
    public function readBDD(): self
    {
        $this->log("Lecture depuis la base de données");
        $infos = $this->pcruService->getActivityPCRUInfoRepository()->getInfosSendable();
        $added = 0;

        /** @var ActivityPcruInfos $info */
        foreach ($infos as $info) {
            if ($this->entryExist($info)) {
                $this->log("PASSED $info est déjà prête.");
            } else {
                $this->addEntry($info);
                $added++;
            }
        }

        $this->log(sprintf('%s nouvelle(s) entrée(s) ajoutée(s) depuis la BDD.', $added));

        return $this;
    }

    /**
     * Extraction des données à partir du fichier CSV existant.
     */
    public function readCSV(): self
    {
        $path = $this->pcruService->getOscarConfigurationService()->getPcruContratFile();

        $this->log("Lecture à partir du fichier $path");

        if (file_exists($path)) {
            if (($handle = fopen($path, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                    // On saute le première ligne
                    if ($row == 0) {
                        $row++;
                        continue;
                    }
                    // Récupération de la donnée côté Oscar à partir du numéro Oscar
                    $numOscar = $data[3];
                    $info = $this->pcruService->getInfosByNumOscar($numOscar);

                    if ($info == null) {
                        $this->log("WARNING : L'activité $numOscar n'est pas dans les données PCRU");
                    } else {
                        $this->addEntry($info);
                        $row++;
                    }
                }
                fclose($handle);
                $this->log(sprintf('%s nouvelle(s) entrée(s) ajoutée(s) depuis le CSV.', ($row - 1)));
            }
        } else {
            $this->log("Le fichier n'existe pas, il sera créé.");
        }
        return $this;
    }

    public function getLogs(): string
    {
        return implode("\n", $this->logs);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// GENERATION des FICHIERS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function generateFiles(): self
    {
        $this->readCSV()
            ->readBDD()
            ->writeContratsCsv()
            ->writeContratsPDF();

        return $this;
    }

    /**
     * écriture des données CSV.
     *
     * @return $this
     * @throws \Oscar\Exception\OscarException
     */
    public function writeContratsCsv()
    {
        $dest = $this->pcruService->getOscarConfigurationService()->getPcruContratFile();
        $handler = fopen($dest, 'w');
        fputcsv($handler, $this->getHeaders(), ';');
        foreach ($this->getData() as $data) {
            fputcsv($handler, $data, ';');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function writeContratsPDF()
    {
        $this->log("# Copie des contrats PDF dans " . $this->path);
        $returnedInfos = [];

        /** @var ActivityPcruInfos $info */
        foreach ($this->getEntries() as $info) {

            // traitement du document
            $filedest = $this->path . DIRECTORY_SEPARATOR . $info->getSignedFileName();

            /** @var ContractDocument $document */
            $document = $this->pcruService->getEntityManager()
                ->getRepository(ContractDocument::class)
                ->find($info->getDocumentId());

            $docpath = $this->pcruService->getOscarConfigurationService()->getDocumentDropLocation()
                . DIRECTORY_SEPARATOR
                . $document->getPath();

            $this->log("Ajout du document $docpath");
            file_put_contents($filedest, file_get_contents($docpath));

            $returnedInfos[] = $info;
        }

        return $this;
    }

    public function writePartenairesCSV()
    {
        // TODO
        return $this;
    }

    public function writeCSVs()
    {

    }

    public function makeZip(): ?string
    {
        $this->log("# Création d'un ZIP");
        $filename = "oscar-pcru-" . date('Ymd_His') . '.zip';
        $tmpfile = '/tmp/' . uniqid() . $filename;

        $this->log(" - Fichier temporaire : $tmpfile");

        $zip = new \ZipArchive();
        if ($zip->open($tmpfile, \ZipArchive::CREATE) !== TRUE) {
            throw new OscarException("Impossible de créer l'archive");
        }

        foreach ($this->pcruService->getUploadableFiles($this->path) as $info) {
            $this->log(" - Ajout de : " . $info['name']);
            $zip->addFile($info['path'], $info['name']);
        }

        $zip->close();

        echo file_get_contents($tmpfile);

        unlink($tmpfile);

        return null;
    }

    public function downloadZip(): void
    {
        $filename = "oscar-pcru-" . date('Ymd_His') . '.zip';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $this->makeZip();
        exit;
    }

    /**
     * Retourne les données sous le forme d'un tableau associatif.
     *
     * @return array
     */
    public function getData(): array
    {
        $datas = [];

        // Ajout des donnèes
        /** @var ActivityPcruInfos $info */
        foreach ($this->datas as $info) {
            $datas[] = $info->toArray();
        }

        return $datas;
    }


    /**
     * Retourne les en-têtes
     * @return array
     */
    public function getHeaders(): array
    {
        return array_keys(ActivityPcruInfoFromActivityFactory::getHeaders());
    }

    public function printInSymfonyConsole(SymfonyStyle $symfonyStyle): void
    {
        $symfonyStyle->table($this->getHeaders(), $this->getData());
    }

    public function debug(string $str): void
    {
        echo $str;
    }

    /**
     * Ajoute en entrée aux logs
     * @param string $str
     */
    public function log(string $str): void
    {
        $this->pcruService->logPool($str);
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Création et initalisation depuis un fichier CSV.
     *
     * @param string $path
     * @return self
     */
    public static function createFromPath(string $path, PCRUService $pcruService): PCRUCvsFile
    {
        throw new \Exception("DEPRECATED");
    }

    /**
     * @param PCRUService $PCRUService
     * @return static
     */
    public static function create(PCRUService $PCRUService): PCRUCvsFile
    {
        return new self($PCRUService);
    }
}