<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 10:20
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Oscar\Entity\Activity;
use Oscar\Entity\Organization;
use Oscar\Entity\ProjectPartner;
use Oscar\Import\Organization\ImportOrganizationLdapStrategy;
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
     * Retourne le résultat de la recherche $search.
     *
     * @param string $search
     *
     * @return Organization[]
     */
    public function getOrganizationsSearchPaged($search, $page, $filter=[])
    {
        $qb = $this->getBaseQuery();
        if ($search) {
            $qb->orderBy('o.code', 'ASC')
                ->orWhere('LOWER(o.shortName) LIKE :search')
                ->orWhere('LOWER(o.fullName) LIKE :search')
                ->orWhere('LOWER(o.city) LIKE :search')
                ->orWhere('o.zipCode = :searchStrict')
                ->orWhere('LOWER(o.code) LIKE :search');

            if( strlen($search) == 14 )
                $qb->orWhere('o.siret = :searchStrict');


            $qb->setParameters([
                'search' => '%'.strtolower($search).'%',
                'searchStrict' => strtolower($search),
                ]
            );

        } else {
            $qb->orderBy('o.id', 'DESC');
        }

        if (isset($filter['type']) && $filter['type']){
            $qb->andWhere('o.type IN(:type)')->setParameter('type', $filter['type']);
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

    const STRUCTURES_BASE_DN = 'ou=structures,dc=unicaen,dc=fr';
    const STAFF_ACTIVE_OR_DISABLED                = 'ou=people,dc=unicaen,dc=fr';

    private function areSameOrganization( Organization $organizationA, Organization $organizationB ){
        if( $organizationA->getCentaureId() == $organizationB->getCentaureId() ){
            return true;
        }
        ?><pre>;
        <?= $organizationA ?>
        <?= $organizationB ?>
        </pre>
        <?php
    }
    
    public function syncLdap($router)
    {
        $sync = new ImportOrganizationLdapStrategy($this->getServiceLdap(), $this->getEntityManager(), $router);
        return $sync->importAll();
    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\Structure
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_structure_service')->getMapper();
    }
}
