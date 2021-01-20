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
        ;
        return $activityPcruInfos;
    }
}