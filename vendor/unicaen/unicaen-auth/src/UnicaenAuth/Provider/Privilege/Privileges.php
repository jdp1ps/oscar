<?php

namespace UnicaenAuth\Provider\Privilege;

use UnicaenAuth\Entity\Db\Privilege;

class Privileges {

    const DROIT_ROLE_VISUALISATION          = 'droit-role-visualisation';
    const DROIT_ROLE_EDITION                = 'droit-role-edition';
    const DROIT_PRIVILEGE_VISUALISATION     = 'droit-privilege-visualisation';
    const DROIT_PRIVILEGE_EDITION           = 'droit-privilege-edition';



    /**
     * Retourne le nom de la ressource associée au privilège donné
     *
     * @param $privilege
     *
     * @return string
     */
    public static function getResourceId( $privilege )
    {
        if ($privilege instanceof Privilege){
            $privilege = $privilege->getFullCode();
        }
        return 'privilege/'.$privilege;
    }

}