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
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Hydrator\HydratorInterface;

class DateTypeFormHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param DateType $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'id' => $object->getId(),
            'description' => $object->getDescription(),
            'facet' => array_search($object->getFacet(), OscarFacet::getFacets()),
            'label' => $object->getLabel(),
            'recursivity' => $object->getRecursivity(),
        ];
        if( $object->isFinishable() ){
            $data['finishable'] = 1;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param DateType $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setDescription($data['description'])
            ->setFacet(OscarFacet::getFacets()[$data['facet']])
            ->setRecursivity($data['recursivity'])
            ->setFinishable(array_key_exists('finishable', $data) ? true : false)
            ->setLabel($data['label']);
    }
}