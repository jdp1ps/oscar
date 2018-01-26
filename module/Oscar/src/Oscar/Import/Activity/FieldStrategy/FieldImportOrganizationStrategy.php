<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;

use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;

class FieldImportOrganizationStrategy extends AbstractFieldImportStrategy
{


    public function getRole()
    {
        return $this->getKey();
    }

    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository()
    {
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    /**
     * @return OrganizationRoleRepository
     */
    public function getOrganizationRoleRepository()
    {
        return $this->getEntityManager()->getRepository(OrganizationRole::class);
    }


    public function run(&$activity, $datas, $index)
    {

        $data = trim(strval($datas[$index]));
        if( !$data ) return $activity;

        $organization = $this->getOrganizationRepository()->getOrganisationByNameOrCreate($datas[$index]);
        $organizationRole = $this->getOrganizationRoleRepository()->getRoleByRoleIdOrCreate($this->getRole());

        if ($organization && !$activity->hasOrganization($organization, $this->getRole())) {
            $activityOrganization = new ActivityOrganization();
            $this->getEntityManager()->persist($activityOrganization);
            $activityOrganization->setActivity($activity)
                ->setRoleObj($organizationRole)
                ->setOrganization($organization);
            $activity->getOrganizations()->add($activityOrganization);
            $this->getEntityManager()->flush($activityOrganization);
        }

        return $activity;
    }
}