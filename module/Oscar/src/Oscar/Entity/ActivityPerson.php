<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/11/15 14:45
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relation Activité de recherche <> Personne
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ActivityPerson  implements ILoggable
{
    use TraitRole, TraitTrackable;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="activities")
     */
    private $person;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="persons")
     */
    private $activity;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     */
    private $roleObj;

    public function isPrincipal(){
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->isPrincipal();
        }
        return false;
    }

    /**
     * @return Role
     */
    public function getRoleObj()
    {
        return $this->roleObj;
    }

    /**
     * @param Role $roleObj
     */
    public function setRoleObj($roleObj)
    {
        $this->roleObj = $roleObj;

        return $this;
    }



    public function getRole()
    {
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->getRoleId();
        }
        return "rôle inconnu";
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param Activity $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    function getEnrolled()
    {
        return $this->getPerson();
    }

    function getEnroller()
    {
        return $this->getActivity();
    }
}
