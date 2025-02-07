<?php
namespace Oscar\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TimesheetCommentPeriodRepository extends EntityRepository
{
    /**
     * Retourne tous les commentaires de la personne.
     *
     * @param int $personId
     * @return array
     */
    public function getCommentairesPerson( int $personId ):array {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.declarer = :personId')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getResult();
    }
}
