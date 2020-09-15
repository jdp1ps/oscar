<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;


class RecallDeclarationRepository extends EntityRepository
{
    public function getRecallDeclarationsPersonPeriod( int $personId, int $periodYear, int $periodMonth )
    {
        return $this->createQueryBuilder('r')
            ->where('r.person = :person AND r.periodYear = :periodYear AND r.periodMonth = :periodMonth')
            ->setParameters([
                'person' => $personId,
                'periodYear' => $periodYear,
                'periodMonth' => $periodMonth,
            ])
            ->getQuery()
            ->getResult();
    }
}