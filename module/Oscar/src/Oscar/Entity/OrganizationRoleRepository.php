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
    public function getRolesAndUsage(): array
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
            $totalActivity = $stmActivity->executeQuery(['id' => $role['id']])->fetchAssociative()['count'];
            $totalProject = $stmProject->executeQuery(['id' => $role['id']])->fetchAssociative()['count'];
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
    public function merge(OrganizationRole $from, OrganizationRole $to): void
    {
        $sql = 'UPDATE activityorganization SET roleobj_id = :to WHERE roleobj_id = :from';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        $stm->executeQuery(['to' => $to->getId(), 'from' => $from->getId()]);

        $sql = 'UPDATE projectpartner SET roleobj_id = :to WHERE roleobj_id = :from';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        $stm->executeQuery(['to' => $to->getId(), 'from' => $from->getId()]);
    }

    public function getRoleDoublonsActivity()
    {
        $sql = 'select 
                s.total, s.activity_id, s.organization_id, s.roleobj_id, s.datestart, s.dateend, s.activityorganization_id,
                a2.label as activity_label, a2.oscarnum as activity_oscarnum,
                o.code as organization_code, o.shortname as organization_shortname, o.fullname  as organization_fullname,
                o2.label
            from (
                select 
                    count(a.id) as total, 
                    a.activity_id, 
                    a.organization_id, 
                    a.roleobj_id,
                    a.datestart,
                    a.dateend, 
                     json_agg(a.id) as activityorganization_id 
                from activityorganization a 
                group by a.activity_id, a.organization_id , a.roleobj_id, a.datestart , a.dateend 
                order by total DESC
            ) as s 
            inner join activity a2 on a2.id = s.activity_id
            inner join organization o on o.id = s.organization_id
            inner join organizationrole o2 on o2.id = s.roleobj_id
            where s.total > 1';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        return $stm->executeQuery()->fetchAllAssociative();
    }

    public function getRoleDoublonsProject()
    {
        $sql = 'select 
                s.total, s.project_id, s.organization_id, s.roleobj_id, s.datestart, s.dateend, s.projectpartner_id,
                a2.acronym as project_acronym, a2.label as project_label,
                o.code as organization_code, o.shortname as organization_shortname, o.fullname  as organization_fullname,
                o2.label
            from (
                select 
                    count(a.id) as total, 
                    a.project_id, 
                    a.organization_id, 
                    a.roleobj_id,
                    a.datestart,
                    a.dateend, 
                     json_agg(a.id) as projectpartner_id 
                from projectpartner a 
                group by a.project_id, a.organization_id , a.roleobj_id, a.datestart , a.dateend 
                order by total DESC
            ) as s 
            inner join project a2 on a2.id = s.project_id
            inner join organization o on o.id = s.organization_id
            inner join organizationrole o2 on o2.id = s.roleobj_id
            where s.total > 1';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        return $stm->executeQuery()->fetchAllAssociative();
    }

    public function doublonDeleteActivityOrganizationBydIds(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM ' . ActivityOrganization::class . ' e WHERE e.id IN (:ids)'
        )->setParameter('ids', $ids);

        $query->execute();
    }

    public function doublonDeleteProjectPartnerBydIds(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM ' . ProjectPartner::class . ' e WHERE e.id IN (:ids)'
        )->setParameter('ids', $ids);

        $query->execute();
    }
}