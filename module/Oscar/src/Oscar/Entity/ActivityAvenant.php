<?php

/**
 * Classe pour gérer les Avenants des activités de recherche.
 *
 * @category Class
 * @package  Oscar\Entity
 * @author   Stéphane Bouvry <stephane.bouvry@unicaen.fr>
 */

namespace Oscar\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Oscar\Entity\Repository\ActivityAvenantRepository")
 */
class ActivityAvenant
{
    const STATUS_DRAFT = 100;

    const STATUS_ACTIVE = 200;

    /**
     * ID bdd
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * Date de signature de l'avenant
     *
     * @ORM\Column(type="datetime")
     * @var ?DateTime
     */
    protected ?DateTime $dateAvenant;

    /**
     * Activité de l'avenant
     *
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="avenants")
     */
    protected Activity $activity;

    /**
     * Liste des modifications
     *
     * @var Collection
     * @ORM\OneToMany(targetEntity="ActivityAvenantModification", mappedBy="avenant", cascade={"remove"})
     */
    protected Collection $modifications;

    /**
     * Nom du fichier stocké (l'emplacement du dossier est dans la configuration)
     *
     * @var string
     * @ORM\Column(type="string")
     */
    protected string $filename;

    /**
     * Commentaire de l'utilisateur
     *
     * @var string Commentaire
     * @ORM\Column(type="string")
     */
    protected string $comment;

    /**
     * Status
     *
     * @var int ID
     * @ORM\Column(type="integer", options={"default": "100"})
     */
    protected int $status;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->modifications = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId(): ?int
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

    public function getStatusLabel(): string
    {
        return self::getStatusText($this->getStatus());
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    public function setModifications(Collection $modifications): self
    {
        $this->modifications = $modifications;
        return $this;
    }

    public function getResourceId()
    {
        return self::class;
    }

    /**
     * @return string[]
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_DRAFT  => 'Brouillon',
            self::STATUS_ACTIVE => 'Effectif',
        ];
    }

    /**
     * @param int $status
     * @return string
     */
    public function getStatusText(int $status): string
    {
        if (!array_key_exists($status, self::getStatusList())) {
            return "Inconnue";
        }
        return self::getStatusList()[$status];
    }
}
