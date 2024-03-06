<?php

namespace Oscar\Strategy\Upload;

class FileUploadStandard
{
    private string $filename;
    private string $destination;
    private string $extension;
    private string $mime;

    /**
     * @var string[]
     */
    private array|null $mimesAllowed;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMimesAllowed(): array|null
    {
        return $this->mimesAllowed;
    }

    /**
     * @param array|null $mimesAllowed
     * @return FileUploadStandard
     */
    public function setMimesAllowed(array|null $mimesAllowed): self
    {
        $this->mimesAllowed = $mimesAllowed;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadPath(): string
    {
        return $this->getDestination() . DIRECTORY_SEPARATOR . $this->getUploadName();
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getUploadName(): string
    {
        return $this->getFilename() . '.' . $this->getExtension();
    }

    /////////////////////////////////////////////////////////////////////////
    public function updoad(array $filedatas): self
    {
        if ($filedatas['error'] == 0) {
            $format = $filedatas['type'];
            $name_original = $filedatas['name'];
            $this->mime = $filedatas['type'];

            // Extension par défaut
            $re = '/.*\.(\w*)$/m';
            preg_match_all($re, $name_original, $matches, PREG_SET_ORDER, 0);
            $extension = $matches[0][1];

            // Contrôle du mime
            if ($this->getMimesAllowed() !== null) {
                if (!array_key_exists($filedatas['type'], $this->getMimesAllowed())) {
                    throw new \Exception("Le format de fichier '$format' n'est pas pris en charge");
                }
                else {
                    $extension = $this->getMimesAllowed()[$filedatas['type']];
                }
            }
            $this->setExtension($extension);

            if (move_uploaded_file($filedatas['tmp_name'], $this->getUploadPath())) {
                return $this;
            }
            else {
                throw new \Exception("Impossible de déplacer le fichier temporaire (contacter l'administrateur)");
            }
        }
        else {
            switch ($filedatas['error']) {
                case UPLOAD_ERR_INI_SIZE :
                    throw new \Exception(
                        "Le fichier la taille autorisée sur le serveur (" . ini_get('upload_max_filesize') . ")"
                    );
                case UPLOAD_ERR_FORM_SIZE :
                    throw new \Exception(
                        "Le fichier est trop gros"
                    );
                case UPLOAD_ERR_PARTIAL :
                    throw new \Exception(
                        "Le fichier a été tronqué pendant le transfert"
                    );
                case UPLOAD_ERR_NO_FILE :
                    throw new \Exception(
                        "Vous devez selectionner un fichier"
                    );
                case UPLOAD_ERR_NO_TMP_DIR :
                    throw new \Exception(
                        "Le dossier temporaire n'est pas disponible sur le serveur (contacter l'administrateur)"
                    );
                case UPLOAD_ERR_CANT_WRITE :
                    throw new \Exception(
                        "Impossible d'écrire le fichier sur le serveur (contacter l'administrateur)"
                    );
                case UPLOAD_ERR_EXTENSION :
                    throw new \Exception(
                        "Un extension a interrompue d'upload (contacter l'administrateur)"
                    );
                default :
                    throw new \Exception(
                        "Erreur inconnue N°'" . $filedatas['error'] . "' (contacter l'administrateur)"
                    );
            }
        }
    }
}