<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-04-25 11:13
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectGrant, correspond aux conventions (Contrats).
 *
 * @package Oscar\Entity
 * @ORM\Entity
 */
class TimeSheet implements ITrackable
{
    use TraitTrackable;

    // Formats pour la déclaration des feuilles de temps
    const TIMESHEET_FORMAT_NONE = 'none';
    const TIMESHEET_FORMAT_DAY = 'day';
    const TIMESHEET_FORMAT_WEEK = 'week';
    const TIMESHEET_FORMAT_MONTH = 'month';

    const STATUS_INFO = 9;

    /**
     * @return array
     */
    public static function getFormatsSelect(){
        static $format_labels;
        if( $format_labels === null ){
            $format_labels = [
                self::TIMESHEET_FORMAT_NONE => 'Pas de déclaration',
                self::TIMESHEET_FORMAT_DAY => 'Quotidienne',
                self::TIMESHEET_FORMAT_WEEK => 'Hebdomadaire',
                self::TIMESHEET_FORMAT_MONTH => 'Mensuelle',
            ];
        }
        return $format_labels;
    }

    /**
     * @return array
     */
    public static function getStatusSelect(){
        static $status_labels;
        if( $status_labels === null ){
            $status_labels = [
                self::STATUS_DRAFT => 'Brouillon',
                self::STATUS_TOVALIDATE => 'Soumis à validation',
                self::STATUS_ACTIVE => 'Validée',
                self::STATUS_CONFLICT => 'Conflit',
            ];
        }
        return $status_labels;
    }

    public static function getStatusText(){
        static $status_text;
        if( $status_text === null ){
            $status_text = [
                self::STATUS_DRAFT => 'draft',
                self::STATUS_TOVALIDATE => 'send',
                self::STATUS_ACTIVE => 'valid',
                self::STATUS_CONFLICT => 'reject',
                self::STATUS_INFO => 'info',
            ];
        }
        return $status_text;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getStatusLabel( $key ){
        return self::getStatusSelect()[$key];
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getFormatLabel( $key ){
       return self::getFormatsSelect()[$key];
    }

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateFrom;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateTo;

    /**
     * @var \DateTime
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     * @ORM\Column(type="text", nullable=true)
     */
    private $label;

    /**
     * @var WorkPackage
     * @ORM\ManyToOne(targetEntity="WorkPackage", inversedBy="timesheets")
     */
    private $workpackage;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity")
     */
    private $activity;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="timesheets")
     */
    private $person;


    //////////////////////////////////////////////////// VALIDATION SCIENTIFIQUE

    ////// VALIDATION
    /** @var string Personne ayant fait la validation scientifique. */
    private $validateSciBy;

    /** @var int ID de la personne ayant fait la validation scientifique. */
    private $validateSciById;

    /** @var \DateTime Date de la validation scientifique. */
    private $validateSciAt;

    ////// REFUS
    /** @var string Personne ayant refusé la validation scientifique. */
    private $rejectedSciBy;

    /** @var int ID de la personne ayant refusé la validation scientifique. */
    private $rejectedSciById;

    /** @var \DateTime Date du refuss de la validation scientifique. */
    private $rejectedSciAt;

    /** @var string Commentaire laisé par la personne ayant refusé la validation scientifique. */
    private $rejectedSciComment;

    ////////////////////////////////////////////////////////////////////////////




    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $sendBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $validatedBy;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectedBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $rejectedAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedComment;





    ////////////////////////////////////////////////////////////////////////////
    public function __construct()
    {
        $this->setDateCreated(new \DateTime())
            ->setDateUpdated(new \DateTime())
            ->setStatus(self::STATUS_DRAFT);
    }

    public function toJson(){
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getComment(),
            'start' => $this->getDateFrom()->format('c'),
            'end' => $this->getDateTo()->format('c'),
            'status' => self::getStatusText()[$this->getStatus()],
            'owner' => $this->getPerson()->getDisplayName(),
            'owner_id' => $this->getPerson()->getId(),
            'rejectedAt' => $this->getRejectedAt(),
            'rejectedComment' => $this->getRejectedComment(),
        ];
    }

    /**
     * @return mixed
     */
    public function getSendBy()
    {
        return $this->sendBy;
    }

    /**
     * @param mixed $sendBy
     */
    public function setSendBy($sendBy)
    {
        $this->sendBy = $sendBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidatedBy()
    {
        return $this->validatedBy;
    }

    /**
     * @param mixed $validatedBy
     */
    public function setValidatedBy($validatedBy)
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRejectedBy()
    {
        return $this->rejectedBy;
    }

    /**
     * @param mixed $rejectedBy
     */
    public function setRejectedBy($rejectedBy)
    {
        $this->rejectedBy = $rejectedBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedAt()
    {
        return $this->rejectedAt;
    }

    /**
     * @param \DateTime $rejectedAt
     */
    public function setRejectedAt($rejectedAt)
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedComment()
    {
        return $this->rejectedComment;
    }

    /**
     * @param string $rejectedComment
     */
    public function setRejectedComment($rejectedComment)
    {
        $this->rejectedComment = $rejectedComment;
        return $this;
    }



    public function getHours(){
        return ($this->getDateTo()->getTimestamp() - $this->getDateFrom()->getTimestamp())/60/60;
    }

    /**
     * @return \DateTime
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param \DateTime $label
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * @return Activity
     */
    public function getWorkpackage()
    {
        return $this->workpackage;
    }

    /**
     * @param WorkPackage $workpackage
     */
    public function setWorkpackage($workpackage)
    {
        $this->workpackage = $workpackage;
        if( $workpackage ){
            $this->setActivity($workpackage->getActivity());
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
     * @param Activity $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Activity $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @param \DateTime $validatedAt
     */
    public function setValidatedAt($validatedAt)
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime $dateTo
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DateTime $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }



    ////////////////////////////////////////////////////////////////////////////

}