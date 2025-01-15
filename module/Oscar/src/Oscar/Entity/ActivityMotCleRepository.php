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
    public function getAll(){
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->orderBy('m.label', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getAllArray(){
        $out = [];
        foreach ($this->getAll() as $activityMotCle) {
            $out[$activityMotCle->getId()] = $activityMotCle->getLabel();
        }
        return $out;
    }
}
