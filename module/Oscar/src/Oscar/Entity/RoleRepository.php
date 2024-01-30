<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-10-21 10:32
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OscarFormatterConst;

class RoleRepository extends EntityRepository
{
    const FORMAT_ROLEID_ARRAY_KEY = 'FORMAT_ROLEID_ARRAY_KEY';

    public function getRolesAtApplication(): QueryBuilder
    {
        return $this->getRolesAtLevel(Role::LEVEL_APPLICATION);
    }

    public function getRolesAtOrganization(): QueryBuilder
    {
        return $this->getRolesAtLevel(Role::LEVEL_ORGANIZATION);
    }

    public function getRolesAtActivity(): QueryBuilder
    {
        return $this->getRolesAtLevel(Role::LEVEL_ACTIVITY);
    }

    /**
     * Retourne la liste des rôles possible pour une personne sur une organisation.
     *
     * @param string $format
     * @return array
     */
    public function getRolesAtOrganizationArray(string $format = OscarFormatterConst::FORMAT_ARRAY_ID_VALUE): array
    {
        $return = [];
        $roles = $this->getRolesAtLevel(Role::LEVEL_ORGANIZATION)->getQuery()->getResult();

        if ($format == OscarFormatterConst::FORMAT_ARRAY_OBJECT) {
            return $roles;
        }

        if ($format == OscarFormatterConst::FORMAT_ARRAY_FLAT) {
            return array_map(function ($role) {
                return $role->getRoleId();
            }, $roles);
        }

        /** @var Role $role */
        foreach ($roles as $role) {
            switch ($format) {
                case OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT:
                    $return[$role->getRoleId()] = $role;
                    break;
                case OscarFormatterConst::FORMAT_ARRAY_ID_VALUE:
                    $return[$role->getRoleId()] = $role->getRoleId();
                    break;
            }
        }

        return $return;
    }

    /**
     * @param string $format
     * @return array
     */
    public function getRolesAtActivityArray(string $format = OscarFormatterConst::FORMAT_ARRAY_ID_VALUE): array
    {
        $return = [];
        $roles = $this->getRolesAtLevel(Role::LEVEL_ACTIVITY)->getQuery()->getResult();

        if ($format == OscarFormatterConst::FORMAT_ARRAY_OBJECT) {
            return $roles;
        }

        if ($format == OscarFormatterConst::FORMAT_ARRAY_FLAT) {
            return array_map(function ($role) {
                return $role->getRoleId();
            }, $roles);
        }

        /** @var Role $role */
        foreach ($this->getRolesAtLevel(Role::LEVEL_ACTIVITY)->getQuery()->getResult() as $role) {
            switch ($format) {
                case OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT:
                    $return[$role->getId()] = $role;
                    break;
                case OscarFormatterConst::FORMAT_ARRAY_ID_VALUE:
                    $return[$role->getId()] = $role->getRoleId();
                    break;
            }
        }

        return $return;
    }

    /**
     * @param array $roleIds
     * @return Role[]
     */
    public function getRolesByRoleId(array $roleIds): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.roleId in (:roleids)')
            ->setParameter('roleids', $roleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des rôles d'une activité sous la forme d'un tableau
     *
     * @return array
     */
    public function getRolesAvailableForPersonInOrganizationArray(): array
    {
        static $rolesOrganization;
        if ($rolesOrganization === null) {
            $rolesOrganization = [];
            foreach ($this->getRolesAvailableForPersonInOrganization() as $role) {
                $rolesOrganization[$role->getId()] = $role->getRoleId();
            }
        }
        return $rolesOrganization;
    }

    /**
     * Retourne la liste des rôles disponibles pour une personne dans une organisation.
     *
     * @return Role[]
     */
    public function getRolesAvailableForPersonInOrganization(): array
    {
        return $this->getRolesAtLevel(Role::LEVEL_ORGANIZATION)->getQuery()->getResult();
    }

    public function getRolesAvailableForPersonInActivity(): array
    {
        return $this->getRolesAtLevel(Role::LEVEL_ACTIVITY)->getQuery()->getResult();
    }

    public function getRolesAvailableForPersonInActivityOrOrganization(): array
    {
        return $this->getRolesAtLevel(Role::LEVEL_ACTIVITY + Role::LEVEL_ORGANIZATION)->getQuery()->getResult();
    }

    public function getRolesAvailableForPersonInActivityArray(): array
    {
        static $rolesActivity;
        if ($rolesActivity === null) {
            $rolesActivity = [];
            foreach ($this->getRolesAvailableForPersonInActivity() as $role) {
                $rolesActivity[$role->getId()] = $role->getRoleId();
            }
        }
        return $rolesActivity;
    }

    public function getRolesAvailableForPersonInActivityOrOrganizationArray(): array
    {
        static $rolesActivityOrOrganization;
        if ($rolesActivityOrOrganization === null) {
            $rolesActivityOrOrganization = [];
            foreach ($this->getRolesAvailableForPersonInActivityOrOrganization() as $role) {
                $rolesActivityOrOrganization[$role->getId()] = $role->getRoleId();
            }
        }
        return $rolesActivityOrOrganization;
    }


    /**
     * Organisation des données retournées :
     *  - FORMAT_ARRAY_OBJECT : [Role,Role,...]
     *  - FORMAT_ROLEID_ARRAY_KEY : [RoleId => Role, RoleId => Role,...]
     *  - FORMAT_ARRAY_ID_OBJECT : [ID => Role, ID => Role,...]
     *  - FORMAT_ARRAY_FLAT : [RoleId, RoleId,...]
     *
     * @param array $roles
     * @param string $format
     * @return array
     */
    protected function formatOutput(array $roles, string $format = OscarFormatterConst::FORMAT_ARRAY_OBJECT): array
    {
        if ($format == OscarFormatterConst::FORMAT_ARRAY_OBJECT) {
            return $roles;
        }

        $formatter = new RepositoryResultFormatter();
        if ($format == self::FORMAT_ROLEID_ARRAY_KEY) {
            $formatter->addFormat(self::FORMAT_ROLEID_ARRAY_KEY, 'getRoleId');
        }

        return $formatter->output($roles, $format);
    }

    public function getRolesAvailableForPerson(string $format = self::FORMAT_ROLEID_ARRAY_KEY): array
    {
        $roles = $this->findAll();
        return $this->formatOutput($roles, $format);
    }

    /**
     * Retourne la liste des rôles au niveau spécifié.
     *
     * @param $level
     * @return QueryBuilder
     */
    public function getRolesAtLevel($level): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('BIT_AND(r.spot, :level) > 0')
            ->orderBy('r.roleId')
            ->setParameter('level', $level);
    }

    /**
     * Retourne un tableau de role indexé par ROLEID.
     *
     * @return array|null
     */
    public function getRolesOscarByRoleId(): array
    {
        static $rolesByRoleId;
        if ($rolesByRoleId === null) {
            $rolesByRoleId = $this->getRolesAtOrganizationArray(OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT);
        }
        return $rolesByRoleId;
    }

    /**
     * @param string $roleId
     * @return array
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRoleByRoleId(string $roleId): array
    {
        $queryRole = $this->createQueryBuilder('r')
            ->from(Role::class, 'role')
            ->where('r.roleId = :roleId');

        return $queryRole->setParameter('roleId', $roleId)->getQuery()->getSingleResult();
    }

    /**
     * Retourne le role en fonction du roleID, si le rôle n'existe pas,
     * Le rôle est créé.
     *
     * @param $roleId
     * @return mixed|Role
     */
    public function getRoleOrCreate($roleId)
    {
        try {
            $role = $this->getRoleByRoleId($roleId);
        } catch (NoResultException $e) {
            echo "Le rôle '$roleId' n'existe pas, il va être créé... \n";
            $role = new Role();
            $this->getEntityManager()->persist($role);
            $role->setRoleId($roleId);
            $this->getEntityManager()->flush($role);
        }
        return $role;
    }


    public function getRolesWithPrivilege($privilege)
    {
        $roles = $this->createQueryBuilder('r')
            ->from(Role::class, 'role')
            ->innerJoin('role.privileges', 'p')
            ->innerJoin('p.categorie', 'c')
            //->where('1')
            ->getQuery()
            ->getResult();
        return $roles;
    }

    public function getRolesLdapFilter($ldapFilter = null)
    {
        $qb = $this->createQueryBuilder('r');
        if ($ldapFilter == null) {
            $qb->where('r.ldapFilter IS NOT NULL');
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne la liste des différents rôles endossés par la personne dans toutes les activitès.
     *
     * @param Person $person
     */
    public function getDistinctRolesPersonInActivities(Person $person)
    {
        $sql = "select distinct roleobj_id from activityperson a where person_id = :idPerson
                    union 
                select distinct roleobj_id from projectmember p where person_id = :idPerson";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $idPerson = $person->getId();

        if (!$query->execute(['idPerson' => $idPerson])) {
            throw new OscarException("Impossible de charger les rôles de $person dans les activités");
        }
        $idsRoles = $query->fetchAll();

        $results = $this->createQueryBuilder('r')
            ->where('r.id IN(:ids)')
            ->setParameter('ids', $idsRoles)
            ->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * Retourne la liste des différents rôles endossés par la personne dans toutes les activitès.
     *
     * @param Person $person
     */
    public function getDistinctRolesPersonInOrganizations(Person $person)
    {
        $sql = "SELECT DISTINCT ur.id FROM user_role ur "
            . "LEFT JOIN organizationperson op ON ur.id = op.roleobj_id "
            . "WHERE op.person_id = :idPerson";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $idPerson = $person->getId();

        if (!$query->execute(['idPerson' => $idPerson])) {
            throw new OscarException("Impossible de charger les rôles de $person dans les organisations");
        }
        $idsRoles = $query->fetchAll();

        $results = $this->createQueryBuilder('r')
            ->where('r.id IN(:ids)')
            ->setParameter('ids', $idsRoles)
            ->getQuery()
            ->getResult();

        return $results;
    }
}