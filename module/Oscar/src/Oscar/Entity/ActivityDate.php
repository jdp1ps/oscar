<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 16:15
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dates des Activités (Jalons)
 *
 * @ORM\Entity
 */
class ActivityDate implements ITrackable
{
    use TraitTrackable;

    /**
     * Données (la date)
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateStart;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="DateType")
     */
    private $type;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="milestones")
     */
    private $activity;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Test
     * @return array
     */
    public function getRecursivity(){
        if( $this->getType() ){
            return $this->getType()->getRecursivity();
        }
        return [];
    }

    /**
     * @return array
     *
     * Retourne les dates de notification du jalon.
     */
    public function getRecursivityDate(){
        $dates = [];
        foreach ( $this->getRecursivity() as $days ){
            $date = new \DateTime($this->getDateStart()->format('Y-m-d'));
            $interval = new \DateInterval('P'.$days.'D');
            $interval->invert = 1;
            $date->add($interval);
            $dates[] = $date;
        }
        return $dates;
    }

    /**
     * @return datetime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return DateType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Activ
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    function __toString()
    {
        return $this->getDateStart()->format('d M Y').' ('.$this->getType().')';
    }


}