<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Oscar\Entity\Notification;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class NotificationService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

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

    public function notification( $message, $personsId, $key=null ){
        if( $key === null ){
            $key = uniqid();
        }
        $push = [];
        foreach ( $personsId as $personid ){
            echo "Envoi à $personid\n";
            $hash = md5($personid.'/'.$key);
            $date = new \DateTime();
            $notification = new Notification();
            $notification->setContext('Application')
                ->setContextId(-1)
                ->setDatas([])
                ->setMessage($message)
                ->setDateEffective($date)
                ->setLevel(Notification::LEVEL_INFO)
                ->setRead(false)
                ->setRecipientId($personid);
            $this->getEntityManager()->persist($notification);

            $this->getEntityManager()->flush($notification);
            $push[] = $notification->getId();
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:3000/push");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "ids=".implode(',', $push));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
    }
}