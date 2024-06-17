<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 03/12/15 14:17
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;
use UnicaenSignature\Entity\Db\SignatureFlow;

/**
 * Class TypeDocument
 * @package Oscar\Entity
 * @ORM\Entity
 */
class TypeDocument implements ITrackable
{
    use TraitTrackable;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeCentaure;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default":0}, name="isdefault")
     */
    private bool $default = false;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="UnicaenSignature\Entity\Db\SignatureFlow")
     */
    private null|SignatureFlow $signatureFlow;


    /**
     * @return bool
     */
    public function getDefault(): bool
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->getDefault();
    }

    /**
     * @return mixed
     */
    public function getSignatureFlow(): ?SignatureFlow
    {
        return $this->signatureFlow;
    }

    /**
     * @param mixed $signatureFlow
     */
    public function setSignatureFlow(?SignatureFlow $signatureFlow): self
    {
        $this->signatureFlow = $signatureFlow;
        return $this;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default): self
    {
        $this->default = $default;
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
    public function getCodeCentaure()
    {
        return $this->codeCentaure;
    }

    /**
     * @param string $codeCentaure
     */
    public function setCodeCentaure($codeCentaure)
    {
        $this->codeCentaure = $codeCentaure;

        return $this;
    }

    function __toString()
    {
        return $this->getLabel();
    }

    function toJson()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
        ];
    }

    function toArray()
    {
        return array(
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'signatureflow_id' => $this->getSignatureFlow() ? $this->getSignatureFlow()->getId() : null,
            'default' => $this->isDefault()
        );
    }
}