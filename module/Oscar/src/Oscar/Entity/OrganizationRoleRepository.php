<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class OrganizationRoleRepository extends EntityRepository
{
    public function getRoleByRoleIdOrCreate( $roleId ){
        try {
            return $this->createQueryBuilder('r')
                ->select('r')
                ->where('r.label = :label')
                ->getQuery()
                ->setParameter('label', $roleId)
                ->getSingleResult();
        } catch (NoResultException $e){
            $role = new OrganizationRole();
            $this->getEntityManager()->persist($role);
            $role->setLabel($roleId);
            $this->getEntityManager()->flush($role);
            return $role;
        }
    }
}