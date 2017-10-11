<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\Person;
use Oscar\Provider\Privileges;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class NotificationService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /** @var array Contiens la liste des ID des notifications créée pendant l'exécution */
    private $notificationsToTrigger = [];

    /**
     * Retourne la liste des notifications programmées pour une activités.
     *
     * @param Activity $activity
     * @return Activity[]
     */
    public function notificationsActivity( Activity $activity )
    {
        return $this->getEntityManager()->getRepository(Notification::class)
            ->findBy([
                'object' => Notification::OBJECT_ACTIVITY,
                'objectId' => $activity->getId()
            ], [
                'dateEffective' => 'ASC'
            ]);
    }

    public function addNotificationTrigerrable( Notification $n )
    {
        $this->getServiceLocator()->get('Logger')->info('Ajout à la pile de ' . $n->getId());
        if( !array_search($n->getId(), $this->notificationsToTrigger) ){
            $this->notificationsToTrigger[] = $n->getId();
        }
    }

    public function triggerSocket()
    {
        // Push vers le socket si besoin
        $configSocket = $this->getServiceLocator()->get('Config')['oscar']['socket'];
        if (count($this->notificationsToTrigger) && $configSocket)
        {
            // todo Faire un truc plus propre pour générer l'URL
            $url = $configSocket['url'].$configSocket['push_path'];
            $this->getServiceLocator()->get('Logger')->info("PUSH " . $url);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "ids=" . implode(',', $this->notificationsToTrigger));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
            $this->notificationsToTrigger = [];
        } else {
            $this->getServiceLocator()->get("Logger")->info("PAS DE PUSH !!!");
        }
    }

    /**
     * Retourne la date limite en deça de laquelle les notifications n'ont pas besoin d'être générée.
     *
     * @return \DateTime
     */
    public function getLimitNotificationDate()
    {
        static $limitNotificationDate;
        if( $limitNotificationDate === null ){
            $limit = 30;
            $limitNotificationDate = new \DateTime();
            $interval = new \DateInterval('P'.$limit.'D');
            $interval->invert = 1;
            $limitNotificationDate->add($interval);
        }
        return $limitNotificationDate;
    }

    private function debug($str){
        echo " [debug] $str\n";
    }

    public function generateNotificationsActivities()
    {
        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.dateEnd >= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            echo "# $activity\n";
            /** @var ActivityDate $milestone */
            foreach ($activity->getMilestones() as $milestone) {
                echo " - $milestone \n";
            }

        }

        echo count($activities) . " activité(s)\n";

    }


    public function generateNotificationsForActivity( Activity $activity )
    {
        $notifications = [];

        /** @var PersonService $personsService */
        $personsService = $this->getServiceLocator()->get('PersonService');

        /** @var Person[] $persons Liste des personnes impliquées ayant un accès aux Jalons */
        $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);
        $personsIds = [];

        /** @var Person $person */
        foreach ($persons as $person){
            $this->debug("$person est concernée.");
            $personsIds[] = $person->getId();
        }

        /** @var ActivityDate $milestone */
        foreach( $activity->getMilestones() as $milestone ){
            $context = "milestone-" . $milestone->getId();
            $notificationMessage = sprintf("Échéance '%s' pour '%s'",
                $milestone->getType()->getLabel(),
                $activity->log()
                );
            foreach ($milestone->getRecursivityDate() as $date) {
                if( $date > $this->getLimitNotificationDate() ){
                    $this->notification($notificationMessage, $persons,
                        Notification::OBJECT_ACTIVITY, $activity->getId(),
                        $context, $date, false);
                }
            }
        }

        $this->triggerSocket();
    }

    public function deleteNotifications( array $ids ){
        $query = $this->getEntityManager()->getRepository(Notification::class)->createQueryBuilder('n')
            ->delete()
            ->where('n.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getResult();
        return true;
    }

    public function getNotificationsPerson( $personId ){

        // @Test
        $personId = 5245;

        $result = [
            'personid' => $personId,
            'notifications' => []
        ];

        $notificationsPerson =

            $this->getEntityManager()->getRepository(NotificationPerson::class)
                ->createQueryBuilder('p')
                ->innerJoin('p.notification', 'n')
                ->orderBy('n.dateEffective', 'DESC')
                ->where('p.person = :person AND n.dateEffective <= :now')
                ->setParameters(['person'=> $personId, 'now'=>date('Y-m-d')])
                ->getQuery()
                ->getResult();


        /** @var NotificationPerson $notificationPerson */
        foreach ($notificationsPerson as $notificationPerson ){
            $dt = $notificationPerson->getNotification()->toArray();
            $dt['read'] = $notificationPerson->getRead();
            $dt['person_id'] = $notificationPerson->getPerson()->getId();
            $dt['person'] = (string)$notificationPerson->getPerson();

            $result['notifications'][] = $dt;
        }

        return $result;
    }


    public function notifyActivitiesTimesheetSend( $activities ){

//        /** @var PersonService $personsService */
//        $personsService = $this->getServiceLocator()->get('PersonService');
//
//        /** @var Activity $activity */
//        foreach ($activities as $activity) {
//            $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
//            $this->notification(sprintf("Déclaration en attente de validation dans l'activité %s.", $activity->log()),
//                $persons,
//                'Activity', $activity->getId());
//        }
    }

    public function notifyActivitiesTimesheetReject( $activities ){

    }

    /**
     * @param $message
     * @param $personsId
     * @param string $object
     * @param int $objectId
     * @param string $context
     * @param null $key
     * @param bool $trigger
     */
    public function notification( $message, $persons, $object, $objectId, $context, \DateTime $dateEffective, $trigger=true)
    {
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);

        // Code unique
        $hash = $serie.':'.$dateEffective->format('Ymd');

        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);
        if( !$notif ){
            $notif = new Notification();
            $this->getEntityManager()->persist($notif);
            $notif->setMessage($message)
                ->setDateEffective($dateEffective)
                ->setContext($context)
                ->setObject($object)
                ->setObjectId($objectId)
                ->setSerie($serie)
                ->setHash($hash);

            $notif->addPersons($persons, $this->getEntityManager());
            $this->getEntityManager()->flush();

            if( $trigger === true )
                $this->triggerSocket();
        }
    }
}