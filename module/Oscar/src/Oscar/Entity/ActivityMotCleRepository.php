<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ActivityMotCleRepository extends EntityRepository
{

    /**
     * Retourne tous les mots clés
     *
     * @return array
     */
    public function getAll(){
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->orderBy('m.label', 'ASC');
        return $query->getQuery()->getResult();
    }

}
