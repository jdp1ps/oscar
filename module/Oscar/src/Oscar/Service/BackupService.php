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
use Oscar\Entity\ContractType;
use Oscar\Entity\Country3166;
use Oscar\Entity\Discipline;
use Oscar\Entity\Person;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\TVA;
use Oscar\Entity\TypeDocument;
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

    const ALL = 'all';

    const COUNTRIES = 'counties';

    const DISCIPLINES = 'disciplines';
    const PERSONS = 'persons';

    const SPENT_TYPE_GROUPS = 'spentypegroups';

    const TVA = 'tvas';

    const TYPE_DOCUMENTS = 'typedocuments';


    /**
     * @return string[]
     */
    public static function getAvailables(bool $labeled): array
    {
        $availables = [
            self::ACTIVITY_TYPES => "Types d'activité",
            self::DISCIPLINES => "Disciplines",
            self::COUNTRIES => "Pays normalisés",
            self::PERSONS => "Personnes",
            self::SPENT_TYPE_GROUPS => "Type de dépense (plan comptable)",
            self::TVA => "TVA",
            self::TYPE_DOCUMENTS => "Type de document"
        ];

        if( $labeled ){
            return $availables;
        } else {
            return array_keys($availables);
        }
    }

    public function export(string $datakeys): array
    {
        if ($datakeys == self::ALL) {
            $keys = self::getAvailables();
        }
        else {
            $keys = array_intersect(explode(',', $datakeys), self::getAvailables());
        }
        $out = [
            'version' => 'beta',
            'date'    => date('T-m-d H:i:s'),
            'errors'  => []
        ];
        foreach ($keys as $key) {
            switch ($key) {
                case self::ACTIVITY_TYPES:
                    $out[self::ACTIVITY_TYPES] = $this->activityType();
                    break;
                case self::COUNTRIES:
                    $out[self::COUNTRIES] = $this->countries();
                    break;
                case self::DISCIPLINES:
                    $out[self::DISCIPLINES] = $this->disciplines();
                    break;
                case self::PERSONS:
                    $out[self::PERSONS] = $this->persons();
                    break;
                case self::SPENT_TYPE_GROUPS:
                    $out[self::SPENT_TYPE_GROUPS] = $this->spentTypeGroup();
                    break;
                case self::TVA:
                    $out[self::TVA] = $this->tvas();
                    break;
                case self::TYPE_DOCUMENTS:
                    $out[self::TYPE_DOCUMENTS] = $this->typeDocuments();
                    break;
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

    public function tvas(): array
    {
        $out = [];
        $tvas = $this->getEntityManager()->getRepository(TVA::class)->findAll();
        /** @var TVA $tva */
        foreach ($tvas as $tva) {
            $out[$tva->getId()] = [
                'id'    => $tva->getId(),
                'label' => $tva->getLabel(),
                'rate'  => $tva->getRate(),
            ];
        }
        return $out;
    }

    public function typeDocuments(): array
    {
        $out = [];
        $typeDocuments = $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
        /** @var TypeDocument $typeDocument */
        foreach ($typeDocuments as $typeDocument) {
            $out[$typeDocument->getId()] = [
                'id'          => $typeDocument->getId(),
                'description' => $typeDocument->getDescription(),
                'label'       => $typeDocument->getLabel(),
                'default'     => $typeDocument->isDefault(),
            ];
        }
        return $out;
    }

    public function spentTypeGroup(): array
    {
        $out = [];
        $entities = $this->getEntityManager()->getRepository(SpentTypeGroup::class)->findAll();
        /** @var SpentTypeGroup $entity */
        foreach ($entities as $entity) {
            $out[$entity->getId()] = [
                'id'          => $entity->getId(),
                'label'       => $entity->getLabel(),
                'description' => $entity->getDescription(),
                'annexe'      => $entity->getAnnexe(),
                'code'        => $entity->getCode(),
                'blind'       => $entity->getBlind(),
                'rgt'         => $entity->getRgt(),
                'lgt'         => $entity->getLft()
            ];
        }
        return $out;
    }

    public function countries(): array
    {
        $out = [];
        $entities = $this->getEntityManager()->getRepository(Country3166::class)->findAll();
        /** @var Country3166 $country */
        foreach ($entities as $entity) {
            $out[$entity->getId()] = [
                'id'      => $entity->getId(),
                'label'   => $entity->getLabel(),
                'alpha2'  => $entity->getAlpha2(),
                'alpha3'  => $entity->getAlpha3(),
                'numeric' => $entity->getNumeric(),
                'fr'      => $entity->getFr(),
                'en'      => $entity->getEn(),
            ];
        }
        return $out;
    }

    public function disciplines():array
    {
        $out = [];
        $entities = $this->getEntityManager()->getRepository(Discipline::class)->findAll();
        /** @var Discipline $country */
        foreach ($entities as $entity) {
            $out[$entity->getId()] = [
                'id'      => $entity->getId(),
                'label'   => $entity->getLabel(),
            ];
        }
        return $out;

    }

}