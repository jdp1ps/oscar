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

        $r = $qb->getQuery()->getArrayResult();
        return array_map('current', $r);
    }

    /**
     * @return int[]
     */
    public function getExcludedPersonsIds(): array
    {
        $qb = $this->getBaseExcludeQueryBuilder()
            ->select('p.id');

        $r = $qb->getQuery()->getArrayResult();
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
     * @return RecallException[]
     */
    public function getBlacklist()
    {
        return $this->getBaseExcludeQueryBuilder()->select('e')->getQuery()->getResult();
    }

    /**
     * @param int $personId
     * @return bool
     */
    public function isInWhiteList( int $personId ): bool
    {
        $r = $this->getBaseIncludeQueryBuilder()
            ->andWhere('p.id = :personid')
            ->setParameter('personid', $personId)
            ->getQuery()
            ->getResult();

        return count($r) > 0;
    }

    /**
     * @param int $personId
     * @return bool
     */
    public function isInBlackList( int $personId ): bool
    {
        $r = $this->getBaseExcludeQueryBuilder()
            ->andWhere('p.id = :personid')
            ->setParameter('personid', $personId)
            ->getQuery()
            ->getResult();

        return count($r) > 0;
    }

    public function removeDeclarerFromBlacklist( int $personId ):void
    {
        $exceptions = $this->getBaseExcludeQueryBuilder()
            ->andWhere('e.person = :personId')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getResult();
        foreach ($exceptions as $exception) {
            $this->getEntityManager()->remove($exception);
        }
    }

    public function removeDeclarerFromWhitelist( int $personId ):void
    {
        $exceptions = $this->getBaseIncludeQueryBuilder()
            ->andWhere('e.person = :personId')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getResult();
        foreach ($exceptions as $exception) {
            $this->getEntityManager()->remove($exception);
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseExcludeQueryBuilder()
    {
        return $this->getBaseQueryBuilder()
            ->andWhere('e.type = :exclude')
            ->setParameter('exclude', RecallException::TYPE_EXCLUDED);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseIncludeQueryBuilder()
    {
        return $this->getBaseQueryBuilder()
            ->andWhere('e.type = :include')
            ->setParameter('include', RecallException::TYPE_INCLUDED);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryBuilder()
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.person', 'p');
    }
}