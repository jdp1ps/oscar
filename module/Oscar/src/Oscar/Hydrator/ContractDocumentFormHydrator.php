<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/12/15 11:53
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Laminas\Hydrator\HydratorInterface;

class ContractDocumentFormHydrator implements HydratorInterface
{

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract(object $object): array
    {
        // TODO: Implement extract() method.
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        // TODO: Implement hydrate() method.
    }
}