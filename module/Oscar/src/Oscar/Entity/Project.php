<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 10:10
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oscar\Utils\StringUtils;
use phpDocumentor\Reflection\Types\Integer;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use ZendTest\Code\Annotation\TestAsset\DoctrineAnnotation;

/**
 * Class Project
 * @ORM\Entity(repositoryClass="ProjectRepository")
 */
class Project implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    private $id;

    /**
     * => CONV_CLEUNIK
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $centaureId;

    /**
     * @Annotation\Options({"code": "Code"})
     * @ORM\Column(type="string", length=48, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $eotp;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $composantePrincipal;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $acronym;

    /**
     * @Annotation\Options({"label": "Label"})
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @Annotation\Options({"label": "Description"})
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateCreated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateUpdated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateValidated;

    /**
     * Liste des contrats de financement pour le projet.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="project")
     */
    protected $grants;

    /**
     * Liste des membres du projet.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ProjectMember", mappedBy="project", cascade={"remove"})
     */
    protected $members;

    /**
     * Liste des partenaires du projet.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ProjectPartner", mappedBy="project", cascade={"remove"})
     */
    protected $partners;

    /**
     * Discipline
     * @var Discipline
     * @ORM\ManyToMany(targetEntity="Discipline")
     */
    protected $disciplines;


    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->dateUpdated = new \DateTime();
        $this->grants = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->partners = new ArrayCollection();
    }


    public function getDateStart()
    {
        $start = null;
        foreach ($this->getActivities() as $activity) {
            if ($activity->getDateStart() === null) {
                continue;
            }

            if ($start === null) {
                $start = $activity->getDateStart();
                continue;
            }

            if ($activity->getDateStart() < $start) {
                $start = $activity->getDateStart();
            }
        }
        return $start;
    }

    public function getDateEnd()
    {
        $end = null;
        foreach ($this->getActivities() as $activity) {
            if ($activity->getDateEnd() === null) {
                continue;
            }

            if ($end === null) {
                $end = $activity->getDateEnd();
                continue;
            }

            if ($activity->getDateEnd() > $end) {
                $end = $activity->getDateEnd();
            }
        }
        return $end;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        foreach ($this->getActivities() as $activity) {
            if ($activity->isActive()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        if ($this->getActivities()->count() == 0) {
            return null;
        }
        $amount = 0.0;
        foreach ($this->getActivities() as $activity) {
            $amount += $activity->getAmount();
        }
        return $amount;
    }

    /**
     * @param \Oscar\Entity\ProjectMember $projectMember
     * @return \Oscar\Entity\Project
     */
    public function addMember(ProjectMember $projectMember)
    {
        $this->members->add($projectMember);
        return $this;
    }

    /**
     * Test si la personne est présente dans le projet entre $start et $end, avec
     * le role si l'argument $role est spécifié.
     *
     * @param Person $person
     * @param null $role
     */
    public function hasPerson(Person $person, $role = null, \DateTime $start = null, \DateTime $end = null)
    {
        foreach ($this->members as $member) {
            /** @var \Oscar\Entity\ProjectMember $member */
            if ($member->getPerson()->getId() === $person->getId()) {
                if ($role === null || $role === $member->getRole()) {
                    return true;
                }
            }
        }
        return false;
    }


    public function activitiesHasPerson(Person $person)
    {
        /** @var Activity $activity */
        foreach ($this->getActivities() as $activity) {
            if ($activity->hasPerson($person)) {
                return true;
            }
        }
        return false;
    }

    public function getRolesPersonne(Person $person, $role)
    {
        $result = [];
        foreach ($this->members as $member) {
            /** @var \Oscar\Entity\ProjectMember $member */
            if ($member->getPerson()->getId() === $person->getId()
                &&
                $member->getRole() === $role) {
                $result[] = $member;
            }
        }
        return $result;
    }

    public function getPersonRoles(Person $person)
    {
        $roles = [];
        foreach ($this->members as $member) {
            /** @var \Oscar\Entity\ProjectMember $member */
            if ($member->getPerson()->getId() === $person->getId()) {
                $roles[] = $member->getRole();
            }
        }
        return $roles;
    }

    public function log()
    {
        return sprintf('[Project:%s:%s]', $this->getId(), (string)$this);
    }


    /**
     * Test si l'organisation est présente dans le projet, avec le role si l'
     * argument $role est spécifié.
     *
     * @param Person $person
     * @param string $role
     */
    public function hasPartner(Organization $organization, $role = null)
    {
        $is = false;
        foreach ($this->partners as $partner) {
            if ($partner->getOrganization()->getId() === $organization->getId() && $partner->getRole() === $role) {
                $is = true;
            }
        }
        return $is;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    private $_partnersByType = null;
    private $_partnersSpecialsTypes = array(
        Organization::ROLE_COMPOSANTE_RESPONSABLE,
        Organization::ROLE_COMPOSANTE_GESTION,
        Organization::ROLE_LABORATORY
    );


    private function getPartnersByType($type, $deep = false)
    {
        $needed = in_array(
            $type,
            $this->_partnersSpecialsTypes
        ) ? $type : 'non-special';
        if ($this->_partnersByType === null) {
            $this->_partnersByType = array();
            foreach ($this->partners as $partner) {
                $key = !in_array(
                    $partner->getRole(),
                    $this->_partnersSpecialsTypes
                ) ? 'non-special' : $partner->getRole();
                if (!isset($this->_partnersByType[$key])) {
                    $this->_partnersByType[$key] = array();
                }
                $this->_partnersByType[$key][] = $partner;
            }
        }

        if (isset($this->_partnersByType[$needed])) {
            return $this->_partnersByType[$needed];
        } else {
            return array();
        }
    }

    public function touch()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * @return ArrayCollection
     */
    public function getPartners($withSpecials = true)
    {
        if ($withSpecials) {
            return $this->partners;
        }

        return $this->getPartnersByType('non-special');
    }

    public function getPersons($deep = false)
    {
        if ($deep) {
            return $this->getPersonsDeep();
        } else {
            return $this->getMembers();
        }
    }

    /**
     * Retourne la liste des personnes ayant le rôle.
     * @param string $role
     */
    public function getPersonsRoled(array $roles)
    {
        $persons = [];
        foreach ($this->getPersons() as $p) {
            if (in_array($p->getRole(), $roles)) {
                $persons[] = $p;
            }
        }
        return $persons;
    }

    public function getPersonsDeep()
    {
        $persons = [];
        foreach ($this->getMembers(true) as $member) {
            $persons[] = $member;
        }

        /** @var Activity $activity */
        foreach ($this->getActivities() as $activity) {
            foreach ($activity->getPersons() as $person) {
                $persons[] = $person;
            }
        }
        return $persons;
    }

    public function getOrganizations($deep = false)
    {
        $organisations = [];
        foreach ($this->getPartners(true) as $partner) {
            $organisations[] = $partner;
        }
        if ($deep === true) {
            /** @var Activity $activity */
            foreach ($this->getActivities() as $activity) {
                foreach ($activity->getOrganizations() as $organisation) {
                    $organisations[] = $organisation;
                }
            }
        }
        return $organisations;
    }

    public function getOrganisationsDeep()
    {
        return $this->getOrganizations(true);
    }

    public function getComposantesResponsables()
    {
        return $this->getPartnersByType(Organization::ROLE_COMPOSANTE_RESPONSABLE);
    }

    public function getComposantesGestionnaires()
    {
        return $this->getPartnersByType(Organization::ROLE_COMPOSANTE_GESTION);
    }

    public function getLaboratories($deep = false)
    {
        return $this->getPartnersByType(Organization::ROLE_LABORATORY);
    }


//    /**
//     * @return mixed
//     */
//    public function getDiscipline()
//    {
//        return $this->discipline;
//    }

//    /**
//     * @param mixed $discipline
//     */
//    public function setDiscipline($discipline)
//    {
//        $this->discipline = $discipline;
//
//        return $this;
//    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Code des contrats de centaure.
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
        $this->centaureId = $centaureId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcronym()
    {
        return $this->acronym;
    }

    /**
     * @param mixed $acronym
     */
    public function setAcronym($acronym)
    {
        $this->acronym = $acronym;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEotp()
    {
        $pfi = [];
        foreach ($this->getActivities() as $activity) {
            if ($activity->getCodeEOTP() && !in_array($activity->getCodeEOTP(), $pfi)) {
                $pfi[] = $activity->getCodeEOTP();
            }
        }
        return $pfi;
    }

    /**
     * @param mixed $eotp
     */
    public function setEotp($eotp)
    {
        $this->eotp = $eotp;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getComposantePrincipal()
    {
        return $this->composantePrincipal;
    }

    /**
     * @param mixed $composantePrincipal
     */
    public function setComposantePrincipal($composantePrincipal)
    {
        $this->composantePrincipal = $composantePrincipal;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param mixed $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param mixed $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getDateValidated()
    {
        return $this->dateValidated;
    }

    /**
     * @param mixed $dateValidated
     */
    public function setDateValidated($dateValidated)
    {
        $this->dateValidated = $dateValidated;

        return $this;
    }


    /**
     * @return ArrayCollection
     */
    public function getGrants()
    {
        return $this->grants;
    }

    private $_grantsValidated = null;
    private $_grantsTotal = null;

    public function getGrantsTotal()
    {
        if ($this->_grantsTotal === null) {
            $this->_grantsTotal = 0;
            foreach ($this->getGrantsValid() as $grant) {
                $this->_grantsTotal += $grant->getAmount();
            }
        }
        return $this->_grantsTotal;
    }

    public function getGrantsValid()
    {
        if ($this->_grantsValidated === null) {
            $validated = [];
            /** @var Activity $grant */
            foreach ($this->getGrants() as $grant) {
                if ($grant->getStatus() == 1) {
                    $this->_grantsValidated[] = $grant;
                }
            }
        }
        return $this->_grantsValidated;
    }

    /**
     * @param $code
     * @return Activity|null
     */
    public function getGrantByCode($code)
    {
        foreach ($this->getGrants() as $grant/** @var Activity */) {
            if ($grant->getCode() === $code) {
                return $grant;
            }
        }

        return null;
    }

    /**
     * @param $id centaure
     * @return Activity|null
     */
    public function getGrantByCentaureId($idCentaure)
    {
        foreach ($this->getGrants() as $grant/** @var Activity */) {
            if ($grant->getCentaureId() === $idCentaure) {
                return $grant;
            }
        }

        return null;
    }

    public function addGrant(Activity $grant)
    {
        $grant->setProject($this);
        $this->grants[] = $grant;
    }

    public function getDisplayName()
    {
        return ($this->getCode() ? $this->getCode() . ' : ' : '')
            . $this->getLabel();
    }

    public function userActiveByEmail($email)
    {
        foreach ($this->getMembers() as $member) {
            if ($member->getPerson()->getEmail() === $email && $member->isActive()) {
                return true;
            }
        }
        return false;
    }


    public function toArray()
    {
        $grants = [];
        $members = [];
        $partners = [];

        /** @var \Oscar\Entity\ProjectMember $member * */
        foreach ($this->getMembers() as $member) {
            $members[] = $member->toArray();
        }

        /** @var \Oscar\Entity\ProjectPartner $member * */
        foreach ($this->getPartners() as $partner) {
            $partners[] = $partner->toArray();
        }

        /** @var \Oscar\Entity\Activity $grant * */
        foreach ($this->getGrants() as $grant) {
            $grants[] = $grant->toArray();
        }

        return array(
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'acronym' => $this->getAcronym(),
            'description' => $this->getDescription(),
            'members' => $members,
            'partners' => $partners,
            'grants' => $grants,
        );
    }

    private $_valo;


    private $_pfi;

    public function getPFI()
    {
        if ($this->_pfi === null) {
            $this->_pfi = [];
            /** @var Activity $activity */
            foreach ($this->getActivities() as $activity) {
                if ($activity->getCodeEOTP()) {
                    $this->_pfi[] = $activity->getCodeEOTP();
                }
            }
        }
        return implode(', ', array_unique($this->_pfi));
    }

    /**
     * @return Activity[]
     */
    public function getActivities()
    {
        return $this->getGrants();
    }

    /**
     * Retourne le chargé de valorisation actif pour ce projet.
     *
     * @return null|Person
     */
    public function getPersonValo()
    {
        if ($this->_valo === null) {
            foreach ($this->getMembers() as $member) {
                if ($member->isActive() && $member->getRole() == ProjectMember::ROLE_VALO) {
                    $this->_valo = $member->getPerson();
                    break;
                }
            }
        }
        return $this->_valo;
    }


    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function getCorpus()
    {
        return StringUtils::transliterateString($this->__toString() . ' ' . $this->getDescription());
    }

    public function __toString()
    {
        return ($this->getAcronym() ? $this->getAcronym() . ' ' : '') . $this->getLabel();
    }

    public function getResourceId()
    {
        return self::class;
    }
}
