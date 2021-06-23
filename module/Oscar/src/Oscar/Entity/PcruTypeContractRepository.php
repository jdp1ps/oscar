<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:54
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


use Doctrine\ORM\EntityManager;
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

    /**
     * @param string $label
     * @return PcruTypeContract
     */
    public function getPcruTypeContratByLabel( string $label ): ?PcruTypeContract
    {
        return $this->findOneBy(['label' => $label]);
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

    /**
     * Retourne l'intitulé PCRU pour le type d'activité donnée.
     *
     * @param ActivityType $activityType
     * @return string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPcruContractByActivityType( ActivityType $activityType ): ?PcruTypeContract
    {
        try {
            $query = $this->createQueryBuilder('ptc')
                ->where('ptc.activityType = :activityType')
                ->setParameter('activityType', $activityType)
                ->getQuery();

            return $query->getSingleResult();

        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * Retourne l'intitulé PCRU pour le type d'activité donnée (en parsant l'arbre des types).
     *
     * @param ActivityType $activityType
     * @return string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPcruContractForActivityTypeChained( ActivityType $activityType) : ?PcruTypeContract
    {
        $labelPcru = $this->getPcruContractByActivityType($activityType);

        if( $labelPcru == null ){
            $typeChain = $this->getEntityManager()->getRepository(ActivityType::class)->getChainFromActivityType($activityType);
            foreach ($typeChain as $type) {
                $type = $this->getPcruContractByActivityType($type);
                if( $type ){
                    return $type;
                }
            }
        }

        return $labelPcru;
    }
}