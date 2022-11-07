<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-06-17 15:35
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @MappedSuperclass
 */
abstract class AbstractVersionnedDocument
{
    ////////////////////////////////////////////////////////////////////////////
    const STATUS_DELETE = 5;
    const STATUS_PUBLISH = 1;
    const STATUS_DRAFT = 3;


    /**
     * Personne à l'origine du téléversement.
     *
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $person;

    /**
     * Date de dépôt du fichier.
     *
     * @var datetime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdoad;

    /**
     * Emplacement (depuis le dossier de dépôt)
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $path;

    /**
     * Informations.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $information;

    /**
     * @var string Le type Mime du fichier.
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileTypeMime;

    /**
     * @var integer La taille du fichier
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fileSize;

    /**
     * @var string Nom du fichier (usage), le nom du fichier envoyé par l'utilisateur à l'origine.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileName;

    /**
     * @var integer Version du fichier
     * @ORM\Column(type="integer", nullable=true)
     */
    private $version = 1;

    /**
     * @var statut du document
     * @ORM\Column(type="integer", nullable=false, options={"default":1})
     */
    private $status = self::STATUS_PUBLISH;

    /**
     * AbstractVersionnedDocument constructor.
     * @param datetime $dateUpdoad
     */
    public function __construct()
    {
        $this->dateUpdoad = new \DateTime();
    }

    /**
     * @return mixed
     */
    abstract public function getId();



    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $person
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateUpdoad()
    {
        return $this->dateUpdoad;
    }

    /**
     * @param datetime $dateUpdoad
     */
    public function setDateUpdoad($dateUpdoad)
    {
        $this->dateUpdoad = $dateUpdoad;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param string $information
     */
    public function setInformation($information)
    {
        $this->information = $information;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileTypeMime()
    {
        return $this->fileTypeMime;
    }

    /**
     * @param string $fileTypeMime
     */
    public function setFileTypeMime($fileTypeMime)
    {
        $this->fileTypeMime = $fileTypeMime;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return statut
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param statut $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    //
    // Calculated getter
    //
    ////////////////////////////////////////////////////////////////////////////
    public function getExtension()
    {
        return preg_replace("/.*\\.(.*)/", "$1", $this->getFileName());
    }


    public function generatePath()
    {
        $slugify = new Slugify();
        return sprintf("oscar-%s-%s", $this->getVersion(), $slugify->slugify($this->getFileName()));
    }



    ////////////////////////////////////////////////////////////////////////////
    //
    // METHODS
    //
    ////////////////////////////////////////////////////////////////////////////
    function __toString()
    {
        return $this->getFileName();
    }


}
