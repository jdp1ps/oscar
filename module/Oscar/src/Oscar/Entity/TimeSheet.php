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

    const STATUS_TOVALIDATE_SCI = 6;
    const STATUS_TOVALIDATE_ADMIN = 7;

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
                self::STATUS_TOVALIDATE_SCI => 'Soumis à validation scientifique',
                self::STATUS_TOVALIDATE_ADMIN => 'Soumis à validation administrative',
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
                self::STATUS_TOVALIDATE_SCI => 'sendsci',
                self::STATUS_TOVALIDATE_ADMIN => 'sendadmin',
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

    /**
     * Identifiant du créneau dans le ficheir ICS.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $icsUid;


    /**
     * Identifiant du calendrier ICS.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $icsFileUid;

    /**
     * Nom du calendrier ICS.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $icsFileName;

    /**
     * @var \DateTime Date de l'import de l'ics
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $icsFileDateAdded;

    //////////////////////////////////////////////////// VALIDATION SCIENTIFIQUE

    ////// VALIDATION
    /**
     * @var string Personne ayant fait la validation scientifique.
     * @ORM\Column(type="string", nullable=true)
     */
    private $validatedSciBy;

    /**
     * @var int ID de la personne ayant fait la validation scientifique.
     * @ORM\Column(type="integer", nullable=true)
     */
    private $validatedSciById;

    /**
     * @var \DateTime Date de la validation scientifique.
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $validatedSciAt;

    /**
     * @var string Personne ayant fait la validation administrative.
     * @ORM\Column(type="string", nullable=true)
     */
    private $validatedAdminBy;

    /**
     * @var int ID de la personne ayant fait la validation administrative.
     * @ORM\Column(type="integer", nullable=true)
     */
    private $validatedAdminById;

    /**
     * @var \DateTime Date de la validation administrative.
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $validatedAdminAt;

    ////// REFUS
    /**
     * @var string Personne ayant refusé la validation scientifique.
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectedSciBy;

    /**
     * @var int ID de la personne ayant refusé la validation scientifique.
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rejectedSciById;

    /**
     * @var \DateTime Date du refuss de la validation scientifique.
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $rejectedSciAt;

    /**
     * @var string Commentaire laisé par la personne ayant refusé la validation scientifique.
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedSciComment;

    ////// REFUS
    /**
     * @var string Personne ayant refusé la validation administrative.
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectedAdminBy;

    /**
     * @var int ID de la personne ayant refusé la validation administrative.
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rejectedAdminById;

    /**
     * @var \DateTime Date du refuss de la validation administrative.
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $rejectedAdminAt;

    /**
     * @var string Commentaire laisé par la personne ayant refusé la validation administrative.
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectedAdminComment;

    ////////////////////////////////////////////////////////////////////////////


    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    private $sendBy;

    ////////////////////////////////////////////////////////////////////////////
    public function __construct()
    {
        $this->setDateCreated(new \DateTime())
            ->setDateUpdated(new \DateTime())
            ->setStatus(self::STATUS_DRAFT);
    }

    public function toJson(){
        $activityId = null;
        $activityLabel = null;
        $workpackageId = null;
        $workpackageLabel = null;
        $workpackageCode = null;


        if( $this->getWorkpackage() ){
            $workpackageId = $this->getWorkpackage()->getId();
            $workpackageLabel = (string)$this->getWorkpackage();
            $workpackageCode = $this->getWorkpackage()->getCode();
            $activityId = $this->getWorkpackage()->getActivity()->getId();
            $activityLabel = (string)$this->getWorkpackage()->getActivity();
        }
        // Pas de lot, mais une activité ?
        else if ( $this->getActivity() ){
            $activityId = $this->getActivity()->getId();
            $activityLabel = (string)$this->getActivity();
        }


        return [
            'id' => $this->getId(),
            'activity_id' => $activityId,
            'activity_label' => $activityLabel,
            'workpackage_id' => $workpackageId,
            'workpackage_code' => $workpackageCode,
            'workpackage_label' => $workpackageLabel,

            'icsuid'=> $this->getIcsUid(),
            'icsfileuid'=> $this->getIcsFileUid(),
            'icsfilename'=> $this->getIcsFileName(),
            'icsfiledateadded'=> $this->getIcsFileDateAdded() ? $this->getIcsFileDateAdded()->format('c') : null,

            'label' => $this->getLabel(),
            'description' => $this->getComment(),
            'start' => $this->getDateFrom()->format('c'),
            'end' => $this->getDateTo()->format('c'),
            'status' => self::getStatusText()[$this->getStatus()],
            'owner' => $this->getPerson()->getDisplayName(),
            'owner_id' => $this->getPerson()->getId(),
            'validatedSciBy' => $this->getValidatedSciBy(),
            'validatedSciAt' => $this->getValidatedSciAt(),
            'validatedAdminBy' => $this->getValidatedAdminBy(),
            'validatedAdminAt' => $this->getValidatedAdminAt(),
            'rejectedSciAt' => $this->getRejectedSciAt(),
            'rejectedSciComment' => $this->getRejectedSciComment(),
            'rejectedSciBy' => $this->getRejectedSciBy(),
            'rejectedAdminAt' => $this->getRejectedAdminAt(),
            'rejectedAdminComment' => $this->getRejectedAdminComment(),
            'rejectedAdminBy' => $this->getRejectedAdminBy(),
        ];
    }

    /**
     * @return string
     */
    public function getIcsUid(): string
    {
        return $this->icsUid;
    }

    /**
     * @param string $icsUid
     */
    public function setIcsUid($icsUid)
    {
        $this->icsUid = $icsUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcsFileUid(): string
    {
        return $this->icsFileUid;
    }

    /**
     * @param string $icsFileUid
     */
    public function setIcsFileUid($icsFileUid)
    {
        $this->icsFileUid = $icsFileUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcsFileName(): string
    {
        return $this->icsFileName;
    }

    /**
     * @param string $icsFileName
     */
    public function setIcsFileName($icsFileName)
    {
        $this->icsFileName = $icsFileName;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIcsFileDateAdded()
    {
        return $this->icsFileDateAdded;
    }

    /**
     * @param string $icsFileDateAdded
     */
    public function setIcsFileDateAdded($icsFileDateAdded)
    {
        $this->icsFileDateAdded = $icsFileDateAdded;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidatedSciBy()
    {
        return $this->validatedSciBy;
    }

    /**
     * @param string $validatedSciBy
     */
    public function setValidatedSciBy($validatedSciBy)
    {
        $this->validatedSciBy = $validatedSciBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getValidatedSciById()
    {
        return $this->validatedSciById;
    }

    /**
     * @param int $validatedSciById
     */
    public function setValidatedSciById($validatedSciById)
    {
        $this->validatedSciById = $validatedSciById;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedSciAt()
    {
        return $this->validatedSciAt;
    }

    /**
     * @param \DateTime $validatedSciAt
     */
    public function setValidatedSciAt($validatedSciAt)
    {
        $this->validatedSciAt = $validatedSciAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidatedAdminBy()
    {
        return $this->validatedAdminBy;
    }

    /**
     * @param string $validatedAdminBy
     */
    public function setValidatedAdminBy($validatedAdminBy)
    {
        $this->validatedAdminBy = $validatedAdminBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getValidatedAdminById()
    {
        return $this->validatedAdminById;
    }

    /**
     * @param int $validatedAdminById
     */
    public function setValidatedAdminById($validatedAdminById)
    {
        $this->validatedAdminById = $validatedAdminById;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedAdminAt()
    {
        return $this->validatedAdminAt;
    }

    /**
     * @param \DateTime $validatedAdminAt
     */
    public function setValidatedAdminAt($validatedAdminAt)
    {
        $this->validatedAdminAt = $validatedAdminAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedSciBy()
    {
        return $this->rejectedSciBy;
    }

    /**
     * @param string $rejectedSciBy
     */
    public function setRejectedSciBy($rejectedSciBy)
    {
        $this->rejectedSciBy = $rejectedSciBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getRejectedSciById()
    {
        return $this->rejectedSciById;
    }

    /**
     * @param int $rejectedSciById
     */
    public function setRejectedSciById($rejectedSciById)
    {
        $this->rejectedSciById = $rejectedSciById;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedSciAt()
    {
        return $this->rejectedSciAt;
    }

    /**
     * @param \DateTime $rejectedSciAt
     * @return TimeSheet
     */
    public function setRejectedSciAt($rejectedSciAt)
    {
        $this->rejectedSciAt = $rejectedSciAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedSciComment()
    {
        return $this->rejectedSciComment;
    }

    /**
     * @param string $rejectedSciComment
     */
    public function setRejectedSciComment($rejectedSciComment)
    {
        $this->rejectedSciComment = $rejectedSciComment;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedAdminBy()
    {
        return $this->rejectedAdminBy;
    }

    /**
     * @param string $rejectedAdminBy
     */
    public function setRejectedAdminBy($rejectedAdminBy)
    {
        $this->rejectedAdminBy = $rejectedAdminBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getRejectedAdminById()
    {
        return $this->rejectedAdminById;
    }

    /**
     * @param int $rejectedAdminById
     */
    public function setRejectedAdminById($rejectedAdminById)
    {
        $this->rejectedAdminById = $rejectedAdminById;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedAdminAt()
    {
        return $this->rejectedAdminAt;
    }

    /**
     * @param \DateTime $rejectedAdminAt
     */
    public function setRejectedAdminAt($rejectedAdminAt)
    {
        $this->rejectedAdminAt = $rejectedAdminAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedAdminComment()
    {
        return $this->rejectedAdminComment;
    }

    /**
     * @param string $rejectedAdminComment
     */
    public function setRejectedAdminComment($rejectedAdminComment)
    {
        $this->rejectedAdminComment = $rejectedAdminComment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSendBy()
    {
        return $this->sendBy;
    }

    /**
     * @param $sendBy
     * @return $this
     */
    public function setSendBy($sendBy)
    {
        $this->sendBy = $sendBy;

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
        if( $this->getWorkpackage() ){
            return (string)$this->getWorkpackage();
        }
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
     * @return WorkPackage
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
    ///
    ///
    ///
    public function isWaitingValidation(){
        return $this->getStatus() == self::STATUS_TOVALIDATE;
    }
    public function isWaitingValidationAdmin(){
        return $this->getValidatedAdminAt() == null && $this->getStatus() == self::STATUS_TOVALIDATE;
    }

    public function isWaitingValidationSci(){
        return $this->getValidatedSciAt() == null && $this->getStatus() == self::STATUS_TOVALIDATE;
    }

}