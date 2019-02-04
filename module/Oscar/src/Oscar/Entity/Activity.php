<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 12:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Import\Data\DataExtractorDate;
use Oscar\Service\ActivityTypeService;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * ProjectGrant, correspond aux conventions (Contrats).
 *
 * @package Oscar\Entity
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\ActivityRepository")
 */
class Activity implements ResourceInterface
{
    use TraitTrackable;

    ///////////////////////////////////////////////////////////////////// STATUS
    // 100 Statuts actifs
    const STATUS_ACTIVE = 101;

    const STATUS_PROGRESS = 102;
    /** Activité en cours de réalisation (dossier) */

    const STATUS_DEPOSIT = 103;

    // 200 : Terminées / Abandonnées

    // Activité fermée
    const STATUS_CLOSED = 200;

    // Activité terminée
    const STATUS_TERMINATED = 201;

    // Activité abandonnée
    const STATUS_ABORDED = 250;

    // Activité refusée
    const STATUS_REFUSED = 210;

    ///////////////////////////////////////////////////////////////////
    // Incidence financière
    const FINANCIAL_IMPACT_TAKE = 'Recette';
    const FINANCIAL_IMPACT_COST = 'Dépense';
    const FINANCIAL_IMPACT_NONE = 'Aucune';

    /**
     * Retourne la liste des incidences financières possible.
     *
     * @return array|null
     */
    public static function getFinancialImpactValues()
    {
        static $finacialImpctValues;
        if ($finacialImpctValues === null) {
            $finacialImpctValues = [
                self::FINANCIAL_IMPACT_TAKE,
                self::FINANCIAL_IMPACT_COST,
                self::FINANCIAL_IMPACT_NONE,
            ];
        }

        return $finacialImpctValues;
    }

    // 400 : Conflits
    const STATUS_DISPUTE = 400; // Litige
    const STATUS_ERROR_STATUS = 404; // Pas de status (suite à l'import)

    public static function getStatusSelect()
    {
        static $statusSelect;
        if ($statusSelect === null) {
            $statusSelect = [
                self::STATUS_ERROR_STATUS   => 'Conflit : pas de statut',
                self::STATUS_ACTIVE         => 'Actif',
                self::STATUS_PROGRESS       => 'Brouillon',
                self::STATUS_DEPOSIT        => 'Déposé',
                self::STATUS_ABORDED        => 'Dossier abandonné',
                self::STATUS_DISPUTE        => 'Litige',
                self::STATUS_REFUSED        => 'Refusé',
                self::STATUS_TERMINATED     => 'Résilié',
                self::STATUS_CLOSED         => 'Terminé',
            ];
        }

        return $statusSelect;
    }

    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////// FEUILLE DE TEMPS (FORMAT)
    const TIMESHEET_FORMAT_NONE             = 0;
    const TIMESHEET_FORMAT_HOURS_BY_YEAR    = 10;
    const TIMESHEET_FORMAT_HOURS_BY_MONTH   = 20;
    const TIMESHEET_FORMAT_HOURS_BY_WEEK    = 30;
    const TIMESHEET_FORMAT_HOURS_BY_DAY     = 40;
    const TIMESHEET_FORMAT_FREE             = 50;

    public static function getTimesheetFormatSelect()
    {
        static $timesheetFormatSelect;
        if ($timesheetFormatSelect === null) {
            $timesheetFormatSelect = [
                self::TIMESHEET_FORMAT_NONE             => 'Aucun',
                self::TIMESHEET_FORMAT_HOURS_BY_MONTH   => 'Heures par mois',
                self::TIMESHEET_FORMAT_HOURS_BY_WEEK    => 'Heures par semaine',
                self::TIMESHEET_FORMAT_HOURS_BY_DAY     => 'Heures par jour',
                self::TIMESHEET_FORMAT_FREE             => 'Heures détaillées',
            ];
        }

        return $timesheetFormatSelect;
    }




    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $oscarId;


    /**
     * => CONV_CLEUNIK
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $centaureId;

    /**
     * => CONV_CLEUNIK
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $oscarNum;

    /**
     * => NUM_CONVENTION
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $centaureNumConvention;

    /**
     * Type d'activité
     *
     * @var ActivityType
     * @ORM\ManyToOne(targetEntity="ActivityType")
     */
    private $activityType;


    /**
     * Code EOTP (Code utilisé en interne entre les différents services pour
     * identifier un contrat de recherche).
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $codeEOTP;

    /**
     * @var double
     * @ORM\Column(type="float", nullable=true)
     */
    private $fraisDeGestion;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $label = '';

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = '';

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasSheet = false;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration = false;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $justifyWorkingTime = 0;

    /**
     * @var integer
     * @ORM\Column(type="float", nullable=true)
     */
    private $justifyCost = 0;

    /**
     * Montant de la subvension.
     *
     * @var integer
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount = 0;

    /**
     * Date de début de la subvension.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateStart;

    /**
     * Date de fin de la subvension.
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateEnd;

    /**
     * Date de signature
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateSigned;

    /**
     * Date d'ouverture du dossier : Jour de la création
     * MAJ : Cette date peut correspondre à la date de création du PFI
     *
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateOpened;


    /**
     * Incidence financière.
     *
     * @var string
     * @ORM\Column(type="string", length=32, nullable=false, options={"default" : "Recette"})
     */
    private $financialImpact = self::FINANCIAL_IMPACT_TAKE;


    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $noteFinanciere;


    /**
     * @var double
     * @ORM\Column(type="float", nullable=true)
     */
    private $assietteSubventionnable;


    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="grants")
     */
    private $project;

    /**
     * Discipline
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Discipline", cascade={"detach"}, inversedBy="activities")
     */
    protected $disciplines;

    /**
     * Liste des personnes impliquées dans cette activité
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityPerson", mappedBy="activity", cascade={"remove"})
     */
    protected $persons;

    /**
     * Liste des jalons (dates clefs).
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityDate", mappedBy="activity", cascade={"remove"})
     */
    protected $milestones;

    /**
     * Liste des versements (dates clefs).
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityPayment", mappedBy="activity", cascade={"remove"})
     */
    protected $payments;

    /**
     * Liste des partenaires du projet.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityOrganization", mappedBy="activity", cascade={"remove"})
     */
    protected $organizations;

    /**
     * Lots de travail
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="WorkPackage", mappedBy="activity", cascade={"remove"})
     * @ORM\OrderBy({"code" = "ASC"})
     */
    protected $workPackages;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="ContractType")
     */
    private $type = null;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Currency", fetch="EAGER")
     */
    private $currency = null;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="TVA")
     */
    private $tva = null;

    /**
     * Liste des documents.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ContractDocument", mappedBy="grant", cascade={"remove"})
     */
    private $documents;

    /**
     * @var String
     * @ORM\Column(type="string", options={"default":"none"}, nullable=false)
     */
    private $timesheetFormat = self::TIMESHEET_FORMAT_NONE;

    private $_cachePersonsByRole;

    /**
     * @var string
     * @ORM\Column(type="object", nullable=true)
     */
    protected $numbers = [];

    public function isActive()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }

    /**
     * @return float
     */
    public function getFraisDeGestion()
    {
        return $this->fraisDeGestion;
    }

    /**
     * @param float $fraisDeGestion
     */
    public function setFraisDeGestion($fraisDeGestion)
    {
        $this->fraisDeGestion = $fraisDeGestion;
        return $this;
    }

    /**
     * Retourne l'acronyme du projet si disponible.
     *
     * @return mixed|null
     */
    public function getAcronym(){
        if( $this->getProject() ){
            return $this->getProject()->getAcronym();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getNumbers()
    {
        if( $this->numbers == null )
            $this->numbers = [];
        return $this->numbers;
    }

    /**
     * @param string $numbers
     */
    public function setNumbers($numbers)
    {
        $this->numbers = $numbers;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addNumber( $key, $value ){
        $this->numbers[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeNumber( $key ){
        if( key_exists($key, $this->numbers) ){
            unset($this->numbers[$key]);
        }
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function getNumber( $key ){
        if( $this->numbers && key_exists($key, $this->numbers) ){
            return $this->numbers[$key];
        }
        return null;
    }

    /**
     * @return String
     */
    public function getTimesheetFormat()
    {
        return $this->timesheetFormat;
    }

    /**
     * @return string
     */
    public function getFinancialImpact()
    {
        return $this->financialImpact;
    }

    /**
     * @param string $financialImpact
     * @return Activity
     */
    public function setFinancialImpact($financialImpact)
    {
        $this->financialImpact = $financialImpact;
        return $this;
    }

    /**
     * @param String $timesheetFormat
     */
    public function setTimesheetFormat($timesheetFormat)
    {
        $this->timesheetFormat = $timesheetFormat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOscarNum()
    {
        return $this->oscarNum;
    }

    /**
     * @return string
     */
    public function getCentaureId()
    {
        return $this->centaureId;
    }

    /**
     * @param mixed $centaureId
     */
    public function setCentaureId($centaureId)
    {
        $this->centaureId = $centaureId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOscarId()
    {
        return $this->oscarId;
    }

    /**
     * @return ActivityType
     */
    public function getActivityType()
    {
        return $this->activityType;
    }

    public function addActivityDate(ActivityDate $activityDate)
    {
        if (!$this->milestones->contains($activityDate)) {
            $this->milestones->add($activityDate);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMilestones()
    {
        return $this->milestones;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param ActivityType $activityType
     */
    public function setActivityType($activityType)
    {
        $this->activityType = $activityType;

        return $this;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * @param mixed $tva
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodeEOTP()
    {
        return $this->codeEOTP;
    }

    /**
     * @param mixed $codeEOTP
     */
    public function setCodeEOTP($codeEOTP)
    {
        $this->codeEOTP = $codeEOTP;

        return $this;
    }

    public function getActivityTypeChain(
        ActivityTypeService $activityTypeService
    ) {
        return $activityTypeService->getActivityTypeChain($this->getActivityType());
    }

    /**
     * @return mixed
     */
    public function getCentaureNumConvention()
    {
        return $this->centaureNumConvention;
    }

    /**
     * @param mixed $centaureNumConvention
     */
    public function setCentaureNumConvention($centaureNumConvention)
    {
        $this->centaureNumConvention = $centaureNumConvention;

        return $this;
    }

    /**
     * @return ContractType
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
     * @return ArrayCollection
     */
    public function getWorkPackages()
    {
        return $this->workPackages;
    }

    /**
     * @param ArrayCollection $workPackages
     */
    public function setWorkPackages($workPackages)
    {
        $this->workPackages = $workPackages;
    }

    /**
     * @param ArrayCollection $workPackages
     */
    public function addWorkPackages(WorkPackage $workPackage)
    {
        if( !$this->workPackages->contains($workPackage) ){
            $this->workPackages->add($workPackage);
            $workPackage->setActivity($this);
        }
    }

    /**
     * @param ArrayCollection $workPackages
     */
    public function removeWorkPackages(WorkPackage $workPackage)
    {
        if( !$this->workPackages->contains($workPackage) ){
            $this->workPackages->removeElement($workPackage);
            $workPackage->setActivity(null);
        }
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart($deep = false)
    {
        return $this->dateStart;
    }

    /**
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        if( is_string($dateStart) ){
            $dateStart = (new DataExtractorDate())->extract($dateStart);
        }
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param datetime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        if( is_string($dateEnd) ){
            $dateEnd = (new DataExtractorDate())->extract($dateEnd);
        }
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateOpened()
    {
        return $this->dateOpened;
    }

    /**
     * @param datetime $dateOpened
     */
    public function setDateOpened($dateOpened)
    {
        $this->dateOpened = $dateOpened;

        return $this;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
        if ($project instanceof Project && !$project->getGrants()->contains($this)) {
            $project->getGrants()->add($this);
        }

        return $this;
    }

    public function touch()
    {
        $this->setDateUpdated(new \DateTime());
        if( $this->getProject() ){
            $this->getProject()->touch();
        }
    }

    /**
     * @return datetime
     */
    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    /**
     * @param datetime $dateSigned
     */
    public function setDateSigned($dateSigned)
    {
        $this->dateSigned = $dateSigned;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getFullLabel(){
        return sprintf('[%s] %s', $this->getAcronym(), $this->getLabel());
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
     * @return boolean
     */
    public function isHasSheet()
    {
        return $this->hasSheet;
    }

    /**
     * @param boolean $hasSheet
     */
    public function setHasSheet($hasSheet)
    {
        $this->hasSheet = $hasSheet;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    public function getCalculatedDuration(){
        if( $this->getDateStart() && $this->getDateEnd() ){
            return ceil(($this->getDateEnd()->getTimestamp() - $this->getDateStart()->getTimestamp())/(60*60*24));
        }
        return 0;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getJustifyWorkingTime()
    {
        return $this->justifyWorkingTime;
    }

    /**
     * @param int $justifyWorkingTime
     */
    public function setJustifyWorkingTime($justifyWorkingTime)
    {
        $this->justifyWorkingTime = $justifyWorkingTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getJustifyCost()
    {
        return $this->justifyCost;
    }

    /**
     * @param int $justifyCost
     */
    public function setJustifyCost($justifyCost)
    {
        $this->justifyCost = $justifyCost;

        return $this;
    }

    public function __construct()
    {
        $this->setDateCreated(new \DateTime());
        $this->setDateUpdated(new \DateTime());
        $this->documents = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->disciplines = new ArrayCollection();
        $this->timesheetFormat = TimeSheet::TIMESHEET_FORMAT_NONE;
    }

    /**
     * Retourne la liste des périodes sous la forme Y-m prévues pour l'activité.
     *
     * @return array
     */
    public function getPredictedPeriods(){
        $out = [
            'warnings' => null,
            'periods' => [],
        ];
        if( !$this->getDateStart() || !$this->getDateEnd()){
            $out['warnings'] = "Les dates de début et de fin de l'activité doivent être renseignée";
        }

        $date1 = $this->getDateStart()->format('Y-m-d');
        $date2 = $this->getDateEnd()->format('Y-m-d');

        $d1 = strtotime($date1);
        $d2 = strtotime($date2);

        while ($d1 <= $d2) {
            $out['periods'][] = date('Y-m', $d1);
            $d1 = strtotime("+1 month", $d1);
        }
        return $out;
    }

    /**
     * @param Discipline $discipline
     * @return bool
     */
    public function hasDiscipline(Discipline $discipline)
    {
        return $this->disciplines->contains($discipline);
    }

    /**
     * @param Discipline $discipline
     * @return $this
     */
    public function addDiscipline(Discipline $discipline)
    {
        if (!$this->hasDiscipline($discipline)) {
            $this->disciplines->add($discipline);
        }
        return $this;
    }

    /**
     * @param Discipline $discipline
     * @return $this
     */
    public function removeDiscipline(Discipline $discipline)
    {
        if ($this->hasDiscipline($discipline)) {
            $this->disciplines->remove($discipline);
        }
        return $this;
    }

    /**
     * @return Discipline[]
     */
    public function getDisciplines()
    {
        return $this->disciplines;
    }

    /**
     * Retourne la liste des disciplines sous la forme d'un tableau de chaînes.
     *
     * @return array
     */
    public function getDisciplinesArray()
    {
        $disciplines = [];
        foreach ($this->getDisciplines() as $d) {
            $disciplines[] = (string)$d;
        }
        return $disciplines;
    }

    /**
     * @return Discipline[]
     */
    public function setDisciplines( $disciplines )
    {
        $this->disciplines = new ArrayCollection();
        foreach( $disciplines as $d ){
            $this->addDiscipline($d);
        }
        return $this;
    }

    public function newPerson(Person $person, $role, $start = null, $to = null)
    {
        if (!$this->hasPerson($person, $role)) {
            $member = new ActivityPerson();
            $member->setPerson($person)
                ->setRole($role)
                ->setDateStart($start)
                ->setDateEnd($to);
            $this->persons->add($member);

            return $member;
        }
    }

    /**
     * @return integer[]
     */
    public function getDisciplinesIds()
    {
        $ids = [];
        foreach( $this->getDisciplines() as $discipline ){
            $ids[] = $discipline->getId();
        }
        return $ids;
    }

    public function newOrganization(
        Organization $organization,
        $role,
        $start = null,
        $to = null
    ) {
        if (!$this->hasOrganization($organization, $role)) {
            $partner = new ActivityOrganization();
            $partner->setOrganization($organization)
                ->setRole($role)
                ->setDateStart($start)
                ->setDateEnd($to);
            $this->organizations->add($partner);

            return $partner;
        }
    }

    /**
     * Retourne la liste des rôles endossés par la personne pour cette activités.
     *
     * @param Person $person
     * @return string[]
     */
    public function getPersonRoles(Person $person)
    {
        $roles = [];
        if ($this->getProject()) {
            $roles = array_merge($roles, $this->getProject()->getPersonRoles($person));
        }

        $today = new \DateTime();
        /** @var ActivityPerson $member */
        foreach ($this->getPersons() as $member) {
		if(!$member->getPerson()){continue;}
            if ($member->getPerson()->getId() === $person->getId()) {
                // Date de début non-nulle et suppérieur à ajourd'hui
                if( $member->getDateStart() !== null && $member->getDateStart() > $today ){
                    continue;
                }
                // Date de début non-nulle et suppérieur à ajourd'hui
                if( $member->getDateEnd() !== null && $member->getDateEnd() < $today ){
                    continue;
                }
                $roles[] = $member->getRole();
            }
        }

        return array_unique($roles);
    }

    private $_personsActives;

    /**
     * Retourne la liste des personnes ayant un rôle principale direct.
     *
     * @return array
     */
    public function getPersonPrincipalActive(){

        $persons = [];

        /** @var ActivityPerson $member */
        foreach ($this->getPersons() as $member) {
            if (!$member->isOutOfDate()) {
                $persons[] = $member;
            }
        }

        if( $this->getProject() ){
            /** @var ProjectMember $projectMember */
            foreach ($this->getProject()->getPersons() as $projectMember) {
                if (!$projectMember->isOutOfDate()) {
                    $persons[] = $projectMember;
                }
            }
        }

        return $persons;
    }

    /**
     * @return ArrayCollection
     */
    public function getPersons($includeInactives = true)
    {
        if ($includeInactives === false) {
            if ($this->_personsActives == null) {
                $this->_personsActives = [];
                /** @var ActivityPerson $member */
                foreach ($this->getPersons() as $member) {
                    if (!$member->isOutOfDate()) {
                        $this->_personsActives[] = $member;
                    }
                }
            }

            return $this->_personsActives;
        }

        return $this->persons;
    }

    /**
     * Retourne la liste des personnes ayant le rôle.
     * @param string $role
     */
    public function getPersonsRoled( array $roles, $includeInactives = true )
    {
        $persons = [];
        foreach( $this->getPersons($includeInactives) as $p ){
            if( in_array($p->getRole(), $roles ) ) {
                $persons[] = $p;
            }
        }
        return $persons;
    }

    /**
     * Retourne la liste des personnes ayant le rôle.
     * @param string $role
     */
    public function getPersonsRoledDeep( array $roles )
    {
        $persons = $this->getPersonsRoled($roles);
        if( $this->getProject() ){
            foreach( $this->getProject()->getPersonsRoled($roles) as $p ){
                $persons[] = $p;
            }
        }
        return $persons;
    }

    public function getPersonJson(){
        $json = [];
        /** @var ActivityPerson $activityPerson */
        foreach ($this->getPersonsDeep() as $activityPerson ){
            $json[] = [
                'id'                => $activityPerson->getId(),
                'end'               => $activityPerson->getDateEnd(),
                'start'               => $activityPerson->getDateStart(),
                'enrolled'          => $activityPerson->getPerson()->getId(),
                'enrolledLabel'     => $activityPerson->getPerson()->__toString(),
                'enroller'          => $activityPerson->getEnroller()->getId(),
                'enrollerLabel'     => $activityPerson->getEnroller()->__toString(),
                'role'              => $activityPerson->getRole(),
                'roleLabel'         => $activityPerson->getRole(),
            ];
        }
        return $json;
    }

    public function getPersonsDeep( $ignoreMain = false )
    {
        $persons = [];

        if ($this->getProject()) {
            /** @var ProjectMember $member */
            foreach ($this->getProject()->getMembers() as $member) {
                $persons[] = $member;
            }
        }
        foreach ($this->getPersons() as $member) {
            $persons[] = $member;
        }

        return $persons;
    }

    public function getOrganizationsDeep( $ignoreMain = false )
    {
        $partners = [];
        if ($this->getProject()) {
            foreach ($this->getProject()->getPartners() as $partner) {
                $partners[] = $partner;
            }
        }

        foreach ($this->getOrganizations() as $partner) {
            $partners[] = $partner;
        }

        return $partners;
    }


    ////////////////////////////////////////////////////////////////////////////
    public function getTypeSlug()
    {
        static $slugify, $sluged;

        if ($slugify === null) {
            $slugify = new Slugify();
        }

        if ($sluged === null) {
            $sluged = [];
        }

        if ($this->getActivityType() == null) {
            return 'icon-acttype-none';
        }

        if (!isset($sluged[$this->getActivityType()->getNature()])) {
            $sluged[$this->getActivityType()->getNature()] = 'icon-acttype-' . $slugify->slugify($this->getActivityType()->getNatureStr());
        }

        return $sluged[$this->getActivityType()->getNature()];
    }

    /**
     * @param ArrayCollection $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
        return $this;
    }

    public function addActivityPerson(ActivityPerson $activityPerson)
    {
        $this->getPersons()->add($activityPerson);
    }

    public function hasPerson(
        Person $person,
        $role = null,
        \DateTime $dateStart = null,
        \DateTime $dateEnd = null,
        $deep = true
    ) {
        $found = false;
        /** @var ActivityPerson $activityPerson */
        foreach ($this->persons as $activityPerson) {
            if ($person == $activityPerson->getPerson()) {
                if ($role !== null && $activityPerson->getRole() !== $role) {
                    continue;
                } else {
                    $found = true;
                }
            }
        }
        if ($deep === true && $found === false && $this->getProject()) {
            $found = $this->getProject()->hasPerson($person, $role);
        }

        return $found;
    }

    private $_organizationsActives;

    /**
     * @return ArrayCollection
     */
    public function getOrganizations($includeInactives = true)
    {
        if ($includeInactives === false) {
            if ($this->_organizationsActives == null) {
                $this->_organizationsActives = [];
                /** @var ActivityOrganization $partner */
                foreach ($this->getOrganizations() as $partner) {
                    if (!$partner->isOutOfDate()) {
                        $this->_organizationsActives[] = $partner;
                    }
                }
            }

            return $this->_organizationsActives;
        }

        return $this->organizations;
    }

    /**
     * @param ArrayCollection $organizations
     */
    public function setOrganizations($organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }


    public function getPersonsWithRole($role, $deep = true)
    {
        if ($this->_cachePersonsByRole === null) {
            $this->_cachePersonsByRole = [];
            /** @var ActivityPerson $activityPerson */
            foreach ($this->getPersons() as $activityPerson) {
                if (!isset($this->_cachePersonsByRole[$activityPerson->getRole()])) {
                    $this->_cachePersonsByRole[$activityPerson->getRole()] = [];
                }
                $this->_cachePersonsByRole[$activityPerson->getRole()][] = $activityPerson;
            }
            if ($this->getProject()) {
                foreach ($this->getProject()->getPersons() as $member) {
                    $this->_cachePersonsByRole[$member->getRole()][] = $member;
                }
            }
        }
        if (isset($this->_cachePersonsByRole[$role])) {
            return $this->_cachePersonsByRole[$role];
        } else {
            return [];
        }
    }

    private $_cacheOrganizationsByRole;

    public function getOrganizationsWithRole($role, $deep = true)
    {
        if ($this->_cacheOrganizationsByRole === null) {
            $this->_cacheOrganizationsByRole = [];
            /** @var ActivityPerson $activityPerson */
            foreach ($this->getOrganizations() as $relation) {
                if (!isset($this->_cacheOrganizationsByRole[$relation->getRole()])) {
                    $this->_cacheOrganizationsByRole[$relation->getRole()] = [];
                }
                $this->_cacheOrganizationsByRole[$relation->getRole()][] = $relation;
            }
            if ($this->getProject()) {
                foreach ($this->getProject()->getOrganizations() as $partner) {
                    $this->_cacheOrganizationsByRole[$partner->getRole()][] = $partner;
                }
            }
        }
        if (isset($this->_cacheOrganizationsByRole[$role])) {
            return $this->_cacheOrganizationsByRole[$role];
        } else {
            return [];
        }
    }

    /**
     * @param ContractDocument $document
     * @return $this
     */
    public function addDocument(ContractDocument $document)
    {
        $this->documents->add($document);

        return $this;
    }

    /**
     * @param ContractDocument $document
     * @return $this
     */
    public function removeDocument(ContractDocument $document)
    {
        $this->documents->removeElement($document);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return string
     */
    public function getNoteFinanciere()
    {
        return $this->noteFinanciere;
    }

    /**
     * @param string $noteFinanciere
     */
    public function setNoteFinanciere($noteFinanciere)
    {
        $this->noteFinanciere = $noteFinanciere;

        return $this;
    }

    /**
     * @return float
     */
    public function getAssietteSubventionnable()
    {
        return $this->assietteSubventionnable;
    }

    /**
     * @param float $assietteSubventionnable
     */
    public function setAssietteSubventionnable($assietteSubventionnable)
    {
        $this->assietteSubventionnable = $assietteSubventionnable;

        return $this;
    }


    /**
     * @param ArrayCollection $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    public function __toString()
    {
        return sprintf("[%s%s] %s", $this->getOscarNum(),
            $this->getCodeEOTP() ? ' ~ ' . $this->getCodeEOTP() : '', $this->getLabel());
    }

    public function log()
    {
        return sprintf('[Activity:%s:%s]', $this->getId(), $this->getLabel());
    }

    /**
     * Retourne le chargé de valorisation
     * @return Person|null
     */
    public function getPersonValo()
    {
        $valos = [];
        if ($this->getProject()) {
            $valos[] = $this->getProject()->getPersonValo();
        }

        foreach ($this->getPersonsWithRole(ProjectMember::ROLE_VALO) as $valo) {
            $valos[] = $valo;
        }

        return $valos;
    }

    /**
     * Retourne le/les responsable(s) pour cette activité.
     * @return Person[]
     */
    public function getPersonsInCharge()
    {
        $inCharge = [];
        if ($this->getProject()) {
            //            $inCharge = $this->getProject()->getPersonValo();
            // todo Retourner les responsable du projet
        }

        foreach ($this->getPersonsWithRole(ProjectMember::ROLE_RESPONSABLE) as $person) {
            $inCharge[] = $person;
        }

        return $inCharge;
    }

    public function hasMilestoneAt( DateType $type, \DateTime $date){
        /** @var ActivityDate $milestone */
        foreach ($this->getMilestones() as $milestone ){
            if( $milestone->getDateStart() == $date  && $milestone->getType() == $type ){
                return true;
            }
        }
        return false;
    }

    public function hasPaymentAt( $amount, $datePayment, $datePredicted ){
        /** @var ActivityPayment $payment */
        foreach ($this->getPayments() as $payment ){
            if( $payment->getDatePayment() == $datePayment  && $payment->getAmount() == $amount && $payment->getDatePredicted() == $datePredicted){
                return true;
            }
        }
        return false;
    }

    /**
     * Test si l'organisation est présente sur l'activité de recherche.
     *
     * @param Organization $organization
     * @param null $role
     * @return bool
     */
    public function hasOrganization(
        Organization $organization,
        $role = null,
        $deep = true
    ) {
        $found = false;
        /** @var ActivityOrganization $relation */
        foreach ($this->organizations as $relation) {
            if ($organization == $relation->getOrganization()) {
                if ($role !== null && $relation->getRole() !== $role) {
                    continue;
                }
                $found = true;
            }
        }
        if ($found === false && $deep === true && $this->getProject()) {
            $found = $this->getProject()->hasPartner($organization, $role);
        }

        return $found;
    }

    public function getOrganizationsNonPrimary()
    {
        $bc = $this->getOrganizationsWithRole("cache");

        return [];
    }

    public function getLaboratories($deep = true)
    {
        return $this->getOrganizationsWithRole(Organization::ROLE_LABORATORY, $deep);
    }

    public function getComposanteResponsable($deep = true)
    {
        return $this->getOrganizationsWithRole(Organization::ROLE_COMPOSANTE_RESPONSABLE, $deep);
    }

    public function getComposanteGestion($deep = true)
    {
        return $this->getOrganizationsWithRole(Organization::ROLE_COMPOSANTE_GESTION, $deep);
    }

    public function getStatusLabel()
    {
        return (isset(self::getStatusSelect()[$this->getStatus()]) ?
            self::getStatusSelect()[$this->getStatus()]
            :
            self::getStatusSelect()[self::STATUS_ERROR_STATUS]);
    }

    /**
     * Retourne les données pour la constitution d'un index de recherche.
     */
    public function getCorpus()
    {

    }

    private $_cachedPercentDone = null;
    private $_cachedTodo = null;
    private $_cachedDone = null;

    private $_cachedTodoDone = null;

    protected function getTodoDone(){
        if( $this->_cachedTodoDone === null ){
            $todo = 0.0;
            $tovalidate = 0.0;
            $done = 0.0;
            $percent = 0.0;
            /** @var WorkPackage $workPackage */
            foreach( $this->getWorkPackages() as $workPackage ){
                /** @var WorkPackagePerson $workPackagePerson */
                foreach( $workPackage->getPersons() as $workPackagePerson ){
                    $todo += $workPackagePerson->getDuration();
                }
                /** @var TimeSheet $timeSheet */
                foreach( $workPackage->getTimesheets() as $timeSheet ){
                    if( $timeSheet->getStatus() == TimeSheet::STATUS_TOVALIDATE ){
                        $tovalidate += $timeSheet->getHours();
                    }
                    if( $timeSheet->getStatus() == TimeSheet::STATUS_ACTIVE){
                        $done += $timeSheet->getHours();
                    }
                }
            }
            $this->_cachedTodoDone = [
                'todo' => $todo,
                'tovalidate' => $tovalidate,
                'done' => $done
            ];
            if( $todo > 0 ) {
                $percent = 100 / $todo * $done;
            }
            $this->_cachedTodoDone['percent'] = $percent;
        }
        return $this->_cachedTodoDone;
    }

    public function getPercentDone()
    {
        return $this->getTodoDone()['percent'];
    }
    public function getDone()
    {
        return $this->getTodoDone()['done'];
    }
    public function getTodo()
    {
        return $this->getTodoDone()['todo'];
    }
    public function getTovalidate()
    {
        return $this->getTodoDone()['tovalidate'];
    }

    /**
     * Retourne les données préparées pour le génération des documents
     */
    public function documentDatas(){

        //
        $datas = [
            'id' => $this->getId(),
            'acronym' => $this->getAcronym(),
            'amount' => $this->getAmount(),
            'pfi' => $this->getCodeEOTP(),
            'oscar' => $this->getOscarNum(),
            'montant' => number_format((double)$this->getAmount(), 2, ',', ' ') . $this->getCurrency()->getSymbol(),
            'annee-debut' => $this->getDateStart()->format('Y'),
            'annee-fin' => $this->getDateEnd()->format('Y'),
            'debut' => $this->getDateStart()->format('d/m/Y'),
            'fin' => $this->getDateEnd()->format('d/m/Y'),
            'intitule' => $this->getLabel(),
            'label' => $this->getLabel(),
            'acronym' => $this->getAcronym(),
            'type' => (string)$this->getActivityType(),
        ];

        $sluger = Slugify::create();


        $persons = [];

        foreach ($this->getPersonsDeep() as $personActivity){
            $roleStr = (string)$personActivity->getRoleObj();
            if( !array_key_exists($roleStr, $persons) ){
                $persons[$roleStr] = [];
            }
            $persons[$roleStr][] = (string) $personActivity->getPerson();
        }
        foreach ($persons as $role=>$ps) {
            $slug = $sluger->slugify($role);
            $datas[$slug] = implode(', ', $ps);
            $datas["$slug-list"] = $ps;
        }

        $organizations = [];

        foreach ($this->getOrganizationsDeep() as $organisationActivity){
            $roleStr = (string)$organisationActivity->getRoleObj();
            if( !array_key_exists($roleStr, $organizations) ){
                $organizations[$roleStr] = [];
            }
            $organizations[$roleStr][] = (string) $organisationActivity->getOrganization();
        }

        foreach ($organizations as $role=>$ps) {
            $slug = $sluger->slugify($role);
            $datas[$slug] = implode(', ', $ps);
            $datas["$slug-list"] = $ps;
        }

        $versements = [];

        /** @var ActivityPayment $payment */
        foreach ($this->getPayments() as $payment){
            $versements[] = $payment->getAmount();
            if( !array_key_exists($roleStr, $organizations) ){
                $organizations[$roleStr] = [];
            }
            $organizations[$roleStr][] = (string) $organisationActivity->getOrganization();
        }

        foreach ($organizations as $role=>$ps) {
            $slug = $sluger->slugify($role);
            $datas[$slug] = implode(', ', $ps);
            $datas["$slug-list"] = $ps;
        }

        return $datas;


    }

    public function csv()
    {
        return array(
            'id' => $this->getId(),
            'Acronyme' => $this->getAcronym(),
            'Projet' => $this->getProject() ? $this->getProject()->getLabel() : '',
            'Intitulé' => $this->getLabel(),
            'PFI' => $this->getCodeEOTP(),
            'Date du PFI' => $this->getDateOpened() ? $this->getDateOpened()->format('Y-m-d') : '',
            'Montant' => number_format($this->getAmount(), 2, ',', ' '),
            'numéro SAIC' => $this->getCentaureNumConvention(),
            'numéro oscar' => $this->getOscarNum(),
            'Type' => $this->getActivityType() ? (string)$this->getActivityType() : '',
            'Statut' => Activity::getStatusLabel(),
            'Début' => $this->getDateStart() ? $this->getDateStart()->format('Y-m-d') : '',
            'Fin' => $this->getDateEnd() ? $this->getDateEnd()->format('Y-m-d') : '',
            'Date de signature' => $this->getDateSigned() ? $this->getDateSigned()->format('Y-m-d') : '',
            'versement effectué' =>number_format($this->getTotalPaymentReceived(), 2, ',', ' '),
            'versement prévu' => number_format($this->getTotalPaymentProvided(), 2, ',', ' '),
            'Frais de gestion' => number_format($this->getFraisDeGestion(), 2, ',', ''),
        );
    }

    public static function csvHeaders()
    {
        return array(
            'id',
            'Acronyme',
            'Projet',
            'Intitulé',
            'PFI',
            'Date du PFI',
            'Montant',
            'numéro SAIC',
            'numéro oscar',
            'Type',
            'Statut',
            'Début',
            'Fin',
            'Date de signature',
            'versement effectué',
            'versement prévu',
            'Frais de gestion',
        );
    }

    private $totalPaymentReceived;

    /**
     * Retourne le total des versements perçus.
     *
     * @return number
     */
    public function getTotalPaymentReceived()
    {
        if ($this->totalPaymentReceived === null) {
            $this->totalPaymentReceived = 0.0;
            /** @var ActivityPayment $payment */
            foreach ($this->getPayments() as $payment) {
                if ($payment->getStatus() === ActivityPayment::STATUS_REALISE) {
                    $this->totalPaymentReceived += $payment->getAmount();
                }
            }
        }

        return $this->totalPaymentReceived;
    }

    private $totalPaymentProvided;

    /**
     * Retourne le total des versements prévisionnels.
     *
     * @return float
     */
    public function getTotalPaymentProvided()
    {
        if ($this->totalPaymentProvided === null) {
            $this->totalPaymentProvided = 0.0;
            /** @var ActivityPayment $payment */
            foreach ($this->getPayments() as $payment) {
                if ($payment->getStatus() === ActivityPayment::STATUS_PREVISIONNEL) {
                    $this->totalPaymentProvided += $payment->getAmount();
                }
            }
        }

        return $this->totalPaymentProvided;
    }


    public function toArray($withAssoc = false)
    {
        $out = array(
            'id' => $this->getId(),
            'projectacronym' => $this->getProject() ? $this->getProject()->getAcronym() : '',
            'project' => $this->getProject() ? $this->getProject()->getLabel() : '',
            'label' => $this->getLabel(),
            'PFI' => $this->getCodeEOTP(),
            'dateInit' => $this->getDateOpened() ? $this->getDateOpened()->format('Y-m-d') : '',
            'amount' => $this->getAmount(),
            'numero' => $this->getCentaureNumConvention(),
            'numOscar' => $this->getOscarNum(),
            'typeOscar' => $this->getActivityType() ? (string)$this->getActivityType() : '',
            'statut' => Activity::getStatusLabel(),
            'dateStart' => $this->getDateStart() ? $this->getDateStart()->format('Y-m-d') : '',
            'dateEnd' => $this->getDateEnd() ? $this->getDateEnd()->format('Y-m-d') : '',
            'dateSigned' => $this->getDateSigned() ? $this->getDateSigned()->format('Y-m-d') : '',
            'paymentReceived' => $this->getTotalPaymentReceived(),
            'paymentProvided' => $this->getTotalPaymentProvided(),
        );

        if( $withAssoc === true ){
            $out['persons'] = [];
            foreach ( $this->getPersons() as $personActivity ){
                $out['persons'][] = sprintf('%s (%s)', $personActivity->getPerson(), $personActivity->getRoleObj());
            }
            $out['organizations'] = [];
            foreach ( $this->getOrganizations() as $organizationActivity ){
                $out['organizations'][] = sprintf('%s (%s)', $organizationActivity->getOrganization()->__toString(), $organizationActivity->getRoleObj());
            }
            $out['payments'] = [];
            /** @var ActivityPayment $payment */
            foreach ( $this->getPayments() as $payment ){
                $out['payments'][] = sprintf('%s (%s)', $payment->getAmount(), $payment->getDatePayment() ? $payment->getDatePayment()->format('Ymd') : 'nop');
            }
        }

        return $out;
    }

    public function getResourceId()
    {
        return self::class;
    }

    public function getDateStartStr(){
        if( $this->getDateStart() ){
            return $this->getDateStart()->format('Y-m-d');
        }
        return "";
    }

    public function getDateEndStr(){
        if( $this->getDateEnd() ){
            return $this->getDateEnd()->format('Y-m-d');
        }
        return "";
    }

    public function toJson()
    {
        return [
            'id' => $this->getId(),
            'text' => sprintf("[%s] %s", $this->getOscarNum(), $this->getLabel()),
            'num' => $this->getOscarNum(),
            'label' => $this->getLabel(),
        ];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// COMPUTED
    ///

    public function hasDeclarant( $person ){
        if( !$person ){
            return false;
        }
        if( $person instanceof ActivityPerson || $person instanceof ProjectMember )
            $person = $person->getPerson();

        /** @var WorkPackage $wp */
        foreach( $this->getWorkPackages() as $wp ){
            /** @var WorkPackagePerson $p */
            foreach( $wp->getPersons() as $p ){
                if( $person->getId() == $p->getPerson()->getId() ){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retourne TRUE si l'activité a des déclarants d'identifiés.
     *
     * @return bool
     */
    public function hasDeclarers(){
        /** @var WorkPackage $wp */
        foreach( $this->getWorkPackages() as $wp ){
            foreach( $wp->getPersons() as $p ){
                return true;
            }
        }
        return false;
    }

    /**
     * @return Person[]
     */
    public function getDeclarers(){
        $persons = [];

        /** @var WorkPackage $workPackage */
        foreach ($this->getWorkPackages() as $workPackage){
            /** @var WorkPackagePerson $workPackagePerson */
            foreach ($workPackage->getPersons() as $workPackagePerson) {
                if( !array_key_exists($workPackagePerson->getPerson()->getId(), $persons) ){
                    $persons[$workPackagePerson->getPerson()->getId()] = $workPackagePerson->getPerson();
                }
            }
        }

        return $persons;
    }

    public function getTimesheets(){
        $timesheets = [];
        /** @var WorkPackage $workPackage */
        foreach($this->getWorkPackages() as $workPackage ){
            /** @var TimeSheet $timesheet */
            foreach ($workPackage->getTimesheets() as $timesheet ){
                $timesheets[] = $timesheet;
            }
        }
        return $timesheets;
    }

    public function hasTimesheetsUpForValidation(){
        /** @var TimeSheet $timesheet */
        foreach ($this->getTimesheets() as $timesheet ){
            if( true === $timesheet->isWaitingValidation() ){
                return true;
            }
        }
        return false;
    }

    public function hasTimesheetsUpForValidationAdmin(){
        /** @var TimeSheet $timesheet */
        foreach ($this->getTimesheets() as $timesheet ){
            if( true === $timesheet->isWaitingValidationAdmin() ){
                return true;
            }
        }
        return false;
    }

    public function hasTimesheetsUpForValidationSci(){
        /** @var TimeSheet $timesheet */
        foreach ($this->getTimesheets() as $timesheet ){
            if( true === $timesheet->isWaitingValidationSci() ){
                return true;
            }
        }
        return false;
    }


}
