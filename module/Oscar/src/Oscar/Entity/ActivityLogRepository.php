<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ActivityLogRepository extends EntityRepository
{

    /**
     * @param $message  Informations sur l'événement à inscrire.
     * @param int $level    Niveau de confidentialité
     * @param string $context Context
     * @param int $contextId ID du context
     * @param int $userId UID
     * @param array $data Données complémentaires (libre)
     *
     * @return LogActivity
     */
    public function addActivity(
                $message,
                $level = LogActivity::DEFAULT_LEVEL,
                $type = LogActivity::DEFAULT_TYPE,
                $context = LogActivity::DEFAULT_CONTEXT,
                $contextId = LogActivity::DEFAULT_CONTEXTID,
                $userId = LogActivity::DEFAULT_USER,
                array $data = null)
    {

        $activity = new LogActivity();
        $activity->setMessage($message)
            ->setType($type)
            ->setLevel($level)
            ->setUserId($userId)
            ->setContext($context)
            ->setContextId($contextId)
            ->setDatas($data)
        ;
        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush($activity);
        error_log($activity);
        return $activity;
    }

    /**
     * Retourne les dernières actions de l'utilisateur.
     *
     * @param $userId
     * @param int $limit
     * @return LogActivity[]
     */
    public function getUserActivity($userId, $limit=20)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.userId = :userId')
            ->andWhere('a.type != \'debug\'')
            ->setMaxResults($limit)
            ->addOrderBy('a.dateCreated', 'DESC')
            ->setParameter('userId', $userId);
        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param int $page
     * @param array $filter
     * @param int $resultByPage
     * @return UnicaenDoctrinePaginator
     */
    public function getActivitiesPaged( $page=1, $filter=array(), $resultByPage=50 )
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->setMaxResults($resultByPage)
            ->addOrderBy('a.dateCreated', 'DESC')
            ;
        return new UnicaenDoctrinePaginator($qb->getQuery(), $page, $resultByPage);
    }
}
