<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 19/03/18
 * Time: 13:16
 */

namespace Oscar\Service;


use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\DateType;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MilestoneService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    public function getMilestonesByActivityId( $idActivity ){
        // Droit d'accès
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);

        return $this->getMiletonesByActivity($activity);
    }

    public function getMiletonesByActivity( Activity $activity ){
        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getServiceLocator()->get('OscarUserContext');

        // Check générale du la visibilité
        $oscarUserContext->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

        // Droits plus précis à transmettre aux objets
        $deletable = $editable = $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
        $progression =  $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_PROGRESSION, $activity);

        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $activity->getId())->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $out = [];
        $now = new \DateTime();
        foreach( $dates as $data ){
            $data['deletable'] = true;
            $data['past'] = ($data['dateStart']<$now);
            $data['css'] = ($data['dateStart']<$now) ? 'past' : '';
            $data['deletable'] = $deletable;
            $data['editable'] = $editable;
            $data['validable'] = $progression;
            $data['isPayment'] = false;

            $out[$data['dateStart']->format('YmdHis').$data['id']] = $data;
        }

        //  versements sous la forme JALON
        $versementsQB = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->addSelect('p')
            ->innerJoin('p.activity', 'a')
            ->where('p.status = :status')
            ->andWhere('a.id = :idactivity');

        $versements = $versementsQB->setParameters([
            'idactivity' => $activity->getId(),
            'status' => ActivityPayment::STATUS_PREVISIONNEL
        ])->getQuery()->getResult();

        ksort($out, SORT_STRING);

        return $out;
    }

    /**
     * @return Logger
     */
    protected function getLogger(){
        return $this->getServiceLocator()->get('Logger');
    }

    /**
     * @param $milestoneId
     * @return ActivityDate
     * @throws OscarException
     */
    public function getMilestone( $milestoneId ){
        try {
            $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->findOneBy(['id' => $milestoneId]);
        } catch ( \Exception $e ){
            $message = sprintf("Erreur BDD, Impossible de charger le jalon '%s' : %s !", $milestoneId, $e->getMessage());
            $this->getLogger()->err($message);
            throw new OscarException($message);
        }
        if( !$milestone ){
            throw new OscarException("Ce jalon($milestoneId) est introuvable");
        }
        return $milestone;
    }

    /**
     * Suppression d'un jalon
     * @param $id
     */
    public function deleteMilestoneById( $id ){
        $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
        if( $milestone)
            return $this->deleteMilestone($milestone);
    }

    /**
     * Suppression d'un jalon
     * @param $id
     */
    public function deleteMilestone( ActivityDate $milestone ){
        $this->getNotificationService()->purgeNotificationMilestone($milestone);
        $this->getEntityManager()->remove($milestone);
        $this->getEntityManager()->flush();
    }

    public function updateFromArray( ActivityDate $milestone, array $dataArray ){
        $typeId = $dataArray['type_id'];
        $comment = $dataArray['comment'];
        $date = new \DateTime($dataArray['dateStart']);

        try {
            /** @var DateType $type */
            $type = $this->getEntityManager()
                ->getRepository(DateType::class)->find($typeId);

            // Changement de type
            $rebuildNotifications = false;
            if( $milestone->getType()->getId() != $type->getId() ){
                $rebuildNotifications = true;
                $milestone->setType($type);
            }

            if( $milestone->getDateStart() != $date ){
                $rebuildNotifications = true;
                $milestone->setDateStart($date);
            }


            $milestone
                ->setComment($comment)
            ;

            if( $rebuildNotifications ){
                /** @var NotificationService $notificationService */
                $this->getNotificationService()->purgeNotificationMilestone($milestone);
                $this->getNotificationService()->generateMilestoneNotifications($milestone);
            }

            $this->getEntityManager()->flush($milestone);

            return $milestone;

        } catch (\Exception $e) {
            return $this->getResponseNotFound("Type de jalon non-trouvé.");
        }
    }

    public function setMilestoneProgression( ActivityDate $milestone, $progressionName ){
        if( $progressionName == 'valid' )
            $milestone->setFinished(ActivityDate::FINISH_VALUE);

        else if ($progressionName == 'unvalid')
            $milestone->setFinished(0);

        else
            $milestone->setFinished(50);

        $this->getEntityManager()->flush($milestone);
        $this->getNotificationService()->purgeNotificationMilestone($milestone);
        $this->getNotificationService()->generateMilestoneNotifications($milestone);

        // TODO Mise à jour des notifications en fonction de l'évolution de la progression

        return $milestone;
    }

    public function createFromArray( $dataArray ){

        // Récupération du type
        $type = $this->getEntityManager()->getRepository(DateType::class)->find($dataArray['type_id']);
        if( !$type )
            throw new OscarException("Ce type de jalon est introuvable");

        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($dataArray['activity_id']);

        $comment = $dataArray['comment'];

        $date = new \DateTime($dataArray['dateStart']);

        $milestone = new ActivityDate();

        $this->getEntityManager()->persist($milestone);

        $milestone->setDateStart($date)
            ->setActivity($activity)
            ->setComment($comment)
            ->setType($type);
        $this->getEntityManager()->flush($milestone);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        $notificationService->generateMilestoneNotifications($milestone);

        return $milestone;
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService(){
        return $this->getServiceLocator()->get('NotificationService');
    }
}