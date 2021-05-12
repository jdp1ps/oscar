<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:54
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\IConnectedRepository;
use Oscar\Import\Activity\FieldStrategy\FieldImportOrganizationStrategy;
use Oscar\Import\Data\DataExtractorOrganization;

class PcruTypeContractRepository extends EntityRepository
{
    public function getFlatArrayLabel(): array
    {
        $query = $this->createQueryBuilder('ptc');
        $query->select('ptc.label');
        $entities = $query->getQuery()->getResult();
        return array_map('current', $entities);
    }
    public function getArrayDatasJoined(): array
    {
        $query = $this->createQueryBuilder('ptc')->orderBy("ptc.label", "ASC");
        $out = [];
        /** @var PcruTypeContract $pcruTypeContract */
        foreach ($query->getQuery()->getResult() as $pcruTypeContract) {
            $activityTypeLabel = "";
            $activityTypeId = null;
            if( $pcruTypeContract->getActivityType() ){
                $activityTypeLabel = $pcruTypeContract->getActivityType()->getLabel();
                $activityTypeId = $pcruTypeContract->getActivityType()->getId();
            }

            $out[] = [
                'id' => $pcruTypeContract->getId(),
                'label' => $pcruTypeContract->getLabel(),
                'activitytype_id' => $activityTypeId,
                'activitytype_label' => $activityTypeLabel
            ];
        }
        return $out;
    }
}