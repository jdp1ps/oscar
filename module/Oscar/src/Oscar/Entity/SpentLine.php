<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Import\Data\DataExtractorDate;
use Oscar\Service\ActivityTypeService;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * @package Oscar\Entity
 * @ORM\Entity
 */
class SpentLine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $syncId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pfi;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numSifac;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numCommandeAff;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numPiece;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numFournisseur;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pieceRef;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeSociete;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeServiceFait;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeDomaineFonct;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $designation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $texteFacture;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $typeDocument;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $montant;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $centreDeProfit;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $compteBudgetaire;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $centreFinancier;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $compteGeneral;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $datePiece;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dateComptable;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dateAnneeExercice;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $datePaiement;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dateServiceFait;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSyncId()
    {
        return $this->syncId;
    }

    /**
     * @param mixed $syncId
     */
    public function setSyncId($syncId)
    {
        $this->syncId = $syncId;
    }

    /**
     * @return mixed
     */
    public function getPfi()
    {
        return $this->pfi;
    }

    /**
     * @param mixed $pfi
     */
    public function setPfi($pfi): void
    {
        $this->pfi = $pfi;
    }

    /**
     * @return mixed
     */
    public function getNumSifac()
    {
        return $this->numSifac;
    }

    /**
     * @param mixed $numSifac
     */
    public function setNumSifac($numSifac): void
    {
        $this->numSifac = $numSifac;
    }

    /**
     * @return mixed
     */
    public function getNumCommandeAff()
    {
        return $this->numCommandeAff;
    }

    /**
     * @param mixed $numCommandeAff
     */
    public function setNumCommandeAff($numCommandeAff): void
    {
        $this->numCommandeAff = $numCommandeAff;
    }

    /**
     * @return mixed
     */
    public function getNumPiece()
    {
        return $this->numPiece;
    }

    /**
     * @param mixed $numPiece
     */
    public function setNumPiece($numPiece): void
    {
        $this->numPiece = $numPiece;
    }

    /**
     * @return mixed
     */
    public function getNumFournisseur()
    {
        return $this->numFournisseur;
    }

    /**
     * @param mixed $numFournisseur
     */
    public function setNumFournisseur($numFournisseur): void
    {
        $this->numFournisseur = $numFournisseur;
    }

    /**
     * @return mixed
     */
    public function getPieceRef()
    {
        return $this->pieceRef;
    }

    /**
     * @param mixed $pieceRef
     */
    public function setPieceRef($pieceRef): void
    {
        $this->pieceRef = $pieceRef;
    }

    /**
     * @return mixed
     */
    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    /**
     * @param mixed $codeSociete
     */
    public function setCodeSociete($codeSociete): void
    {
        $this->codeSociete = $codeSociete;
    }

    /**
     * @return mixed
     */
    public function getCodeServiceFait()
    {
        return $this->codeServiceFait;
    }

    /**
     * @param mixed $codeServiceFait
     */
    public function setCodeServiceFait($codeServiceFait): void
    {
        $this->codeServiceFait = $codeServiceFait;
    }

    /**
     * @return mixed
     */
    public function getCodeDomaineFonct()
    {
        return $this->codeDomaineFonct;
    }

    /**
     * @param mixed $codeDomaineFonct
     */
    public function setCodeDomaineFonct($codeDomaineFonct): void
    {
        $this->codeDomaineFonct = $codeDomaineFonct;
    }

    /**
     * @return mixed
     */
    public function getDesignation()
    {
        return trim($this->designation);
    }

    /**
     * @param mixed $designation
     */
    public function setDesignation($designation): void
    {
        $this->designation = $designation;
    }

    /**
     * @return mixed
     */
    public function getTexteFacture()
    {
        return trim($this->texteFacture);
    }

    /**
     * @param mixed $texteFacture
     */
    public function setTexteFacture($texteFacture): void
    {
        $this->texteFacture = $texteFacture;
    }

    /**
     * @return mixed
     */
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * @param mixed $typeDocument
     */
    public function setTypeDocument($typeDocument): void
    {
        $this->typeDocument = $typeDocument;
    }

    /**
     * @return mixed
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * @param mixed $montant
     */
    public function setMontant($montant): void
    {
        $this->montant = $montant;
    }

    /**
     * @return mixed
     */
    public function getCentreDeProfit()
    {
        return $this->centreDeProfit;
    }

    /**
     * @param mixed $centreDeProfit
     */
    public function setCentreDeProfit($centreDeProfit): void
    {
        $this->centreDeProfit = $centreDeProfit;
    }

    /**
     * @return mixed
     */
    public function getCompteBudgetaire()
    {
        return $this->compteBudgetaire;
    }

    /**
     * @param mixed $compteBudgetaire
     */
    public function setCompteBudgetaire($compteBudgetaire): void
    {
        $this->compteBudgetaire = $compteBudgetaire;
    }

    /**
     * @return mixed
     */
    public function getCentreFinancier()
    {
        return $this->centreFinancier;
    }

    /**
     * @param mixed $centreFinancier
     */
    public function setCentreFinancier($centreFinancier): void
    {
        $this->centreFinancier = $centreFinancier;
    }

    /**
     * @return mixed
     */
    public function getCompteGeneral()
    {
        return $this->compteGeneral;
    }

    /**
     * @param mixed $compteGeneral
     */
    public function setCompteGeneral($compteGeneral): void
    {
        $this->compteGeneral = $compteGeneral;
    }

    /**
     * @return mixed
     */
    public function getDatePiece()
    {
        return $this->datePiece;
    }

    /**
     * @param mixed $datePiece
     */
    public function setDatePiece($datePiece): void
    {
        $this->datePiece = $datePiece;
    }

    /**
     * @return mixed
     */
    public function getDateComptable()
    {
        return $this->dateComptable;
    }

    /**
     * @param mixed $dateComptable
     */
    public function setDateComptable($dateComptable): void
    {
        $this->dateComptable = $dateComptable;
    }

    /**
     * @return mixed
     */
    public function getDateAnneeExercice()
    {
        return $this->dateAnneeExercice;
    }

    /**
     * @param mixed $dateAnneeExercice
     */
    public function setDateAnneeExercice($dateAnneeExercice): void
    {
        $this->dateAnneeExercice = $dateAnneeExercice;
    }

    /**
     * @return mixed
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
    }

    /**
     * @param mixed $datePaiement
     */
    public function setDatePaiement($datePaiement): void
    {
        $this->datePaiement = $datePaiement;
    }

    /**
     * @return mixed
     */
    public function getDateServiceFait()
    {
        return $this->dateServiceFait;
    }

    /**
     * @param mixed $dateServiceFait
     */
    public function setDateServiceFait($dateServiceFait): void
    {
        $this->dateServiceFait = $dateServiceFait;
    }

    public function __toString()
    {
        return sprintf("%s : %s (cpt: %s)", $this->getId(), $this->getMontant(), $this->getCompteGeneral());
    }


    public function getDetailsHeaders(){
        return [
                "ID(oscar)",
                "PFI",
                "Centre de profit",
                "N° SIFAC",
                "N° PIECE",
                "CODE Ste",
                "CODE Domaine Fonc.",
                "CODE Service Fait",
                "Date Comptable",
                "Année Exercice",
                "Date paiment",
                "Date pièce",
                "Date service fait",

                "COMPT. Général",
                "Compt. Budg",
                "Centre profit",
                "Centre financier",

                "Designation",
                "Texte",
                "MONTANT",

                "N° Commande Aff",
                "N° Fournisseur",
            ];
    }
    public function getDetailsDatas(){
        return [
            $this->getId(),
            $this->getPfi(),

            $this->getCentreDeProfit(),
            $this->getNumSifac(),
            $this->getNumPiece(),
            $this->getCodeSociete(),
            $this->getCodeDomaineFonct(),
            $this->getCodeServiceFait(),


            $this->getDateComptable(),
            $this->getDateAnneeExercice(),
            $this->getDatePaiement(),
            $this->getDatePiece(),
            $this->getDateServiceFait(),

            $this->getCompteGeneral(),
            $this->getCompteBudgetaire(),
            $this->getCentreDeProfit(),
            $this->getCentreFinancier(),

            $this->getDesignation(),
            $this->getTexteFacture(),
            $this->getMontant(),
            $this->getNumCommandeAff(),
            $this->getNumFournisseur(),
        ];
    }

    public function toArray()
    {
        return [
            // IDs / Numéros
            'id' => $this->getId(),
            'syncid' => $this->getSyncId(),
            'pfi' => $this->getPfi(),
            'numSifac' => $this->getNumSifac(),
            'numPiece' => $this->getNumPiece(),

            // Infos
            'montant' => $this->getMontant(),
            'compteBudgetaire' => $this->getCompteBudgetaire(),
            'centreProfit' => $this->getCentreDeProfit(),
            'compteGeneral' => $this->getCompteGeneral(),
            'centreFinancier' => $this->getCentreFinancier(),

            'texteFacture' => $this->getTexteFacture(),
            'designation' => $this->getDesignation(),

            //Dates
            'dateAnneeExercice' => $this->getDateAnneeExercice(),
            'datePaiement' => $this->getDatePaiement(),
            'datePiece' => $this->getDatePiece(),
            'dateComptable' => $this->getDateComptable(),

            //
        ];
    }

}
