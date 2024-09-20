<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 16:03
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;


use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Oscar\Formatter\OscarFormatterConst;

class OrganizationRoleRepository extends EntityRepository
{
    const FORMAT_ID_ROLE_ID = 'FORMAT_ID_ROLE_ID';

    /**
     * Retourne la liste des roles des organisations dans les activités/projets avec leur usage.
     *
     * @return array
     * @throws Exception
     */
    public function getRolesAndUsage() :array
    {
        $sql = 'select r.id, r.principal, r.label 
                from organizationrole r
                order by r.label';

        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        $roles = $stm->executeQuery()->fetchAllAssociative();

        $countActivity = 'select count(distinct id) from activityorganization a where roleobj_id = :id';
        $countProject = 'select count(distinct id) from projectpartner p where roleobj_id = :id';
        $stmActivity = $this->getEntityManager()->getConnection()->prepare($countActivity);
        $stmProject = $this->getEntityManager()->getConnection()->prepare($countProject);
        foreach ($roles as &$role) {
            $totalActivity = $stmActivity->executeQuery(['id'=>$role['id']])->fetchAssociative()['count'];
            $totalProject = $stmProject->executeQuery(['id'=>$role['id']])->fetchAssociative()['count'];
            $role['in_activity'] = $totalActivity;
            $role['in_project'] = $totalProject;
        }
        return $roles;
    }

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
            ->orderBy('r.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param OrganizationRole $from
     * @param OrganizationRole $to
     * @return void
     * @throws Exception
     */
    public function merge(OrganizationRole $from, OrganizationRole $to) :void
    {
        $sql = 'UPDATE activityorganization SET roleobj_id = :to WHERE roleobj_id = :from';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        $stm->executeQuery(['to' => $to->getId(), 'from' => $from->getId()]);

        $sql = 'UPDATE projectpartner SET roleobj_id = :to WHERE roleobj_id = :from';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        $stm->executeQuery(['to' => $to->getId(), 'from' => $from->getId()]);
    }
}