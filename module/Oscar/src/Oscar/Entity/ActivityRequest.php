<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Import\Data\DataExtractorDate;
use Oscar\Service\ActivityTypeService;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Demande d'activité
 *
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ActivityRequest
{
    use TraitTrackable;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function toJson(){
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'statut' => $this->getStatus(),
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
        ];
    }

}
