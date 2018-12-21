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
class ActivityRequestFollow
{
    use TraitTrackable;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = '';

    /**
     * @var ActivityRequest
     * @ORM\ManyToOne(targetEntity="ActivityRequest", inversedBy="follows")
     * @ORM\JoinColumn(name="activityrequest_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $activityRequest;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ActivityRequest
     */
    public function getActivityRequest()
    {
        return $this->activityRequest;
    }

    /**
     * @param Organization $activityRequest
     */
    public function setActivityRequest($activityRequest)
    {
        $this->activityRequest = $activityRequest;
        return $this;
    }



    public function __construct()
    {

    }

    public function toJson(){
        return [
            'id' => $this->getId(),
            'statut' => $this->getStatus(),
            'description' => $this->getDescription(),
            'activityrequest_id' => $this->getActivityRequest(),
            'datecreated' => $this->getDateCreated()->format('Y-m-d H:i:s'),
            'by' => [
                'gravatar' => md5($this->getCreatedBy()->getEmail()),
                'username' => (string) $this->getCreatedBy(),
            ]
        ];
    }

}
