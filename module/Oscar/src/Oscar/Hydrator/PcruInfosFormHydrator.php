<?php


namespace Oscar\Hydrator;


use Oscar\Formatter\PCRU\ActivityPcruInfosToFormArray;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Zend\Hydrator\HydratorInterface;

class PcruInfosFormHydrator implements HydratorInterface, UseServiceContainer
{
    use UseServiceContainerTrait;

    public function extract($object)
    {
        $hydrator = new ActivityPcruInfosToFormArray();
        return $hydrator->toArray($object);
    }

    public function hydrate(array $data, $object)
    {
        // TODO: Implement hydrate() method.
    }
}