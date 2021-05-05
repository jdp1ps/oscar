<?php


namespace Oscar\Strategy\PCRU;


use Oscar\Entity\Activity;

interface PCRUDepotStrategy
{
    public function sendActivity(Activity $activity): bool;
}