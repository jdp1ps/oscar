<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class PersonService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    public function search($what){
        /** @var ProjectGrantService $activityService */
        $activityService = $this->getServiceLocator()->get('ActivityService');
        $idsActivities = $activityService->search($what);
        $idsProjects = $activityService->searchProject($what);
        return $this->getBaseQuery()
            ->leftJoin('p.projectAffectations', 'pj')
            ->leftJoin('p.activities', 'ac')
            ->where('ac.activity IN(:activityIds) OR pj.project IN (:projectIds)')
            ->setParameters([
                'activityIds' => $idsActivities,
                'projectIds' => $idsProjects,
            ]);
    }

    public function getByIds( array $ids )
    {
        $qb = $this->getBaseQuery()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids);
        return $qb->getQuery()->execute();
    }


    private $_cachePersonLdapLogin;

    /**
     * @param $login
     * @return Person
     */
    public function getPersonByLdapLogin( $login )
    {
        if( $this->_cachePersonLdapLogin === null ){
            $this->_cachePersonLdapLogin = [];
        }

        if( !isset($this->_cachePersonLdapLogin[$login]) ){
            $this->_cachePersonLdapLogin[$login] = $this->getBaseQuery()
                ->where('p.ladapLogin = :login')
                ->setParameter('login', $login)
                ->getQuery()
                ->getSingleResult();
        }
        return $this->_cachePersonLdapLogin[$login];
    }

    /**
     * @param $email
     * @return Person
     */
    public function getPersonByEmail( $email )
    {
        return $this->getBaseQuery()
            ->where('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param int $currentPage
     * @param int $resultByPage
     *
     * @return UnicaenDoctrinePaginator
     */
    public function getPersonsPaged($currentPage = 1, $resultByPage = 50)
    {
        return new UnicaenDoctrinePaginator($this->getBaseQuery(), $currentPage,
            $resultByPage);
    }

    /**
     * @param string|null $search
     * @param int         $currentPage
     * @param int         $resultByPage
     *
     * @return UnicaenDoctrinePaginator
     */
    public function getPersonsSearchPaged(
        $search = null,
        $currentPage = 1,
        $filters = [],
        $resultByPage = 50
    ) {
        $query = $this->getBaseQuery();


        // RECHERCHE sur le connector
        // Ex: rest:p00000001
        if( preg_match('/(([a-z]*):(.*))/', $search, $matches) ){
            $connector = $matches[2];
            $connectorValue = $matches[3];
            try {
                $query = $this->getEntityManager()->getRepository(Person::class)->getPersonByConnectorQuery($connector, $connectorValue);
            }catch( \Exception $e ){
                die($e->getTraceAsString());
            }
        }

        // RECHERCHE sur le nom/prenom/email
        else {
            if ($search !== null) {
                $query->where('lower(p.firstname) LIKE :search OR lower(p.lastname) LIKE :search OR lower(p.email) LIKE :search OR LOWER(CONCAT(CONCAT(p.firstname, \' \'), p.lastname)) LIKE :search OR LOWER(CONCAT(CONCAT(p.lastname, \' \'), p.firstname)) LIKE :search')
                    ->setParameter('search', '%' . strtolower($search) . '%');
            }
        }

        // FILTRE : Application des filtres sur les rôles
        if (isset($filters['filter_roles']) && count($filters['filter_roles']) > 0) {

            // Liste des ID person retenus
            $ids = [];

            ////////////////////////////////////////////////////////////////////
            // IDPERSON à partir des filtres LDAP

            // Obtention des groupes LDAP fixes sur les rôles
            // BUT : Obtenir la liste des filtres LDAP pour les rôles selectionnés
            $roles = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('r')
                ->where('r.roleId IN(:roles)')
                ->setParameter('roles', $filters['filter_roles']);

            // Rôles attribuès en dur
            /** @var SELECT * FROM person p
            INNER JOIN authentification a
            ON p.ladaplogin = a.username $fixed */
            try {
                $rsm = new Query\ResultSetMapping();
                $rsm->addScalarResult('person_id', 'person_id');;
                $native = $this->getEntityManager()->createNativeQuery(
                    'SELECT p.id as person_id FROM person p
                    INNER JOIN authentification a
                    ON p.ladaplogin = a.username

                    INNER JOIN authentification_role ar
                    ON ar.authentification_id = a.id

                    INNER JOIN user_role ur
                    ON ar.role_id = ur.id

                    WHERE ur.role_id IN (:roles)',
                    $rsm
                );

                foreach ($native->setParameter('roles', $filters['filter_roles'])->getResult() as $row) {
                    $ids[] = $row['person_id'];
                }

            } catch(\Exception $e ){
                throw $e;
            }


            $filterLdap = [];

            // Création de la cause pour la selection des personnes niveau Application
            foreach($roles->getQuery()->getResult() as $role ){
                if( $role->getLdapFilter() )
                    $filterLdap[] = "ldapmemberof LIKE '%" . preg_replace('/\(memberOf=(.*)\)/', '$1', $role->getLdapFilter()) . "%'";
            }

            if( $filterLdap ) {

                // Récupération des IDPERSON avec les filtres LDAP
                $rsm = new Query\ResultSetMapping();
                $rsm->addScalarResult('person_id', 'person_id');;
                $native = $this->getEntityManager()->createNativeQuery(
                    'select distinct id as person_id from person where ' . implode(' OR ',
                        $filterLdap),
                    $rsm
                );

                try {
                    foreach ($native->getResult() as $row) {
                        $ids[] = $row['person_id'];
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                    echo $e->getTraceAsString();
                }
            }


            ////////////////////////////////////////////////////////////////////
            // IDPERSON à partir des rôles dans :
            // - les projets
            // - les activités
            // - les organisations

            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('person_id', 'person_id');

            try {
                $native = $this->getEntityManager()->createNativeQuery(
                    'select person_id from organizationperson j inner join user_role r on r.id = j.roleobj_id where r.role_id IN (:roles)
                    UNION
                    select person_id from activityperson ap inner join user_role r on r.id = ap.roleobj_id where r.role_id IN (:roles)
                    UNION
                    select person_id from projectmember pm inner join user_role r on r.id = pm.roleobj_id where r.role_id IN (:roles)
                    ',
                    $rsm
                );

                foreach ($native->setParameter('roles',
                    $filters['filter_roles'])->getResult() as $row) {
                    $ids[] = $row['person_id'];
                };
            } catch(\Exception $e ){
                throw $e;
            }

            // On compète la requète en réduisant les résultats à la liste
            // d'ID caluclée
            $query->andWhere('p.id IN (:ids)')
                ->setParameter(':ids', $ids);
        }

        return new UnicaenDoctrinePaginator($query, $currentPage,
            $resultByPage);
    }

    /**
     * Retourne l'objet Person à partir de l'identifiant.
     *
     * @param $id
     *
     * @return Person
     */
    public function getPerson($id)
    {
        return $this->getBaseQuery()
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function getPersons()
    {
        return $this->getBaseQuery()
            ->getQuery()
            ->getResult();
    }

    const LDAP_PERSONS = '(&(eduPersonAffiliation=member)(!(eduPersonaffiliation=student)))';
    public function getLdapPersons()
    {
        return $this->getServiceLdap()->searchSimplifiedEntries(
            self::LDAP_PERSONS,
            self::STAFF_ACTIVE_OR_DISABLED,
            [],
            'cn'
        );
    }

    /**
     * Synchronise les informations depuis l'annuaire LDAP.
     *
     * @param $person
     */
    public function syncLdap(Person $person)
    {
        $ldapDatas = $this->getFromLdap($person);
        if( !$ldapDatas ){
            return;
        }
        $structureMapper = $this->getServiceLocator()->get('ldap_structure_mapper');

        // Affectation administrative
        try {
            $affectations = $ldapDatas->getAffectationsAdmin($structureMapper,
                true);
            if (count($affectations) >= 1) {
                $person->setLdapAffectation(array_values($affectations)[0]);
            }
        } catch (\Exception $e) {
        }

        try {
            $person->setLadapLogin($ldapDatas->getSupannAliasLogin())
                ->setCodeLdap($ldapDatas->getUid())
                ->setLdapDisabled($ldapDatas->getEstDesactive())
                ->setLdapSiteLocation($ldapDatas->getUcbnSiteLocalisation())
                ->setLdapStatus($ldapDatas->getUcbnStatus())
                ->setDateSyncLdap(new \DateTime())
                ->setDateUpdated(new \DateTime())
                ->setPhone($ldapDatas->getTelephoneNumber());
            $this->getEntityManager()->flush($person);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(_("Impossible de synchroniser les informations pour '%s'"),
                $person));
        }

        return true;
    }

    /**
     * Ecrase les données dans Person avec celle issue de LDAP.
     * @param Person $person
     * @param array|\UnicaenApp\Entity\Ldap\People $ldapData Données LDAP
     */
    public function pushLdapDataToPerson( Person &$person, $ldapData )
    {
        if( ! $ldapData instanceof \UnicaenApp\Entity\Ldap\People ){
            $ldapData = new \UnicaenAuth\Entity\Ldap\People($ldapData);
        }

        $localisations = $ldapData->getUcbnSiteLocalisation();
        if (is_array($localisations)){
            if( count($localisations)>0  ){
                $localisations = $localisations[0];
            } else {
                $localisations = '';
            }
        }

        $person->setLadapLogin($ldapData->getSupannAliasLogin())
            ->setLastname($ldapData->getNomUsuel())
            ->setFirstname($ldapData->getGivenName())
            ->setCodeLdap($ldapData->getUid())
            ->setCodeHarpege($this->convertCodeLdapToCodeHarpege($ldapData->getUid()))
            ->setEmail($ldapData->getMail())
            ->setLdapDisabled($ldapData->getEstDesactive())
            ->setLdapSiteLocation($localisations)
            ->setLdapStatus($ldapData->getUcbnStatus())
            ->setDateSyncLdap(new \DateTime())
            ->setDateUpdated(new \DateTime())
            ->setPhone($ldapData->getTelephoneNumber());
        return $person;
    }

    /**
     * Charge des données Ldap pour la personne donnée.
     *
     * @param Person $person
     *
     * @return \UnicaenApp\Entity\Ldap\People
     *
     * @throws \Exception
     */
    protected function getFromLdap(Person $person)
    {

        if ($person->getCodeLdap()) {
            return $this->getServiceLdap()->findOneByUid($person->getCodeLdap());
        } else {
            $datas = $this->getLdapDatasFromEmail($person->getEmail());

            if (count($datas) !== 1) {
                throw new \Exception(count($datas) === 0 ? sprintf('Aucun résultat depuis "%s"',
                    $person->getEmail()) : 'Trop de résultat');
            }

            return new \UnicaenApp\Entity\Ldap\People($datas[0]);
        }
    }

    /**
     * @param $email
     *
     * @return array
     */
    protected function getLdapDatasFromEmail($email)
    {
        return $this->getServiceLdap()->searchSimplifiedEntries(
            sprintf(self::LDAP_FILTER_EMAIL, $email),
            People::UTILISATEURS_BASE_DN,
            [],
            'cn'
        );
    }

    /**
     * Recherche dans le personnel (staff)
     */
    const LDAP_SEARCH_STAFF = '(&(|(supannAliasLogin=%s)(cn=%s*))(eduPersonAffiliation=staff))';

    public function searchStaff( $search )
    {

        $result = [];

        $oscarDatas = $this->searchInOscar($search)->getQuery()->getResult();

        /** @var Person $od */
        foreach( $oscarDatas as $od ){
            $found = false;
            /*
            foreach( $ldapDatas as $index=>$ld ){
                if( $ld['uid'] == $od->getCodeLdap() || $ld['mail'] == $od->getEmail() ){
                    $this->pushLdapDataToPerson($od, $ld);
                    $this->getEntityManager()->flush($od);
                    $result[] = $od;
                    $found = true;
                    unset($ldapDatas[$index]);
                    break;
                }
            }*/
            if( $found === false ){
                $result[] = $od;
            }
        }
        /*
        try {
            $ldapDatas = $this->searchInLdap($search);
            foreach ($ldapDatas as $ldapData) {
                $person = $this->createPersonFromLdapDatas($ldapData);
                $result[] = $person;
            }
        } catch( \Exception $e ){

        }
        */

        return $result;
    }

    /**
     * Compare la liste issue de oscar et celle issue de ldap, synchronise les
     * données Oscar avec celles de Ldap, et ajoute (si besoin) les données de
     * Ldap dans oscar.
     *
     * @param $oscarDatas
     * @param $ldapData
     */
    private function blendLdapToOscar( $oscarDatas, $ldapData )
    {

    }

    /**
     * Création de la personne à partir des données LDap.
     *
     * @param \UnicaenApp\Entity\Ldap\People|array $ldapDatas Données issues de LDap
     * @param boolean $flush Flag pour "flusher" la personne automatiquement.
     * @return Person
     * @throws \Exception
     */
    public function createPersonFromLdapDatas( $ldapDatas, $flush=true )
    {
        try {
            $person = new Person();
            $this->getEntityManager()->persist($person);
            $this->pushLdapDataToPerson($person, $ldapDatas);
            if( $flush ){
                $this->getEntityManager()->flush($person);
            }
            return $person;
        } catch( \Exception $e ){
            throw new \Exception(sprintf("Impossible de créer la personne à partir des données LDap (%s)", $e->getMessage()));
        }

    }

    private function searchInLdap( $search, $includeStudent=false )
    {
        return $this->getServiceLdap()->searchSimplifiedEntries(
            sprintf(self::LDAP_SEARCH_STAFF, $search, $search),
            self::STAFF_ACTIVE_OR_DISABLED,
            [],
            'cn'
        );
    }

    const STAFF_ACTIVE_OR_DISABLED                = 'ou=people,dc=unicaen,dc=fr';

    private function searchInOscar( $search )
    {
        $query = $this->getBaseQuery();
        if ($search !== null) {
            $query->andWhere('lower(p.firstname) LIKE :search')
                ->orWhere('lower(p.lastname) LIKE :search')
                ->orWhere('LOWER(CONCAT(CONCAT(p.firstname, \' \'), p.lastname)) LIKE :search')
                ->orWhere('LOWER(CONCAT(CONCAT(p.lastname, \' \'), p.firstname)) LIKE :search')
                ->setParameter('search', strtolower($search).'%');
        }
        return $query;
    }


    public function convertCodeHarpegeToCodeLdap($codeHarpege){
        return 'p' . str_pad("".intval($codeHarpege), 8, '0', STR_PAD_LEFT);
    }

    public function convertCodeLdapToCodeHarpege($codeLdap){
        return preg_replace("/p0*([0-9]*)/", "$1", $codeLdap);
    }

    public function getRolesLdap(){
        $roles = [];
        $ldapPersons =  $this->getServiceLdap()->searchSimplifiedEntries(
            self::LDAP_PERSONS,
            self::STAFF_ACTIVE_OR_DISABLED,
            [],
            'cn'
        );
        die('ROLES LDAP');

    }



    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Person::class, 'p');

        return $queryBuilder;
    }

    const LDAP_FILTER_EMAIL = '(mail=%s)';

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_people_service')->getMapper();
    }
}
