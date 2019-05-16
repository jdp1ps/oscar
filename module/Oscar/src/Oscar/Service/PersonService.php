<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\AuthentificationRepository;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\PrivilegeRepository;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\Referent;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
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


    /**
     * Retourne la configuration OSCAR.
     *
     * @return ConfigurationParser
     */
    protected function getOscarConfig()
    {
        return $this->getServiceLocator()->get('OscarConfig');
    }

    /**
     * @return PersonRepository
     */
    public function getRepository(){
        return $this->getEntityManager()->getRepository(Person::class);
    }

    public function getManagers( $person ){
        if( !$person ) return [];

        $qb = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin(Referent::class, 'r', 'WITH', 'r.referent = p')
            ->where('r.person = :person');

        return $qb->setParameter('person', $person)->getQuery()->getResult();
    }

    public function getSubordinates( $person ){
        if( !$person ) return [];

        $qb = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin(Referent::class, 'r', 'WITH', 'r.person = p')
            ->where('r.referent = :person');

        return $qb->setParameter('person', $person)->getQuery()->getResult();
    }

    public function addReferent( $referent_id, $person_id ){
        $referent = $this->getPerson($referent_id);
        $person = $this->getPerson($person_id);

        // @todo Vérifier si le référent n'est pas déjà identifié

        $referentRec = new Referent();
        $this->getEntityManager()->persist($referentRec);
        $referentRec->setPerson($person)->setReferent($referent);
        $this->getEntityManager()->flush($referentRec);

        return true;
    }

    public function removeReferentById( $referent_id ){
        $referent = $this->getEntityManager()->getRepository(Referent::class)->find($referent_id);
        $this->getEntityManager()->remove($referent);
        $this->getEntityManager()->flush();
        return true;
    }



    public function getReferentsPerson( $personId ){
        $query = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->where('r.person = :personId')
            ->setParameter('personId', $personId);
        return $query->getQuery()->getResult();
    }

    public function getSubordinatesPerson( $personId ){
        $query = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->where('r.referent = :personId')
            ->setParameter('personId', $personId);
        return $query->getQuery()->getResult();
    }



    public function getNP1( Person $person, $date = null ){
        // @todo
    }

    /**
     * Lance la procédure de relance par email pour les personnes ayant souscrit à
     * la relance automatique et ayant des notifications non-lues.
     *
     * @param string $dateRef
     */
    public function mailPersonsWithUnreadNotification( $dateRef = "" ){
        $date = new \DateTime($dateRef);

        $rel = [
          'Mon' => 'Lun',
          'Tue' => 'Mar',
          'Wed' => 'Mer',
          'Thu' => 'Jeu',
          'Fri' => 'Ven',
          'Sat' => 'Sam',
          'Sun' => 'Dim',
        ];

        // Fromat du cron
        $cron = $rel[$date->format('D')].$date->format('G');

        $this->getLoggerService()->info("Notifications des inscrits à '$cron'");

        // Liste des personnes ayant des notifications non-lues
        $persons = $this->getRepository()->getPersonsWithUnreadNotificationsAndAuthentification();
        $this->getLoggerService()->info(sprintf(" %s personne(s) ont des notifications non-lues", count($persons)));

        /** @var Person $person */
        foreach ($persons as $person) {
            /** @var Authentification $auth */
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $person->getLadapLogin()]);
            $settings = $auth->getSettings();

            if( !$settings ){
                $settings = [];
            }

            if( !array_key_exists('frequency', $settings) ){
                $settings['frequency'] = [];
            }

            $settings['frequency'] = array_merge($settings['frequency'], $this->getServiceLocator()->get('OscarConfig')->getConfiguration('notifications.fixed'));

            if( in_array($cron, $settings['frequency']) ){
                $this->mailNotificationsPerson($person);
            } else {
                $this->getLoggerService()->info(sprintf('%s n\'est pas inscrite à ce crénaux', $person));
            }
        }
    }


    protected function getDateMoment($date){
        $moment = new Moment($date);
        return $moment->format('l d F Y');
    }


    public function mailNotificationsPerson( $person, $debug = true ){
        /** @var ConfigurationParser $configOscar */
        $configOscar = $this->getServiceLocator()->get('OscarConfig');

        if( $debug ){
            $log = function($msg){
                $this->getLoggerService()->debug($msg);
            };
        } else {
            $log = function(){};
        }

        $datas = $this->getNotificationService()->getNotificationsPerson($person->getId(), true);
        $notifications = $datas['notifications'];

        if( count($notifications) ==  0 ){
            $log(sprintf(" - Pas de notifications non-lues pour %s", $person));
            return;
        }

        $url = $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('url');

        $reg = '/(.*)\[Activity:([0-9]*):(.*)\](.*)/';

        $content = "Bonjour $person, <br>\n";
        $content .= "Vous avez des notifications non-lues sur Oscar : \n";
        $content .= "<ul>\n";

        Moment::setLocale('fr_FR');

        foreach ($notifications as $n) {
            $moment = new Moment($n['dateEffective']);
            $formatted = $moment->format('l d F Y');
            $since = $moment->from('now')->getRelative();

            $message = $n['message'];
            if( preg_match($reg, $n['message'], $matches) ){
                $link = $configOscar->getConfiguration("urlAbsolute").$url('contract/show',array('id' => $matches[2]));
                $message = preg_replace($reg, '$1 <a href="'.$link.'">$3</a> $4', $n['message']);
            }
            $content .= "<li><strong>".$formatted." (".$since.") : </strong> " .$message."</li>\n";
        }

        /** @var MailingService $mailer */
        $mailer = $this->getServiceLocator()->get("mailingService");
        $to = $person->getEmail();
        $content .= "</ul>\n";
        $mail = $mailer->newMessage("Notifications en attente sur Oscar", ['body' => $content]);
        $mail->setTo([$to => (string) $person]);
        $mailer->send($mail);
    }

    public function getRolesPrincipaux( $privilege = null){
        $query = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('r');
        if( $privilege != null ){
            $query->innerJoin('r.privileges', 'p')
                ->where('p.code = :privilege')
            ->setParameter('privilege', $privilege);
        }

        return $query->getQuery()->getResult();

    }


    /**
     * Retourne la liste des organizations où la personne a un rôle principale.
     *
     * @param Person $person
     */
    public function getOrganizationsPersonWithPrincipalRole(Person $person){


        $roles = $this->getOscarUserContext()->getRoleIdPrimary();

        $structures = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder('o')
            ->innerJoin('o.persons', 'p')
            ->innerJoin('p.roleObj', 'r')
            ->where('p.person = :person AND r.roleId IN(:roles)')
            ->setParameters([
               'person'    => $person,
               'roles'     => $roles,
            ])
            ->getQuery()
            ->getResult();

        return $structures;
    }

    /**
     * Charge en profondeur la liste des personnes disposant du privilége sur une
     * activité. (Beaucoup de requêtes, attention ux perfs)
     *
     * @param $privilegeFullCode
     * @param $activity
     */
    public function getAllPersonsWithPrivilegeInActivity( $privilegeFullCode, Activity $activity, $includeApp=false )
    {
        // Résultat
        $persons = [];

        /** @var PrivilegeRepository $privilegeRepository */
        $privilegeRepository = $this->getEntityManager()->getRepository(Privilege::class);

        try {
            $rolesIds = []; // rôles
            $ldapFilters = []; // filtre LDAP
            // 1. Récupération des rôles associès au privilège
            $privilege = $privilegeRepository->getPrivilegeByCode($privilegeFullCode);




            foreach ($privilege->getRole() as $role) {
                $rolesIds[] = $role->getRoleId();
                if ($role->getLdapFilter()) {
                    $ldapFilters[] = preg_replace('/\(memberOf=(.*)\)/',
                        '$1', $role->getLdapFilter());
                }
            }

            if( $includeApp ) {

                // Selection des personnes qui ont le filtre LDAP (Niveau applicatif)
                if ($ldapFilters) {
                    $clause = [];
                    foreach ($ldapFilters as $f) {
                        $clause[] = "p.ldapMemberOf LIKE '%$f%'";
                    }

                    $personsLdap = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
                        ->where(implode(' OR ', $clause))
                        ->getQuery()
                        ->getResult();

                    foreach ($personsLdap as $p) {
                        $persons[$p->getId()] = $p;
                    }
                }

                // Selection des personnes via l'authentification (Affectation en dur, niveau applicatif)
                $authentifications = $this->getEntityManager()->createQueryBuilder()
                    ->select('a, r')
                    ->from(Authentification::class, 'a')
                    ->innerJoin('a.roles', 'r')
                    ->getQuery()
                    ->getResult();

                foreach ($authentifications as $auth) {
                    if ($auth->hasRolesIds($rolesIds)) {
                        try {
                            $person = $this->getEntityManager()->getRepository(Person::class)->findOneBy(['ladapLogin' => $auth->getUsername()]);
                            if ($person) {
                                $persons[$person->getId()] = $person;
                            }
                        } catch (\Exception $e) {
                            echo "Error : " . $e->getMessage() . "<br>\n";
                        }
                    }
                }
            }

            // Selection des personnes associées via le Projet/Activité

            foreach ($activity->getPersonsDeep() as $p ){
                if( in_array($p->getRole(), $rolesIds) ){
                    $persons[$p->getPerson()->getId()] = $p->getPerson();
                }
            }

            // Selection des personnes via l'oganisation assocociée au Projet/Activité
            /** @var Organization $organization */
            foreach ($activity->getOrganizationsDeep() as $organization ){

                /** @var OrganizationPerson $personOrganization */
                if( $organization->isPrincipal() ) {
                    foreach ($organization->getOrganization()->getPersons(false) as $personOrganization) {
                        if (in_array($personOrganization->getRole(), $rolesIds)) {
                            $persons[$personOrganization->getPerson()->getId()] = $personOrganization->getPerson();
                        }
                    }
                }
            }

            return $persons;

        } catch ( \Exception $e ){
            throw new OscarException("Impossible de trouver les personnes : " . $e->getMessage());
        }
    }

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
            $query = $this->getBaseQuery()
                ->setParameter('login', $login);

            $normalize = $this->getOscarConfig()->getConfiguration('authPersonNormalize', false);

            if( $normalize == true ){
                
                $query->where('LOWER(p.ladapLogin) = :login')
                    ->setParameter('login', strtolower($login));
            } else {
                $query->where('p.ladapLogin = :login')
                    ->setParameter('login', $login);
            }

            $this->_cachePersonLdapLogin[$login] = $query->getQuery()->getSingleResult();
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
     * @return array
     * @throws \Exception
     */
    public function getRolesByAuthentification(){
        try {
            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('login', 'login');
            $rsm->addScalarResult('roleid', 'roleid');

            $native = $this->getEntityManager()->createNativeQuery(
                'SELECT a.username as login, ur.role_id as roleid FROM authentification a
                    INNER JOIN authentification_role ar
                    ON ar.authentification_id = a.id
                    INNER JOIN user_role ur
                    ON ar.role_id = ur.id',
                $rsm
            );

            $out = [];

            foreach ($native->getResult() as $row) {
                if( !array_key_exists($row['login'], $out) ){
                    $out[$row['login']] = [];
                }
                $out[$row['login']][] = $row['roleid'];
            }

            return $out;

        } catch(\Exception $e ){
            throw $e;
        }
    }

    public function getPersonAuthentification( Person $person ){
        /** @var AuthentificationRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Authentification::class);

        return $repo->getAuthentificationPerson($person);
    }

    /**
     * Retourne la liste des roles de la person définit manuellement sur l'authentification ou obtenu via les
     * groupes LDAP
     * @return Role[]
     */
    public function getRolesApplication(Person $person){

        /** @var Role[] $inRoles */
        $inRoles = [];

        // Récupération des rôles via l'authentification
        $authentification = $this->getPersonAuthentification($person);

        /** @var Role $role */
        foreach ($authentification->getRoles() as $role) {
            $inRoles[$role->getRoleId()] = $role;
        }

        if( $person->getLdapMemberOf() ){

            // Récupération des rôles avec des filtres LDAP
            $roles = $this->getEntityManager()->getRepository(Role::class)->getRolesLdapFilter();
            /** @var Role $role */
            foreach ($roles as $role) {

                // Le rôle est déjà présent "en dur"
                if( array_key_exists($role->getRoleId(), $inRoles) ) continue;

                // Test des rôles via le filtreLDAP
                $roleLdapFilter = $role->getLdapFilter();

                foreach ($person->getLdapMemberOf() as $memberOf) {
                    if( strpos($roleLdapFilter, $memberOf) >= 0 ){
                        $inRoles[$role->getRoleId()] = $role;
                        continue 2;
                    }
                }
            }
        }

        return $inRoles;
    }

    /**
     * @param Person $person
     * @return string[]
     */
    public function getRolesApplicationArray(Person $person){
        return array_keys($this->getRolesApplication($person));
    }

    public function getRolesAuthentification(Authentification $authentification){
        return $authentification->getRoles();
    }

    public function getCoWorkerIds( $idPerson ){
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getEntityManager()->getRepository(Organization::class);

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        // Organisations où la person est "principale"
        $orgaIds = $organizationRepository->getOrganizationsIdsForPerson($idPerson, true);

        if( count($orgaIds) == 0 ){
            return [];
        }

        // IDS des membres des organisations
        $coWorkersIds = $personRepository->getPersonIdsInOrganizations($orgaIds);

        // Inclue les personnes impliquées dans des activités ?
        $listPersonIncludeActivityMember = $this->getServiceLocator()->get('OscarConfig')->getConfiguration('listPersonnel');
        if( $listPersonIncludeActivityMember == 3 ) {
            $engaged = $personRepository->getPersonIdsForOrganizationsActivities($orgaIds);

            $coWorkersIds = array_unique(array_merge($coWorkersIds, $engaged));
        }


        return $coWorkersIds;
    }

    public function getSubordinateIds( $idPerson ){

        // Récupération des subordonnés
        $nm1 = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->innerJoin('r.person', 'p')
            ->select('p.id')
            ->where('r.referent = :person')
            ->setParameters([
                'person' => $this->getCurrentPerson()
            ])
            ->getQuery()
            ->getResult();

        return array_map('current', $nm1);
    }

    /**
     * Retourne la liste des IDS des personnes qui ont autorisé la délégation du remplissage des feuilles de temps.
     * @param $idPerson
     */
    public function getTimesheetDelegationIds( $idPerson ){
        /** @var Person $person */
        $person = $this->getPersonRepository()->find($idPerson);
        $ids = [];
        foreach ($person->getTimesheetsFor() as $p) {
            $ids[] = $p->getId();
        }
        return $ids;
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
     * @return PersonRepository
     */
    protected function getPersonRepository(){
        return $this->getEntityManager()->getRepository(Person::class);
    }

    public function searchPersonnel(
        $search = null,
        $currentPage = 1,
        $filters = [],
        $resultByPage = 50
    ) {
        $query = $this->getBaseQuery();

        if( $search ){
            /** @var ProjectGrantService $activityService */
            $activityService = $this->getServiceLocator()->get("ActivityService");

            $ids = $activityService->search($search);

            $idsPersons = $this->getPersonRepository()->getPersonsIdsForActivitiesids($ids);


            $query->leftJoin('p.organizations', 'o')
                ->leftJoin('p.activities', 'a')
                ->where('p.id IN(:ids)')
                ->setParameter('ids', $idsPersons);
        }

        if( array_key_exists('ids', $filters) ){
            $query->andWhere('p.id IN(:filterIds)')
                ->setParameter('filterIds', $filters['ids']);
        }

        return new UnicaenDoctrinePaginator($query, $currentPage,
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

        $query->leftJoin('p.organizations', 'o')
            ->leftJoin('p.activities', 'a');


        if( $filters['leader'] ){
            $query = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
                ->innerJoin('p.organizations', 'o')
                ->innerJoin('o.roleObj', 'r')
                ->innerJoin('p.activities', 'a')
                ->where('r.principal = true')
            ;
        }

        if( array_key_exists('order_by', $filters) ){
            $query->addOrderBy('p.'.$filters['order_by'], 'ASC');
        }

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

                $searchR = str_replace('*', '%', $search);

                $query->where('lower(p.firstname) LIKE :search OR lower(p.lastname) LIKE :search OR lower(p.email) LIKE :search OR LOWER(CONCAT(CONCAT(p.firstname, \' \'), p.lastname)) LIKE :search OR LOWER(CONCAT(CONCAT(p.lastname, \' \'), p.firstname)) LIKE :search')
                    ->setParameter('search', '%' . strtolower($searchR) . '%');
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

        if( array_key_exists('ids', $filters) ){
            $query->andWhere('p.id IN(:filterIds)')
                ->setParameter('filterIds', $filters['ids']);
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


    /**
     * Retourne la liste des rôles disponibles niveau activité.
     *
     * @return Role[]
     */
    public function getAvailableRolesPersonActivity(){
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getEntityManager()->getRepository(Role::class);
        $roles = $roleRepository->getRolesAtActivityArray();
        return $roles;
    }

    /**
     * @param $id
     * @param bool $throw
     * @return null|Person
     * @throws OscarException
     */
    public function getPersonById($id, $throw = false){
        $person = $this->getEntityManager()->getRepository(Person::class)->find($id);
        if( $throw === true && $person == null ){
            throw new OscarException(sprintf(_("La personne avec l'identifiant %s n'est pas présente dans la base de données."), $id));
        }
        return $person;
    }

    /**
     * @param $id
     * @param bool $throw
     * @return null|Role
     * @throws OscarException
     */
    public function getRolePersonById($id, $throw = false){
        $role = $this->getEntityManager()->getRepository(Role::class)->find($id);
        if( $throw === true && $role == null ){
            throw new OscarException(sprintf(_("Le rôle avec l'identifiant %s n'est pas présente dans la base de données."), $id));
        }
        return $role;
    }


    public function getPersonsPrincipalInActivityIncludeOrganization( Activity $activity ){
        $persons = [];

        /** @var ActivityPerson $activityperson */
        foreach( $activity->getPersonsDeep() as $activityperson ){
            if( $activityperson->isPrincipal() && !$activityperson->isOutOfDate() ){
                if( !in_array($activityperson->getPerson(), $persons))
                    $persons[] = $activityperson->getPerson();
            }
        }

        /** @var ActivityOrganization $activityOrganization */
        foreach( $activity->getOrganizationsDeep() as $activityOrganization ){
            if( $activityOrganization->isPrincipal() && !$activityOrganization->isOutOfDate() ){

                /** @var OrganizationPerson $organizationPerson */
                foreach( $activityOrganization->getOrganization()->getPersons() as $organizationPerson ){
                    if( $organizationPerson->isPrincipal() && !$organizationPerson->isOutOfDate() && !in_array($organizationPerson->getPerson(), $persons) ){
                        $persons[] = $organizationPerson->getPerson();
                    }
                }
            }
        }

        return $persons;
    }



    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            // ->leftJoin('p.timesheetsBy', 'tb')
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

    /**
     * @return NotificationService
     */
    protected function getNotificationService(){
        return $this->getServiceLocator()->get('NotificationService');
    }

    /**
     * @return Logger
     */
    protected function getLoggerService(){
        return $this->getServiceLocator()->get('Logger');
    }


    /**
     * @return ActivityLogService
     */
    protected function getActivityLogService()
    {
        return $this->getServiceLocator()->get('ActivityLogService');
    }

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
    }

    /**
     * @return Person
     */
    protected function getCurrentPerson()
    {
        return $this->getOscarUserContext()->getCurrentPerson();
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AFFECTATIONS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Organization
    public function personOrganizationAdd( Organization $organization, Person $person, Role $role, $dateStart=null, $dateEnd=null ){
        if( !$organization->hasPerson($person, $role) ){
            $message = sprintf("a ajouté %s(%s) dans l'organisation %s", $person->log(), $role->getRoleId(), $organization->log());
            $this->getLoggerService()->info($message);
            $op = new OrganizationPerson();
            $this->getEntityManager()->persist($op);

            $op->setPerson($person)
                ->setOrganization($organization)
                ->setRoleObj($role)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);

            $this->getEntityManager()->flush($op);
            $this->getEntityManager()->refresh($organization);
            $this->getEntityManager()->refresh($person);

            if( $role->isPrincipal() ){
                $this->getLoggerService()->info("Role principal");
                /** @var ActivityOrganization $oa */
                foreach ($organization->getActivities() as $oa){
                    $this->getLoggerService()->info("Activité : " . $oa->getActivity());
                    if( $oa->isPrincipal() ){
                        $this->getLoggerService()->info("Activités, rôle principal");
                        $this->getEntityManager()->refresh($oa->getActivity());
                        $this->getNotificationService()->generateNotificationsForActivity($oa->getActivity(), $person);
                    }
                }
                foreach ($organization->getProjects() as $op){
                    $this->getLoggerService()->info("Projet : " . $op->getProject);
                    if( $op->isPrincipal() ){
                        foreach ($op->getProject()->getActivities() as $a){
                            $this->getLoggerService()->info("Project > Activités, rôle principal");
                            $this->getEntityManager()->refresh($a);
                            $this->getNotificationService()->generateNotificationsForActivity($a, $person);
                        }
                    }
                }
            }
        }
    }

    public function personOrganizationRemove( OrganizationPerson $organizationPerson ){
        if( $organizationPerson->isPrincipal() ){
            /** @var OrganizationService $os */
            $os = $this->getServiceLocator()->get('OrganizationService');

            foreach ( $os->getOrganizationActivititiesPrincipalActive($organizationPerson->getOrganization()) as $activity ){
                $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $organizationPerson->getPerson());
            }
        }
        $this->getEntityManager()->remove($organizationPerson);
        $this->getEntityManager()->flush();
    }


    /**
     * Retourne la liste des organisations de la personne.
     *
     * @param Person $person
     * @param bool $date Si $date est non-false, on test la date donnée
     * @param bool $pincipal TRUE : Tiens compte uniquement des rôles 'principaux'
     * @return array
     */
    public function getPersonOrganizations( Person $person, $date = false, $pincipal = false ){

        $qb = $this->getEntityManager()->getRepository(Organization::class)
            ->createQueryBuilder('o')
            ->innerJoin('o.persons', 'op')
            ->where('op.person = :person')
            ->setParameter('person', $person);

        if( $date !== false ){
            $date = $date === true ? new \DateTime() : $date;
            $qb->andWhere('op.dateStart IS NULL OR op.dateStart <= :date');
            $qb->andWhere('op.dateEnd >= :date OR op.dateEnd IS NULL');
            $qb->setParameter('date', $date);
        }

        if( $pincipal === true ){
            $qb->innerJoin('op.roleObj', 'r')
                ->andWhere('r.principal = true');
        }

        return $qb->getQuery()->getResult();
    }



    /// ACTIVITY
    public function personActivityAdd( Activity $activity, Person $person, Role $role, $dateStart=null, $dateEnd=null ){
        if( !$activity->hasPerson($person, $role, $dateStart, $dateEnd) ){


            $personActivity = new ActivityPerson();
            $this->getEntityManager()->persist($personActivity);

            $personActivity->setPerson($person)
                ->setActivity($activity)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush($personActivity);

            // LOG
            $this->getActivityLogService()->addUserInfo(
                sprintf("a ajouté %s(%s) dans l'activité %s ", $person->log(), $role->getRoleId(), $activity->log()),
                'Activity:person', $activity->getId()
            );

            // Si le rôle est principal, on actualise les notifications de la personne
            if( $role->isPrincipal() ){
                $this->getEntityManager()->refresh($activity);
                $this->getNotificationService()->generateNotificationsForActivity($activity, $person);
            }
        }
    }

    public function personActivityRemove( ActivityPerson $activityPerson )
    {
        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();
        $roleId = $activityPerson->getRole();
        $updateNotification = $activityPerson->isPrincipal();
        $this->getEntityManager()->remove($activityPerson);

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $activity->log()),
            'Activity:person', $activity->getId()
        );

        // Si le rôle est principal, on actualise les notifications de la personne
        if( $updateNotification ){
            $this->getEntityManager()->refresh($activity);
            $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $person);
            $this->getNotificationService()->generateNotificationsForActivity($activity, $person);
        }
    }

    public function personActivityChangeRole( ActivityPerson $activityPerson, Role $newRole )
    {
        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();

        // TODO Faire un contrôle sur les dates


        $updateNotification = $activityPerson->isPrincipal() || $newRole->isPrincipal();
        $activityPerson->setRoleObj($newRole);
        $this->getEntityManager()->flush($activityPerson);
        $this->getLoggerService()->info(sprintf("Le rôle de personne %s a été modifié dans l'activité %s", $person, $activity));

        // Si le rôle est principal, on actualise les notifications de la personne
        if( $updateNotification ){
            $this->getEntityManager()->refresh($activity);
            $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $person);
            $this->getNotificationService()->generateNotificationsForActivity($activity, $person);
        }
    }

    // PROJECT
    public function personProjectAdd( Project $project, Person $person, Role $role, $dateStart=null, $dateEnd=null ){
        if( !$project->hasPerson($person, $role, $dateStart, $dateEnd) ){

            $personProject = new ProjectMember();
            $this->getEntityManager()->persist($personProject);

            $personProject->setPerson($person)
                ->setProject($project)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush($personProject);
            $this->getLoggerService()->info(sprintf("La personne %s a été ajouté au projet %s", $person, $project));

            // Si le rôle est principal, on actualise les notifications de la personne
            if( $role->isPrincipal() ){
                $this->getEntityManager()->refresh($project);
                foreach ($project->getActivities() as $activity) {
                    $this->getEntityManager()->refresh($activity);
                    $this->getNotificationService()->generateNotificationsForActivity($activity, $person);
                }
            }
        }
    }

    public function personProjectRemove( ProjectMember $projectPerson ){
        $person = $projectPerson->getPerson();
        $project = $projectPerson->getProject();

        $roleId = $projectPerson->getRole();
        $updateNotification = $projectPerson->isPrincipal();

        $this->getEntityManager()->remove($projectPerson);

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $project->log()),
            'Project:person', $project->getId()
        );

        // Si le rôle est principal, on actualise les notifications de la personne
        if( $updateNotification ){
            $this->getEntityManager()->refresh($project);
            $this->getNotificationService()->purgeNotificationsPersonProject($project, $person);
            $this->getNotificationService()->generateNotificationsForProject($project, $person);
        }
    }

    // PROJECT
    public function personProjectChangeRole( ProjectMember $personProject, Role $newRole, $dateStart=null, $dateEnd=null ){

        if( $newRole == $personProject->getRoleObj() ) return;

        $person = $personProject->getPerson();
        $project = $personProject->getProject();

        $updateNotification = $personProject->isPrincipal() || $newRole->isPrincipal();
        $personProject->setRoleObj($newRole);
        $project->touch();

        $this->getEntityManager()->flush($personProject);
        $this->getEntityManager()->flush($project);

        $this->getLoggerService()->info(sprintf("Le rôle de personne %s a été modifié dans le projet %s", $person, $project));

        // Si le rôle est principal, on actualise les notifications de la personne
        if( $updateNotification ){
            $this->getEntityManager()->refresh($project);
            $this->getNotificationService()->purgeNotificationsPersonProject($project, $person);
            $this->getNotificationService()->generateNotificationsForProject($project, $person);
        }
    }
}
