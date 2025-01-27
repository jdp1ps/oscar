<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class ActivityAvenantModification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="ActivityAvenant")
     */
    private Activity $avenant;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $newValue1;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $oldValue1;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $newValue2;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $oldValue2;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getAvenant(): Activity
    {
        return $this->avenant;
    }

    public function setAvenant(Activity $avenant): self
    {
        $this->avenant = $avenant;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getNewValue1(): string
    {
        return $this->newValue1;
    }

    public function setNewValue1(string $newValue1): self
    {
        $this->newValue1 = $newValue1;
        return $this;
    }

    public function getOldValue1(): string
    {
        return $this->oldValue1;
    }

    public function setOldValue1(string $oldValue1): self
    {
        $this->oldValue1 = $oldValue1;
        return $this;
    }

    public function getNewValue2(): string
    {
        return $this->newValue2;
    }

    public function setNewValue2(string $newValue2): self
    {
        $this->newValue2 = $newValue2;
        return $this;
    }

    public function getOldValue2(): string
    {
        return $this->oldValue2;
    }

    public function setOldValue2(string $oldValue2): self
    {
        $this->oldValue2 = $oldValue2;
        return $this;
    }

    public function getResourceId()
    {
        return self::class;
    }
}
