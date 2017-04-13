<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Oscar\Entity;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
trait RoleInTimeTrait {

    /**
     * Début de l'association au projet
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart = null;

    /**
     * Fin de l'association au projet
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd = null;

    /**
     * @return \DateTime
     */
    function getDateStart() {
        return $this->dateStart;
    }

    function isCaduc(){
        return true;
    }
    
    /**
     * @return \DateTime
     */
    function getDateEnd() {
        return $this->dateEnd;
    }

    function setDateStart(\DateTime $dateStart = null) {
        $this->dateStart = $dateStart;
        return $this;
    }

    function setDateEnd(\DateTime $dateEnd = null) {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    public function intersect(IRoleInTime $roleInTime) {
        $aStart = $this->getDateStart() ? $this->getDateStart()->getTimestamp() : PHP_INT_MIN;
        $bStart = $roleInTime->getDateStart() ? $roleInTime->getDateStart()->getTimestamp() : PHP_INT_MIN;
        $aEnd = $this->getDateEnd() ? $this->getDateEnd()->getTimestamp() : PHP_INT_MAX;
        $bEnd = $roleInTime->getDateEnd() ? $roleInTime->getDateEnd()->getTimestamp() : PHP_INT_MAX;
        
        return !($aStart >= $bEnd || $aEnd <= $bStart);
        
    }

    /**
     * Etend le premier role avec le deuxième.
     */
    public function extend(IRoleInTime $roleInTime){
        // Borne de début
        if( $this->getDateStart() !== null &&
                ($roleInTime->getDateStart() === null || $roleInTime->getDateStart() < $this->getDateStart())){
            $this->setDateStart($roleInTime->getDateStart());
        }
        // Borne de fin
        if( $this->getDateEnd() !== null &&
                ($roleInTime->getDateEnd() === null || $roleInTime->getDateEnd() > $this->getDateEnd())){
            $this->setDateEnd($roleInTime->getDateEnd());
        }
        return $this;
    }
}
