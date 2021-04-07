<?php


namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPcruInfos;
use Oscar\Entity\ActivityPerson;
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
        /** @var ActivityOrganization $unite */
        foreach ($activity->getOrganizationsWithRole($roleStructureToFind) as $unite) {
           $codeUniteLabintel = $unite->getOrganization()->getLabintel();
           $sigleUnit = $unite->getOrganization()->getShortName();
        }

        // Recherche automatique du responsable scientifique
        $roleRSToFind = $this->oscarConfigurationService->getOptionalConfiguration('pcru_respscien_role', 'Responsable scientifique');
        $responsable = "";
        /** @var ActivityPerson $personActivity */
        foreach ($activity->getPersonsWithRole($roleRSToFind) as $personActivity) {
            $responsable = $personActivity->getPerson()->getFirstname() . " " . strtoupper($personActivity->getPerson()->getLastname());
        }

        $activityPcruInfos->setActivity($activity)
            ->setSigleUnite($sigleUnit)
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
}