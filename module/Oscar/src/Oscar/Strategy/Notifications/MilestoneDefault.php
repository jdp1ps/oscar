<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 07/03/18
 * Time: 11:34
 */

namespace Oscar\Strategy\Notifications;

use Doctrine\ORM\EntityManager;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Activity;

class MilestoneDefault
{
    /**
     * Génération des notifications pour les jalons.
     *
     * @param Activity $activity
     */
    public function generateNotifications( Activity $activity, EntityManager $entityManager ){
        $repport = new ConnectorRepport();
        $repport->addnotice("TEST");
        return $repport;
    }
}