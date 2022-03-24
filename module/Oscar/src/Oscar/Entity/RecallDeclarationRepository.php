<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class RecallDeclarationRepository extends EntityRepository
{
    /**
     * Liste des procédures de rappel pour les validateurs.
     *
     * @param int $personId
     * @param int|null $year
     * @param int|null $month
     * @return RecallDeclaration[]
     */
    public function getRecallValidationPerson(int $personId, ?int $year = null, ?int $month = null)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.person = :person AND r.context = :context ')
            ->setParameters(
                [
                    'person' => $personId,
                    'context' => RecallDeclaration::CONTEXT_VALIDATOR,
                ]
            );

        if ($year) {
            $qb->andWhere('r.periodYear = :year')->setParameter('year', $year);
        }

        if ($month) {
            $qb->andWhere('r.periodMonth = :month')->setParameter('month', $month);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la procédure de rappel pour un déclarant pour la période.
     *
     * @param int $personId
     * @param int $periodYear
     * @param int $periodMonth
     *
     * @return RecallDeclaration[]
     */
    public function getRecallDeclarationsPersonPeriod(int $personId, int $periodYear, int $periodMonth)
    {
        return $this->createQueryBuilder('r')
            ->where(
                'r.person = :person AND r.periodYear = :periodYear AND r.periodMonth = :periodMonth AND r.context = :context'
            )
            ->setParameters(
                [
                    'person' => $personId,
                    'periodYear' => $periodYear,
                    'periodMonth' => $periodMonth,
                    'context' => RecallDeclaration::CONTEXT_DECLARER,
                ]
            )
            ->getQuery()
            ->getResult();
    }
}