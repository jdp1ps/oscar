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
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ValidationPeriod;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class NotificationService implements UseServiceContainer
{

    use UseServiceContainerTrait;
    // use UseLoggerServiceTrait, UseEntityManagerTrait;
//$s->setLoggerService($container->get('Logger'));
//$s->setEntityManager($container->get(EntityManager::class));
//$s->setOrganizationService($container->get(OrganizationService::class));

    public function getLoggerService(){
        return $this->getServiceContainer()->get('Logger');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManagerService(){
        return $this->getServiceContainer()->get(EntityManager::class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->getEntityManagerService();
    }

    /**
     * @return OrganizationService
     */
    public function getOrganizationService(){
        return $this->getServiceContainer()->get(OrganizationService::class);
    }

    /**
     * @return PersonService
     */
    public function getPersonService(){
        return $this->getServiceContainer()->get(PersonService::class);
    }














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
            ->findBy([
                'object' => Notification::OBJECT_ACTIVITY,
                'objectId' => $activity->getId()
            ], [
                'dateEffective' => 'ASC'
            ]);
    }

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

    private function debug($str)
    {
        $this->getLoggerService()->info($str);
    }

    public function generateNotificationsActivities($silent = false)
    {
        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.dateEnd IS NULL OR a.dateEnd >= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $this->generateNotificationsForActivity($activity);
        }
    }

    /**
     * Génére une liste d'objet "Notification" pour les Jalons pour une activité donnée
     *
     * @param Activity $activity
     */
    public function createOrUpdateMilestoneNotifications(Activity $activity)
    {

    }

    public function generateMilestonesNotificationsForActivity(Activity $activity, $person = null)
    {
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
    public function generateActivityDocumentUploaded( ContractDocument $document ){
        $personToNotify = $this->getPersonsIdFor(Privileges::ACTIVITY_DOCUMENT_SHOW, $document->getGrant());
        $documentText   = $document->getFileName();
        $uploaderText   = (string) $document->getPerson();
        $activityText   = $document->getGrant()->log();

        // Notification de base à la date D
        $message = sprintf("%s a déposé le document %s dans l'activité %s.",
           $uploaderText,
            $documentText,
            $activityText
        );

        $this->getLoggerService()->debug("PERSONNES NOTIFIEES DOCUMENT : " . implode(',', $personToNotify));

        $this->notification($message,
            $personToNotify, Notification::OBJECT_ACTIVITY,
            $document->getGrant()->getId(), "activity", new \DateTime(),
            new \DateTime(), false);
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// JALONS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Génération des notifications pour le jalon $milestone.
     *
     * @param ActivityDate $milestone
     */
    public function generateMilestoneNotifications(ActivityDate $milestone, $person = null)
    {
        $context = "milestone-" . $milestone->getId();
        $activity = $milestone->getActivity();

        $persons = $this->getPersonsIdFor(Privileges::ACTIVITY_MILESTONE_SHOW, $milestone->getActivity());

        if( $person !== null ){
            if( !in_array($person, $persons) ){
                $this->getLoggerService()
                    ->warning(sprintf("La personne %s n'est pas associée à l'activité %s",
                        $person, $milestone->getActivity()));
                return;
            } else {
                $persons = [$person];
            }
        }

        // Si le jalon peut être complété
        if ($milestone->isFinishable()) {

            // Si il est fini, on passe
            if ($milestone->isFinishable() && $milestone->isFinished())
                return;

            // Si il est en retard
            if ($milestone->isLate()) {
                $message = sprintf("Le jalon %s de l'activité %s est en retard.",
                    $milestone->getType()->getLabel(),
                    $activity->log());
                $this->notification($message,
                    $persons, Notification::OBJECT_ACTIVITY,
                    $activity->getId(), $context, new \DateTime('now'),
                    $milestone->getDateStart(), false);
            }
        }

        // Si le jalon est passé, on passe
        if ($milestone->getDateStart() < new \DateTime('now')) {
            return;
        }

        // Notification de base à la date D
        $message = sprintf("Le jalon %s de l'activité %s arrive à échéance",
            $milestone->getType()->getLabel(),
            $activity->log());

        $this->notification($message,
            $persons, Notification::OBJECT_ACTIVITY,
            $activity->getId(), $context, $milestone->getDateStart(),
            $milestone->getDateStart(), false);

        // Les rappels configurés dans le le type de jalon
        foreach ($milestone->getRecursivityDate() as $dateRappel) {
            $this->notification($message, $persons,
                Notification::OBJECT_ACTIVITY, $activity->getId(),
                $context, $dateRappel,
                $milestone->getDateStart(), false);
        }
    }

    public function purgeNotificationMilestone(ActivityDate $milestone)
    {
        $context = "milestone-" . $milestone->getId();
        $notifications = $this->getEntityManager()->getRepository(Notification::class)
            ->findBy(['context' => $context]);
        $this->getLoggerService()->info(sprintf('Purge milestone : %s jalon(s) vont être supprimé(s)', count($notifications)));
        foreach ($notifications as $notification) {
            $this->getEntityManager()->remove($notification);
        }

        $this->getEntityManager()->flush();
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
        foreach ($activity->getPayments() as $payment) {
            $this->generatePaymentsNotifications($payment, $person);
        }
    }

    public function generatePaymentsNotifications(ActivityPayment $payment, $person = null)
    {
        $activity = $payment->getActivity();
        $now = new \DateTime();
        $persons = $this->getPersonsIdFor(Privileges::ACTIVITY_PAYMENT_SHOW, $activity);

        if( $person !== null ){
            if( !in_array($person, $persons) ){
                return;
            } else {
                $persons = [$person];
            }
        }

        if ($payment->getDatePredicted() && $payment->getStatus() == ActivityPayment::STATUS_PREVISIONNEL) {
            $message = "$payment dans l'activité " . $activity->log();
            $context = "payment:" . $payment->getId();
            $dateEffective = $payment->getDatePredicted();

            if ($payment->getDatePredicted() < $now) {
                $message .= " est en retard";
                $dateEffective = $now;
            }

            $this->notification($message, $persons,
                Notification::OBJECT_ACTIVITY, $activity->getId(),
                $context, $dateEffective, $payment->getDatePredicted(), false);
        }
    }

    /**
     * Supprime les notification d'une personne dans une activité
     *
     * @param Activity $activity
     * @param Person $person
     */
    public function purgeNotificationsPersonActivity(Activity $activity, Person $person){
        // objectid = $activityId
        // object = activity
        $query = $this->getEntityManager()->getRepository(NotificationPerson::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.notification', 'n')
            ->where('p.person = :person AND n.object = :object AND n.objectId = :activityid')
            ->setParameters(['person' => $person->getId(), 'activityid' => $activity->getId(), 'object' => Notification::OBJECT_ACTIVITY]);

        /** @var NotificationPerson $r */
        foreach ($query->getQuery()->getResult() as $r ){
            $this->getEntityManager()->remove($r);
        }
        $this->getEntityManager()->flush();

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
    public function purgeNotificationsPersonProject(Project $project, Person $person){
        /** @var Activity $activity */
        foreach ($project->getActivities() as $activity) {
            $this->purgeNotificationsPersonActivity($activity, $person);
        }
    }

    public function purgeNotificationPayment(ActivityPayment $payment)
    {
        $context = "payment:" . $payment->getId();
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
        foreach ($project->getActivities() as $activity ) {
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
            ->setParameters([
                'ids' => $ids,
                'person' => $person,
                'now' => new \DateTime()
            ])
            ->getQuery()
            ->execute();
    }

    public function deleteNotificationsPerson(Person $person){
        return $this->getEntityManager()->getRepository(NotificationPerson::class, 'np')
            ->createQueryBuilder('np')
            ->delete(NotificationPerson::class, 'np')
            ->where('np.person = :person')
            ->setParameters([
                'person' => $person,
            ])
            ->getQuery()
            ->execute();
    }

    public function generateNotificationsPerson(Person $person)
    {
        $this->deleteNotificationsPerson($person);
        
        // Récupération des activités dans lesquelles la personne est impliquée
        $activities = [];

        /** @var OrganizationPerson $member */
        foreach ($person->getOrganizations() as $member) {
            if (!$member->isOutOfDate() && $member->isPrincipal()) {
                /** @var ActivityOrganization $activity */
                foreach ($this->getOrganizationService()->getOrganizationActivititiesPrincipalActive($member->getOrganization()) as $activity) {
                    $this->generateNotificationsForActivity($activity->getActivity(), $person);
                }
            }
        }

        /** @var ActivityPerson $activityPerson */
        foreach ($person->getActivities() as $activityPerson ){
            if($activityPerson->isPrincipal() && !in_array($activityPerson->getActivity(), $activities)){
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
            if( !in_array($serie, $series) ){
//                $this->$this->getLoggerService()->debug('NOTIFICATION : ' . $notificationPerson->getNotification()->getDateEffective()->format('Y-m-d'));
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
                false);
        }

        $this->triggerSocket();
    }

    public function notifyActivitiesTimesheetReject($activities)
    {

    }

    public function generateNotificationValidation( ValidationPeriod $validationperiod ){
        // todo Diffusion des notifications pour les déclarations
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
    public function notification($message, $persons, $object, $objectId, $context, \DateTime $dateEffective, \DateTime $dateReal, $trigger = true)
    {
        // Code de série
        $serie = sprintf('%s:%s:%s', $object, $objectId, $context);



        // Code unique
        $hash = $serie . ':' . $dateEffective->format('Ymd');

        /** @var Notification $notif */
        $notif = $this->getEntityManager()->getRepository(Notification::class)->findOneBy(['hash' => $hash]);

        // Création de la notification
        if (!$notif) {
            $this->getLoggerService()->info(" [+] notification ($serie)");

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
            $this->getLoggerService()->info(" [~] notification ($serie)");
            $notif->setDateReal($dateReal)
                ->setDateEffective($dateEffective);
        }

        $change = $notif->addPersons($persons, $this->getEntityManager());

        $this->addNotificationTrigerrable($notif);

        $this->getEntityManager()->flush();

        if ($trigger === true)
            $this->triggerSocket();
    }
}