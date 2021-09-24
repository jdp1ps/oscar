<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\NotificationRepository;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ValidationPeriod;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use phpDocumentor\Reflection\Types\Iterable_;
use PHPUnit\Framework\Error\Deprecated;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class NotificationService implements UseServiceContainer
{
    use UseServiceContainerTrait;

    /**
     * @return Logger
     */
    public function getLoggerService()
    {
        return $this->getServiceContainer()->get('Logger');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManagerService()
    {
        return $this->getServiceContainer()->get(EntityManager::class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getEntityManagerService();
    }

    /**
     * @return OrganizationService
     */
    public function getOrganizationService()
    {
        return $this->getServiceContainer()->get(OrganizationService::class);
    }

    /**
     * @return PersonService
     */
    public function getPersonService()
    {
        return $this->getServiceContainer()->get(PersonService::class);
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService()
    {
        return $this->getServiceContainer()->get(OscarUserContext::class);
    }

    public function getNotificationRepository(): NotificationRepository
    {
        return $this->getEntityManager()->getRepository(Notification::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    public function getNotificationsToTrigger(): array
    {
        return $this->notificationsToTrigger;
    }

    /**
     * @param array $notificationsToTrigger
     */
    public function setNotificationsToTrigger(array $notificationsToTrigger): void
    {
        $this->notificationsToTrigger = $notificationsToTrigger;
    }

    /** @var array Contiens la liste des ID des notifications créée pendant l'exécution */
    private $notificationsToTrigger = [];

    /**
     * Retourne les notifications d'une personne.
     *
     * @param $personId
     * @return array
     */
    public function getAllNotificationsPerson($personId)
    {
        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->orderBy('n.dateEffective', 'DESC')
            ->where('p.person = :person')
            ->setParameters(['person' => $personId]);

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la liste des notifications programmées pour une activités.
     *
     * @param Activity $activity
     * @return Activity[]
     */
    public function notificationsActivity(Activity $activity)
    {
        return $this->getEntityManager()->getRepository(Notification::class)
            ->findBy(
                [
                    'object' => Notification::OBJECT_ACTIVITY,
                    'objectId' => $activity->getId()
                ],
                [
                    'dateEffective' => 'ASC'
                ]
            );
    }

    /**
     * @param Notification $n
     * @deprecated
     */
    public function addNotificationTrigerrable(Notification $n)
    {
        /** @var NotificationPerson $p */
        foreach ($n->getPersons() as $p) {
            $person = $p->getPerson();
            if ($person->getLadapLogin() && !in_array($person->getLadapLogin(), $this->notificationsToTrigger)) {
                $this->notificationsToTrigger[] = $person->getLadapLogin();
            }
        }
    }

    /**
     * @deprecated
     */
    public function triggerSocket()
    {
        // Push vers le socket si besoin
        $configSocket = false; //$this->getServiceLocator()->get('Config')['oscar']['socket'];
        if (count($this->notificationsToTrigger) && $configSocket) {
            $this->getLoggerService()->info("TRIGGER !");
            $auths = $this->getEntityManager()->getRepository(Authentification::class)->createQueryBuilder('a')
                ->where('a.username IN (:logins)')
                ->setParameter('logins', array_unique($this->notificationsToTrigger))
                ->getQuery()
                ->getResult();

            $keys = [];
            foreach ($auths as $auth) {
                $this->getLoggerService()->info($auth);
                $keys[] = $auth->getSecret();
            }
            // todo Faire un truc plus propre pour générer l'URL
            $url = $configSocket['url'] . $configSocket['push_path'];
            $this->getLoggerService()->info("PUSH " . $url . " WITH " . implode(",", $keys));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "ids=" . implode(',', $keys));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
            //$this->notificationsToTrigger = [];
        } else {
            $this->getLoggerService()->info("PAS DE PUSH !!!");
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
        if ($limitNotificationDate === null) {
            $limit = 30;
            $limitNotificationDate = new \DateTime();
            $interval = new \DateInterval('P' . $limit . 'D');
            $interval->invert = 1;
            $limitNotificationDate->add($interval);
        }
        return $limitNotificationDate;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// GENERATION des NOTIFICATION

    /**
     * Génère les notifications pour toutes les activités
     * @param false $silent
     */
    public function generateNotificationsActivities($silent = false)
    {
        $this->getLoggerService()->debug("[notifications:generate] ALL activities");

        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.dateEnd IS NULL OR a.dateEnd >= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $this->generateNotificationsForActivity($activity);
            $this->generatePaymentsNotificationsForActivity($activity);
        }
    }

    public function generateMilestonesNotificationsForActivity(Activity $activity, $person = null)
    {
        $msg = "[notifications:generate] Activity:" . $activity->getOscarNum();
        if ($person) {
            $msg .= " et Person:" . $person->getId();
        }
        $this->getLoggerService()->debug($msg);
        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $this->generateMilestoneNotifications($milestone, $person);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// CACHE
    private $_object_privilege_persons = [];

    /**
     * Retourne la liste des personnes ayant le privilège $privilège dans l'activité $activité.
     *
     * @param $privilege
     * @param Activity $activity
     */
    public function getPersonsIdFor($privilege, Activity $activity)
    {
        $activityId = $activity->getId();

        if (!array_key_exists($activityId, $this->_object_privilege_persons)) {
            $this->_object_privilege_persons[$activityId] = [];
        }

        if (!array_key_exists($privilege, $this->_object_privilege_persons[$activityId])) {
            $personsIds = [];

            /** @var PersonService $personsService */
            $personsService = $this->getPersonService();

            /** @var Person[] $persons Liste des personnes impliquées ayant un accès aux Jalons */
            $persons = $personsService->getAllPersonsWithPrivilegeInActivity($privilege, $activity);

            $this->_object_privilege_persons[$activityId][$privilege] = $persons;
        }

        return $this->_object_privilege_persons[$activityId][$privilege];
    }


    /**
     * Déclenche l'envoi d'un document lors de l'upload.
     *
     * @param ContractDocument $document
     * @throws \Exception
     */
    public function generateActivityDocumentUploaded(ContractDocument $document)
    {
        $personToNotify = $this->getPersonsIdFor(Privileges::ACTIVITY_DOCUMENT_SHOW, $document->getGrant());
        $documentText = $document->getFileName();
        $uploaderText = (string)$document->getPerson();
        $activityText = $document->getGrant()->log();

        // Notification de base à la date D
        $message = sprintf(
            "%s a déposé le document %s dans l'activité %s.",
            $uploaderText,
            $documentText,
            $activityText
        );

        $this->getLoggerService()->debug("PERSONNES NOTIFIEES DOCUMENT : " . implode(',', $personToNotify));

        $this->notification(
            $message,
            $personToNotify,
            Notification::OBJECT_ACTIVITY,
            $document->getGrant()->getId(),
            "document",
            new \DateTime(),
            new \DateTime(),
            false
        );
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// JALONS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Suppression des notifications liées à un jalon.
     *
     * @param ActivityDate $milestone
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function purgeNotificationMilestone(ActivityDate $milestone)
    {
        $context = "milestone-" . $milestone->getId();
        $this->getLoggerService()->debug("[notifications:purge] " . $context);
        $notifications = $this->getEntityManager()->getRepository(Notification::class)
            ->findBy(['context' => $context]);
        foreach ($notifications as $notification) {
            $this->getEntityManager()->remove($notification);
        }

        $this->getEntityManager()->flush();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RECUPERATION des DONNEES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getNotifiableActivitiesPerson(int $personId)
    {
        $person = $this->getPersonService()->getPersonById($personId, true);

        $roles = $this->getOscarUserContextService()->getRolesWithPrivileges(Privileges::ACTIVITY_MILESTONE_SHOW);
        $out = [];

        // ACTIVITES DIRECTES
        /** @var ActivityPerson $activityPerson */
        foreach ($person->getActivities() as $activityPerson) {
            if (in_array($activityPerson->getRoleObj(), $roles)) {
                $activity = $activityPerson->getActivity();
                if (!array_key_exists($activity->getId(), $out)) {
                    $out[$activity->getId()] = $activity;
                }
            }
        }
        // TODO (Via les projets)

        // TODO (Via les organisations)

        return array_values($out);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// PAYMENTS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Génération des notifications pour les payments.
     *
     * @param Activity $activity
     */
    public function generatePaymentsNotificationsForActivity(Activity $activity, $person = null)
    {
        $this->getLoggerService()->debug(
            "[notifications:generate] ALL activity-payements :" . $activity->getOscarNum()
        );
        foreach ($activity->getPayments() as $payment) {
            $this->generatePaymentsNotifications($payment, $person);
        }
    }

    /**
     * Génération des notifications liées à un payement.
     *
     * @param ActivityPayment $payment
     * @param null $person
     * @deprecated
     */
    public function generatePaymentsNotifications(ActivityPayment $payment, $person = null)
    {
//        $activity = $payment->getActivity();
//        $this->getLoggerService()->debug("[notification:generate] Payement :" . $payment);
//        $now = new \DateTime();
//        $persons = $this->getPersonsIdFor(Privileges::ACTIVITY_PAYMENT_SHOW, $activity);
//
//        if ($person !== null) {
//            if (!in_array($person, $persons)) {
//                return;
//            } else {
//                $persons = [$person];
//                if (count($persons) == 0) {
//                    return;
//                }
//            }
//        }
//
//        if ($payment->getDatePredicted() && $payment->getStatus() == ActivityPayment::STATUS_PREVISIONNEL) {
//            $message = "$payment dans l'activité " . $activity->log();
//            $context = "payment:" . $payment->getId();
//            $dateEffective = $payment->getDatePredicted();
//
//            if ($payment->getDatePredicted() < $now) {
//                $message .= " est en retard";
//                $dateEffective = $now;
//            }
//
//            $this->notification(
//                $message,
//                $persons,
//                Notification::OBJECT_ACTIVITY,
//                $activity->getId(),
//                $context,
//                $dateEffective,
//                $payment->getDatePredicted(),
//                false
//            );
//        }
    }

    protected function buildNotificationCore(
        $message,
        $object,
        $objectId,
        $context,
        \DateTime $dateEffective,
        \DateTime $dateReal
    ){
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);

        // Code unique
        $hash = $serie . ':' . $dateEffective->format('Ymd');


        /** @var Notification $notif */
        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);

        // Création de la notification
        if (!$notif) {
            $this->getLoggerService()->debug("[notification] $hash");

            /** @var Notification $notif */
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
            $this->getLoggerService()->debug(" [ + notifications:update] $hash");
            $notif->setDateReal($dateReal)
                ->setDateEffective($dateEffective);
        }

        $this->getEntityManager()->flush($notif);

        return $notif;
    }

    public function updateNotificationCorePayment( Activity $activity ): void
    {
        $now = new \DateTime();
        $hashs = [];

        foreach ( $activity->getPayments() as $payment ){
            if ($payment->getDatePredicted() && $payment->getStatus() == ActivityPayment::STATUS_PREVISIONNEL) {
                $message = "$payment dans l'activité " . $activity->log();
                $context = "payment:" . $payment->getId();
                $dateEffective = $payment->getDatePredicted();

                if ($payment->getDatePredicted() < $now) {
                    $message .= " est en retard";
                    $dateEffective = $now;
                }

                $hashs[] = $this->buildNotificationCore(
                    $message,
                    Notification::OBJECT_ACTIVITY,
                    $activity->getId(),
                    $context,
                    $dateEffective,
                    $payment->getDatePredicted()
                )->getHash();
            }
        }

        // Suppression
        // Suppression
        $qb = $this->getNotificationRepository()->createQueryBuilder('n')
            ->delete()
            ->where("n.object = :object AND n.objectId = :objectid AND n.hash NOT IN(:hashs) AND n.context LIKE :like");

        $qb->setParameters([
           'object' => Notification::OBJECT_ACTIVITY,
           'objectid' => $activity->getId(),
           'hashs' => $hashs,
           'like' => 'payment:%'
       ]);

        $qb->getQuery()->getResult();

    }
    public function updateNotificationCoreMilestone( Activity $activity ): void
    {
        $hashs = [];

        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {

            $context = "milestone:".$milestone->getId();

            // Si le jalon peut être complété
            if ($milestone->isFinishable()) {

                // Si il est en retard
                if ($milestone->isLate()) {
                    $message = sprintf(
                        "Le jalon %s de l'activité %s est en retard.",
                        $milestone->getType()->getLabel(),
                        $activity->log()
                    );
                    $hashs[] = $this->buildNotificationCore(
                        $message,
                        Notification::OBJECT_ACTIVITY,
                        $activity->getId(),
                        $context,
                        new \DateTime('now'),
                        $milestone->getDateStart()
                    )->getHash();
                }
            }

            // Notification de base à la date D
            $message = sprintf(
                "Le jalon %s de l'activité %s arrive à échéance",
                $milestone->getType()->getLabel(),
                $activity->log()
            );

            $hashs[] = $this->buildNotificationCore(
                $message,
                Notification::OBJECT_ACTIVITY,
                $activity->getId(),
                $context,
                $milestone->getDateStart(),
                $milestone->getDateStart()
            )->getHash();

            // Les rappels configurés dans le le type de jalon
            foreach ($milestone->getRecursivityDate() as $dateRappel) {
                $hashs[] = $this->buildNotificationCore(
                    $message,
                    Notification::OBJECT_ACTIVITY,
                    $activity->getId(),
                    $context,
                    $dateRappel,
                    $milestone->getDateStart()
                )->getHash();
            }
        }

        // Suppression
        $qb = $this->getNotificationRepository()->createQueryBuilder('n')
            ->delete()
            ->where("n.object = :object AND n.objectId = :objectid AND n.hash NOT IN(:hashs) AND n.context LIKE :like");

        $qb->setParameters([
            'object' => Notification::OBJECT_ACTIVITY,
            'objectid' => $activity->getId(),
            'hashs' => $hashs,
            'like' => 'milestone:%'
        ]);

        $qb->getQuery()->getResult();
    }

    public function updateNotificationCore(Activity $activity) :void
    {

        $this->updateNotificationCoreMilestone($activity);
        $this->updateNotificationCorePayment($activity);
    }

    public function jobUpdateNotificationsActivity($activity){
        $client = new \GearmanClient();
        $client->addServer($this->getOrganizationService()->getOscarConfigurationService()->getGearmanHost());

        $this->getLoggerService()->debug(
            "[job:send] jobUpdateNotificationsActivity " . $activity->getOscarNum()
        );

        $client->doBackground(
            'updateNotificationsActivity',
            json_encode(
                [
                    'activityid' => $activity->getId()
                ]
            ),
            'updateNotificationsActivity-' . $activity->getId()
        );
    }

    /**
     * Recalcule les notifications d'une personne sur une activité
     *
     * @param Activity $activity
     * @param Person $person
     */
    public function updateNotificationsActivity(Activity $activity)
    {
        $this->updateNotificationCore($activity);
        $this->updateNotificationsMilestonePersonActivity($activity);

    }

    public function updateNotificationsMilestonePersonActivity(Activity $activity)
    {
        // TODO Recalculer les Notification (objet) à partir des jalons
        //
        $this->getEntityManager()->refresh($activity);

        // Liste des notifications Programmées
        $notificationsActivity = $this->getNotificationRepository()->getNotificationsActivity($activity->getId());

        // Personnes devant être inscrite
        $expectedSubscribers = $this->getPersonsIdFor(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);
        $expectedSubscribersById = [];
        $idsExpectedSubscribers = [];

        /** @var Person $p */
        foreach ($expectedSubscribers as $p) {
            $expectedSubscribersById[$p->getId()] = $p;
            $idsExpectedSubscribers[] = $p->getId();
        }

        $ignorePast = false;
        $now = new \DateTime('now');

        /** @var Notification $na */
        foreach ($notificationsActivity as $na) {
            $idsPersonInPlace = [];

            if( $ignorePast && $na->getDateEffective() < $now ){
                continue;
            }

            /** @var NotificationPerson $inscrit */
            foreach ($na->getPersons() as $inscrit) {
                $idPersonInscrit = $inscrit->getPerson()->getId();

                // Ne devrait pas avoir la notif
                if (!array_key_exists($idPersonInscrit, $expectedSubscribersById)) {
                    $this->getEntityManager()->remove($inscrit);
                } // OK
                else {
                    $idsPersonInPlace[] = $idPersonInscrit;
                }
            }
            $diff = array_diff($idsExpectedSubscribers, $idsPersonInPlace);
            if (count($diff) > 0) {
                foreach ($diff as $idPersonToAdd) {
                    $personToAdd = $expectedSubscribersById[$idPersonToAdd];
                    $subscribe = new NotificationPerson();
                    $this->getEntityManager()->persist($subscribe);
                    $subscribe->setNotification($na)
                        ->setPerson($personToAdd);
                }
            }
        }
        try {
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->getLoggerService()->alert($e->getMessage());
        }
    }


    /**
     * Supprime les notification d'une personne dans une activité
     *
     * @param Activity $activity
     * @param Person $person
     */
    public function purgeNotificationsPersonActivity(Activity $activity, $person = null)
    {
        $msg = "[notifications:purge] activity:" . $activity->getOscarNum();

        //$this->getLoggerService()->debug("[notifications:purge] person/activity ".$person->getId()."/" . $activity->getOscarNum());

        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->where('n.object = :object AND n.objectId = :activityid');

        $parameters = [
            'activityid' => $activity->getId(),
            'object' => Notification::OBJECT_ACTIVITY
        ];

        if ($person != null) {
            $msg .= " / person:" . $person->log();
            $query->andWhere('p.person = :person');
            $parameters['person'] = $person->getId();
        }

        $notificationsDeletable = $query->setParameters($parameters)->getQuery()->getResult();

        if (count($notificationsDeletable) == 0) {
            $msg .= " = Aucune notification à supprimer";
        } else {
            $msg .= " = " . count($notificationsDeletable) . " à supprimer";

            $delete = $this->getEntityManager()->getRepository(NotificationPerson::class)
                ->createQueryBuilder('np')
                ->delete('np')
                ->innerJoin('np.notification', 'n')
                ->where('n.object = activity AND n.objectid = :idactivity AND np.person = :person')
                ->setParameters(
                    [
                        'idactivity' => $activity,
                        'person' => $person
                    ]
                )->getQuery()->getResult();
        }

        $this->getLoggerService()->debug($msg);
    }

    /**
     * Suppression des notifications d'un activité.
     *
     * @param Activity $activity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function purgeNotificationsActivity(Activity $activity)
    {
        $this->getLoggerService()->debug("[notifications:purge] activity :" . $activity->getOscarNum());
        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->where('n.object = :object AND n.objectId = :activityid')
            ->setParameters(['activityid' => $activity->getId(), 'object' => Notification::OBJECT_ACTIVITY]);

        /** @var NotificationPerson $r */
        foreach ($query->getQuery()->getResult() as $r) {
            $this->getEntityManager()->remove($r);
        }
        $this->getEntityManager()->flush();
    }

    public function jobNotificationsPersonProject(Project $project, Person $person)
    {
        $this->getLoggerService()->debug("[job:send] jobNotificationsPersonProject $person / ");
        /** @var Activity $activity */
        foreach ($project->getActivities() as $activity) {
            $this->jobNotificationsPersonActivity($activity, $person);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// PROJET
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Supprime les notifications d'une personne d'un Projet
     *
     * @param Project $project
     * @param Person $person
     */
    public function purgeNotificationsPersonProject(Project $project, Person $person)
    {
        $this->getLoggerService()->debug("[notifications:purge] project " . $project->log());
        /** @var Activity $activity */
        foreach ($project->getActivities() as $activity) {
            $this->purgeNotificationsPersonActivity($activity, $person);
        }
    }

    public function purgeNotificationPayment(ActivityPayment $payment)
    {
        $context = "payment:" . $payment->getId();
        $this->getLoggerService()->debug("[notifications:purge] $context");
        $notifications = $this->getEntityManager()
            ->getRepository(Notification::class)
            ->findBy(['context' => $context]);

        foreach ($notifications as $notification) {
            $this->getEntityManager()->remove($notification);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Génère les notifications pour une activité.
     *
     * @param Activity $activity
     */
    public function generateNotificationsForActivity(Activity $activity, $person = null)
    {
        $this->generateMilestonesNotificationsForActivity($activity, $person);
        $this->generatePaymentsNotificationsForActivity($activity, $person);
    }

    /**
     * Génère les notifications pour un projet.
     *
     * @param Project $project
     * @param null|Person $person
     */
    public function generateNotificationsForProject(Project $project, $person = null)
    {
        $this->getLoggerService()->debug(
            "[notifications:generate] project:" . $project->getId() . " " . $project->getAcronym()
        );
        foreach ($project->getActivities() as $activity) {
            $this->generateNotificationsForActivity($activity, $person);
        }
    }

    /**
     * Marque les notifications comme lues
     *
     * @param array $ids IDS des Notifications
     * @param Person $person Personne consernée
     * @return mixed
     */
    public function deleteNotificationsPersonById(array $ids, Person $person)
    {
        return $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->update(NotificationPerson::class, 'np')
            ->set('np.read', ':now')
            ->where('np.notification IN (:ids) AND np.person = :person')
            ->setParameters(
                [
                    'ids' => $ids,
                    'person' => $person,
                    'now' => new \DateTime()
                ]
            )
            ->getQuery()
            ->execute();
    }

    public function deleteNotificationsPerson(Person $person)
    {
        return $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->delete(NotificationPerson::class, 'np')
            ->where('np.person = :person')
            ->setParameters(
                [
                    'person' => $person,
                ]
            )
            ->getQuery()
            ->execute();
    }

    public function generateNotificationsPerson(Person $person)
    {
        $this->getLoggerService()->debug("[notifications:generate] person:" . $person);
        $this->deleteNotificationsPerson($person);

        // Récupération des activités dans lesquelles la personne est impliquée
        $activities = [];

        /** @var OrganizationPerson $member */
        foreach ($person->getOrganizations() as $member) {
            if (!$member->isOutOfDate() && $member->isPrincipal()) {
                /** @var ActivityOrganization $activity */
                foreach (
                    $this->getOrganizationService()->getOrganizationActivititiesPrincipalActive(
                        $member->getOrganization()
                    ) as $activity
                ) {
                    $this->generateNotificationsForActivity($activity->getActivity(), $person);
                }
            }
        }

        /** @var ActivityPerson $activityPerson */
        foreach ($person->getActivities() as $activityPerson) {
            if ($activityPerson->isPrincipal() && !in_array($activityPerson->getActivity(), $activities)) {
                $this->generateNotificationsForActivity($activityPerson->getActivity(), $person);
            }
        }
    }

    /**
     * @param $personId
     * @return array
     */
    public function getNotificationsPerson($personId, $onlyFresh = false)
    {
        $result = [
            'personid' => $personId,
            'notifications' => []
        ];

        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
//            ->select('MAX(n.dateEffective) as lastedDate')
            ->innerJoin('p.notification', 'n')
//            ->groupBy('n.serie')
            ->orderBy('n.dateEffective', 'DESC')
            ->where('p.person = :person AND n.dateEffective <= :now')
            ->setParameters(['person' => $personId, 'now' => date('Y-m-d')]);

        if ($onlyFresh === true) {
            $query->andWhere("p.read IS NULL");
        }

        $notificationsPerson = $query->getQuery()->getResult();
        $series = [];

        /** @var NotificationPerson $notificationPerson */
        foreach ($notificationsPerson as $notificationPerson) {
            $serie = $notificationPerson->getNotification()->getSerie();
            if (!in_array($serie, $series)) {
                $dt = $notificationPerson->getNotification()->toArray();
                $dt['read'] = $notificationPerson->getRead();
                $dt['person_id'] = $notificationPerson->getPerson()->getId();
                $dt['person'] = (string)$notificationPerson->getPerson();
                $dt['notificationperson_id'] = $notificationPerson->getId();
                $result['notifications'][] = $dt;
                $series[] = $serie;
            }
        }

        return $result;
    }

    public function notifyActivitiesTimesheetSend($activities)
    {
        $this->getLoggerService()->info("Notification timesheet send !");

        /** @var PersonService $personsService */
        $personsService = $this->getPersonService();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $persons = $personsService
                ->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
            $this->notification(
                sprintf("Déclaration en attente de validation dans l'activité %s.", $activity->log()),
                $persons,
                Notification::OBJECT_ACTIVITY,
                $activity->getId(),
                'declarationsend',
                new \DateTime(),
                new \DateTime(),
                false
            );
        }
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
    public function notification(
        $message,
        $persons,
        $object,
        $objectId,
        $context,
        \DateTime $dateEffective,
        \DateTime $dateReal,
        $trigger = true
    ) {
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);


        // Code unique
        $hash = $serie . ':' . $dateEffective->format('Ymd');

        /** @var Notification $notif */
        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);

        // Création de la notification
        if (!$notif) {
            $this->getLoggerService()->debug(" [ /!\ notifications:create] $hash");

            /** @var Notification $notif */
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
            $this->getLoggerService()->debug(" [ /!\ notifications:update] $hash");
            $notif->setDateReal($dateReal)
                ->setDateEffective($dateEffective);
        }

        if( count($persons) ){
            $notif->addPersons($persons, $this->getEntityManager());
        }

        $this->getEntityManager()->flush();
    }
}