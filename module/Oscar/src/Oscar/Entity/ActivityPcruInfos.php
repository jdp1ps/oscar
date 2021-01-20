<?php


namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne as OneToOne;

/**
 * Class ActivityPcruInfos
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ActivityPcruInfos
{
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
     * @param string $PresencePartenaireIndustriel
     */
    public function setPresencePartenaireIndustriel(string $PresencePartenaireIndustriel): self
    {
        $this->PresencePartenaireIndustriel = $PresencePartenaireIndustriel;
        return $this;
    }
}