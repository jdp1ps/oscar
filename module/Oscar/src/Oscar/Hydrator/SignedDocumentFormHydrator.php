<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/11/15 15:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Hydrator;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityType;
use Oscar\Entity\DateType;
use Oscar\Service\ProjectGrantService;
use Oscar\Utils\DateTimeUtils;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class SignedDocumentFormHydrator implements HydratorInterface
{
    /** @var ProjectGrantService */
    private $projectGrantService;

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->projectGrantService;
    }

    /**
     * @param ProjectGrantService $projectGrantService
     */
    public function setProjectGrantService(ProjectGrantService $projectGrantService): void
    {
        $this->projectGrantService = $projectGrantService;
    }

    /**
     * @param ActivityDate $object
     * @return array
     */
    public function extract($object) :array
    {
        return [

        ];
    }

    /**
     * @param array $data
     * @param $object
     */
    public function hydrate(array $data, $object)
    {
        return $object;
    }
}