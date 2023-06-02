<?php


namespace Oscar\Utils;

use Oscar\Exception\OscarException;
use Psr\Log\LoggerInterface;

class FileSystemUtils
{
    private ?LoggerInterface $logger = null;
    private bool $verbose = false;

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new FileSystemUtils();
        }
        return self::$instance;
    }

    /**
     * FileSystemUtils constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setVerbosityDebug(bool $active): self
    {
        $this->verbose = $active;
        return $this;
    }

    public function verbose(string $msg): void
    {
        if ($this->verbose) {
            echo "FSU: $msg\n";
        }
    }

    private function error(string $msg): void
    {
        if ($this->logger) {
            $this->logger->error($msg);
        }
        $this->verbose("[error] - $msg");
    }

    //////////////////////////////////////////////// Foncton de base PHP
    protected function is_writable(string $path): bool
    {
        return is_writable($path);
    }

    protected function is_readable(string $path): bool
    {
        return is_readable($path);
    }

    protected function is_file(string $path): bool
    {
        return is_file($path);
    }

    protected function is_dir(string $path): bool
    {
        return is_dir($path);
    }

    protected function file_exists(string $path): bool
    {
        return file_exists($path);
    }

    ////////////////////////////////////////////////// CHECKER

    /**
     * @param string $path
     * @throws OscarException
     */
    public function checkDirWritable(string $path): void
    {
        try {
            $this->checkIsDir($path);
            $this->checkWritable($path);
        } catch (OscarException $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @throws OscarException
     */
    public function checkDirReadable(string $path): void
    {
        try {
            $this->checkIsDir($path);
            $this->checkReadable($path);
        } catch (OscarException $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @throws OscarException
     */
    public function checkFileWritable(string $path): void
    {
        try {
            $this->checkIsFile($path);
            $this->checkWritable($path);
        } catch (OscarException $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @throws OscarException
     */
    public function checkFileReadable(string $path): void
    {
        try {
            $this->checkIsFile($path);
            $this->checkReadable($path);
        } catch (OscarException $e) {
            throw $e;
        }
    }

    /**
     * @param string $path
     * @throws OscarException
     */
    public function checkIsDir(string $path): void
    {
        $this->checkDirFileExist($path);

        if (!$this->is_dir($path)) {
            $this->error(sprintf("L'emplacement '%s' n'est pas un dossier", $path));
            throw new OscarException(sprintf("L'emplacement '%s' n'est pas un dossier", $path));
        }
    }

    public function checkIsFile(string $path): void
    {
        if (!$this->is_file($path)) {
            $this->error(sprintf("L'emplacement '%s' n'est pas un fichier", $path));
            throw new OscarException(sprintf("L'emplacement '%s' n'est pas un fichier", $path));
        }
    }

    public function checkWritable(string $path): void
    {
        if (!$this->is_writable($path)) {
            $this->error(sprintf("L'emplacement '%s' n'est pas accessible en écriture", $path));
            throw new OscarException(sprintf("L'emplacement n'est pas accessible en écriture", $path));
        }
    }

    public function checkReadable(string $path): void
    {
        if (!$this->is_readable($path)) {
            $this->error(sprintf("L'emplacement '%s' n'est pas accessible en lecture", $path));
            throw new OscarException(sprintf("L'emplacement n'est pas accessible en lecture", $path));
        }
    }

    public function checkDirFileExist(string $path): void
    {
        if (!$this->file_exists($path)) {
            $this->error(sprintf("L'emplacement '%s' n'existe pas", $path));
            throw new OscarException(sprintf("L'emplacement n'existe pas", $path));
        }
    }

    public function file_put_contents(string $path, string $content): bool
    {
        $this->verbose("...file_put_contents PROCESS '$path'");
        $infos = pathinfo($path);
        $where = $infos['dirname'];
        try {
            $this->checkDirWritable($where);
        } catch (\Exception $e) {
            $this->error(
                sprintf("file_put_contents : L'emplacement '%s' n'est pas un dossier accessible en écriture", $where)
            );
            throw new OscarException("Impossible d'enregistrer les données");
        }

        if (file_put_contents($path, $content)) {
            return true;
        }
    }

    public function rename(string $from, string $to): bool
    {
        $this->verbose("...rename PROCESS '$from' > '$to'");

        try {
            $this->checkDirFileExist($from);
        } catch (OscarException $e) {
            $this->error(sprintf("rename : L'emplacement '%s' n'existe pas", $from));
            throw new OscarException("Impossible de déplacer, l'emplacement source n'existe pas");
        }

        try {
            $this->checkDirFileExist($to);
            $this->error(sprintf("rename : L'emplacement cible '%s' existe déjà", $from));
            throw new OscarException("Impossible de déplacer, l'emplacement cible existe déjà");
        } catch (OscarException $e) {
        }

        if (rename($from, $to)) {
            $this->verbose("...rename OK");
            return true;
        } else {
            $this->error(sprintf("rename : L'emplacement cible '%s' existe déjà", $from));
            throw new OscarException("Impossible de déplacer, Erreur PHP");
        }
    }

    public function unlink(string $path): bool
    {
        $this->verbose("...unlink PROCESS '$path'");
        try {
            $this->checkDirFileExist($path);
        } catch (OscarException $e) {
            $this->error(sprintf("unlink : L'emplacement '%s' n'existe pas", $path));
            throw new OscarException("Impossible de supprimer l'emplacement, cet emplacement n'existe pas");
        }

        try {
            $this->checkIsFile($path);
        } catch (OscarException $e) {
            $this->error(sprintf("unlink : L'emplacement '%s' n'est pas un fichier", $path));
            throw new OscarException("Impossible de supprimer le ficher, ça n'est pas un fichier");
        }

        try {
            $this->checkWritable($path);
        } catch (OscarException $e) {
            $this->error(sprintf("unlink : Droits insuffisants pour supprimer le fichier '%s'", $path));
            throw new OscarException("Impossible de supprimer le fichier, authorisations insuffisantes");
        }

        if (!unlink($path)) {
            $this->error(sprintf("unlink : PHP n'est pas parvenu à supprimer l'emplacement '%s'", $path));
            throw new OscarException("PHP n'est pas parvenu à supprimer le fichier");
        }

        $this->verbose("...unlink DONE '$path'");

        return true;
    }


    public function rmdir(string $path): bool
    {
        $this->verbose("\n...rmdir PROCESS '$path'");
        try {
            $this->checkDirFileExist($path);
        } catch (OscarException $e) {
            $this->error(sprintf("rmdir : L'emplacement '%s' n'existe pas", $path));
            throw new OscarException("Impossible de supprimer l'emplacement, cet emplacement n'existe pas");
        }

        try {
            $this->checkIsDir($path);
        } catch (OscarException $e) {
            $this->error(sprintf("rmdir : L'emplacement '%s' n'est pas un dossier", $path));
            throw new OscarException("Impossible de supprimer l'emplacement, ça n'est pas un dossier");
        }

        try {
            $this->checkWritable($path);
        } catch (OscarException $e) {
            $this->error(sprintf("rmdir : Droits insuffisants pour supprimer '%s'", $path));
            throw new OscarException("Impossible de supprimer l'emplacement, authorisations insuffisantes");
        }

        if (!rmdir($path)) {
            $this->error(sprintf("rmdir : Erreur PHP pour supprimer '%s'", $path));
            throw new OscarException("PHP n'est pas parvenu à supprimer le dossier");
        }

        $this->verbose("...rmdir DONE '$path'");

        return true;
    }

    /**
     * @param string $path
     * @param bool $throw
     * @return bool
     * @throws OscarException
     */
    public function mkdir(string $path, $throw = true): bool
    {
        $this->verbose("\n...mkdir PROCESS '$path'");

        // L'emplacement existe ?
        if ($this->file_exists($path)) {
            if ($this->is_file($path)) {
                throw new OscarException("L'emplacement existe déjà et est un fichier");
            } else {
                return true;
            }
        }

        $infos = pathinfo($path);
        $where = $infos['dirname'];
        $filename = $infos['filename'];

        try {
            $this->checkDirWritable($where);
        } catch (OscarException $e) {
            throw new OscarException("Impossible de créer le dossier, droit d'écriture insuffisant");
        }

        if (mkdir($path)) {
            $this->verbose("...mkdir DONE '$path'");
            return true;
        } else {
            $this->error(
                sprintf(
                    "La fonction mkdir n'est pas parvenue à créer le dossier '%s' dans '%s'",
                    $filename,
                    $where
                )
            );
            throw new OscarException("Impossible de créer le dossier.");
        }
    }

    public function file_get_contents(string $path): string
    {
        $this->checkFileReadable($path);
        return file_get_contents($path);
    }
}