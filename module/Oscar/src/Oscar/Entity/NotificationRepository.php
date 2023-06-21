<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * Retourne la liste des notifications d'une activité
     *
     * @param $activityId
     * @return Notification[]
     */
    public function getNotificationsActivity($activityId): array
    {
        $qb = $this->createQueryBuilder("n")
            ->where("n.object = :object AND n.objectId = :objectid")
            ->orderBy('n.dateEffective', 'ASC');

        $qb->setParameters(
            [
                'object' => 'activity',
                'objectid' => $activityId
            ]
        );

        return $qb->getQuery()->getResult();
    }

    public function removeNotificationPersons( int $notificationId, array $personsIds ) :void
    {
        /** @var Notification $notification */
        $notification = $this->getEntityManager()->find(Notification::class, $notificationId);

        /** @var NotificationPerson $notificationPerson */
        foreach ($notification->getPersons() as $notificationPerson) {
            if( in_array($notificationPerson->getPerson()->getId(), $personsIds) ){
                $this->getEntityManager()->remove($notificationPerson);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// NotificationPerson

    /**
     * Retourne la liste des notifications d'une personne.
     *
     * @param int $personId Identifiant de la Person
     * @param bool $active Ne retourne que les notifications effectives (date effective <= now)
     * @param bool $unread ne retourne que les notifications non-lues
     * @return NotificationPerson[]
     */
    public function getNotificationsPerson(int $personId, $active = false, $unread = false): array
    {
        $qb = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->orderBy('n.dateEffective', 'DESC')
            ->where('p.person = :person');
        $parameters = [
            'person' => $personId
        ];

        if ($active === true) {
            $qb->andWhere('n.dateEffective <= :now');
            $parameters['now'] = date('Y-m-d');
        }

        if ($unread === true) {
            $qb->andWhere('p.read IS NULL');
        }

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }


    /**
     * Marque les notifications d'une personne comme lue.
     *
     * @param array $notificationIds
     * @param int $personid
     * @return int|mixed|string
     */
    public function updateNotificationPersonReadNow(array $notificationIds, int $personid)
    {
        $qb = $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->update(NotificationPerson::class, 'np')
            ->set('np.read', ':now')
            ->where('np.notification IN (:ids) AND np.person = :person')
            ->setParameters(
                [
                    'ids' => $notificationIds,
                    'person' => $personid,
                    'now' => new \DateTime()
                ]
            );
        return $qb->getQuery()->execute();
    }

    public function deleteNotificationsPerson(int $personId)
    {
        $qb = $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->delete(NotificationPerson::class, 'np')
            ->where('np.person = :person')
            ->setParameters(
                [
                    'person' => $personId,
                ]
            );
        return $qb->getQuery()->execute();
    }

    public function purgeAll()
    {
        $dql = 'DELETE ' . Notification::class;
        $this->getEntityManager()->createQuery($dql)->getResult();

    }

    protected function getQueryBuilderDeleteBase()
    {
        return $this->getBaseQueryBuilder()
            ->delete();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryBuilder()
    {
        return $this->createQueryBuilder('n');
    }
}