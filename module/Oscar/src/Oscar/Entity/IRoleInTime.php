<?php
namespace Oscar\Entity;

/**
 * @author jacksay
 */
interface IRoleInTime {
    /**
     * @return \DateTime Date de début.
     */
    public function getDateStart();
    
    /**
     * @param mixed $dateStart
     */
    public function setDateStart( $dateStart );
    
    /**
     * Compare deux IRoleInTime et retourne un booléen s'ils se chevauche.
     * 
     * @param \Oscar\Entity\IRoleInTime $roleInTime
     * @return bool
     */
    public function intersect( IRoleInTime $roleInTime );
    
    /**
     * Etend le premier role avec le deuxième.
     */
    public function extend( IRoleInTime $merged );
}
