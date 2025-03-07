<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * ActivityNote, note liée à une activité de recherche.
 *
 * @package Oscar\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityNoteRepository")
 */
class ActivityNote implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text", length=10000, nullable=false)
     */
    private $content = "";

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="notes")
     */
    private $activity;


    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $createdBy;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $updatedBy;


    function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

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
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): self
    {
        $this->content = mb_substr($content, 0, 10000);
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
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return Person
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param Person $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getResourceId()
    {
        return self::class;
    }
}
