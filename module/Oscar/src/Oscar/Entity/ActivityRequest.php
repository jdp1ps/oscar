<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Demande d'activité
 *
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityRequestRepository")
 */
class ActivityRequest
{
    use TraitTrackable;


    const STATUS_DRAFT = 1;
    const STATUS_SEND = 2;
    const STATUS_VALID = 5;
    const STATUS_REJECT = 7;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label = '';

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = '';

    /**
     * Montant de la subvension.
     *
     * @var integer
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount = 0;

    /**
     * Date de début de la subvension.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateStart;

    /**
     * Date de fin de la subvension.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateEnd;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Organization")
     */
    private $organisation;

    /**
     * Liste des documents.
     *
     * @var String
     * @ORM\Column(type="array", nullable=true)
     */
    private $files = null;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityRequestFollow", mappedBy="activityRequest", cascade={"remove"})
     */
    private $follows;

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param datetime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @return String
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function getFilesArray()
    {
        return $this->getFiles();
    }

    public function getFileInfosByFile( $file ){
        foreach ($this->getFilesArray() as $f) {
            if( $f['file'] == $file ){
                return $f;
            }
        }
        return null;
    }

    /**
     * @param String $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organization $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFollows()
    {
        return $this->follows;
    }

    /**
     * @param ArrayCollection $follows
     */
    public function setFollows($follows)
    {
        $this->follows = $follows;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->files = [];
        $this->setDateCreated(new \DateTime());
        $this->follows = new ArrayCollection();
    }

    public function getStatutText(){
        static $statusText;
        if( $statusText === null ){
            $statusText = [
                self::STATUS_DRAFT => "draft",
                self::STATUS_SEND => "send",
                self::STATUS_VALID => "valid",
                self::STATUS_REJECT => "reject",
            ];
        }
        return $statusText[$this->getStatus()];
    }

    public function toJson(){

        $follows = [];
        /** @var ActivityRequestFollow $f */
        foreach ($this->getFollows() as $f) {
            $follows[] = $f->toJson();
        }
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'statut' => $this->getStatus(),
            'statutText' => $this->getStatutText(),
            'amount' => $this->getAmount(),
            'description' => $this->getDescription(),
            'files' => $this->getFiles(),
            'dateStart' => $this->getDateStart() ? $this->getDateStart()->format('Y-m-d') : '',
            'dateEnd' => $this->getDateEnd() ? $this->getDateEnd()->format('Y-m-d') : '',
            'dateCreated' => $this->getDateCreated()->format("Y-m-d"),
            'requester_id' => $this->getCreatedBy()->getId(),
            'requester' => (string)$this->getCreatedBy(),
            'organisation_id' => $this->getOrganisation() ? $this->getOrganisation()->getId() : null,
            'organisation' => $this->getOrganisation() ? (string)$this->getOrganisation() : null,
            'suivi' => $follows
        ];
    }

}
