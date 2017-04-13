<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie_privilege")
 */
class CategoriePrivilege
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=150, unique=false, nullable=false)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="libelle", type="string", length=200, unique=false, nullable=false)
     */
    private $libelle;

    /**
     * @var int
     * @ORM\Column(name="ordre", type="integer", unique=false, nullable=true)
     */
    private $ordre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Privilege", mappedBy="categorie")
     */
    private $privilege;


    public function toArray(){
        return [
            'id' => $this->getId(),
            'libelle' => $this->getLibelle(),
            'code' => $this->getCode(),
            'order' => $this->getOrdre()
        ];
    }



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->privilege = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * Set code
     *
     * @param string $code
     *
     * @return Privilege
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }



    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }



    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Privilege
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }



    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }



    /**
     *
     * @return integer
     */
    function getOrdre()
    {
        return $this->ordre;
    }



    /**
     *
     * @param integer $ordre
     *
     * @return \Application\Entity\Db\Privilege
     */
    function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Add privilege
     *
     * @param Privilege $privilege
     *
     * @return Privilege
     */
    public function addPrivilege(Privilege $privilege)
    {
        $this->privilege[] = $privilege;

        return $this;
    }



    /**
     * Remove privilege
     *
     * @param Privilege $privilege
     */
    public function removePrivilege(Privilege $privilege)
    {
        $this->privilege->removeElement($privilege);
    }



    /**
     * Get privilege
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }



    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }
}
