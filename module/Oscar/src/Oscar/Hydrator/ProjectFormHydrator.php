<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 14:21
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Project;
use Laminas\Hydrator\HydratorInterface;

class ProjectFormHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Project $object
     */
    public function hydrate(array $data, $object)
    {
        $object->setLabel($data['label'])
            ->setAcronym($data['acronym'])
            ->setDescription($data['description']);
        return $object;
    }

    /**
     * @param Project $object
     * @return array
     */
    public function extract( object $object ): array
    {
        return [
            'id'        => $object->getId(),
            'label' => $object->getLabel(),
            'acronym' => $object->getAcronym(),
            'description' => $object->getDescription(),
        ];
    }
}