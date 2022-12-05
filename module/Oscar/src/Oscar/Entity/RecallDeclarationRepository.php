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
     * Liste des procédures de rappel générique.
     *
     * @param int $personId
     * @param int|null $year
     * @param int|null $month
     * @param string|null $context
     * @return int|mixed|string
     */
    protected function getRecallDeclarationPersonCore(
        int $personId,
        ?int $year = null,
        ?int $month = null,
        ?string $context
    ) {
        $qb = $this->createQueryBuilder('r')
            ->where('r.person = :person AND r.context = :context ')
            ->setParameters(
                [
                    'person' => $personId,
                    'context' => $context,
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
     * Liste des procédures de rappel pour les validateurs.
     *
     * @param int $personId
     * @param int|null $year
     * @param int|null $month
     * @return RecallDeclaration[]
     */
    public function getRecallValidationPerson(int $personId, ?int $year = null, ?int $month = null)
    {
        return $this->getRecallDeclarationPersonCore($personId, $year, $month, RecallDeclaration::CONTEXT_VALIDATOR);
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
        return $this->getRecallDeclarationPersonCore(
            $personId,
            $periodYear,
            $periodMonth,
            RecallDeclaration::CONTEXT_DECLARER
        );
    }
}