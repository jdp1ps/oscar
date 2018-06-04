<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class DisciplineRepository extends EntityRepository
{
    /**
     * Retourne les disciplines.
     */
    public function getDisciplines(){
        return $this->createQueryBuilder('d')
            ->select('d')
            ->from(Discipline::class)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des disciplines avec le comptage des projets.
     */
    public function getDisciplinesCounted(){
        $dql = "SELECT d.id, d.label, count(a.id) as activitiesLng FROM Oscar\Entity\Discipline d LEFT JOIN d.activities a GROUP BY d.id ORDER BY d.label";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getArrayResult();
    }

}