<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/06/15 12:01
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class ProjectPartner implements ILoggable
{
    use TraitRole, TraitTrackable;

    /**
     * Projet
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="partners")
     */
    private $project;


    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="projects")
     */
    private $organization;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="OrganizationRole")
     */
    private $roleObj;

    /**
     * @return OrganizationRole
     */
    public function getRoleObj()
    {
        return $this->roleObj;
    }

    /**
     * @param OrganizationRole $roleObj
     */
    public function setRoleObj($roleObj)
    {
        $this->roleObj = $roleObj;

        return $this;
    }

    public function isPrincipal(){
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->isPrincipal();
        }
        return false;
    }

    public function getRole()
    {
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->getLabel();
        }
        return "rôle inconnu";
    }



    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }


    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organisation
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    function getEnrolled()
    {
        return $this->getOrganization();
    }

    function getEnroller()
    {
        return $this->getProject();
    }


    public function toArray()
    {
        return [
            'id'   => $this->getId(),
            'dateStart'         => \Oscar\Utils\DateTimeUtils::toStr($this->getDateStart()),
            'dateEnd'         => \Oscar\Utils\DateTimeUtils::toStr($this->getDateEnd()),
            'role'         => $this->getRole(),
            'object'    => $this->getOrganization()->toArray()
        ];
    }

    function __toString()
    {
        return (string) $this->getOrganization() . '('.$this->getRole().')';
    }

    /**
     * Création d'un nouveau partenaire pour le projet à partir de clui là.
     *
     * @param Organization $organization
     * @param \DateTime $date
     * @return ProjectPartner
     */
    function fusionTo( Organization $organization, \DateTime $date )
    {
        $new = new ProjectPartner();
        $new->setDateStart($date)
            ->setProject($this->getProject())
            ->setOrganization($organization)
            ->setRoleObj($this->getRoleObj());
        return $new;
    }

}