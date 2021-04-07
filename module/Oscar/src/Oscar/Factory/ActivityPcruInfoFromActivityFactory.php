<?php


namespace Oscar\Factory;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPcruInfos;
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
        foreach ($activity->getOrganizationsWithRole($roleStructureToFind) as $unite) {
            echo "-" . $unite->getOrganization() . "\n";
        }

        die("DEV");


        $activityPcruInfos->setActivity($activity)
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