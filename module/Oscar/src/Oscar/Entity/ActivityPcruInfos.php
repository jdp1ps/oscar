<?php


namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne as OneToOne;
use Oscar\Utils\DateTimeUtils;

/**
 * Class ActivityPcruInfos
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityPcruInfosRepository")
 */
class ActivityPcruInfos
{

    const VALIDATION_VALID = 'valid';
    const VALIDATION_DISABLED = 'disabled';
    const VALIDATION_ERROR = 'error';
    const VALIDATION_EMPTY = 'empty';



    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Activity
     * @OneToOne(targetEntity="Activity", inversedBy="pcruInfo")
     */
    private $activity;

    /**
     * @var string
     * @ORM\Column(type="text", length=1000, nullable=false)
     */
    private $objet = "";

    /**
     * @var string Code labintel du contrat
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $codeUniteLabintel = "";

    /**
     * @var string Le sigle de l’unité du contrat
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $sigleUnite = "";

    /**
     * @var string Le sigle de l’unité du contrat
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $numContratTutelleGestionnaire = "";

    /**
     * @var string Nom de l'équipe/sous-unité
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private $equipe = "";

    /**
     * @var PcruTypeContract Type de contrat
     * @ORM\ManyToOne(targetEntity="PcruTypeContract")
     */
    private $typeContrat;

    /**
     * @var string
     * @ORM\Column(type="text", length=50, nullable=true)
     */
    private $acronyme = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $contratsAssocies = "";


    /**
     * @var string
     * @ORM\Column(type="text", length=50, nullable=false)
     */
    private $responsableScientifique = "";

    /**
     * @var string
     * @ORM\Column(type="text", length=50, nullable=true)
     */
    private $employeurResponsableScientifique = "";

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $cordinateurConsortium = false;

    /**
     * @var string
     * @ORM\Column(type="text", length=200, nullable=true)
     */
    private $partenaires = "";

    /**
     * @var boolean Nom du partenaire principal
     * @ORM\Column(type="text", length=50, nullable=false)
     */
    private $partenairePrincipal = "";


    /**
     * @var string Identifiant partenaire (SIRET/SIREN/DUN/TVA Intra)
     * @ORM\Column(type="string")
     */
    private $idPartenairePrincipal = "";


    /**
     * @var PcruSourceFinancement Source de financement
     * @ORM\ManyToOne(targetEntity="PcruSourceFinancement")
     */
    private $sourceFinancement;


    /**
     * @var string Identifiant partenaire (SIRET/SIREN/DUN/TVA Intra)
     * @ORM\Column(type="string", length=50)
     */
    private $lieuExecution = "";


    /**
     * @var \DateTime La date de dernière signature du contrat (jj-mm-aaaa)
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDerniereSignature;


    /**
     * @var float Durée en mois (min 0.5)
     * @ORM\Column(type="float", nullable=true)
     */
    private $duree;

    /**
     * @var \DateTime La date de début (jj-mm-aaaa)
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @var \DateTime La date de dernière signature du contrat (jj-mm-aaaa)
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var float Montant reçu (par l'unité)
     * @ORM\Column(type="float", nullable=true)
     */
    private $montantPercuUnite = 0.0;

    /**
     * @var float Coût/Frais
     * @ORM\Column(type="float", nullable=true)
     */
    private $coutTotalEtude = 0.0;

    /**
     * @var float Montant total (du contrat)
     * @ORM\Column(type="float", nullable=true)
     */
    private $montantTotal = 0.0;

    /**
     * @var boolean Validé par le pôle de compétivité
     * @ORM\Column(type="boolean")
     */
    private $validePoleCompetivite = false;

    /**
     * @var string Nom du pôle de compétitivité qui a validé le projet
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $poleCompetivite = "";

    const STATUS_PREVIEW = "preview"; // Aperçu
    const STATUS_ERROR_DATA = "error_data"; // Erreur dans les données
    const STATUS_SEND_READY = "send_ready"; // Prêt pour envoi
    const STATUS_FILE_READY = "file_wait"; // En attente d'envoi
    const STATUS_SEND_PENDING = "send_pending"; // Envoyé (attente du retour)
    const STATUS_ERROR_REMOTE = "error_remote"; // Erreur (retour PCRU)
    const STATUS_DONE = "done"; // Envoyé (OK)

    static $status_str = null;
    public static function statusStr( $status )
    {
        if( self::$status_str == null ){
            self::$status_str = [
                self::STATUS_PREVIEW => "Aperçu",
                self::STATUS_ERROR_DATA => "ERREUR (Oscar)",
                self::STATUS_SEND_READY => "Prêt pour l'envoi",
                self::STATUS_FILE_READY => "Fichier prêt (Attente du transfert)",
                self::STATUS_SEND_PENDING => "Envoyé",
                self::STATUS_ERROR_REMOTE => "ERREUR (PCRU)",
                self::STATUS_DONE => "OK",
            ];
        }
        return self::$status_str[$status];
    }

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $status = "";

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $error = [];

    /**
     * @var string Commentaire du gestionnaire de contrat
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaires = "";

    /**
     * @var boolean Programme Investissement Avenir
     * @ORM\Column(type="boolean")
     */
    private $pia = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $reference = "";

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accordCadre = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $cifre = "";

    /**
     * @var string (True/False/Indéfini)
     * @ORM\Column(type="string", length=8)
     */
    private $chaireIndustrielle = "Indéfini";

    /**
     * @var string (True/False/Indéfini)
     * @ORM\Column(type="string", length=8)
     */
    private $PresencePartenaireIndustriel = "Indéfini";

    /**
     * @var integer Identifiant du document "Contrat Signé"
     * @ORM\Column(type="integer", nullable=true)
     */
    private $documentId = null;

    /**
     * ActivityPcruInfos constructor.
     * @param string $status
     */
    public function __construct()
    {
        $this->status = self::STATUS_PREVIEW;
    }

    public function __toString()
    {
        return sprintf("[%s] %s : %s (%s)",
            $this->getNumContratTutelleGestionnaire(),
            $this->getAcronyme(),
            $this->getObjet(),
            $this->getResponsableScientifique()
        );
    }

    public function toArray() :array {
        $out = [];
        $out['Objet'] = $this->getObjet();
        $out['CodeUniteLabintel'] = $this->getCodeUniteLabintel();
        $out['SigleUnite'] = $this->getSigleUnite();
        $out['NumContratTutelleGestionnaire'] = $this->getNumContratTutelleGestionnaire();
        $out['Equipe'] = $this->getEquipe();
        $out['TypeContrat'] = $this->getTypeContrat() ? $this->getTypeContrat()->getLabel() : "Aucun";
        $out['Acronyme'] = $this->getAcronyme();
        $out['ContratsAssocies'] = $this->getContratsAssocies();
        $out['ResponsableScientifique'] = $this->getResponsableScientifique();
        $out['EmployeurResponsableScientifique'] = $this->getEmployeurResponsableScientifique();
        $out['CoordinateurConsortium'] = $this->isCordinateurConsortium() ? 'True' : 'False';
        $out['Partenaires'] = $this->getPartenaires();
        $out['PartenairePrincipal'] = "Part inconnu"; // TODO
        $out['IdPartenairePrincipal'] = $this->getIdPartenairePrincipal();
        $out['SourceFinancement'] = $this->getSourceFinancement() ? $this->getSourceFinancement()->getLabel() : "";
        $out['LieuExecution'] = $this->getLieuExecution();
        $out['DateDerniereSignature'] = DateTimeUtils::toStr($this->getDateDerniereSignature(), 'd-m-Y');
        $out['Duree'] = $this->getDuree();
        $out['DateDebut'] = DateTimeUtils::toStr($this->getDateDebut(), 'd-m-Y');
        $out['DateFin'] = DateTimeUtils::toStr($this->getDateFin(), 'd-m-Y');
        $out['MontantPercuUnite'] = $this->getMontantPercuUnite();
        $out['CoutTotalEtude'] = $this->getCoutTotalEtude();
        $out['MontantTotal'] = $this->getMontantTotal();
        $out['ValidePoleCompetivite'] = $this->isValidePoleCompetivite() ? "True" : "False";
        $out['PoleCompetivite'] = $this->getPoleCompetivite();
        $out['Commentaires'] = $this->getCommentaires();
        $out['Pia'] = $this->isPia() ? "True" : "False";
        $out['Reference'] = $this->getReference();
        $out['AccordCadre'] = $this->isAccordCadre() ? "True" : "False";
        $out['Cifre'] = $this->getCifre();
        $out['ChaireIndustrielle'] = $this->getChaireIndustrielle();
        $out['PresencePartenaireIndustriel'] = $this->getPresencePartenaireIndustriel();
        return $out;
    }

    public function validation()
    {
        $datas = $this->toArray();

        $out = [];
        $out['Objet'] = self::VALIDATION_ERROR;                             // Requis
        $out['CodeUniteLabintel'] = self::VALIDATION_ERROR;                 // Requis
        $out['SigleUnite'] = self::VALIDATION_EMPTY;
        $out['NumContratTutelleGestionnaire'] = self::VALIDATION_ERROR;     // Requis
        $out['Equipe'] = self::VALIDATION_EMPTY;
        $out['TypeContrat'] = self::VALIDATION_ERROR;
        $out['Acronyme'] = self::VALIDATION_EMPTY;
        $out['ContratsAssocies'] = self::VALIDATION_EMPTY;
        $out['ResponsableScientifique'] = self::VALIDATION_ERROR;           // Requis
        $out['EmployeurResponsableScientifique'] = self::VALIDATION_EMPTY;
        $out['CoordinateurConsortium'] = self::VALIDATION_EMPTY;
        $out['Partenaires'] = self::VALIDATION_EMPTY;
        $out['PartenairePrincipal'] = self::VALIDATION_ERROR;               // Requis
        $out['IdPartenairePrincipal'] = self::VALIDATION_EMPTY;
        $out['SourceFinancement'] = self::VALIDATION_ERROR;                 // Requis
        $out['LieuExecution'] = self::VALIDATION_ERROR;                     // Requis
        $out['DateDerniereSignature'] = self::VALIDATION_ERROR;             // Requis
        $out['Duree'] = self::VALIDATION_EMPTY;
        $out['DateDebut'] = self::VALIDATION_ERROR;                         // Requis
        $out['DateFin'] = self::VALIDATION_ERROR;                           // Requis
        $out['MontantPercuUnite'] = self::VALIDATION_ERROR;                 // Requis
        $out['CoutTotalEtude'] = self::VALIDATION_EMPTY;
        $out['MontantTotal'] = self::VALIDATION_EMPTY;
        $out['ValidePoleCompetivite'] = self::VALIDATION_EMPTY;
        $out['PoleCompetivite'] = self::VALIDATION_EMPTY;
        $out['Commentaires'] = self::VALIDATION_EMPTY;
        $out['Pia'] = self::VALIDATION_EMPTY;
        $out['Reference'] = self::VALIDATION_EMPTY;
        $out['AccordCadre'] = self::VALIDATION_EMPTY;
        $out['Cifre'] = self::VALIDATION_EMPTY;
        $out['ChaireIndustrielle'] = self::VALIDATION_EMPTY;
        $out['PresencePartenaireIndustriel'] = self::VALIDATION_EMPTY;
        $out['contract_signed'] = self::VALIDATION_ERROR;

        $disabledFields = [
            'Equipe','ContratsAssocies', 'EmployeurResponsableScientifique',
            'CoordinateurConsortium','Partenaires', 'PartenairePrincipal',
            'IdPartenairePrincipal','LieuExecution', 'Duree',
            'MontantPercuUnite','CoutTotalEtude', 'Commentaires',
            'Pia','AccordCadre', 'Cifre',
            'ChaireIndustrielle','PresencePartenaireIndustriel'
            ];

        foreach ($disabledFields as $unusedField) {
            $out[$unusedField] = self::VALIDATION_DISABLED;
        }

        if( $datas['Objet'] )
            $out['Objet'] = self::VALIDATION_VALID;

        if( $datas['CodeUniteLabintel'] )
            $out['CodeUniteLabintel'] = self::VALIDATION_VALID;

        if( $datas['SigleUnite'] )
            $out['SigleUnite'] = self::VALIDATION_VALID;

        if( $datas['NumContratTutelleGestionnaire'] )
            $out['NumContratTutelleGestionnaire'] = self::VALIDATION_VALID;

        if( $datas['NumContratTutelleGestionnaire'] )
            $out['NumContratTutelleGestionnaire'] = self::VALIDATION_VALID;

        if( $datas['TypeContrat'] )
            $out['TypeContrat'] = self::VALIDATION_VALID;

        if( $datas['Acronyme'] )
            $out['Acronyme'] = self::VALIDATION_VALID;

        if( $datas['ResponsableScientifique'] )
            $out['ResponsableScientifique'] = self::VALIDATION_VALID;

        if( $datas['EmployeurResponsableScientifique'] )
            $out['EmployeurResponsableScientifique'] = self::VALIDATION_VALID;

        if( $datas['PartenairePrincipal'] )
            $out['PartenairePrincipal'] = self::VALIDATION_VALID;

        if( $datas['SourceFinancement'] )
            $out['SourceFinancement'] = self::VALIDATION_VALID;

        if( $datas['LieuExecution'] )
            $out['LieuExecution'] = self::VALIDATION_VALID;

        if( $datas['DateDerniereSignature'] )
            $out['DateDerniereSignature'] = self::VALIDATION_VALID;

        if( $datas['DateDebut'] )
            $out['DateDebut'] = self::VALIDATION_VALID;

        if( $datas['DateFin'] )
            $out['DateFin'] = self::VALIDATION_VALID;

        if( $datas['MontantTotal'] )
            $out['MontantTotal'] = self::VALIDATION_VALID;


        if( $datas['ValidePoleCompetivite'] == true ){
            $out['ValidePoleCompetivite'] = self::VALIDATION_VALID;
            $out['PoleCompetivite'] = self::VALIDATION_ERROR;
            if( $out['PoleCompetivite'] ){
                $out['PoleCompetivite'] = self::VALIDATION_VALID;
            }
        }

        if( $datas['Reference'] )
            $out['Reference'] = self::VALIDATION_VALID;

        if( $this->getDocumentPath() ){
            $out['document_signed'] = self::VALIDATION_VALID;
        }

        foreach ($out as $champ=>$state) {
            if( $champ == 'contract_signed') continue;
            if( $state == self::VALIDATION_ERROR ){
                $this->status = self::STATUS_ERROR_DATA;
                $this->addError($this->getMessage($champ));
            }
        }

        return $out;
    }

    static private $messages;

    protected function getMessage( $champ )
    {
        if( self::$messages === null ) {
            self::$messages = [];
            self::$messages['SourceFinancement'] = "La source de financement est manquante, vous pouvez la renseigner depuis la fiche activité";
            self::$messages['DateDerniereSignature'] = "La date de signature est manquante, vous pouvez la renseigner depuis la fiche activité";
            self::$messages['CodeUniteLabintel'] = "Le code de l'unité de recherche (Labintel) est manquant, il peut être complété depuis la fiche organisation (type UMR XXXX)";
        }

        if( array_key_exists($champ, self::$messages) ){
            return self::$messages[$champ];
        } else {
            return sprintf("Le champ %s n'est pas renseigné.", $champ);
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Activity
     */
    public function getActivity(): Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity $activity
     */
    public function setActivity(Activity $activity): self
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjet(): string
    {
        return $this->objet;
    }

    /**
     * @param string $objet
     */
    public function setObjet(string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeUniteLabintel(): string
    {
        return $this->codeUniteLabintel;
    }

    /**
     * @param string $codeUniteLabintel
     */
    public function setCodeUniteLabintel(string $codeUniteLabintel): self
    {
        $this->codeUniteLabintel = $codeUniteLabintel;
        return $this;
    }

    /**
     * @return string
     */
    public function getSigleUnite(): string
    {
        return $this->sigleUnite;
    }

    /**
     * @param string $sigleUnite
     */
    public function setSigleUnite(string $sigleUnite): self
    {
        $this->sigleUnite = $sigleUnite;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumContratTutelleGestionnaire(): string
    {
        return $this->numContratTutelleGestionnaire;
    }

    public function isSendable() :bool
    {
        return $this->getStatus() == self::STATUS_SEND_READY;
    }

    /**
     * @param string $numContratTutelleGestionnaire
     */
    public function setNumContratTutelleGestionnaire(string $numContratTutelleGestionnaire): self
    {
        $this->numContratTutelleGestionnaire = $numContratTutelleGestionnaire;
        return $this;
    }

    /**
     * @return string
     */
    public function getEquipe(): string
    {
        return $this->equipe;
    }

    /**
     * @param string $equipe
     */
    public function setEquipe(string $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }

    /**
     * @return PcruTypeContract
     */
    public function getTypeContrat()
    {
        return $this->typeContrat;
    }

    /**
     * @param PcruTypeContract $typeContrat
     */
    public function setTypeContrat($typeContrat): self
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcronyme(): string
    {
        return $this->acronyme;
    }

    /**
     * @param string $acronyme
     */
    public function setAcronyme(string $acronyme): self
    {
        $this->acronyme = $acronyme;
        return $this;
    }

    /**
     * @return string
     */
    public function getContratsAssocies(): string
    {
        return $this->contratsAssocies;
    }

    /**
     * @param string $contratsAssocies
     */
    public function setContratsAssocies(string $contratsAssocies): self
    {
        $this->contratsAssocies = $contratsAssocies;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponsableScientifique(): string
    {
        return $this->responsableScientifique;
    }

    /**
     * @param string $responsableScientifique
     */
    public function setResponsableScientifique(string $responsableScientifique): self
    {
        $this->responsableScientifique = $responsableScientifique;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmployeurResponsableScientifique(): string
    {
        return $this->employeurResponsableScientifique;
    }

    /**
     * @param string $employeurResponsableScientifique
     */
    public function setEmployeurResponsableScientifique(string $employeurResponsableScientifique): self
    {
        $this->employeurResponsableScientifique = $employeurResponsableScientifique;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCordinateurConsortium(): bool
    {
        return $this->cordinateurConsortium;
    }

    /**
     * @param bool $cordinateurConsortium
     */
    public function setCordinateurConsortium(bool $cordinateurConsortium): self
    {
        $this->cordinateurConsortium = $cordinateurConsortium;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPartenaires(): string
    {
        return $this->partenaires;
    }

    /**
     * @param string $partenaires
     */
    public function setPartenaires($partenaires): self
    {
        $this->partenaires = $partenaires;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPartenairePrincipal(): bool
    {
        return $this->partenairePrincipal;
    }

    /**
     * @param bool $partenairePrincipal
     */
    public function setPartenairePrincipal(bool $partenairePrincipal): self
    {
        $this->partenairePrincipal = $partenairePrincipal;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdPartenairePrincipal(): string
    {
        return $this->idPartenairePrincipal;
    }

    /**
     * @param string $idPartenairePrincipal
     */
    public function setIdPartenairePrincipal(string $idPartenairePrincipal): self
    {
        $this->idPartenairePrincipal = $idPartenairePrincipal;
        return $this;
    }

    /**
     * @return PcruSourceFinancement | null
     */
    public function getSourceFinancement()
    {
        return $this->sourceFinancement;
    }

    /**
     * @param PcruSourceFinancement $souceFinancement
     */
    public function setSourceFinancement($souceFinancement): self
    {
        $this->sourceFinancement = $souceFinancement;
        return $this;
    }

    /**
     * @return string
     */
    public function getLieuExecution(): string
    {
        return $this->lieuExecution;
    }

    /**
     * @param string $lieuExecution
     */
    public function setLieuExecution(string $lieuExecution): self
    {
        $this->lieuExecution = $lieuExecution;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateDerniereSignature()
    {
        return $this->dateDerniereSignature;
    }

    /**
     * @param \DateTime $dateDerniereSignature
     */
    public function setDateDerniereSignature($dateDerniereSignature): self
    {
        $this->dateDerniereSignature = $dateDerniereSignature;
        return $this;
    }

    /**
     * @return float
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * @param float $duree
     */
    public function setDuree($duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * @param \DateTime $dateDebut
     */
    public function setDateDebut($dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * @param \DateTime $dateFin
     */
    public function setDateFin($dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * @return float
     */
    public function getMontantPercuUnite(): float
    {
        return $this->montantPercuUnite;
    }

    /**
     * @param float $montantPercuUnite
     */
    public function setMontantPercuUnite(float $montantPercuUnite): self
    {
        $this->montantPercuUnite = $montantPercuUnite;
        return $this;
    }

    /**
     * @return float
     */
    public function getCoutTotalEtude(): float
    {
        return $this->coutTotalEtude;
    }

    /**
     * @param float $coutTotalEtude
     */
    public function setCoutTotalEtude(float $coutTotalEtude): self
    {
        $this->coutTotalEtude = $coutTotalEtude;
        return $this;
    }

    /**
     * @return float
     */
    public function getMontantTotal(): float
    {
        return $this->montantTotal;
    }

    /**
     * @param float $montantTotal
     */
    public function setMontantTotal(float $montantTotal): self
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValidePoleCompetivite(): bool
    {
        return $this->validePoleCompetivite;
    }

    /**
     * @param bool $validePoleCompetivite
     */
    public function setValidePoleCompetivite(bool $validePoleCompetivite): self
    {
        $this->validePoleCompetivite = $validePoleCompetivite;
        return $this;
    }

    /**
     * @return string
     */
    public function getPoleCompetivite(): string
    {
        return $this->poleCompetivite;
    }

    /**
     * @param string $poleCompetivite
     */
    public function setPoleCompetivite(string $poleCompetivite): self
    {
        $this->poleCompetivite = $poleCompetivite;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommentaires(): string
    {
        return $this->commentaires;
    }

    /**
     * @param string $commentaires
     */
    public function setCommentaires(string $commentaires): self
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPia(): bool
    {
        return $this->pia;
    }

    /**
     * @param bool $pia
     */
    public function setPia(bool $pia): self
    {
        $this->pia = $pia;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAccordCadre(): bool
    {
        return $this->accordCadre;
    }

    /**
     * @param bool $accordCadre
     */
    public function setAccordCadre(bool $accordCadre): self
    {
        $this->accordCadre = $accordCadre;
        return $this;
    }

    /**
     * @return string
     */
    public function getCifre(): string
    {
        return $this->cifre;
    }

    /**
     * @param string $cifre
     */
    public function setCifre(string $cifre): self
    {
        $this->cifre = $cifre;
        return $this;
    }

    /**
     * @return string
     */
    public function getChaireIndustrielle(): string
    {
        return $this->chaireIndustrielle;
    }

    /**
     * @param string $chaireIndustrielle
     */
    public function setChaireIndustrielle(string $chaireIndustrielle): self
    {
        $this->chaireIndustrielle = $chaireIndustrielle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPresencePartenaireIndustriel(): string
    {
        return $this->PresencePartenaireIndustriel;
    }

    /**
     * @return int
     */
    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    /**
     * @param int $documentId
     */
    public function setDocumentId(?int $documentId): self
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * @param string $PresencePartenaireIndustriel
     */
    public function setPresencePartenaireIndustriel(string $PresencePartenaireIndustriel): self
    {
        $this->PresencePartenaireIndustriel = $PresencePartenaireIndustriel;
        return $this;
    }

    public function getSignedFileName()
    {
        return $this->getNumContratTutelleGestionnaire().".pdf";
    }

    private $documentPath = null;

    public function setDocumentPath($path){
        $this->documentPath = $path;
        return $this;
    }

    public function getDocumentPath()
    {
        return $this->documentPath;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusStr(): string
    {
        return self::statusStr($this->getStatus());
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getError(): ?array
    {
        return $this->error;
    }

    public function addError( $errorMessage ) :self
    {
        $this->error[] = $errorMessage;
        $this->setStatus(self::STATUS_ERROR_DATA);
        return $this;
    }

    /**
     * @param array $error
     */
    public function setError(?array $error): self
    {
        $this->error = $error;
        return $this;
    }


}