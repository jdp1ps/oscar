<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/06/15 13:54
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Oscar\Utils\StringUtils;

/**
 * Class Person
 * @package Oscar\Entity
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Oscar\Entity\PersonRepository")
 */
class Person implements ResourceInterface
{
    use TraitTrackable;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $firstname = "";

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $lastname = "";

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $codeHarpege;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected ?array $centaureId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $codeLdap;

    /**
     * @var string
     * @ORM\Column(type="object", nullable=true)
     */
    protected $connectors;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $email = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $emailPrive = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ldapStatus = null;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ldapSiteLocation = null;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ldapAffectation = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $ldapDisabled;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ldapFinInscription = null;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ladapLogin = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $phone = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $dateSyncLdap = null;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $harpegeINM = null;

    /**
     * Liste des affectations.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ProjectMember", mappedBy="person")
     */
    protected $projectAffectations;

    /**
     * Activités de recherche
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityPerson", mappedBy="person")
     */
    protected $activities;

    /**
     * Activités de recherche
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="NotificationPerson", mappedBy="person")
     */

    protected $notifications;

    /**
     * Organization
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="OrganizationPerson", mappedBy="person")
     */
    protected $organizations;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="TimeSheet", mappedBy="person")
     */
    protected $timesheets;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Activity", mappedBy="validatorsPrj")
     */
    protected $validatorActivitiesPrj;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Activity", mappedBy="validatorsSci")
     */
    protected $validatorActivitiesSci;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Activity", mappedBy="validatorsAdm")
     */
    protected $validatorActivitiesAdm;

    /**
     * @ORM\ManyToMany(targetEntity="Person", inversedBy="timesheetsFor")
     * @ORM\JoinTable(name="timesheetsBy",
     *      joinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="usurpation_person_id", referencedColumnName="id")}
     *      )
     */
    private $timesheetsBy;

    /**
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="timesheetsBy")
     */
    private $timesheetsFor;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="WorkPackagePerson", mappedBy="person")
     */
    protected $workPackages;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $ldapMemberOf;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $scheduleKey;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    protected $customSettings;

    /**
     * @ManyToMany(targetEntity="ContractDocument", mappedBy="persons")
     */
    protected $documents;

    function __construct()
    {
        $this->projectAffectations = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->workPackages = new ArrayCollection();
        $this->timesheets = new ArrayCollection();
        $this->timesheetsBy = new ArrayCollection();
        $this->timesheetsFor = new ArrayCollection();
        $this->validatorActivitiesPrj = new ArrayCollection();
        $this->validatorActivitiesSci = new ArrayCollection();
        $this->validatorActivitiesAdm = new ArrayCollection();
        $this->centaureId = [];
        $this->setDateCreated(new \DateTime());
        $this->documents = new ArrayCollection();
    }

    /**
     * Return TRUE si l'objet a un connector.
     */
    public function isConnected($connectors = null)
    {
        foreach ($this->getConnectors() as $connector => $value) {
            if ($connectors != null && !in_array($connector, $connectors)) {
                continue;
            }
            if ($value) {
                return true;
            }
        }
        return false;
    }

    public function getConnectorsDatasStr()
    {
        $out = [];
        foreach ($this->getConnectors() as $connector => $value) {
            $out[] = sprintf("%s=%s", $connector, $value);
        }
        return implode(', ', $out);
    }

    /**
     * @return mixed
     */
    public function getCustomSettings()
    {
        return $this->customSettings;
    }

    /**
     * @param mixed $customSettings
     */
    public function setCustomSettings($customSettings)
    {
        $this->customSettings = $customSettings;
        return $this;
    }

    public function getCustomSettingsObj()
    {
        return json_decode($this->getCustomSettings(), JSON_OBJECT_AS_ARRAY);
    }

    public function getCustomSettingsKey($key)
    {
        $custom = $this->getCustomSettingsObj();
        if (is_array($custom) && array_key_exists($key, $custom)) {
            return $custom[$key];
        }
        return null;
    }

    public function setCustomSettingsObj($datas)
    {
        $this->setCustomSettings(json_encode($datas));
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTimesheetsBy()
    {
        return $this->timesheetsBy;
    }

    /**
     * @param mixed $timesheetsBy
     */
    public function setTimesheetsBy($timesheetsBy)
    {
        $this->timesheetsBy = $timesheetsBy;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTimesheetsFor()
    {
        return $this->timesheetsFor;
    }

    /**
     * @param mixed $timesheetsFor
     */
    public function setTimesheetsFor($timesheetsFor)
    {
        $this->timesheetsFor = $timesheetsFor;
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function addTimesheetUsurpation(Person $person)
    {
        $this->getTimesheetsBy()->add($person);
//        $person->getTimesheetsFor()->add($this);
        return $this;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function removeTimesheetUsurpation(Person $person)
    {
        $this->getTimesheetsBy()->removeElement($person);
        return $this;
    }

    public function getRolesFromConnector($connectorName)
    {
        return [];
    }

    /**
     * @return array
     */
    public function getLdapMemberOf()
    {
        return $this->ldapMemberOf;
    }

    /**
     * @param array $ldapMemberOf
     */
    public function setLdapMemberOf($ldapMemberOf)
    {
        $this->ldapMemberOf = $ldapMemberOf;
        return $this;
    }

    /**
     * @return string
     */
    public function getScheduleKey()
    {
        return $this->scheduleKey;
    }

    /**
     * @param string $scheduleKey
     */
    public function setScheduleKey($scheduleKey)
    {
        $this->scheduleKey = $scheduleKey;
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

        return $this;
    }

    /**
     * Retourne uniquement les affectations (PersonOrganization)
     * issue de la synchronisation.
     *
     * @return OrganizationPerson[]
     */
    public function getOrganizationsSync(): array
    {
        $syncOrganizations = [];
        /** @var OrganizationPerson $organizationPerson */
        foreach ($this->getOrganizations() as $organizationPerson) {
            if( $organizationPerson->isSync() ){
                $syncOrganizations[] = $organizationPerson;
            }
        }
        return $syncOrganizations;
    }


    public function getLeadedOrganizations()
    {
        $organizations = [];

        /** @var OrganizationPerson $organizationPerson */
        foreach ($this->getOrganizations() as $organizationPerson) {
            if ($organizationPerson->getRoleObj()->isPrincipal()) {
                $organizations[] = $organizationPerson;
            }
        }
        return $organizations;
    }

    public function isLeader()
    {
        return count($this->getLeadedOrganizations()) > 0;
    }

    /**
     * @return ArrayCollection
     */
    public function getOrganizations()
    {
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

    /**
     * @return mixed
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
        if (!$this->hasCentaureId($centaureId)) {
            $this->centaureId[] = $centaureId;
        }

        return $this;
    }

    public function hasCentaureId($centaureId)
    {
        return in_array($centaureId, $this->centaureId);
    }

    /**
     * @return mixed
     */
    public function getHarpegeINM()
    {
        return $this->harpegeINM;
    }

    /**
     * @param mixed $harpegeINM
     */
    public function setHarpegeINM($harpegeINM)
    {
        $this->harpegeINM = $harpegeINM;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateSyncLdap()
    {
        return $this->dateSyncLdap;
    }

    /**
     * @param \DateTime $dateSyncLdap
     */
    public function setDateSyncLdap($dateSyncLdap)
    {
        $this->dateSyncLdap = $dateSyncLdap;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodeLdap()
    {
        return $this->codeLdap;
    }

    /**
     * @param string $codeLdap
     */
    public function setCodeLdap($codeLdap)
    {
        $this->codeLdap = $codeLdap;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getFullname()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodeHarpege()
    {
        return $this->codeHarpege;
    }

    /**
     * @param string $codeHarpege
     */
    public function setCodeHarpege($codeHarpege)
    {
        $this->codeHarpege = $codeHarpege;

        return $this;
    }

    public function getMd5Email(): string
    {
        return md5($this->getEmail());
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return $this
     */
    public function setEmail(?string $email) :self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailPrive()
    {
        return $this->emailPrive;
    }

    /**
     * @param string $emailPrive
     */
    public function setEmailPrive($emailPrive)
    {
        $this->emailPrive = $emailPrive;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProjectAffectations()
    {
        return $this->projectAffectations;
    }

    /**
     * @param ArrayCollection $projectAffectations
     */
    public function setProjectAffectations($projectAffectations)
    {
        $this->projectAffectations = $projectAffectations;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @param ArrayCollection $activities
     */
    public function setActivities($activities)
    {
        $this->activities = $activities;

        return $this;
    }

    /**
     * @return string
     */
    public function getLdapStatus()
    {
        return $this->ldapStatus;
    }

    /**
     * @param string $ldapStatus
     */
    public function setLdapStatus($ldapStatus)
    {
        $this->ldapStatus = $ldapStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getLdapSiteLocation()
    {
        return $this->ldapSiteLocation;
    }

    /**
     * @param string $ldapSiteLocation
     */
    public function setLdapSiteLocation($ldapSiteLocation)
    {
        $this->ldapSiteLocation = $ldapSiteLocation;

        return $this;
    }

    public function isValidator()
    {
        return
            count($this->getValidatorActivitiesPrj()) > 0 ||
            count($this->getValidatorActivitiesSci()) > 0 ||
            count($this->getValidatorActivitiesAdm()) > 0;
    }

    /**
     * @return Activity[]
     */
    public function getValidatorActivitiesPrj()
    {
        return $this->validatorActivitiesPrj;
    }

    /**
     * @return Activity[]
     */
    public function getValidatorActivitiesSci()
    {
        return $this->validatorActivitiesSci;
    }

    /**
     * @return Activity[]
     */
    public function getValidatorActivitiesAdm()
    {
        return $this->validatorActivitiesAdm;
    }


    /**
     * @return string
     */
    public function getLdapAffectation()
    {
        return $this->ldapAffectation;
    }

    /**
     * @param string $ldapAffectation
     */
    public function setLdapAffectation($ldapAffectation)
    {
        $this->ldapAffectation = $ldapAffectation;

        return $this;
    }

    /**
     * @return string
     */
    public function getLdapDisabled()
    {
        return $this->ldapDisabled;
    }

    /**
     * @param string $ldapDisabled
     */
    public function setLdapDisabled($ldapDisabled)
    {
        $this->ldapDisabled = $ldapDisabled;

        return $this;
    }

    public function isLdapActive(): bool
    {
        if ($this->getLdapFinInscription()) {
            if (date('Y-m-d') > $this->getLdapFinInscription()) {
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getLdapFinInscription()
    {
        return $this->ldapFinInscription;
    }

    /**
     * @param string $ldapFinInscription
     */
    public function setLdapFinInscription($ldapFinInscription)
    {
        $this->ldapFinInscription = $ldapFinInscription;

        return $this;
    }

    public function disabledLdapNow()
    {
        return $this->setLdapFinInscription((new \DateTime('-1 day'))->format('Y-m-d'));
    }

    /**
     * @return string
     */
    public function getLadapLogin()
    {
        return $this->ladapLogin;
    }

    /**
     * @param string $ladapLogin
     */
    public function setLadapLogin($ladapLogin)
    {
        $this->ladapLogin = $ladapLogin;

        return $this;
    }

    /**
     * @return array
     */
    public function getConnectors()
    {
        return $this->connectors;
    }

    public function getConnectorID($connectorName)
    {
        $id = null;
        if ($this->connectors && isset($this->connectors[$connectorName])) {
            $id = $this->connectors[$connectorName];
        }
        return $id;
    }

    public function setConnectorID($connectorName, $value)
    {
        $this->connectors[$connectorName] = $value;
        return $this;
    }

    public function setConnector($data)
    {
        $this->connectors = $data;
        return $this;
    }

    //////////////////////////////////////////////////////////


    public function getDisplayName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function getCorpus()
    {
        return sprintf("%s %s", StringUtils::transliterateString($this->getDisplayName()), $this->getEmail());
    }

    public function isDeclarerInActivity(Activity $activity)
    {
        $activity->hasDeclarant($this);
    }


    public function hasDeclarationIn(Activity $activity)
    {
        /** @var TimeSheet $timesheet */
        foreach ($activity->getTimesheets() as $timesheet) {
            if ($timesheet->getPerson() == $this) {
                return true;
            }
        }
        return false;
    }

    public function toArray()
    {
        return array(
            'id'                   => $this->getId(),
            'firstName'            => $this->getFirstname(),
            'lastName'             => $this->getLastname(),
            'displayname'          => $this->getDisplayName(),
            'login'                => $this->getLadapLogin(),
            'label'                => $this->getDisplayName(),
            'text'                 => $this->getDisplayName(),
            'closed'                => !$this->isLdapActive(),
            'email'                => $this->getEmail(),
            'phone'                => $this->getPhone(),
            'mail'                 => $this->getEmail(),
            'mailMd5'              => md5($this->getEmail()),
            'ucbnSiteLocalisation' => $this->getLdapSiteLocation() ? $this->getLdapSiteLocation() : "",
            'affectation'          => $this->getLdapAffectation() ? $this->getLdapAffectation() : ""
        );
    }

    public function toArrayList()
    {
        $datas = $this->toArray();
        $organisations = [];
        /** @var OrganizationPerson $o */
        foreach ($this->getOrganizations() as $o) {
            $organisation = $o->getOrganization();
            $role = (string)$o->getRoleObj();
            if (!array_key_exists($organisation->getId())) {
                $organisations[$organisation->getId()] = [
                    'organisation' => $organisation->displayName(),
                    'roles'        => []
                ];
            }
            if (!in_array($role, $organisations[$organisation->getId()]['roles'])) {
                $organisations[$organisation->getId()]['roles'][] = $role;
            }
        }
        $datas['organisations'] = $organisations;
        return $datas;
    }

    /**
     * SYNC : Supprimer l'ID centaure de la liste.
     *
     * @param $idCentaure
     */
    public function removeCentaureId($idCentaure)
    {
        if (false !== ($key = array_search($idCentaure, $this->centaureId))) {
            unset($this->centaureId[$key]);
        }
    }


    public function mergeTo(Person $person)
    {
        $activititesWithWP = [];

        /** @var ProjectMember $projectMember */
        foreach ($this->getProjectAffectations() as $projectMember) {
            $projectMember->setPerson($person);
        }
        /** @var ActivityPerson $activityperson */
        foreach ($this->getActivities() as $activityperson) {
            $activityperson->setPerson($person);
        }

        /** @var OrganizationPerson $organizationPerson */
        foreach ($this->getOrganizations() as $organizationPerson) {
            $organizationPerson->setPerson($person);
        }

        /** @var WorkPackagePerson $organizationPerson */
        foreach ($this->getWorkPackages() as $workPackage) {
            $workPackage->setPerson($person);
        }
    }

    public function toJson($options = [])
    {
        $json = $this->toArray();
        $json['urlPerson'] = array_key_exists('urlPerson', $options) ? $options['urlPerson'] : false;
        return $json;
    }

    public function log()
    {
        return sprintf('[Person:%s:%s]', $this->getId(), (string)$this);
    }

    public function getResourceId()
    {
        return self::class;
    }

    private $cGetDateCreated;
    private $cGetDateUpdated;

    public function getDateCreatedStr( string $format = 'c' )
    {
        if ($this->cGetDateCreated == null) {
            $this->cGetDateCreated = $this->getDateCreated() ? $this->getDateCreated()->format($format) : "";
        }
        return $this->cGetDateCreated;
    }

    public function getDateUpdatedStr( string $format = 'c' )
    {
        if ($this->cGetDateUpdated == null) {
            $this->cGetDateUpdated = $this->getDateUpdated() ? $this->getDateUpdated()->format($format) : $this->getDateCreatedStr();
        }
        return $this->cGetDateUpdated;
    }

    public function getDateCachedStr( string $format = 'c' )
    {
        return $this->getDateUpdatedStr($format);
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param ContractDocument $document
     * @return Person
     */
    public function addDocument(ContractDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents [] = $document;
            $document->addPerson($this);
        }
        return $this;
    }

    /**
     * @param ContractDocument $document
     * @return $this
     */
    public function removeDocument(ContractDocument $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            $document->removePerson($this);
        }
        return $this;
    }

}
