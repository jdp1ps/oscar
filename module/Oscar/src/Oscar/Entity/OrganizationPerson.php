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
class OrganizationPerson implements ILoggable
{
    use TraitRole, TraitTrackable;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="organizations")
     */
    private $person;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="Person", cascade={"persist"})
     */
    private $organization;


    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     */
    private $roleObj;

    /**
     * Permet de stoquer l'origine du rôle.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true, name="origin")
     */
    private $origin;

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    public function isSync()
    {
        return !($this->getOrigin() == null || $this->getOrigin() == '');
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
        if ($this->getRoleObj()) {
            return $this->getRoleObj()->getRoleId();
        }
        return "rôle inconnu";
    }

    public function isPrincipal()
    {
        if ($this->getRoleObj()) {
            return $this->getRoleObj()->isPrincipal();
        }
        return false;
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
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    public function idLeader()
    {
        return in_array($this->getRole(), ['Responsable']);
    }

    function getEnrolled()
    {
        return $this->getPerson();
    }

    function getEnroller()
    {
        return $this->getOrganization();
    }

    public function __toString(): string
    {
        return sprintf(
            "OrganizationPerson[%s-%s] %s > %s (%s)",
            $this->getId(),
            $this->getOrigin(),
            $this->getOrganization(),
            $this->getPerson(),
            $this->getRoleObj()
        );
    }


}
