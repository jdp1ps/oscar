<?php

namespace Oscar\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ActivityMotCle
 * @package Oscar\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\Repository\ActivityAvenantRepository")
 */
class ActivityAvenant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateAvenant;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity")
     */
    private Activity $activity;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $filename;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $comment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getDateAvenant(): DateTime
    {
        return $this->dateAvenant;
    }

    public function setDateAvenant(DateTime $dateAvenant): self
    {
        $this->dateAvenant = $dateAvenant;
        return $this;
    }

    public function getActivity(): Activity
    {
        return $this->activity;
    }

    public function setActivity(Activity $activity): self
    {
        $this->activity = $activity;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getResourceId()
    {
        return self::class;
    }
}
