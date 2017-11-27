<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/06/15 13:54
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Utils\StringUtils;
use Zend\Permissions\Acl\Resource\ResourceInterface;

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
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastname;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $codeHarpege;

    /**
     * @var string
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $centaureId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $codeLdap;

    /**
     * @var string
     * @ORM\Column(type="object", nullable=true)
     */
    protected $connectors;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $emailPrive;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ldapStatus;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ldapSiteLocation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ldapAffectation;

    /**
     * @var string
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $ldapDisabled;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ldapFinInscription;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ladapLogin;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateSyncLdap;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    protected $harpegeINM;

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
     * Organization
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="OrganizationPerson", mappedBy="person")
     */
    protected $organizations;

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


    function __construct()    {
        $this->projectAffectations = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->workPackages = new ArrayCollection();
        $this->centaureId = [];
        $this->setDateCreated(new \DateTime());
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



    public function getLeadedOrganizations(){
        $organizations = [];

        /** @var OrganizationPerson $organizationPerson */
        foreach( $this->getOrganizations() as $organizationPerson ){
            if( $organizationPerson->getRole() == 'Responsable' ){
                $organizations[] = $organizations;
            }
        }
        return $organizations;
    }

    public function isLeader(){
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
        if( !$this->hasCentaureId($centaureId) ){
            $this->centaureId[] = $centaureId;
        }

        return $this;
    }

    public function hasCentaureId( $centaureId )
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

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
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

    public function getConnectorID( $connectorName )
    {
        $id = null;
        if( $this->connectors && isset($this->connectors[$connectorName]) ){
            $id = $this->connectors[$connectorName];
        }
        return $id;
    }

    public function setConnectorID( $connectorName, $value )
    {
        $this->connectors[$connectorName] = $value;
        return $this;
    }

    public function setConnector( $data ){
        $this->connectors = $data;
        return $this;
    }

    //////////////////////////////////////////////////////////



    public function getDisplayName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function getCorpus()
    {
        return sprintf("%s %s", StringUtils::transliterateString($this->getDisplayName()), $this->getEmail());
    }

    public function isDeclarerInActivity( Activity $activity )
    {
        $activity->hasDeclarant($this);
    }

    public function toArray()
    {
        return array(
            'id'                    => $this->getId(),
            'firstName'             => $this->getFirstname(),
            'lastName'              => $this->getLastname(),
            'displayname'           => $this->getDisplayName(),
            'text'           => $this->getDisplayName(),
            'email'                 => $this->getEmail(),
            'mail'                  => $this->getEmail(),
            'mailMd5'               => md5($this->getEmail()),
            'ucbnSiteLocalisation'  => $this->getLdapSiteLocation() ?? "",
            'affectation' => $this->getLdapAffectation() ?? ""
        );
    }

    /**
     * SYNC : Supprimer l'ID centaure de la liste.
     *
     * @param $idCentaure
     */
    public function removeCentaureId( $idCentaure )
    {
       if( false !== ($key = array_search($idCentaure, $this->centaureId))){
           unset($this->centaureId[$key]);
       }
    }


    public function mergeTo( Person $person ){
        /** @var ProjectMember $projectMember */
        foreach($this->getProjectAffectations() as $projectMember ){
            $projectMember->setPerson($person);
        }
        /** @var ActivityPerson $activityperson */
        foreach($this->getActivities() as $activityperson ){
            $activityperson->setPerson($person);
        }

        /** @var OrganizationPerson $organizationPerson */
        foreach($this->getOrganizations() as $organizationPerson ){
            $organizationPerson->setPerson($person);
        }
    }

    public function toJson( $options = []){
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
}
