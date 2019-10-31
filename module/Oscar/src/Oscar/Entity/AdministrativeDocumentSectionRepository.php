<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 31/10/19
 * Time: 14:35
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class AdministrativeDocumentSectionRepository extends EntityRepository
{
    public function getAll( $asArray ){
        return $this->getbaseQuery()
            ->getQuery()
            ->getResult($asArray ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
    }

    public function getOne( $administrativeDocumentSectionId, $asArray = false ){
        return $this->getbaseQuery()->where('s.id = :id')
            ->setParameter('id', $administrativeDocumentSectionId)
            ->getQuery()
            ->getSingleResult($asArray ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getbaseQuery(){
        return $this->createQueryBuilder('s');
    }
}