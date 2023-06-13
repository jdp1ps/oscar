<?php


namespace Oscar\Strategy\Upload;


use Exception;
use Oscar\Entity\AbstractVersionnedDocument;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;


class StrategyOscarUpload implements StrategyTypeInterface
{
    const SIZE_FILE                 = "size";
    const ERROR_FILE                = "error";
    const TYPE_FILE                 = "type";
    const NAME_FILE                 = "name";
    const ARRAY_FILE_MIME           = ['application/octet-stream; charset=binary', 'application/vnd.ms-office; charset=binary'];
    const DATE_DEPOSIT              = "dateDeposit";
    const PRIVATE                   = "private";
    const TAB_DOCUMENT              = "tab";
    const DATE_SEND                 = "dateSend";
    const PERSONS                   = "persons";
    const NAME_INPUT_FILE           = "file";

    private $etat;

    private $document;
    private $datas;

    public function __construct()
    {
        $this->etat = false;
        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function uploadDocument(): void
    {
        // Traitement des données envoyées utilisation System file Oscar interne à Oscar donc pas de Ged générique ici
        $this->datas = $this->document->getRequest()->getPost()->toArray();
        $this->datas[self::ERROR_FILE] = null;
        $file = $this->document->getRequest()->getFiles(self::NAME_INPUT_FILE);

        // Permet de détecter le dépassement des données postées (post_max_size)
        $lastError = error_get_last();
        if( $lastError ){
            $this->datas[self::ERROR_FILE] = array_key_exists('message', $lastError) ? $lastError['message'] : "Erreur inattendue";
            return;
        }

        if( !$file ){
            $error = "Beaucoup de chose restent inexpliquées, cette erreur en fait partie...";
            if( is_array($lastError) && array_key_exists('message', $lastError) ){
                $error = $lastError['message'];
            }
            $this->datas[self::ERROR_FILE] = sprintf(_('Erreur : %s'), $error);
        }

        if( $file[self::ERROR_FILE] != 0 ){
            // Erreurs dans le fichier téléversé exemple : taille trop grande etc...
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille autorisée par le serveur ('.ini_get('upload_max_filesize').')',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille autorisée par le formulaire',
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé.",
                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléversé (vérifier que vous avez bien selectionné un fichier).",
                UPLOAD_ERR_NO_TMP_DIR => "Le dossier temporaire est manquant.",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                UPLOAD_ERR_EXTENSION => "Envoi interrompu par une extension PHP, laquelle ? On n'sait pas trop pour le coup.",
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

                $fileName = $this->getDocument()->getDocReplaced() ?
                    $this->getDocument()->getDocReplaced():
                    substr(md5(date('y-m-d H:i:s')), 0, 4) . "_" .
                    $file[self::NAME_FILE];

                $fileSize = $file[self::SIZE_FILE];

                if(false === $fileExtension){
                    $this->datas[self::ERROR_FILE] = sprintf("Le fichier '%s' est un type de fichier %s (%s) non-supporté dans Oscar.", $fileName, $fileExtension, $fileMime);
                } else {
                    /** @var AbstractVersionnedDocument $document */
                    $nameClass = $this->document->getDocumentService()->getEffectiveClass();
                    /** @var ContractDocument $document */
                    $document = new $nameClass;

                    $document
                        ->setVersion(1)
                        ->setDateUpdoad(new \DateTime())
                        ->setFileName($fileName)
                        ->setFileSize($fileSize)
                        ->setFileTypeMime($fileMime)
                        ->setInformation($this->datas['informations'])
                        ->setPerson($this->getDocument()->getOscarUserContext()->getCurrentPerson())
                        ->setTypeDocument($this->getDocument()->getDocumentService()->getContractDocumentType($this->datas[self::TYPE_FILE]))
                        ->setGrant($this->getDocument()->getActivity())
                        ->setDateDeposit($this->datas[self::DATE_DEPOSIT] ? new \DateTime($this->datas[self::DATE_DEPOSIT]):null)
                        ->setDateSend($this->datas[SELF::DATE_SEND] ? new \DateTime($this->datas[self::DATE_SEND]):null);
                    $uploaderPerson = $this->getDocument()->getOscarUserContext()->getCurrentPerson();

                    $isPrivate = boolval($this->datas[self::PRIVATE]);

                    try {
                        $tabId = $this->datas[self::TAB_DOCUMENT];
                        $tab = $this->getDocument()->getDocumentService()->getContractTabDocument($tabId);
                        $document->setTabDocument($tab);
                    } catch (Exception $e) {
                        // todo Transmettre le Logger ici
                    }

                    // Si le document téléversé n'est pas notifié comme privé alors ajout du tab (onglet) sélectionné
                    if (false === $isPrivate){
                            $document
                                ->setPrivate(false)
                                ->addPerson($uploaderPerson);
                        }else{
                            $document ->setPrivate(true);
                            // Traitement des datas personnes si personnes associées à la consultation du document contexte métier document privé
                            if (!is_null($this->datas[self::PERSONS]) && trim($this->datas[self::PERSONS]) !=""){
                                $personsIds = explode("," , $this->datas[self::PERSONS]);
                                foreach ($personsIds as $id){
                                    $person = $this->getDocument()->getDocumentService()->getEntityManager()->getRepository(Person::class)->find($id);
                                    $document->addPerson($person);
                                }
                                $document->addPerson($uploaderPerson);
                            }else{
                                $document->addPerson($uploaderPerson);
                            }
                    }
                    $this->etat = true;
                    $this->datas ["activityId" ] = $this->getDocument()->getActivity()->getId();
                    //if ( $this->getDocument()->getDocumentService()->createDocument($file['tmp_name'], $document) ){
                    if ( $this->getDocument()->getDocumentService()->createDocumentInTab($file['tmp_name'], $document) ){
                            $this->getDocument()->getNotificationService()->generateActivityDocumentUploaded($document);
                            $this->getDocument()->getActivityLogService()->addUserInfo(
                                sprintf("a déposé le document '%s' dans l'activité %s", $document->getFileName(), $this->getDocument()->getActivity()->log()),
                                'Activity', $this->getDocument()->getActivity()->getId()
                            );
                            $this->datas ["activityId" ] = $this->getDocument()->getActivity()->getId();
                            $this->etat = true;
                    } else {
                        $this->etat = false;
                        $this->datas[self::ERROR_FILE] = "Un problème est survenu lors de la copie du document.";
                    }
                }
            }
        }
    }

    /**
     * @param TypeDocumentInterface $document
     */
    public function setDocument(TypeDocumentInterface $document): void
    {
        $this->document = $document;
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
    public function getEtat(): bool
    {
        return $this->etat;
    }

    /**
     * @param bool $etat
     */
    public function setEtat(bool $etat): void
    {
        $this->etat = $etat;
    }

}
