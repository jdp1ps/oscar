<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/02/16 18:00
 * @copyright Certic (c) 2016
 */

namespace Oscar\Service;


use BjyAuthorize\Acl\HierarchicalRoleInterface;
use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\AuthentificationRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\LogActivity;
use Oscar\Entity\LogActivityRepository;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\PrivilegeRepository;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Entity\TabDocument;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use UnicaenAuthentification\Service\UserContext;
use ZfcUser\Entity\UserInterface;

/**
 * Cette classe centralise les informations liées à l'authentification et à l'identité
 * de la personne connectée. Elle permet notamment d'obtenir la personne (Person) lié
 * au l'authentifiaction courante en passant par l'identifiant LDAP ou l'email.
 *
 * @package Oscar\Service
 */
class OscarUserContext implements UseOscarConfigurationService, UseLoggerService, UseEntityManager, UseServiceContainer
{

    use UseOscarConfigurationServiceTrait, UseLoggerServiceTrait, UseEntityManagerTrait, UseServiceContainerTrait;


    // Méthode d'authentification
    const AUTHENTIFICATION_METHOD_DB = 'BDD';
    const AUTHENTIFICATION_METHOD_LDAP = 'LDAP';
    const AUTHENTIFICATION_METHOD_SHIB = 'SHIB';
    const AUTHENTIFICATION_METHOD_NONE = 'NONE';

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Accès SERVICES

    /** @var UserContext */
    protected UserContext $userContext;

    /**
     * @param UserContext $userContext
     */
    public function setUserContext(UserContext $userContext): void
    {
        $this->userContext = $userContext;
    }

    /**
     * @return UserContext
     */
    public function getUserContext(): UserContext
    {
        return $this->userContext;
    }

    /**
     * @return PersonService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getPersonService(): PersonService
    {
        return $this->getServiceContainer()->get(PersonService::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Accès REPOSITORY

    /**
     * @return AuthentificationRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getAuthentificationRepository(): AuthentificationRepository
    {
        return $this->getEntityManager()->getRepository(Authentification::class);
    }

    /**
     * @return RoleRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    protected function getRoleRepository(): RoleRepository
    {
        return $this->getEntityManager()->getRepository(Role::class);
    }

    /**
     * @return OrganizationRoleRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getOrganizationRoleRepository(): OrganizationRoleRepository
    {
        return $this->getEntityManager()->getRepository(OrganizationRole::class);
    }

    /**
     * @return PrivilegeRepository
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getPrivilegeRepository(): PrivilegeRepository
    {
        return $this->getEntityManager()->getRepository(Privilege::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return UserInterface|null
     */
    public function getDbUser(): ?UserInterface
    {
        return $this->getUserContext()->getDbUser();
    }

    public function getAuthentificationMethod()
    {
        if ($this->getUserContext()->getLdapUser()) {
            return self::AUTHENTIFICATION_METHOD_LDAP;
        }
        elseif ($this->getUserContext()->getDbUser()) {
            return self::AUTHENTIFICATION_METHOD_DB;
        }
        elseif ($this->getUserContext()->getShibUser()) {
            return self::AUTHENTIFICATION_METHOD_SHIB;
        }
        else {
            return self::AUTHENTIFICATION_METHOD_NONE;
        }
    }

    /**
     * @param int $id
     * @param bool $throw
     * @return object|null
     * @throws OscarException
     */
    public function getOrganizationRoleById(int $id, bool $throw = false): ?OrganizationRole
    {
        $roleObj = $this->getOrganizationRoleRepository()->find($id);
        if (!$roleObj && $throw) {
            throw new OscarException("Impossible de charger le rôle d'organisation '$id'");
        }
        return $roleObj;
    }

    /**
     * @param string $roleId
     * @param bool $throw
     * @return OrganizationRole|null
     * @throws OscarException
     */
    public function getOrganizationRoleByRoleId(string $roleId, bool $throw = false): ?OrganizationRole
    {
        $roleObj = $this->getOrganizationRoleRepository()->findOneBy(['label' => $roleId]);
        if (!$roleObj && $throw) {
            throw new OscarException("Impossible de charger le rôle d'organisation '$roleId'");
        }
        return $roleObj;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getAvailabledRolesPersonOrganization(): array
    {
        return $this->getRoleRepository()->getRolesAvailableForPersonInOrganizationArray();
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getAvailabledRolesPersonActivity($format = OscarFormatterConst::FORMAT_ARRAY_ID_VALUE): array
    {
        switch ($format) {
            case OscarFormatterConst::FORMAT_ARRAY_SIMPLE:
                $out = [];
                foreach ($this->getRoleRepository()->getRolesAvailableForPersonInActivity() as $role) {
                    $out[] = ['id' => $role->getId(), 'label' => $role->getRoleId()];
                }
                return $out;
            default:
                return $this->getRoleRepository()->getRolesAvailableForPersonInActivityArray();
        }
    }

    public function getRolesPersonsInActivityArray(): array
    {
        $rolesActivity = [];
        foreach ($this->getRoleRepository()->getRolesAvailableForPersonInActivity() as $role) {
            $rolesActivity[] = [
                'id'    => $role->getId(),
                'label' => $role->getRoleId()
            ];
        }
        return $rolesActivity;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getAvailabledRolesOrganizationActivity(): array
    {
        return $this->getOrganizationRoleRepository()->getRolesAvailableInActivityArray();
    }

    public function getAvailableRolesActivityOrOrganization(): array
    {
        return $this->getRoleRepository()->getRolesAvailableForPersonInActivityOrOrganizationArray();
    }

    /**
     * Retourne les IDS des rôles ayant le privilège donné.
     *
     * @param $privilegeCode
     * @param $roleLevel
     * @return int[]
     * @throws OscarException
     */
    public function getRolesIdsWithPrivileges($privilegeCode, $roleLevel = 0): array
    {
        $ids = [];
        foreach ($this->getRolesWithPrivileges($privilegeCode, $roleLevel) as $privilege) {
            $ids[] = $privilege->getId();
        }
        return $ids;
    }

    /**
     * Retourne les Role ayant le privilège donné.
     *
     * @param $privilegeCode
     * @param $roleLevel
     * @return Role[]
     * @throws OscarException
     */
    public function getRolesWithPrivileges($privilegeCode, $roleLevel = 0)
    {
        static $roles_privileges;

        if ($roles_privileges == null) {
            $roles_privileges = [];
        }

        if (!array_key_exists($privilegeCode, $roles_privileges)) {
            try {
                /** @var Privilege $privilege */
                $privilege = $this->getPrivilegeRepository()->getPrivilegeByCode($privilegeCode);

                /** @var Role $role */
                foreach ($privilege->getRole() as $role) {
                    if ($roleLevel == 0 || $role->isLevel($roleLevel)) {
                        $roles_privileges[] = $role;
                    }
                }
            } catch (\Exception $e) {
                $this->getLoggerService()->critical("Impossible de charger le privilège '$privilegeCode'");
                throw new OscarException(
                    sprintf('Impossible de charger le privilège %s : %s', $privilegeCode, $e->getMessage())
                );
            }
        }
        return $roles_privileges;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AUTHENTIFICATION
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getAuthentifications(array $options)
    {
        $query = $this->getAuthentificationRepository()
            ->createQueryBuilder('a')
            ->select('a.id', 'a.username', 'a.email', 'a.dateLogin', "a.displayName", "p.id as IDPERSON")
            ->leftJoin(Person::class, 'p', 'WITH', 'p.ladapLogin = a.username');

        $sort = "a." . $options['sort'];
        if ($sort == 'a.personId') {
            $sort = 'p.id';
        }

        if (array_key_exists('search', $options)) {
            $query->where('a.username LIKE :search OR a.email LIKE :search OR a.displayName LIKE :search')
                ->setParameter('search', '%' . $options['search'] . '%');
        }

        return $query
            ->addOrderBy($sort, $options['direction'])
            ->getQuery()->getResult();
    }

    public function getLogsAuthentification(Authentification $authentification, $options = [])
    {
        $parameters = [
            'authentification_id' => $authentification->getId()
        ];
        $stack = 20;
        $date = null;

        /** @var LogActivityRepository $logActivityRepository */
        $logActivityRepository = $this->getEntityManager()->getRepository(LogActivity::class);
        $query = $logActivityRepository->createQueryBuilder('l')
            ->where('l.userId = :authentification_id');

        if ($date == null) {
            $date = strtotime('-1 months');
            $since = date("Y-m-d 00:00:00", $date);
            $query->andWhere("l.dateCreated > :since");
            $parameters['since'] = $since;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $roleId
     * @return Role|null
     */
    public function getRoleByRoleId($roleId): ?Role
    {
        return $this->getRoleRepository()->findOneBy(['roleId' => $roleId]);
    }

    /**
     * @param string $login
     * @return Authentification|null
     */
    public function getAuthentificationByLogin(string $login, $throw = false): ?Authentification
    {
        $authentification = $this->getAuthentificationRepository()->findOneBy(['username' => $login]);
        if (!$authentification && $throw === true) {
            throw new OscarException("Aucune authentification trouvé");
        }
        return $authentification;
    }


    /**
     * @return bool
     * @throws OscarException
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function hasPersonnelAccess(): bool
    {
        if (!$this->getCurrentPerson()) {
            return false;
        }
        $access = $this->getOscarConfigurationService()->getConfiguration('listPersonnel');

        // OFF
        if ($access == 0) {
            return false;
        }

        // Accès global
        if ($this->hasPrivileges(Privileges::PERSON_INDEX)) {
            return true;
        }

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        /** @var OrganizationRepository $organisationRepository */
        $organisationRepository = $this->getEntityManager()->getRepository(Organization::class);

        // Accès niveau 1 : N+1
        $subodinates = $this->getPersonService()->getSubordinateIds($this->getCurrentPerson()->getId());

        if ($access > 0 && (count($subodinates) > 0 || count($this->getCurrentPerson()->getTimesheetsFor()) > 0)) {
            return true;
        }

        // Accès niveau 2 : Membre de l'organisation
        $idsOrga = $organisationRepository->getOrganizationsIdsForPerson($this->getCurrentPerson()->getId());
        $coworkers = $personRepository->getPersonIdsInOrganizations($idsOrga);
        if ($access > 1 && count($coworkers) > 0) {
            return true;
        }

        // Accès niveau 3 : ... et personnes impliquées dans les activités
        $cocoworkers = $personRepository->getPersonIdsForOrganizationsActivities($idsOrga);
        if ($access > 1 && count($cocoworkers) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return null|Authentification
     */
    public function getAuthentification(): ?Authentification
    {
        if ($this->getUserContext()->getDbUser()) {
            return $this->getAuthentificationRepository()->find($this->getDbUser()->getId());
        }
        return null;
    }

    public function getRequestToken()
    {
        if (array_key_exists('HTTP_X_CSRF_TOKEN', $_SERVER)) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        else {
            return "";
        }
    }

    public function checkToken()
    {
        if ($this->getTokenValue() != $this->getRequestToken()) {
            throw new HttpException("Jeton de sécurité expiré, ");
        }
    }

    public function getTokenValue($reload = false)
    {
        if (!$this->getSessionContainer()->offsetGet('OSCAR-TOKEN') || $reload === true) {
            $this->getSessionContainer()->offsetSet('OSCAR-TOKEN', md5(date('Y-m-d H:i:s')));
        }
        $token = $this->getSessionContainer()->offsetGet('OSCAR-TOKEN');
        return $token;
    }

    public function getTokenName()
    {
        return 'TOKENNAME';
    }

    public function getAllRoleIdPerson()
    {
        static $_ROLES_IDS;
        if ($_ROLES_IDS === null) {
            $_ROLES_IDS = [];

            // Récupération des rôles triés par LABEL
            $roles = $this->getEntityManager()
                ->getRepository(Role::class)
                ->findBy([], ['roleId' => 'ASC']);

            /** @var Role $role */
            foreach ($roles as $role) {
                $_ROLES_IDS[$role->getId()] = $role->getRoleId();
            }
        }

        return $_ROLES_IDS;
    }

    public function isDeclarer(): bool
    {
        if ($this->getCurrentPerson()) {
            return $this->getCurrentPerson()->getWorkPackages()->count() > 0;
        }
        return false;
    }

    public function getAllRoleIdPersonInActivity()
    {
        // todo factoriser
        static $_ROLES_IDS_ACTIVITY;
        if ($_ROLES_IDS_ACTIVITY === null) {
            $_ROLES_IDS_ACTIVITY = [];
            /** @var Role $role */
            foreach ($this->getEntityManager()->getRepository(Role::class)->findBy([], ['roleId' => 'ASC']) as $role) {
                if ($role->isLevelActivity()) {
                    $_ROLES_IDS_ACTIVITY[$role->getId()] = $role->getRoleId();
                }
            }
        }

        return $_ROLES_IDS_ACTIVITY;
    }

    public function getCurrentUserOrganisationPrincipal()
    {
        $organizations = [];
        /** @var OrganizationPerson $organizationPerson */
        foreach ($this->getCurrentPerson()->getOrganizations() as $organizationPerson) {
            if (!$organizationPerson->isOutOfDate() && $organizationPerson->getRoleObj()->isPrincipal()) {
                $organizations[$organizationPerson->getOrganization()->getId()] = $organizationPerson->getOrganization(
                );
            }
        }
        return $organizations;
    }

    public function getCurrentUserOrganisationWithPrivilege(string $privilege)
    {
        static $organizationsWithPrivilege;

        if ($organizationsWithPrivilege === null) {
            $organizationsWithPrivilege = [];
        }

        if (!array_key_exists($privilege, $organizationsWithPrivilege)) {
            $organizationsWithPrivilege[$privilege] = [];
            /** @var OrganizationPerson $organizationPerson */
            foreach ($this->getCurrentPerson()->getOrganizations() as $organizationPerson) {
                if (!$organizationPerson->isOutOfDate() && $organizationPerson->getRoleObj()->isPrincipal(
                    ) && $organizationPerson->getRoleObj()->hasPrivilege($privilege)) {
                    $organizationsWithPrivilege[$privilege][$organizationPerson->getOrganization()->getId(
                    )] = $organizationPerson->getOrganization();
                }
            }
        }

        return $organizationsWithPrivilege[$privilege];
    }

    /**
     * Retourne la liste des rôles.
     *
     * @return null
     */
    public function getOscarRoles(): array
    {
        static $_ROLES;

        if ($_ROLES === null) {
            $_ROLES = $this->getRoleRepository()->getRolesAvailableForPerson(RoleRepository::FORMAT_ROLEID_ARRAY_KEY);
        }

        return $_ROLES;
    }

    /**
     * Retourne la liste des rolesID
     *
     * @return array
     */
    public function getRoleId(): array
    {
        static $_ROLES_ALL;
        if ($_ROLES_ALL === null) {
            $_ROLES_ALL = array_keys($this->getOscarRoles());
        }
        return $_ROLES_ALL;
    }


    /**
     * Retourne la liste des roles "principaux".
     *
     * @return array
     */
    public function getRoleIdPrimary(): array
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
     * @throws NotSupported
     */
    public function getRolesOrganizationInActivityArray(): array
    {
        $roles = [];


        /** @var OrganizationRole $role */
        foreach (
            $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy([], ['label' => 'ASC']) as $role
        ) {
            $roles[] = [
                'id'    => $role->getId(),
                'label' => $role->getLabel()
            ];
        }
        return $roles;
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
            foreach (
                $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy([], ['label' => 'ASC']
                ) as $role
            ) {
                $_ROLE_ORGANIZATION_IN_ACTIVITY[$role->getId()] = $role->getLabel();
            }
        }

        return $_ROLE_ORGANIZATION_IN_ACTIVITY;
    }

    /**
     * Retourne la liste des organisations où $person a un rôle principal.
     *
     * @param $person
     * @param $id
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface|\Psr\Container\NotFoundExceptionInterface|OscarException
     */
    public function getOrganisationsPersonPrincipal($person = null, $id = false): array
    {
        $organizations = [];
        $person = $person == null ? $this->getCurrentPerson() : $person;

        /** @var OrganizationPerson $affectation */
        foreach ($person->getOrganizations() as $affectation) {
            if (!$affectation->isOutOfDate() && $affectation->isPrincipal()) {
                $organizations = array_merge(
                    $organizations,
                    $this->getPersonService()->getOrganizationService()->getDescentsIds(
                        $affectation->getOrganization()->getId()
                    )
                );
            }
        }

        return $organizations;
    }

    protected function getOrganizationsIdRecursive(Organization $organization, &$idsReturn = [], $id = false): array
    {
        if (!in_array($organization->getId(), $idsReturn)) {
            $idsReturn[] = $id === true ? $organization->getId() : $organization;
        }
        foreach ($organization->getChildren() as $o) {
            $this->getOrganizationsIdRecursive($o, $idsReturn, $id);
        }

        return $idsReturn;
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
            if (!$affectation->isOutOfDate() && in_array($affectation->getRole(), $rolesLead)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne la personne en fonction de l'authentification active.
     *
     * @return null|\Oscar\Entity\Person
     */
    public function getCurrentPerson()
    {
        try {
            $person = null;
            if ($this->getUserContext()->getLdapUser()) {
                // PATCH : Ensam (Matthieu MARC), 2020-08
                $attribute = $this->getOscarConfigurationService()->getServiceLocator()->get(
                    'Config'
                )['unicaen-auth']['ldap_username'];
                if (isset($attribute)) {
                    $pseudo = $this->getUserContext()->getLdapUser()->getData($attribute);
                }

                // PATCH Limoges
                // au cas ou le supannAliasLogin n'est pas fournis...
                if (!$pseudo) {
                    $pseudo = $this->getUserContext()->getLdapUser()->getUid();
                }

                $person = $this->getPersonService()->getPersonByLdapLogin($pseudo);
            }
            elseif ($this->getUserContext()->getDbUser()) {
                $person = $this->getPersonService()->getPersonByLdapLogin(
                    $this->getUserContext()->getDbUser()->getUsername()
                );
            }
            if ($person && !$person->isLdapActive()) {
                session_destroy();
                throw new OscarException(OscarException::ACCOUNT_DISABLED);
            }
            return $person;
        } catch (NoResultException $ex) {
            // $this->getLoggerService()->warning("getCurrentPerson() => " . $ex->getMessage());
            // ... can happening with users stored in database directly
        }
        return null;
    }

    /**
     * @param Authentification $authentification
     * @return null|Person
     */
    public function getPersonFromAuthentification(Authentification $authentification)
    {
        try {
            return $this->getPersonService()->getPersonByLdapLogin($authentification->getUsername());
        } catch (NoResultException $ex) {
            // ... can happening with users stored in database directly
        }
        return null;
    }

    /**
     * Vérifie les privilèges sur une entité privilègeVérifié/entitéCiblée
     *
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
     * Evalue le privilège au niveau application et organisation.
     *
     * @param $privilege
     * @return bool
     * @throws \Exception
     */
    public function checkWithorganizationDeep($privilege)
    {
        if (!$this->hasPrivileges($privilege)) {
            if (!$this->hasPrivilegeInOrganizations($privilege)) {
                throw new UnAuthorizedException('Droits insuffisants');
            }
        }
        return true;
    }

    /**
     * Test si l'utilisteur courant a le role $roleId en tenant compte de la
     * hiérarchie des rôles.
     *
     * @param $roleId
     * @return bool
     */
    public function hasRole($roleId): bool
    {
        $recursiveCheck = function (
            $role,
            $roleId
        ) use (&$recursiveCheck): bool {
            if ($role->getRoleId() === $roleId) {
                return true;
            }
            elseif ($role->getParent()) {
                return $recursiveCheck($role->getParent(), $roleId);
            }
            return false;
        };

        /** @var HierarchicalRoleInterface $role */
        foreach ($this->getUserContext()->getIdentityRoles() as $role) {
            if ($recursiveCheck($role, $roleId)) {
                return true;
            }
        }
        return false;
    }


    private function getRoleIdRecursive(HierarchicalRoleInterface $role): array
    {
        $roles = [];
        $roles[] = $role->getRoleId();
        if ($role->getParent()) {
            $roles = array_merge(
                $roles,
                $this->getRoleIdRecursive($role->getParent())
            );
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

            if ($this->getUserContext()->getIdentity()) {
                /** @var HierarchicalRoleInterface $role */
                foreach ($this->getUserContext()->getIdentityRoles() as $role) {
                    $_ROLESID = array_merge(
                        $_ROLESID,
                        $this->getRoleIdRecursive($role)
                    );
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
                    $privileges_roles[$roleId] = array_merge(
                        $privileges_roles[$roleId],
                        $privileges_roles[$role->getParent()->getRoleId()]
                    );
                }
                $privileges_roles[$roleId] = array_unique($privileges_roles[$roleId]);
            }
        }

        if (is_string($testedRole)) {
            $testedRole = [$testedRole];
        }
        elseif (!is_array($testedRole)) {
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
        }
        elseif ($entity instanceof Activity) {
            return $this->getPrivilegesActivity($entity);
        }
        elseif ($entity instanceof Organization) {
            return $this->getPrivilegesOrganization($entity);
        }
        else {
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

            foreach (
                $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy(
                    ['principal' => true]
                ) as $role
            ) {
                if ($role->isPrincipal()) {
                    $_ROLES_ORGANIZATION_LEADER[] = $role->getLabel();
                }
            }
        }

        return $_ROLES_ORGANIZATION_LEADER;
    }

    public function getRolesPersonInActivityDeep(?Person $person, Activity $activity)
    {
        if ($person) {
            $roles = $this->getRolesPersonInActivity($person, $activity);
        }
        else {
            $roles = [];
        }
        $rolesAppli = $this->getBaseRoleId();
        return array_merge($roles, $rolesAppli);
    }

    public function getRolesPersonInActivity(?Person $person, Activity $activity)
    {
        static $tmpRolesActivities;
        if (!$person) {
            return [];
        }
        $key = sprintf('%s-%s', $person->getId(), $activity->getId());
        if (!isset($tmpRoles[$key])) {
            $tmpRolesActivities[$key] = [];
            $roles = $activity->getPersonRoles($person);
            /** @var ActivityPerson $activityOrganization */
            foreach ($activity->getOrganizations() as $activityOrganization) {
                if (in_array(
                    $activityOrganization->getRole(),
                    $this->getRolesOrganisationLeader()
                )) {
                    $roles = array_merge(
                        $roles,
                        $this->getRolesPersonInOrganization(
                            $person,
                            $activityOrganization->getOrganization()
                        )
                    );
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
                if (in_array(
                    $projectOrganization->getRole(),
                    $this->getRolesOrganisationLeader()
                )) {
                    $roles = array_merge(
                        $roles,
                        $this->getRolesPersonInOrganization(
                            $person,
                            $projectOrganization->getOrganization()
                        )
                    );
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
            $this->writeRolesPersonInOrganizationDeep(
                $person,
                $this->getRootOrganization($organization),
                $tmpRolesOrganization,
                []
            );
        }
        return $tmpRolesOrganization[$key];
    }

    protected function getRootOrganization(Organization $organization): Organization
    {
        if ($organization->getParent()) {
            return $this->getRootOrganization($organization->getParent());
        }
        return $organization;
    }

    protected function writeRolesPersonInOrganizationDeep(
        Person $person,
        Organization $organization,
        array &$output,
        array $baseRoles
    ): void {
        $key = sprintf('%s-%s', $person->getId(), $organization->getId());
        if (!array_key_exists($key, $output)) {
            $output[$key] = $baseRoles;
        }

        /** @var OrganizationPerson $organizationPerson */
        foreach ($organization->getPersons(false) as $organizationPerson) {
            if ($organizationPerson->getPerson()->getId() == $person->getId()) {
                $output[$key][] = $organizationPerson->getRole();
            }
        }

        if ($organization->getChildren()) {
            foreach ($organization->getChildren() as $subOrganization) {
                $this->writeRolesPersonInOrganizationDeep($person, $subOrganization, $output, $output[$key]);
            }
        }

        $output[$key] = array_unique($output[$key]);
    }

    public function getPersonPrivilegesInOrganization(Person $person, Organization $organization)
    {
        $roles = $this->getRolesPersonInOrganization($person, $organization);
        return $this->getPrivilegesRoles($roles);
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
                $roles = array_merge(
                    $roles,
                    $this->getRolesPersonInProject($person, $entity)
                );
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
                if (count(
                    $this->getRolesPersonInActivity(
                        $this->getCurrentPerson(),
                        $activity
                    )
                )) {
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
    public function getRolesInActivity(?Person $person, Activity $activity)
    {
        static $tmpActivities = [];
        if (!$person) {
            return [];
        }
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
                        if (in_array(
                            $partner->getRole(),
                            $this->getRolesOrganisationLeader()
                        )) {
                            $roles = array_merge(
                                $roles,
                                $this->getRolesPersonInOrganization(
                                    $person,
                                    $partner->getOrganization()
                                )
                            );
                        }
                    }
                }
                $tmpActivities[$key] = $roles;
            } catch (\Exception $e) {
                $this->getLoggerService()->error(
                    "Un erreur est survenue lors du calcule des rôles pour '$person' dans '$activity' : " . $e->getMessage(
                    )
                );
            }
        }

        return $tmpActivities[$key];
    }

    public function getPrivilegesOrganization(Organization $organization): array
    {
        if (!$this->getCurrentPerson()) {
            return [];
        }
        else {
            $roles = $this->getRolesPersonInOrganization($this->getCurrentPerson(), $organization);
            return $this->getPrivilegesRoles($roles);;
        }
    }

    public function getPrivilegesActivity(Activity $entity): array
    {
        if (!$this->getCurrentPerson()) {
            return [];
        }
        else {
            $roles = [];
            if ($entity->getProject()) {
                $roles = $this->getRolesPersonInProject(
                    $this->getCurrentPerson(),
                    $entity->getProject()
                );
            }
            $roles = array_merge(
                $this->getRolesInActivity(
                    $this->getCurrentPerson(),
                    $entity
                ),
                $roles
            );

            return $this->getPrivilegesRoles($roles);
        }
    }


    /**
     * @param $roles Role[]
     * @param $privilege string
     */
    public function hasPrivilegeInRoles($roles, $privilege)
    {
        foreach ($roles as $role) {
            if ($role->hasPrivilege($privilege)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne TRUE si la personne dispose du privilège dans un des rôles obtenus via une activité/projet et/ou une
     * organisation.
     *
     * @param $privilege Code du privilège à tester
     * @return bool
     */
    public function hasPrivilegeInAnyRoles($privilege)
    {
        $rolesInActivities = $this->getPersonService()->getRolesPersonInActivities($this->getCurrentPerson());
        if ($this->hasPrivilegeInRoles($rolesInActivities, $privilege)) {
            return true;
        }

        $rolesInOrganisation = $this->getPersonService()->getRolesPersonInOrganizations($this->getCurrentPerson());
        if ($this->hasPrivilegeInRoles($rolesInOrganisation, $privilege)) {
            return true;
        }
        return false;
    }

    /**
     * Retourne TRUE si la personne dispose d'UN des privilèges dans un de ces rôles parmi
     * les activités/Projets/Organisations.
     *
     * @param $privileges
     * @return bool
     */
    public function hasOneOfPrivilegesInAnyRoles($privileges): bool
    {
        foreach ($privileges as $privilege) {
            if ($this->hasPrivilegeInAnyRoles($privilege)) {
                return true;
            }
        }
        return false;
    }

    /////////////////////////////////////////////////// ACCESS DOCUMENTS

    /**
     * Calcule l'accès à un onglet de document.
     * Un cache est créé pour la liste des rôles fournis en créant une clef globale indiquant
     * si la lecture est autorisée de façon générale. Puis une clef par onglet avant le détail
     * de l'accès (read / write).
     *
     * @param TabDocument|null $tabDocument
     * @param array|null $roles
     * @return false[]
     * @throws NotSupported
     * @throws OscarException
     */
    public function getAccessTabDocument(?TabDocument $tabDocument = null, ?array $roles = null): array
    {
        static $tmpAccessTabDocuments, $tmpAccessByRoles;

        if ($roles === null) {
            $roles = $this->getBaseRoleId();
        }
        if ($tmpAccessByRoles === null) {
            $tmpAccessByRoles = [];
        }

        if ($tmpAccessTabDocuments === null) {
            $tabDocuments = $this->getEntityManager()->getRepository(TabDocument::class)->findAll();
            if (!$tabDocuments) {
                $this->getLoggerService()->critical("Aucun onglet de document configuré");
                throw new OscarException("Aucun onglet de document configuré");
            }
            /** @var TabDocument $t */
            foreach ($this->getEntityManager()->getRepository(TabDocument::class)->findAll() as $t) {
                $tmpAccessTabDocuments[$t->getId()] = $t->getRolesAccess();
            }
        }

        $rolesSimplified = array_unique($roles);
        sort($rolesSimplified);
        $key = implode("---", $rolesSimplified);

        if (!array_key_exists($key, $tmpAccessByRoles)) {
            $accessRole = [
                'global' => ['read' => false]
            ];
            foreach ($tmpAccessTabDocuments as $id => $rolesAccess) {
                $read = false;
                $write = false;
                if (array_intersect($rolesAccess['read'], $roles)) {
                    $accessRole['global']['read'] = true;
                    $read = true;
                }
                if (array_intersect($rolesAccess['write'], $roles)) {
                    $write = true;
                }
                $accessRole[$id] = [
                    'read'  => $read,
                    'write' => $write,
                ];
            }
            $tmpAccessByRoles[$key] = $accessRole;
        }

        if ($tabDocument === null) {
            return $tmpAccessByRoles[$key]['global'];
        }
        else {
            if (array_key_exists($tabDocument->getId(), $tmpAccessByRoles[$key])) {
                return $tmpAccessByRoles[$key][$tabDocument->getId()];
            }
            return [
                'read'  => false,
                'write' => false
            ];
        }
    }

    /**
     * Retourne les droits d'accès à un document.
     *
     * @param ContractDocument $contractDocument
     * @return bool[]|false[]
     */
    public function getAccessDocument(ContractDocument $contractDocument): array
    {
        return $this->contractDocumentComputeAccess($contractDocument);
    }


    /**
     * @param ContractDocument $contractDocument
     * @return bool[]|false[]
     */
    protected function contractDocumentComputeAccess(ContractDocument $contractDocument): array
    {
        static $tmpContractDocuments;
        if ($tmpContractDocuments === null) {
            $tmpContractDocuments = [];
        }

        if (!array_key_exists($contractDocument->getId(), $tmpContractDocuments)) {
            // Document privé
            if ($contractDocument->isPrivate() && $this->getCurrentPerson() && $contractDocument->getPersons(
                )->contains(
                    $this->getCurrentPerson()
                )) {
                $read = true;
                $write = true;
            }
            elseif (!$contractDocument->hasTabDocument()) {
                // TODO On test le privilège global, à retirer
                $read = false; //$this->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_SHOW, $contractDocument->getActivity());
                $write = false; // $this->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE, $contractDocument->getActivity());
            }
            else {
                // On charge les différents rôles de la personnes dans l'activités
                $rolesInActivity = $this->getRolesInActivity(
                    $this->getCurrentPerson(),
                    $contractDocument->getActivity()
                );

                // Et ceux dans l'application
                $rolesInApp = $this->getBaseRoleId();
                $roles = array_merge($rolesInApp, $rolesInActivity);

                // Puis on calcule les accès
                $access = $this->getAccessTabDocument($contractDocument->getTabDocument(), $roles);
                $read = $access['read'] === true;
                $write = $access['write'] === true;

                // accès privé (exception)
                if ($contractDocument->isPrivate()) {
                    if ($contractDocument->getPersons()->contains($this->getCurrentPerson())) {
                        $read = true;
                    }
                    else {
                        $read = false;
                        $write = false;
                    }
                }
            }

            // On conserve le calcule de l'accès au document
            $tmpContractDocuments[$contractDocument->getId()] = ['read' => $read, 'write' => $write];
        }

        return $tmpContractDocuments[$contractDocument->getId()];
    }

    /**
     * Calcule l'accès aux documents d'une activité en se basant sur :
     *  - Les rôles de l'utilisateur dans l'activité
     *  - La configuration des onglets de document
     *
     * @param Activity $activity
     * @return false[]
     * @throws \Exception
     */
    public function getAccessActivityDocument(Activity $activity): array
    {
        static $tmpActivityDocumentAccess;

        if ($tmpActivityDocumentAccess === null) {
            $tmpActivityDocumentAccess = [];
        }

        if (!array_key_exists($activity->getId(), $tmpActivityDocumentAccess)) {
            $read = false;
            $write = false;
            if ($this->hasPrivileges(Privileges::ACTIVITY_SHOW, $activity)) {
                $roles = array_merge(
                    $this->getBaseRoleId(),
                    $this->getRolesInActivity($this->getCurrentPerson(), $activity)
                );
                $roles = $this->getCurrentRolesApplication();

                if ($this->getCurrentPerson()) {
                    $roles = array_merge(
                        $roles,
                        $this->getRolesInActivity($this->getCurrentPerson(), $activity)
                    );
                }
                $rules = $this->getAccessTabDocument(null, $roles);
                $read = $rules['read'];
                $write = $rules['write'];
            }
            $tmpActivityDocumentAccess[$activity->getId()] = [
                'read'  => $read,
                'write' => $write
            ];
        }

        return $tmpActivityDocumentAccess[$activity->getId()];
    }

    /**
     * Test l'accès en lecture au document.
     *
     * @param ContractDocument $contractDocument
     * @return bool
     */
    public function contractDocumentRead(ContractDocument $contractDocument): bool
    {
        return $this->contractDocumentComputeAccess($contractDocument)['read'];
    }

    /**
     * @param ContractDocument $contractDocument
     * @return bool
     */
    public function contractDocumentWrite(ContractDocument $contractDocument): bool
    {
        return $this->contractDocumentComputeAccess($contractDocument)['write'];
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
    public function hasPrivileges(string $privilege, $ressource = null): bool
    {
        try {
            // On commence par évaluer les privilèges issus du rôle niveau application
            if (in_array($privilege, $this->getBasePrivileges())) {
                return true;
            }

            // Puis si besoin, les rôles hérités de l'application
            if ($ressource) {
                // $this->getLoggerService()->info("hasPrivilege $privilege dans $ressource non global");
                $privileges = $this->getPrivileges($ressource);

                if ($privilege == Privileges::PROJECT_DOCUMENT_SHOW && $ressource instanceof Project) {
                    foreach ($ressource->getActivities() as $activity) {
                        if ($this->getAccessActivityDocument($activity)['read']) {
                            return true;
                        }
                    }
                }

                if ($privilege == Privileges::ACTIVITY_DOCUMENT_SHOW || $privilege == Privileges::ACTIVITY_DOCUMENT_MANAGE) {
                    if ($ressource instanceof Activity) {
                        $access = $this->getAccessActivityDocument($ressource);
                        if ($privilege == Privileges::ACTIVITY_DOCUMENT_MANAGE) {
                            return $access['write'] === true;
                        }
                        else {
                            return $access['read'] === true;
                        }
                    }
                }
                return in_array($privilege, $privileges);
            }
        } catch (\Exception $e) {
        }

        // Récupération des
        return false;
    }

    /**
     * @param string $privilege
     * @return bool
     * @throws \Exception
     */
    public function hasPrivilegeDeep(string $privilege): bool
    {
        if (!$this->hasPrivileges($privilege)) {
            return $this->hasPrivilegeInOrganizations($privilege);
        }
        return true;
    }

    /**
     * @param string $privilege
     * @return bool
     * @throws OscarException
     */
    public function hasPrivilegeInOrganizations(string $privilege): bool
    {
        $person = $this->getCurrentPerson();

        if (!$person) {
            return false;
        }

        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization) {
            if ($personOrganization->isPrincipal() && $this->hasPrivileges(
                    $privilege,
                    $personOrganization->getOrganization()
                )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $privilege
     * @return false|Organization[]
     * @throws \Exception
     */
    public function getOrganizationsWithPrivilege($privilege): ?array
    {
        $person = $this->getCurrentPerson();

        if (!$person) {
            return false;
        }
        /** @var PersonService $personService */
        $personService = $this->getPersonService();
        $organizations = $personService->getPersonOrganizations($person, true, true);
        $result = [];

        /** @var OrganizationPerson $personOrganization */
        foreach ($organizations as $personOrganization) {
            if ($this->hasPrivilegeInOrganizations($privilege, $personOrganization)) {
                $result[] = $personOrganization;
            }
        }

        return $result;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RECUPERATION des DONNEES (Général)
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @return array
     */
    public function getPrivilegesDatasArray(): array
    {
        $output = [];
        $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findBy([], ['root' => 'DESC']);
        /** @var Privilege $privilege */
        foreach ($privileges as $privilege) {
            $p = [
                'id'       => $privilege->getId(),
                'label'    => $privilege->getLibelle(),
                'category' => $privilege->getCategorie()->getLibelle(),
                'spot'     => $privilege->getSpot(),
                'roleIds'  => $privilege->getRoleIds(),
                'root'     => $privilege->getRoot() ? $privilege->getRoot()->getId() : null,
                'enabled'  => false
            ];
            $output[$privilege->getId()] = $p;
        }
        return $output;
    }

    public function hasSignaturePersonalAccess(): bool
    {
        return $this->getOscarConfigurationService()->signatureEnabled();
    }

    public function getCurrentRolesApplication(): array
    {
        return $this->getBaseRoleId();
    }
}
