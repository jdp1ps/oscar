<?php

namespace UnicaenAuth\Service;

use UnicaenAuth\Entity\Db\Privilege;
use UnicaenAuth\Entity\Db\Role;
use UnicaenAuth\Provider\Privilege\PrivilegeProviderInterface;
use \BjyAuthorize\Provider\Resource\ProviderInterface as ResourceProviderInterface;
use UnicaenAuth\Provider\Privilege\Privileges;

class PrivilegeService extends AbstractService implements PrivilegeProviderInterface, ResourceProviderInterface
{
    /**
     * @var array
     */
    protected $privilegesRoles;



    /**
     * @return Privilege[]
     */
    public function getList()
    {
        $dql        = 'SELECT p, c FROM UnicaenAuth\Entity\Db\Privilege p JOIN p.categorie c ORDER BY c.ordre, p.ordre';
        $query      = $this->getEntityManager()->createQuery($dql);
        $privileges = $query->getResult();

        return $privileges;
    }



    /**
     * @param $id
     *
     * @return null|Privilege
     */
    public function get($id)
    {
        return $this->getEntityManager()->getRepository('UnicaenAuth\Entity\Db\Privilege')->findOneBy(['id' => $id]);
    }



    /**
     * @param Privilege $privilege
     * @param Role      $role
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addRole(Privilege $privilege, Role $role)
    {
        $privilege->addRole($role);
        $this->getEntityManager()->flush();
    }



    /**
     * @param Privilege $privilege
     * @param Role      $role
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeRole(Privilege $privilege, Role $role)
    {
        $privilege->removeRole($role);
        $this->getEntityManager()->flush();
    }



    /**
     * Retourne un tableau à deux dimentions composé de chaînes de caractère UNIQUEMENT
     *
     * Format du tableau :
     * [
     *   'privilege_a' => ['role_1', ...],
     *   'privilege_b' => ['role_1', 'role_2', ...],
     * ]
     *
     * @return string[][]
     */
    public function getPrivilegesRoles()
    {
        if (null === $this->privilegesRoles) {
            $this->privilegesRoles = [];
            $dql = '
            SELECT
              p, c, r
            FROM
              UnicaenAuth\Entity\Db\Privilege p
              JOIN p.categorie c
              JOIN p.role r
            ';
            $result = $this->getEntityManager()->createQuery($dql)->getResult();
            foreach( $result as $privilege){
                /* @var $privilege Privilege */
                $pr = [];
                foreach( $privilege->getRole() as $role ){
                    /* @var $role Role */
                    $pr[] = $role->getRoleId();
                }
                $this->privilegesRoles[$privilege->getFullCode()] = $pr;
            }
        }

        return $this->privilegesRoles;
    }



    /**
     * @return array
     */
    public function getResources()
    {
        $resources  = [];
        $privileges = array_keys($this->getPrivilegesRoles());
        foreach ($privileges as $privilege) {
            $resources[] = Privileges::getResourceId($privilege);
        }

        return $resources;
    }

}
