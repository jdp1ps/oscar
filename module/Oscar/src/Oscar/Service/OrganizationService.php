<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 10:20
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Import\Organization\ImportOrganizationLdapStrategy;
use Oscar\Strategy\Search\OrganizationSearchStrategy;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\Structure;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Organisations :
 *  - Partenaires
 *  - laboratoires
 *  - Composantes responsables.
 *
 * Class OrganizationService
 */
class OrganizationService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{

    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    private $cacheCountries = null;
    private $cacheConnectors = null;

    /**
     * @return OrganizationSearchStrategy
     */
    public function getSearchEngineStrategy()
    {
        static $searchStrategy;
        if( $searchStrategy === null ){
            $opt = $this->getServiceLocator()->get('OscarConfig')->getConfiguration('strategy.organization.search_engine');
            $class = new \ReflectionClass($opt['class']);
            $searchStrategy = $class->newInstanceArgs($opt['params']);
        }
        return $searchStrategy;
    }

    public function search($expression)
    {
        $ids = $this->getSearchEngineStrategy()->search($expression);
        if( count($ids) ){
            $query = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder('o');
            $query->where('o.id IN(:ids)')->setParameter('ids', $ids);
            return $query->getQuery()->getResult();
        } else {
            return [];
        }
    }


    public function searchDelete( $id )
    {
        $this->getSearchEngineStrategy()->remove($id);
    }

    public function searchUpdate( Person $person )
    {
        $this->getSearchEngineStrategy()->update($person);
    }

    public function searchIndex_reset()
    {
        $this->getSearchEngineStrategy()->resetIndex();
    }

    public function searchIndexRebuild(){
        $this->searchIndex_reset();
        $persons = $this->getEntityManager()->getRepository(Organization::class)->findAll();
        return $this->getSearchEngineStrategy()->rebuildIndex($persons);
    }

    /**
     * Retourne la liste des Roles disponible pour une organisation dans une activité.
     */
    public function getAvailableRolesOrganisationActivity(){
        return $this->getEntityManager()->getRepository(OrganizationRole::class)->findAll();
    }

    public function deleteOrganization( $id ){
        $o = $this->getOrganization($id);
        $this->getEntityManager()->remove($o);
        $this->getEntityManager()->flush();
    }

    public function getConnectorsList()
    {
        if( $this->cacheConnectors == null ){
            $this->cacheConnectors = [];
            // todo Utiliser le service qui gère l'accès à la configuration
            $config = $this->getServiceLocator()->get('Config');

            $paths = explode('.', 'oscar.connectors.organization');
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    throw new \Exception("Clef $path absente dans la configuration");
                }
                $config = $config[$path];
            }
            $this->cacheConnectors = array_keys($config);
        }
        return $this->cacheConnectors;
    }

    public function getCountriesList()
    {
        if( $this->cacheCountries == null ){
            $this->cacheCountries = [];
            $countries = $this->getEntityManager()->createQueryBuilder()
                ->select('o.country')
                ->from(Organization::class, 'o')
                ->distinct('o.country')
                ->getQuery()
                ->getScalarResult();
            foreach($countries as $r ){
                $this->cacheCountries[] = $r['country'];
            }
        }
        return $this->cacheCountries;
    }

    public function getActivities( $organizationId )
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('a, p, o, o, pe, m')
            ->from(Activity::class, 'a')
            ->innerJoin('a.organizations', 'p')
            ->innerJoin('p.organization', 'o')
            ->innerJoin('a.persons', 'm')
            ->innerJoin('m.person', 'pe')
            ->where('o.id = :id')
            ->orderBy('a.dateCreated', 'DESC')
            ->setParameter('id', $organizationId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des activités d'un organisation où elle a un role principal actif.
     *
     * @param Organization $o
     * @return array
     */
    public function getOrganizationActivititiesPrincipalActive( Organization $o ){
        $activities = [];
        /** @var ActivityOrganization $activity */
        foreach ($o->getActivities() as $activity ){
            if( $activity->isPrincipal() && !$activity->isOutOfDate() ){
                if( !in_array($activity->getActivity(), $activities) )
                    $activities[] = $activity->getActivity();
            }
        }

        /** @var ProjectPartner $p */
        foreach ($o->getProjects() as $p ){
            if( $p->isPrincipal() && !$p->isOutOfDate() ){
                foreach ($p->getProject()->getActivities() as $activity) {
                    if( !in_array($activity, $activities) )
                        $activities[] = $activity;
                }
            }
        }

        return $activities;
    }

    public function getOrganizationTypes(){

        $types = [];
        $result = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy(['root' => null], ['label' => 'DESC']);

        /** @var OrganizationType $type */
        foreach ($result as $type ) {
            $types[$type->getId()] = $type->toJson();
        }

        return $types;
    }

    public function getTypes(){

        $types = Organization::getTypes();

        $query = $this->getEntityManager()->createQueryBuilder('o')
            ->select('o.type')
            ->from(Organization::class, 'o')
            ->distinct()
            ->getQuery();

        foreach ($query->getResult(Query::HYDRATE_ARRAY) as $type ) {
            $t = $type['type'];
            if (!in_array($t, $types) && $t != null)
                $types[] = $t;
        }
        sort($types);
        return $types;
    }

    public function getOrganizationTypesSelect(){
        $options = [];

        $types = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy([], ['label' => 'DESC']);
        foreach ($types as $type) {
            $options[$type->getId()] = $type;
        }
        return $options;


    }

    /**
     * Retourne le résultat de la recherche $search.
     *
     * @param string $search
     *
     * @return Organization[]
     */
    public function getOrganizationsSearchPaged($search, $page, $filter=[])
    {
        $qb = $this->getBaseQuery();
        $ids = [];

        if ($search) {

            // Recherche sur le connector
            $reg = preg_match('/([a-z]*)=(.*)/', $search, $matches);
            if( $reg ){
                $connectors = $this->getConnectorsList();
                $connectorName = $matches[1];
                if( !in_array($connectorName, $connectors) ){
                    throw new OscarException("Le connecteur $connectorName n'existe pas.");
                }
                $connectorValue = $matches[2].'%';
                $where = 'o.connectors LIKE \'%"'.$connectorName.'";s:%:"'.$connectorValue.'"%\'';
                $qb->orWhere($where);
            }
            else {

                if( $this->getSearchEngineStrategy() ){
                    $ids = $this->getSearchEngineStrategy()->search($search);
                    $qb->where('o.id IN(:ids)')->setParameter('ids', $ids);
                }
                else {
                    $qb
                        ->orWhere('LOWER(o.shortName) LIKE :search')
                        ->orWhere('LOWER(o.fullName) LIKE :search')
                        ->orWhere('LOWER(o.city) LIKE :search')
                        ->orWhere('o.zipCode = :searchStrict')
                        ->orWhere('LOWER(o.code) LIKE :search');

                    if (strlen($search) == 14)
                        $qb->orWhere('o.siret = :searchStrict');


                    $qb->setParameters([
                            'search' => '%' . strtolower($search) . '%',
                            'searchStrict' => strtolower($search),
                        ]
                    );
                }
            }
        }

        if (isset($filter['type']) && $filter['type']){
            $types = $this->getEntityManager()->getRepository(OrganizationType::class)->createQueryBuilder('t')
                ->where('t.id IN (:types)')
                ->setParameter('types', $filter['type'])
                ->getQuery()
                ->getResult();

            $qb->leftJoin('o.typeObj', 't')
                ->andWhere('t.id IN(:type)')->setParameter('type', $types);
        }

        if (isset($filter['active']) && $filter['active']){
            if( $filter['active'] == 'ON' ){
                $qb->andWhere('o.dateEnd IS NULL OR o.dateEnd > :now')->setParameter('now', new \DateTime());
            }
            else if( $filter['active'] == 'OFF' ){
                $qb->andWhere('o.dateEnd < :now')->setParameter('now', new \DateTime());
            }
        }

        if (isset($filter['roles']) && count($filter['roles'])) {
            $ids = [];
            $roles = $this->getEntityManager()->getRepository(ProjectPartner::class)->createQueryBuilder('r')
                ->where('r.role IN (:roles)')
                ->setParameter('roles', $filter['roles'])->getQuery()->getResult();
            /** @var ProjectPartner $role */
            foreach( $roles as $role ){
                if( !in_array($role->getOrganization()->getId(), $ids) ){
                    $ids[] = $role->getOrganization()->getId();
                }
            }

            $qb->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if( count($ids) ){
            // On ne trie que les 30 premiers
            $limit = 30;
            $nbr = 0;
            $case = '(CASE ';
            $i = 0;
            foreach ($ids as $id) {
                if( $nbr++ < $limit )
                    $case .= sprintf('WHEN o.id = \'%s\' THEN %s ', $id, $i++);
            }
            $case .= " ELSE $id END) AS HIDDEN ORD";
            $qb->addSelect($case);
            $qb->orderBy("ORD", 'ASC');
        }

        return new UnicaenDoctrinePaginator($qb, $page);
    }

    /**
     * @param $id
     *
     * @return Organization
     */
    public function getOrganization($id)
    {
        return $this->getBaseQuery()->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return Organization[]
     */
    public function getOrganizations()
    {
        return $this->getBaseQuery()->getQuery()->getResult();
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Organization::class, 'o');
        return $queryBuilder;
    }

}
