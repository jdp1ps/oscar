<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectRepository;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityDateRepository;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Discipline;
use Oscar\Entity\ContractType;
use Oscar\Entity\Activity;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\PcruPoleCompetitivite;
use Oscar\Entity\PcruPoleCompetitiviteRepository;
use Oscar\Entity\PcruSourceFinancement;
use Oscar\Entity\PcruSourceFinancementRepository;
use Oscar\Entity\PcruTypeContract;
use Oscar\Entity\PcruTypeContractRepository;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRolesRepository;
use Oscar\Entity\TVA;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Formatter\AsArrayFormatter;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Formatter\OscarFormatterFactory;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Strategy\Search\IActivitySearchStrategy;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseActivityTypeService;
use Oscar\Traits\UseActivityTypeServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseGearmanJobLauncherService;
use Oscar\Traits\UseGearmanJobLauncherServiceTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
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
use Oscar\Utils\ArrayUtils;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\FileSystemUtils;
use Oscar\Utils\StringUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Oscar\Validator\EOTP;
use UnicaenSignature\Entity\Db\Signature;
use UnicaenSignature\Entity\Db\SignatureRecipient;
use UnicaenSignature\Service\SignatureService;
use UnicaenSignature\Strategy\Letterfile\Esup\EsupLetterfileStrategy;


class ProjectGrantService implements UseGearmanJobLauncherService, UseOscarConfigurationService, UseEntityManager,
                                     UseLoggerService,
                                     UseOscarUserContextService, UsePCRUService,
                                     UseProjectService, UsePersonService, UseOrganizationService, UseActivityLogService,
                                     UseActivityTypeService, UseNotificationService
{
    use UseOscarConfigurationServiceTrait,
        UseNotificationServiceTrait,
        UseActivityLogServiceTrait,
        UseEntityManagerTrait,
        UseLoggerServiceTrait,
        UseOscarUserContextServiceTrait,
        UsePersonServiceTrait,
        UseOrganizationServiceTrait,
        UseActivityTypeServiceTrait,
        UseProjectServiceTrait,
        UsePCRUServiceTrait,
        UseGearmanJobLauncherServiceTrait;


    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES
    ///
    /** @var MilestoneService */
    private $milestoneService;

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

    private SignatureService $signatureService;

    /**
     * @return SignatureService
     */
    public function getSignatureService(): SignatureService
    {
        return $this->signatureService;
    }

    /**
     * @param SignatureService $signatureService
     */
    public function setSignatureService(SignatureService $signatureService): self
    {
        $this->signatureService = $signatureService;
        return $this;
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// REPOSITORY
    /**
     * @return PcruTypeContractRepository
     */
    public function getPcruTypeContratRepository(): PcruTypeContractRepository
    {
        return $this->getEntityManager()->getRepository(PcruTypeContract::class);
    }

    /**
     * @return PcruSourceFinancementRepository
     */
    public function getPcruSourceFinancementRepository(): PcruSourceFinancementRepository
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

    public function getSignedProcessDefaultPersons(Activity $activity): array
    {
        $signedConfig = $this->getOscarConfigurationService()->getSignedContractConfiguration();
        $signedRolesPersons = $this->getOscarConfigurationService()->getSignedContractRolesPersons();
        $signedRolesOrganizations = $this->getOscarConfigurationService()->getSignedContractRolesOrganizations();

        // TODO Ajouter dans la configuration des personnes nommées
        //$extratsPersons = $this->getPersonService()->getPersonsByIds($this->getOscarConfigurationService()->getSignedContractPersons());
        $extrasPersons = [];
        return array_merge(
            $this->getPersonsDeepActivity($activity, $signedRolesPersons, $signedRolesOrganizations),
            $extrasPersons
        );
    }

    /**
     * Récupération des personnes impliquées dans une activité, inculant les structures et sous-structures.
     *
     * @param int $idActivity
     * @param array|null $filterRolePerson
     * @param array|null $filterRoleOrganization
     * @return array
     * @throws OscarException
     */
    public function getPersonsActivity(
        int $idActivity,
        ?array $filterRolePerson = null,
        ?array $filterRoleOrganization = null
    ): array {
        $out = [];
        $activity = $this->getActivityById($idActivity);
        /** @var ActivityPerson $personActivity */
        foreach ($activity->getPersonsDeep() as $personActivity) {
            $p = $personActivity->getPerson();
            $r = $personActivity->getRoleObj();
            if ($filterRolePerson == null || in_array($r->getId(), $filterRolePerson)) {
                $out[$p->getId()] = $p;
            }
        }

        if (count($filterRoleOrganization)) {
            /** @var ActivityOrganization $organizationActivity */
            foreach ($activity->getOrganizationsDeep() as $organizationActivity) {
                if ($filterRoleOrganization == null || in_array(
                        $organizationActivity->getRoleObj()->getId(),
                        $filterRoleOrganization
                    )) {
                    $org = $organizationActivity->getOrganization();
                    $orgs = $this->getOrganizationService()->getOrganizationAndParents($org->getId());
                    foreach ($orgs as $o) {
                        foreach ($o->getPersons() as $personOrganization) {
                            if ($filterRolePerson == null || in_array(
                                    $personOrganization->getRoleObj()->getId(),
                                    $filterRolePerson
                                )) {
                                $p = $personOrganization->getPerson();
                                $out[$p->getId()] = $p;
                            }
                        }
                    }
                }
            }
        }
        return $out;
    }

    public function getRecipients($options)
    {
        if(!array_key_exists('activity_id', $options)){
            return [];
        }
        $activity_id = intval($options['activity_id']);

        if(!array_key_exists('role_person_id', $options)){
            return [];
        }
        $role_activity_id = ArrayUtils::normalizeArray($options['role_person_id']);

        if(!array_key_exists('role_person_id', $options)){
            $role_organisation_id = [];
        } else {
            $role_organisation_id = ArrayUtils::normalizeArray($options['role_organisation_id']);
        }

        $persons = $this->getPersonsActivity($activity_id, $role_activity_id, $role_organisation_id);
        $recipients = [];
        foreach ($persons as $p) {
            $recipients[$p->getId()] = [
                'firstname' => $p->getFirstname(),
                'lastname' => $p->getLastname(),
                'email' => $p->getEmail(),
            ];
        }
        return $recipients;
    }

    public function sendSignedContract(
        string $path_file,
        array $personsIds,
        string $label = "Exemple de signature"
    ): void {
        $parpheur = $this->getOscarConfigurationService()->getSignedContractLetterFile();
        $level = $this->getOscarConfigurationService()->getSignedContractLevel();
        /** @var EsupLetterfileStrategy $letterFile */
        $letterFile = $this->getSignatureService()->getLetterfileService()->getLetterFileStrategy($parpheur);
        $emails = [];

        $signature = new Signature();
        $this->getEntityManager()->persist($signature);
        /** @var Person $p */
        foreach ($this->getPersonService()->getPersonsByIds($personsIds) as $p) {
            $r = new SignatureRecipient();
            $this->getEntityManager()->persist($r);
            $r->setSignature($signature);
            $r->setEmail($p->getEmail());
            $r->setFirstname($p->getFirstname());
            $r->setLastname($p->getLastname());
            $emails[] = $r;
        }
        $comment = "Commentaire par défaut";
        $signature->setLetterfileKey($parpheur)
            ->setDocumentPath($path_file)
            ->setLabel($label)
            ->setDescription($comment)
            ->setRecipients($emails)
            ->setType($level)
            ->setAllSignToComplete(true);
        $this->getEntityManager()->flush($signature);
        $this->getSignatureService()->sendSignature($signature);
    }

    /**
     * Retourne la liste des personnes en profondeur impliquées dans l'activité.
     *  - Implication directe (Activité/Projet)
     *  - Implication via les structures
     *  - Implication via les structures maitres
     *
     * @param Activity $activity
     * @param array $rolesIdsPersons IDs des rôles des personnes à retenir
     * @param array $rolesIdOrganizations ID des rôles des organizations à retenir
     *
     * @return array
     */
    public function getPersonsDeepActivity(
        Activity $activity,
        array $rolesIdsPersons = [],
        array $rolesIdOrganizations = []
    ): array {
        $out = [];

        /** @var ActivityPerson $activityPerson */
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            $person = $activityPerson->getPerson();
            $role = $activityPerson->getRoleObj();

            if (count($rolesIdsPersons) && !in_array($role->getId(), $rolesIdsPersons)) {
                continue;
            }

            if (!array_key_exists($person->getId(), $out)) {
                $out[$person->getId()] = [
                    'id' => $person->getId(),
                    'email' => $person->getEmail(),
                    'label' => $person->getFullname(),
                    'roles_ids' => [],
                ];
            }
            if (!array_key_exists($role->getId(), $out[$person->getId()]['roles_ids'])) {
                $out[$person->getId()]['roles_ids'][$role->getId()] = $role->getRoleId();
            }
        }

        $organizations = [];

        /** @var ActivityOrganization $activityOrganization */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            $role = $activityOrganization->getRoleObj();

            if (count($rolesIdOrganizations) && !in_array($role->getId(), $rolesIdOrganizations)) {
                continue;
            }

            if ($activityOrganization->isPrincipal()) {
                $org = $activityOrganization->getOrganization();
                $organizations[$org->getId()] = $org;
                if ($org->hasParent()) {
                    foreach ($org->getParents() as $sub) {
                        $organizations[$sub->getId()] = $sub;
                    }
                }
            }
        }

        /** @var Organization $o */
        foreach ($organizations as $o) {
            /** @var OrganizationPerson $organizationPerson */
            foreach ($o->getPersons() as $organizationPerson) {
                /** @var Person $person */
                $person = $organizationPerson->getPerson();

                /** @var Role $role */
                $role = $organizationPerson->getRoleObj();

                if (count($rolesIdsPersons) && !in_array($role->getId(), $rolesIdsPersons)) {
                    continue;
                }

                if (!array_key_exists($person->getId(), $out)) {
                    $out[$person->getId()] = [
                        'id' => $person->getId(),
                        'email' => $person->getEmail(),
                        'label' => $person->getFullname(),
                        'roles_ids' => [],
                    ];
                }
                if (!array_key_exists($role->getId(), $out[$person->getId()]['roles_ids'])) {
                    $out[$person->getId()]['roles_ids'][$role->getId()] = $role->getRoleId();
                }
            }
        }

        return $out;
    }

    public function checkPFIRegex($regex): array
    {
        $out = [
            'warnings' => [],
            'valids' => [],
            'count' => 0,
            'valid' => false,
            'error' => []
        ];

        $badPfi = false;

        $pfi = $this->getActivityRepository()->getDistinctPFI();
        foreach ($pfi as $pfiTested) {
            if ($pfiTested == "") {
                continue;
            }
            if (preg_match_all($regex, $pfiTested, $matches, PREG_SET_ORDER, 0)) {
                $out['valids'][] = $pfiTested;
            } else {
                $badPfi = "Un ou plusieurs N°Financier ne correspondent pas au format attendu";
                $out['warnings'][] = $pfiTested;
            }
            $out['count']++;
        }
        if ($badPfi) {
            $out['error'][] = $badPfi;
        }
        if (!$regex) {
            $out['error'][] = "Aucune regex renseignée";
        }

        $out['valid'] = count($out['warnings']) == 0;

        return $out;
    }


    public function getTypeDocument($typeDocumentId, $throw = false)
    {
        $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($typeDocumentId);
        if ($type == null && $throw === true) {
            throw new OscarException(sprintf(_("Le type de document %s n'existe pas"), $typeDocumentId));
        }
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
            ->setParameters(
                [
                    'person' => $person,
                    'from' => $from,
                    'to' => $to
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return ActivityRepository
     */
    public function getActivityRepository()
    {
        return $this->getEntityManager()->getRepository(Activity::class);
    }

    public function getActivityTypeById($activityTypeId)
    {
        return $this->getEntityManager()->getRepository(ActivityType::class)->find($activityTypeId);
    }

    /**
     * @param Person $from
     * @param Person $to
     * @param Activity $activity
     */
    public function replacePerson(Person $from, Person $to, Activity $activity): void
    {
        $date = new \DateTime();

        /** @var ActivityPerson $activityPerson */
        foreach ($activity->getPersons() as $activityPerson) {
            if ($activityPerson->getPerson()->getId() == $from->getId()) {
                $roleObj = $activityPerson->getRoleObj();
                $this->getPersonService()->personActivityAdd($activity, $to, $roleObj, $date);
                $activityPerson->setDateEnd($date);
                $this->getEntityManager()->flush($activityPerson);
            }
        }
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
            if ($throw === true) {
                throw new OscarException("Impossible de charger l'activité (ID = $id)");
            } else {
                return null;
            }
        }
        return $activity;
    }

    /**
     * @param string $importedUid
     * @param bool $throw
     * @return Activity|null
     * @throws OscarException
     */
    public function getActivityByImportedUid(string $importedUid, $throw = true): ?Activity
    {
        $activity = $this->getActivityRepository()->findOneBy(['centaureId' => $importedUid]);
        if (!$activity) {
            if ($throw === true) {
                throw new OscarException("Impossible de charger l'activité (UID = $importedUid)");
            } else {
                return null;
            }
        }
        return $activity;
    }

    /**
     * @param int $id
     * @param bool $throw
     * @return Project|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getProjectById(int $id, bool $throw = true): ?Project
    {
        return $this->getProjectService()->getProject($id, $throw);
    }

    /**
     * Retourne la liste des structures impliquées dans l'activité, incluant :
     * les sous-structures, les structures mères.
     *
     * @param Activity $activity
     * @return array
     */
    public function getOrganizationsAccessDeeper(Activity $activity, bool $principal = true, bool $withRole = false)
    {
        $out = [
            'organizations_ids' => [],
            'organizations' => []
        ];

        /** @var ActivityOrganization $activityOrganization */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            if ($activityOrganization->isPrincipal()) {
                $organization = $activityOrganization->getOrganization();
                $organizationId = $organization->getId();

                if (array_key_exists($organizationId, $out['organizations'])) {
                    continue;
                }
                $out['organizations'][$organizationId] = $organization;
                $out['organizations_ids'][] = $organizationId;

                // sous-organization
                $organizationsMain = $this->getOrganizationService()
                    ->getAncestors($activityOrganization->getOrganization()->getId());

                foreach ($organizationsMain as $main) {
                    if (!array_key_exists($main->getId(), $out['organizations'])) {
                        $out['organizations'][$main->getId()] = $main;
                        $out['organizations_ids'][] = $main->getId();
                    }
                }
            }
        }
        return $out;
    }

    // TODO cleanup
    public function getPersonsRoles(Activity $activity): array
    {
        $out = [

        ];

        /** @var ActivityPerson $activityPerson */
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            $person = $activityPerson->getPerson();
            $personId = $person->getId();
            if (!array_key_exists($personId, $out)) {
                $out[$personId] = [
                    'person' => $person,
                    'role_ids' => [],
                    'role_labels' => []

                ];
            }
            $roleId = $activityPerson->getRoleObj()->getId();
            $roleLabel = $activityPerson->getRoleObj()->getRoleId();
            if (!in_array($roleId, $out[$personId]['role_ids'])) {
                $out[$personId]['role_ids'][] = $roleId;
            }

            if (!in_array($roleLabel, $out[$personId]['role_labels'])) {
                $out[$personId]['role_labels'][] = $roleLabel;
            }
        }

        $org = [];
        /** @var ActivityOrganization $o */
        foreach ($activity->getOrganizationsDeep() as $o) {
            if ($o->isPrincipal()) {
                $org = $o->getOrganization()->getSelfWithAncestors($org);
            }
        }

        /** @var Organization $o */
        foreach ($org as $o) {
            foreach ($o->getPersons() as $personOrganization) {
                $person = $personOrganization->getPerson();
                $personId = $person->getId();
                if (!array_key_exists($personId, $out)) {
                    $out[$personId] = [
                        'person' => $person,
                        'role_ids' => [],
                        'role_labels' => []

                    ];
                }
                $roleId = $personOrganization->getRoleObj()->getId();
                $roleLabel = $personOrganization->getRoleObj()->getRoleId();
                if (!in_array($roleId, $out[$personId]['role_ids'])) {
                    $out[$personId]['role_ids'][] = $roleId;
                }

                if (!in_array($roleLabel, $out[$personId]['role_labels'])) {
                    $out[$personId]['role_labels'][] = $roleLabel;
                }
            }
        }

        return $out;
    }

    public function getActivityByOscarNum(string $oscarNum, $throw = true): ?Activity
    {
        $activity = $this->getActivityRepository()->findOneBy(['oscarNum' => $oscarNum]);
        if (!$activity) {
            if ($throw === true) {
                throw new OscarException("Impossible de charger l'activité (OSCAR N° = $oscarNum)");
            } else {
                return null;
            }
        }
        return $activity;
    }

    /**
     * Retourne la liste des types de documents disponibles pour qualifier les documents dans les activités de
     * recherche.
     *
     * @param bool $asArray
     * @return array|TypeDocument[]
     */
    public function getTypesDocuments($asArray = true)
    {
        $types = $this->getEntityManager()->getRepository(TypeDocument::class)->findBy([], ['label' => 'ASC']);
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

    public function getActivityIdsForOrganization(Organization $organization): array
    {
    }

    /**
     * @param Person $person
     * @return int[]
     */
    public function getActivityIdsForPerson(Person $person): array
    {
        $idsActivityDirect = $this->getActivityRepository()->getIdsForPersons([$person->getId()]);

        $idsOrganizations = $this->getOrganizationService()->getIdsForPerson($person);
        $idsActivityInOrganization = $this->getActivityRepository()->getIdsForOrganizations($idsOrganizations);

        return array_unique(array_merge($idsActivityDirect, $idsActivityInOrganization));
    }

    public function getActivitiesWithUndoneMilestones()
    {
        return $this->getActivityRepository()->getActivitiesWithUndoneMilestones();
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


    public function getActivityIdsByJalon($jalonTypeId, $progression = null)
    {
        $q = $this->getActivityRepository()->createQueryBuilder('c')
            ->select('c.id')
            ->innerJoin('c.milestones', 'm')
            ->where('m.type = :jalonId');

        if (is_array($progression) && count($progression) > 0) {
            $clause = 'm.finished IN(:progression)';

            if (in_array('0', $progression)) {
                $clause .= ' OR m.finished IS NULL';
            }
            $q->andWhere($clause)
                ->setParameter('progression', $progression);
        }

        $q->setParameter('jalonId', $jalonTypeId);

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

    //////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RECHERCHE AVANCES
    ///
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Système de recherche avancées V2.
     *
     * @param string $search
     * @param array $options
     */
    public function searchActivities(string $search, array $options): array
    {
        //
        $sort = 'hit';
        $direction = 'desc';
        $page = 1;
        $resultByPage = 50;
        $ids_search = null;
        $restricted_ids = null;

        if (array_key_exists('restricted_ids', $options)) {
            $restricted_ids = $options['restricted_ids'];
        }

        // Critère de trie
        if (array_key_exists('sort', $options)) {
            $sort = $options['sort'];
        }

        // Ordre de trie
        if (array_key_exists('direction', $options)) {
            $direction = $options['direction'];
        }

        $output = [
            'version' => OscarVersion::getBuild(),
            'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            'total' => 0,
            'total_page' => 0,
            'total_time' => 0,
            'total_page_time' => 0,
            'page' => $page,
            'result_by_page' => $resultByPage,
            'options' => json_encode($options),
            'search_text' => '',
            'search_text_total' => 0,
            'search_text_error' => '',
            'search_text_time' => 0,
            'filters_infos' => [],
            'activities' => [],
        ];

        $step = $begin = microtime(true);

        if ($restricted_ids === false && count($options['filters']) == 0) {
            $restricted_ids = $this->getActivityRepository()->getActivitiesIdsAll();
        }

        // Recherche textuel via Elastic
        if ($search) {
            try {
                $output['search_text'] = $search;
                $ids_search = $this->search($search);
                $output['search_text_total'] = count($ids_search);
            } catch (\Exception $e) {
                $output['search_text_error'] = $e->getMessage();
            }
            $output['search_text_time'] = microtime(true) - $step;
            $step = microtime(true);
        }

        if (is_array($restricted_ids)) {
            if ($ids_search === null) {
                $ids_search = $restricted_ids;
            } else {
                $ids_search = array_intersect($ids_search, $restricted_ids);
            }
        }

        // Critères des filtres
        if (array_key_exists('filters', $options)) {
            $criteriaDef = $this->getActivitiesSearchCriteria();
            foreach ($options['filters'] as $filterStr) {
                $filter_info = [
                    'input' => $filterStr,
                    'error' => '',
                    'time' => 0,
                    'type' => '',
                    'label' => '',
                    'total' => 0
                ];
                $params = explode(';', $filterStr);
                $type = $params[0];

                if (!array_key_exists($type, $criteriaDef) && $type != 's') {
                    $this->getLoggerService()->error("Mauvais filtre '$type'");
                    $filter_info['error'] = "Filtre '$type' inconnu";
                    $output['filters_infos'][] = $filter_info;
                    continue;
                }

                $filter_info['type'] = $type;
                $filter_info['label'] = $criteriaDef[$type];

                // Extraction des paramètres du filtre
                $value1 = count($params) >= 2 ? intval($params[1]) : -1;
                $value2 = count($params) == 3 ? intval($params[2]) : -1;

                $ids_exclude = null;

                try {
                    switch ($type) {
                        case 'ao' :
                            if (!$value1) {
                                throw new OscarException("Vous devez préciser une structure");
                            }
                            $ids = $this->getActivityRepository()->getIdsForOrganizationWithRole($value1, $value2);
                            break;

                        case 'so' :
                            if (!$value1) {
                                throw new OscarException("Vous devez préciser une structure");
                            }
                            $ids = $this->getActivityRepository()->getIdsWithoutOrganizationWithRole($value1, $value2);
                            break;

                        case 'sp' :
                            if (!$value1) {
                                throw new OscarException("Vous devez préciser une personne");
                            }
                            $ids = $this->getActivityRepository()->getIdsWithoutPersonWithRole($value1, $value2);
                            break;

                        case 'ap' :
                            if (!$value1) {
                                throw new OscarException("Vous devez préciser une personne");
                            }
                            $ids = $this->getActivityRepository()->getIdsForPersonWithRole($value1, $value2);
                            break;

                        case 'pm' :
                            $idPerson = explode(',', $params[1]);
                            try {
                                $idsPerson = StringUtils::intArray($params[1]);
                            } catch (\Exception $e) {
                                throw new \Exception("Mauvais format : " . $e->getMessage());
                            }
                            $ids = $this->getActivityRepository()->getIdsForPersons($idsPerson);
                            break;

                        case 'cnt' :
                            $pays = explode(',', $params[1]);

                            // IDS des organizations
                            /** @var OrganizationRepository $organizationRepository */
                            $organizationRepository = $this->getEntityManager()->getRepository(Organization::class);
                            $organizationIds = $organizationRepository->getIdWithCountries($pays);

                            $ids = $this->getActivityRepository()
                                ->getActivitiesIdsForOrganizations($organizationIds, false);
                            break;

                        case 'tnt' :
                            // Activités impliquant certains types d'organisation
                            $types = explode(',', $params[1]);

                            // IDS des organizations
                            /** @var OrganizationRepository $organizationRepository */
                            $organizationRepository = $this->getEntityManager()->getRepository(Organization::class);
                            $organizationIds = $organizationRepository->getIdWithTypes($types);
                            $ids = $this->getActivityRepository()
                                ->getActivitiesIdsForOrganizations($organizationIds, false);
                            break;

                        case 's' :
                            $statusarray = explode(',', $params[1]);
                            $filter_info['param'] = $value1;
                            $filter_info['debug'] = $statusarray;
                            $ids = $this->getActivityRepository()->getIdsWithStatus($statusarray);
                            break;

                        // Critère liè aux DATES
                        case 'add' :
                            $dateDebut = $params[1];
                            $dateFin = $params[2];
                            $ids = $this->getActivityRepository()->getBeetween2Dates($dateDebut, $dateFin, 'dateStart');
                            break;

                        case 'adf' :
                            $dateDebut = $params[1];
                            $dateFin = $params[2];
                            $ids = $this->getActivityRepository()->getBeetween2Dates($dateDebut, $dateFin, 'dateEnd');
                            break;

                        case 'adc' :
                            $dateDebut = $params[1];
                            $dateFin = $params[2];
                            $ids = $this->getActivityRepository()->getBeetween2Dates(
                                $dateDebut,
                                $dateFin,
                                'dateCreated'
                            );
                            break;
                        case 'adm' :
                            $dateDebut = $params[1];
                            $dateFin = $params[2];
                            $ids = $this->getActivityRepository()->getBeetween2Dates(
                                $dateDebut,
                                $dateFin,
                                'dateUpdated'
                            );
                            break;


                        default :
                            $filter_info['error'] = "Filtre non-implémenté '$type'";
                            throw new OscarException("Critère '$type' non-implémenté");
                    }

                    $filter_info['total'] = count($ids);
                    $filter_info['time'] = microtime(true) - $step;

                    $this->getLoggerService()->info(
                        sprintf(
                            "Filter result '%' take %s ms with %s result(s)",
                            $filterStr,
                            $filter_info['time'],
                            $filter_info['total']
                        )
                    );

                    $step = microtime(true);

                    if ($ids_search === null) {
                        $ids_search = $ids;
                    } else {
                        $ids_search = array_intersect($ids_search, $ids);
                    }
                } catch (\Exception $e) {
                    $filter_info['error'] = $e->getMessage();
                }

                $output['filters_infos'][] = $filter_info;
            }
        }

        $start = ($page - 1) * $resultByPage;
        $output['total'] = count($ids_search);
        $idsKeep = array_slice($ids_search, $start, $resultByPage);

        if (count($idsKeep) > 0) {
            $queryBuilder = $this->getActivityRepository()->getBaseQueryBuilderByIdsPaged(
                $idsKeep,
                $page,
                $resultByPage
            );

            if ($sort == "hit") {
                $resultRaw = $queryBuilder->getQuery()->getResult();
                $pertinenceFunction = function ($a1, $a2) use ($idsKeep) {
                    $a1ID = array_search($a1->getId(), $idsKeep);
                    $a2ID = array_search($a2->getId(), $idsKeep);
                    return $a1ID > $a2ID;
                };
                usort($resultRaw, $pertinenceFunction);
            } else {
                $queryBuilder->orderBy('c.' . $sort, $direction);
                $resultRaw = $queryBuilder->getQuery()->getResult();
            }

            $output['activities'] = $resultRaw;
            $output['total_page'] = count($resultRaw);
            $output['total_page_time'] = microtime(true) - $step;
        }

        $output['total_time'] = microtime(true) - $begin;
        $this->getLoggerService()->info(
            sprintf(
                "Recherche '%s' (took %s ms)",
                $output['search_text'],
                $output['total_time']
            )
        );

        return $output;
    }

    protected function debug__displayIds(array $ids)
    {
        echo " = ";
        $sep = "";
        foreach ($ids as $id) {
            echo "$sep$id";
            $sep = ',';
        }
        echo "\n---\n";
    }


    /**
     * Retourne la liste des critères de trie disponibles pour la recherche avancées.
     *
     * @return string[]
     */
    public function getActivitiesSearchSort(): array
    {
        static $_activitiesSearchSort;
        if ($_activitiesSearchSort === null) {
            $_activitiesSearchSort = [
                'hit' => 'Pertinence',
                'dateUpdated' => 'Date de mise à jour',
                'dateCreated' => 'Date de création',
                'dateStart' => 'Date de début',
                'dateEnd' => 'Date de fin',
                'dateSigned' => 'Date de signature',
                'acronym' => 'Acronyme de projet',
                'label' => 'Intitulé',
                'status' => 'Status',
                'oscarNum' => 'N°Oscar',
                'codeEOTP' => 'N°Financier (' . $this->getOscarConfigurationService()->getFinancialLabel() . ')',
                'amount' => 'Montant'
            ];
        }
        return $_activitiesSearchSort;
    }

    public function getActivitiesSearchStatus(): array
    {
        return Activity::getStatusSelect();
    }

    /**
     * Retourne la liste des critères de trie disponibles pour la recherche avancées.
     *
     * @return string[]
     */
    public function getActivitiesSearchDirection(): array
    {
        static $_activitiesSearchDirection;
        if ($_activitiesSearchDirection === null) {
            $_activitiesSearchDirection = [
                'asc' => 'Croissant',
                'desc' => 'Décroissant',
            ];
        }
        return $_activitiesSearchDirection;
    }

    /**
     * Retourne la liste des critères de filtrage pour la recherche avancées.
     *
     * @return string[]
     */
    public function getActivitiesSearchCriteria(): array
    {
        static $activitiesSearchCriteria;
        if ($activitiesSearchCriteria === null) {
            $activitiesSearchCriteria = [
                'ap' => "Impliquant la personne",
                'sp' => "N'impliquant pas la personne",
                'pm' => "Impliquant une de ces personnes",
                'ao' => "Impliquant l'organisation",
                'so' => "N'impliquant pas l'organisation",
                'om' => "Impliquant une des organisations",
                'as' => 'Ayant le statut',
                'ss' => 'N\'ayant pas le statut',
                'cnt' => "Pays (d'une organisation)",
                'tnt' => "Type d'organisation",
                'af' => 'Ayant comme incidence financière',
                'sf' => 'N\'ayant pas comme incidence financière',
                'mp' => 'Montant prévu',
                'at' => 'est de type',
                'st' => 'n\'est pas de type',
                'add' => 'Date de début',
                'adf' => 'Date de fin',
                'adc' => 'Date de création',
                'adm' => 'Date de dernière mise à jour',
                'ads' => 'Date de signature',
                'adp' => 'Date d\'ouverture du N°Financier',
                'pp' => 'Activités sans projet',
                'fdt' => 'Activités soumise à feuille de temps',
                'ds' => 'Ayant pour discipline',
                'aj' => 'Ayant le jalon'
            ];
        }
        return $activitiesSearchCriteria;
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

            foreach ($query->getQuery()->getResult(Query::HYDRATE_ARRAY) as $r) {
                if ($r['numbers']) {
                    foreach ($r['numbers'] as $key => $value) {
                        if (!$value) {
                            //echo "$key\n";
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
            $editable = $datas['persons']['editable'] = $oscaruserContext->hasPrivileges(
                Privileges::ACTIVITY_PERSON_MANAGE,
                $activity
            );
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
            $editable = $datas['organizations']['editable'] = $oscaruserContext->hasPrivileges(
                Privileges::ACTIVITY_ORGANIZATION_MANAGE,
                $activity
            );
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
            $editable = $datas['milestones']['editable'] = $oscaruserContext->hasPrivileges(
                Privileges::ACTIVITY_MILESTONE_MANAGE,
                $activity
            );

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
            $editable = $datas['payments']['editable'] = $oscaruserContext->hasPrivileges(
                Privileges::ACTIVITY_PAYMENT_MANAGE,
                $activity
            );

            /** @var ActivityPayment $p */
            foreach ($activity->getPayments() as $p) {
                $datas['payments']['datas'][$p->getId()] = $p->toArray();
            }
        }


        return $datas;
    }

    public function getMilestoneTypesArray(): array
    {
        $milestones = [];
        /** @var DateType $milestoneType */
        foreach ($this->getEntityManager()->getRepository(DateType::class)->findAll() as $milestoneType) {
            $milestones[$milestoneType->getId()] = $milestoneType->toArray();
        }
        return $milestones;
    }

    protected function getRoleRepository(): RoleRepository
    {
        return $this->getEntityManager()->getRepository(Role::class);
    }

    public function getRolesPersonActivity(string $format = OscarFormatterConst::FORMAT_ARRAY_OBJECT): array
    {
        return $this->getRoleRepository()->getRolesAtActivityArray($format);
    }

    public $_cachedPersonRileIdsInActivity;

    public function getPersonRoleIdsInActivity(Activity $activity, Person $person): array
    {
        if ($this->_cachedPersonRileIdsInActivity == null) {
            $this->_cachedPersonRileIdsInActivity = [];
        }

        if (!array_key_exists($person->getId(), $this->_cachedPersonRileIdsInActivity)) {
            $this->_cachedPersonRileIdsInActivity[$person->getId()] = [];
        }

        if (!array_key_exists($activity->getId(), $this->_cachedPersonRileIdsInActivity[$person->getId()])) {
            $roleIds = [];
            /** @var ActivityPerson $activityPerson */
            foreach ($activity->getPersonsDeep() as $activityPerson) {
                if ($activityPerson->getPerson() == $person) {
                    $roleId = $activityPerson->getRoleObj()->getId();
                    if (!in_array($roleId, $roleIds)) {
                        $roleIds[] = $roleId;
                    }
                }

                /** @var ActivityOrganization $activityPartner */
                foreach ($activity->getOrganizationsDeep() as $activityPartner) {
                    if ($activityPartner->getOrganization()->hasPerson($person)) {
                        foreach ($activityPartner->getOrganization()->getPersonRolesId($person) as $roleId) {
                            if (!in_array($roleId, $roleIds)) {
                                $roleIds[] = $roleId;
                            }
                        }
                    }
                }
            }
            $this->_cachedPersonRileIdsInActivity[$person->getId()][$activity->getId()] = $roleIds;
        }

        return $this->_cachedPersonRileIdsInActivity[$person->getId()][$activity->getId()];
    }

    /**
     * Retourne la liste des Jalons(ActivityDate) non-réalisé pour la personne.
     *
     * @param Person $person
     * @return ActivityDate[]
     */
    public function getUndoneMilestonesForPerson(Person $person): array
    {
        $activityWithUndoneMilestones = $this->getActivitiesWithUndoneMilestones();
        $milestonesUndone = [];
        foreach ($activityWithUndoneMilestones as $a) {
            $roleIdsInActivity = $this->getPersonRoleIdsInActivity($a, $person);
            /** @var ActivityDate $m */
            foreach ($a->getMilestones() as $m) {
                if (!$m->isFinished() && array_intersect($m->getType()->getRolesId(), $roleIdsInActivity)) {
                    $milestonesUndone[] = $m;
                }
            }
        }
        return $milestonesUndone;
    }


    /**
     * Retourne les payements en retard de la personne.
     *
     * @param Person $person
     * @return ActivityPayment[]
     */
    public function getUndonePayementsForPerson(Person $person): array
    {
        $rolesIds = $this->getOscarUserContextService()->getRolesIdsWithPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE);

        $paymentsLate = $this->getPaymentsLate();
        $payements = [];
        /** @var ActivityPayment $p */
        foreach ($paymentsLate as $p) {
            $roleIdsInActivity = $this->getPersonRoleIdsInActivity($p->getActivity(), $person);
            if ($p->isLate() && array_intersect($roleIdsInActivity, $rolesIds)) {
                $payements[] = $p;
            }
        }
        return $payements;
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
        return $this->getActivityRepository()->getActivitiesPersonDate((int)$personId, $date);
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
        $keys = [];

        /** @var Activity $activity */
        foreach ($query->getQuery()->getResult() as $activity) {
            $hasUnknow = false;
            foreach (array_keys($activity->getNumbers()) as $key) {
                if (!in_array($key, $authorisedKeys)) {
                    if (!in_array($key, $keys)) {
                        $keys[] = $key;
                    }
                    $hasUnknow = true;
                }
            }
            if ($hasUnknow === true) {
                $activities[] = $activity;
            }
        }

        return [
            'activities' => $activities,
            'keys' => $keys
        ];
    }

    public function getActivitiesIdsWithTypeDocument(array $idsTypeDocument, bool $reverse = false): array
    {
        return $this->getActivityRepository()->getActivitiesIdsWithTypeDocument($idsTypeDocument, $reverse);
    }

    public function getActivitiesWithNumerotation(array $numerotations): array
    {
        $ids = $this->getActivityRepository()->getActivitiesIdsWithNumerotations($numerotations);
        return $ids;
    }

    /**
     * Renomage des clefs pour les numérotations personnalisées;
     *
     * @param $from
     * @param $to
     */
    public function administrationMoveKey($from, $to): int
    {
        $activities = $this->getActivityRepository()->getActivitiesWithNumber($from);
        $out = [];
        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $value = $activity->getNumber($from);
            $activity->removeNumber($from);
            $activity->addNumber($to, $value);
            $out[] = $activity->getId();
            $this->getEntityManager()->flush($activity);
        }
        return count($out);
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
    public function setLonelyPartnerAsFinancer(
        Activity $activity,
        $flush =
        true
    ) {
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
                        sprintf(
                            "OSCAR BOT a définit le rôle 'Financeur' pour le 
                    partenaire %s dans l'activité %s\n",
                            $found->getOrganization()->log(),
                            $found->getActivity()->log()
                        ),
                        null,
                        LogActivity::LEVEL_ADMIN,
                        'Activity',
                        $activity->getId()
                    );
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
            ->setParameters(
                [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d')
                ]
            );
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
                \Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                    new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
                );
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
        $this->getLoggerService()->info('[elasic] Reindex ' . count($activities) . ' activitie(s)');
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
     * @return IActivitySearchStrategy
     */
    public function getSearchEngineStrategy()
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
        $this->getLoggerService()->debug("[elastic:activity:delete] Activity:" . $id);
        $this->getSearchEngineStrategy()->deleteActivity($id);
    }

    public function searchUpdate(Activity $activity)
    {
        $this->getLoggerService()->debug("[elastic:activity:update] Activity:" . $activity->getId());
        $this->getSearchEngineStrategy()->updateActivity($activity);
    }

    public function testGearmanError()
    {
        throw new OscarException("Erreur envoyée depuis un service OSCAR.");
    }

    public function jobSearchUpdate(Activity $activity)
    {
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
    }

    public function searchIndex_reset()
    {
        $this->getLoggerService()->debug("[elastic:activity:reset]");
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
        $this->getLoggerService()->debug(
            "Association de type de contract OSCAR:$idTypeActivity pour PCRU:$idPcruContractType"
        );
        /** @var ActivityType $activityType */
        $activityType = $this->getEntityManager()->getRepository(ActivityType::class)->find($idTypeActivity);

        /** @var PcruTypeContract $pcruContractType */
        $pcruContractType = $this->getEntityManager()->getRepository(PcruTypeContract::class)->find(
            $idPcruContractType
        );

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

    public function getAvailableDocumentTypes(string $format = OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT): array
    {
        return OscarFormatterFactory::getFormatter($format)->format($this->getDocumentTypes());
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

            $options[$facet]['options'][$data->getId()] = $data->getLabel() . ($data->getDescription() ? sprintf(
                    ' (%s)',
                    $data->getDescription()
                ) : '');
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
            ->setPcruPoleCompetitivite($source->getPcruPoleCompetitivite())
            ->setPcruSourceFinancement($source->getPcruSourceFinancement())
            ->setPcruValidPoleCompetitivite($source->isPcruValidPoleCompetitivite())
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

            $this->getActivityLogService()->addUserInfo(
                sprintf(
                    "a supprimer le verserment de %s %s sur l'activité %s",
                    $activityPayment->getAmount(),
                    $activityPayment->getCurrency(),
                    $activityPayment->getActivity()->log()
                ),
                LogActivity::CONTEXT_ACTIVITY,
                $activityPayment->getActivity()->getId()

            );

            $this->getNotificationService()->jobUpdateNotificationsActivity($activityPayment->getActivity());

            return true;
        } catch (\Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            if ($throw) {
                throw new OscarException(
                    sprintf("Impossible de supprimer le versement '%s'.", $activityPayment->getId())
                );
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
            ->setCurrency(
                $this->getEntityManager()
                    ->getRepository(Currency::class)
                    ->find($data['currencyId'])
            );


        $status = $data['status'];
        $rate = $data['rate'];
        $datePredicted = $data['datePredicted'];
        $datePayment = $data['datePayment'];

        if ($datePayment) {
            $payment->setDatePayment(new \DateTime($datePayment));
        } else {
            $payment->setDatePayment(null);
        }

        if ($datePredicted) {
            $payment->setDatePredicted(new \DateTime($datePredicted));
        } else {
            $payment->setDatePredicted(null);
        }
        $payment->setRate($rate)
            ->setStatus($status);
        $this->getEntityManager()->flush($payment);

        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a modifié le verserment de %s %s sur l'activité %s",
                $payment->getAmount(),
                $payment->getCurrency(),
                $payment->getActivity()->log()
            ),
            LogActivity::CONTEXT_ACTIVITY,
            $payment->getActivity()->getId()

        );

        $this->getNotificationService()->jobUpdateNotificationsActivity($payment->getActivity());

        return $payment;
    }

    public function addNewActivityPayment($datas, $notification = true)
    {
        $payment = new ActivityPayment();
        $this->getEntityManager()->persist($payment);

        $payment->setAmount($datas['amount'])
            ->setComment($datas['comment'])
            ->setActivity($datas['activity'])
            ->setCodeTransaction($datas['codeTransaction'])
            ->setCurrency(
                $this->getEntityManager()
                    ->getRepository(Currency::class)
                    ->find($datas['currencyId'])
            );


        $status = $datas['status'];
        $rate = $datas['rate'];
        $datePredicted = $datas['datePredicted'];
        $datePayment = $datas['datePayment'];

        if ($datePayment) {
            $payment->setDatePayment(new \DateTime($datePayment));
        } else {
            $payment->setDatePayment(null);
        }

        if ($datePredicted) {
            $payment->setDatePredicted(new \DateTime($datePredicted));
        } else {
            $payment->setDatePredicted(null);
        }

        $payment->setRate($rate)
            ->setStatus($status);


        $this->getEntityManager()->flush($payment);
        $this->getActivityLogService()->addUserInfo(
            sprintf(
                "a ajouté le verserment de %s %s sur l'activité %s",
                $payment->getAmount(),
                $payment->getCurrency(),
                $payment->getActivity()->log()
            ),
            LogActivity::CONTEXT_ACTIVITY,
            $payment->getActivity()->getId()

        );
        $this->getNotificationService()->jobUpdateNotificationsActivity($payment->getActivity());
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
     * @param $id int Identifiant de l'échéance
     * @param bool|true $throw
     * @return null|object
     * @throws \Exception
     */
    public function getActivityDate(int $id, $throw = true): ActivityDate
    {
        $activityDate = $this->getActivityDateRepository()->find($id);
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
    public function deleteActivityDate(ActivityDate $activityDate, $throw = true): void
    {
        try {
            $this->getEntityManager()->remove($activityDate);
            $this->getEntityManager()->flush();
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
            ->setParameters(
                [
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                    'status' => ActivityPayment::STATUS_PREVISIONNEL,
                ]
            );

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
            ->setParameters(
                [
                    'date' => $date->format('Y-m-d'),
                    'status' => ActivityPayment::STATUS_PREVISIONNEL,
                ]
            );

        return $qb->getQuery()->getResult();
    }

    public function getPaymentsDifference()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(ActivityPayment::class, 'p')
            ->where('p.status = :status')
            ->orderBy('p.dateCreated', 'desc')
            ->setParameters(
                [
                    'status' => ActivityPayment::STATUS_ECART,
                ]
            );

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
     * @param $projectId int Identifiant du projet
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
    public function byOrganizationWithoutProject($idOrganization, bool $deep = false)
    {
        if ($deep) {
            $ids = $this->getOrganizationService()->getOrganizationIdsDeep($idOrganization);
            $ids[] = $idOrganization;
        } else {
            $ids = [$idOrganization];
        }
        return $this->getBaseQuery()
            ->where('org.id IN (:id) AND c.project IS NULL')
            ->setParameter('id', $ids)
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
        foreach (
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('d')
                ->from(Discipline::class, 'd')
                ->getQuery()
                ->getResult() as $discipline
        ) {
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
        foreach (
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(ContractType::class, 't')
                ->orderBy('t.lft', 'ASC')
                ->getQuery()
                ->getResult() as $grantType
        ) {
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
     * Retourne le contrat/activité par son id
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

    public function getBaseDataTemplate(): array
    {
        //
        $datas = [
            'id' => '',
            'acronym' => '',
            'amount' => '',
            'pfi' => '',
            'oscar' => '',
            'montant' => '',
            'annee-debut' => '',
            'annee-fin' => '',
            'debut' => '',
            'fin' => '',
            'intitule' => '',
            'label' => '',
            'tva' => '',
            'assiette-subventionnable' => '',
            'note-financiere' => '',
            'type' => '',
        ];

        $sluger = Slugify::create();

        // Dépenses
        $datas['total-depense'] = '';
        $datas['total-depense-percent'] = '';
        $datas['total-reste'] = '';

        // Rôles possibles
        $rolesInActivity = $this->getOscarUserContextService()->getAvailabledRolesPersonActivity();
        foreach ($rolesInActivity as $role) {
            $slug = $sluger->slugify($role);
            $datas[$slug] = "";
            $datas["$slug-list"] = "";
        }

        $organizationRolesActivity = $this->getOscarUserContextService()->getAvailabledRolesOrganizationActivity();

        foreach ($organizationRolesActivity as $role) {
            $slug = $sluger->slugify($role);
            $datas[$slug] = "";
            $datas["$slug-list"] = "";
        }

        foreach ($this->getMilestoneTypesArray() as $milestoneType) {
            $slug = $sluger->slugify($milestoneType['label']);
            $datas['jalon-' . $slug] = "";
            $datas["jalon-$slug-list"] = "";
        }

        $datas['versements-prevus'] = "";
        $datas['versements-effectues'] = "";
        $datas['versementPrevuMontant'] = "";
        $datas['versementPrevuDate'] = "";
        $datas['versement-effectue-montant'] = "";
        $datas['versement-effectue-date'] = "";

        foreach ($this->getCustomNum() as $code) {
            $codeSlug = $sluger->slugify('num ' . $code);
            $datas[$codeSlug] = "";
        }

        return $datas;
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

    public function getDocumentTabInfos(): array
    {
        /** @var TabsDocumentsRolesRepository $documentTabRepository */
        $documentTabRepository = $this->getEntityManager()->getRepository(TabDocument::class);

        $infos = [

        ];

        // Onglet "par défaut"
        $defaultTab = [
            'label' => "Onglet 'Par défaut'"
        ];
        try {
            $path = $this->getOscarConfigurationService()->getDocumentDropLocation();
            $defaultTab['path'] = $path;
            FileSystemUtils::getInstance()->checkDirWritable($path);
        } catch (\Exception $e) {
            $defaultTab['error'] = $e->getMessage();
        }

        // Onglet privé
        $privateTab = [
            'label' => "Onglet 'Privé'"
        ];

        try {
            $path = $this->getOscarConfigurationService()->getDocumentPrivateLocation();
            $privateTab['path'] = $path;
            FileSystemUtils::getInstance()->checkDirWritable($path);
        } catch (\Exception $e) {
            $privateTab['error'] = $e->getMessage();
        }

        $infos[] = $defaultTab;
        $infos[] = $privateTab;

        return $infos;
    }


    public function organizationActivityEdit(
        ActivityOrganization $activityorganization,
        OrganizationRole $roleOrganization,
        $dateStart = null,
        $dateEnd = null,
        $buildIndex = true
    ) {
        try {
            if (!$activityorganization->getRoleObj()) {
                $updateNotification = $roleOrganization->isPrincipal();
            } else {
                $updateNotification = $activityorganization->getRoleObj()->isPrincipal(
                    ) !== $roleOrganization->isPrincipal();
            }

            $activityorganization->setRoleObj($roleOrganization)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);

            $this->getEntityManager()->flush($activityorganization);

            $activity = $activityorganization->getActivity();
            $organization = $activityorganization->getOrganization();

            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization($organization);
            if ($updateNotification) {
                $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
            }

            $this->getActivityLogService()->addUserInfo(
                sprintf(
                    " a modifié l'organisation %s(%s) de l'activité %s",
                    $organization->log(),
                    $roleOrganization,
                    $activity->log()
                ),
                LogActivity::CONTEXT_ACTIVITY,
                $activity->getId()
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Affectation ORGANISATION <> ACTIVITÉ

    public function organizationActivityAdd(
        Organization $organization,
        Activity $activity,
        OrganizationRole $roleOrganization,
        $dateStart = null,
        $dateEnd = null,
        $buildIndex = true
    ) {
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


            try {
                $this->jobSearchUpdate($activity);
                $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization($organization);
                $this->getNotificationService()->jobUpdateNotificationsActivity($activity);
            } catch (\Exception $e) {
                $this->getLoggerService()->error("Soucis d'appel avec Gearman");
            }

            $this->getActivityLogService()->addUserInfo(
                sprintf(" a ajouté l'organisation %s de l'activité %s", $organization->log(), $activity->log()),
                LogActivity::CONTEXT_ACTIVITY,
                $activity->getId()
            );
        } catch (\Exception $e) {
            throw new OscarException(
                sprintf(
                    _(
                        "Impossible d'ajouter l'organisation %s comme %s dans %s : %s",
                        $organization,
                        $roleOrganization,
                        $activity,
                        $e->getMessage()
                    )
                )
            );
        }
    }

    public function activityOrganizationRemove(ActivityOrganization $activityOrganization)
    {
        try {
            $activity = $activityOrganization->getActivity();
            $organization = $activityOrganization->getOrganization();
            $updateNotification = $activityOrganization->getRoleObj() &&
                $activityOrganization->getRoleObj()->isPrincipal();
            $this->getEntityManager()->remove($activityOrganization);
            $this->getEntityManager()->flush();

            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization($organization);
            if ($updateNotification) {
                $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
            }

            $this->getActivityLogService()->addUserInfo(
                sprintf(" a supprimé l'organisation %s de l'activité %s", $organization->log(), $activity->log()),
                LogActivity::CONTEXT_ACTIVITY,
                $activity->getId()
            );
        } catch (\Exception $e) {
            throw new OscarException(
                sprintf(
                    _("Impossible de supprimer %s de l'activité %s : %s", $organization, $activity, $e->getMessage())
                )
            );
        }
    }




    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery($includeObsolet = false)
    {
        if ($includeObsolet === true) {
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

        if ($includeObsolet === true) {
            $qb->setParameter('dateRef', new \DateTime());
        }

        return $qb;
    }

    public function getPcruPoleCompetitiviteByLabel($label)
    {
        /** @var PcruPoleCompetitiviteRepository $poleRepository */
        $poleRepository = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class);

        return $poleRepository->findOneBy(['label' => $label]);
    }

    public function getPcruSourceFinancementByLabel($label)
    {
        /** @var PcruSourceFinancementRepository $poleRepository */
        $sourceFinancnementRepository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);

        return $sourceFinancnementRepository->findOneBy(['label' => $label]);
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
        if (!$poleRepository->findOneBy(['label' => $label])) {
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
        if (!$poleRepository->findOneBy(['label' => $label])) {
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
        if (!$poleRepository->findOneBy(['label' => $label])) {
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
    public function getPcruTypeContractArray($format = AsArrayFormatter::ARRAY_FLAT): array
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        if ($format == AsArrayFormatter::ARRAY_FLAT) {
            return $repository->getFlatArrayLabel();
        } else {
            throw new OscarException("Format pour la liste des Type de contrat PCRU non-disponible");
        }
    }

    /**
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruTypeContractSelect(): array
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
     * @return array
     */
    public function getDocumentTypes(): array
    {
        return $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
    }

    /**
     * @param string $format
     * @return array
     * @throws OscarException
     */
    public function getPcruTypeContractByLabel(string $label): ?PcruTypeContract
    {
        /** @var PcruTypeContractRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruTypeContract::class);
        return $repository->findBy(['label' => $label]);
    }

    /**
     * Retourne la liste des pôles de compétitivité PCRU certified chargé en BDD.
     * @return PcruPoleCompetitivite[]
     */
    public function getPcruPoleCompetitivite()
    {
        return $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class)->findAll();
    }

    /**
     * Retourne la liste des sources de financement PCRU certified chargé en BDD.
     * @return PcruSourceFinancement[]
     */
    public function getPcruSourcesFinancement()
    {
        return $this->getEntityManager()->getRepository(PcruSourceFinancement::class)->findAll();
    }


    /**
     * Retourne les données pour le selecteur de pôle de compétitivité.
     * @return string[]
     */
    public function getPcruPoleCompetitiviteSelect()
    {
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
    public function getPcruPoleCompetitiviteArray($format = AsArrayFormatter::ARRAY_FLAT, $withEmptyAtFirst = false)
    {
        /** @var PcruPoleCompetitiviteRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruPoleCompetitivite::class);

        if ($format == AsArrayFormatter::ARRAY_FLAT) {
            if ($withEmptyAtFirst == true) {
                $array = ["Aucun"];
            } else {
                $array = [];
            }
            $array = array_merge($array, $repository->getFlatArrayLabel());
            return $array;
        } else {
            throw new OscarException("Format pour la liste des pôles de compétivité PCRU non-disponible");
        }
    }

    /**
     * @param string $format
     * @return mixed
     * @throws OscarException
     */
    public function getPcruSourceFinancement($format = AsArrayFormatter::ARRAY_FLAT)
    {
        /** @var PcruSourceFinancementRepository $repository */
        $repository = $this->getEntityManager()->getRepository(PcruSourceFinancement::class);
        if ($format == AsArrayFormatter::ARRAY_FLAT) {
            return $repository->getFlatArrayLabel();
        } else {
            throw new OscarException("Format pour la liste des sources de financement PCRU non-disponible");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// REPOSITORY ACCESS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getActivityDateRepository(): ActivityDateRepository
    {
        return $this->getEntityManager()->getRepository(ActivityDate::class);
    }
}
