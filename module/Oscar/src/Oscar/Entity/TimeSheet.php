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
 * @ORM\Entity(repositoryClass="Oscar\Entity\TimesheetRepository")
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
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateFrom;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateTo;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSync;

    /**
     * @var \DateTime
     * @ORM\Column(type="string", nullable=true)
     */
    private $syncId;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $icsFileDateAdded;


    /**
     * @var ValidationPeriod
     * @ORM\ManyToOne(targetEntity="ValidationPeriod", inversedBy="timesheets")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $validationPeriod;

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

    /**
     * @return ValidationPeriod
     */
    public function getValidationPeriod()
    {
        return $this->validationPeriod;
    }

    /**
     * @param ValidationPeriod $validationPeriod
     */
    public function setValidationPeriod($validationPeriod)
    {
        $this->validationPeriod = $validationPeriod;
        return $this;
    }

    const UNIT_MINUTE = 60;
    const UNIT_HOUR = 3600;

    public function getDuration( $unit = self::UNIT_HOUR){
        return ($this->getDateTo()->getTimestamp() - $this->getDateFrom()->getTimestamp()) / $unit;
    }

    public function toJson2(){
        $activityId = null;
        $activityLabel = null;
        $workpackageId = null;
        $workpackageLabel = null;
        $workpackageCode = null;

        $projectAcronym = null;
        $projectLabel = null;
        $projectId = null;


        if( $this->getWorkpackage() ){
            $workpackageId = $this->getWorkpackage()->getId();
            $workpackageLabel = (string)$this->getWorkpackage();
            $workpackageCode = $this->getWorkpackage()->getCode();
            $activityId = $this->getWorkpackage()->getActivity()->getId();
            $activityLabel = (string)$this->getWorkpackage()->getActivity();

            $projectId = $this->getWorkpackage()->getActivity()->getProject()->getId();
            $projectAcronym = $this->getWorkpackage()->getActivity()->getProject()->getAcronym();
            $projectLabel = $this->getWorkpackage()->getActivity()->getProject()->getLabel();

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

            'project_id' => $projectId,
            'project_acronym' => $projectAcronym,
            'project_label' => $projectLabel,

            'label' => $this->getLabel(),
            'description' => $this->getComment(),
            'start' => $this->getDateFrom()->format('c'),
            'end' => $this->getDateTo()->format('c'),
            'status' => $this->getStatus(),
            'owner' => $this->getPerson()->getDisplayName(),
            'owner_id' => $this->getPerson()->getId(),
        ];
    }

    /**
     * @return string
     */
    public function getPeriodCode(){
        return $this->getDateFrom()->format('Y-m');
    }

    public function toJson(){
        $activityId = null;
        $activityLabel = null;
        $workpackageId = null;
        $workpackageLabel = null;
        $workpackageCode = null;

        $projectAcronym = null;
        $projectLabel = null;
        $projectId = null;


        if( $this->getWorkpackage() ){
            $workpackageId = $this->getWorkpackage()->getId();
            $workpackageLabel = (string)$this->getWorkpackage();
            $workpackageCode = $this->getWorkpackage()->getCode();
            $activityId = $this->getWorkpackage()->getActivity()->getId();
            $activityLabel = (string)$this->getWorkpackage()->getActivity();

            $projectId = $this->getWorkpackage()->getActivity()->getProject()->getId();
            $projectAcronym = $this->getWorkpackage()->getActivity()->getProject()->getAcronym();
            $projectLabel = $this->getWorkpackage()->getActivity()->getProject()->getLabel();

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

            'project_id' => $projectId,
            'project_acronym' => $projectAcronym,
            'project_label' => $projectLabel,

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
     * @return \DateTime
     */
    public function getDateSync()
    {
        return $this->dateSync;
    }

    /**
     * @param \DateTime $dateSync
     */
    public function setDateSync($dateSync)
    {
        $this->dateSync = $dateSync;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSyncId()
    {
        return $this->syncId;
    }

    /**
     * @param \DateTime $syncId
     */
    public function setSyncId($syncId)
    {
        $this->syncId = $syncId;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidatedProjectBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidatedProjectBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getValidatedProjectById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationActivityById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedProjectAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationActivityAt();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedProjectBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectActivityBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getRejectedProjectById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectActivityById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedProjectAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectActivityBy();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedProjectComment()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectActivityMessage();
        }
        return null;
    }

     // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * @return string
     */
    public function getValidatedSciBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationSciBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getValidatedSciById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationSciById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedSciAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationSciAt();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getValidatedAdminBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationAdmBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getValidatedAdminById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationAdmById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedAdminAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getValidationAdmAt();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedSciBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectSciBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getRejectedSciById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectSciById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedSciAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectSciAt();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedSciComment()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectSciMessage();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedAdminBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectAdmBy();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getRejectedAdminById()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectAdmById();
        }
        return null;
    }

    /**
     * @return \DateTime
     */
    public function getRejectedAdminAt()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectAdmAt();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getRejectedAdminComment()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getRejectAdmMessage();
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getSendBy()
    {
        if( $this->getValidationPeriod() ){
            return $this->getValidationPeriod()->getDeclarer();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getIcsUid()
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
    public function getIcsFileUid()
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
    public function getIcsFileName()
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

    /**
     * Retourne l'année du créneau.
     *
     * @return int
     */
    public function getYear(){
        return intval($this->getDateFrom()->format('Y'));
    }

    /**
     * Retourne l'année du créneau.
     *
     * @return int
     */
    public function getMonth(){
        return intval($this->getDateFrom()->format('m'));
    }

    /**
     * Retourne l'année du créneau.
     *
     * @return int
     */
    public function getDate(){
        return intval($this->getDateFrom()->format('d'));
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

    public function __toString()
    {
        $acronym = "*";
        $activity = "Hors-Lot";
        $wpCode = $this->getLabel();

        if($this->getActivity()){
            $activity = $this->getActivity();
            $acronym = $this->getActivity()->getAcronym();
            $wpCode = $this->getWorkpackage() ? $this->getWorkpackage()->getCode() : 'no WP';
        }

        return sprintf("[timesheet:%s] %s = %s '%s':'%s':'%s' (%s)",
            $this->getId(),
            $this->getDateFrom()->format('Y-m-d'),
            $this->getDuration(),
            $acronym,
            $activity,
            $wpCode,
            $this->getPerson()
        );
    }


}