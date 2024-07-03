<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\Country3166Repository")
 */
class Country3166
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $fr;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $en;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     */
    private $alpha2;


    /**
     * @var string
     * @ORM\Column(type="string", length=3)
     */
    private $alpha3;

    /**
     * @var string
     * @ORM\Column(type="integer")
     */
    private $numeric;

    public function asArray()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
        ];
    }

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
    public function getFr(): string
    {
        return $this->fr;
    }

    /**
     * @param string $fr
     */
    public function setFr(string $fr): self
    {
        $this->fr = $fr;
        return $this;
    }

    /**
     * @return string
     */
    public function getEn(): string
    {
        return $this->en;
    }

    /**
     * @param string $en
     */
    public function setEn(string $en): self
    {
        $this->en = $en;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlpha2(): string
    {
        return $this->alpha2;
    }

    /**
     * @param string $alpha2
     */
    public function setAlpha2(string $alpha2): self
    {
        $this->alpha2 = $alpha2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlpha3(): string
    {
        return $this->alpha3;
    }

    /**
     * @param string $alpha3
     */
    public function setAlpha3(string $alpha3): self
    {
        $this->alpha3 = $alpha3;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumeric(): string
    {
        return $this->numeric;
    }

    /**
     * @param string $numeric
     */
    public function setNumeric(string $numeric): self
    {
        $this->numeric = $numeric;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->getFr();
    }


    function __toString()
    {
        return $this->getLabel();
    }
}