<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Import\Data\DataExtractorDate;

class FieldImportMilestoneStrategy extends AbstractFieldImportStrategy
{
    public function run(&$activity, $datas, $index)
    {
        $repo = $this->getEntityManager()->getRepository(DateType::class);
        $milestoneType = $repo->findOneBy(['label' => $this->getKey()]);
        if( $milestoneType ){
            $milestone = new ActivityDate();
            $dateExtractor = new DataExtractorDate();
            $date = $dateExtractor->extract($datas[$index]);
            if( $date ){
                $milestone->setActivity($activity)
                    ->setType($milestoneType)
                    ->setDateStart($date);
                $activity->addActivityDate($milestone);
                $this->getEntityManager()->persist($milestone);
            }
        }
        return $activity;
    }
}