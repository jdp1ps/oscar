<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ActivityMotCleRepository extends EntityRepository
{
    /**
     * Retourne tous les mots clés
     *
     * @return ActivityMotCle[]
     */
    public function getAll()
    {
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->orderBy('m.label', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getAllArray()
    {
        $out = [];
        foreach ($this->getAll() as $activityMotCle) {
            $out[$activityMotCle->getId()] = $activityMotCle->getLabel();
        }
        return $out;
    }

    /**
     * Retourne la liste des mots clés avec le comptage des projets.
     */
    public function getMotsClesCounted()
    {
        $dql = "SELECT m.id, m.label, count(a.id) as activity_count FROM Oscar\Entity\ActivityMotCle m LEFT JOIN m.activities a GROUP BY m.id ORDER BY m.label";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getArrayResult();
    }
}
