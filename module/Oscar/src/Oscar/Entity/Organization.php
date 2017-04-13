<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/06/15 10:54
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Connector\IConnectedObject;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class Organization
 * @package Oscar\Entity
 * @ORM\Entity(repositoryClass="Oscar\Entity\OrganizationRepository")
 */
class Organization implements ResourceInterface, IConnectedObject
{
    use TraitTrackable;

    const TYPE_LABORATORY = 'Laboratoire';
    const TYPE_SOCIETE = 'Société';
    const TYPE_FONDATION = 'Fondation';
    const TYPE_ASSOCIATION = 'Association';
    const TYPE_GIE = 'Groupement d\'intérêt économique';
    const TYPE_COLLECTIVITE = 'Collectivité territoriale';
    const TYPE_ETABLISSEMENT = 'Établissement publique';
    const TYPE_INSTITUTION = 'Institution';
    const TYPE_COMPOSANTE = 'Composante';
    const TYPE_INCONNU = 'Inconnue';

    public static function getTypes(){
        static $types;
        if( !$types ){
            $types = [
                self::TYPE_INCONNU,
                self::TYPE_COMPOSANTE,
                self::TYPE_LABORATORY,
                self::TYPE_SOCIETE,
                self::TYPE_ASSOCIATION,
                self::TYPE_GIE,
                self::TYPE_COLLECTIVITE,
                self::TYPE_ETABLISSEMENT,
                self::TYPE_INSTITUTION,
            ];
        }
        return $types;
    }
    public static function getTypesSelect(){
        return self::getTypes();
    }

    const ROLE_CLIENT = "Client";
    const ROLE_CONSEILLER = "Conseiller";
    const ROLE_COORDINATEUR = "Coordinateur";
    const ROLE_CO_CONTRACTANT = "Co-contractant";
    const ROLE_FINANCEUR = "Financeur";
    const ROLE_CO_FINANCEUR = "Co-financeur";
    const ROLE_LICENCIE = "Licencié";
    const ROLE_SCIENTIFIQUE = "Scientifique";
    const ROLE_SCIENTIFIQUE_R = "Scientifique avec reversement";
    const ROLE_LABORATORY = "Laboratoire";
    const ROLE_COMPOSANTE_GESTION = "Tutelle de gestion";
    const ROLE_COMPOSANTE_RESPONSABLE = "Composante responsable";

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $centaureId;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $fullName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $street1;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $street2;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $street3;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * Liste des affectations.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ProjectPartner", mappedBy="organization")
     */
    protected $projects;

    /**
     * Liste des affectations.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityOrganization", mappedBy="organization")
     */
    protected $activities;

    /**
     * Personnes
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="OrganizationPerson", mappedBy="organization")
     */
    protected $persons;

    /**
     * Code LDAP
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ldapSupannCodeEntite;

    /**
     * Pays.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * Identifiant obtenu depuis SIFAC.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sifacId;

    /**
     * Code pays (XX)
     *
     * @var string
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $codePays;

    /**
     * N° de SIRET
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $siret;

    /**
     * Boîte postale
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $bp;

    /**
     * Type d'organisation.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * Groupe (SIFAC).
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sifacGroup;

    /**
     * Groupe ID (SIFAC).
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sifacGroupId;

    /**
     * Numéro de TVA CA (SIFAC)
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    protected $numTVACA;

    /**
     * @var string
     * @ORM\Column(type="object", nullable=true)
     */
    protected $connectors;



    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->setDateCreated(new \DateTime());
    }

    public function isClose()
    {
        return $this->getDateEnd() && $this->getDateEnd() <= new \DateTime();
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

    /**
     * @return ArrayCollection
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param ArrayCollection $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;

        return $this;
    }

    public function hasPerson( Person $person, $role = false ){
        /** @var OrganizationPerson $member */
        foreach( $this->getPersons() as $member ){
            if( $member->getPerson()->getId() == $person->getId() ){
                if( $role === false || $role == $member->getRole() ){
                    return true;
                }
            }
        }
        return false;
    }


    public function hasResponsable( Person $person )
    {
        $responsables = [ProjectMember::ROLE_RESPONSABLE];
        /** @var OrganizationPerson $member */
        foreach( $this->getPersons() as $member ){

            if( $member->getPerson()->getId() == $person->getId() && in_array($member->getRole(), $responsables ) ){
                return true;
            }
        }
        return false;
    }

    public function touch()
    {
        $this->setDateUpdated(new \DateTime());
    }


    /**
     * @return string
     */
    public function getCentaureId()
    {
        return $this->centaureId;
    }

    /**
     * @param string $centaureId
     */
    public function setCentaureId($centaureId)
    {
        $this->centaureId = $centaureId;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     */
    public function setDateStart($dateStart)
    {
        if( $dateStart == '' ){
            $dateStart = null;
        }
        elseif( is_string($dateStart) ){
            $dateStart = new \DateTime($dateStart);
        }
        $this->dateStart = $dateStart;

        return $this;
    }



    /**
     * @return mixed
     */
    public function getDateEnd()
    {

        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        if( $dateEnd == '' ){
            $dateEnd = null;
        }
        elseif( is_string($dateEnd) ){
            $dateEnd = new \DateTime($dateEnd);
        }
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;

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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;

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
     * @return string
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * @param string $street1
     */
    public function setStreet1($street1)
    {
        $this->street1 = $street1;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param string $street2
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet3()
    {
        return $this->street3;
    }

    /**
     * @param string $street3
     */
    public function setStreet3($street3)
    {
        $this->street3 = $street3;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

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
     * @return string
     */
    public function getSifacId()
    {
        return $this->sifacId;
    }

    /**
     * @param string $sifacId
     */
    public function setSifacId($sifacId)
    {
        $this->sifacId = $sifacId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodePays()
    {
        return $this->codePays;
    }

    /**
     * @param string $codePays
     */
    public function setCodePays($codePays)
    {
        $this->codePays = $codePays;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * @param string $siret
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * @return string
     */
    public function getBp()
    {
        return $this->bp;
    }

    /**
     * @param string $bp
     */
    public function setBp($bp)
    {
        $this->bp = $bp;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSifacGroup()
    {
        return $this->sifacGroup;
    }

    /**
     * @param string $sifacGroup
     */
    public function setSifacGroup($sifacGroup)
    {
        $this->sifacGroup = $sifacGroup;

        return $this;
    }

    /**
     * @return string
     */
    public function getSifacGroupId()
    {
        return $this->sifacGroupId;
    }

    /**
     * @param string $sifacGroupId
     */
    public function setSifacGroupId($sifacGroupId)
    {
        $this->sifacGroupId = $sifacGroupId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumTVACA()
    {
        return $this->numTVACA;
    }

    /**
     * @param mixed $numTVACA
     */
    public function setNumTVACA($numTVACA)
    {
        $this->numTVACA = $numTVACA;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param ArrayCollection $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;

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
    public function getLdapSupannCodeEntite()
    {
        return $this->ldapSupannCodeEntite;
    }

    /**
     * @param string $ldapSupannCodeEntite
     */
    public function setLdapSupannCodeEntite($ldapSupannCodeEntite)
    {
        $this->ldapSupannCodeEntite = $ldapSupannCodeEntite;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }



    public function displayName( $displayClose = false )
    {
        return
            ($this->isClose() && $displayClose ? '!FERME! ' : '').
            ($this->getCode() ? '['.$this->getCode().'] ' : '').
            ($this->getShortName() ? $this->getShortName() : $this->getFullName()).
            ($this->getCity() ? '(' . $this->getCity() . ')' : '');
    }

    public function displayNameLong()
    {
        return
            ($this->getShortName() ? $this->getShortName() : '').
            ($this->getFullName() && $this->getShortName() ? ', ' : '').
            ($this->getFullName() ? $this->getFullName() : '').
            ($this->getCity() ? ' (' . $this->getCity() . ')' : '');
    }


    public function __toString()
    {
        return $this->displayName();
    }

    public function getCorpus()
    {
        return $this->__toString();
    }
    
    public function toArray()
    {
        return [
            'id'    => $this->getId(),
            'label' => $this->displayName(true),
            'closed' => $this->isClose()
        ];
    }

    public function log()
    {
        return sprintf('[Organization:%s:%s]', $this->getId(), (string)$this);
    }

    public function getResourceId()
    {
        return self::class;
    }
}
