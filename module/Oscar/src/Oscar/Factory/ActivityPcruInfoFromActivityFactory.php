<?php


namespace Oscar\Factory;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPcruInfos;

class ActivityPcruInfoFromActivityFactory
{
    public static function createNew(Activity $activity) :ActivityPcruInfos
    {
        $activityPcruInfos = new ActivityPcruInfos();

        $activityPcruInfos->setActivity($activity)
            ->setObjet($activity->getLabel())
            ->setAcronyme($activity->getAcronym())
            ->setMontantTotal($activity->getAmount())
            ->setDateDebut($activity->getDateStart())
            ->setDateFin($activity->getDateEnd())
            ->setDateDerniereSignature($activity->getDateSigned())
            ->setReference($activity->getOscarNum())
        ;
        return $activityPcruInfos;
    }
}