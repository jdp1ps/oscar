<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class CurrencyRepository extends EntityRepository
{
    public function getCurrenciesArray()
    {
        try {
            $qb = $this->createQueryBuilder('c')
                //->orderBy('c.label', 'ASC')
                ->getQuery();
            return $qb->getArrayResult();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
