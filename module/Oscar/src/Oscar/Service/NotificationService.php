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

    /**
     * @return GearmanJobLauncherService
     */
    public function getGearmanJobLauncherService(): GearmanJobLauncherService
    {
        return $this->getServiceContainer()->get(GearmanJobLauncherService::class);
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// RECUPERATION des DONNEES

    /**
     * @param $personId
     * @return NotificationPerson[]
     * @see NotificationRepository::getNotificationsPerson()
     */
    public function getAllNotificationsPerson($personId)
    {
        return $this->getNotificationRepository()->getNotificationsPerson($personId);
    }

    /**
     * Retourne la liste des notifications programmées pour une activités.
     *
     * @param Activity $activity
     * @return Activity[]
     */
    public function notificationsActivity(Activity $activity)
    {
        return $this->getNotificationRepository()->getNotificationsActivity($activity->getId());
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

        $activities = $this->getEntityManager()->getRepository(Activity::class)->getActivitiesWithDateEndUnrushed();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $this->updateNotificationsActivity($activity);
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
    public function getPersonsIdFor($privilege, Activity $activity, $forceRecalculate = false)
    {
        $activityId = $activity->getId();

        if ($forceRecalculate) {
            $this->getEntityManager()->refresh($activity);
        }

        if ($forceRecalculate === true || !array_key_exists($activityId, $this->_object_privilege_persons)) {
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

    public function purgeNotificationsAll(): void
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->getEntityManager()->getRepository(Notification::class);

        $notificationRepository->purgeAll();
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


    protected function buildNotificationCore(
        $message,
        $object,
        $objectId,
        $context,
        \DateTime $dateEffective,
        \DateTime $dateReal
    ) {
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);

        // Code unique
        $hash = $serie . ':' . $dateEffective->format('Ymd');


        /** @var Notification $notif */
        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);

        // Création de la notification
        if (!$notif) {
            $this->getLoggerService()->debug("[ + notification] $hash");

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
            $notif->setDateReal($dateReal)
                ->setMessage($message)
                ->setDateEffective($dateEffective);
        }

        $this->getEntityManager()->flush($notif);

        return $notif;
    }

    /**
     * Mise à jour des notifications liées aux payements.
     *
     * @param Activity $activity
     */
    public function updateNotificationCorePayment(Activity $activity): void
    {
        $now = new \DateTime();
        $hashs = [];

        /** @var ActivityPayment $payment */
        foreach ($activity->getPayments() as $payment) {
            $message = "$payment dans l'activité " . $activity->log();
            $context = "payment:" . $payment->getId();

            // Prévisionnel
            if ($payment->getStatus() == ActivityPayment::STATUS_PREVISIONNEL) {
                if ($payment->getDatePredicted() < $now) {
                    $message .= " EST EN RETARD";
                } else {
                    $message .= " EST PREVU";
                }
                $dateNotification = $payment->getDatePredicted();
            } // écart de payment
            else {
                if ($payment->getStatus() == ActivityPayment::STATUS_ECART) {
                    $dateNotification = $payment->getDatePredicted();
                    if ($dateNotification == null) {
                        $dateNotification = $payment->getDatePayment();
                    }
                } // Réalisé
                else {
                    $message .= " EST REALISE";
                    $dateNotification = $payment->getDatePayment();
                }
            }

            if ($dateNotification == null) {
                $this->getLoggerService()->alert(
                    sprintf("Le payment [%s]%s a un problème de date dans %s", $payment->getId(), $payment, $activity)
                );
                continue;
            }

            $hashs[] = $this->buildNotificationCore(
                $message,
                Notification::OBJECT_ACTIVITY,
                $activity->getId(),
                $context,
                $dateNotification,
                $dateNotification
            )->getHash();
        }

        // Suppression
        $qb = $this->getNotificationRepository()->createQueryBuilder('n')
            ->delete()
            ->where("n.object = :object AND n.objectId = :objectid AND n.hash NOT IN(:hashs) AND n.context LIKE :like");

        $qb->setParameters(
            [
                'object' => Notification::OBJECT_ACTIVITY,
                'objectid' => $activity->getId(),
                'hashs' => $hashs,
                'like' => 'payment:%'
            ]
        );

        $qb->getQuery()->getResult();
    }

    public function updateNotificationCoreMilestone(Activity $activity): void
    {
        $hashs = [];

        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $context = "milestone:" . $milestone->getId();

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

        $deleteQuery = $this->getNotificationRepository()->createQueryBuilder('n')
            ->delete()
            ->where("n.object = :object AND n.objectId = :objectid AND n.context LIKE :like");
        $parameters = [
            'object' => Notification::OBJECT_ACTIVITY,
            'objectid' => $activity->getId(),
            'like' => 'milestone:%'
        ];

        if (count($hashs) > 0) {
            $deleteQuery->andWhere('n.hash NOT IN(:hashs)');
            $parameters['hashs'] = $hashs;
        }

        $deleteQuery->getQuery()->setParameters($parameters)->getResult();
    }

    public function updateNotificationCore(Activity $activity): void
    {
        $this->updateNotificationCoreMilestone($activity);
        $this->updateNotificationCorePayment($activity);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    /// JOB (Trigger vers GEARMAN)

    /**
     * @param Project $project
     */
    public function jobUpdateNotificationsProject(Project $project): void
    {
        $this->getGearmanJobLauncherService()->triggerUpdateNotificationProject($project);
    }

    /**
     * @param Activity $activity
     */
    public function jobUpdateNotificationsActivity(Activity $activity): void
    {
        $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    ///

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

    public function updateNotificationsProject(Project $project)
    {
        foreach ($project->getActivities() as $activity) {
            $this->updateNotificationsActivity($activity);
        }
    }

    public function updateNotificationsMilestonePersonActivity(Activity $activity, $ignorePast = true)
    {
        // Liste des notifications Programmées
        $notificationsActivity = $this->getNotificationRepository()->getNotificationsActivity($activity->getId());

        // Personnes devant être inscrite
        $expectedSubscribers = $this->getPersonsIdFor(Privileges::ACTIVITY_MILESTONE_SHOW, $activity, true);

        $expectedSubscribersById = [];
        $idsExpectedSubscribers = [];

        /** @var Person $p */
        foreach ($expectedSubscribers as $p) {
            $expectedSubscribersById[$p->getId()] = $p;
            $idsExpectedSubscribers[] = $p->getId();
        }

        $now = (new \DateTime('now'))->modify('-1 month');

        foreach ($notificationsActivity as $na) {
            // TODO récupérer les rôles des personnes
            // TODO Cas particulier sur le test du jalon, celui-ci est dépassé (terminé) mais la personne ne l'a pas qualifié c'est un cas particulier à prendre en compte dans la feature (ajouter à la condition)
            $isPasted = $ignorePast && ($na->getDateEffective() < $now);
            $idsPersonInPlace = [];

            if ($isPasted) {
                continue;
            }

            /** @var NotificationPerson $inscrit */
            foreach ($na->getPersons() as $inscrit) {
                $idPersonInscrit = $inscrit->getPerson()->getId();

                // TODO faire première requête : "Quel est le jalon, ou en tout cas quel est le type de Jalon concerné par cette notification là ?"
                // TODO Pour chaque personne récupérer les rôles qu'elle possède dans chaque activité et faire un comparatifs entre les rôles de la personnes et les rôles sur ce jalon.
                // TODO Attention une personne possède des rôles sur une activité, sur le projet d'une activité et des rôles sur les organisations actives de l'activité
                // TODO Sur l'objet personne il y a une méthode getOrganizations qui renvoie non pas des organisations, mais renvoie les organisations de personnes

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
                /** @var int $idPersonToAdd */
                foreach ($diff as $idPersonToAdd) {
                    $personToAdd = $expectedSubscribersById[$idPersonToAdd];
                    $subscribe = new NotificationPerson();
                    $this->getEntityManager()->persist($subscribe);
                    $subscribe->setNotification($na)
                        ->setPerson($personToAdd);
                }
            }
        }
        //$this->getEntityManager()->flush();
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
        return $this->getNotificationRepository()->updateNotificationPersonReadNow($ids, $person->getId());
    }

    /**
     * @param Person $person
     * @return int|mixed|string
     */
    public function deleteNotificationsPerson(Person $person)
    {
        return $this->getNotificationRepository()->deleteNotificationsPerson($person->getId());
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

        $notificationsPerson = $this->getNotificationRepository()->getNotificationsPerson($personId, true, $onlyFresh);
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

    public function getNotificationsActivity(Activity $activity)
    {
        return $this->getNotificationRepository()->getNotificationsActivity($activity->getId());
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

        if (count($persons)) {
            $notif->addPersons($persons, $this->getEntityManager());
        }

        $this->getEntityManager()->flush();
    }
}
