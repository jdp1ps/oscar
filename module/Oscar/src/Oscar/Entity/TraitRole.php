<?php
namespace Oscar\Entity;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 05/11/15 14:37
 * @copyright Certic (c) 2015
 */
trait TraitRole
{
    /**
     * Est principal
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $main = false;

    /**
     * Intitulé du rôle
     * @ORM\Column(type="string", nullable=true)
     */
    private $role = "";

    /**
     * @var \DateTime Date de début du rôle (infini si null)
     * Intitulé du rôle
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime Date de fin du rôle (infini si null)
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @return mixed
     */
    public function isMain()
    {
        return $this->main;
    }

    /**
     * @param boolean $main
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart( $deep = false )
    {
        if( $deep === true && !$this->dateStart ){
            return $this->getEnroller()->getDateStart();
        }
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateEnd( $deep = false )
    {
        if( $deep === true && !$this->dateEnd ){
            return $this->getEnroller()->getDateEnd();
        }
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Retourne l'object associé (ex : Person/Organization)
     * @return mixed
     */
    abstract function getEnrolled();

    /**
     * Retourne l'object principale 'qui contient' (ex : Activity/Project)
     * @return mixed
     */
    abstract function getEnroller();


    function log(){
        return sprintf("%s (%s) dans %s", $this->getEnrolled()->log(), $this->getRole(), $this->getEnroller()->log());
    }

    /**
     * @param null $at
     * @return bool
     */
    public function isOutOfDate( \DateTime $at = null)
    {
        if( $at === null ){
            $at = new \DateTime();
        }
        return !(($this->getDateStart() === null || $this->getDateStart() <= $at)
            &&
            ($this->getDateEnd() === null || $this->getDateEnd() >= $at));
    }

    /**
     * Si le rôle appartient au passé.
     */
    public function isPast( \DateTime $at = null )
    {
        if( $at === null ){
            $at = new \DateTime();
        }
        return !($this->getDateEnd() === null || $this->getDateEnd() > $at);
    }

    /**
     * Si le rôle appartient au passé.
     */
    public function isFuture( \DateTime $at = null )
    {
        if( $at === null ){
            $at = new \DateTime();
        }
        return !($this->getDateStart() === null || $this->getDateStart() < $at);
    }
}
