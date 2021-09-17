<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Discipline;
use Oscar\Entity\GrantSource;
use Oscar\Entity\ContractType;
use Oscar\Entity\Activity;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\PcruPoleCompetitivite;
use Oscar\Entity\PcruPoleCompetitiviteRepository;
use Oscar\Entity\PcruSourceFinancement;
use Oscar\Entity\PcruSourceFinancementRepository;
use Oscar\Entity\PcruTypeContract;
use Oscar\Entity\PcruTypeContractRepository;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectGrantRepository;
use Oscar\Entity\Role;
use Oscar\Entity\TVA;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Formatter\AsArrayFormatter;
use Oscar\Provider\Privileges;
use Oscar\Strategy\Search\ActivitySearchStrategy;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseActivityTypeService;
use Oscar\Traits\UseActivityTypeServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UsePCRUService;
use Oscar\Traits\UsePCRUServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseProjectServiceTrait;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Oscar\Validator\EOTP;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Runner\Exception;

class ProjectGrantService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService, UseOscarUserContextService,
    UseProjectService, UsePersonService, UseOrganizationService, UseActivityLogService, UseActivityTypeService, UsePCRUService
{
    use UseOscarConfigurationServiceTrait,
        UseActivityLogServiceTrait,
        UseEntityManagerTrait,
        UseLoggerServiceTrait,
        UseOscarUserContextServiceTrait,
        UsePersonServiceTrait,
        UseOrganizationServiceTrait,
        UseActivityTypeServiceTrait,
        UseProjectServiceTrait,
        UsePCRUServiceTrait;


    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES
    ///
    /** @var MilestoneService */
    private $milestoneService;

    /** @var NotificationService */
    private $notificationService;

    /**
     * @return MilestoneService
     */
    public function getMilestoneService(): MilestoneService
    {
        return $this->milestoneService;
    }

    /**
     * @param MilestoneService $milestoneService
     */
    public function setMilestoneService(MilestoneService $milestoneService): void
    {
        $this->milestoneService = $milestoneService;
    }

    /**
     * @return NotificationService
     */
    public function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function setNotificationService(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// REPOSITORY
    /**
     * @return PcruTypeContractRepository
     */
    public function getPcruTypeContratRepository() :PcruTypeContractRepository
    {
        return $this->getEntityManager()->getRepository(PcruTypeContract::class);
    }

    /**
     * @return PcruSourceFinancementRepository
     */
    public function getPcruSourceFinancementRepository() :PcruSourceFinancementRepository
    {
        return $this->getEntityManager()->getRepository(PcruSourceFinancement::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getActivityFull(Activity $activity)
    {
        return $this->getActivityTypeService()->getActivityTypeChain($activity->getActivityType());
    }

    /**
     * Retourne la chemin Complet du type d'activité
     *
     * @param Activity $activity
     * @return string
     */
    public function getActivityFullText(Activity $activity)
    {
        return $this->getActivityTypeService()->getActivityFullText($activity->getActivityType());
    }


    public function getTypeDocument($typeDocumentId, $throw = false)
    {
        $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($typeDocumentId);
        if ($type == null && $throw === true)
            throw new OscarException(sprintf(_("Le type de document %s n'existe pas"), $typeDocumentId));
        return $type;
    }

    public function getWorkPackagePersonPeriod(Person $person, $year, $month)
    {

        // extraction de la période
        $from = sprintf("%s-%s-01", $year, $month);
        $to = sprintf("%s-%s-%s", $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));


        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('wpp')
            ->from(WorkPackagePerson::class, 'wpp')
            ->innerJoin('wpp.workPackage', 'wp')
            ->innerJoin('wp.activity', 'a')
            ->where('wpp.person = :person AND NOT(a.dateEnd < :from OR a.dateStart > :to)')
            ->setParameters([
                'person' => $person,
                'from' => $from,
                'to' => $to
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return ActivityRepository
     */
    protected function getActivityRepository()
    {
        return $this->getEntityManager()->getRepository(Activity::class);
    }

    public function getActivityTypeById($activityTypeId)
    {
        return $this->getEntityManager()->getRepository(ActivityType::class)->find($activityTypeId);
    }


    /**
     * @param $id
     * @param bool $throw
     * @return Activity|null
     * @throws OscarException
     */
    public function getActivityById($id, $throw = true)
    {
        $activity = $this->getActivityRepository()->find($id);
        if (!$activity) {
            if ($throw === TRUE)
                throw new OscarException("Impossible de charger l'activité (ID = $id)");
            else
                return null;
        }
        return $activity;
    }

    /**
     * Retourne la liste des types de documents disponibles pour qualifier les documents dans les activités de
     * recherche.
     *
     * @param bool $asArray
     * @return array|object[]
     */
    public function getTypesDocuments($asArray = true)
    {
        $types = $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
        if ($asArray) {
            $documentTypes = [];
            /** @var TypeDocument $type */
            foreach ($types as $type) {
                $documentTypes[$type->getId()] = $type->getLabel();
            }
        } else {
            $documentTypes = $types;
        }
        return $documentTypes;


    }

    /**
     * Retourne le nombre d'activités total en BDD.
     * @return mixed
     */
    public function getTotalActivitiesInDb()
    {
        $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a');
        $query->select('COUNT(a.id)');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getActivitiesIdsPerson(Person $person)
    {
        $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a');
        $query->select('a.id')
            ->leftJoin('a.persons', 'apers')
            ->leftJoin('apers.person', 'pers1')
            ->leftJoin('a.project', 'aprj')
            ->leftJoin('aprj.members', 'pprs')
            ->leftJoin('pprs.person', 'pers2')
            ->where('pers1 = :person OR pers2 = :person')
            ->setParameter('person', $person);
        $activities = $query->getQuery()->getResult();
        return array_map('current', $activities);
    }

    public function getProjectsIdsSearch($text)
    {
        $query = $this->getEntityManager()->getRepository(Project::class)
            ->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.label LIKE :search OR p.description LIKE :search')
            ->setParameter('search', '%' . $text . '%');
        $projects = $query->getQuery()->getResult();
        return array_map('current', $projects);
    }


    public function getActivityIdsByJalon($jalonTypeId)
    {
        $q = $this->getActivityRepository()->createQueryBuilder('c')
            ->select('c.id')
            ->innerJoin('c.milestones', 'm')
            ->where('m.type = :jalonId')
            ->setParameter('jalonId', $jalonTypeId);

        $activities = $q->getQuery()->getResult();
        return array_map('current', $activities);
    }

    /**
     * @param $ids
     * @param int $page
     * @param int $resultByPage
     * @return array
     */
    public function getActivitiesByIds($ids, $page = 1, $resultByPage = 50)
    {

        $offsetSQL = ($page - 1) * $resultByPage;
        $limitSQL = $resultByPage;

        $query = $this->getEntityManager()->createQueryBuilder('a')
            ->select('a')
            ->from(Activity::class, 'a')
            ->setMaxResults($limitSQL)
            ->setFirstResult($offsetSQL);

        if ($ids !== null) {
            $query->where('a.id IN(:ids)')
                ->setParameter('ids', $ids);
        }

        return $query->getQuery()->getResult();
    }

    public function exportJsonPerson(Person $person)
    {
        $datas = $person->toJson();
        $datas['uid'] = $person->getId();
        return $datas;
    }

    public function exportJsonOrganization(Organization $organization)
    {
        $datas = $organization->toArray();
        $datas['uid'] = $organization->getId();
        return $datas;
    }

    public function exportJsonActivity(Activity $activity)
    {
        $datas = $activity->toArray();
        $datas['uid'] = $activity->getOscarNum();

        $datas['persons'] = [];
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            $role = $activityPerson->getRole();
            if (!array_key_exists($role, $datas['persons'])) {
                $datas['persons'][$role] = [];
            }
            $datas['persons'][$role][] = $activityPerson->getPerson()->getDisplayName();
        }

        $datas['organizations'] = [];
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            $role = $activityOrganization->getRole();
            if (!array_key_exists($role, $datas['organizations'])) {
                $datas['organizations'][$role] = [];
            }
            $datas['organizations'][$role][] = (string)$activityOrganization->getOrganization();
        }

        $datas['payments'] = [];
        /** @var ActivityPayment $payment */
        foreach ($activity->getPayments() as $payment) {
            $datas['payments'][] = [
                'amount' => $payment->getAmount(),
                'date' => $payment->getDatePayment() ? $payment->getDatePayment()->format('Y-m-d') : null,
                'predicted' => $payment->getDatePredicted() ? $payment->getDatePredicted()->format('Y-m-d') : null

            ];
        }

        $datas['milestones'] = [];
        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $type = (string)$milestone->getType();
            $datas['milestones'][] = [
                'type' => $type,
                'date' => $milestone->getDateStart()->format('Y-m-d')
            ];
        }


        return $datas;
    }

    public function getCustomNum()
    {
        static $customNum;
        if ($customNum === null) {
            // Récupération des différentes numérotations
            $customNum = [];

            $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
                ->select('a.numbers')
                ->distinct();
            echo "<pre>";
            foreach ($query->getQuery()->getResult(Query::HYDRATE_ARRAY) as $r) {
                if ($r['numbers']) {
                    foreach ($r['numbers'] as $key => $value) {
                        if (!$value) {
                            echo "$key\n";
                        }
                        if (!in_array($key, $customNum)) {
                            $customNum[] = $key;
                        }
                    }
                }
            }

        }
        return $customNum;

    }

    public function exportJson($object)
    {
        switch (get_class($object)) {
            case Activity::class:
                return $this->exportJsonActivity($object);
        }
    }

    /**
     * @param $id
     * @param OscarUserContext $oscaruserContext
     * @return array
     */
    public function getActivityJson($id, $oscaruserContext)
    {
        /** @var Activity $activity */
        $activity = $this->getActivityRepository()->find($id);

        $datas = [
            'infos' => $activity->toArray()
        ];

        // --- Membres de l'activités
        $datas['persons'] = [
            'readable' => false,
            'editable' => false,
            'datas' => []
        ];
        if ($oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity)) {
            $datas['persons']['readable'] = true;
            $editable = $datas['persons']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity);
            /** @var ActivityPerson $p */
            foreach ($activity->getPersonsDeep() as $p) {
                $person = $p->getPerson();
                $datas['persons']['datas'][$person->getId()] = [
                    'join' => get_class($p),
                    'join_id' => $p->getId(),
                    'displayName' => (string)$person,
                    'main' => $p->isPrincipal(),
                    'role' => $p->getRole(),
                    'editable' => $editable
                ];
            }
        }

        // --- Partenaires de l'activités
        $datas['organizations'] = [
            'readable' => false,
            'editable' => false,
            'datas' => []
        ];
        if ($oscaruserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_SHOW, $activity)) {
            $datas['organizations']['readable'] = true;
            $editable = $datas['organizations']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE, $activity);
            foreach ($activity->getOrganizationsDeep() as $p) {
                $organization = $p->getOrganization();
                $datas['organizations']['datas'][$organization->getId()] = [
                    'join' => get_class($p),
                    'join_id' => $p->getId(),
                    'displayName' => (string)$organization,
                    'role' => $p->getRole(),
                    'editable' => $editable
                ];
            }
        }

        // --- Partenaires de l'activités
        $datas['milestones'] = [
            'readable' => false,
            'editable' => false,
            'datas' => []
        ];
        if ($oscaruserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_SHOW, $activity)) {
            $datas['milestones']['readable'] = true;
            $editable = $datas['milestones']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);

            if ($editable) {
                $datas['milestones']['types'] = $this->getMilestoneTypesArray();
                $datas['milestoneEdit'] = null;
            }
            /** @var ActivityDate $m */
            foreach ($activity->getMilestones() as $m) {
                $datas['milestones']['datas'][$m->getId()] = $m->toArray();
            }
        }

        // --- Partenaires de l'activités
        $datas['payments'] = [
            'readable' => false,
            'editable' => false,
            'datas' => []
        ];
        if ($oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity)) {
            $datas['payments']['readable'] = true;
            $editable = $datas['payments']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity);

            /** @var ActivityPayment $p */
            foreach ($activity->getPayments() as $p) {
                $datas['payments']['datas'][$p->getId()] = $p->toArray();
            }
        }


        return $datas;
    }

    public function getMilestoneTypesArray()
    {
        $milestones = [];
        /** @var DateType $milestoneType */
        foreach ($this->getEntityManager()->getRepository(DateType::class)->findAll() as $milestoneType) {
            $milestones[$milestoneType->getId()] = $milestoneType->toArray();
        }
        return $milestones;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// EXPORT
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getExportComputedFields()
    {
        return $this->getOscarConfigurationService()->getExportComputedFields();
    }

    /**
     * Retourne la liste des champs éligibles à l'export.
     *
     * @return array
     */
    public function getFieldsCSV()
    {

        $headers = [
            'core' => Activity::csvHeaders(),
            'organizations' => [],
            'persons' => [],
            'milestones' => [],
            'numerotation' => $this->getOscarConfigurationService()->getNumerotationKeys(),
            'computed' => []
        ];

        $rolesOrganizationsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('r.label')
            ->from(OrganizationRole::class, 'r')
            ->getQuery()
            ->getResult();

        foreach ($this->getOscarConfigurationService()->getExportComputedFields() as $field) {
            $headers['computed'][] = $field['label'];
        }

        foreach ($rolesOrganizationsQuery as $role) {
            $headers['organizations'][] = $role['label'];
        }

        $rolesOrga = $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray();


        foreach ($rolesOrga as $role) {
            $headers['persons'][] = $role;
        }

        $dateTypes = $this->getEntityManager()->getRepository(DateType::class)->findAll();


        foreach ($dateTypes as $dateType) {
            $headers['milestones'][] = $dateType->getLabel();
        }

        return $headers;
    }

    public function getDistinctNumbersKey()
    {
        static $numbersKey;
        if ($numbersKey === null) {
            $query = $this->getEntityManager()->getRepository(Activity::class)
                ->createQueryBuilder('a')
                ->select('a.numbers')
                ->distinct('a.numbers');

            $key = [];
            foreach ($query->getQuery()->getResult() as $activity) {
                if (is_array($activity['numbers']) && count($activity['numbers'])) {
                    $key = array_merge(array_keys($activity['numbers']), $key);
                }
            }
            $numbersKey = array_unique($key);
        }
        return $numbersKey;
    }

    public function getActivitiesPersonPeriod($personId, $periodStr)
    {
        $period = DateTimeUtils::periodBounds($periodStr);
        $date = new \DateTime($period['end']);
        return $this->getActivityRepository()->getActivitiesPersonDate($personId, $date);
    }

    public function getDistinctNumberKeyUnreferenced()
    {
        $exists = $this->getDistinctNumbersKey();
        $referenced = $this->getOscarConfigurationService()->getOptionalConfiguration('editable.numerotation', []);
        $unique = [];
        foreach ($exists as $key) {
            if (!in_array($key, $referenced)) {
                $unique[] = $key;
            }
        }
        return $unique;
    }

    /**
     * @return array
     */
    public function getActivitiesWithUnreferencedNumbers()
    {

        // Clefs connues
        $authorisedKeys = $this->getOscarConfigurationService()->getNumerotationKeys();
        $this->getLoggerService()->debug("Clefs connues : " . print_r($authorisedKeys, true));

        // Récupération des activités ayant des numérotations
        $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->where('a.numbers IS NOT NULL AND a.numbers != \'N;\' AND a.numbers != \'a:0:{}\'');

        // On isole les activités ayant des clefs de numérotation "Hors configuration"
        $activities = [];

        /** @var Activity $activity */
        foreach ($query->getQuery()->getResult() as $activity) {
            $hasUnknow = false;
            foreach (array_keys($activity->getNumbers()) as $key) {
                if (!in_array($key, $authorisedKeys)) {
                    $this->getLoggerService()->debug("$key n'est pas référencé");
                    $hasUnknow = true;
                }
            }
            if ($hasUnknow === true) {
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    public function getPaymentsByActivityId(array $idsActivity, $organizations = null)
    {
        $query = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->innerJoin('p.activity', 'c')
            ->leftJoin('c.organizations', 'o1')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('pr.partners', 'o2')
            ->where('c.id IN (:ids)');

        $parameters = ['ids' => $idsActivity];

        if ($organizations) {
            $query->andWhere('o1.organization IN (:organizationsIds) OR o2.organization IN (:organizationsIds)');
            $parameters['organizationsIds'] = $organizations;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * Analyse les partenaires d'une activité (ayant un PFI), si l'activité n'a
     * qu'un seul partenaire avec un rôle non-définit (hors composante
     * responsable et tutelle de gestion), elle fixe le rôle sur "Financeur".
     *
     * @param Activity $activity
     * @param bool $flush Flush les modifications via l'entity manager
     * @return bool
     */
    public function setLonelyPartnerAsFinancer(Activity $activity, $flush =
    true)
    {
        if ($activity->getCodeEOTP()) {
            $found = null;

            // Liste des rôles ignorés par le traitement
            $ignoreRoles = [Organization::ROLE_COMPOSANTE_GESTION, Organization::ROLE_COMPOSANTE_RESPONSABLE];

            /** @var ActivityOrganization $organization */
            foreach ($activity->getOrganizations() as $organization) {
                if (in_array($organization->getRole(), $ignoreRoles)) {
                    continue;
                }

                if ($organization->getRole() != "") {
                    return false;
                }

                if ($found !== null) {
                    return false;
                }

                $found = $organization;
            }
            if ($found) {
                $organization->setRole(Organization::ROLE_FINANCEUR);
                if ($flush === true) {
                    $this->getEntityManager()->flush($organization);
                    $this->getActivityLogService()->addInfo(
                        sprintf("OSCAR BOT a définit le rôle 'Financeur' pour le 
                    partenaire %s dans l'activité %s\n",
                            $found->getOrganization()->log(),
                            $found->getActivity()->log()),
                        null,
                        LogActivity::LEVEL_ADMIN, 'Activity', $activity->getId());
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne un QueryBuilder pour obtenir les Projets sur une période.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $field
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivitiesBeetween2dates(\DateTime $from, \DateTime $to, $field = 'dateStart')
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->where('c.' . $field . ' >= :from AND c.' . $field . ' <= :to')
            ->setParameters([
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d')
            ]);
    }

    /**
     * Retourne les activités bientôt terminées. (par défaut, plage de 1 mois).
     *
     * @param string $gap
     * @param string $start
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivityAlmostDone($gap = '+1 month', $start = 'now')
    {
        // Date d'encadrement
        $from = new \DateTime($start);
        $to = new \DateTime($start);
        $to->modify($gap);

        return $this->getActivitiesBeetween2dates($from, $to, 'dateEnd');
    }

    /**
     * @param string $gap
     * @param string $start
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivityBeginsSoon($gap = '+2 weeks', $start = 'now')
    {
        // Date d'encadrement
        $from = new \DateTime($start);
        $to = new \DateTime($start);
        $to->modify($gap);
        return $this->getActivitiesBeetween2dates($from, $to, 'dateStart');
    }


    public function digest()
    {
        foreach ($this->getActivityAlmostDone()->getQuery()->getResult() as $activity) {
            echo "$activity<br/>";
        }
    }

    /**
     * @return Activity[]
     */
    public function getFictiveList()
    {
        $qb = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->setMaxResults(10);
        return $qb->getQuery()->getResult();

    }

    ////////////////////////////////////////////////////////////////////// INDEX
    ////////////////////////////////////////////////////////////////////////////
    private $index;

    protected function searchIndex_getIndex()
    {
        try {
            $path = $this->searchIndex_getPath();
            if ($this->index === null) {
                if (!$this->searchIndex_checkPath()) {
                    $this->index = \Zend_Search_Lucene::create($path);
                    $this->index = \Zend_Search_Lucene::create($path);
                } else {
                    $this->index = \Zend_Search_Lucene::open($path);
                }
                // Lucene configuration globale
                \Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
                \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
            }
            return $this->index;
        } catch (\Zend_Search_Lucene_Exception $e) {
            throw new OscarException("Une erreur est survenu lors de l'accès à l'index de recherche");
        }
    }

    public function searchIndex_addToIndex(Activity $activity)
    {
        $this->getSearchEngineStrategy()->addActivity($activity);
    }

    /**
     * Retourne les jalons de l'activités.
     *
     * @param $idActivity
     * @return array
     */
    public function getMilestones($idActivity)
    {
        return $this->getMilestoneService()->getMilestonesByActivityId($idActivity);
    }

    public function searchIndex_rebuild()
    {
        $this->searchIndex_reset();
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findAll();
        $this->getLoggerService()->info('[INDEX ACTIVITY] Reindex de ' . count($activities) . ' activité(s)');
        return $this->getSearchEngineStrategy()->rebuildIndex($activities);
    }


    public function specificSearch($what, &$qb, $activityAlias = 'c')
    {
        $oscarNumSeparator = $this->getOscarConfigurationService()->getConfiguration('oscar_num_separator');
        $fieldName = uniqid('num_');
        if (preg_match(EOTP::REGEX_EOTP, $what)) {
            $qb->andWhere($activityAlias . '.codeEOTP = :' . $fieldName)
                ->setParameter($fieldName, $what);
        } // Numéro SAIC
        elseif (preg_match("/^[0-9]{4}SAIC.*/mi", $what)) {
            $qb->andWhere($activityAlias . '.centaureNumConvention LIKE :' . $fieldName)
                ->setParameter($fieldName, $what . '%');
        } // La saisie est un numéro OSCAR©
        elseif (preg_match("/^[0-9]{4}$oscarNumSeparator.*/mi", $what)) {
            $qb->andWhere($activityAlias . '.oscarNum LIKE :' . $fieldName)
                ->setParameter($fieldName, $what . '%');
        } // Saisie 'libre'
        else {
            return false;
        }
        return true;
    }


    /**
     * @return ActivitySearchStrategy
     */
    private function getSearchEngineStrategy()
    {
        static $searchStrategy;
        if ($searchStrategy === null) {
            $opt = $this->getOscarConfigurationService()->getConfiguration('strategy.activity.search_engine');
            $class = new \ReflectionClass($opt['class']);
            $searchStrategy = $class->newInstanceArgs($opt['params']);
        }
        return $searchStrategy;
    }

    public function search($what)
    {
        return $this->getSearchEngineStrategy()->search($what);
    }

    public function searchProject($what)
    {
        return $this->getSearchEngineStrategy()->searchProject($what);
    }

    public function searchDelete($id)
    {
        $this->getLoggerService()->info("[INDEX ACTIVTY] Suppression de l'index '$id'");
        $this->getSearchEngineStrategy()->searchDelete($id);
    }

    public function searchUpdate(Activity $activity)
    {
        $this->getLoggerService()->info("[INDEX ACTIVTY] Réindexation de l'activité '$activity'");
        $this->getSearchEngineStrategy()->searchUpdate($activity);
    }

    public function testGearmanError(){
        throw new OscarException("Erreur envoyée depuis un service OSCAR.");
    }

    public function jobSearchUpdate(Activity $activity)
    {
        $client = new \GearmanClient();
        $client->addServer($this->getOscarConfigurationService()->getGearmanHost());

        $gearmanid = sprintf('activitysearchupdate-%s', $activity->getId());
        $this->getLoggerService()->info("[INDEX ACTIVTY] Envoi à gearman : Réindexation de l'activité '$activity'");


        $client->doBackground('activitySearchUpdate', json_encode([
            'activityid' => $activity->getId()
        ]),$gearmanid);
    }

    public function searchIndex_reset()
    {
        $this->getLoggerService()->info("[INDEX ACTIVITY] Remise à zéro de l'index");
        $this->getSearchEngineStrategy()->resetIndex();
    }


    /**
     * @param array $ids
     * @return Activity[]
     */
    public function activitiesByIds(array $ids)
    {
        return $this->getBaseQuery()
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $idActivitypayment
     * @param bool $throw
     * @return ActivityPayment|null
     * @throws OscarException
     */
    public function getActivityPaymentById($idActivitypayment, $throw = true): ?ActivityPayment
    {
        $activitypayment = $this->getEntityManager()->getRepository(ActivityPayment::class)->find($idActivitypayment);
        if (!$activitypayment && $throw) {
            throw new OscarException("Le payment $idActivitypayment est introuvable !");
        }
        return $activitypayment;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getActivityTypes($asArray = false)
    {
        return $this->getActivityTypeService()->getActivityTypes($asArray);
    }

    public function getActivityTypesTree($asArray = false)
    {
        return $this->getActivityTypeService()->getActivityTypesTree($asArray);
    }

    public function getActivityTypesPcru()
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        return $repository->getArrayDatasJoined();
    }

    public function pcruUpdateAssociateTypeContract(int $idTypeActivity, int $idPcruContractType)
    {
        $this->getLoggerService()->debug("Association de type de contract OSCAR:$idTypeActivity pour PCRU:$idPcruContractType");
        /** @var ActivityType $activityType */
        $activityType = $this->getEntityManager()->getRepository(ActivityType::class)->find($idTypeActivity);

        /** @var PcruTypeContract $pcruContractType */
        $pcruContractType = $this->getEntityManager()->getRepository(PcruTypeContract::class)->find($idPcruContractType);

        $pcruContractType->setActivityType($activityType);

        $this->getEntityManager()->flush();
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getStatus()
    {
        return Activity::getStatusSelect();
    }

    public function getStatusByKey($key)
    {
        return Activity::getStatusSelect()[$key];
    }

    /**
     * Retourne la liste des TVAs supportées dans Oscar.
     *
     * @return TVA[]
     */
    public function getTVAs()
    {
        return $this->getEntityManager()->getRepository(TVA::class)->findAll();
    }

    public function getTVAsValuesOptions()
    {
        $out = [];
        foreach ($this->getTVAsForJson() as $tva) {
            $out[$tva['id']] = $tva['label'] . ($tva['active'] ? '' : ' (Obsolète)');
        }
        return $out;
    }

    public function getTVAsForJson()
    {
        try {
            $query = $this->getEntityManager()->getRepository(TVA::class)->createQueryBuilder('t')
                ->select('t.id, t.label, t.rate, t.active AS active, count(a) as used')
                ->groupBy('t.id')
                ->orderBy('t.rate')
                ->leftJoin(Activity::class, 'a', 'WITH', 't.id = a.tva');

            $tvas = [];
            foreach ($query->getQuery()->getResult() as $tva) {
                $tvas[] = [
                    'id' => $tva['id'],
                    'label' => $tva['label'],
                    'rate' => $tva['rate'],
                    'active' => $tva['active'],
                    'used' => $tva['used'],
                ];
            }

            return $tvas;
        } catch (\Exception $e) {
            throw new OscarException($e->getMessage());
        }

    }

    /**
     * Retourne la liste des types de dates pour alimenter un Select.
     *
     * @return array
     */
    public function getDateTypesSelect()
    {
        $datas = $this->getQueryBuilderDateType()
            ->getQuery()
            ->getResult();

        $options = [];
        $currentGroup = null;

        /** @var DateType $data */
        foreach ($datas as $data) {
            $facet = $data->getFacet() ? $data->getFacet() : 'Général';
            if (!isset($options[$facet])) {
                $options[$facet] = [
                    'label' => $facet,
                    'options' => []
                ];
            }

            $options[$facet]['options'][$data->getId()] = $data->getLabel() . ($data->getDescription() ? sprintf(' (%s)', $data->getDescription()) : '');
        }

        return $options;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderDateType()
    {
        return $this->getEntityManager()->getRepository(DateType::class)
            ->createQueryBuilder('d')
            ->orderBy('d.facet', 'ASC')
            ->orderBy('d.label', 'ASC');
    }

    /**
     * @param $id
     * @return DateType
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDateType($id)
    {
        return $this->getQueryBuilderDateType()
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getSingleResult();
    }

    public function duplicate(Activity $source, $options)
    {
        $qb = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.type', 't')
            ->leftJoin('a.tva', 'tv')
            ->leftJoin('a.currency', 'c')
            ->leftJoin('a.project', 'p')
            ->leftJoin('a.persons', 'pe')
            ->leftJoin('pe.person', 'pr')
            ->leftJoin('a.organizations', 'or')
            ->leftJoin('or.organization', 'og')
            ->where('a.id = :id');

        /** @var Activity $source */
        $source = $qb->setParameter('id', $source->getId())->getQuery()->getSingleResult();

        $newActivity = new Activity();

        $this->getEntityManager()->persist($newActivity);

        $newActivity->setProject($source->getProject())
            ->setType($source->getType())
            ->setTva($source->getTva())
            ->setCurrency($source->getCurrency())
            ->setLabel('Copie de ' . $source->getLabel())
            ->setDescription('')
            ->setAmount(0.0);

        if ($options['admdata']) {
            $newActivity->setAmount($source->getAmount())
                ->setAssietteSubventionnable($source->getAssietteSubventionnable())
                ->setFinancialImpact($source->getFinancialImpact())
                ->setDescription($source->getDescription())
                ->setStatus($source->getStatus())
                ->setDateOpened($source->getDateOpened())
                ->setDateSigned($source->getDateSigned())
                ->setNoteFinanciere($source->getNoteFinanciere())
                //->setDateStart($source->getDateStart())
                ->setDateEnd($source->getDateEnd())
                ->setCodeEOTP($source->getCodeEOTP());
            $newActivity->setFraisDeGestion($source->getFraisDeGestion());
            $newActivity->getAssietteSubventionnable($source->getAssietteSubventionnable());

        }

        $this->getEntityManager()->flush($newActivity);

        if ($options['organizations']) {
            /** @var ActivityOrganization $partner */
            foreach ($source->getOrganizations() as $partner) {
                $newPartner = new ActivityOrganization();
                $this->getEntityManager()->persist($newPartner);
                $newPartner->setOrganization($partner->getOrganization())
                    ->setRoleObj($partner->getRoleObj())
                    ->setActivity($newActivity)
                    ->setDateStart($partner->getDateStart())
                    ->setDateEnd($partner->getDateEnd());
                $this->getEntityManager()->flush($newPartner);
            }
        }

        if ($options['persons']) {
            /** @var ActivityPerson $member */
            foreach ($source->getPersons() as $member) {
                $newMember = new ActivityPerson();
                $this->getEntityManager()->persist($newMember);
                $newMember->setPerson($member->getPerson())
                    ->setActivity($newActivity)
                    ->setRoleObj($member->getRoleObj())
                    ->setDateStart($member->getDateStart())
                    ->setDateEnd($member->getDateEnd());
                $this->getEntityManager()->flush($newMember);
            }
        }

        if ($options['milestones']) {
            /** @var ActivityDate $milestone */
            foreach ($source->getMilestones() as $milestone) {
                $new = new ActivityDate();
                $this->getEntityManager()->persist($new);
                $new->setStatus($milestone->getStatus())
                    ->setActivity($newActivity)
                    ->setDateStart($milestone->getDateStart())
                    ->setDateFinish($milestone->getDateFinish())
                    ->setType($milestone->getType())
                    ->setComment($milestone->getComment());
                $this->getEntityManager()->flush($new);
            }
        }

        if ($options['workpackages']) {

            /** @var WorkPackage $workpackage */
            foreach ($source->getWorkPackages() as $workpackage) {
                $new = new WorkPackage();
                $this->getEntityManager()->persist($new);
                $new->setCode($workpackage->getCode())
                    ->setLabel($workpackage->getLabel())
                    ->setActivity($newActivity)
                    ->setDateStart($workpackage->getDateStart())
                    ->setDateEnd($workpackage->getDateEnd());

                $this->getEntityManager()->flush($new);

                /** @var WorkPackagePerson $workpackagePerson */
                foreach ($workpackage->getPersons() as $workpackagePerson) {
                    $wpPerson = new WorkPackagePerson();
                    $this->getEntityManager()->persist($wpPerson);
                    $wpPerson->setPerson($workpackagePerson->getPerson())
                        ->setDuration($workpackagePerson->getDuration())
                        ->setWorkPackage($new);

                    $this->getEntityManager()->flush($wpPerson);
                }
            }
        }

        $newActivity->setDateStart($source->getDateStart());
        $this->getEntityManager()->flush($newActivity);

        return $newActivity;

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// ACTIVITY PAYMENT
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $idActivityPayment
     * @return null|ActivityPayment
     */
    public function getActivityPayment($idActivityPayment)
    {
        return $this->getEntityManager()->getRepository(ActivityPayment::class)->find($idActivityPayment);
    }

    /**
     * @param ActivityPayment $activityPayment
     * @param bool|true $throw
     * @return bool
     * @throws \Exception
     */
    public function deleteActivityPayment(ActivityPayment $activityPayment, $throw = true)
    {
        try {
            $activityPayment->getActivity()->touch();
            $this->getEntityManager()->remove($activityPayment);
            $this->getEntityManager()->flush();
            $this->getActivityLogService()->addUserInfo(sprintf("a supprimer le verserment de %s %s sur l'activité %s", $activityPayment->getAmount(), $activityPayment->getCurrency(), $activityPayment->getActivity()->log()));
            return true;
        } catch (\Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            if ($throw) {
                throw new OscarException(sprintf("Impossible de supprimer le versement '%s'.", $activityPayment->getId()));
            }
            return false;
        }
    }

    public function updateActivityPayment($data)
    {
        /** @var ActivityPayment $payment */
        $payment = $this->getActivityPayment($data['id']);

        $payment->setAmount($data['amount'])
            ->setComment($data['comment'])
            ->setCodeTransaction($data['codeTransaction'])
            ->setCurrency($this->getEntityManager()
                ->getRepository(Currency::class)
                ->find($data['currencyId']));


        $status = $data['status'];
        $rate = $data['rate'];
        $datePredicted = $data['datePredicted'];
        $datePayment = $data['datePayment'];

        if ($datePayment)
            $payment->setDatePayment(new \DateTime($datePayment));
        else
            $payment->setDatePayment(null);

        if ($datePredicted)
            $payment->setDatePredicted(new \DateTime($datePredicted));
        else
            $payment->setDatePredicted(null);
        $payment->setRate($rate)
            ->setStatus($status);
        $this->getEntityManager()->flush($payment);
        $this->getNotificationService()->purgeNotificationPayment($payment);
        $this->getNotificationService()->generatePaymentsNotifications($payment);
        return $payment;
    }

    public function addNewActivityPayment($datas, $notification = true)
    {

        $payment = new ActivityPayment();

        // TODO Vérifier les données de création du nouveau payment

        $this->getEntityManager()->persist($payment);

        $payment->setAmount($datas['amount'])
            ->setComment($datas['comment'])
            ->setActivity($datas['activity'])
            ->setCodeTransaction($datas['codeTransaction'])
            ->setCurrency($this->getEntityManager()
                ->getRepository(Currency::class)
                ->find($datas['currencyId']));


        $status = $datas['status'];
        $rate = $datas['rate'];
        $datePredicted = $datas['datePredicted'];
        $datePayment = $datas['datePayment'];

        if ($datePayment)
            $payment->setDatePayment(new \DateTime($datePayment));
        else
            $payment->setDatePayment(null);

        if ($datePredicted)
            $payment->setDatePredicted(new \DateTime($datePredicted));
        else
            $payment->setDatePredicted(null);

        $payment->setRate($rate)
            ->setStatus($status);


        $this->getEntityManager()->flush($payment);
        $this->getNotificationService()->generatePaymentsNotifications($payment);
        return $payment;
    }

    public function getListActivityPaymentByActivity(Activity $activity, $format = 'array')
    {
        $qb = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->addSelect('c')
            ->innerJoin('p.activity', 'a')
            ->leftJoin('p.currency', 'c')
            ->where('a.id = :idactivity')
            ->orderBy('p.status', 'DESC')
            ->addOrderBy('p.datePayment');

        $qb->setParameter('idactivity', $activity->getId());


        $hydratationMode = Query::HYDRATE_OBJECT;
        if ($format == 'array') {
            $hydratationMode = Query::HYDRATE_ARRAY;
        }

        $result = $qb->getQuery()->getResult($hydratationMode);

        return $result;
    }

    public function getListActivityPayment($search = '', $page = 1)
    {
        $qb = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->addSelect('c, COALESCE(p.datePredicted, p.datePayment) as HIDDEN dateSort')
            ->innerJoin('p.activity', 'a')
            ->innerJoin('p.currency', 'c')
            ->addOrderBy('dateSort', 'DESC');

        if ($search) {
            if (!$this->specificSearch($search, $qb, 'a')) {
                $ids = $this->search($search);
                $qb->andWhere('a.id in (:ids)')
                    ->setParameter('ids', $ids);
            }
        }

        return [
            'search' => $search,
            'payments' => new UnicaenDoctrinePaginator($qb, $page, 50)
        ];
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // ActivityDate (échéance)
    //
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Retourne l'échéance.
     *
     * @param $id Identifiant de l'échéance
     * @param bool|true $throw
     * @return null|object
     * @throws \Exception
     */
    public function getActivityDate($id, $throw = true)
    {
        $activityDate = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
        if ($throw === true && !$activityDate) {
            throw new \Exception(sprintf("Échéance '%s' introuvable", $id));
        }
        return $activityDate;
    }

    /**
     * Supprime l'échéance.
     *
     * @param ActivityDate $activityDate
     * @param bool|true $throw
     * @return bool
     * @throws \Exception
     */
    public function deleteActivityDate(ActivityDate $activityDate, $throw = true)
    {
        try {
            $this->getEntityManager()->remove($activityDate);
            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $e) {
            if ($throw) {
                throw new \Exception(sprintf("Impossible de supprimer l'échéance '%s'.", $activityDate->getId()));
            }
        }
    }



    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $startAt
     * @param null $endAt
     * @return ActivityPayment[]
     */
    public function getPaymentsIncoming($startAt = 'now', $endAt = null)
    {
        $start = new \DateTime($startAt);
        $end = new \DateTime($endAt ? $endAt : 'now');
        if (!$endAt) {
            $end->modify('+1 month');
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(ActivityPayment::class, 'p')
            ->where('p.datePredicted >= :start AND p.datePredicted <= :end AND p.status = :status')
            ->orderBy('p.datePredicted', 'desc')
            ->setParameters([
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'status' => ActivityPayment::STATUS_PREVISIONNEL,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getPaymentsLate($now = 'now')
    {
        $date = new \DateTime($now);
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(ActivityPayment::class, 'p')
            ->where('p.datePredicted < :date AND p.status = :status')
            ->orderBy('p.datePredicted', 'desc')
            ->setParameters([
                'date' => $date->format('Y-m-d'),
                'status' => ActivityPayment::STATUS_PREVISIONNEL,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getPaymentsDifference()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(ActivityPayment::class, 'p')
            ->where('p.status = :status')
            ->orderBy('p.dateCreated', 'desc')
            ->setParameters([
                'status' => ActivityPayment::STATUS_ECART,
            ]);

        return $qb->getQuery()->getResult();
    }



    ////////////////////////////////////////////////////////////////////////////

    /**
     * Retourne la TVA.
     *
     * @param $currencyId
     * @return null|TVA
     */
    public function getTVA($tvaId)
    {
        return $this->getEntityManager()->getRepository(TVA::class)->find($tvaId);
    }

    /**
     * Retourne la liste des devises supportées dans Oscar.
     *
     * @return Currency[]
     */
    public function getCurrencies($asArray = false)
    {
        $currencies = $this->getEntityManager()->getRepository(Currency::class)->findAll();
        if ($asArray === true) {
            $result = [];
            /** @var Currency $currency */
            foreach ($currencies as $currency) {
                $result[] = $currency->asArray();
            }
            return $result;
        }
        return $currencies;
    }

    /**
     * Retourne la devise.
     *
     * @param $currencyId
     * @return null|Currency
     */
    public function getCurrency($currencyId)
    {
        return $this->getEntityManager()->getRepository(Currency::class)->find($currencyId);
    }

    /**
     * Retourne la liste des unités monaitaire disponibles.
     *
     * @return array
     */
    public function getCurrenciesSelect()
    {
        static $_select_currencies;
        if ($_select_currencies === null) {
            $_select_currencies = [];
            $entities = $this->getCurrencies();
            /** @var Currency $entity */
            foreach ($entities as $entity) {
                $_select_currencies[$entity->getId()] = $entity->getLabel();
            }
        }
        return $_select_currencies;
    }


    /**
     * Retourne la liste.
     *
     * @param $projectId integer Identifiant du projet
     * @return Activity[]
     */
    public function getGrantsProject($projectId)
    {
        return $this->getBaseQuery()
            ->where('c.project = :projectid')
            ->setParameter('projectid', $projectId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les activités actives de l'organisation donnée.
     *
     * @param $idOrganization
     * @return Activity[]
     */
    public function byOrganization($idOrganization)
    {
        return $this->getBaseQuery()
            ->where('org.id = :id')
            ->setParameter('id', $idOrganization)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les activités actives de l'organisation donnée.
     *
     * @param $idOrganization
     * @return Activity[]
     */
    public function byOrganizationWithoutProject($idOrganization)
    {
        return $this->getBaseQuery()
            ->where('org.id = :id AND c.project IS NULL')
            ->setParameter('id', $idOrganization)
            ->getQuery()
            ->getResult();
    }


    /**
     * Retourne les activités actives de la personne donnée.
     *
     * @param $idPerson
     * @return Activity[]
     */
    public function byPerson($idPerson)
    {
        return $this->getBaseQuery()
            ->where('per.id = :personId')
            ->setParameter('personId', $idPerson)
            ->orderBy('c.dateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function byPersonWithoutProject($idPerson)
    {
        return $this->getBaseQuery()
            ->where('per.id = :personId AND c.project IS NULL')
            ->setParameter('personId', $idPerson)
            ->orderBy('c.dateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function personActivities($personId)
    {
        return $this->getBaseQuery()
            ->where('per.id = :personId')
            ->setParameter('personId', $personId)
            ->orderBy('c.dateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function personActivitiesWithoutProject($personId)
    {
        return $this->getBaseQuery()
            ->where('per.id = :personId AND c.project IS NULL')
            ->setParameter('personId', $personId)
            ->orderBy('c.dateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la requète pour obtenir la liste complète des contrats.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrants()
    {
        return $this->getBaseQuery()
            ->orderBy('c.dateCreated', 'DESC')
            ->addSelect('d')
            ->leftJoin('c.documents', 'd');
    }

    /**
     * @param $sourceId
     * @return GrantSource
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSource($sourceId)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(GrantSource::class, 's')
            ->where('s.id = :id')
            ->setParameter('id', $sourceId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param integer[] $ids
     */
    public function getDisciplinesById($ids)
    {
        return $this->getEntityManager()->getRepository(Discipline::class)->createQueryBuilder('d')
            ->select('d')
            ->where('d.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Discipline[]
     */
    public function getDisciplines()
    {
        $array = [];
        foreach ($this->getEntityManager()
                     ->createQueryBuilder()
                     ->select('d')
                     ->from(Discipline::class, 'd')
                     ->getQuery()
                     ->getResult() as $discipline) {
            $array[$discipline->getId()] = strval($discipline);
        }
        return $array;
    }

    /**
     * @param $typeId
     * @return ContractType
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getType($typeId)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(ContractType::class, 's')
            ->where('s.id = :id')
            ->setParameter('id', $typeId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return ContractType[]
     */
    public function getTypes()
    {
        $array = [];
        $prefix = '';
        $lft = 0;
        $rgt = 0;
        $open = [];

        /** @var ContractType $grantType */
        foreach ($this->getEntityManager()
                     ->createQueryBuilder()
                     ->select('t')
                     ->from(ContractType::class, 't')
                     ->orderBy('t.lft', 'ASC')
                     ->getQuery()
                     ->getResult() as $grantType) {
            $close = count($open);
            $prefix = '';
            while ($close > 0) {
                if ($open[count($open) - 1] < $grantType->getLft()) {
                    array_pop($open);
                } else {
                    $prefix .= " - ";
                    $prefix .= " - ";
                }
                $close--;
            }

            if ($grantType->getLft() + 1 == $grantType->getRgt()) {
                $prefix .= ' # ';
                $qt = '';
            } else {
                $open[] = $grantType->getRgt();
                $qt = sprintf(' (%s)', (($grantType->getRgt() - $grantType->getLft() - 1) / 2));
            }
            $array[$grantType->getId()] = $prefix . strval($grantType) . $qt;
            $lft = $grantType->getLft();
            $rgt = $grantType->getRgt();
        }
        return $array;
    }

    /**
     * Retourne le contrat.
     *
     * @param $projectGrantId Identifiant du contrat.
     * @return Activity
     */
    public function getGrant($projectGrantId)
    {
        return $this->getBaseQuery()
            ->where('c.id = :id')
            ->setParameter('id', $projectGrantId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param $idRole
     * @return OrganizationRole
     */
    public function getRoleOrganizationById($idRole)
    {
        try {
            $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($idRole);
            return $role;
        } catch (\Exception  $e) {
            throw new OscarException("Le rôle d'organisation '$idRole' est introuvable.");
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Affectation ORGANISATION <> ACTIVITÉ

    public function deleteActivityPerson(ActivityPerson $activityPerson)
    {

    }

    public function organizationActivityEdit(ActivityOrganization $activityorganization, OrganizationRole $roleOrganization, $dateStart = null, $dateEnd = null, $buildIndex = true)
    {
        try {
            $activityorganization->setRoleObj($roleOrganization)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);
            $this->getEntityManager()->flush($activityorganization);

            try {
                if ($buildIndex) {
                    $this->jobSearchUpdate($activityorganization->getActivity());
                    $this->getOrganizationService()->updateIndex($activityorganization->getOrganization());
                }
            } catch (\Exception $e) {
                $this->getLoggerService()->error($e->getMessage());
            }

        } catch (\Exception $e) {
            throw new OscarException(sprintf(_("Impossible de mettre à jour le rôle de l'organisation %s comme %s dans %s : %s", $activityorganization->getOrganization(), $roleOrganization, $activityorganization->getActivity(), $e->getMessage())));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Affectation ORGANISATION <> ACTIVITÉ

    public function organizationActivityAdd(Organization $organization, Activity $activity, OrganizationRole $roleOrganization, $dateStart = null, $dateEnd = null, $buildIndex = true)
    {
        try {
            // TODO Date de début/fin
            $organizationActivity = new ActivityOrganization();
            $this->getEntityManager()->persist($organizationActivity);
            $organizationActivity->setOrganization($organization)
                ->setActivity($activity)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($roleOrganization);
            $this->getEntityManager()->flush($organizationActivity);

            if ($buildIndex) {
                $this->jobSearchUpdate($activity);
                $this->getOrganizationService()->updateIndex($organization);
            }
        } catch (\Exception $e) {
            throw new OscarException(sprintf(_("Impossible d'ajouter l'organisation %s comme %s dans %s : %s", $organization, $roleOrganization, $activity, $e->getMessage())));
        }
    }

    public function activityOrganizationRemove(ActivityOrganization $activityOrganization)
    {
        try {
            $activity = $activityOrganization->getActivity();
            $organization = $activityOrganization->getOrganization();
            $this->getEntityManager()->remove($activityOrganization);
            $this->getEntityManager()->flush($activityOrganization);
            $this->jobSearchUpdate($activity);
            $this->getOrganizationService()->updateIndex($organization);
        } catch (\Exception $e) {
            throw new OscarException(sprintf(_("Impossible de supprimer %s de l'activité %s : %s", $organization, $activity, $e->getMessage())));
        }
    }




    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery($includeObsolet = false)
    {
        if ($includeObsolet === TRUE) {
            $roleClaude = ' AND ((m.dateStart is NULL OR m.dateStart <= :dateRef)AND(m.dateEnd is NULL OR m.dateEnd >= :dateRef))';
        } else {
            $roleClaude = '';
        }
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c, p, m, per, org, t, d, pr', 'at', 'org', 'dt')
            ->from(Activity::class, 'c')
            ->leftJoin('c.organizations', 'p', Query\Expr\Join::WITH, 'p.status = 1' . $roleClaude)
            ->leftJoin('c.persons', 'm', Query\Expr\Join::WITH, 'm.status = 1' . $roleClaude)
            ->leftJoin('m.person', 'per')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('c.activityType', 'at')
            ->leftJoin('c.type', 't')
            ->leftJoin('c.documents', 'd')
            ->leftJoin('d.typeDocument', 'dt')
            ->leftJoin('p.organization', 'org');

        if ($includeObsolet === true)
            $qb->setParameter('dateRef', new \DateTime());

        return $qb;
    }

    public function getPcruPoleCompetitiviteByLabel($label)
    {
        /** @var PcruPoleCompetitiviteRepository $poleRepository */
        $poleRepository = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class);

        return $poleRepository->findOneBy([ 'label' => $label ]);
    }

    public function getPcruSourceFinancementByLabel($label)
    {
        /** @var PcruSourceFinancementRepository $poleRepository */
        $sourceFinancnementRepository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);

        return $sourceFinancnementRepository->findOneBy([ 'label' => $label ]);
    }

    public function getPcruSourceFinancementSelect()
    {
        /** @var PcruSourceFinancement $poleRepository */
        $sourceFinancementRepository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);

        $out = [
            '' => 'Aucune'
        ];

        /** @var PcruSourceFinancement $sourceFinancement */
        foreach ($sourceFinancementRepository->findAll() as $sourceFinancement) {
            $out[$sourceFinancement->getLabel()] = $sourceFinancement->getLabel();
        }

        return $out;
    }

    public function addNewPoleCompetivite(string $label): PcruPoleCompetitivite
    {
        /** @var PcruPoleCompetitiviteRepository $poleRepository */
        $poleRepository = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class);
        if( !$poleRepository->findOneBy(['label' => $label]) ){
            try {
                $pole = new PcruPoleCompetitivite();
                $this->getEntityManager()->persist($pole);
                $pole->setLabel($label);
                $this->getEntityManager()->flush($pole);
                return $pole;
            } catch (\Exception $e) {
                throw new OscarException("Impossible d'ajouter le pôle '$label', " . $e->getMessage());
            }
        } else {
            throw new OscarException("Le pôle '$label' existe déjà");
        }

    }

    public function addNewSourceFinancement(string $label): PcruSourceFinancement
    {
        /** @var PcruSourceFinancementRepository $poleRepository */
        $poleRepository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);
        if( !$poleRepository->findOneBy(['label' => $label]) ){
            try {
                $pole = new PcruSourceFinancement();
                $this->getEntityManager()->persist($pole);
                $pole->setLabel($label);
                $this->getEntityManager()->flush($pole);
                return $pole;
            } catch (\Exception $e) {
                throw new OscarException("Impossible d'ajouter la source de financement '$label', " . $e->getMessage());
            }
        } else {
            throw new OscarException("La source de financement '$label' existe déjà");
        }

    }
    public function addNewTypeContract(string $label): PcruTypeContract
    {
        /** @var PcruTypeContractRepository $poleRepository */
        $poleRepository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        if( !$poleRepository->findOneBy(['label' => $label]) ){
            try {
                $pole = new PcruTypeContract();
                $this->getEntityManager()->persist($pole);
                $pole->setLabel($label);
                $this->getEntityManager()->flush($pole);
                return $pole;
            } catch (\Exception $e) {
                throw new OscarException("Impossible d'ajouter le type de contrat '$label', " . $e->getMessage());
            }
        } else {
            throw new OscarException("Le type de contrat '$label' existe déjà");
        }
    }

    /**
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruTypeContractArray( $format = AsArrayFormatter::ARRAY_FLAT ) :array
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        if( $format == AsArrayFormatter::ARRAY_FLAT )
            return $repository->getFlatArrayLabel();
        else
            throw new OscarException("Format pour la liste des Type de contrat PCRU non-disponible");
    }

    /**
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruTypeContractSelect() :array
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        $out = [];
        foreach ($repository->getFlatArrayLabel() as $type) {
            $out[$type] = $type;
        }
        return $out;
    }

    /**
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruTypeContractByLabel( string $label ) :?PcruTypeContract
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        return $repository->findBy(['label' => $label]);
    }

    /**
     * Retourne la liste des pôles de compétitivité PCRU certified chargé en BDD.
     * @return PcruPoleCompetitivite[]
     */
    public function getPcruPoleCompetitivite(){
        return $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class)->findAll();
    }

    /**
     * Retourne la liste des sources de financement PCRU certified chargé en BDD.
     * @return PcruSourceFinancement[]
     */
    public function getPcruSourcesFinancement(){
        return $this->getEntityManager()->getRepository(PcruSourceFinancement::class)->findAll();
    }


    /**
     * Retourne les données pour le selecteur de pôle de compétitivité.
     * @return string[]
     */
    public function getPcruPoleCompetitiviteSelect(){
        $out = [
            '' => 'Aucun',
        ];
        /** @var PcruPoleCompetitivite $pole */
        foreach ($this->getEntityManager()->getRepository(PcruPoleCompetitivite::class)->getFlatArrayLabel() as $pole) {
            $out[$pole] = $pole;
        }
        return $out;
    }
    /**
     * Retourne la liste des pôle de compétitivité PCRU.
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruPoleCompetitiviteArray( $format = AsArrayFormatter::ARRAY_FLAT, $withEmptyAtFirst = false ){
        /** @var PcruPoleCompetitiviteRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class);

        if( $format == AsArrayFormatter::ARRAY_FLAT ) {
            if ($withEmptyAtFirst == true) {
                $array = ["Aucun"];
            } else {
                $array = [];
            }
            $array = array_merge($array, $repository->getFlatArrayLabel());
            return $array;
        }
        else
            throw new OscarException("Format pour la liste des pôles de compétivité PCRU non-disponible");
    }

    /**
     * @param string $format
     * @return mixed
     * @throws OscarException
     */
    public function getPcruSourceFinancement( $format = AsArrayFormatter::ARRAY_FLAT ){
        /** @var PcruSourceFinancementRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);
        if( $format == AsArrayFormatter::ARRAY_FLAT )
            return $repository->getFlatArrayLabel();
        else
            throw new OscarException("Format pour la liste des sources de financement PCRU non-disponible");
    }
}
