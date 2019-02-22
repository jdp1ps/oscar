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
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Discipline;
use Oscar\Entity\GrantSource;
use Oscar\Entity\ContractType;
use Oscar\Entity\Activity;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectGrantRepository;
use Oscar\Entity\Role;
use Oscar\Entity\TVA;
use Oscar\Entity\TypeDocument;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Strategy\Search\ActivitySearchStrategy;
use Oscar\Utils\StringUtils;
use Oscar\Validator\EOTP;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProjectGrantService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @return OscarConfigurationService
     */
    public function getOscarConfigurationService(){
        return $this->getServiceLocator()->get('OscarConfig');
    }

    public function getTypeDocument( $typeDocumentId, $throw=false ){
        $type = $this->getEntityManager()->getRepository(TypeDocument::class)->find($typeDocumentId);
        if( $type == null && $throw === true )
            throw new OscarException(sprintf(_("Le type de document %s n'existe pas"), $typeDocumentId));
        return $type;
    }

    public function getWorkPackagePersonPeriod( Person $person, $year, $month ){

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
     * @return ProjectGrantRepository
     */
    protected function getActivityRepository()
    {
       return $this->getEntityManager()->getRepository(Activity::class);
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

    public function getActivitiesIdsPerson( Person $person ){
        $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a');
        $query->select('a.id')
            ->leftJoin('a.persons', 'apers')
            ->leftJoin('apers.person', 'pers1')
            ->leftJoin('a.project','aprj')
            ->leftJoin('aprj.members', 'pprs')
            ->leftJoin('pprs.person', 'pers2')
            ->where('pers1 = :person OR pers2 = :person')
            ->setParameter('person', $person)
        ;
        $activities = $query->getQuery()->getResult();
        return array_map('current', $activities);
    }

    public function getProjectsIdsSearch($text){
        $query = $this->getEntityManager()->getRepository(Project::class)
            ->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.label LIKE :search OR p.description LIKE :search')
            ->setParameter('search', '%'.$text.'%');
        $projects = $query->getQuery()->getResult();
        return array_map('current', $projects);
    }


    public function getActivityIdsByJalon( $jalonTypeId ){
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
    public function getActivitiesByIds( $ids, $page=1, $resultByPage=50 ) {

        $offsetSQL = ($page-1) * $resultByPage;
        $limitSQL = $resultByPage;

        $query = $this->getEntityManager()->createQueryBuilder('a')
            ->select('a')
            ->from(Activity::class, 'a')
            ->setMaxResults($limitSQL)
            ->setFirstResult($offsetSQL);

        if( $ids !== null ){
            $query->where('a.id IN(:ids)')
                ->setParameter('ids', $ids);
        }

        return $query->getQuery()->getResult();
    }

    public function exportJsonPerson( Person $person ){
        $datas = $person->toJson();
        $datas['uid'] = $person->getId();
        return $datas;
    }

    public function exportJsonOrganization( Organization $organization ){
        $datas = $organization->toArray();
        $datas['uid'] = $organization->getId();
        return $datas;
    }

    public function exportJsonActivity( Activity $activity ){
        $datas = $activity->toArray();
        $datas['uid'] = $activity->getOscarNum();

        $datas['persons'] = [];
        foreach ($activity->getPersonsDeep() as $activityPerson) {
            $role = $activityPerson->getRole();
            if( !array_key_exists($role, $datas['persons']) ){
                $datas['persons'][$role] = [];
            }
            $datas['persons'][$role][] = $activityPerson->getPerson()->getDisplayName();
        }

        $datas['organizations'] = [];
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            $role = $activityOrganization->getRole();
            if( !array_key_exists($role, $datas['organizations']) ){
                $datas['organizations'][$role] = [];
            }
            $datas['organizations'][$role][] = (string) $activityOrganization->getOrganization();
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
            $type = (string) $milestone->getType();
            $datas['milestones'][] = [
                'type' => $type,
                'date' => $milestone->getDateStart()->format('Y-m-d')
            ];
        }


        return $datas;
    }

    public function getCustomNum() {
        static $customNum;
        if( $customNum === null ){
            // Récupération des différentes numérotations
            $customNum = [];

            $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
                ->select('a.numbers')
                ->distinct();
            echo "<pre>";
            foreach ($query->getQuery()->getResult(Query::HYDRATE_ARRAY) as $r) {
                if( $r['numbers'] ){
                    foreach ($r['numbers'] as $key=>$value){
                        if( !$value ){
                            echo "$key\n";
                        }
                        if( !in_array($key, $customNum) ){
                            $customNum[] = $key;
                        }
                    }
                }
            }

        }
        return $customNum;

    }

    public function exportJson( $object ){
        switch( get_class($object) ){
            case Activity::class:
                return $this->exportJsonActivity($object);
        }
    }

    /**
     * @param $id
     * @param OscarUserContext $oscaruserContext
     * @return array
     */
    public function getActivityJson( $id, $oscaruserContext){
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
        if( $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity) ){
            $datas['persons']['readable'] = true;
            $editable = $datas['persons']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE, $activity);
            /** @var ActivityPerson $p */
            foreach ( $activity->getPersonsDeep() as $p ){
                $person = $p->getPerson();
                $datas['persons']['datas'][$person->getId()] = [
                    'join' => get_class($p),
                    'join_id' => $p->getId(),
                    'displayName' => (string) $person,
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
        if( $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_SHOW, $activity) ){
            $datas['organizations']['readable'] = true;
            $editable = $datas['organizations']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE, $activity);
            foreach ( $activity->getOrganizationsDeep() as $p ){
                $organization = $p->getOrganization();
                $datas['organizations']['datas'][$organization->getId()] = [
                    'join' => get_class($p),
                    'join_id' => $p->getId(),
                    'displayName' => (string) $organization,
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
        if( $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_SHOW, $activity) ){
            $datas['milestones']['readable'] = true;
            $editable = $datas['milestones']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);

            if( $editable ) {
                $datas['milestones']['types'] = $this->getMilestoneTypesArray();
                $datas['milestoneEdit'] = null;
            }
            /** @var ActivityDate $m */
            foreach ( $activity->getMilestones() as $m ){
                $datas['milestones']['datas'][$m->getId()] = $m->toArray();
            }
        }

        // --- Partenaires de l'activités
        $datas['payments'] = [
            'readable' => false,
            'editable' => false,
            'datas' => []
        ];
        if( $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_SHOW, $activity) ){
            $datas['payments']['readable'] = true;
            $editable = $datas['payments']['editable'] = $oscaruserContext->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity);

            /** @var ActivityPayment $p */
            foreach ( $activity->getPayments() as $p ){
                $datas['payments']['datas'][$p->getId()] = $p->toArray();
            }
        }


        return $datas;
    }

    public function getMilestoneTypesArray(){
        $milestones = [];
        /** @var DateType $milestoneType */
        foreach ($this->getEntityManager()->getRepository(DateType::class)->findAll() as $milestoneType ){
            $milestones[ $milestoneType->getId() ] = $milestoneType->toArray();
        }
        return $milestones;
    }

    public function getFieldsCSV(){

        $headers = [
            'core' => Activity::csvHeaders(),
            'organizations' => [],
            'persons' => [],
            'milestones' => []
        ];

        $rolesOrganizationsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('r.label')
            ->from(OrganizationRole::class, 'r')
            ->getQuery()
            ->getResult();

        foreach( $rolesOrganizationsQuery as $role ){
            $headers['organizations'][] = $role['label'];
        }

        $rolesOrga = $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray();


        foreach( $rolesOrga as $role ){
            $headers['persons'][] = $role;
        }

        $dateTypes = $this->getEntityManager()->getRepository(DateType::class)->findAll();


        foreach( $dateTypes as $dateType ){
            $headers['milestones'][] = $dateType->getLabel();
        }

        return $headers;
    }

    public function getDistinctNumbersKey()
    {
        static $numbersKey;
        if( $numbersKey === null ){
            $query = $this->getEntityManager()->getRepository(Activity::class)
                ->createQueryBuilder('a')
                ->select('a.numbers')
                ->distinct('a.numbers');

            $key = [];
            foreach( $query->getQuery()->getResult() as $activity ){
                if( is_array($activity['numbers']) && count($activity['numbers']) ){
                    $key = array_merge(array_keys($activity['numbers']), $key);
                }
            }
            $numbersKey = array_unique($key);
        }
        return $numbersKey;
    }

    public function getDistinctNumberKeyUnreferenced(){
        $exists = $this->getDistinctNumbersKey();
        $referenced = $this->getServiceLocator()->get('OscarConfig')->getOptionalConfiguration('editable.numerotation', []);
        $unique = [];
        foreach ($exists as $key){
            if( !in_array($key, $referenced) ){
                $unique[] = $key;
            }
        }
        return $unique;
    }

    /**
     * @return array
     */
    public function getActivitiesWithUnreferencedNumbers(){

        // Clefs connues
        $authorisedKeys = $this->getOscarConfigurationService()->getNumerotationKeys();

        // Récupération des activités ayant des numérotations
        $query = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->where('a.numbers IS NOT NULL AND a.numbers != \'N;\' AND a.numbers != \'a:0:{}\'');

        // On isole les activités ayant des clefs de numérotation "Hors configuration"
        $activities = [];

        /** @var Activity $activity */
        foreach ($query->getQuery()->getResult() as $activity) {
            $hasUnknow = false;
            foreach(array_keys($activity->getNumbers()) as $key){
                if( !in_array($key, $authorisedKeys) ){
                    $hasUnknow = true;
                }
            }
            if( $hasUnknow === true ){
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    public function getPaymentsByActivityId( array $idsActivity, $organizations = null )
    {
        $query = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->innerJoin('p.activity', 'c')
            ->leftJoin('c.organizations', 'o1')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('pr.partners', 'o2')
            ->where('c.id IN (:ids)');

        $parameters = ['ids' => $idsActivity];

        if( $organizations ){
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
        if( $activity->getCodeEOTP() ){
            $found = null;

            // Liste des rôles ignorés par le traitement
            $ignoreRoles = [Organization::ROLE_COMPOSANTE_GESTION, Organization::ROLE_COMPOSANTE_RESPONSABLE];

            /** @var ActivityOrganization $organization */
            foreach ($activity->getOrganizations() as $organization ){
                if( in_array($organization->getRole(), $ignoreRoles) ){
                    continue;
                }

                if( $organization->getRole() != "" ){
                    return false;
                }

                if( $found !== null ){
                    return false;
                }

                $found = $organization;
            }
            if( $found ){
                $organization->setRole(Organization::ROLE_FINANCEUR);
                if( $flush === true ){
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
    public function getActivitiesBeetween2dates( \DateTime $from, \DateTime $to, $field = 'dateStart' )
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->where('c.'.$field.' >= :from AND c.'.$field.' <= :to')
            ->setParameters([
                'from'=> $from->format('Y-m-d'),
                'to'=> $to->format('Y-m-d')
            ]);
    }

    /**
     * Retourne les activités bientôt terminées. (par défaut, plage de 1 mois).
     *
     * @param string $gap
     * @param string $start
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivityAlmostDone( $gap = '+1 month', $start='now' )
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
    public function getActivityBeginsSoon( $gap = '+2 weeks', $start = 'now')
    {
        // Date d'encadrement
        $from = new \DateTime($start);
        $to = new \DateTime($start);
        $to->modify($gap);
        return $this->getActivitiesBeetween2dates($from, $to, 'dateStart');
    }


    
    public function digest(){
        foreach( $this->getActivityAlmostDone()->getQuery()->getResult() as $activity ){
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
        } catch( \Zend_Search_Lucene_Exception $e ){
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
    public function getMilestones( $idActivity ){
        return $this->getServiceLocator()->get('MilestoneService')->getMilestonesByActivityId( $idActivity );
    }

    public function searchIndex_rebuild()
    {
        $this->searchIndex_reset();
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findAll();
        return $this->getSearchEngineStrategy()->rebuildIndex($activities);
    }


    public function specificSearch( $what, &$qb, $activityAlias='c' )
    {
        $oscarNumSeparator = $this->getServiceLocator()->get('OscarConfig')->getConfiguration('oscar_num_separator');
        $fieldName = uniqid('num_');
        if (preg_match(EOTP::REGEX_EOTP, $what)) {
            $qb->andWhere($activityAlias.'.codeEOTP = :' . $fieldName)
                ->setParameter($fieldName, $what);
        }

        // Numéro SAIC
        elseif (preg_match("/^[0-9]{4}SAIC.*/mi", $what)) {
            $qb->andWhere($activityAlias.'.centaureNumConvention LIKE :'.$fieldName)
                ->setParameter($fieldName, $what.'%');
        }

        // La saisie est un numéro OSCAR©
        elseif (preg_match("/^[0-9]{4}$oscarNumSeparator.*/mi", $what)) {
            $qb->andWhere($activityAlias.'.oscarNum LIKE :'.$fieldName)
                ->setParameter($fieldName, $what.'%');
        }

        // Saisie 'libre'
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
        if( $searchStrategy === null ){
            $opt = $this->getServiceLocator()->get('OscarConfig')->getConfiguration('strategy.activity.search_engine');
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

    public function searchDelete( $id )
    {
        $this->getSearchEngineStrategy()->searchDelete($id);
    }

    public function searchUpdate( Activity $activity )
    {
        $this->getSearchEngineStrategy()->searchUpdate($activity);
    }

    public function searchIndex_reset()
    {
        $this->getSearchEngineStrategy()->resetIndex();
    }


    /**
     * @param array $ids
     * @return Activity[]
     */
    public function activitiesByIds( array $ids )
    {
        return $this->getBaseQuery()
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ActivityTypeService
     */
    private function getActivityTypeService()
    {
        return $this->getServiceLocator()->get('ActivityTypeService');
    }

    /**
     * @return ActivityLogService
     */
    private function getActivityLogService()
    {
        return $this->getServiceLocator()->get('ActivityLogService');
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getActivityTypes( $asArray = false )
    {
        return $this->getActivityTypeService()->getActivityTypes($asArray);
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getStatus()
    {
        return Activity::getStatusSelect();
    }

    public function getStatusByKey( $key )
    {
        return Activity::getStatusSelect()[$key];
    }

    public function number( Activity $activity )
    {
        if( $activity->getOscarId() !== null ){
            throw new Exception(sprintf("L'activité %s est déjà numérotée.", $activity));
        }
        echo "".$activity->getDateCreated()->format('Y-m-d')."<br>\n";
        $q = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->select('MAX(a.id) AS last_id, MIN(a.id) as first_id, CONCAT("toto","tutu")')

            ->where('YEAR(a.dateCreated) = :year')
            ->setParameter('year', $activity->getDateCreated()->format('Y'));
        $r = $q->getQuery()->getResult();
        echo "année de référence : " . $activity->getDateCreated()->format('Y') . "<br>" . $q->getQuery()->getDQL();
        var_dump($r);
        die($r);
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

    public function getTVAsForJson(){
        try {
            $query = $this->getEntityManager()->getRepository(TVA::class)->createQueryBuilder('t')
                ->select('t.id, t.label, t.rate, t.active AS active, count(a) as used')
                ->groupBy('t.id')
                ->orderBy('t.rate')
                ->leftJoin(Activity::class, 'a', 'WITH', 't.id = a.tva');

            $tvas = [];
            foreach ($query->getQuery()->getResult() as $tva ){
                $tvas[] = [
                    'id' => $tva['id'],
                    'label' => $tva['label'],
                    'rate' => $tva['rate'],
                    'active' => $tva['active'],
                    'used' => $tva['used'],
                ];
            }

            return $tvas;
        } catch (\Exception $e ){
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
        foreach($datas as $data){
            $facet = $data->getFacet() ? $data->getFacet() : 'Général';
            if( !isset($options[$facet]) ){
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
            ->orderBy('d.label', 'ASC')
            ;
    }

    /**
     * @param $id
     * @return DateType
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDateType( $id )
    {
        return $this->getQueryBuilderDateType()
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getSingleResult();
    }

    public function duplicate( Activity $source, $options )
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
                    ->setComment($milestone->getComment())
                ;
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
                foreach ( $workpackage->getPersons() as $workpackagePerson ){
                    $wpPerson = new WorkPackagePerson();
                    $this->getEntityManager()->persist($wpPerson);
                    $wpPerson->setPerson($workpackagePerson->getPerson())
                        ->setDuration($workpackagePerson->getDuration())
                        ->setWorkPackage($new);

                    $this->getEntityManager()->flush($wpPerson);
                }


            }
        }



        return $newActivity;

    }

    /**
     * @param $idActivityPayment
     * @return null|ActivityPayment
     */
    public function getActivityPayment( $idActivityPayment )
    {
        return $this->getEntityManager()->getRepository(ActivityPayment::class)->find($idActivityPayment);
    }

    /**
     * @param ActivityPayment $activityPayment
     * @param bool|true $throw
     * @return bool
     * @throws \Exception
     */
    public function deleteActivityPayment( ActivityPayment $activityPayment, $throw=true )
    {
        try {
            $activityPayment->getActivity()->touch();
            $this->getEntityManager()->remove($activityPayment);
            $this->getEntityManager()->flush();
            $this->getActivityLogService()->addUserInfo(sprintf("a supprimer le verserment de %s %s sur l'activité %s", $activityPayment->getAmount(), $activityPayment->getCurrency(), $activityPayment->getActivity()->log()));
            return true;
        } catch( \Exception $e ){
            if( $throw ){
                throw new \Exception(sprintf("Impossible de supprimer le versement '%s'.", $activityPayment->getId()));
            }
            return false;
        }
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
    public function getActivityDate( $id, $throw=true )
    {
        $activityDate = $this->getEntityManager()->getRepository(ActivityDate::class)->find($id);
        if( $throw === true && !$activityDate ){
            throw new \Exception(sprintf("Échéance '%s' introuvable", $id));
        }
        return$activityDate;
    }

    /**
     * Supprime l'échéance.
     *
     * @param ActivityDate $activityDate
     * @param bool|true $throw
     * @return bool
     * @throws \Exception
     */
    public function deleteActivityDate( ActivityDate $activityDate, $throw=true )
    {
        try {
            $this->getEntityManager()->remove($activityDate);
            $this->getEntityManager()->flush();
            return true;
        } catch( \Exception $e ){
            if( $throw ){
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
    public function getTVA( $tvaId )
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
        if( $asArray === true ){
            $result = [];
            /** @var Currency $currency */
            foreach( $currencies as $currency ){
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
    public function getCurrency( $currencyId )
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
        if( $_select_currencies === null ){
            $_select_currencies = [];
            $entities = $this->getCurrencies();
            /** @var Currency $entity */
            foreach($entities as $entity){
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
    public function byOrganization( $idOrganization )
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
    public function byOrganizationWithoutProject( $idOrganization )
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
    public function byPerson( $idPerson )
    {
        return $this->getBaseQuery()
            ->where('per.id = :personId')
            ->setParameter('personId', $idPerson)
            ->orderBy('c.dateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function byPersonWithoutProject( $idPerson )
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
    public function getDisciplinesById( $ids )
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
                if ($open[count($open)-1] < $grantType->getLft()) {
                    array_pop($open);
                } else {
                    $prefix .= " - ";
                    $prefix .= " - ";
                }
                $close--;
            }

            if ($grantType->getLft()+1 == $grantType->getRgt()) {
                $prefix .= ' # ';
                $qt = '';
            } else {
                $open[] = $grantType->getRgt();
                $qt = sprintf(' (%s)', (($grantType->getRgt()-$grantType->getLft()-1)/2));
            }
            $array[$grantType->getId()] = $prefix.strval($grantType).$qt;
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

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery( $includeObsolet = false )
    {
        if( $includeObsolet === TRUE ){
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

        if( $includeObsolet === true )
            $qb->setParameter('dateRef', new \DateTime());

        return $qb;
    }
}
