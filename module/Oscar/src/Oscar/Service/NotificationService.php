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
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Authentification;
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

    public function getAllNotificationsPerson( $personId )
    {
        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->orderBy('n.dateEffective', 'DESC')
            ->where('p.person = :person')
            ->setParameters(['person'=> $personId]);


        return $query->getQuery()->getResult();
    }
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
        /** @var NotificationPerson $p */
        foreach ($n->getPersons() as $p) {
            $person = $p->getPerson();
            if( $person->getLadapLogin() && !in_array($person->getLadapLogin(), $this->notificationsToTrigger) ){
                $this->notificationsToTrigger[] = $person->getLadapLogin();
            }
        }
    }

    public function triggerSocket()
    {
        // Push vers le socket si besoin
        $configSocket = $this->getServiceLocator()->get('Config')['oscar']['socket'];
        if (count($this->notificationsToTrigger) && $configSocket)
        {
            $this->getServiceLocator()->get('Logger')->info("TRIGGER !");
            $auths = $this->getEntityManager()->getRepository(Authentification::class)->createQueryBuilder('a')
                ->where('a.username IN (:logins)')
                ->setParameter('logins', array_unique($this->notificationsToTrigger))
                ->getQuery()
                ->getResult();

            $keys = [];
            foreach ($auths as $auth) {
                $this->getServiceLocator()->get('Logger')->info($auth);
                $keys[] = $auth->getSecret();
            }
            // todo Faire un truc plus propre pour générer l'URL
            $url = $configSocket['url'].$configSocket['push_path'];
            $this->getServiceLocator()->get('Logger')->info("PUSH " . $url . " WITH " . implode(",", $keys));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "ids=" . implode(',', $keys));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
            //$this->notificationsToTrigger = [];
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

    public function generateNotificationsActivities( $silent = false )
    {
        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.dateEnd >= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $this->generateNotificationsForActivity($activity, $silent);
        }
    }

    /**
     * Génère les notifications pour une activité.
     *
     * @param Activity $activity
     */
    public function generateNotificationsForActivity( Activity $activity , $silent = false)
    {
        /** @var PersonService $personsService */
        $personsService = $this->getServiceLocator()->get('PersonService');

        /** @var Person[] $persons Liste des personnes impliquées ayant un accès aux Jalons */
        $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);
        $personsIds = [];

        /** @var Person $person */
        foreach ($persons as $person){
            $personsIds[] = $person->getId();
        }

        $now = new \DateTime();
        /** @var ActivityPayment $payment */
        foreach( $activity->getPayments() as $payment ){
            if( $payment->getDatePredicted() ){
                $message = "$payment";
                $context = "payment:" . $payment->getId();
                $dateEffective = $payment->getDatePredicted();
                
                if( $payment->getDatePredicted() < $now ){
                    $message .= " est en retard";
                    $dateEffective = $now;
                }

                $this->notification($message, $persons,
                    Notification::OBJECT_ACTIVITY, $activity->getId(),
                    $context, $dateEffective, $payment->getDatePredicted(), false);
            }
        }

        //die("TEST");

        /** @var ActivityDate $milestone */
        foreach( $activity->getMilestones() as $milestone ){
            $context = "milestone-" . $milestone->getId();
            $notificationMessage = sprintf("L'échéance %s pour l'activité %s",
                $milestone->getType()->getLabel(),
                $activity->log()
                );
            foreach ($milestone->getRecursivityDate() as $date) {
                if( $date > $this->getLimitNotificationDate() ){
                    $this->notification($notificationMessage, $persons,
                        Notification::OBJECT_ACTIVITY, $activity->getId(),
                        $context, $date, $milestone->getDateStart(), false);
                }
            }
        }
        if( $silent == true )
            $this->triggerSocket();
    }

    /**
     * Marque les notifications comme lues
     *
     * @param array $ids IDS des Notifications
     * @param Person $person Personne consernée
     * @return mixed
     */
    public function deleteNotificationsPersonById( array $ids, Person $person ){
        return $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->update(NotificationPerson::class, 'np')
            ->set('np.read', ':now')
            ->where('np.notification IN (:ids) AND np.person = :person')
            ->setParameters([
                'ids' => $ids,
                'person' => $person,
                'now' => new \DateTime()
            ])
            ->getQuery()
            ->execute();
    }

    /**
     * @param $personId
     * @return array
     */
    public function getNotificationsPerson( $personId, $onlyFresh = false)
    {
        $result = [
            'personid' => $personId,
            'notifications' => []
        ];

        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
                ->createQueryBuilder('p')
                ->innerJoin('p.notification', 'n')
                ->orderBy('n.dateEffective', 'DESC')
                ->where('p.person = :person AND n.dateEffective <= :now')
                ->setParameters(['person'=> $personId, 'now'=>date('Y-m-d')]);

        if( $onlyFresh === true ){
            $query->andWhere("p.read IS NULL");
        }

        $notificationsPerson = $query->getQuery()->getResult();

        /** @var NotificationPerson $notificationPerson */
        foreach ($notificationsPerson as $notificationPerson ){
            $dt = $notificationPerson->getNotification()->toArray();
            $dt['read'] = $notificationPerson->getRead();
            $dt['person_id'] = $notificationPerson->getPerson()->getId();
            $dt['person'] = (string)$notificationPerson->getPerson();
            $dt['notificationperson_id'] = $notificationPerson->getId();

            $result['notifications'][] = $dt;
        }

        return $result;
    }


    public function notifyActivitiesTimesheetSend( $activities ){

        $this->getServiceLocator()->get('Logger')->info("Notification timesheet send !");

        /** @var PersonService $personsService */
        $personsService = $this->getServiceLocator()->get('PersonService');

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
            $this->notification(
                sprintf("Déclaration en attente de validation dans l'activité %s.", $activity->log()),
                $persons,
                Notification::OBJECT_ACTIVITY,
                $activity->getId(),
                'declarationsend',
                new \DateTime(),
                new \DateTime(),
                false);
        }

        $this->triggerSocket();
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
    public function notification( $message, $persons, $object, $objectId, $context, \DateTime $dateEffective, \DateTime $dateReal, $trigger=true)
    {
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);

        $this->getServiceLocator()->get('Logger')->info("Add $serie");

        // Code unique
        $hash = $serie.':'.$dateEffective->format('Ymd');

        /** @var Notification $notif */
        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);

        // Création de la notification
        if( !$notif ){
            $notif = new Notification();
            $this->getEntityManager()->persist($notif);
            $notif->setMessage($message)
                ->setDateEffective($dateEffective)
                ->setDateReal($dateReal)
                ->setContext($context)
                ->setObject($object)
                ->setObjectId($objectId)
                ->setSerie($serie)
                ->setHash($hash);
        } else {
            $notif->setDateReal($dateReal)
                ->setDateEffective($dateEffective);
        }

        $change = $notif->addPersons($persons, $this->getEntityManager());

        $this->addNotificationTrigerrable($notif);

        $this->getEntityManager()->flush();

        if( $trigger === true )
            $this->triggerSocket();
    }
}