<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use UnicaenSignature\Service\LoggerServiceAwareTrait;

class BackupService implements UseEntityManager, UseLoggerService, UseServiceContainer
{
    use UseEntityManagerTrait, UseLoggerServiceTrait, UseServiceContainerTrait;

    const ACTIVITY_TYPES = 'activitytypes';
    const PERSONS = 'persons';

    public function export(string $datakey): array
    {
        $keys = explode(',', $datakey);
        $out = [
            'version' => 'beta',
            'date' => date('T-m-d H:i:s'),
            'errors' => []
        ];
        foreach ($keys as $key) {
            switch ($key) {
                case self::ACTIVITY_TYPES:
                    $out[self::ACTIVITY_TYPES] = $this->activityType();
                case self::PERSONS:
                    $out[self::PERSONS] = $this->persons();
                default:
                    $out['errors'][] = "clef '$key' inconnue";
            }
        }
        return $out;
    }

    public function activityType(): array
    {
        /** @var ActivityTypeService $activityTypeService */
        $activityTypeService = $this->getServiceContainer()->get(ActivityTypeService::class);
        $types = $activityTypeService->getActivityTypes();
        $out = [];
        foreach ($types as $type) {
            $out[$type->getId()] = [
                'id'          => $type->getId(),
                'label'       => $type->getLabel(),
                'description' => $type->getDescription(),
                'lft'         => $type->getLft(),
                'rgt'         => $type->getRgt(),
            ];
        }
        return $out;
    }

    public function persons(): array
    {
        /** @var PersonService $personService */
        $personService = $this->getServiceContainer()->get(PersonService::class);
        $persons = $personService->getPersons();
        $out = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $out[$person->getId()] = $person->toArray();
        }
        return $out;
    }
}