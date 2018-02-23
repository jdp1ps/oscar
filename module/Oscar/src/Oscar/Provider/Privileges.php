<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 03/03/16
 * Time: 10:40
 */

namespace Oscar\Provider;


use Oscar\Entity\Privilege;

class Privileges extends \UnicaenAuth\Provider\Privilege\Privileges
{

    const ACTIVITY_EXPORT = 'ACTIVITY-EXPORT';
    const ACTIVITY_PAYMENT_MANAGE = 'ACTIVITY-PAYMENT_MANAGE';
    const ACTIVITY_ORGANIZATION_MANAGE = 'ACTIVITY-ORGANIZATION_MANAGE';
    const ACTIVITY_EDIT = 'ACTIVITY-EDIT';
    const ACTIVITY_INDEX = 'ACTIVITY-INDEX';
    const ACTIVITY_SHOW = 'ACTIVITY-SHOW';
    const ACTIVITY_NUMBER = 'ACTIVITY-NUMBER';
    const ACTIVITY_PAYMENT_SHOW = 'ACTIVITY-PAYMENT_SHOW';
    const ACTIVITY_MILESTONE_SHOW = 'ACTIVITY-MILESTONE_SHOW';
    const ACTIVITY_MILESTONE_MANAGE = 'ACTIVITY-MILESTONE_MANAGE';
    const ACTIVITY_MILESTONE_PROGRESSION = 'ACTIVITY-MILESTONE_PROGRESSION';
    const ACTIVITY_DOCUMENT_SHOW = 'ACTIVITY-DOCUMENT_SHOW';
    const ACTIVITY_DOCUMENT_MANAGE = 'ACTIVITY-DOCUMENT_MANAGE';
    const ACTIVITY_DUPLICATE = 'ACTIVITY-DUPLICATE';
    const ACTIVITY_CHANGE_PROJECT = 'ACTIVITY-CHANGE_PROJECT';
    const ACTIVITY_DELETE = 'ACTIVITY-DELETE';
    const ACTIVITY_STATUS_OFF = 'ACTIVITY-STATUS_OFF';
    const ACTIVITY_PERSON_SHOW = 'ACTIVITY-PERSON_SHOW';
    const ACTIVITY_ORGANIZATION_SHOW = 'ACTIVITY-ORGANIZATION_SHOW';
    const ACTIVITY_PERSON_MANAGE = 'ACTIVITY-PERSON_MANAGE';
    const ACTIVITY_WORKPACKAGE_SHOW = 'ACTIVITY-WORKPACKAGE_SHOW';
    const ACTIVITY_WORKPACKAGE_MANAGE = 'ACTIVITY-WORKPACKAGE_MANAGE';
    const ACTIVITY_WORKPACKAGE_COMMIT = 'ACTIVITY-WORKPACKAGE_COMMIT';
    const ACTIVITY_WORKPACKAGE_VALIDATE = 'ACTIVITY-WORKPACKAGE_VALIDATE';
    const ACTIVITY_TIMESHEET_VALIDATE_SCI = 'ACTIVITY-TIMESHEET_VALIDATE_SCI';
    const ACTIVITY_TIMESHEET_VALIDATE_ADM = 'ACTIVITY-TIMESHEET_VALIDATE_ADM';
    const ACTIVITY_TIMESHEET_USURPATION = 'ACTIVITY-TIMESHEET_USURPATION';
    const ACTIVITY_TIMESHEET_VIEW = 'ACTIVITY-TIMESHEET_VIEW';

    const ACTIVITY_NOTIFICATIONS_SHOW = 'ACTIVITY-NOTIFICATIONS_SHOW';
    const ACTIVITY_NOTIFICATIONS_GENERATE = 'ACTIVITY-NOTIFICATIONS_GENERATE';
    const ACTIVITY_PERSON_ACCESS = 'ACTIVITY-PERSON_ACCESS';

    const PROJECT_CREATE = 'PROJECT-CREATE';
    const PROJECT_EDIT = 'PROJECT-EDIT';
    const PROJECT_ACTIVITY_ADD = 'PROJECT-ACTIVITY-ADD';
    const PROJECT_PERSON_MANAGE = 'PROJECT-PERSON_MANAGE';
    const PROJECT_ORGANIZATION_MANAGE = 'PROJECT-ORGANIZATION_MANAGE';
    const PROJECT_DASHBOARD = 'PROJECT-DASHBOARD';
    const PROJECT_INDEX = 'PROJECT-INDEX';
    const PROJECT_SHOW = 'PROJECT-SHOW';
    const PROJECT_PERSON_SHOW = 'PROJECT-PERSON_SHOW';
    const PROJECT_ORGANIZATION_SHOW = 'PROJECT-ORGANIZATION_SHOW';
    const PROJECT_DOCUMENT_SHOW = 'PROJECT-DOCUMENT_SHOW';
    const PROJECT_ACTIVITY_SHOW = 'PROJECT-ACTIVITY_SHOW';

    const PERSON_SHOW = 'PERSON-SHOW';
    const PERSON_EDIT = 'PERSON-EDIT';
    const PERSON_SYNC_LDAP = 'PERSON-SYNC_LDAP';
    const PERSON_PROJECTS = 'PERSON-PROJECTS';
    const PERSON_INFOS_RH = 'PERSON-INFOS_RH';
    const PERSON_INDEX = 'PERSON-INDEX';
    const PERSON_NOTIFICATION_MENU = 'PERSON-NOTIFICATION_MENU';
    const PERSON_VIEW_TIMESHEET = 'PERSON-VIEW_TIMESHEET';

    const ORGANIZATION_SHOW = 'ORGANIZATION-SHOW';
    const ORGANIZATION_EDIT = 'ORGANIZATION-EDIT';
    const ORGANIZATION_SYNC_LDAP = 'ORGANIZATION-SYNC_LDAP';
    const ORGANIZATION_INDEX = 'ORGANIZATION-INDEX';

    const ADMINISTRATIVE_DOCUMENT_INDEX = 'ADMINISTRATIVE-DOCUMENT_INDEX';
    const ADMINISTRATIVE_DOCUMENT_DELETE = 'ADMINISTRATIVE-DOCUMENT_DELETE';
    const ADMINISTRATIVE_DOCUMENT_DOWNLOAD = 'ADMINISTRATIVE-DOCUMENT_DOWNLOAD';
    const ADMINISTRATIVE_DOCUMENT_NEW = 'ADMINISTRATIVE-DOCUMENT_NEW';

    const MAINTENANCE_MENU_ADMIN = 'MAINTENANCE-MENU_ADMIN';
    const MAINTENANCE_CONNECTOR_ACCESS = 'MAINTENANCE-CONNECTOR_ACCESS';
    const MAINTENANCE_NOTIFICATION_PERSON = 'MAINTENANCE-NOTIFICATION_PERSON';
    const MAINTENANCE_DECLARERS_INDEX = "MAINTENANCE-DECLARERS_INDEX";

    const ORGANIZATION_TEST = 'ORGANIZATION-TEST';

    const DROIT_USER_VISUALISATION = 'droit-USER_VISUALISATION';
    const DROIT_USER_EDITION = 'droit-USER_EDITION';
    const DROIT_ROLEORGA_VISUALISATION = 'droit-ROLEORGA_VISUALISATION';
    const DROIT_ROLEORGA_EDITION = 'droit-ROLEORGA_EDITION';


    const PERSON_SHOW_INM = 'PERSON-SYNC_LDA';

    const DEPENSE_SHOW = 'DEPENSE-SHOW';

    public static function getResourceId($privilege)
    {
        if ($privilege instanceof Privilege) {
            $privilege = $privilege->getFullCode();
        }
        return 'privilege/' . $privilege;
    }

    /**
     * Cette méthode retourne la structure attendue des privilèges pour
     * l'installation.
     * @deprecated
     */
    public static function getStructureForCheck()
    {
        return [
            'categories' => [
                1 => [
                    'code' => 'PROJECT',
                    'label' => 'Projet',
                    'privileges' => [

                    ]
                ],
                2 => ['code' => 'ACTIVITY', 'label' => 'Activité de recherche'],
                3 => ['code' => 'PERSON', 'label' => 'Personne'],
                4 => ['code' => 'ORGANIZATION', 'label' => 'Organisation'],
                5 => ['code' => 'DOCUMENT', 'label' => 'Document'],
                6 => ['code' => 'MAINTENANCE', 'label' => 'Maintenance'],
                7 => ['code' => 'droit', 'label' => 'Gestion des droits'],
                8 => ['code' => 'ADMINISTRATIVE', 'label' => 'Informations administratives'],
                9 => ['code' => 'DEPENSE', 'label' => 'Accès aux dépenses'],
            ],

            'privileges' => [

            ]
        ];
    }

}

