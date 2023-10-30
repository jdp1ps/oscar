<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2021
 */

namespace Oscar\Hydrator;


use Oscar\Entity\ActivityPcruInfos;
use Oscar\Service\ProjectGrantService;
use Laminas\Hydrator\HydratorInterface;

class ActivityInfosPCRUFormHydrator implements HydratorInterface
{
    private $projectGrantService;

    /**
     * OrganizationFormHydrator constructor.
     */
    public function __construct( ProjectGrantService $projectGrantService )
    {
        $this->projectGrantService = $projectGrantService;
    }


    /**
     * @param array $data
     * @param ActivityPcruInfos $object
     */
    public function hydrate(array $data, $object)
    {
        $object->setObjet($data['objet']);
        $object->setCodeUniteLabintel($data['codeunitelabintel']);
        $object->setSigleUnite($data['sigleunite']);
        //$object->setFullName($data['fullname']);
        $object->setNumContratTutelleGestionnaire($data['numcontrattutellegestionnaire']);
        $object->setReference($data['reference']);
        $object->setEquipe($data['equipe']);

        $typeContrat = $this->projectGrantService->getPcruTypeContratRepository()->getPcruTypeContratByLabel($data['typecontrat']);
        $object->setTypeContrat($typeContrat);

        $object->setAcronyme($data["acronyme"]);
        $object->setContratsAssocies($data["contratsassocies"]);
        $object->setResponsableScientifique($data["responsablescientifique"]);
        $object->setEmployeurResponsableScientifique($data["employeurresponsablescientifique"]);
        $object->setCoordinateurConsortium($data["coordinateurconsortium"]);
        $object->setPartenaires($data["partenaires"]);
        $object->setPartenairePrincipal($data["partenaireprincipal"]);
        $object->setIdPartenairePrincipal($data["idpartenaireprincipal"]);

        $sourceFinancement = $this->projectGrantService->getPcruSourceFinancementRepository()->findOneByLabel($data['sourcefinancement']);
        $object->setSourceFinancement($sourceFinancement);

        $dateSignature = $data["datedernieresignature"] ? new \DateTime($data["datedernieresignature"]) : null;
        $object->setDateDerniereSignature($dateSignature);

        $object->setDuree($data["duree"]);

        $dateDebut = $data["datedebut"] ? new \DateTime($data["datedebut"]) : null;
        $object->setDateDebut($dateDebut);

        $dateFin = $data["datefin"] ? new \DateTime($data["datefin"]) : null;
        $object->setDateDebut($dateFin);

        $object->setMontantPercuUnite(floatval($data["montantpercuunite"]));
        $object->setCoutTotalEtude(floatval($data["couttotaletude"]));
        $object->setMontantTotal(floatval($data["montanttotal"]));
        $object->setValidePoleCompetivite($data["validepolecompetivite"]);
        $object->setPoleCompetivite($data["polecompetivite"]);
        $object->setCommentaires($data["commentaires"]);
        $object->setPia($data["pia"]);
        $object->setAccordCadre($data["accordcadre"]);
        $object->setCifre($data["cifre"]);
        $object->setChaireIndustrielle($data["chaireindustrielle"]);
        $object->setPresencePartenaireIndustriel($data["presencepartenaireindustriel"]);

        return $object;
    }

    /**
     * @param ActivityPcruInfos $object
     * @return array
     */
    public function extract($object)
    {
        $datas = [
            'objet' => $object->getObjet(),
            'codeunitelabintel' => $object->getCodeUniteLabintel(),
            'sigleunite' => $object->getSigleUnite(),
            'numcontrattutellegestionnaire' => $object->getNumContratTutelleGestionnaire(),
            'reference' => $object->getReference(),
            'equipe' => $object->getEquipe(),
            "typecontrat" => $object->getTypeContrat() ? $object->getTypeContrat()->getLabel() : "",
            "acronyme" => $object->getAcronyme(),
            "contratsassocies" => $object->getContratsAssocies(),
            "responsablescientifique" => $object->getResponsableScientifique(),
            "employeurresponsablescientifique" => $object->getEmployeurResponsableScientifique(),
            "coordinateurconsortium" => $object->isCoordinateurConsortium(),
            "partenaires" => $object->getPartenaires(),
            "partenaireprincipal" => $object->getIdPartenairePrincipal(),
            "idpartenaireprincipal" => $object->getIdPartenairePrincipal(),
            "sourcefinancement" => $object->getSourceFinancement(),
            "datedernieresignature" => $object->getDateDerniereSignatureStr(),
            "duree" => $object->getDuree(),
            "datedebut" => $object->getDateDebutStr(),
            "datefin" => $object->getDateFinStr(),
            "montantpercuunite" => $object->getMontantPercuUnite(),
            "montanttotal" => $object->getMontantTotal(),
            "validepolecompetivite" => $object->isValidePoleCompetivite(),
            "polecompetivite" => $object->getPoleCompetivite(),
            "commentaires" => $object->getCommentaires(),
            "pia" => $object->isPia(),
            "accordcadre" => $object->isAccordCadre(),
            "cifre" => $object->getCifre(),
            "chaireindustrielle" => $object->getChaireIndustrielle(),
            "presencepartenaireindustriel" => $object->isPresencePartenaireIndustriel(),
        ];

        return $datas;
    }
}
