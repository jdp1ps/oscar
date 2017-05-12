<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorPersonOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\ProjectMember;
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
        if ($search !== null) {
            $query->andWhere('lower(p.firstname) LIKE :search')
                ->orWhere('lower(p.lastname) LIKE :search')
                ->orWhere('LOWER(CONCAT(CONCAT(p.firstname, \' \'), p.lastname)) LIKE :search')
                ->orWhere('LOWER(CONCAT(CONCAT(p.lastname, \' \'), p.firstname)) LIKE :search')
                ->setParameter('search', '%'.strtolower($search).'%');
        }
        if( isset($filters['filter_roles']) && count($filters['filter_roles'])>0 ){
            $query->leftJoin('p.projectAffectations', 'pj')
                ->leftJoin('p.activities', 'ac')
                ->andWhere('ac.role IN(:roles) OR pj.role IN (:roles)')
                ->setParameter('roles', $filters['filter_roles']);
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

    public function synchronizeRole( Person $person )
    {
        $connector = new ConnectorPersonOrganization('ldap', 'toto',
            $this->getEntityManager()->getRepository(Person::class),
            $this->getEntityManager()->getRepository(Organization::class),
            $this->getServiceLdap(),
            []);

        //

        var_dump($connector->synchronizePerson($person));
    }


    public function synchronize( Person $person )
    {
        $this->synchronizeRole($person);
        die("Synchronisation pour $person");
        try {
            /** @var \UnicaenApp\Entity\Ldap\People $ldapDatas */
            $ldapDatas = $this->getFromLdap($person);
        } catch( \Exception $e ){
            return;
        }

        if( !$ldapDatas ){
            return;
        }

        foreach($ldapDatas->getSupannRolesEntiteToArray('R00') as $role ){
            $structureCode = $role['code'];
            if( stripos($structureCode, 'HS_') === 0 ){
                $structureCode = substr($structureCode, 3);
            }

            try {
                /** @var Organization $structure */
                $structure = $this->getEntityManager()->getRepository(Organization::class)->findOneBy(['code' => $structureCode]);
                if( !$structure ) {
                    continue;
                }

                if( !$structure->hasResponsable($person) ){
                    echo '<h4>'.$structureCode.'</h4>';
                    $organizationPerson = new OrganizationPerson();
                    $this->getEntityManager()->persist($organizationPerson);
                    $organizationPerson->setPerson($person)->setOrganization($structure)->setRole(ProjectMember::ROLE_RESPONSABLE);
                    $this->getEntityManager()->flush($organizationPerson);
                }
            }
            catch( \Exception $e ){

            }
        }
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
