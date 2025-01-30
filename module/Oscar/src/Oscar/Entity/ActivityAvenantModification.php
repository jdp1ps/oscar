<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class ActivityAvenantModification
{
    const TYPE_PERSON_DEL = 'personDel';
    const TYPE_PERSON_ADD = 'personAdd';
    const TYPE_ORGANIZATION_ADD = 'organizationAdd';
    const TYPE_ORGANIZATION_DEL = 'organizationDel';
    const TYPE_CHANGE_AMOUNT = 'changeAmount';
    const TYPE_DATE_END = 'dateEnd';

    public static function getTypes(): array
    {
        return array(
            self::TYPE_PERSON_DEL => "Suppression d'une personne",
            self::TYPE_PERSON_ADD => "Ajout d'une personne",
            self::TYPE_ORGANIZATION_ADD => "Suppression d'une organization",
            self::TYPE_ORGANIZATION_DEL => "Ajout d'une organisation",
            self::TYPE_CHANGE_AMOUNT => "Modification du montant",
            self::TYPE_DATE_END => "Modification de la date de fin",
        );
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $newValue1;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $oldValue1;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $newValue2;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $oldValue2;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $info;

    /**
     * @var ActivityAvenant
     * @ORM\ManyToOne(targetEntity="ActivityAvenant", inversedBy="modifications")
     */
    private ActivityAvenant $avenant;

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    public function getAvenant(): ActivityAvenant
    {
        return $this->avenant;
    }

    public function setAvenant(ActivityAvenant $avenant): self
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

    public function getNewValue1(): ?string
    {
        return $this->newValue1;
    }

    public function setNewValue1(?string $newValue1): self
    {
        $this->newValue1 = $newValue1;
        return $this;
    }

    public function getOldValue1(): ?string
    {
        return $this->oldValue1;
    }

    public function setOldValue1(?string $oldValue1): self
    {
        $this->oldValue1 = $oldValue1;
        return $this;
    }

    public function getNewValue2(): ?string
    {
        return $this->newValue2;
    }

    public function setNewValue2(?string $newValue2): self
    {
        $this->newValue2 = $newValue2;
        return $this;
    }

    public function getOldValue2(): ?string
    {
        return $this->oldValue2;
    }

    public function setOldValue2(?string $oldValue2): self
    {
        $this->oldValue2 = $oldValue2;
        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;
        return $this;
    }

    public function getResourceId(): string
    {
        return self::class;
    }

    public function toJson() :array {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'value1' => $this->getNewValue1(),
            'value2' => $this->getNewValue2(),
            'oldValue1' => $this->getOldValue1(),
            'info' => $this->getInfo(),
        ];
    }
}
