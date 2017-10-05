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

    public function generateNotificationsForActivity( Activity $activity )
    {
        $notifications = [];

        /** @var PersonService $personsService */
        $personsService = $this->getServiceLocator()->get('PersonService');

        /** @var Person[] $persons Liste des personnes impliquées ayant un accès aux Jalons */
        $persons = $personsService->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

        /** @var Person $person */
        foreach ($persons as $person){
            echo "$person\n";
        }

        /** @var ActivityDate $milestone */
        foreach( $activity->getMilestones() as $milestone ){
            echo " --- " . $milestone->getType()->getLabel() . "\n";
            var_dump($milestone->getRecursivityDate());
            echo " - $milestone\n";
        }

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

    public function notification( $message, $personsId, $context='Application', $contextId=-1, $key=null)
    {
        if ($key === null) {
            $key = $context.':'.$contextId;
        }
        $push = [];
        foreach ($personsId as $personid) {
            $hash = md5($personid . '/' . $key);

            try {
                // On commence par regarder si une notification n'existe pas déjà
                // pour cette person dans le context (basé sur le HASH)
                $notification = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);
                if( !$notification ){
                    $date = new \DateTime();
                    $notification = new Notification();
                    $notification->setContext('Application')
                        ->setHash($hash)
                        ->setContext($context)
                        ->setContextId($contextId)
                        ->setDatas([])
                        ->setMessage($message)
                        ->setDateEffective($date)
                        ->setLevel(Notification::LEVEL_INFO)
                        ->setRead(false)
                        ->setRecipientId($personid);
                    $this->getEntityManager()->persist($notification);

                    $push[] = $notification->getId();
                } else {
                    $notification->setDateEffective(new \DateTime());
                }
            } catch ( \Exception $e ){

            }
        }
        $this->getEntityManager()->flush();

        // Push vers le socket si besoin
        $configSocket = $this->getServiceLocator()->get('Config')['oscar']['socket'];
        if (count($push) && $configSocket)
        {
            // todo Faire un truc plus propre pour générer l'URL
            $url = "http://". $_SERVER['SERVER_NAME'].":". $configSocket['port'].$configSocket['push_path'];
            $this->getServiceLocator()->get('Logger')->info("PUSH " . $url);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "ids=" . implode(',', $push));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
        }
    }
}