<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class DateTypeRepository extends EntityRepository
{
    public function allWithUsage(){
        $dql = "SELECT d.id, d.label, d.description, d.facet, d.finishable, d.recursivity, count(m.id) as used 
          FROM Oscar\Entity\DateType d 
          LEFT JOIN d.milestones m 
          GROUP BY d.id 
          ORDER BY d.facet, d.label";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getArrayResult();
    }

}