<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 23/02/16 15:12
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;

/**
 * Fix les contexts pour le contrôle des accès.
 *
 * @package Oscar\Entity
 */
class Contexts
{
    const PROJECT       = Project::class;
    const ACTIVITY      = Activity::class;
    const PERSON        = Person::class;
    const ORGANIZATION  = Organization::class;
    const APPLICATION   = 'Oscar';

    /**
     * Retourne la liste des contextes supportés dans l'application.
     *
     * @return string[]
     */
    public static function getContextsLabeled(){
        return [
            self::APPLICATION =>    'Application',
            self::PROJECT =>        'Projet',
            self::ACTIVITY =>       'Activité',
            self::PERSON =>         'Personne',
            self::ORGANIZATION =>   'Organisation',
        ];
    }
}