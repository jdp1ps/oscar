<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/06/15 12:14
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Discipline
 * @package Oscar\Entity
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Oscar\Entity\DisciplineRepository")
 */
class Discipline
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $centaureId;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    private $label;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Activity", mappedBy="disciplines")
     */
    private $activities;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getCentaureId()
    {
        return $this->centaureId;
    }

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $activities
     */
    public function setActivities($activities)
    {
        $this->activities = $activities;

        return $this;
    }



    /**
     * @param string $centaureId
     */
    public function setCentaureId($centaureId)
    {
        $this->centaureId = $centaureId;

        return $this;
    }
    
    public function __toString()
    {
        return (string) $this->getLabel();
    }

    public function toJson(){
        return [
            'id' => $this->getId(),
            'label' => $this->label,
        ];
    }
}
