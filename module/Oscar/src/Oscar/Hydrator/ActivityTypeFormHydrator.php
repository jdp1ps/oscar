<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\ActivityType;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class ActivityTypeFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param ActivityType $object
     * @return array
     */
    public function extract(object $object): array
    {
        return [
            'description' => $object->getDescription(),
            'label' => $object->getLabel(),
            'nature' => $object->getNature()
        ];
    }

    /**
     * @param array $data
     * @param ActivityType $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setDescription($data['description'])
            ->setLabel($data['label'])
            ->setNature($data['nature']);
    }


}