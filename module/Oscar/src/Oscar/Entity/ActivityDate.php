<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 16:15
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Dates des Activités (Jalons)
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityDateRepository")
 */
class ActivityDate implements ITrackable
{
    use TraitTrackable;

    const PROGRESSION_TODO = 'todo';
    const PROGRESSION_VALID = 'valid';
    const PROGRESSION_UNVALID = 'unvalid';
    const PROGRESSION_CANCEL = 'cancel';
    const PROGRESSION_REFUSED = 'refused';
    const PROGRESSION_INPROGRESS = 'inprogress';


    // Jalon terminé
    const VALUE_TODO = 0;
    const VALUE_INPROGRESS = 50;
    const VALUE_VALIDED = 100;
    const VALUE_CANCELED = 200;
    const VALUE_REFUSED = 400;

    /**
     * @return string[]
     */
    static public function progressLabels() :array
    {
        return [
            self::VALUE_TODO => "A faire",
            self::VALUE_INPROGRESS => "En cours",
            self::VALUE_VALIDED => "Validé",
            self::VALUE_CANCELED => "Annulé",
            self::VALUE_REFUSED => "Refusé"
        ];
    }

    /**
     * @return string[]
     */
    static public function progressCodes() :array
    {
        return [
            self::VALUE_TODO => self::PROGRESSION_TODO,
            self::VALUE_INPROGRESS => self::PROGRESSION_INPROGRESS,
            self::VALUE_VALIDED => self::PROGRESSION_VALID,
            self::VALUE_CANCELED => self::PROGRESSION_CANCEL,
            self::VALUE_REFUSED => self::PROGRESSION_REFUSED
        ];
    }

    static public function progressInfoCode( int $finishedValue ) :string
    {
        $codes = self::progressCodes();
        if( array_key_exists($finishedValue, $codes) ){
            return $codes[$finishedValue];
        }
        return "";
    }

    static public function progressInfoLabel( int $finishedValue ): string
    {
        $states = self::progressLabels();
        if( array_key_exists($finishedValue, $states) ){
            return $states[$finishedValue];
        }
        return "UNKNOW";
    }

    /**
     * Données (la date)
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateStart;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="DateType", inversedBy="milestones")
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
     * Un entier représentant le niveau de complétion (en %)
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $finished;

    /**
     * Chaîne contenant la personne [ID] DisplayName
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $finishedBy;


    /**
     * Date à laquelle le jalon a été complété.
     *
     * @var DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFinish;


    public function finish($value = 100, $date = null)
    {
        $this->finished = $value;
        if ($this->finished >= self::VALUE_VALIDED) {
            $this->dateFinish = $date === null ? new DateTime() : $date;
        }
    }

    /***
     * @return bool
     */
    public function isFinishable() :bool
    {
        if ($this->getType()) {
            return $this->getType()->isFinishable();
        }
        return false;
    }

    public function getFinishState() :?array
    {

        if( $this->isFinishable() ){
            $finishedValueLabel = $this->getFinished() != null ? $this->getFinished() : self::VALUE_TODO;
            return [
                'finish' => $this->getFinished(),
                'finished_label' => self::progressInfoLabel($finishedValueLabel),
                'finished_code' => self::progressInfoCode($finishedValueLabel),
                'finished_by' => $this->getFinishedBy() ? $this->getFinishedBy() : ""
            ];
        }
        return null;
    }

    public function getStateCssClass() :string
    {
        if( $this->isFinishable() ){
            $finishedValueLabel = $this->getFinished() != null ? $this->getFinished() : self::VALUE_TODO;
            if( $this->isLate() ){
                return 'progress-item-late';
            } else {
                return 'progress-item-'.self::progressInfoCode($finishedValueLabel);
            }
        } else {
            if( $this->isToday() ){
                return 'progress-item-today';
            }
            elseif ( $this->isFutur() ){
                return 'progress-item-futur';
            }
            else {
                return 'progress-item-past';
            }
        }
    }



    public function getProgressInfo() :string
    {
        if( $this->isFinishable() ){
            return self::getProgressInfo()[$this->getFinished()];
        }
    }

    /**
     * @return string
     */
    public function getFinishedBy(): ?string
    {
        return $this->finishedBy;
    }

    /**
     * @param string $finishedBy
     */
    public function setFinishedBy(string $finishedBy)
    {
        $this->finishedBy = $finishedBy;
        return $this;
    }


    public function isFinished()
    {
        return $this->getFinished() >= self::VALUE_VALIDED;
    }

    /**
     * Retourne TRUE sie le jalon doit être complété et qu'il est en retard.
     */
    public function isLate( DateTime $now = new DateTime()) :bool
    {
        return $this->isFinishable() && !$this->isFinished() && ($now > $this->getDateStart());
    }

    /**
     * @return integer
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * @param Un $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateFinish()
    {
        return $this->dateFinish;
    }

    /**
     * @param datetime $dateFinish
     */
    public function setDateFinish($dateFinish)
    {
        $this->dateFinish = $dateFinish;

        return $this;
    }

    /**
     * Test
     * @return array
     */
    public function getRecursivity()
    {
        if ($this->getType()) {
            return $this->getType()->getRecursivityArray();
        }
        return [];
    }

    /**
     * @return array
     *
     * Retourne les dates de notification du jalon.
     */
    public function getRecursivityDate()
    {
        $dates = [];
        foreach ($this->getRecursivity() as $days) {
            $date = new DateTime($this->getDateStart()->format('Y-m-d'));
            $interval = new \DateInterval('P' . $days . 'D');
            $interval->invert = 1;
            $date->add($interval);
            $dates[] = $date;
        }
        return $dates;
    }

    /**
     * @return DateTime
     */
    public function getDateStart() : DateTime
    {
        return $this->dateStart;
    }

    public function getDateStartStr($format = 'Y-m-d'): string
    {
        if ($this->getDateStart()) {
            return $this->getDateStart()->format($format);
        }
        return "";
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
     * @param DateType $type
     */
    public function setType($type)
    {
        $this->type = $type;
        if( $type && $type->isFinishable() ){

        }

        return $this;
    }

    /**
     * @return Activity
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
        return $this->getDateStart()->format('d M Y') . ' (' . $this->getType() . ')';
    }

    public function isToday( DateTime $dateRef = new DateTime() ):bool
    {
        return $this->getDateStart()->format('Y-m-d') == $dateRef->format('Y-m-d');
    }

    public function isFutur( DateTime $dateRef = new DateTime() ):bool
    {
        return $this->getDateStart()->format('Y-m-d') > $dateRef->format('Y-m-d');
    }

    public function getLateDays(DateTime $now = new DateTime()): ?int
    {
        if ($this->isFinishable() && $this->isLate()) {
            $interval = $now->diff($this->getDateStart());
            $days = $interval->days;
            if ($days) {
                return $days;
            } else {
                return 0;
            }
        }
    }

    function toArray()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType()->getLabel(),
            'type_id' => $this->getType()->getId(),
            'type_facet' => $this->getType()->getFacet(),
            'comment' => $this->getComment(),
            'dateStart' => $this->getDateStart()
        ];
    }

    function toJson()
    {
        return [
            'id' => $this->getId(),
            'project' => $this->getActivity()->getAcronym(),
            'activity' => $this->getActivity()->getOscarNum(),
            'type' => $this->getType()->getLabel(),
            'dateStart' => $this->getDateStartStr(),
        ];
    }

}
