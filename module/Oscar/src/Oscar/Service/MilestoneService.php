<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 19/03/18
 * Time: 13:16
 */

namespace Oscar\Service;


use Doctrine\ORM\Query;
use Interop\Container\ContainerInterface;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityDateRepository;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\DateType;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Utils\DateTimeUtils;

class MilestoneService implements UseLoggerService, UseEntityManager, UseOscarUserContextService,
                                  UseNotificationService, UseActivityLogService
{

    use UseEntityManagerTrait, UseLoggerServiceTrait, UseOscarUserContextServiceTrait, UseNotificationServiceTrait, UseActivityLogServiceTrait;


    private $serviceContainer;

    public function setServiceContainer($sc)
    {
        $this->serviceContainer = $sc;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return ProjectGrantService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    public function getMilestonesByActivityId($idActivity)
    {
        // Droit d'accès
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);

        return $this->getMiletonesByActivity($activity);
    }

    /**
     * @return Person
     */
    public function getCurrentPerson()
    {
        return $this->getOscarUserContextService()->getCurrentPerson();
    }

    /**
     * @return string
     */
    public function getCurrentPersonText()
    {
        $person = $this->getCurrentPerson();
        if ($person) {
            return $person->log();
        } else {
            $dbUser = $this->getOscarUserContextService()->getUserContext()->getDbUser();
            return 'BD ' . $dbUser->getDisplayName() . '(' . $dbUser->getEmail() . ')';
        }
    }

    /**
     *
     * @param string $format
     * @return mixed
     */
    public function getMilestoneTypes($format = 'object')
    {
        $hydratationMode = Query::HYDRATE_OBJECT;
        if ($format == 'array') {
            $hydratationMode = Query::HYDRATE_ARRAY;
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->orderBy('t.facet')
            ->addOrderBy('t.label')
            ->from(DateType::class, 't');

        $result = $qb->getQuery()->getResult($hydratationMode);

        return $result;
    }

    public function getMilestoneTypeForSelect()
    {
        $output = [];
        /** @var DateType $milestoneType */
        foreach ($this->getMilestoneTypes() as $milestoneType) {
            $facet = $milestoneType->getFacet();
            if (!array_key_exists($facet, $output)) {
                $output[$facet] = [];
            }
            $output[$facet][] = [
                'id' => $milestoneType->getId(),
                'label' => $milestoneType->getLabel()
            ];
        }
        return $output;
    }

    public function getMilestoneTypeFlat()
    {
        $output = [];
        /** @var DateType $milestoneType */
        foreach ($this->getMilestoneTypes() as $milestoneType) {
            $output[$milestoneType->getId()] = $milestoneType->getLabel();
        }
        return $output;
    }

    /**
     * @return mixed
     */
    public function getMilestones()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from(ActivityDate::class, 'm')
            ->orderBy('m.dateStart', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function search($search, $filters)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from(ActivityDate::class, 'm')
            ->orderBy('m.dateStart', 'DESC');

        if ($search) {
            $activitiesIds = $this->getProjectGrantService()->search($search);
            $qb->innerJoin('m.activity', 'a')
                ->where('a.id IN(:ids)')
                ->setParameter('ids', $activitiesIds);
            //$filterIds = $this->
        }

        if ($filters) {
            if (array_key_exists('periodStart', $filters) && $filters['periodStart']) {
                $periodStart = DateTimeUtils::periodBounds($filters['periodStart']);
                $from = $periodStart['start'];
                $to = $periodStart['end'];

                if (array_key_exists('periodEnd', $filters) && $filters['periodEnd']) {
                    $periodEnd = DateTimeUtils::periodBounds($filters['periodEnd']);
                    $to = $periodEnd['end'];
                }

                $qb->andWhere('m.dateStart BETWEEN :from AND :to')
                    ->setParameter('from', $from)
                    ->setParameter('to', $to);
            }

            if (array_key_exists('type', $filters) && $filters['type']) {
                $qb->andWhere('m.type = :typeid')
                    ->setParameter('typeid', $filters['type']);
            }
        }
        return $qb->getQuery()->getResult();
    }


    public function getMiletonesByActivity(Activity $activity)
    {
        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getOscarUserContextService();

        // Check générale du la visibilité
        $oscarUserContext->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

        // Droits plus précis à transmettre aux objets
        $deletable = $editable = $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
        $progression = $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_PROGRESSION, $activity);

        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $activity->getId())->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $out = [];
        $now = new \DateTime();
        foreach ($dates as $data) {
            $data['deletable'] = true;
            $data['past'] = ($data['dateStart'] < $now);
            $data['css'] = ($data['dateStart'] < $now) ? 'past' : '';
            $data['deletable'] = $deletable;
            $data['editable'] = $editable;
            $data['validable'] = $progression;
            $data['isPayment'] = false;

            $out[$data['dateStart']->format('YmdHis') . $data['id']] = $data;
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
     * @param $milestoneId
     * @return ActivityDate
     * @throws OscarException
     */
    public function getMilestone($milestoneId)
    {
        try {
            $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->findOneBy(['id' => $milestoneId]
            );
        } catch (\Exception $e) {
            $message = sprintf(
                "Erreur BDD, Impossible de charger le jalon '%s' : %s !",
                $milestoneId,
                $e->getMessage()
            );
            $this->getLoggerService()->err($message);
            throw new OscarException($message);
        }
        if (!$milestone) {
            throw new OscarException("Ce jalon($milestoneId) est introuvable");
        }
        return $milestone;
    }

    /**
     * Suppression d'un jalon
     * @param $id
     */
    public function deleteMilestoneById($id)
    {
        /** @var ActivityDate $milestone */
        $milestone = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
        if ($milestone) {
            $this->deleteMilestone($milestone);
        }
    }

    /**
     * Suppression d'un jalon
     * @param $id
     */
    public function deleteMilestone(ActivityDate $milestone)
    {
        try {
            $this->getEntityManager()->remove($milestone);
            $this->getEntityManager()->flush();
            $this->getActivityLogService()->addUserInfo(
                sprintf("a supprimé le jalon %s dans  l'activité %s", $milestone, $milestone->getActivity()->log()),
                LogActivity::CONTEXT_ACTIVITY,
                $milestone->getActivity()->getId()
            );
            $this->getNotificationService()->jobUpdateNotificationsActivity($milestone->getActivity());
        } catch (\Exception $e) {
            $msg = "Impossible de supprimer le jalon";
            $this->getLoggerService()->error($msg . " : " . $e->getMessage());
            throw new OscarException("Impossible de supprimer le jalon");
        }
    }

    public function updateFromArray(ActivityDate $milestone, array $dataArray)
    {
        $typeId = $dataArray['type_id'];
        $comment = $dataArray['comment'];
        $date = new \DateTime($dataArray['dateStart']);

        try {
            /** @var DateType $type */
            $type = $this->getEntityManager()
                ->getRepository(DateType::class)->find($typeId);

            if ($milestone->getType()->getId() != $type->getId()) {
                $milestone->setType($type);
            }

            if ($milestone->getDateStart() != $date) {
                $milestone->setDateStart($date);
            }

            $milestone->setComment($comment);

            $this->getEntityManager()->flush($milestone);

            $this->getActivityLogService()->addUserInfo(
                sprintf("a modifié le jalon %s dans l'activité %s", $milestone, $milestone->getActivity()->log()),
                LogActivity::CONTEXT_ACTIVITY,
                $milestone->getActivity()->getId()
            );
            $this->getNotificationService()->jobUpdateNotificationsActivity($milestone->getActivity());

            return $milestone;
        } catch (\Exception $e) {
            return $this->getResponseNotFound("Type de jalon non-trouvé.");
        }
    }

    public function setMilestoneProgression(ActivityDate $milestone, $progressionName)
    {
        switch ($progressionName) {
            case ActivityDate::PROGRESSION_VALID:
                $milestone->setFinished(ActivityDate::VALUE_VALIDED)->setFinishedBy($this->getCurrentPersonText());
                break;

            case ActivityDate::PROGRESSION_UNVALID:
                $milestone->setFinished(ActivityDate::VALUE_TODO)->setFinishedBy('');
                break;

            case ActivityDate::PROGRESSION_INPROGRESS:
                $milestone->setFinished(ActivityDate::VALUE_INPROGRESS)->setFinishedBy($this->getCurrentPersonText());
                break;

            case ActivityDate::PROGRESSION_REFUSED:
                $milestone->setFinished(ActivityDate::VALUE_REFUSED)->setFinishedBy($this->getCurrentPersonText());
                break;

            case ActivityDate::PROGRESSION_CANCEL:
                $milestone->setFinished(ActivityDate::VALUE_CANCELED)->setFinishedBy($this->getCurrentPersonText());
                break;
        }

        $this->getEntityManager()->flush($milestone);

        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a modifié la progression du jalon %s dans l'activité %s",
                $milestone,
                $milestone->getActivity()->log()
            ),
            LogActivity::CONTEXT_ACTIVITY,
            $milestone->getActivity()->getId()
        );
        $this->getNotificationService()->jobUpdateNotificationsActivity($milestone->getActivity());

        return $milestone;
    }

    /**
     * @return ActivityDateRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getMilestoneRepository(): ActivityDateRepository
    {
        return $this->getEntityManager()->getRepository(ActivityDate::class);
    }

    /**
     * @param \DateTime $dateRef
     * @return ActivityDate[]
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getMilestonesAtDate(\DateTime $dateRef): array
    {
        return array_merge(
        // Jalons qui se terminent le jour de la date de référence
            $this->getMilestoneRepository()->getMilestoneAt($dateRef),

            // Jalons dont une date de rappel tombe le jour donné
            $this->getMilestoneRepository()->getMilestoneWithRecursivityMatch($dateRef),

            // Jalons à faire non finalisé
            $this->getMilestoneRepository()->getMilestonesFinishableUnfinishedAt($dateRef)
        );
    }

    /**
     * @param \DateTime $dateRef
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getMilestonesRecallableAtDate(\DateTime $dateRef = new \DateTime(), ?Person $person = null): array
    {
        $activityIds = [];
        if( $person ){
            $activityIds = $this->getProjectGrantService()->getActivityIdsForPerson($person);
        }
        $milestones = [];
        foreach ($this->getMilestonesAtDate($dateRef) as $t) {
            if( $person && !in_array($t->getActivity()->getId(), $activityIds) ){
                continue;
            }
            if (array_key_exists($t->getId(), $milestones)) {
                continue;
            }
            $dt = $t->toJson();

            // nature du rappel
            if ($t->isToday($dateRef)) {
                $dt['nature_recall'] = "TODAY";
            } elseif ($t->isLate($dateRef)) {
                $late = $t->getLateDays($dateRef);
                $dt['nature_recall'] = "LATE ( $late jours)";
            } else {
                $dt['nature_recall'] = "RECALL";
            }
            $milestones[$t->getId()] = $dt;
        }
        return $milestones;
    }

    private static array $_tmp_persons_milestones = [];

    public function getMilestonesRecallableWithPersons(\DateTime $dateRef = new \DateTime()): array
    {
        $out = [];
        $milestonesRoles = $this->computeMilestoneTypesRoles();
        $milestones = $this->getMilestonesAtDate($dateRef);
        $activityPersons = [];
        foreach ($milestones as $milestone) {
            $activityId = $milestone->getActivity()->getId();
            if (!array_key_exists($activityId, $activityPersons)) {
                $persons = $this->getProjectGrantService()->getPersonsRoles($milestone->getActivity());
                $activityPersons[$activityId] = $persons;
            }

            $personsMilestone = $activityPersons[$activityId];
            $milestoneRoleId = $milestone->getType()->getRolesIds();
            $persons = [];

            foreach ($personsMilestone as $personId => $personDetails) {
                if (count(array_intersect($personDetails['role_ids'], $milestoneRoleId))) {
                    $persons[$personId] = (string)$personDetails['person'];
                }
            }
            $dt = $milestone->toJson();
            // nature du rappel
            if ($milestone->isToday($dateRef)) {
                $dt['nature_recall'] = "TODAY";
            } elseif ($milestone->isLate($dateRef)) {
                $late = $milestone->getLateDays($dateRef);
                $dt['nature_recall'] = "LATE ( $late jours)";
            } else {
                $dt['nature_recall'] = "RECALL";
            }

            $dt['persons'] = implode(",", $persons);
            $out[$milestone->getId()] = $dt;
        }
        return $out;
    }

    private static array $_tmp_milestones_roles = [];

    public function computeMilestoneTypesRoles(): array
    {
        if (!self::$_tmp_milestones_roles) {
            self::$_tmp_milestones_roles = $this->getMilestoneRepository()->getMilestoneTypesRolesArray();
        }
        return self::$_tmp_milestones_roles;
    }


    public function createFromArray($dataArray)
    {
        // Récupération du type
        $type = $this->getEntityManager()->getRepository(DateType::class)->find($dataArray['type_id']);
        if (!$type) {
            throw new OscarException("Ce type de jalon est introuvable");
        }

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

        $this->getActivityLogService()->addUserInfo(
            sprintf("a ajouté le jalon %s dans l'activité %s", $milestone, $milestone->getActivity()->log()),
            LogActivity::CONTEXT_ACTIVITY,
            $milestone->getActivity()->getId()
        );
        $this->getNotificationService()->jobUpdateNotificationsActivity($milestone->getActivity());

        return $milestone;
    }
}