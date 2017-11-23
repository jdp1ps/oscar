<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Currency;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;

class FieldImportMilestoneStrategy extends AbstractFieldImportStrategy
{
    public function run(&$activity, $datas, $index)
    {
        echo "MILESTONE " . $this->getKey() . "\n";
        return $activity;
    }
}