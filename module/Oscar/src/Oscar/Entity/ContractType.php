<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18/06/15 11:32
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ContractType
 * @package Oscar\Entity
 * @ORM\Entity(repositoryClass="ContractTypeRepository")
 */
class ContractType extends AbstractTreeData
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $label;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $description;


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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;

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

    public function __toString()
    {
        return sprintf('[%s] %s', $this->getCode(), $this->getLabel());
    }



}