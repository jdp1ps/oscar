<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 15:31
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;

use Oscar\Entity\WorkPackage;
use Oscar\Hydrator\Hydrator;
use Oscar\Utils\DateTimeUtils;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Hydrator\HydratorInterface;

class WorkPackageHydrator implements HydratorInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    /**
     * @param array $data
     * @param WorkPackage $object
     */
    public function hydrate(array $data, $object)
    {
        $object
            ->setLabel($data['label'])
            ->setCode($data['code'])
            ->setDescription($data['description'])
            ->setDateStart(DateTimeUtils::toDatetime($data['dateStart']))
            ->setDateEnd(DateTimeUtils::toDatetime($data['dateEnd']))
        ;
        return $object;
    }

    /**
     * @param WorkPackage $object
     * @return array
     */
    public function extract( $object )
    {
        return [
            'id' => $object->getId() ? $object->getId() : '',
            'label' => $object->getLabel(),
            'code' => $object->getCode(),
            'description' => $object->getDescription(),
            'dateStart' => $object->getDateStart(),
            'dateEnd' => $object->getDateEnd(),
        ];
    }
}
