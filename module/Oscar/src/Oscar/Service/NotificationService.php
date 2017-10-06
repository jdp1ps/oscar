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
use Oscar\Entity\Notification;
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
            $personsIds[] = $person->getId();
        }

        /** @var ActivityDate $milestone */
        foreach( $activity->getMilestones() as $milestone ){
            $notificationMessage = sprintf("Échéance '%s' dans '%s'.", $milestone->getType()->getLabel(), $activity->log());
            foreach ($milestone->getRecursivityDate() as $date) {
                if( $date > $this->getLimitNotificationDate() ){
                    $this->notification($notificationMessage, $personsIds, "Activity:milestone".$milestone->getType()->getId().'-'.$date->format('Ymd'), $activity->getId(), $date);
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
        $result = [
            'personid' => $personId,
            'notifications' => []
        ];

        $notifications = $this->getEntityManager()->getRepository(Notification::class)->findBy([
            'recipientId' => $personId
        ]);

        /** @var Notification $notification */
        foreach ($notifications as $notification ){
            $result['notifications'][] = $notification->toArray();
        }

        return $result;
    }


    public function notifyActivitiesTimesheetSend( $activities ){
        /** @var PersonService $personsService */
        $personsService = $this->getServiceLocator()->get('PersonService');

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
            $ids = [];
            foreach ($persons as $person ){
                $ids[] = $person->getId();
            }
            $this->notification(sprintf("Déclaration en attente de validation dans l'activité %s.", $activity->log()),
                $ids,
                'Activity',
                $activity->getId());
        }
    }

    public function notifyActivitiesTimesheetReject( $activities ){

    }

    /**
     * @param $message
     * @param $personsId
     * @param string $context
     * @param int $contextId
     * @param null $key
     * @param bool $trigger
     */
    public function notification( $message, $personsId, $context='Application', $contextId=-1, $dateEffective = null, $key=null, $trigger=true)
    {

        if ($key === null)
            $key = $context.':'.$contextId;

        if( $dateEffective === null )
            $dateEffective = new \DateTime();


        foreach ($personsId as $personid) {
            $hash = md5($personid . '/' . $key);
            $this->getServiceLocator()->get('Logger')->info('ADD notification : ' . $hash." - " . $message);

            try {
                // On commence par regarder si une notification n'existe pas déjà
                // pour cette person dans le context (basé sur le HASH)
                /** @var Notification $notification */
                $notification = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);
                //$this->getServiceLocator()->get("Logger")->info("DUMP " . print_r($notification->toArray()));
                if( !$notification ){
                    $this->getServiceLocator()->get('Logger')->info('CREATE');
                    $notification = new Notification();
                    $notification->setContext('Application')
                        ->setHash($hash)
                        ->setContext($context)
                        ->setContextId($contextId)
                        ->setDatas([])
                        ->setMessage($message)
                        ->setDateEffective($dateEffective)
                        ->setLevel(Notification::LEVEL_INFO)
                        ->setRead(false)
                        ->setRecipientId($personid);
                    $this->getEntityManager()->persist($notification);
                    $this->getServiceLocator()->get("Logger")->info("Création d'une notification $notification");

                    $this->addNotificationTrigerrable($notification);
                } else {
                    $this->getServiceLocator()->get('Logger')->info('UPDATE');
                    $notification->setDateEffective(new \DateTime());
                }
            } catch ( \Exception $e ){
                $this->getServiceLocator()->get('Logger')->error('Notification error : ' . $e->getMessage());
            }
        }
        $this->getEntityManager()->flush();
        if( $trigger === true )
            $this->triggerSocket();

    }
}