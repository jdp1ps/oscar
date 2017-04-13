<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/06/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class WorkPackagePerson implements ILoggable
{
    use TraitTrackable;

    function log(){
        return sprintf("%s doit déclarer %s heures dans le lot %s.", $this->getPerson(), $this->getDuration(), $this->getWorkPackage());
    }

    /**
     * @var WorkPackage
     * @ORM\ManyToOne(targetEntity="WorkPackage", inversedBy="persons")
     */
    private $workPackage;


    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="workPackages")
     */
    private $person;


    /**
     * Durée de travail prévue
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
     private $duration = 0;

    /**
     * @return WorkPackage
     */
    public function getWorkPackage()
    {
        return $this->workPackage;
    }

    /**
     * @param WorkPackage $workPackage
     */
    public function setWorkPackage($workPackage)
    {
        $this->workPackage = $workPackage;

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

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function toArray()
    {
        throw new \Exception('Not implmented');
    }

    function __toString()
    {
        return 'Prévision';
    }
}