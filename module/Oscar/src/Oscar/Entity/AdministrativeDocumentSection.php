<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 23/04/19
 * Time: 14:29
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="Oscar\Entity\AdministrativeDocumentSectionRepository")
 */
class AdministrativeDocumentSection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * AdministrativeDocumentSection constructor.
     * @param $id
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Intitulé de la section
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $label;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AdministrativeDocument", mappedBy="section")
     */
    protected $documents;

    /**
     * Description.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;




    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param ArrayCollection $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

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
    public function setLabel(string $label)
    {
        $this->label = $label;
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
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function __toString()
    {
        return $this->getLabel();
    }


}