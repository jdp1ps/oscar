<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\ActivityType;
use Oscar\Entity\DateType;
use Oscar\Entity\OscarFacet;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class DateTypeFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param DateType $object
     * @return array
     */
    public function extract($object)
    {
        return [
            'id' => $object->getId(),
            'description' => $object->getDescription(),
            'facet' => $object->getFacet(),
            'label' => $object->getLabel(),
        ];
    }

    /**
     * @param array $data
     * @param DateType $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setDescription($data['description'])
            ->setFacet(OscarFacet::getFacets()[$data['facet']])
            ->setLabel($data['label']);
    }


}