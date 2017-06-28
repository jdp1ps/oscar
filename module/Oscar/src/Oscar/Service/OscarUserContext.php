<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/02/16 18:00
 * @copyright Certic (c) 2016
 */

namespace Oscar\Service;


use BjyAuthorize\Acl\HierarchicalRoleInterface;
use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Privilege;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Provider\Privileges;
use UnicaenAuth\Acl\NamedRole;
use UnicaenAuth\Service\UserContext;
use Zend\Http\Request;
use Zend\Json\Server\Exception\HttpException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Cette classe centralise les informations liées à l'authentification et à l'identité
 * de la personne connectée. Elle permet notamment d'obtenir la personne (Person) lié
 * au l'authentifiaction courante en passant par l'identifiant LDAP ou l'email.
 *
 * @package Oscar\Service
 */
class OscarUserContext extends UserContext
{

    /**
     * Retourne la liste des rôles disponibles.
     *
     * @return array
     * @deprecated Utilisé pendant les test sur les droits
     */
    public function getAvailabledRoles()
    {
        return $this->getServiceAuthorize()->getRoles();
    }

    public function getRequestToken(){
        if( array_key_exists('HTTP_X_CSRF_TOKEN', $_SERVER) ){
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        } else {
            return "";
        }
    }

    public function checkToken(){
        if( $this->getTokenValue() != $this->getRequestToken() ){
            throw new HttpException("Jeton de sécurité expiré, ");
        }
    }

    public function getTokenValue( $reload = false ){

        if( !$this->getSessionContainer()->offsetGet('OSCAR-TOKEN' ) || $reload === true ) {
            $this->getSessionContainer()->offsetSet('OSCAR-TOKEN', md5(date('Y-m-d H:i:s')));
        }
        $token = $this->getSessionContainer()->offsetGet('OSCAR-TOKEN');
        return $token;
    }

    public function getTokenName(){
        return 'TOKENNAME';
    }

    public function getAllRoleIdPerson()
    {
        static $_ROLES_IDS;
        if ($_ROLES_IDS === null) {
            $_ROLES_IDS = [];
            /** @var Role $role */
            foreach ($this->getEntityManager()->getRepository(Role::class)->findAll() as $role) {
                $_ROLES_IDS[$role->getId()] = $role->getRoleId();
            }
        }

        return $_ROLES_IDS;
    }

    public function isDeclarer(){
        if( $this->getCurrentPerson() ){
            return $this->getCurrentPerson()->getWorkPackages()->count() > 0;
        }
        return false;
    }

    public function getAllRoleIdPersonInActivity()
    {
        static $_ROLES_IDS_ACTIVITY;
        if ($_ROLES_IDS_ACTIVITY === null) {
            $_ROLES_IDS_ACTIVITY = [];
            /** @var Role $role */
            foreach ($this->getEntityManager()->getRepository(Role::class)->findAll() as $role) {
                if (($role->getSpot() & Role::LEVEL_ACTIVITY) > 0) {
                    $_ROLES_IDS_ACTIVITY[$role->getId()] = $role->getRoleId();
                }
            }
        }

        return $_ROLES_IDS_ACTIVITY;
    }

    /**
     * Retourne la liste des rôles.
     *
     * @return null
     */
    public function getOscarRoles()
    {
        static $_ROLES;
        if ($_ROLES === null) {
            $_ROLES = [];
            /** @var Role $role */
            foreach ($this->getEntityManager()->getRepository(Role::class)->findAll() as $role) {
                $_ROLES[$role->getRoleId()] = $role;
            }
        }

        return $_ROLES;
    }


    /**
     * Retourne la liste des roles "principaux".
     *
     * @return array
     */
    public function getRoleIdPrimary()
    {
        static $_ROLES_PRIMARY;
        if ($_ROLES_PRIMARY === null) {
            $_ROLES_PRIMARY = [];
            /** @var Role $role */
            foreach ($this->getOscarRoles() as $role) {
                if ($role->isPrincipal()) {
                    $_ROLES_PRIMARY[] = $role->getRoleId();
                }
            }
        }

        return $_ROLES_PRIMARY;
    }

    /**
     * @return array
     */
    public function getRolesOrganization()
    {
        static $_ROLES_ORGANISATION;
        if ($_ROLES_ORGANISATION === null) {
            $_ROLES_ORGANISATION = [];

            /** @var Role $role */
            foreach ($this->getOscarRoles() as $role) {
                if ($role->isPrincipal()) {
                    $_ROLES_ORGANISATION[] = $role->getRoleId();
                }
            }
        }

        return $_ROLES_ORGANISATION;
    }

    /**
     * @return OrganizationRole[]
     */
    public function getRolesOrganizationInActivity()
    {
        static $_ROLE_ORGANIZATION_IN_ACTIVITY;
        if ($_ROLE_ORGANIZATION_IN_ACTIVITY === null) {
            $_ROLE_ORGANIZATION_IN_ACTIVITY = [];

            /** @var OrganizationRole $role */
            foreach ($this->getEntityManager()->getRepository(OrganizationRole::class)->findAll() as $role) {
                $_ROLE_ORGANIZATION_IN_ACTIVITY[$role->getId()] = $role->getLabel();
            }
        }

        return $_ROLE_ORGANIZATION_IN_ACTIVITY;
    }

    /**
     * @param null $person
     * @return array
     * @deprecated
     */
    public function getOrganisationsPerson($person = null)
    {
        if ($person == null) {
            $person = $this->getCurrentPerson();
        }

        echo "Récupération des organisation de $person";

        /** @var OrganizationPerson $affectation */
        foreach ($person->getOrganizations() as $affectation) {
            echo "<li>" . $affectation->getOrganization() . ' ' . $affectation->getRole() . ' (' . $affectation->getRoleObj()->isPrincipal() . ')</li>';
        }

        if (!$person) {
            return [];
        }
    }

    /**
     * Retourne la liste des organisation où $person a un rôle principal.
     *
     * @param null $person
     */
    public function getOrganisationsPersonPrincipal($person = null, $id = false)
    {
        $organizations = [];
        $person = $person == null ? $this->getCurrentPerson() : $person;
        /** @var OrganizationPerson $affectation */
        foreach ($person->getOrganizations() as $affectation) {
            if( $affectation->isPrincipal() )
                $organizations[] = $id === true ? $affectation->getOrganization()->getId() : $affectation->getOrganization();
        }

        return $organizations;
    }

    /**
     * Retourn TRUE si la personne a un rôle principal dans une organisation.
     *
     * @param null $person
     */
    public function hasRolePrincipalInAnyOrganisations($person = null)
    {
        if ($person == null) {
            $person = $this->getCurrentPerson();
        }

        $rolesLead = $this->getRoleIdPrimary();
        /** @var OrganizationPerson $affectation */
        foreach ($person->getOrganizations() as $affectation) {
            if (in_array($affectation->getRole(), $rolesLead)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne les organisations de la personne dans lesquels
     *
     * @param null $person
     */
//    public function getOrganisationsPersonWithPrincipalRole($person = null)
//    {
//        if ($person == null) {
//            $person = $this->getCurrentPerson();
//        }
//
//        $rolesLead = $this->getRolesOrganisationLeader();
//        $organisations = [];
//
//        /** @var OrganizationPerson $affectation */
//        foreach ($person->getOrganizations() as $affectation) {
//            if (in_array($affectation->getRole(), $rolesLead)) {
//                $organisations[] = $affectation->getOrganization();
//            }
//        }
//
//        return $organisations;
//    }


    /**
     * Retourne la personne en fonction de l'authentification active.
     *
     * @return null|\Oscar\Entity\Person
     */
    public function getCurrentPerson()
    {
        /** @var PersonService $personService */
        $personService = $this->getServiceLocator()->get('PersonService');

        try {
            if ($this->getLdapUser()) {
                return $personService->getPersonByLdapLogin($this->getLdapUser()->getSupannAliasLogin());
            } elseif ($this->getDbUser()) {
                return $personService->getPersonByLdapLogin($this->getDbUser()->getUsername());
            }
        } catch (NoResultException $ex) {
            // ... can happening with users stored in database directly
        }

        return null;
    }

    /**
     * @param Authentification $authentification
     * @return null|Person
     */
    public function getPersonFromAuthentification(
        Authentification $authentification
    ) {
        /** @var PersonService $personService */
        $personService = $this->getServiceLocator()->get('PersonService');

        try {
            return $personService->getPersonByLdapLogin($authentification->getUsername());
        } catch (NoResultException $ex) {
            // ... can happening with users stored in database directly
        }

        return null;
    }

    /**
     * @param $privilege
     * @param $entity
     */
    public function check($privilege, $entity = null)
    {
        if (!$this->hasPrivileges($privilege, $entity)) {
            throw new UnAuthorizedException('Droits insuffisants');
        }
    }





    /**
     * Test si l'utilisteur courant a le role $roleId en tenant compte de la
     * hiérarchie des rôles.
     *
     * @param $roleId
     * @return bool
     */
    public function hasRole($roleId)
    {

        $recursiveCheck = function (
            HierarchicalRoleInterface $role,
            $roleId
        ) use (&$recursiveCheck) {
            if ($role->getRoleId() === $roleId) {
                return true;
            } elseif ($role->getParent()) {
                return $recursiveCheck($role->getParent(), $roleId);
            }
        };

        /** @var HierarchicalRoleInterface $role */
        foreach ($this->getIdentityRoles() as $role) {
            if ($recursiveCheck($role, $roleId)) {
                return true;
            }
        }
    }


    private function getRoleIdRecursive(HierarchicalRoleInterface $role)
    {
        $roles = [];
        $roles[] = $role->getRoleId();
        if ($role->getParent()) {
            $roles = array_merge($roles,
                $this->getRoleIdRecursive($role->getParent()));
        }

        return $roles;
    }


    /**
     * Charge la liste des privilèges de l'utilisateur courant niveau
     * application (hérité des rôles par défaut).
     */
    public function getBasePrivileges()
    {
        static $_PRIVILEGES;
        if ($_PRIVILEGES === null) {
            $_PRIVILEGES = [];

            $privileges = $this->getEntityManager()->createQueryBuilder()
                ->select('p')
                ->from(Privilege::class, 'p')
                ->innerJoin('p.role', 'r')
                ->where('r.roleId IN (:roles)')
                ->setParameter('roles', $this->getBaseRoleId())
                ->getQuery()
                ->getResult();

            /** @var Privilege $privilege */
            foreach ($privileges as $privilege) {
                $_PRIVILEGES[] = $privilege->getFullCode();
            }

        }

        return $_PRIVILEGES;
    }

    /**
     * Liste des roleId de l'utilisateur courant.
     *
     * @return string[]
     */
    public function getBaseRoleId()
    {
        static $_ROLESID;
        if ($_ROLESID === null) {
            $_ROLESID = [];

            if ($this->getIdentity()) {
                /** @var HierarchicalRoleInterface $role */
                foreach ($this->getIdentityRoles() as $role) {
                    $_ROLESID = array_merge($_ROLESID,
                        $this->getRoleIdRecursive($role));
                }
                $_ROLESID = array_unique($_ROLESID);
            }
        }

        return $_ROLESID;
    }

    /**
     * Retourne la liste des priviléges accordés aux roles passé en paramètre.
     *
     * @param $testedRole string || string[]
     * @return string[]
     * @throws \Exception
     */
    public function getPrivilegesRoles($testedRole)
    {
        static $privileges_roles;

        ////////////////////////////////////////////////////////////////////////
        // CONSTRUCTION du CACHE des PRIVILEGES en FONCTION des ROLES
        ////////////////////////////////////////////////////////////////////////
        if ($privileges_roles == null) {
            $privileges_roles = [];
            $privileges = $this->getEntityManager()->createQueryBuilder()
                ->select('p')
                ->from(Privilege::class, 'p')
                ->getQuery()
                ->getResult();

            $roles = $this->getEntityManager()->createQueryBuilder()
                ->select('r')
                ->from(Role::class, 'r')
                ->orderBy('r.parent', 'DESC')
                ->getQuery()
                ->getResult();

            $recursive = function (
                Role $role,
                Privilege $privilege,
                &$privileges_roles
            ) use (&$recursive) {
                if (!isset($privileges_roles[$role->getRoleId()])) {
                    $privileges_roles[$role->getRoleId()] = [];
                }
                $privileges_roles[$role->getRoleId()][] = $privilege->getFullCode();
            };


            /** @var Role $role */
            foreach ($roles as $role) {
                /** @var Privilege $privilege */
                foreach ($privileges as $privilege) {
                    if ($privilege->getRole()->contains($role)) {
                        $recursive($role, $privilege, $privileges_roles);
                    }
                }
            }

            foreach ($roles as $role) {
                $roleId = $role->getRoleId();
                if (!isset($privileges_roles[$roleId])) {
                    $privileges_roles[$roleId] = [];
                }
                if ($role->getParent() && isset($privileges_roles[$role->getParent()->getRoleId()])) {
                    $privileges_roles[$roleId] = array_merge($privileges_roles[$roleId],
                        $privileges_roles[$role->getParent()->getRoleId()]);

                }
                $privileges_roles[$roleId] = array_unique($privileges_roles[$roleId]);
            }
        }

        if (is_string($testedRole)) {
            $testedRole = [$testedRole];
        } elseif (!is_array($testedRole)) {
            throw new \Exception('Cette méthode accepte un tableau ou une chaîne.');
        }

        $privileges = [];

        foreach ($testedRole as $r) {

            if (!isset($privileges_roles[$r])) {
                continue;
            }
            $privileges = array_merge($privileges, $privileges_roles[$r]);
        }

        return $privileges;
    }

    public function getPrivileges($entity)
    {
        if ($entity instanceof Project) {
            return $this->getPrivilegesProjet($entity);
        } elseif ($entity instanceof Activity) {
            return $this->getPrivilegesActivity($entity);
        } else {
            throw new \Exception('La ressource fournie doit être un projet ou une activité');
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * Retourne la liste des rôles des organization donnant lieu à des accès.
     * /!\ A METTRE EN BDD
     */
    public function getRolesOrganisationLeader()
    {
        static $_ROLES_ORGANIZATION_LEADER;
        if ($_ROLES_ORGANIZATION_LEADER === null) {
            $_ROLES_ORGANIZATION_LEADER = [];

            foreach ($this->getEntityManager()->getRepository(OrganizationRole::class)->findBy(['principal' => true]) as $role) {
                if ($role->isPrincipal()) {
                    $_ROLES_ORGANIZATION_LEADER[] = $role->getLabel();
                }
            }
        }

        return $_ROLES_ORGANIZATION_LEADER;
    }

    public function getRolesPersonInActivity(Person $person, Activity $activity)
    {
        static $tmpRolesActivities;
        $key = sprintf('%s-%s', $person->getId(), $activity->getId());
        if (!isset($tmpRoles[$key])) {
            $tmpRolesActivities[$key] = [];
            $roles = $activity->getPersonRoles($person);
            /** @var ActivityPerson $activityOrganization */
            foreach ($activity->getOrganizations() as $activityOrganization) {
                if (in_array($activityOrganization->getRole(),
                    $this->getRolesOrganisationLeader())) {
                    $roles = array_merge($roles,
                        $this->getRolesPersonInOrganization($person,
                            $activityOrganization->getOrganization()));
                }
            }
            $tmpRolesActivities[$key] = array_unique($roles);
        }

        return $tmpRolesActivities[$key];
    }

    /**
     * Retourne la liste des rôles de la personne dans le projet donné.
     *
     * @param Person $person
     * @param Project $project
     * @return mixed
     */
    public function getRolesPersonInProject(Person $person, Project $project)
    {
        static $tmpRoles;
        $key = sprintf('%s-%s', $person->getId(), $project->getId());
        if (!isset($tmpRoles[$key])) {
            $tmpRoles[$key] = [];
            $roles = $project->getPersonRoles($person);
            /** @var ProjectPartner $projectOrganization */
            foreach ($project->getOrganizations() as $projectOrganization) {
                if (in_array($projectOrganization->getRole(),
                    $this->getRolesOrganisationLeader())) {
                    $roles = array_merge($roles,
                        $this->getRolesPersonInOrganization($person,
                            $projectOrganization->getOrganization()));
                }
            }
            $tmpRoles[$key] = array_unique($roles);
        }

        return $tmpRoles[$key];
    }

    /**
     * Retourne la liste des rôles dans l'oganisation.
     *
     * @param Person $person
     * @param Organization $organization
     * @return string[]
     */
    public function getRolesPersonInOrganization(
        Person $person,
        Organization $organization
    ) {
        static $tmpRolesOrganization = [];
        $key = sprintf('%s-%s', $person->getId(), $organization->getId());
        if (!isset($tmpRolesOrganization[$key])) {
            $tmpRolesOrganization[$key] = [];
            /** @var OrganizationPerson $organizationPerson */
            foreach ($person->getOrganizations() as $organizationPerson) {
                if ($organizationPerson->getOrganization()->getId() == $organization->getId()) {
                    $tmpRolesOrganization[$key][] = $organizationPerson->getRole();
                }
            }
            $tmpRolesOrganization[$key] = array_unique($tmpRolesOrganization[$key]);

        }

        return $tmpRolesOrganization[$key];
    }

    /**
     * Retourne les privilèges de la personne courante pour le Projet donnée.
     *
     * @param Project $entity
     * @return array
     * @throws \Exception
     */
    public function getPrivilegesProjet(Project $entity)
    {

        static $tmpProject = [];
        if (!$this->getCurrentPerson()) {
            return [];
        }

        /** @var Person $person */
        $person = $this->getCurrentPerson();

        $key = $this->getCurrentPerson()->getId() . '-' . $entity->getId();

        if (!isset($tmpProject[$key])) {
            // Récupérations des rôles de la personne dans le projet
            $roles = $entity->getPersonRoles($this->getCurrentPerson());


            if (!array_intersect($roles, $this->getRoleIdPrimary())) {
                // Role dans les organizations du projet
                $roles = array_merge($roles,
                    $this->getRolesPersonInProject($person, $entity));
            }

            $privileges = $this->getPrivilegesRoles($roles);
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
            if ($entity->activitiesHasPerson($this->getCurrentPerson())) {
                $privileges[] = Privileges::PROJECT_SHOW;
            }

            foreach ($entity->getActivities() as $activity) {
                if (count($this->getRolesPersonInActivity($this->getCurrentPerson(),
                    $activity))) {
                    $privileges[] = Privileges::PROJECT_SHOW;
                }

            }

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
            $tmpProject[$key] = $privileges;
        }


        return $tmpProject[$key];
    }

    /**
     * Retourne la liste des rôles actifs de la personne dans l'activité donnée.
     *
     * @param Person $person
     * @param Activity $activity
     * @return mixed
     */
    public function getRolesInActivity(Person $person, Activity $activity)
    {
        static $tmpActivities = [];
        $key = $person->getId() . '-' . $activity->getId();
        if (!isset($tmpActivities[$key])) {
            try {
                $roles = $activity->getPersonRoles($person);

                // --- Role dans les organizations du projet
                // le but est d'obtenir les personnes responsables dans une
                // organisation QUI A UN ROLE 'responsable' dans l'activité.
                if (!array_intersect($roles, $this->getRoleIdPrimary())) {
                    /** @var ProjectPartner $partner */
                    foreach ($activity->getOrganizationsDeep() as $partner) {

                        // L'organisation à un rôle a responsabilité dans l'activité
                        if (in_array($partner->getRole(),
                            $this->getRolesOrganisationLeader())) {
                            $roles = array_merge($roles,
                                $this->getRolesPersonInOrganization($person,
                                    $partner->getOrganization()));
                        }
                    }
                }
                $tmpActivities[$key] = $roles;
            } catch (\Exception $e) {
                $this->getServiceLocator()->get('logger')->error($e->getMessage());
            }
        }

        return $tmpActivities[$key];
    }

    public function getPrivilegesActivity(Activity $entity)
    {
        if (!$this->getCurrentPerson()) {
            return [];
        } else {
            $roles = [];
            if ($entity->getProject()) {
                $roles = $this->getRolesPersonInProject($this->getCurrentPerson(),
                    $entity->getProject());
            }
            $roles = array_merge($this->getRolesInActivity($this->getCurrentPerson(),
                $entity), $roles);

            return $this->getPrivilegesRoles($roles);
        }
    }

    /**
     * Retourne un booléen indiquant si l'utilisateur courant dispose du privilége
     * (de façon global ou sur la ressource spécifié).
     *
     * @param $privilege string
     * @param null $ressource Project|Activity
     * @return bool
     * @throws \Exception
     */
    public function hasPrivileges($privilege, $ressource = null)
    {
        try {
            // On commence par évaluer les privilèges issus du rôle niveau application
            if (in_array($privilege, $this->getBasePrivileges())) {
                return true;
            }

            // Puis si besoin, les rôles hérités de l'application
            if ($ressource) {
                return in_array($privilege, $this->getPrivileges($ressource));
            }
        } catch (\Exception $e) {

        }

        // Récupération des
        return false;
    }


}