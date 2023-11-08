<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;

use Oscar\Entity\DateType;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class DateTypeHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param DateType $object
     * @return array
     */
    public function extract(object $object): array
    {
        return [
            'label' => $object->getLabel(),
            'description' => $object->getDescription(),
            'facet' => $object->getFacet(),
            'recursivity' => $object->getRecursivity()
        ];
    }

    /**
     * @param array $data
     * @param DateType $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setDescription($data['description'])
            ->setLabel($data['label'])
            ->setFacet($data['facet'])
            ->setRecursivity($data['recursivity']);
    }
}