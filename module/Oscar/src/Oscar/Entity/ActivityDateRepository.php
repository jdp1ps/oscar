<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class ActivityDateRepository extends EntityRepository
{
    public function getMilestonesFinishable( $status = null )
    {
         $query = $this->createQueryBuilder('m')
            ->select('m')
             ->innerJoin('m.type', 't')
             ->andWhere('t.id = 53') // AND m.dateStart <= :now')
         ;

         $query->where('t.finishable = :finishable')
            ->setParameter('finishable', true)
            ->andWhere('m.finished IS NULL OR m.finished <= 0');

         //$query->setParameter('now', date('Y-m-d'));
         // AND m.dateStart <= :now')


         $result = $query->getQuery()->getResult();

         /** @var ActivityDate $r */
        foreach ($result as $r) {
            $fin = ($r->getDateFinish() ? $r->getDateFinish()->format('Y-m-d'): 'Pas fini');
             echo $r->getType()->getId()  . " - $r - " . $r->getFinished() . " / " . $fin . "\n";
         }
         echo $query->getDQL();

         die("TOTAL : " . count($result));
    }
}
