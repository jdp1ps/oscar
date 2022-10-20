<?php

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relation entre les onglets des documents et les roles
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\TabsDocumentsRolesRepository")
 */
class TabsDocumentsRoles implements ITrackable
{
    use TraitTrackable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Gestion des droits sur les documents dans les onglets consulter, téléverser
    * @ORM\Column(type="integer")
    */
    private ?int $access;

    /**
     * Relation avec les onglets documents
     * @ORM\ManyToOne(targetEntity=TabDocument::class, inversedBy="tabsDocumentsRoles")
     */
    private ?TabDocument $tabDocument;

    /**
     * Relation avec les rôles associés aux onglets
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="tabsDocumentsRoles")
     */
    private ?Role $role;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getAccess(): ?int
    {
        return $this->access;
    }

    /**
     * @param int|null $access
     */
    public function setAccess(?int $access): void
    {
        $this->access = $access;
    }

    /**
     * @return TabDocument|null
     */
    public function getTabDocument(): ?TabDocument
    {
        return $this->tabDocument;
    }

    /**
     * @param TabDocument|null $tabDocument
     */
    public function setTabDocument(?TabDocument $tabDocument): void
    {
        $this->tabDocument = $tabDocument;
    }

    /**
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     */
    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Matrice droit : " . $this->getAccess()." Role : ".$this->getRole()->getRoleId(). " Onglet : " . $this->getTabDocument()->getLabel();
    }

    /**
     * Retourne TabsDocumentsRoles sous forme de tableau associatif clef/valeurs
     * @return array
     */
    public function toArray():array{
        return [
            'id' => $this->getId(),
            'tabDocumentLabel' => $this->getTabDocument()->getLabel(),
            'tabDocumentId' => $this->getTabDocument()->getId(),
            'role_Label' => $this->getRole()->getRoleId(),
            'role_id' => $this->getRole()->getId()
        ];
    }

}
