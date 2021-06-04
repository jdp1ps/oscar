<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2021
 */

namespace Oscar\Hydrator;


use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\Organization;
use Zend\Hydrator\HydratorInterface;

class ActivityInfosPCRUFormHydrator implements HydratorInterface
{

    /**
     * OrganizationFormHydrator constructor.
     */
    public function __construct()
    {
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
        $object->setFullName($data['fullname']);
        $object->setNumContratTutelleGestionnaire($data['numcontrattutellegestionnaire']);
        $object->setEquipe($data['equipe']);
        $object->setTypeContrat($data["typecontrat"]);
        $object->setAcronyme($data["acronyme"]);
        $object->setContratsAssocies($data["contratsassocies"]);
        $object->setResponsableScientifique($data["responsablescientifique"]);
        $object->setEmployeurResponsableScientifique($data["employeurresponsablescientifique"]);
        $object->setCordinateurConsortium($data["cordinateurconsortium"]);
        $object->setPartenaires($data["partenaires"]);
        $object->setPartenairePrincipal($data["partenaireprincipal"]);
        $object->setIdPartenairePrincipal($data["idpartenaireprincipal"]);
        $object->setSourceFinancement($data["sourcefinancement"]);
        $object->setDateDerniereSignature($data["datedernieresignature"]);
        $object->setDuree($data["duree"]);
        $object->setDateDebut($data["datedebut"]);
        $object->setDateFin($data["datefin"]);
        $object->setMontantPercuUnite($data["montantpercuunite"]);
        $object->setMontantTotal($data["montanttotal"]);
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
            'equipe' => $object->getEquipe(),
            "typecontrat" => $object->getTypeContrat(),
            "acronyme" => $object->getAcronyme(),
            "contratsassocies" => $object->getContratsAssocies(),
            "responsablescientifique" => $object->getResponsableScientifique(),
            "employeurresponsablescientifique" => $object->getEmployeurResponsableScientifique(),
            "cordinateurconsortium" => $object->isCordinateurConsortium(),
            "partenaires" => $object->getPartenaires(),
            "partenaireprincipal" => "",
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
            "presencepartenaireindustriel" => $object->getPresencePartenaireIndustriel(),
        ];

        return $datas;
    }
}
