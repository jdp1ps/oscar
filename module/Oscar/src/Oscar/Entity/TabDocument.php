<?php

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class TabDocument
 * @package Oscar\Entity
 * @ORM\Entity
 */
class TabDocument
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private string $label;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private string $description;

    /**
     * @ORM\OneToMany(targetEntity=TabsDocumentsRoles::class, mappedBy="tabDocument", cascade={"persist"})
     */
    private ArrayCollection $tabsDocumentsRoles;

    public function __construct(){
        $this->tabsDocumentsRoles = new ArrayCollection();
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

    function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @return array
     */
    public function toJson()
    {
        $roles = [];
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole){
            $roles [] = $tabDocumentRole->getRole()->getRoleId();
        }
        return
            [
                'id' => $this->getId(),
                'label' => $this->getLabel(),
                'description' => $this->getDescription(),
                'roles' => $roles
            ];
    }

    /**
     * @return Collection|TabsDocumentsRoles[]
     */
    public function getTabsDocumentsRoles(): Collection
    {
        return $this->tabsDocumentsRoles;
    }

    public function addTabDocumentRole(TabsDocumentsRoles $tabDocumentRole): self
    {
        if (!$this->tabsDocumentsRoles->contains($tabDocumentRole)) {
            $this->tabsDocumentsRoles[] = $tabDocumentRole;
            $tabDocumentRole->setTabDocument($this);
        }

        return $this;
    }

    public function removeTabDocumentRole(TabsDocumentsRoles $tabDocumentRole): self
    {
        if ($this->tabsDocumentsRoles->contains($tabDocumentRole)) {
            $this->tabsDocumentsRoles->removeElement($tabDocumentRole);
            // set the owning side to null (unless already changed)
            if ($tabDocumentRole->getTabDocument() === $this) {
                $tabDocumentRole->setTabDocument(null);
            }
        }

        return $this;
    }

    /**
     * Reset la collection association vers TabsDocumentsRoles
     *
     * @return $this
     */
    public function resetTabDocumentRole():self
    {
        $this->tabsDocumentsRoles=  new ArrayCollection();
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $roles = [];
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole){
            $roles [] = $tabDocumentRole->getRole()->getRoleId();
        }
        return array(
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'roles' => $roles
        );
    }
}
