<?php

namespace Oscar\Service;

use Oscar\Entity\Country3166;
use Oscar\Entity\Discipline;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Entity\TVA;
use Oscar\Entity\TypeDocument;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;

class BackupService implements UseEntityManager, UseLoggerService, UseServiceContainer
{
    use UseEntityManagerTrait, UseLoggerServiceTrait, UseServiceContainerTrait;

    const ACTIVITY_TYPES = 'activitytypes';

    const ALL = 'all';

    const COUNTRIES = 'counties';

    const DISCIPLINES = 'disciplines';

    const ORGANIZATIONS = 'organizations';

    const PERSONS = 'persons';

    const SPENT_TYPE_GROUPS = 'spentypegroups';

    const TVA = 'tvas';

    const TYPE_DOCUMENTS = 'typedocuments';


    /**
     * @return string[]
     */
    public static function getAvailables(bool $labeled = false): array
    {
        $available = [
            self::ACTIVITY_TYPES    => "Types d'activité",
            self::DISCIPLINES       => "Disciplines",
            self::COUNTRIES         => "Pays normalisés",
            self::ORGANIZATIONS     => "Organisations",
            self::PERSONS           => "Personnes",
            self::SPENT_TYPE_GROUPS => "Type de dépense (plan comptable)",
            self::TVA               => "TVA",
            self::TYPE_DOCUMENTS    => "Type de document"
        ];

        if ($labeled) {
            return $available;
        }
        else {
            return array_keys($available);
        }
    }

    public function export(string $datakeys): array
    {
        $took = microtime(true);
        $out = [
            'version' => 'beta',
            'date'    => date('T-m-d H:i:s'),
            'errors'  => [],
            'params'  => $datakeys
        ];

        if ($datakeys == self::ALL) {
            $keys = self::getAvailables();
        }
        else {
            $keys = array_intersect(explode(',', $datakeys), self::getAvailables());
        }

        if (!count($keys)) {
            $out['errors'][] = "clef '$datakeys' invalide";
        }

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
                case self::ORGANIZATIONS:
                    $out[self::ORGANIZATIONS] = $this->organizations();
                    break;
                default:
                    $out['errors'][] = "clef '$key' inconnue";
                    break;
            }
        }
        $out['took'] = microtime(true) - $took;
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
            $out[$person->getId()] = $this->person($person);
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

    public function person(Person $person): array
    {
        $address = [
            "address1" => ""
        ];
        $groups = [];
        $roles = [];

        /** @var OrganizationPerson $organization */
        foreach ($person->getOrganizations() as $organization) {
            $codeOrganization = $organization->getOrganization()->getCode();
            if (!array_key_exists($codeOrganization, $roles)) {
                $roles[$codeOrganization] = [];
            }
            $roles[$codeOrganization][] = $organization->getRoleObj()->getRoleId();
        }

        $out = [
            'id'                 => $person->getId(),
            'uid'                => $person->getId(),
            'login'              => $person->getLadapLogin(),
            'firstName'          => $person->getFirstname(),
            'lastName'           => $person->getLastname(),
            'displayname'        => $person->getDisplayName(),
            'mail'               => $person->getEmail(),
            'email'              => $person->getEmail(),
            'status'             => $person->getLdapStatus(),
            'affectation'        => $person->getLdapAffectation() ? $person->getLdapAffectation() : "",
            'structure'          => $person->getLdapSiteLocation() ? $person->getLdapSiteLocation() : "",
            'inm'                => $person->getHarpegeINM(),
            'phone'              => $person->getPhone(),
            'datefininscription' => $person->getLdapFinInscription(),
            'datecreated'        => $person->getDateCreatedStr('Y-m-d'),
            'dateupdated'        => $person->getDateUpdatedStr('Y-m-d'),
            'datecached'         => $person->getDateCachedStr('Y-m-d'),
            'address'            => $address,
            'groups'             => $groups,
            'roles'              => $roles,
            'label'              => $person->getDisplayName(),
            'text'               => $person->getDisplayName(),
            'mailMd5'            => md5($person->getEmail()),
        ];
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

    public function disciplines(): array
    {
        $out = [];
        $entities = $this->getEntityManager()->getRepository(Discipline::class)->findAll();
        /** @var Discipline $country */
        foreach ($entities as $entity) {
            $out[$entity->getId()] = [
                'id'    => $entity->getId(),
                'label' => $entity->getLabel(),
            ];
        }
        return $out;
    }

    private function organizations(): array
    {
        $out = [];
        $entities = $this->getEntityManager()->getRepository(Organization::class)->findAll();
        foreach ($entities as $entity) {
            $out[$entity->getId()] = $this->organization($entity);
        }
        return $out;
    }

    private function organization(Organization $organization): array
    {
        $address = [
            "address1"     => $organization->getStreet1(),
            "address2"     => $organization->getStreet2(),
            "address3"     => $organization->getStreet3(),
            "zipcode"      => $organization->getZipCode(),
            "city"         => $organization->getCity(),
            "country"      => $organization->getCountry(),
            "country_code" => $organization->getCodePays(),
        ];

        $out = [
            'id'          => $organization->getId(),
            'uid'         => $organization->getId(),
            'parent'      => $organization->getParent() ? $organization->getParent()->getId() : null,
            'shortname'   => $organization->getShortName(),
            'longname'    => $organization->getFullName(),
            "labintel"    => $organization->getLabintel(),
            'description' => $organization->getDescription(),
            'email'       => $organization->getEmail(),
            'phone'       => $organization->getPhone(),
            'type'       => $organization->getType(),
            'tvaintra'       => $organization->getTvaintra(),
            'siret'       => $organization->getSiret(),
            'datecreated' => $organization->getDateCreatedStr('Y-m-d'),
            'dateupdated' => $organization->getDateUpdatedStr('Y-m-d'),
            'datecached'  => $organization->getDateCachedStr('Y-m-d'),
            'address'     => $address,
            'mailMd5'     => md5($organization->getEmail()),
        ];
        return $out;
    }

    public function restore(string $key, array $value)
    {
        var_dump($key);
        var_dump($value);
    }
}