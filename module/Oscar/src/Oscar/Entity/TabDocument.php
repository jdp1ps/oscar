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
    private $label;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="boolean", options={"default":0}, name="isdefault")
     */
    private bool $default = false;

    /**
     * @ORM\OneToMany(targetEntity=TabsDocumentsRoles::class, mappedBy="tabDocument", cascade={"persist","remove"})
     */
    private Collection $tabsDocumentsRoles;

    public function __construct()
    {
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
     * @return ?string
     */
    public function getLabel(): ?string
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
    public function isDefault(): bool
    {
        return $this->getDefault();
    }

    /**
     * @return string
     */
    public function getDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault(bool $default): self
    {
        $this->default = $default;
        return $this;
    }



    function __toString()
    {
        return $this->getLabel();
    }

    /**
     * Accès au document
     * Retourne un booléen en fonction de l'autorisation d'accès aux documents
     * en comparant si autorisation d'accès supérieur à 0 (lecture au minima) et si présent dans le tableau fourni en paramètre
     *
     * @param array $roleIds
     * @return bool
     */
    public function hasAccess(array $roleIds)
    {
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole) {
            if ($tabDocumentRole->getAccess() > 0 && in_array($tabDocumentRole->getRole()->getRoleId(), $roleIds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Management du doc
     * Retourne un booléen en fonction de l'autorisation d'accès aux documents
     * en comparant si autorisation d'accès == 2 (lecture + écriture) et si présent dans le tableau fourni en paramètre
     *
     * @param array $roleIds
     * @return bool
     */
    public function isManage(array $roleIds)
    {
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole) {
            if( in_array( $tabDocumentRole->getRole()->getRoleId(), $roleIds) && $tabDocumentRole->getAccess() == 2 ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function toJson()
    {
        $roles = [];
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole) {
            if ($tabDocumentRole->getAccess() > 0) {
                $roles [] = $tabDocumentRole->getRole()->getRoleId();
            }
        }
        return
            [
                'id' => $this->getId(),
                'label' => $this->getLabel(),
                'description' => $this->getDescription(),
                'manage' => false,
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

    public function getRolesAccess() :array
    {
        $rolesIds = [
            'read' => [],
            'write' => []
        ];
        foreach ($this->getTabsDocumentsRoles() as $tabsDocumentsRole) {
            if( $tabsDocumentsRole->getAccess() > 0 ){
                $rolesIds['read'][] = $tabsDocumentsRole->getRole()->getRoleId();
            }
            if( $tabsDocumentsRole->getAccess() > 1 ){
                $rolesIds['write'][] = $tabsDocumentsRole->getRole()->getRoleId();
            }
        }
        return $rolesIds;
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
    public function resetTabDocumentRole(): self
    {
        $this->tabsDocumentsRoles = new ArrayCollection();
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $roles = [];
        foreach ($this->getTabsDocumentsRoles() as $tabDocumentRole) {
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
