<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/07/15 13:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

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

    public function getAll(){
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->orderBy('t.lft', 'ASC');
        return $query->getQuery()->getResult();
    }
}