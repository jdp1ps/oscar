<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 11/07/18
 * Time: 16:55
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ValidationPeriod
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

    const OBJECT_ACTIVITY       = 'activity';
    const OBJECT_HOLIDAY        = 'conges';
    const OBJECT_LEARNING       = 'learning';
    const OBJECT_TEACHING       = 'teaching';
    const OBJECT_SICKLEAVE      = 'sickleave';
    const OBJECT_AWAY           = 'absent';
    const OBJECT_RESEARCH       = 'research';

    const GROUP_WORKPACKAGE     = 'workpackage';
    const GROUP_OTHER           = 'other';



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
     * Date d'envoi.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSend;

    /**
     * Historique
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau activité

    /**
     * Date de la validation projet.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $validationActivityAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $validationActivityBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $validationActivityById;

    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationActivityMessage;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau scientifique (Laboratoire)

    /**
     * Date de la validation.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $validationSciAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $validationSciBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $validationSciById;

    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationSciMessage;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau administratif (Composante)

    /**
     * Date de la validation.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $validationAdmAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $validationAdmBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $validationAdmById;

    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationAdmMessage;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Rejet niveau activité

    /**
     * Date de la validation projet.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $rejectActivityAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectActivityBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rejectActivityById;

    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectActivityMessage;


    /// Rejet scientifique hiérarchique

    /**
     * Date de la reject.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $rejectSciAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectSciBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rejectSciById;


    // Rejet administratif

    /**
     * Date du rejet.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $rejectAdmAt;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $rejectAdmBy;

    /**
     * Identifiant du valideur (Person).
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rejectAdmById;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $status;

    const STATUS_STEP1      = 'send-prj';
    const STATUS_STEP2      = 'send-sci';
    const STATUS_STEP3      = 'send-adm';

    const STATUS_CONFLICT   = 'conflict';
    const STATUS_VALID      = 'valid';

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
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
    public function setMonth($month)
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
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateSend()
    {
        return $this->dateSend;
    }

    /**
     * @param datetime $dateSend
     */
    public function setDateSend($dateSend)
    {
        $this->dateSend = $dateSend;
        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getValidationActivityAt()
    {
        return $this->validationActivityAt;
    }

    /**
     * @param datetime $validationActivityAt
     */
    public function setValidationActivityAt($validationActivityAt)
    {
        $this->validationActivityAt = $validationActivityAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationActivityBy()
    {
        return $this->validationActivityBy;
    }

    /**
     * @param string $validationActivityBy
     */
    public function setValidationActivityBy($validationActivityBy)
    {
        $this->validationActivityBy = $validationActivityBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getValidationActivityById()
    {
        return $this->validationActivityById;
    }

    /**
     * @param int $validationActivityById
     */
    public function setValidationActivityById($validationActivityById)
    {
        $this->validationActivityById = $validationActivityById;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationActivityMessage()
    {
        return $this->validationActivityMessage;
    }

    /**
     * @param string $validationActivityMessage
     */
    public function setValidationActivityMessage($validationActivityMessage)
    {
        $this->validationActivityMessage = $validationActivityMessage;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getValidationSciAt()
    {
        return $this->validationSciAt;
    }

    /**
     * @param datetime $validationSciAt
     */
    public function setValidationSciAt($validationSciAt)
    {
        $this->validationSciAt = $validationSciAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationSciBy()
    {
        return $this->validationSciBy;
    }

    /**
     * @param string $validationSciBy
     */
    public function setValidationSciBy($validationSciBy)
    {
        $this->validationSciBy = $validationSciBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getValidationSciById()
    {
        return $this->validationSciById;
    }

    /**
     * @param int $validationSciById
     */
    public function setValidationSciById($validationSciById)
    {
        $this->validationSciById = $validationSciById;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationSciMessage()
    {
        return $this->validationSciMessage;
    }

    /**
     * @param string $validationSciMessage
     */
    public function setValidationSciMessage($validationSciMessage)
    {
        $this->validationSciMessage = $validationSciMessage;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getValidationAdmAt()
    {
        return $this->validationAdmAt;
    }

    /**
     * @param datetime $validationAdmAt
     */
    public function setValidationAdmAt($validationAdmAt)
    {
        $this->validationAdmAt = $validationAdmAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationAdmBy()
    {
        return $this->validationAdmBy;
    }

    /**
     * @param string $validationAdmBy
     */
    public function setValidationAdmBy($validationAdmBy)
    {
        $this->validationAdmBy = $validationAdmBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getValidationAdmById()
    {
        return $this->validationAdmById;
    }

    /**
     * @param int $validationAdmById
     */
    public function setValidationAdmById($validationAdmById)
    {
        $this->validationAdmById = $validationAdmById;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationAdmMessage()
    {
        return $this->validationAdmMessage;
    }

    /**
     * @param string $validationAdmMessage
     */
    public function setValidationAdmMessage($validationAdmMessage)
    {
        $this->validationAdmMessage = $validationAdmMessage;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getRejectActivityAt()
    {
        return $this->rejectActivityAt;
    }

    /**
     * @param datetime $rejectActivityAt
     */
    public function setRejectActivityAt($rejectActivityAt)
    {
        $this->rejectActivityAt = $rejectActivityAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectActivityBy()
    {
        return $this->rejectActivityBy;
    }

    /**
     * @param string $rejectActivityBy
     */
    public function setRejectActivityBy($rejectActivityBy)
    {
        $this->rejectActivityBy = $rejectActivityBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getRejectActivityById()
    {
        return $this->rejectActivityById;
    }

    /**
     * @param int $rejectActivityById
     */
    public function setRejectActivityById($rejectActivityById)
    {
        $this->rejectActivityById = $rejectActivityById;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectActivityMessage()
    {
        return $this->rejectActivityMessage;
    }

    /**
     * @param string $rejectActivityMessage
     */
    public function setRejectActivityMessage($rejectActivityMessage)
    {
        $this->rejectActivityMessage = $rejectActivityMessage;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getRejectSciAt()
    {
        return $this->rejectSciAt;
    }

    /**
     * @param datetime $rejectSciAt
     */
    public function setRejectSciAt($rejectSciAt)
    {
        $this->rejectSciAt = $rejectSciAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectSciBy()
    {
        return $this->rejectSciBy;
    }

    /**
     * @param string $rejectSciBy
     */
    public function setRejectSciBy($rejectSciBy)
    {
        $this->rejectSciBy = $rejectSciBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getRejectSciById()
    {
        return $this->rejectSciById;
    }

    /**
     * @param int $rejectSciById
     */
    public function setRejectSciById($rejectSciById)
    {
        $this->rejectSciById = $rejectSciById;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getRejectAdmAt()
    {
        return $this->rejectAdmAt;
    }

    /**
     * @param datetime $rejectAdmAt
     */
    public function setRejectAdmAt($rejectAdmAt)
    {
        $this->rejectAdmAt = $rejectAdmAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectAdmBy()
    {
        return $this->rejectAdmBy;
    }

    /**
     * @param string $rejectAdmBy
     */
    public function setRejectAdmBy($rejectAdmBy)
    {
        $this->rejectAdmBy = $rejectAdmBy;
        return $this;
    }

    /**
     * @return int
     */
    public function getRejectAdmById()
    {
        return $this->rejectAdmById;
    }

    /**
     * @param int $rejectAdmById
     */
    public function setRejectAdmById($rejectAdmById)
    {
        $this->rejectAdmById = $rejectAdmById;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


}