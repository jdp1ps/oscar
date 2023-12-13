<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 11/07/18
 * Time: 16:55
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Exception\OscarException;
use Oscar\Utils\DateTimeUtils;


/**
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ValidationPeriodRepository")
 */
class ValidationPeriod
{
    const OBJECT_ACTIVITY = 'activity';
    const OBJECT_HOLIDAY = 'conges';
    const OBJECT_LEARNING = 'learning';
    const OBJECT_TEACHING = 'teaching';
    const OBJECT_SICKLEAVE = 'sickleave';
    const OBJECT_AWAY = 'absent';
    const OBJECT_RESEARCH = 'research';
    const GROUP_WORKPACKAGE = 'workpackage';
    const GROUP_OTHER = 'other';

    const STATUS_STEP1 = 'send-prj';
    const STATUS_STEP2 = 'send-sci';
    const STATUS_STEP3 = 'send-adm';
    const STATUS_CONFLICT = 'conflict';
    const STATUS_VALID = 'valid';

    /**
     * Personnes
     *
     * @ORM\ManyToMany(targetEntity="Person")
     * @ORM\JoinTable(name="validationperiod_prj")
     */
    protected $validatorsPrj;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
     *
     */
    protected $validatorsPrjDefault = true;

    /**
     * Personnes
     *
     * @ORM\ManyToMany(targetEntity="Person")
     * @ORM\JoinTable(name="validationperiod_sci")
     */
    protected $validatorsSci;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
     *
     */
    protected $validatorsSciDefault = true;

    /**
     * Personnes
     *
     * @ORM\ManyToMany(targetEntity="Person")
     * @ORM\JoinTable(name="validationperiod_adm")
     */
    protected $validatorsAdm;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
     *
     */
    protected $validatorsAdmDefault = true;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private string $object;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private string $objectGroup;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private string $object_id;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau activité
    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private Person $declarer;

    /**
     * Mois
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $month;

    /**
     * Année
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $year;

    /**
     * Date d'envoi.
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTime $dateSend;


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau scientifique (Laboratoire)
    /**
     * Historique
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;

    /**
     * Date de la validation projet.
     *
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


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Validation niveau administratif (Composante)
    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationActivityMessage;

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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Rejet niveau activité
    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationSciMessage;

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

    /// Rejet scientifique hiérarchique
    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationAdmMessage;

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

    // Rejet administratif
    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectActivityMessage;

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

    /**
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectSciMessage;

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
     * Message
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectAdmMessage;

    /**
     * Schedule
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $schedule;

    /**
     * Intitulé du valideur.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * liste des créneaux associés
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="TimeSheet", mappedBy="validationPeriod")
     */
    protected $timesheets;

    /**
     * Commentaire du déclarant.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * ValidationPeriod constructor.
     * @param $id
     */
    public function __construct()
    {
        $this->validatorsPrj = new ArrayCollection();
        $this->validatorsSci = new ArrayCollection();
        $this->validatorsAdm = new ArrayCollection();
        $this->timesheets = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isValidatorsPrjDefault(): bool
    {
        return $this->validatorsPrjDefault;
    }

    /**
     * @param bool $validatorsPrjDefault
     */
    public function setValidatorsPrjDefault(bool $validatorsPrjDefault): self
    {
        $this->validatorsPrjDefault = $validatorsPrjDefault;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValidatorsSciDefault(): bool
    {
        return $this->validatorsSciDefault;
    }

    /**
     * @param bool $validatorsSciDefault
     */
    public function setValidatorsSciDefault(bool $validatorsSciDefault): self
    {
        $this->validatorsSciDefault = $validatorsSciDefault;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValidatorsAdmDefault(): bool
    {
        return $this->validatorsAdmDefault;
    }

    /**
     * @param bool $validatorsAdmDefault
     */
    public function setValidatorsAdmDefault(bool $validatorsAdmDefault): self
    {
        $this->validatorsAdmDefault = $validatorsAdmDefault;
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
     * @return ValidationPeriod
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTimesheets()
    {
        return $this->timesheets;
    }

    /**
     * @param ArrayCollection $timesheets
     */
    public function setTimesheets($timesheets)
    {
        $this->timesheets = $timesheets;
        return $this;
    }

    public function isActivityValidation()
    {
        return $this->getObjectGroup() == self::GROUP_WORKPACKAGE;
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
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * Retourne true si la personne est valide pour valider l'étape courante.
     *
     * @param Person $person
     */
    public function isValidator(Person $person)
    {
        switch ($this->getStatus()) {
            case self::STATUS_STEP1:
                return $this->isValidablePrjBy($person);
            case self::STATUS_STEP2:
                return $this->isValidableSciBy($person);
            case self::STATUS_STEP3:
                return $this->isValidableAdmBy($person);
            default:
                return false;
        }
    }

    public function isValidable()
    {
        return
            $this->getStatus() == self::STATUS_STEP1 ||
            $this->getStatus() == self::STATUS_STEP2 ||
            $this->getStatus() == self::STATUS_STEP3;
    }

    /**
     * Retourne TRUE si la validation est terminée.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getStatus() == self::STATUS_VALID;
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

    /**
     * Peut être validé niveau projet par la personne.
     *
     * @param Person $person
     * @return bool
     */
    public function isValidablePrjBy(Person $person)
    {
        if ($this->getStatus() == self::STATUS_STEP1 && $this->getValidatorsPrj()->contains($person)) {
            return true;
        }
        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getValidatorsPrj()
    {
        return $this->validatorsPrj;
    }

    /**
     * @param ArrayCollection $validatorsPrj
     */
    public function setValidatorsPrj($validatorsPrj)
    {
        $this->validatorsPrj = $validatorsPrj;
        return $this;
    }

    /**
     * Peut être validé niveau scientifique par la personne.
     *
     * @param Person $person
     * @return bool
     */
    public function isValidableSciBy(Person $person)
    {
        if ($this->getStatus() == self::STATUS_STEP2 && $this->getValidatorsSci()->contains($person)) {
            return true;
        }
        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getValidatorsSci()
    {
        return $this->validatorsSci;
    }

    /**
     * @param ArrayCollection $validatorsSci
     */
    public function setValidatorsSci($validatorsSci)
    {
        $this->validatorsSci = $validatorsSci;
        return $this;
    }

    /**
     * Peut être validé administrativement par la personne.
     *
     * @param Person $person
     * @return bool
     */
    public function isValidableAdmBy(Person $person)
    {
        if ($this->getStatus() == self::STATUS_STEP3 && $this->getValidatorsAdm()->contains($person)) {
            return true;
        }
        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getValidatorsAdm()
    {
        return $this->validatorsAdm;
    }

    /**
     * @param ArrayCollection $validatorsAdm
     */
    public function setValidatorsAdm($validatorsAdm)
    {
        $this->validatorsAdm = $validatorsAdm;
        return $this;
    }

    public function reject(Person $validateur, $message, $date = null)
    {
        if ($date == null) {
            $date = new \DateTime();
        }

        switch ($this->getStatus()) {
            case ValidationPeriod::STATUS_STEP1:
                $this->setRejectActivity($validateur, $date, $message);
                break;

            case ValidationPeriod::STATUS_STEP2:
                $this->setRejectSci($validateur, $date, $message);
                break;

            case ValidationPeriod::STATUS_STEP3:
                $this->setRejectAdm($validateur, $date, $message);
                break;

            default:
                throw new OscarException("Cette période n'a pas le bon status pour être validée.");
        }
    }

    public function setValidationActivity(Person $validateur, $when, $message = "")
    {
        $this->setValidationActivityMessage($message)
            ->addLog("Validation niveau projet par $validateur")
            ->setValidationActivityBy((string)$validateur)
            ->setValidationActivityById($validateur->getId())
            ->setValidationActivityAt($when)
            ->setStatus(self::STATUS_STEP2);
    }

    public function setValidationSci(Person $validateur, $when, $message = "")
    {
        $this->setValidationSciMessage($message)
            ->addLog("Validation niveau scientifique par $validateur")
            ->setValidationSciBy((string)$validateur)
            ->setValidationSciById($validateur->getId())
            ->setValidationSciAt($when)
            ->setStatus(self::STATUS_STEP3);
    }

    public function setValidationAdm(Person $validateur, $when, $message = "")
    {
        $this->setValidationAdmMessage($message)
            ->addLog("Validation niveau administratif par $validateur")
            ->setValidationAdmBy((string)$validateur)
            ->setValidationAdmById($validateur->getId())
            ->setValidationAdmAt($when)
            ->setStatus(self::STATUS_VALID);
    }

    public function setRejectActivity(Person $validateur, $when, $message = "")
    {
        $this->setRejectActivityMessage($message)
            ->addLog("Rejet niveau projet par $validateur")
            ->setRejectActivityBy((string)$validateur)
            ->setRejectActivityById($validateur->getId())
            ->setRejectActivityAt($when)
            ->setStatus(self::STATUS_CONFLICT);
    }

    public function setRejectSci(Person $validateur, $when, $message = "")
    {
        $this->setRejectSciMessage($message)
            ->addLog("Rejet niveau scientifique par $validateur")
            ->setRejectSciBy((string)$validateur)
            ->setRejectSciById($validateur->getId())
            ->setRejectSciAt($when)
            ->setStatus(self::STATUS_CONFLICT);
    }

    public function setRejectAdm(Person $validateur, $when, $message = "")
    {
        $this->setRejectAdmMessage($message)
            ->addLog("Rejet niveau administratif", $validateur)
            ->setRejectAdmBy((string)$validateur)
            ->setRejectAdmById($validateur->getId())
            ->setRejectAdmAt($when)
            ->setStatus(self::STATUS_CONFLICT);
    }

    /**
     * @return bool
     */
    public function requireValidation(): bool
    {
        return $this->getStatus() == self::STATUS_STEP1 ||
            $this->getStatus() == self::STATUS_STEP2 ||
            $this->getStatus() == self::STATUS_STEP3;
    }

    /**
     * @return ArrayCollection
     * @throws OscarException
     */
    public function getCurrentValidators()
    {
        if ($this->getStatus() == self::STATUS_STEP1) {
            return $this->getValidatorsPrj();
        } elseif ($this->getStatus() == self::STATUS_STEP2) {
            return $this->getValidatorsSci();
        } elseif ($this->getStatus() == self::STATUS_STEP3) {
            return $this->getValidatorsAdm();
        }

        return [];
    }

    public function isOpenForDeclaration()
    {
        return $this->hasConflict() || !$this->getStatus();
    }

    public function hasConflict()
    {
        return $this->getStatus() == self::STATUS_CONFLICT;
    }

    public function json()
    {
        return $this->getState();
    }

    public function getState()
    {
        return [
            'id' => $this->getId(),
            'log' => $this->getLog(),

            'label' => $this->getLabel(),

            'status' => $this->getStatus(),

            'validationactivity_at' => $this->getValidationActivityAt() ? $this->getValidationActivityAt()->format(
                'Y-m-d'
            ) : null,
            'validationactivity_by' => $this->getValidationActivityBy(),

            'validationsci_by' => $this->getValidationSciBy(),
            'validationsci_at' => $this->getValidationSciAt() ? $this->getValidationSciAt()->format('Y-m-d') : null,

            'validationadm_by' => $this->getValidationAdmBy(),
            'validationadm_at' => $this->getValidationAdmAt() ? $this->getValidationAdmAt()->format('Y-m-d') : null,

            'rejectactivity_at' => $this->getRejectActivityAt() ? $this->getRejectActivityAt()->format('Y-m-d') : null,
            'rejectactivity_by' => $this->getRejectActivityBy(),
            'rejectactivity_message' => $this->getRejectActivityMessage(),

            'rejectsci_by' => $this->getRejectSciBy(),
            'rejectsci_at' => $this->getRejectSciAt() ? $this->getRejectSciAt()->format('Y-m-d') : null,
            'rejectsci_message' => $this->getRejectSciMessage(),

            'rejectadm_by' => $this->getRejectAdmBy(),
            'rejectadm_at' => $this->getRejectAdmAt() ? $this->getRejectAdmAt()->format('Y-m-d') : null,
            'rejectadm_message' => $this->getRejectAdmMessage(),
        ];
    }

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

    public function getLabel()
    {
        if ($this->getObjectGroup() == self::GROUP_WORKPACKAGE) {
            return "Déclaration sur une activité ";
        } else {
            return "Déclaration hors-lot " . $this->getObject();
        }
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
     * @return datetime
     */
    public function getRejectSciAt()
    {
        return $this->rejectSciAt;
    }

    // ---

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
    public function getRejectSciMessage()
    {
        return $this->rejectSciMessage;
    }

    /**
     * @param string $rejectSciMessage
     */
    public function setRejectSciMessage($rejectSciMessage)
    {
        $this->rejectSciMessage = $rejectSciMessage;
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
    public function getRejectAdmMessage()
    {
        return $this->rejectAdmMessage;
    }

    /**
     * @param string $rejectAdmMessage
     */
    public function setRejectAdmMessage($rejectAdmMessage)
    {
        $this->rejectAdmMessage = $rejectAdmMessage;
        return $this;
    }

    public function getText()
    {
        if ($this->getObjectGroup() == self::GROUP_WORKPACKAGE) {
            return $this->getObject();
        } else {
        }
    }

    public function getFirstDayStr()
    {
        $month = $this->getMonth() < 10 ? '0' . $this->getMonth() : $this->getMonth();
        return sprintf('%s-%s-01', $this->getYear(), $month);
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
     * Actualise les logs.
     *
     * @param $message
     * @param string $by
     */
    public function addLog($message, $by = 'Anonymous')
    {
        $log = $this->getLog();
        $date = new \DateTime();
        $msg = sprintf('%s %s %s', $date->format('Y-m-d H:i:s'), $by, $message);
        $log .= $msg . "\n";
        $this->setLog($log);
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function addValidatorPrj(Person $person)
    {
        if (!$this->getValidatorsPrj()->contains($person)) {
            $this->getValidatorsPrj()->add($person);
        }
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function addValidatorSci(Person $person)
    {
        if (!$this->getValidatorsSci()->contains($person)) {
            $this->getValidatorsSci()->add($person);
        }
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function addValidatorAdm(Person $person)
    {
        if (!$this->getValidatorsAdm()->contains($person)) {
            $this->getValidatorsAdm()->add($person);
        }
        return $this;
    }

    public function removeValidatorAdm(Person $person)
    {
        if (!$this->getValidatorsAdm()->contains($person)) {
            $this->getValidatorsAdm()->removeElement($person);
        }
        return $this;
    }

    /**
     * Retourne la clef de trie pour le rangement mensuel.
     *
     * @return string
     */
    public function getPeriodKey()
    {
        if ($this->getObjectGroup() == self::GROUP_WORKPACKAGE) {
            return $this->getObjectGroup();
        } else {
            return $this->getObject() . '-' . $this->getObjectId();
        }
    }


    public function getPeriod()
    {
        return DateTimeUtils::getCodePeriod($this->getYear(), $this->getMonth());
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

    public function toJson()
    {
        $validateursPrj = [];
        $validateursSci = [];
        $validateursAdm = [];

        foreach ($this->getValidatorsPrj() as $validateur) {
            $validateursPrj[] = [
                'id' => $validateur->getId(),
                'person' => (string)$validateur
            ];
        }

        foreach ($this->getValidatorsSci() as $validateur) {
            $validateursSci[] = [
                'id' => $validateur->getId(),
                'person' => (string)$validateur
            ];
        }

        foreach ($this->getValidatorsAdm() as $validateur) {
            $validateursAdm[] = [
                'id' => $validateur->getId(),
                'person' => (string)$validateur
            ];
        }

        return [
            'id' => $this->getId(),
            'year' => $this->getYear(),
            'month' => $this->getMonth(),
            'period' => sprintf('%s-%s', $this->getYear(), $this->getMonth()),
            'declarer' => (string)$this->getDeclarer(),
            'declarer_id' => $this->getDeclarer()->getId(),
            'declarer_mail' => $this->getDeclarer()->getEmail(),
            'declarer_mailmd5' => md5($this->getDeclarer()->getEmail()),
            'status' => $this->getStatus(),
            'object' => $this->getObject(),
            'objectgroup' => $this->getObjectGroup(),
            'objectid' => $this->getObjectId(),
            'datesend' => $this->getDateSend()->format('Y-m-d'),
            'validatorsPrj' => $validateursPrj,
            'validatorsSci' => $validateursSci,
            'validatorsAdm' => $validateursAdm,
            'validatedPrjBy' => $this->getValidationActivityBy(),
            'validatedSciBy' => $this->getValidationSciBy(),
            'validatedAdmBy' => $this->getValidationAdmBy(),
        ];
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

    public function __toString()
    {
        return sprintf(
            '[ValidationPeriod:%s] %s-%s %s:%s=%s, pid=%s',
            $this->getId(),
            $this->getYear(),
            $this->getMonth(),
            $this->getObjectGroup(),
            $this->getObject(),
            $this->getObjectId(),
            $this->getDeclarer()
        );
    }

    /**
     * @return Person
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


}