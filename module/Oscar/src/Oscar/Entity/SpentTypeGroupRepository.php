<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/07/15 13:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Oscar\Exception\OscarException;

/**
 * Class SpentTypeGroupRepository
 * @package Oscar\Entity
 */
class SpentTypeGroupRepository extends EntityRepository{

    /**
     * @return SpentTypeGroup
     */
    public function getLastSpentTypeGroup(){
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->orderBy('t.rgt', 'DESC');

        return $query->getQuery()->getResult()[0];
    }

    /**
     * Retourne tous les types de dépenses.
     *
     * @return array
     */
    public function getAll(){
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->orderBy('t.lft', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne le nombre de types.
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count(array $criteria){
        $qb = $this->createQueryBuilder('t')
            ->select('count(t.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne les liste des types de dépenses en fonction des bornes données.
     *
     * @param $lft
     * @param $rgt
     * @return mixed
     */
    public function getBranchByBounds( $lft, $rgt ){

        $brancheDeplacee = $this->createQueryBuilder('t')
            ->where('t.lft >= :lft AND t.rgt <= :rgt ')
            ->setParameters(['lft' => $lft, 'rgt' => $rgt])
            ->getQuery();

        return $brancheDeplacee->getResult();;
    }

    public function findOneByCode( $code ){
        return $this->createQueryBuilder('st')
            ->where('st.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Retourne la liste des comptes référencés dans les dépenses (SpentLine).
     *
     * @return array
     */
    public function getUsedAccount()
    {
        $qb = $this->getEntityManager()->getRepository(SpentLine::class)
            ->createQueryBuilder('s')
            ->select('DISTINCT s.compteGeneral');

        return array_map('current', $qb->getQuery()->getArrayResult());
    }

    public function getPfiForCodesAccounts( $codes )
    {

        $qb = $this->getEntityManager()->getRepository(SpentLine::class)
            ->createQueryBuilder('g')
            ->select('DISTINCT g.pfi')
            ->where('g.compteGeneral IN(:codes)')
            ->setParameter('codes', $codes)
        ;

        return array_map('current', $qb->getQuery()->getArrayResult());
    }
}