<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 03/11/15 14:47
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Oscar\Entity\PcruTypeContractRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="typecontractlabel_idx", columns={"label"})})
 */
class PcruTypeContract
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $label;

    /**
     * @var ActivityType
     * @ORM\ManyToOne(targetEntity="ActivityType")
     */
    private $activityType;

    /**
     * @return mixed
     */
    public function getId() :int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return ActivityType
     */
    public function getActivityType(): ?ActivityType
    {
        return $this->activityType;
    }

    /**
     * @param ActivityType $activityType
     */
    public function setActivityType(?ActivityType $activityType): self
    {
        $this->activityType = $activityType;
        return $this;
    }

    function __toString()
    {
        return $this->getLabel();
    }
}