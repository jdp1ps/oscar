<?php


namespace Oscar\Formatter\PCRU;


use Oscar\Entity\ActivityPcruInfos;
use Oscar\Utils\DateTimeUtils;
use Zend\Hydrator\ArraySerializable;

class ActivityPcruInfosToFormArray
{

    public function toArray( ActivityPcruInfos $activityPcruInfos ) :array{
        return [
            'id' => $activityPcruInfos->getId(),
            'objet' => $activityPcruInfos->getObjet(),
            'codeUniteLabintel' => $activityPcruInfos->getCodeUniteLabintel(),
            'sigleUnite' => $activityPcruInfos->getSigleUnite(),
            'numContratTutelleGestionnaire' => $activityPcruInfos->getNumContratTutelleGestionnaire(),
            'equipe' => $activityPcruInfos->getEquipe(),
            'typeContrat' => $activityPcruInfos->getTypeContrat(),
            'acronyme' => $activityPcruInfos->getAcronyme(),
            'contratsAssocies' => $activityPcruInfos->getContratsAssocies(),
            'responsableScientifique' => $activityPcruInfos->getResponsableScientifique(),
            'employeurResponsableScientifique' => $activityPcruInfos->getEmployeurResponsableScientifique(),
            'coordinateurConsortium' => $activityPcruInfos->isCordinateurConsortium(),
            'partenaires' => $activityPcruInfos->getPartenaires(),
            'partenairePrincipal' => $activityPcruInfos->isPartenairePrincipal(),
            'idPartenairePrincipal' => $activityPcruInfos->getIdPartenairePrincipal(),
            'sourceFinancement' => $activityPcruInfos->getSourceFinancement(),
            'lieuExecution' => $activityPcruInfos->getLieuExecution(),
            'dateDerniereSignature' => DateTimeUtils::toStr($activityPcruInfos->getDateDerniereSignature(), 'd-m-Y'),
            'duree' => $activityPcruInfos->getDuree(),
            'dateDebut' => DateTimeUtils::toStr($activityPcruInfos->getDateDebut(), 'd-m-Y'),
            'dateFin' => DateTimeUtils::toStr($activityPcruInfos->getDateFin(), 'd-m-Y'),
            'montantPercuUnite' => $activityPcruInfos->getMontantPercuUnite(),
            'coutTotalEtude' => $activityPcruInfos->getCoutTotalEtude(),
            'montantTotal' => $activityPcruInfos->getMontantTotal(),
            'validePoleCompetivite' => $activityPcruInfos->isValidePoleCompetivite(),
            'poleCompetivite' => $activityPcruInfos->getPoleCompetivite(),
            'commentaires' => $activityPcruInfos->getCommentaires(),
            'pia' => $activityPcruInfos->isPia(),
            'reference' => $activityPcruInfos->getReference(),
            'accordCadre' => $activityPcruInfos->isAccordCadre(),
            'cifre' => $activityPcruInfos->getCifre(),
            'chaireIndustrielle' => $activityPcruInfos->getChaireIndustrielle(),
            'resencepartenaireindustriel' => $activityPcruInfos->getPresencePartenaireIndustriel(),
        ];
    }
}