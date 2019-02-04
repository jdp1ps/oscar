<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/07/15 13:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

/**
 * Class ProjectGrantRepository
 * @package Oscar\Entity
 */
class ProjectGrantRepository extends EntityRepository{

    protected function getBaseQuery()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('pg, p, m, t')
            ->from('Oscar\Entity\ProjectGrant', 'pg')
            ->leftJoin('pg.project', 'p')
            ->leftJoin('pg.type', 't')
            ->leftJoin('p.members', 'm');

        return $qb;
    }

    public function getAllByYear( $year=null ){
        $query = $this->getBaseQuery();
        if( $year != null ){
            $query->where('pg.dateStart LIKE :year')
                ->setParameter('year', "$year-%");
        }
        return $query->orderBy('pg.dateStart', 'DESC')->getQuery()->getResult();
    }

    public function getContractByNumConvention( $numConvention )
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('pg, p, m')
            ->from('Oscar\Entity\ProjectGrant', 'pg')
            ->leftJoin('pg.project', 'p')
            ->leftJoin('p.members', 'm')
            ->where('pg.centaureNumConvention = :numConvention')
            ->setParameter('numConvention', $numConvention)
            ;
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $idProject
     * @return \Doctrine\ORM\Query
     */
    public function getOne( $idProject )
    {
        $queryBuilder = $this->getBaseQuery();
        $queryBuilder->where('p.id = :idProject')
            ->setParameter('idProject', $idProject);
        return $queryBuilder->getQuery();

    }
}