<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/06/15 12:53
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oscar\Utils\DateTimeUtils;

/**
 * Membre d'un projet.
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class ProjectMember implements ILoggable
{

    use TraitRole, TraitTrackable;

    public function isLeader()
    {
        return $this->getRoleObj()->isPrincipal();
    }

    public function isMain()
    {
        return $this->getRoleObj()->isPrincipal();
    }

    public function isRole($role)
    {
        return $this->getRole() == $role;
    }

    /**
     * Retourne un Booléen qui indique si le rôle est actif (ou pas).
     *
     * @return bool
     */
    public function isActive(\DateTime $dateReference = null)
    {
        $dateReference = $dateReference ? $dateReference : new \DateTime();

        return
            ($this->getDateStart() === null || $dateReference > $this->getDateStart())
            &&
            ($this->getDateEnd() === null || $dateReference < $this->getDateEnd());
    }

    /**
     * Retourne un Booléen qui indique si le rôle est caduc (passé).
     *
     * @return bool
     */
    public function isObsolete(\DateTime $dateReference = null)
    {
        $dateReference = $dateReference ? $dateReference : new \DateTime();

        return
            ($this->getDateEnd() !== null && $this->getDateEnd() < $dateReference)
            &&
            ($this->getDateStart() === null || $this->getDateStart() < $dateReference);
    }

    /**
     * Retourne un Booléen qui indique si le rôle est caduc (passé).
     *
     * @return bool
     */
    public function isFuture(\DateTime $dateReference = null)
    {
        $dateReference = $dateReference ? $dateReference : new \DateTime();

        return
            ($this->getDateStart() !== null && $this->getDateStart() > $dateReference);
    }

    /**
     * Projet
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="members")
     */
    private $project;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="projectAffectations")
     */
    private $person;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     */
    private $roleObj;

    /**
     * @return Role
     */
    public function getRoleObj()
    {
        return $this->roleObj;
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
     * @param Person $roleObj
     */
    public function setRoleObj($roleObj)
    {
        $this->roleObj = $roleObj;

        return $this;
    }


    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
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

    function __toString()
    {
        return (string)$this->getPerson() . '(' . $this->getRole() . ')';
    }

    public function log()
    {
        return sprintf("%s (%s) dans le projet %s)", $this->getPerson()->log(),
            $this->getRole(), $this->getProject()->log());
    }

    function toArray()
    {
        return array(
            'id' => $this->getId(),
            'role' => $this->getRole(),
            'isMain' => $this->isMain(),
            'isLeader' => $this->isLeader(),
            'dateStart' => DateTimeUtils::toStr($this->getDateStart()),
            'dateEnd' => DateTimeUtils::toStr($this->getDateEnd()),
            'object' => $this->getPerson()->toArray(),
            'person' => $this->getPerson()->toArray(),
        );
    }


    function getEnrolled()
    {
        return $this->getPerson();
    }

    function getEnroller()
    {
        return $this->getProject();
    }
}
