<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * Retourne la liste des notifications d'une personne.
     *
     * @param $person
     */
    public function getPersonRepository( $person ){
        if( $person instanceof Person ){
            $personId = $person->getId();
        } else {
            $personId = $person;
        }
    }

    /**
     * Retourne la liste des notifications d'une activité
     */
    public function getNotificationsActivity( $activityId )
    {
        $qb = $this->createQueryBuilder("n")
            ->where("n.object = :object AND n.objectId = :objectid")
        ;

        $qb->setParameters([
            'object' => 'activity',
            'objectid' => $activityId
         ]);

        return $qb->getQuery()->getResult();


    }

}