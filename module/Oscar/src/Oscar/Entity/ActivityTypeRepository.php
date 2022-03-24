<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ActivityTypeRepository extends EntityRepository
{
    /**
     * Retourne une "chaîne" des types (du plus proche au plus lointain, incluant ROOT)
     * @param ActivityType $activityType
     * @return int|mixed|string
     */
    public function getChainFromActivityType( ActivityType $activityType )
    {
        $query = $this->createQueryBuilder('at')
            ->where('at.lft < :lft AND at.rgt > :rgt')
            ->orderBy('at.lft', 'DESC');

        return $query->getQuery()->setParameters([
            'lft' => $activityType->getLft(),
            'rgt' => $activityType->getRgt()
        ])->getResult();
    }
}