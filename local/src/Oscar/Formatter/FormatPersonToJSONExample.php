<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/01/20
 * Time: 18:11
 */

namespace Oscar\Formatter;

use Oscar\Entity\Person;

class FormatPersonToJSONExample extends PersonToJsonConnectorFormatter
{
    /**
     * Retourne le rôle correspondant.
     *
     * @param $roleRecu
     * @return mixed|null
     */
    private static function getCorrespondance( $roleRecu ){
        static $correspondances;
        if( $correspondances === null ){
            $correspondances = [
                "Directeur de laboratoire" => "Directeur administratif de composante ",
                "Directeur de composante" => "Directeur administratif de composante "
            ];
        }
        if( array_key_exists($roleRecu, $correspondances) ){
            return $correspondances[$roleRecu];
        } else {
            return null;
        }
    }

    public function format(Person $person)
    {
        $output = parent::format($person);
        $rolesRecus = $output['roles'];
        $rolesReels = [];
        foreach ($rolesRecus as $codeStructure => $roles) {
            $rolesStructure = [];
            foreach ($roles as $roleRecu ){
                if( null != ($roleReel = $this->getCorrespondance($roleRecu)) ){
                    $rolesStructure[] = $roleReel;
                }
            }
            $rolesReels[$codeStructure] = $rolesStructure;
        }
        $output['roles'] = $rolesReels;
        return $output;
    }
}