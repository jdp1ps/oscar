<?php


namespace Oscar\Strategy\Upload;


use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Exception\OscarException;


class StrategyOscarUpload implements StrategyTypeInterface
{
    const SIZE_FILE                 = "size";
    const ERROR_FILE                = "error";
    const TYPE_FILE                 = "type";
    const NAME_FILE                 = "name";
    const ARRAY_FILE_MIME           = ['application/octet-stream; charset=binary', 'application/vnd.ms-office; charset=binary'];
    const DATE_DEPOSIT              = "dateDeposit";
    const DATE_SEND                 = "dateSend";
    const NAME_INPUT_FILE           = "file";

    public static $noPostUpload     = 0;
    public static $postUpload       = 1;
    public static $errorsUpload     = 2;
    public static $succesUpload     = 3;
    private $etat;

    private $document;
    //private $replaceName;
    private $datas;

    public function __construct(TypeDocumentInterface $document)
    {
        $this->document= $document;
        $this->etat = self::$noPostUpload;
    }

    public function uploadDocument(): void
    {
        //Echo "je suis dans la stratégie GED OSCAR ! method uploadDocument";
        $this->datas = [
            "error" => null
        ];
        $this->datas ["activityId"] = null;
        // Traitement des données envoyées utilisation System file Oscar interne à Oscar donc pas de Ged générique ici
        $this->datas = $this->document->getRequest()->getPost()->toArray();
        $this->datas["error"] = null;
        $file = $this->document->getRequest()->getFiles(self::NAME_INPUT_FILE);
        //var_dump($file);
        // OK file récup
        if( !$file ){
            //die("J'ai une erreur pas de fichier !");
            $lastError = error_get_last();
            $error = "Erreur inconnue";
            if( is_array($lastError) && array_key_exists('message', $lastError) ){
                $error = $lastError['message'];
            }
            throw new OscarException(sprintf(_('Fichier incorrect : %s'), $error));
        }

        if( $file[self::ERROR_FILE] != 0 ){
            //var_dump($datas); // Errors dans le fichier uploadé genre taille trop grande etc...
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille autorisée par le serveur',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille autorisée par le formulaire',
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé.",
                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé.",
                UPLOAD_ERR_NO_TMP_DIR => "Le dossier temporaire est manquant.",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                UPLOAD_ERR_EXTENSION => "Envoi interrompu pour une extension PHP, laquelle ? On n'sait pas trop pour le coup.",
            ];
            $this->datas[self::ERROR_FILE] = $errors[$file[self::ERROR_FILE]];
        } else {
            if( $file[self::SIZE_FILE] <= 0 ){
                $this->datas[self::ERROR_FILE] = "Votre fichier a un poids nul, curieux...";
            } else {
                $original = $file['tmp_name'];
                $fileMime = $this->document->getDocumentService()->getMime($original);
                if( in_array($fileMime, self::ARRAY_FILE_MIME )){
                    $fileMime = $file[self::TYPE_FILE];
                }
                $fileExtension = $this->document->getDocumentService()->checkMime($fileMime);
                $fileName = $this->getDocument()->getDocReplaced() ? $this->replaceName : $file[self::NAME_FILE];
                $fileSize = $file[self::SIZE_FILE];

                if(false === $fileExtension){
                    $this->datas[self::ERROR_FILE] = sprintf("Le fichier '%s' est un type de fichier %s (%s) non-supporté dans Oscar.", $fileName, $fileExtension, $fileMime);
                    var_dump($fileName . "---".$fileExtension . "----". $fileMime);
                } else {
                    /** @var AbstractVersionnedDocument $document */
                    $nameClass = $this->document->getDocumentService()->getEffectiveClass();
                    $document = new $nameClass;
                    $document
                        ->setVersion(1)
                        ->setDateUpdoad(new \DateTime())
                        ->setFileName($fileName)
                        ->setFileSize($fileSize)
                        ->setFileTypeMime($fileMime)
                        ->setInformation($this->datas['informations'])

                        // Essayer d'intégrer les closures hydrate ci-dessous
                        ->setPerson($this->getDocument()->getOscarUserContext()->getCurrentPerson())
                        ->setTypeDocument($this->getDocument()->getDocumentService()->getContractDocumentType($this->datas[self::TYPE_FILE]))
                        ->setGrant($this->getDocument()->getActivity())
                        ->setDateDeposit($this->datas[self::DATE_DEPOSIT] ? new \DateTime($this->datas[self::DATE_DEPOSIT]):null)
                        ->setDateSend($this->datas[SELF::DATE_SEND] ? new \DateTime($this->datas[self::DATE_SEND]):null);
                        $this->etat = self::$succesUpload;
                        $this->datas ["activityId" ] = $this->getDocument()->getActivity()->getId();
                    if ( $this->getDocument()->getDocumentService()->createDocument($file['tmp_name'], $document) ){
                            $this->getDocument()->getNotificationService()->generateActivityDocumentUploaded($document);
                            $this->getDocument()->getActivityLogService()->addUserInfo(
                                sprintf("a déposé le document '%s' dans l'activité %s", $document->getFileName(), $this->getDocument()->getActivity()->log()),
                                'Activity', $this->getDocument()->getActivity()->getId()
                            );
                            $this->datas ["activityId" ] = $this->getDocument()->getActivity()->getId();
                            //var_dump($this->getDocument()->getActivity()->getId());
                            $this->etat = self::$succesUpload;
                            //die("here");
                    } else {
                        $this->etat = self::$errorsUpload;
                        $this->datas[self::ERROR_FILE] = "Un problème est survenu lors de la copie du document.";
                    }
                }
            }
        }
        //return $this->datas;
    }

    public function getDocument(): TypeDocumentInterface
    {
         return $this->document;
    }

    public function getDatas(): array
    {
        return $this->datas;
    }

    /**
     * @return int
     */
    public function getEtat(): int
    {
        return $this->etat;
    }

    /**
     * @param int $etat
     */
    public function setEtat(int $etat): void
    {
        $this->etat = $etat;
    }

}