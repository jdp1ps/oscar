<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 04/09/15 11:34
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Monolog\Logger;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Cette classe fournit des automatismes liés à la manipulation et la
 * consultation des projets.
 */
class ProjectService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->getServiceLocator()->get('Logger');
    }


    public function fixMovePartnersToActivities( Project $project, $flush =
    true )
    {
        foreach ($project->getActivities() as $activity ){
            /** @var ProjectMember $member */
            foreach ($project->getMembers() as $member ){
                if( !$activity->hasPerson($member->getPerson(),
                    $member->getRole(), $member->getDateStart(),
                        $member->getDateEnd(), false
                ) ){
//                    $activityPerson = new ActivityPerson();
//                    //$this->getEntityManager()->persist($activityPerson);
//                    $activityPerson->setPerson($member->getPerson())
//                        ->setActivity($activity)
//                        ->setRole($member->getRole())
//                        ->setDateStart($member->getDateStart())
//                        ->setDateEnd($member->getDateEnd());

                    $this->getLogger()->addDebug("Déplacement");

                }

            }
        }
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->getServiceLocator()->get('search');
    }


    public function fusion( Project $main, Project $fusionned ){
        echo "Fusion de " . $main->getId() ." " . $fusionned->getId() ." <br />";
        /** @var Activity $activity */
        foreach( $fusionned->getGrants() as $activity ){
            echo "Déplacement de l'activité $activity";
            $activity->setProject($main);
        }

        // On déplace les membres
        /** @var ProjectMember $member */
        foreach( $fusionned->getMembers() as $member ){
            if( !$fusionned->hasPerson($member->getPerson(), $member->getRole()) ){
                $member->setProject($main);
            } else {
                $this->getEntityManager()->remove($member);
            }
        }

        // On déplace les partenaires
        /** @var ProjectPartner $partner */
        foreach( $fusionned->getPartners() as $partner ){
            if( !$fusionned->hasPartner($partner->getOrganization(), $partner->getRole()) ){
                $partner->setProject($main);
            } else {
                $this->getEntityManager()->remove($partner);
            }
        }

        $this->getEntityManager()->remove($fusionned);

        $this->getEntityManager()->flush();
    }

    /**
     * Factorise la distribution des membres (Person).
     *
     * @param $projectId
     */
    public function simplifyMember( $projectId )
    {
        /** @var Project $project */
        $project = $this->getEntityManager()->getRepository(Project::class)->find($projectId);

        // Stoque la distribution dans les activités du projet
        $distrib = [];

        /** @var Activity $activity */
        foreach( $project->getGrants() as $activity ){

            /** @var ActivityPerson $member */
            foreach( $activity->getPersons() as $member ){

                // Déjà présent dans le projet avec le même rôle
                if( $project->hasPerson($member->getPerson(), $member->getRole()) ){
                    $this->getEntityManager()->remove($member);
                }

                // On enregistre la distribution
                else {
                    $personId = $member->getPerson()->getId();
                    $role = $member->getRole();
                    if( !isset($distrib[$personId.$role]) ){
                        $distrib[$personId.$role] = [
                            'person' => $member->getPerson(),
                            'role' => $role,
                            'count' => [$member]
                        ];
                    } else {
                        $distrib[$personId.$role]['count'][] = $member;
                    }
                }
            }
        }

        // On parse la distribution pour factoriser
        foreach( $distrib as $parter=>$dist ){
            if( count($dist['count']) == count($project->getGrants()) ){
                $merged = new ProjectMember();
                $this->getEntityManager()->persist($merged);
                $merged->setProject($project)
                    ->setPerson($dist['person'])
                    ->setRole($dist['role']);
                foreach($dist['count'] as $member){
                    $this->getEntityManager()->remove($member);
                }
            }
        }

        // On commit
        $this->getEntityManager()->flush();
    }

    /**
     * Factorise la distribution des partenaires.
     *
     * @param $projectId
     */
    public function simplifyPartners( $projectId )
    {
        /** @var Project $project */
        $project = $this->getEntityManager()->getRepository(Project::class)->find($projectId);

        // Stoque la distribution des personnes dans les activités du projet
        $distrib = [];

        // Partenaires des activités rangés par id
        /** @var Activity $activity */
        foreach( $project->getGrants() as $activity ){
            // Liste des membres de l'activité
            /** @var ActivityOrganization $partner */
            foreach( $activity->getOrganizations() as $partner ){

                // Déjà présent dans le projet avec le même rôle
                if( $project->hasPartner($partner->getOrganization(), $partner->getRole()) ){
                    $this->getEntityManager()->remove($partner);
                }

                // On enregistre la distribution
                else {
                    $organizationId = $partner->getOrganization()->getId();
                    $role = $partner->getRole();
                    if( !isset($distrib[$organizationId.$role]) ){
                        $distrib[$organizationId.$role] = [
                            'organization' => $partner->getOrganization(),
                            'role' => $role,
                            'count' => [$partner]
                        ];
                    } else {
                        $distrib[$organizationId.$role]['count'][] = $partner;
                    }
                }
            }
        }

        // On parse la distribution pour factoriser
        foreach( $distrib as $parter=>$dist ){
            if( count($dist['count']) == count($project->getGrants()) ){
                $merged = new ProjectPartner();
                $this->getEntityManager()->persist($merged);
                $merged->setProject($project)
                    ->setOrganization($dist['organization'])
                    ->setRole($dist['role']);
                foreach($dist['count'] as $partner){
                    $this->getEntityManager()->remove($partner);
                }
            }
        }

        // On commit
        $this->getEntityManager()->flush();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p,mm,pr,p,o, g.dateCreated AS HIDDEN activityDateCreated')
            ->from(Project::class, 'p')
            ->leftJoin('p.members', 'mm')
            ->leftJoin('mm.person', 'ps')
            ->leftJoin('p.partners', 'pr')
            ->leftJoin('pr.organization', 'o')
            ->leftJoin('p.grants', 'g')
            ->leftJoin('g.organizations', 'go')
            ->leftJoin('g.persons', 'gp')
        ;
        return $queryBuilder;
    }


    public function getProjects()
    {
        return $this->getBaseQuery()->getQuery()->getResult();
    }

    /**
     * Retourne la liste des projets paginés.
     *
     * @param $search
     * @param $page
     * @return UnicaenDoctrinePaginator
     */
    public function getProjectsSearchPaged($search, $page)
    {
        if ($search) {
            $qb = $this->search($search);
        } else {
            $qb = $this->getBaseQuery()->addOrderBy('g.dateCreated', 'DESC')->addOrderBy('p.id', 'DESC');
        }
        return new UnicaenDoctrinePaginator($qb, $page);
    }

    public function searchUpdate( Project $project )
    {
        /** @var ProjectGrantService $activityService */
        $activityService = $this->getServiceLocator()->get("ActivityService");

        foreach($project->getActivities() as $activity ){
            $activityService->searchUpdate($activity);
        }
    }

    /**
     * @param $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function search( $search )
    {
        $idsProject = $this->getServiceLocator()->get('ActivityService')->searchProject($search);
        return $qb = $this->getBaseQuery()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $idsProject);
    }

    /**
     * Liste les projets où une personne est impliquée.
     *
     * @param $userId
     * @return Project[]
     */
    public function getProjectUser($userId)
    {
        $qb = $this->getBaseQuery()
            ->where('mm.person = :userId OR gp.person = :userId')
            ->orderBy('p.dateUpdated', 'DESC')
            ->setParameter('userId', $userId);
        return $qb;
    }

    /**
     * Liste les projets où une organisation est impliquée.
     *
     * @param $userId
     * @return Project[]
     */
    public function getProjectOrganization($organizationId)
    {
        $qb = $this->getBaseQuery()
            ->where('pr.organization = :organizationId OR go.organization = :organizationId')
            ->setParameter('organizationId', $organizationId);
        return $qb;
    }

    /**
     * @param $id
     * @return Project
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getProject($id)
    {
        return $this->getBaseQuery()->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Retourne les projets filtrés par EOTP.
     *
     * @param $eotp
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProjectByEOTP( $eotp )
    {
        return $this->getBaseQuery()->where('g.codeEOTP = :eotp')
            ->setParameter('eotp', $eotp);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Consultation
    //
    ////////////////////////////////////////////////////////////////////////////
    public function getProjectsByUserEmail($email)
    {
        $projects = $this->getEntityManager()
            ->getRepository('Oscar\Entity\Person')
            ->findOneBy(['email' => $email]);

        if ($projects) {
            return $projects->getProjectAffectations();
        } else {
            return [];
        }
    }
}
