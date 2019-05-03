<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-06-17 13:38
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class AdministrativeDocument extends AbstractVersionnedDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    public function getId()
    {
        return $this->id;
    }

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="AdministrativeDocumentSection", inversedBy="documents")
     */
    private $section = null;

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }
}