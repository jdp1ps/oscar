<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/10/19
 * Time: 15:03
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

class LogActivityRepository extends EntityRepository
{
    public function getLogsProject(int $projectId):array {
        // ID des activités du projet
        $idsActivity = array_map('current', $this->getEntityManager()->createQueryBuilder()
            ->select('a.id')
            ->from(Activity::class, 'a')
            ->where('a.project = :project')
            ->setParameter('project', $projectId)
            ->getQuery()
            ->getResult());

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(LogActivity::class, 'a')
            ->where('a.type NOT IN(\'debug\')')
            ->andWhere("(a.contextId = :id AND a.context LIKE 'Project%') OR (a.contextId IN(:ids) AND a.context LIKE 'Activity%')")
            ->orderBy('a.dateCreated', 'DESC')
            ->setParameters([
                                'ids' => $idsActivity,
                                'id' => $projectId,
                            ]);
        return $qb->getQuery()->getArrayResult();
    }
}