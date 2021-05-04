<?php


namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPerson;
use Oscar\Exception\OscarException;
use Oscar\Service\OscarConfigurationService;

class ActivityPcruInfoFromActivityFactory
{
    /** @var OscarConfigurationService */
    private $oscarConfigurationService;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ActivityPcruInfoFromActivityFactory constructor.
     * @param OscarConfigurationService $oscarConfigurationService
     * @param EntityManager $entityManager
     */
    public function __construct(OscarConfigurationService $oscarConfigurationService, EntityManager $entityManager)
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
        $this->entityManager = $entityManager;
    }


    public function createNew(Activity $activity): ActivityPcruInfos
    {
        $activityPcruInfos = new ActivityPcruInfos();

        // Recherche automatique de l'Unité (Laboratoire)
        $roleStructureToFind = $this->oscarConfigurationService->getOptionalConfiguration('pcru_unite_role', 'Laboratoire');
        $codeUniteLabintel = "";
        $sigleUnit = "";

        $structures = $activity->getOrganizationsWithRole($roleStructureToFind);
        if( count($structures) == 0 ){
            throw new OscarException("Aucune structure $roleStructureToFind pour cette activité.");
        }


        $organizationsParsed = [];
        /** @var ActivityOrganization $unite */
        foreach ($activity->getOrganizationsWithRole($roleStructureToFind) as $unite) {
            $organizationsParsed[] = (string)$unite->getOrganization();
            if( $unite->getOrganization()->getLabintel() ){
                $codeUniteLabintel = $unite->getOrganization()->getLabintel();
                $sigleUnit = $unite->getOrganization()->getShortName();
            }
        }

        if( $codeUniteLabintel == "" ){
            throw new OscarException("Le $roleStructureToFind (".implode(', ', $organizationsParsed).") n'a pas de code LABINTEL");
        }

        // Recherche automatique du responsable scientifique
        $roleRSToFind = $this->oscarConfigurationService->getOptionalConfiguration('pcru_respscien_role', 'Responsable Scientifique');
        $responsable = "";

        /** @var ActivityPerson $personActivity */
        foreach ($activity->getPersonsWithRole($roleRSToFind) as $personActivity) {
            $responsable = $personActivity->getPerson()->getFirstname() . " " . strtoupper($personActivity->getPerson()->getLastname());
        }

        $activityPcruInfos->setActivity($activity)
            ->setSigleUnite($sigleUnit)
            ->setPoleCompetivite($activity->getPcruPoleCompetitiviteStr())
            ->setNumContratTutelleGestionnaire($activity->getOscarNum())
            ->setValidePoleCompetivite($activity->isPcruPolePoleCompetitiviteStr())
            ->setSourceFinancement($activity->getPcruSourceFinancementStr())
            ->setResponsableScientifique($responsable)
            ->setCodeUniteLabintel($codeUniteLabintel)
            ->setObjet($activity->getLabel())
            ->setAcronyme($activity->getAcronym())
            ->setMontantTotal($activity->getAmount())
            ->setDateDebut($activity->getDateStart())
            ->setDateFin($activity->getDateEnd())
            ->setDateDerniereSignature($activity->getDateSigned())
            ->setReference($activity->getOscarNum());
        return $activityPcruInfos;
    }

    public function getHeaders(){
        return [
            'Objet' => "Intitulé de l'activité",
            'CodeUniteLabintel' => 'Code LABINTEL (extrait depuis la fiche organisation du laboratoire)',
            'SigleUnite' =>'Extrait depuis la fiche organisation du laboratoire (nom court)',
            'NumContratTutelleGestionnaire' => 'N°Oscar',
            'Equipe' => 'off',
            'TypeContrat' => '',
            'Acronyme' => "Acronyme du projet de l'activité",
            'ContratsAssocies' => 'off',
            'ResponsableScientifique' => 'Nom de la personne ayant le rôle "Responsable scientifique"',
            'EmployeurResponsableScientifique' => 'off',
            'CoordinateurConsortium' => 'off',
            'Partenaires' => 'off',
            'PartenairePrincipal' => 'off',
            'IdPartenairePrincipal' => 'off',
            'SourceFinancement' => 'Extrait de la fiche activité',
            'LieuExecution' => 'off',
            'DateDerniereSignature' => 'Extrait de la fiche activité',
            'Duree' => 'off',
            'DateDebut' => 'Extrait de la fiche activité',
            'DateFin' => 'Extrait de la fiche activité',
            'MontantPercuUnite' => 'off',
            'CoutTotalEtude' => 'off',
            'MontantTotal' => 'Extrait de la fiche activité',
            'ValidePoleCompetivite' => 'Extrait de la fiche activité',
            'PoleCompetivite' => 'Extrait de la fiche activité',
            'Commentaires' => 'off',
            'Pia' => 'off',
            'Reference' => '',
            'AccordCadre' => 'off',
            'Cifre' => 'off',
            'ChaireIndustrielle' => 'off',
            'PresencePartenaireIndustriel' => 'off',
        ];
    }
}