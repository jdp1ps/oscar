<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Oscar\Formatter\OscarFormatterConst;

class OrganizationRoleRepository extends EntityRepository
{
    const FORMAT_ID_ROLE_ID = 'FORMAT_ID_ROLE_ID';

    public function getRoleByRoleIdOrCreate($roleId)
    {
        try {
            return $this->createQueryBuilder('r')
                ->select('r')
                ->where('r.label = :label')
                ->getQuery()
                ->setParameter('label', $roleId)
                ->getSingleResult();
        } catch (NoResultException $e) {
            $role = new OrganizationRole();
            $this->getEntityManager()->persist($role);
            $role->setLabel($roleId);
            $this->getEntityManager()->flush($role);
            return $role;
        }
    }

    /**
     * @return array
     * @throws \Oscar\Exception\OscarException
     */
    public function getRolesAvailableInActivityArray(): array
    {
        $formatter = new RepositoryResultFormatter();
        $formatter->addFormat(self::FORMAT_ID_ROLE_ID, 'getRoleId');
        return $formatter->output($this->getRolesAvailableInActivity(), OscarFormatterConst::FORMAT_ARRAY_ID_VALUE);
    }

    /**
     * @return OrganizationRole[]
     */
    public function getRolesAvailableInActivity(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->getQuery()
            ->getResult();
    }
}