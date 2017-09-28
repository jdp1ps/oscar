<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-10-21 10:32
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{
    /**
     * Retourne la liste des rôles niveau Application.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtApplication()
    {
        return $this->getRolesAtLevel(Role::LEVEL_APPLICATION);
    }

    /**
     * Retourne la liste des rôles niveau organization.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtOrganization()
    {
        return $this->getRolesAtLevel(Role::LEVEL_ORGANIZATION);
    }

    /**
     * Retourne la liste des rôles niveau Activité.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtActivity()
    {
        return $this->getRolesAtLevel(Role::LEVEL_ACTIVITY);
    }

    /**
     * Retourne la liste des rôles d'une activité sous la forme d'un tableau
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtActivityArray()
    {
        static $rolesActivity;
        if( $rolesActivity === null ){
            $rolesActivity = [];
            /** @var Role $role */
            foreach( $this->getRolesAtLevel(Role::LEVEL_ACTIVITY)->getQuery()->getResult() as $role ){
                $rolesActivity[$role->getId()] = $role->getRoleId();
            }
        }
        return $rolesActivity;
    }

    /**
     * Retourne la liste des rôles d'une activité sous la forme d'un tableau
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtOrganizationArray()
    {
        static $rolesOrganization;
        if( $rolesOrganization === null ){
            $rolesOrganization = [];
            /** @var Role $role */
            foreach( $this->getRolesAtLevel(Role::LEVEL_ORGANIZATION)->getQuery()->getResult() as $role ){
                $rolesOrganization[$role->getId()] = $role->getRoleId();
            }
        }
        return $rolesOrganization;
    }

    /**
     * Retourne la liste des rôles au niveau spécifié.
     *
     * @param $level
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesAtLevel($level)
    {
        $q = $this->createQueryBuilder('r')
            ->andWhere('BIT_AND(r.spot, :level) > 0')
            ->setParameter('level', $level);
        return $q;
    }

    /**
     * Retourne un tableau de role indexé par ROLEID.
     *
     * @return array|null
     */
    public function getRolesOscarByRoleId()
    {
        static $rolesByRoleId;
        if( $rolesByRoleId === null ){
            $rolesByRoleId = [];
            /** @var Role $role */
            foreach($this->findAll() as $role ){
                $rolesByRoleId[$role->getRoleId()] = $role;
            }
        }
        return $rolesByRoleId;
    }
}