<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;


class RecallExceptionRepository extends EntityRepository
{
    /**
     * @return int[]
     */
    public function getIncludedPersonsIds(): array
    {
        $qb = $this->getBaseIncludeQueryBuilder()
            ->select('p.id');

        $r = $qb->getQuery()->setParameter('include', RecallException::TYPE_INCLUDED)->getArrayResult();
        return array_map('current', $r);
    }

    /**
     * @return RecallException[]
     */
    public function getWhitelist()
    {
        return $this->getBaseIncludeQueryBuilder()->select('e')->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseIncludeQueryBuilder()
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.person', 'p')
            ->where('e.type = :include')
            ->setParameter('include', RecallException::TYPE_INCLUDED);
    }
}