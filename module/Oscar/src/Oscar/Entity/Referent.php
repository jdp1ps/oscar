<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/10/18
 * Time: 14:43
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cette classe référence les rôles GLOBAUX sur l'application.
 *
 * @ORM\Entity
 */
class Referent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $referent;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $person;

    /**
     * @var \DateTime Date de début du rôle (infini si null)
     * Intitulé du rôle
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime Date de fin du rôle (infini si null)
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @return Person
     */
    public function getReferent()
    {
        return $this->referent;
    }

    /**
     * @param Person $referent
     */
    public function setReferent($referent)
    {
        $this->referent = $referent;
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
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }
}