<?php

namespace UnicaenAuth\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use UnicaenAuth\Entity\Db\Role;


/**
 * Class RoleService
 *
 * @package UnicaenAuth\Service
 * @author  Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class RoleService extends AbstractService
{
    /**
     * @return ObjectRepository
     */
    public function getRepo()
    {
        return $this->getEntityManager()->getRepository('UnicaenAuth\Entity\Db\Role');
    }



    /**
     * @return Role[]
     */
    public function getList()
    {
        $dql   = 'SELECT r FROM UnicaenAuth\Entity\Db\Role r ORDER BY r.roleId';
        $query = $this->getEntityManager()->createQuery($dql);
        $roles = $query->getResult();

        return $roles;
    }



    /**
     * @param $id
     *
     * @return null|Role
     */
    public function get($id)
    {
        return $this->getRepo()->findOneBy(['id' => $id]);
    }



    /**
     * Sauvegarde le rôle en BDD
     *
     * @param Role $role
     *
     * @return self
     */
    public function save(Role $role)
    {
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush($role);

        return $this;
    }



    /**
     * Supprime un rôle
     *
     * @param Role $role
     *
     * @return $this
     */
    public function delete(Role $role)
    {
        $this->getEntityManager()->remove($role);
        $this->getEntityManager()->flush($role);

        return $this;
    }



    /**
     * Nouvelle entité
     *
     * @return Role
     */
    public function newEntity()
    {
        return new Role;
    }
}
