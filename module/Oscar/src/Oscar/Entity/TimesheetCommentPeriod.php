<?php
namespace Oscar\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Exception\OscarException;
use Oscar\Utils\DateTimeUtil;


/**
 * @package Oscar\Entity
 * @ORM\Entity
 */
class TimesheetCommentPeriod
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
    private $object;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $objectGroup;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $object_id;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau activité
    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $declarer;
    /**
     * Mois
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $month;
    /**
     * Année
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $year;

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
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $object
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectGroup()
    {
        return $this->objectGroup;
    }

    /**
     * @param string $objectGroup
     */
    public function setObjectGroup($objectGroup)
    {
        $this->objectGroup = $objectGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @param string $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeclarer()
    {
        return $this->declarer;
    }

    /**
     * @param mixed $declarer
     */
    public function setDeclarer($declarer)
    {
        $this->declarer = $declarer;
        return $this;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param int $month
     */
    public function setMonth(int $month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year)
    {
        $this->year = $year;
        return $this;
    }


}