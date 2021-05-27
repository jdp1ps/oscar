<?php
namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class ActivityPcruInfosRepository extends EntityRepository
{
    /**
     * Retourne la liste des ActivityPcruInfos prêtes à être envoyées.
     *
     * @return int|mixed|string
     */
    public function getInfosSendable()
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', ActivityPcruInfos::STATUS_SEND_READY);
        return $qb->getQuery()->getResult();
    }

    public function getInfoActivity( int $activity_id ) :ActivityPcruInfos
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.activity = :activity_id')
            ->setParameter('activity_id', $activity_id);

        return $qb->getQuery()->getSingleResult();
    }
}