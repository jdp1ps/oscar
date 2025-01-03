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

class ActivityDateFormHydrator implements HydratorInterface
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
    public function extract($object)
    {
        return [
            'id' => $object->getId(),
            'comment' => $object->getComment(),
            'dateStart' => $object->getDateStart() ? $object->getDateStart()->format('Y-m-d') : '',
            'type' => $object->getType() ? $object->getType()->getId() : null,
        ];
    }

    /**
     * @param array $data
     * @param ActivityDate $object
     */
    public function hydrate(array $data, $object)
    {
        return $object->setComment($data['comment'])
            ->setType($this->getType($data['type']))
            ->setDateStart(DateTimeUtils::toDatetime($data['dateStart']));
    }

    /**
     * @param $id
     * @return null|DateType
     */
    protected function getType( $id ){
        return $this->getProjectGrantService()->getDateType($id);
    }
}