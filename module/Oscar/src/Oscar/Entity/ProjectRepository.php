<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class ProjectRepository extends EntityRepository {
    public function getUserProjects( $userId ){
        return $this->findAll();
    }

    public function getProjectByLabelOrCreate( $label ){

        $projects = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getResult();
        if( count($projects) == 0 ){
            $project = new Project();
            $this->getEntityManager()->persist($project);
            $project->setLabel($label)->setAcronym($label);
            echo "Création du projet $project\n";
            $this->getEntityManager()->flush($project);
            return $project;
        } else {
            return $projects[0];
        }

    }

    public function getByUserEmail( $userEmail, $time='all' )
    {
        $ids = $this->getEntityManager()->createQueryBuilder()
            ->select('p.id')
            ->from(Project::class, 'p')
            ->leftJoin('p.members', 'm')
            ->leftJoin('m.person', 'pe')
            ->leftJoin('p.grants', 'a')
            ->leftJoin('a.persons', 'ps')
            ->leftJoin('ps.person', 'pr')
            ->where('pe.email = :email OR pr.email = :email')
            ->setParameter('email', $userEmail)
            ->getQuery()
            ->getResult();

        $projectIds = [];
        foreach ($ids as $a) {
            $projectIds[] = $a['id'];
        }

        $projects = $query = $this->getCoreQuery()
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $projectIds)
            ->getQuery()->getResult();

        return $projects;
    }

    /**
     * Retourne le projet avec l'identifiant $id.
     * 
     * @param type $id Identifiant du projet.
     * @return Project
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getSingle( $id, $options=[] )
    {
        $query = $this->baseQuery($options)->andWhere('p.id = :id');

        return $query->getQuery()->setParameter('id', $id)->getSingleResult();
    }

    protected function applyOptions( QueryBuilder $query, array $options = null )
    {

    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function baseQuery($options=[])
    {
        $query = $this->getCoreQuery();

        // --- Options
        // ignoreDateMember: boolean
        // Filtre les personnes en fonction des dates d'affectations
        if( !(isset($options['ignoreDateMember']) && $options['ignoreDateMember'] === true) ){
            $query->andWhere('(m.dateStart <= :now OR m.dateStart IS NULL) AND (m.dateEnd IS NULL OR m.dateEnd >= :now)');
            $query->setParameter('now', new \DateTime());
        }

        return $query;
    }

    protected function getCoreQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('p, pg, s, gt, m, mp, pr, o')
            ->from('Oscar\Entity\Project', 'p')
            ->leftJoin('p.grants', 'pg')
            ->leftJoin('p.members', 'm')
            ->leftJoin('m.person', 'mp')
            ->leftJoin('pg.type', 'gt')
//            ->leftJoin('p.discipline', 'd')
            ->leftJoin('p.partners', 'pr')
            ->leftJoin('pr.organization', 'o')
            ->leftJoin('pg.source', 's')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->addOrderBy('mp.lastname', 'ASC')
            ->addOrderBy('o.shortName', 'ASC')
            ->addOrderBy('pr.role', 'ASC')
            ->addOrderBy('pg.dateCreated', 'DESC')
            ->addOrderBy('m.role', 'ASC');
        return $query;
    }

    public function getByIds( array $ids )
    {
        return $this->baseQuery()->andWhere('p.id IN (:ids)')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->addOrderBy('pr.main', 'DESC')
            ->addOrderBy('o.shortName', 'ASC')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
    
    public function getById( $id )
    {
        return $this->baseQuery()->andWhere('p.id = :id')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->addOrderBy('pr.main', 'DESC')
            ->addOrderBy('o.shortName', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function allByOrganization( $organizationId )
    {
        return $this->baseQuery()->where(':member MEMBER OF p.partners')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->getQuery()->execute(array(
            'member'    => $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($organizationId)
        ));
    }

    public function allByPerson( $personId )
    {
        return $this->baseQuery()->where('mp.id = :id')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->getQuery()->execute(array(
            'id'    => $personId
        ));
    }

    public function all()
    {
        return $this->baseQuery()
            ->orderBy('p.discipline','DESC')
            ->addOrderBy('p.dateCreated', 'DESC')
            ->getQuery()
            ->execute();
    }
}