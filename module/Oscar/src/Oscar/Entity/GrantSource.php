<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:10
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GrantSource
 *
 * Permet de référencer les différentes sources de financement.
 *
 * @package Oscar\Entity
 * @ORM\Entity
 */
class GrantSource
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * ID Centaure.
     *
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $centaureId;

    /**
     * Label
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    /**
     * Description
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * Logo
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $logo;

    /**
     * Informations
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $informations;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCentaureId()
    {
        return $this->centaureId;
    }

    /**
     * @param string $centaureId
     */
    public function setCentaureId($centaureId)
    {
        $this->centaureId = $centaureId;

        return $this;
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
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @return string
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * @param string $informations
     */
    public function setInformations($informations)
    {
        $this->informations = $informations;
        return $this;
    }

    public function isNew()
    {
        return $this->getId() !== null || $this->getId() < 1;
    }

    public function asArray()
    {
        return array(
            'id'    => $this->getId(),
            'label'    => $this->getLabel(),
            'description'    => $this->getDescription(),
            'informations'    => $this->getInformations(),
        );
    }

    public function __toString(){
        return sprintf('%s, %s', $this->getLabel(), $this->getDescription());
    }


}