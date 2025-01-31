<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class ActivityNoteRepository extends EntityRepository
{
    /**
     * @param int $activityId
     * @return ActivityNote[]
     */
    public function getNotesActivity(int $activityId): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.activity = :activityId');
        return $qb->setParameter('activityId', $activityId)
            ->getQuery()
            ->getResult();
    }
}
