<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-22 15:31
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Activity\FieldStrategy;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;

class FieldImportPersonStrategy extends AbstractFieldImportStrategy
{

    public function getRole(){
        return $this->getKey();
    }

    /**
     * @return PersonRepository
     */
    protected function getPersonRepository(){
        return $this->getEntityManager()->getRepository(Person::class);
    }

    /**
     * @return RoleRepository
     */
    protected function getRoleRepository(){
        return $this->getEntityManager()->getRepository(Role::class);
    }

    /**
     * @param Activity $activity
     * @param array $datas
     * @param int $index
     * @return mixed
     */
    public function run(&$activity, $datas, $index)
    {
        $person = $this->getPersonRepository()->getPersonByDisplayNameOrCreate($datas[$index]);
        $roleObj = $this->getRoleRepository()->getRoleOrCreate($this->getRole());

        if( !$person ){
            echo "Error, impossible de créer la personne : " . $datas[$index] ." \n";
            return $activity;
        }

        if( !$activity->hasPerson($person, $roleObj->getRoleId()) ){
            $rolePerson = new ActivityPerson();
            $this->getEntityManager()->persist($rolePerson);
            $rolePerson->setPerson($person)
                ->setActivity($activity)
                ->setRoleObj($roleObj);
            $activity->addActivityPerson($rolePerson);
        }
        return $activity;
    }
}