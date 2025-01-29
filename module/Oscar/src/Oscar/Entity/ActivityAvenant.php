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

    const STATUS_DRAFT = 100;

    const STATUS_ACTIVE = 200;

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
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="avenants")
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
     * @var int
     * @ORM\Column(type="integer", options={"default": "100"})
     */
    private int $status;

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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusLabel() :string
    {
        return self::getStatusText($this->getStatus());
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getResourceId()
    {
        return self::class;
    }

    /**
     * @return string[]
     */
    public static function getStatusList() :array
    {
        return [
          self::STATUS_DRAFT => 'Brouillon',
          self::STATUS_ACTIVE => 'Effectif',
        ];
    }

    /**
     * @param int $status
     * @return string
     */
    public function getStatusText( int $status ) :string
    {
        if( !array_key_exists($status, self::getStatusList()) ){
            return "Inconnue";
        }
        return self::getStatusList()[$status];
    }
}
