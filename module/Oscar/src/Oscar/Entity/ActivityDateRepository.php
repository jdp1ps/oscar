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
    //////////////////////////////////////////////////////////////// CORE
    /**
     * @return EntityRepository
     */
    protected function getMilestoneTypeRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(DateType::class);
    }


    /////////////////////////////////////////////////////// Milestone Types (DateType)
    /**
     * Retourne les types de date qui ont une récursivité.
     *
     * @return DateType[]
     */
    public function getMilestoneTypeWithRecursivity(): array
    {
        $query = $this->getMilestoneTypeRepository()->createQueryBuilder('t')
            ->select('t')
            ->where("t.recursivity IS NOT NULL AND t.recursivity != ''");

        return $query->getQuery()->getResult();
    }

    /**
     * @return DateType[]
     */
    public function getMilestoneTypes() :array
    {
        $query = $this->getMilestoneTypeRepository()->createQueryBuilder('t')
            ->select('t');

        return $query->getQuery()->getResult();
    }

    /////////////////////////////////////////////////////// Milestone (ActivityDate)
    /**
     * Retourne les jalons qui se terminent à la date donnée.
     *
     * @param \DateTime $date
     * @return ActivityDate[]
     */
    public function getMilestoneAt(\DateTime $date): array
    {
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.type', 't')
            ->andWhere('m.dateStart = :date') // AND m.dateStart <= :now')
        ;
        $query->setParameters(['date' => $date]);
        return $query->getQuery()->getResult();
    }

    /**
     * Jalons à faire non terminés.
     *
     * @param \DateTime $date
     * @return ActivityDate[]
     */
    public function getMilestonesFinishableUnfinishedAt(\DateTime $date): array
    {
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.type', 't')
            ->andWhere('t.finishable = TRUE')
            ->andWhere('m.dateStart < :date AND m.finished < 100') // AND m.dateStart <= :now')
        ;
        $query->setParameters([
                                  'date' => $date
                              ]);
        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getMilestoneTypesRolesArray() :array
    {
        $output = [];
        foreach ($this->getMilestoneTypes() as $milestoneType) {
            $milestoneId = $milestoneType->getId();
            $output[$milestoneId] = [];
            foreach ($milestoneType->getRoles() as $role) {
                $output[$milestoneId][] = $role->getId();
            }
        }
        return $output;
    }

    /**
     * Retourne les jalons dont une des dates récursives correspond à la date donnée.
     *
     * @param \DateTime $date
     * @return ActivityDate[]
     * @throws \Exception
     */
    public function getMilestoneWithRecursivityMatch(\DateTime $date): array
    {
        $typeRecursive = $this->getMilestoneTypeWithRecursivity();

        // calcules des écarts (calcules des dates en fonction de la récusivité)
        $gaps = [];

        /** @var  $t */
        foreach ($typeRecursive as $t) {
            $repetition = $t->getRecursivityArray();
            $gaps[$t->getId()] = [];
            foreach ($repetition as $days) {
                $gap = sprintf("P%sD", $days);
                $d = new \DateTime($date->format('Y-m-d'));
                $gapedDate = $d->add(new \DateInterval($gap))->format('Y-m-d');
                $gaps[$t->getId()][] = $gapedDate;
            }
        }

        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.type', 't');

        foreach ($gaps as $milestoneId => $dates) {
            $paramId = 'param_id_' . $milestoneId;
            $paramDate = 'param_dates_' . $milestoneId;
            $query->orWhere('m.type = :' . $paramId . ' AND m.dateStart IN(:' . $paramDate . ')');
            $query->setParameter($paramId, $milestoneId);
            $query->setParameter($paramDate, $dates);
        }

        return $query->getQuery()->getResult();
    }


    public function getMilestonesFinishable($status = null)
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
            $fin = ($r->getDateFinish() ? $r->getDateFinish()->format('Y-m-d') : 'Pas fini');
            echo $r->getType()->getId() . " - $r - " . $r->getFinished() . " / " . $fin . "\n";
        }
        echo $query->getDQL();

        die("TOTAL : " . count($result));
    }
}
