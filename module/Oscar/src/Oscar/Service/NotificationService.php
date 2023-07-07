<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:52
 * @copyright Certic (c) 2017
 */

namespace Oscar\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityDateRepository;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\DateType;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\NotificationRepository;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
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
     * @var array
     */
    private $_object_roles_persons = [];

    /**
     * Retourne un tableau de la liste des personnes et leurs rôles en relation avec l'activité $activité.
     *
     * @param Activity $activity
     * @return mixed
     * @throws OscarException
     */
    public function getRolesIdsWithPersonsIds(Activity $activity): array
    {
        $activityId = $activity->getId();
        if (!array_key_exists($activityId, $this->_object_roles_persons)) {
            $this->_object_roles_persons[$activityId] = $this->getPersonService()->getAllPersonsWithRolesInActivity(
                $activity
            );
        }
        return $this->_object_roles_persons[$activityId];
    }

    /**
     * Retourne la liste des IDS des rôles qui peuvent voir les payements.
     *
     * @return array
     * @throws OscarException
     */
    public function getRolesIdConcernedByPayments(): array
    {
        $rolesAllowPayments = $this->getOscarUserContextService()->getRolesWithPrivileges(
            Privileges::ACTIVITY_PAYMENT_SHOW
        );
        return array_map(
            function ($r) {
                return $r->getId();
            },
            $rolesAllowPayments
        );
    }

    private array $_cacheActivityPayementPersons = [];

    /**
     * Retourne les identifiants des personnes concernées par les payments d'une activité.
     *
     * @param Activity $activity
     * @return array
     * @throws OscarException
     */
    protected function getPersonsIdsConcernedByPaymentInActivity(Activity $activity): array
    {
        $activityId = $activity->getId();
        if (!array_key_exists($activityId, $this->_cacheActivityPayementPersons)) {
            $output = [];
            $idsPersonsRoles = $this->getRolesIdsWithPersonsIds($activity);
            $idsRolesConcerned = $this->getRolesIdConcernedByPayments();
            foreach ($idsPersonsRoles as $roleId => $personsIds) {
                if (in_array($roleId, $idsRolesConcerned)) {
                    $output = array_merge($output, $idsPersonsRoles[$roleId]);
                }
            }
            $this->_cacheActivityPayementPersons[$activityId] = array_unique($output);
        }
        return $this->_cacheActivityPayementPersons[$activityId];
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
     * @throws ORMException
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
        Moment::setLocale('fr_FR');

        $this->getLoggerService()->debug("MAJ notification pour $activity");

        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $context = "milestone:" . $milestone->getId();
            $this->getLoggerService()->debug(" - Traitement de $milestone");

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
                $moment = new Moment($milestone->getDateStartStr('Y-m-d'));
                $dateDiff = $moment->from($dateRappel->format('Y-m-d'));
                $delayStr = $dateDiff->getRelative();
                $hashs[] = $this->buildNotificationCore(
                    $message . ' (' . $delayStr . ')',
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
        $this->updateNotificationCorePayment($activity);
        $this->updateNotificationsMilestonePersonActivity($activity);
    }

    public function updateNotificationsProject(Project $project)
    {
        foreach ($project->getActivities() as $activity) {
            $this->updateNotificationsActivity($activity);
        }
    }

    public function updateNotificationsPayment(Notification $notification): void
    {
        /** @var ActivityPayment $payment */
        $payment = $this->getEntityManager()->getRepository(ActivityPayment::class)->find(
            $notification->getContextId()
        );

        if (!$payment) {
            $this->getLoggerService()->alert("Impossible de mettre à jour les notifications pour le payment ($notification)");
            return;
        }

        if( $payment->isDone() ){
            return;
        }

        // TODO calculer si le payment est eligible aux notifications (date passée, fait, en retard, etc...)


        $haveToSub = $this->getPersonsIdsConcernedByPaymentInActivity($payment->getActivity());
        $this->updateSubscribersToNotification($notification, $haveToSub);
    }


    /**
     * Mise à jour des inscriptions des personnes à la notification.
     *
     * @param Notification $notification
     * @param array $expectedSubscribersIds ID des personnes à inscrire
     */
    protected function updateSubscribersToNotification(Notification $notification, array $expectedSubscribersIds): void
    {
        // identifiants des personnes déjà inscrites
        $alreadySub = $notification->getSubscribersIds();

        // Personnes à retirer
        $subscribeToRemove = array_diff($alreadySub, $expectedSubscribersIds);

        // Personnes à ajouter
        $subscribeToDo = array_diff($expectedSubscribersIds, $alreadySub);

        try {
            foreach ($subscribeToDo as $idPerson) {
                $person = $this->getEntityManager()->getRepository(Person::class)->find($idPerson);
                if (!$person) {
                    continue;
                }
                $subscribe = new NotificationPerson();
                $this->getEntityManager()->persist($subscribe);
                $subscribe->setNotification($notification)->setPerson($person);
            }
            $this->getNotificationRepository()->removeNotificationPersons($notification->getId(), $subscribeToRemove);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->getLoggerService()->alert("Impossible de mettre à jour les notifications dans '$notification'");
        }
    }


    /**
     * @param Activity $activity
     * @param $ignorePast
     * @return void
     * @throws ORMException
     * @throws OscarException
     */
    public function updateNotificationsMilestonePersonActivity(Activity $activity, $ignorePast = true)
    {
        $this->getLoggerService()->debug("updateNotificationsMilestonePersonActivity $activity");
        // Liste des notifications Programmées
        $notificationsActivity = $this->getNotificationRepository()->getNotificationsActivity($activity->getId());

        // Personnes devant être inscrites
        $idsPersonsRoles = $this->getRolesIdsWithPersonsIds($activity);

        $now = (new \DateTime('now'))->modify('-1 month');

        //$na = notification
        /** @var Notification $na */
        foreach ($notificationsActivity as $na) {
            $contextNotification = explode(":", $na->getContext());

            if (count($contextNotification) < 2) {
                continue;
            }
            //ActivityDate = Jalon donc Milestone (ancienne nomenclature, terminologie métier)
            $context = $na->getContextKey();
            $contextId = $na->getContextId();

            if ($context == 'payment') {
                try {
                    $this->updateNotificationsPayment($na);
                } catch (\Exception $e) {
                    $this->getLoggerService()->alert(
                        "Un problème est survenu lors de la génération des notifications pour le payment $contextId"
                    );
                }
                continue;
            }

            if ($context != Notification::OBJECT_MILESTONE) {
                continue;
            }

            try {
                $activityDate = $this->getEntityManager()->getRepository(ActivityDate::class)->findOneBy(
                    ["id" => $contextId]
                );
            } catch (\Exception $e) {
                $this->getLoggerService()->alert("Problème de génération de notification pour $na : " . $e->getMessage());
                continue;
            }

            if (!$activityDate) {
                continue;
            }

            //Récupère-les roles associés au jalon (Milestone) grâce au type du jalon (rôles associés au type de jalon)
            $rolesActivityDate = $activityDate->getType()->getRoles();

            // Si pas de rôles on passe directement, pas de calcul de notifications
            if (count($rolesActivityDate) == 0) {
                continue;
            }

            // Si finishable et pas fait
            // TODO
//            if (!$activityDate->isLate()){
//                $this->getLoggerService()->debug(" > Finishable / pas en retard");
//                continue;
//            }

            // Mais la personne ne l'a pas qualifié c'est un cas particulier à prendre en compte dans la feature (ajouter à la condition)
            $isPasted = $ignorePast && ($na->getDateEffective() < $now);
            if ($isPasted) {
                $this->getLoggerService()->debug(" > Passé");
                continue;
            }

            // IDS des roles pour ce type de jalon
            $idsRolesActivityDate = [];

            /** @var  Role $roleActivityDate */
            foreach ($rolesActivityDate as $roleActivityDate) {
                //idRole associé au type de jalon/milestone (ActivityDate)
                $idsRolesActivityDate [] = $roleActivityDate->getId();
            }

            // Comparaison rôles du jalon et rôles des personnes et génération du tableau des personnes concernées
            $idsExpectedSubscribersById = [];

            $rolesAndExpectedSubscribers = $idsPersonsRoles;
            foreach ($rolesAndExpectedSubscribers as $idRole => $arrayIdspersons) {
                if (in_array($idRole, $idsRolesActivityDate)) {
                    foreach ($arrayIdspersons as $key => $idPerson) {
                        if (!in_array($idPerson, $idsExpectedSubscribersById)) {
                            $idsExpectedSubscribersById [] = $idPerson;
                        }
                    }
                }
            }

            $idsPersonInPlace = [];
            // On récupère déjà les personnes inscrites
            /** @var NotificationPerson $inscrit */
            foreach ($na->getPersons() as $inscrit) {
                $idPersonInscrit = $inscrit->getPerson()->getId();
                // Ne devrait pas avoir la notif
                if (!array_key_exists($idPersonInscrit, $idsExpectedSubscribersById)) {
                    $this->getEntityManager()->remove($inscrit);
                } // OK
                else {
                    $idsPersonInPlace[] = $idPersonInscrit;
                }
            }

            $diff = array_diff($idsExpectedSubscribersById, $idsPersonInPlace);
            if (count($diff) > 0) {
                $this->getLoggerService()->debug("Mise à jour des inscrits");
                /** @var int $idPersonToAdd */
                foreach ($diff as $idPersonToAdd) {
                    $personToAdd = $this->getEntityManager()->getRepository(Person::class)->find($idPersonToAdd);
                    $subscribe = new NotificationPerson();
                    $this->getEntityManager()->persist($subscribe);
                    $subscribe->setNotification($na)->setPerson($personToAdd);
                }
            }
        }
        $this->getEntityManager()->flush();
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

    public function deleteNotificationActivityById(int $activityId)
    {
        try {
            $this->getNotificationRepository()->removeNotificationsActivity($activityId);
        } catch (\Exception $e) {
            $this->getLoggerService()->err("Suppression des notifications pour l'activité '$activityId' : "
                . $e->getMessage());
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


    /**
     * Récupère la liste des jalons "terminable" où le status n'est pas modifié afin de notifier les personnes
     * idoines.
     * @param \DateTime $reference
     */
    public function generateActivityMilestonesUncompleted(\DateTime $reference): void
    {
        // TODO
//        /** @var ActivityDateRepository $milestoneRepository */
//        $milestoneRepository = $this->getEntityManager()->getRepository(ActivityDate::class);
//
//        $milestones = $milestoneRepository->getMilestonesFinishable();
//
//        die("Calcule");
    }
}
