<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/11/15 14:45
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relation Activité de recherche <> Organisation
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ActivityOrganization implements ILoggable
{
    use TraitRole, TraitTrackable;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="activities", fetch="EAGER")
     */
    private $organization = "";

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="organizations")
     */
    private $activity = "";

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="OrganizationRole")
     */
    private $roleObj;

    /**
     * @return Role
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

    public function getRole(){
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->getLabel();
        }
        return "Unknow role";
    }

    public function isPrincipal(){
        if( $this->getRoleObj() ){
            return $this->getRoleObj()->isPrincipal();
        }
        return false;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

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

    function getEnrolled()
    {
        return $this->getOrganization();
    }

    function getEnroller()
    {
        return $this->getActivity();
    }


    function fusionTo( Organization $organization, \DateTime $date ){
        $new = new ActivityOrganization();
        $new->setDateStart($date)
            ->setActivity($this->getActivity())
            ->setOrganization($organization)
            ->setRoleObj($this->getRoleObj());
        return $new;
    }
}
