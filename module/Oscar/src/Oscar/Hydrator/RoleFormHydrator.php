<?php


namespace Oscar\Hydrator;


use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Service\PersonService;
use Oscar\Utils\DateTimeUtils;
use Zend\Hydrator\HydratorInterface;

class RoleFormHydrator implements HydratorInterface
{

    /** @var PersonService */
    protected $personService;

    protected $enroller;

    /**
     * RoleFormHydrator constructor.
     * @param PersonService $personService
     */
    public function __construct(PersonService $personService, $enroller)
    {
        $this->personService = $personService;
        $this->enroller = $enroller;
    }

    /**
     * @param OrganizationPerson $object
     * @return array|void
     */
    public function extract($object)
    {
        return [
            'enrolled' => $object->getPerson() ? $object->getPerson()->getId() : null,
            'role' => $object->getRoleObj() ? $object->getRoleObj()->getId() : null,
            'dateStart' => $object->getDateStart() ? $object->getDateStart()->format('Y-m-d') : '',
            'dateEnd' => $object->getDateEnd() ? $object->getDateEnd()->format('Y-m-d') : '',
        ];
    }

    /**
     * @param array $data
     * @param OrganizationPerson $object
     * @return object|void
     */
    public function hydrate(array $data, $object)
    {
        $object->setPerson($this->personService->getPersonById($data['enrolled'], true))
            ->setOrganization($this->enroller)
            ->setRoleObj($this->personService->getRolePersonById($data['role']))
            ->setDateStart(DateTimeUtils::toDatetime($data['dateStart']))
            ->setDateEnd(DateTimeUtils::toDatetime($data['dateEnd']));
        return $object;
    }
}