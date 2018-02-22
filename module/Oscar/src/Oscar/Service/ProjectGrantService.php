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
use Oscar\Entity\Project;
use Oscar\Entity\ProjectGrantRepository;
use Oscar\Entity\Role;
use Oscar\Entity\TVA;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Utils\StringUtils;
use Oscar\Validator\EOTP;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenLdap\Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProjectGrantService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @return ProjectGrantRepository
     */
    protected function getActivityRepository()
    {
       return $this->getEntityManager()->getRepository(Activity::class);
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

        $this->getServiceLocator()->get('Logger')->info("Ajout de $activity à l'index");

        $members = [];
        /** @var ActivityPerson $p */
        foreach ($activity->getPersonsDeep() as $p) {
            $members[] = $p->getPerson()->getCorpus();
        }
        $members = implode(', ', $members);

        $partners = [];
        /** @var ActivityOrganization $o */
        foreach ($activity->getOrganizationsDeep() as $o ) {
            $partners[] = $o->getOrganization()->getCorpus();
        }
        $partners = implode(', ', $partners);

        $project = '';
        $acronym = '';
        if( $activity->getProject() ){
            $project = $activity->getProject()->getCorpus();
            $acronym = $activity->getProject()->getAcronym();
        }

        $corpus = new \Zend_Search_Lucene_Document();

        $corpus->addField(\Zend_Search_Lucene_Field::text('acronym', StringUtils::transliterateString($acronym), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('label', StringUtils::transliterateString($activity->getLabel()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('description', StringUtils::transliterateString($activity->getDescription()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('saic', $activity->getCentaureId(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('oscar', $activity->getOscarNum(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('eotp', $activity->getCodeEOTP(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('members', $members, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('partners', $partners, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('project', $project, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('key', md5($activity->getId()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::UnIndexed('ID', $activity->getId(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::UnIndexed('project_id', $activity->getProject() ? $activity->getProject()->getId() : '', 'UTF-8'));


        $this->searchIndex_getIndex()->addDocument($corpus);
    }


    private function searchIndex_getPath(){
        return $this->getServiceLocator()->get('OscarConfig')->getConfiguration('paths.search_activity');
    }

    public function searchIndex_reset()
    {
        $this->index = \Zend_Search_Lucene::create($this->searchIndex_getPath());
    }

    private function searchIndex_checkPath()
    {
        $path = $this->searchIndex_getPath();
        return file_exists($path) && is_readable($path) && ($resources = scandir($path)) && (count($resources) > 2);
    }

    /**
     * Retourne les jalons de l'activités.
     *
     * @param $idActivity
     * @return array
     */
    public function getMilestones( $idActivity ){

        // Droit d'accès
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);

        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getServiceLocator()->get('OscarUserContext');

        $oscarUserContext->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

        $deletable = $editable = $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);

        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $idActivity)->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $out = [];
        $now = new \DateTime();
        foreach( $dates as $data ){
            $data['deletable'] = true;
            $data['past'] = ($data['dateStart']<$now);
            $data['css'] = ($data['dateStart']<$now) ? 'past' : '';
            $data['deletable'] = $deletable;
            $data['editable'] = $editable;
            $data['validable'] = $editable;

            $out[$data['dateStart']->format('YmdHis').$data['id']] = $data;
        }

        //  versements sous la forme JALON
        $versementsQB = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->addSelect('p')
            ->innerJoin('p.activity', 'a')
            ->where('p.status = :status')
            ->andWhere('a.id = :idactivity');

        $versements = $versementsQB->setParameters([
            'idactivity' => $idActivity,
            'status' => ActivityPayment::STATUS_PREVISIONNEL
        ])->getQuery()->getResult();


        $currencyFormatter = new \Oscar\View\Helpers\Currency();
        /** @var ActivityPayment $v */
        foreach( $versements as $v ){

            /** @var \DateTime $dateRef */
            $dateRef = $v->getDatePayment() ? $v->getDatePayment() : $v->getDatePredicted();

            $out[$dateRef->format('YmdHis'.'v'.$v->getId())] = [
                'dateStart' => $dateRef,
                'deletable' => false,
                'css' => ($v->getDatePredicted()<$now) ? 'jalon-warn' : '',
                'past' => ($v->getDatePredicted()<$now),
                'comment' => ($v->getDatePredicted()<$now) ? 'Ce versement aurait dû être réalisé.' : 'Versement prévu',
                'id' => $v->getId(),
                'deletable' => false,
                'editable' => false,
                'validable' => false,
                'type' => [
                    'label' => 'Versement de ' . $currencyFormatter->format($v->getAmount()). ' ' . $v->getSymbol(),
                    'facet' => 'payment'
                ]
            ];
        }


        ksort($out, SORT_STRING);




        return $out;
    }

    public function searchIndex_rebuild()
    {
        echo " # Reconstruction de l'index de recherche\n";
        $this->searchIndex_reset();
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findAll();
        echo sprintf("%s activités vont être indexées...\n", count($activities));
        foreach($activities as $activity) {
            $this->searchIndex_addToIndex($activity);
        }
    }


    public function specificSearch( $what, &$qb, $activityAlias='c' )
    {
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
        elseif (preg_match("/^[0-9]{4}DRI.*/mi", $what)) {
            $qb->andWhere($activityAlias.'.oscarNum LIKE :'.$fieldName)
                ->setParameter($fieldName, $what.'%');
        }

        // Saisie 'libre'
        else {
            return false;
        }
        return true;
    }

    public function search($what)
    {
        // try {
            $what = StringUtils::transliterateString($what);
            $query = \Zend_Search_Lucene_Search_QueryParser::parse($what);
            $this->getServiceLocator()->get('Logger')->info(sprintf('Search for "%s"',
                $what));
            $hits = $this->searchIndex_getIndex()->find($query);
            $ids = [];
            foreach ($hits as $hit) {
                $ids[] = $hit->ID;
            }

            return $ids;
//        } catch( \Exception $e ){
//            throw new OscarException(sprintf("Recherche invalide, Lucene a retourné : %s (%s)", $e->getMessage(), get_class($e)));
//        }
    }

    public function searchProject($what)
    {
        $query = \Zend_Search_Lucene_Search_QueryParser::parse($what);
        $hits = $this->searchIndex_getIndex()->find($query);
        $ids = [];
        foreach ($hits as $hit) {
            if( $hit->project_id && !in_array($hit->project_id, $ids) ){
                $ids[] = $hit->project_id;
            }
        }
        return $ids;
    }

    public function searchDelete( $id )
    {
        $hits = $this->searchIndex_getIndex()->find('key:'.md5($id));
        if( count($hits) !== 1 ){
            //throw new \Exception(sprintf("Incohérence de l'index de recherche avec l'objet %s, il n'est probablement pas encore indexé", $id));
        }

        foreach ($hits as $hit) {
            $this->getServiceLocator()->get('Logger')->info("Suppression de l'index $id");
            $this->searchIndex_getIndex()->delete($hit->id);
        }
    }

    public function searchUpdate( Activity $activity )
    {
        $this->getServiceLocator()->get('Logger')->info(sprintf("Mise à jour de l'index de recherche pour [%s]%s", $activity->getId(), $activity));
        try {
            $this->searchDelete($activity->getId());
        } catch(\Exception $e ){
            $this->getServiceLocator()->get('Logger')->error(sprintf("Impossible de supprimer l'ancien index de [%s]%s : %s", $activity->getId(), $activity, $e->getMessage()));
        }

        $this->searchIndex_addToIndex($activity);
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
        var_dump(Activity::getStatusSelect());
        die();
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

    public function duplicate( Activity $source )
    {
        $qb = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.type', 't')
            ->leftJoin('a.source', 's')
            ->leftJoin('a.tva', 'tv')
            ->leftJoin('a.currency', 'c')
            ->leftJoin('a.project', 'p')
            ->leftJoin('a.persons', 'pe')
            ->leftJoin('pe.person', 'pr')
            ->leftJoin('a.organizations', 'or')
            ->leftJoin('or.organization', 'og')
            ->where('a.id = :id')
        ;

        /** @var Activity $source */
        $source = $qb->setParameter('id', $source->getId())->getQuery()->getSingleResult();

        $newActivity = new Activity();

        $this->getEntityManager()->persist($newActivity);

        $newActivity->setProject($source->getProject())
            ->setType($source->getType())
            ->setSource($source->getSource())
            ->setTva($source->getTva())
            ->setCurrency($source->getCurrency())
            ->setLabel('Copie de ' . $source->getLabel())
            ->setDescription('')
            ->setAmount(0.0);

        $this->getEntityManager()->flush($newActivity);

        /** @var ActivityOrganization $partner */
        foreach($source->getOrganizations() as $partner ){
            $newPartner = new ActivityOrganization();
            $this->getEntityManager()->persist($newPartner);
            $newPartner->setOrganization($partner->getOrganization())
                ->setRoleObj($partner->getRoleObj())
                ->setActivity($newActivity)
                ->setDateStart($partner->getDateStart())
                ->setDateEnd($partner->getDateEnd());
            $this->getEntityManager()->flush($newPartner);
        }

        /** @var ActivityPerson $partner */
        foreach($source->getPersons() as $member ){
            $newMember = new ActivityPerson();
            $this->getEntityManager()->persist($newMember);
            $newMember->setPerson($member->getPerson())
                ->setActivity($newActivity)
                ->setRoleObj($member->getRoleObj())
                ->setDateStart($member->getDateStart())
                ->setDateEnd($member->getDateEnd());
            $this->getEntityManager()->flush($newMember);
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
     * @return GrantSource[]
     */
    public function getSources()
    {
        $array = [];
        foreach ($this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(GrantSource::class, 's')
            ->getQuery()
            ->getResult() as $grantSource) {
            $array[$grantSource->getId()] = strval($grantSource);
        }
        return $array;
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
            ->select('c, p, m, per, org, src, t, d, pr', 'at', 'org', 'dt')
            ->from(Activity::class, 'c')
            ->leftJoin('c.organizations', 'p', Query\Expr\Join::WITH, 'p.status = 1' . $roleClaude)
            ->leftJoin('c.persons', 'm', Query\Expr\Join::WITH, 'm.status = 1' . $roleClaude)
            ->leftJoin('m.person', 'per')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('c.activityType', 'at')
            ->leftJoin('c.source', 'src')
            ->leftJoin('c.type', 't')
            ->leftJoin('c.documents', 'd')
            ->leftJoin('d.typeDocument', 'dt')

            ->leftJoin('p.organization', 'org');

        if( $includeObsolet === true )
            $qb->setParameter('dateRef', new \DateTime());

        return $qb;
    }
}
