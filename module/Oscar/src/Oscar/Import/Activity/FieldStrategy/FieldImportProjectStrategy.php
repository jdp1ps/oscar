<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectRepository;

class FieldImportProjectStrategy implements IFieldImportStrategy
{

    private $entityManager;

    /**
     * FieldImportOrganizationStrategy constructor.
     * @param $entityManager
     * @param $role
     */
    public function __construct($entityManager, $role= null)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    
    public function run(&$activity, $datas, $index)
    {
        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->getEntityManager()->getRepository(Project::class);

        $project = $projectRepository->getProjectByLabelOrCreate($datas[$index]);
        if( $project )
            $activity->setProject($project);

        return $activity;
    }
}