<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 23/02/16 15:18
 * @copyright Certic (c) 2016
 */

namespace Oscar\Entity;


class Actions
{
    const PROJECT_CREATE            = 'PROJECT_CREATE';
    const PROJECT_EDIT              = 'PROJECT_EDIT';
    const PROJECT_PERSON_ADD        = 'PROJECT_PERSON_ADD';
    const PROJECT_ORGANIZATION_ADD  = 'PROJECT_ORGANIZATION_ADD';
    const PROJECT_ACTIVITY_ADD      = 'PROJECT_ACTIVITY_ADD';

    /**
     * Retourne la liste des contextes supportés dans l'application.
     *
     * @return string[]
     */
    public static function getActionsLabeled(){
        return [
            self::PROJECT_CREATE                => 'Créer un projet',
            self::PROJECT_EDIT                  => 'Éditer un projet',
            self::PROJECT_PERSON_ADD            => 'Ajouter un membre (personne) au projet',
            self::PROJECT_ORGANIZATION_ADD      => 'Ajouter un partenaire (organisation) au projet',
            self::PROJECT_ACTIVITY_ADD          => 'Ajouter une activité au projet',
        ];
    }
}