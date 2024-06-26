<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ActivityType
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityTypeRepository")
 */
class ActivityType implements ITrackable
{
    use TraitTrackable;

    const NATURE_RV = 'Recherche et valorisation';
    const NATURE_JU = 'Juridique';
    const NATURE_AD = 'Administratif';
    const NATURE_FI = 'Financier';
    const NATURE_PR = 'Production';

    /**
     * Retourne les différentes natures possibles.
     * @return string[]
     */
    public static function getNatures()
    {
        static $natures;
        if ($natures === null) {
            $natures = [
                self::NATURE_RV,
                self::NATURE_PR,
                self::NATURE_JU,
                self::NATURE_AD,
                self::NATURE_FI,
            ];
        }
        return $natures;
    }

    /**
     * Intitulé du type
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label;

    /**
     * Description.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nature = self::NATURE_RV;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private int $lft = 1;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private int $rgt = 2;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $centaureId;

    /**
     * @return integer
     */
    public function getLft() :int
    {
        return $this->lft;
    }


    public function setLft(int $lft)
    {
        $this->lft = $lft;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRgt() :int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt)
    {
        $this->rgt = $rgt;
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
     * @return mixed
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * @param mixed $nature
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    public function getNatureStr()
    {
        return isset(self::getNatures()[$this->getNature()]) ?
            self::getNatures()[$this->getNature()] :
            '';
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

    function __toString()
    {
        return $this->getLabel();
    }

    function trac()
    {
        return sprintf("%s:%s (%s,%s)", $this->getId(), $this->getLabel(), $this->getLft(), $this->getRgt());
    }

    ////////////////////////////////////////////////////////////////////////////
    public function hasChild()
    {
        return $this->getRgt() - $this->getLft() !== 1;
    }


}
