<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Privilege;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Formatter\TimesheetsMonthFormatter;
use Oscar\Provider\Privileges;
use Oscar\Service\TimesheetService;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Router\Http\Method;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class TimesheetController, fournit l'API de communication pour soumettre, et
 * consulter les déclarations d'heures dans Oscar.
 *
 * @package Oscar\Controller
 */
class TimesheetController extends AbstractOscarController
{
    public function validationActivityAction(){

        // Récupération de l'activité
        $activityId = $this->params()->fromRoute('idactivity');
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

        if( !$activity )
            return $this->getResponseInternalError(sprintf("L'activités '%s' n'existe pas", $activityId));

        $method = $this->getHttpXMethod();
        $currentPerson = $this->getCurrentPerson();

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        if( $this->isAjax() ){
            switch ($method) {
                case 'POST':
                    $this->getLogger()->debug(print_r($_POST, true));

                    $validationPeriodId = (int) $this->params()->fromPost('validationperiod_id');
                    $action             = $this->params()->fromPost('action');

                    // Récupération de la période
                    $validationPeriod = $this->getEntityManager()->getRepository(ValidationPeriod::class)->find($validationPeriodId);
                    if( !$validationPeriod ){
                        return $this->getResponseInternalError('Aucune procédure de validation en cours disponible pour cette période');
                    }

                    if( $action == "valid-prj" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour valider la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {

                            if( $timesheetService->validationProject($validationPeriod, $currentPerson) ){
                                return $this->getResponseOk("La période a bien été validée au niveau projet");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de validation pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }

                    if( $action == "valid-sci" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour valider scientifiquement la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {

                            if( $timesheetService->validationSci($validationPeriod, $currentPerson) ){
                                return $this->getResponseOk("La période a bien été validée scientifiquement");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de validation pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }

                    if( $action == "valid-adm" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour valider administrativement la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {

                            if( $timesheetService->validationAdm($validationPeriod, $currentPerson) ){
                                return $this->getResponseOk("La période a bien été validée administrativement");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de validation pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }

                    if( $action == "reject-prj" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour valider la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {
                            $message = $this->params()->fromPost('message');
                            if( !$message ){
                                throw new \Exception('Vous devez renseigner une raison au rejet.');
                            }
                            if( $timesheetService->rejectPrj($validationPeriod, $currentPerson, $message) ){
                                return $this->getResponseOk("La période a bien été rejetée");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de rejet pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }

                    if( $action == "reject-sci" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour rejeter scientifiquement la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {
                            $message = $this->params()->fromPost('message');
                            if( !$message ){
                                throw new \Exception('Vous devez renseigner une raison au rejet.');
                            }
                            if( $timesheetService->rejectSci($validationPeriod, $currentPerson, $message) ){
                                return $this->getResponseOk("La période a bien été rejetée");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de rejet scientifique pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }

                    if( $action == "reject-adm" ){
                        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity) ){
                            $this->getResponseUnauthorized("Vous ne disposez pas des droits pour rejeter administrativement la déclaration");
                        }
                        $error = 'Procédure de validation obsolète (VID: ' . $validationPeriodId . ')';
                        try {
                            $message = $this->params()->fromPost('message');
                            if( !$message ){
                                throw new \Exception('Vous devez renseigner une raison au rejet.');
                            }
                            if( $timesheetService->rejectAdm($validationPeriod, $currentPerson, $message) ){
                                return $this->getResponseOk("La période a bien été rejetée");
                            }
                        } catch ( \Exception $e ){
                            $error = "Erreur de rejet administratif pour la période $validationPeriodId : " . $e->getMessage();
                        }

                        return $this->getResponseInternalError($error);
                    }



                    return $this->getResponseNotImplemented("Cette fonctionnalité n'est pas encore disponible");
                default:
                    return $this->getResponseBadRequest("Méthode non disponible");
            }
        }




        //
        $validationPeriods = $timesheetService->getValidationPeriodsActivity($activity);

        $periods = [];

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $periodInfos = $timesheetService->getTimesheetsForValidationPeriod($validationPeriod);
            $activity = null;
            if( $validationPeriod->isActivityValidation() ){
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($validationPeriod->getObjectId());
            }

            $validablePrj = $validationPeriod->getStatus() == ValidationPeriod::STATUS_STEP1 && $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY, $activity);
            $validableSci = $validationPeriod->getStatus() == ValidationPeriod::STATUS_STEP2 && $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
            $validableAdm = $validationPeriod->getStatus() == ValidationPeriod::STATUS_STEP3 && $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity);

            $periodInfos['validable_prj'] = $validablePrj;
            $periodInfos['validable_sci'] = $validableSci;
            $periodInfos['validable_adm'] = $validableAdm;

            $periodKey = sprintf('%s-%s', $validationPeriod->getYear(), $validationPeriod->getMonth());
            if( !array_key_exists($periodKey, $periods) ){
                $periods[$periodKey] = [];
            }

            $periods[$periodKey][] = $periodInfos;
        }

        return [
            'activity' => $activity,
            'periods' => $periods
        ];
    }

    public function validationHWPPersonAction(){


        $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM);
        $person = $this->getEntityManager()->getRepository(Person::class)->find($this->params()->fromRoute('idperson'));
        if( !$person ){
            return $this->getResponseBadRequest("Personne introuvable");
        }

        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            $serviceTimesheet = $this->getTimesheetService();

            switch ($method) {
                case 'GET' :
                    return $this->ajaxResponse($serviceTimesheet->getDatasOutOfWorkPackageToValidate($person));
                    break;

                case 'POST' :
                    $action = $this->params()->fromPost('action');
                    if( !in_array($action, ['valid', 'reject']) ){
                        return $this->getResponseBadRequest("Mauvaise requête !");
                    }
                    $periodId = $this->params()->fromPost('period_id');
                    $message = $this->params()->fromPost('message', '');
                    try {
                        $period = $this->getTimesheetService()->getValidationPeriod($periodId);
                        if( !$period ){
                            throw new OscarException("Impossible de charger la période.");
                        }
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    break;

                default:
                    return $this->getResponseNotFound();
            }
        }

        return [
            'person' => $person
        ];
    }


    /**
     * Exportation et visualisation des feuilles de temps.
     *
     * @return array
     * @throws OscarException
     */
    public function excelAction(){
        $activityId     = $this->params()->fromQuery('activityid');

        /** @var Activity $activity */
        $activity           = null;
        $action             = $this->params()->fromQuery('action');
        $period             = $this->params()->fromQuery('period', null);
        $personIdQuery      = $this->params()->fromQuery('personid', null );

        //
        $currentPersonId    = $this->getCurrentPerson() ? $this->getCurrentPerson()->getId() : -1;

        if( $activityId ){
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);
        }

        if( $personIdQuery != null && $currentPersonId != $personIdQuery ){
            $this->getOscarUserContext()->check(Privileges::PERSON_VIEW_TIMESHEET, $activity);
            $personId = $personIdQuery;
        } else {
            $personId = $currentPersonId;
        }

        /** @var Person $person */
        $person = $this->getEntityManager()->getRepository(Person::class)->find($personId);

        if( !$person ){
            throw new OscarException("Impossible de trouver la personne.");
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');


        if( $action == "csv" ){
            if( !$activity ){
                $this->getResponseBadRequest("Impossible de trouver l'activité");
            }
            $datas = $timesheetService->getPersonTimesheetsCSV($person, $activity, false);
            $filename = $activity->getAcronym() . '-' . $activity->getOscarNum().'-'.$person->getLadapLogin().'.csv';



            $handler = fopen('/tmp/' . $filename, 'w');

             /** @var ActivityPayment $payment */
            foreach ($datas as $line) {
                fputcsv($handler, $line);
            }

            fclose($handler);

            header('Content-Disposition: attachment; filename='.$filename);
            header('Content-Length: ' . filesize('/tmp/' . $filename));
            header('Content-type: plain/text');

            die(file_get_contents('/tmp/' . $filename));
        }
        $datas = $timesheetService->getPersonTimesheets($person, false, $period, $activity);


        if( $action == "export" ){

            $modele = $this->getConfiguration('oscar.paths.timesheet_modele');
            if( !$modele ){
                throw new OscarException("Impossible de charger le modèle de feuille de temps");
            }

            $fmt = new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::FULL,
                'Europe/Paris',
                \IntlDateFormatter::GREGORIAN,
                'd MMMM Y');

            /** @var Activity $activity */
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

            $cellDays = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U', 'V', 'W','X','Y','Z','AA', 'AB', 'AC', 'AD', 'AE','AF','AG'];
            $lineWpFormula = '=SUM(C%s:AG%s)';
            foreach( $datas[$activityId]['timesheets'] as $period=>$timesheetsPeriod ){
                $lineWpStart = 10;
                $lineWpCurent = $lineWpStart;
                $lineWpCount = 0;
                $spreadsheet = \PHPExcel_IOFactory::load($modele);


                $spreadsheet->getActiveSheet()->setCellValue('A1', $activity->getLabel());
                $spreadsheet->getActiveSheet()->setCellValue('C3', (string)$person);
                $spreadsheet->getActiveSheet()->setCellValue('C4', 'Université de Caen');
                $spreadsheet->getActiveSheet()->setCellValue('C5', $activity->getAcronym());

                $spreadsheet->getActiveSheet()->setCellValue('U3', $fmt->format($activity->getDateStart()));
                $spreadsheet->getActiveSheet()->setCellValue('U4', $fmt->format($activity->getDateEnd()));
                $spreadsheet->getActiveSheet()->setCellValue('U5', $activity->getOscarNum());
                $spreadsheet->getActiveSheet()->setCellValue('U6', $activity->getCodeEOTP());

                $spreadsheet->getActiveSheet()->setCellValue('C6', $period);
                $spreadsheet->getActiveSheet()->setCellValue('B8', $period);
                $spreadsheet->getActiveSheet()->setCellValue('A9', "UE - " . $activity->getAcronym());

                foreach ($timesheetsPeriod as $workpackage=>$timesheetsWorkpackage) {
                    if( $workpackage == "unvalidate" || $workpackage == "total" )
                        continue;

                    $rowNum = $lineWpStart + $lineWpCount;
                    $spreadsheet->getActiveSheet()->insertNewRowBefore(($rowNum + 1));
                    for( $i=0; $i<count($cellDays); $i++ ){
                        $day = $i+1;
                        $cellIndex = $cellDays[$i].$rowNum;
                        $totalDay = array_key_exists($day, $timesheetsWorkpackage) ? $timesheetsWorkpackage[$day] : 0.0;
                        $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $totalDay);
                    }
                    $spreadsheet->getActiveSheet()->setCellValue('A'.$rowNum, "");
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$rowNum, $workpackage);
                    $spreadsheet->getActiveSheet()->setCellValue('AH'.$rowNum, sprintf($lineWpFormula, $rowNum, $rowNum));
                    $lineWpCount++;
                }

                $rowNum = $lineWpStart + $lineWpCount + 1;

                for( $i=0; $i<count($cellDays); $i++ ){
                    $day = $i+1;
                    $cellIndex = $cellDays[$i].$rowNum;
                    $sum = "=SUM(" . $cellDays[$i] .'10:' .$cellDays[$i].($rowNum-1) .')';

                    $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $sum);
                }

                $spreadsheet->getActiveSheet()->setCellValue('AH'.$rowNum, sprintf('=SUM(AH10:AH%s)', ($rowNum-1)));

                $edited = \PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

                $name = ($person->getLadapLogin())."-" . $period . ".xls";
                $filepath = '/tmp/'. $name;

                $edited->save($filepath);

                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $name . "\"");
                die(readfile($filepath));
            }
            die();
        }

        return [
            "datas" => $datas,
            "person" => $person,

        ];
    }

    /**
     * Retourne la liste des déclarants actifs
     */
    public function declarersAction()
    {
        die("Fonctionnalité désactivée");

        $datas = $this->getServiceLocator()->get('TimesheetService')->getDeclarers();
        return [
            'datas' => $datas
        ];
    }

    /**
     * Affiche les déclarations par structure
     */
    public function organizationLeaderAction(){

        /** @var TimesheetService $timesheetsService */
        $timesheetsService = $this->getServiceLocator()->get('TimesheetService');

        $organizationId = $this->params()->fromQuery('id', null);

        $organizationsTimesheets = [];

        if( $organizationId != null ){
            if( !( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM)
                || $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI)
                )) {
                throw new UnAuthorizedException("Droits insuffisants");
            }
            $organisation = $this->getEntityManager()->getRepository(Organization::class)->find($organizationId);
            $label = (string) $organisation;
            $roleOk = null;

            foreach ($this->getOscarUserContext()->getDbUser()->getRoles() as $role){
                if( $role->hasPrivilege(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI) || $role->hasPrivilege(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM) ){
                  $roleOk = $role;
                }
            }
            $organizationsTimesheets[] = [
              'organization' => $organisation,
              'role' => $roleOk
            ];
        } else {
            /** @var OrganizationPerson $organizationPerson */
            foreach ($this->getCurrentPerson()->getLeadedOrganizations() as $organizationPerson ){

                if(
                    $organizationPerson->getRoleObj()->hasPrivilege(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI) ||
                    $organizationPerson->getRoleObj()->hasPrivilege(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM)
                ){
                    $organizationsTimesheets[] = [
                        'organization' => $organizationPerson->getOrganization(),
                        'role' => $organizationPerson->getRoleObj()
                    ];
                }
            }
        }


        $method = $this->getHttpXMethod();

        switch( $method ){
            case 'GET':
                if( $this->isAjax() ){
                    $result = [];

                    $urlActivity = [];
                    $urlProject = [];



                    /** @var OrganizationPerson $organizationPerson */
                    foreach ($organizationsTimesheets as $data ){

                        $organisationDatas = [
                          'label' => (string)$data['organization'],
                          'role'  => (string)$data['role'],
                          'timesheets' => []
                        ];


                        /** @var TimeSheet $timesheet */
                        foreach ($timesheetsService->getTimesheetToValidateByOrganization( $data['organization']) as $timesheet ){

                            $activity = $timesheet->getActivity();
                            $project = $activity->getProject();
                            $activityId = $activity->getId();
                            $projectId = $project->getId();

                            if( !array_key_exists($activityId, $urlActivity) ){
                                if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_SHOW, $activity) ){
                                    $urlActivity[$activityId] = $this->url()->fromRoute('contract/show', ['id' => $activityId]);
                                } else {
                                    $urlActivity[$activityId] = null;
                                }
                            }

                            if( !array_key_exists($projectId, $urlProject) ){
                                if( $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_SHOW, $activity->get) ){
                                    $urlProject[$projectId] = $this->url()->fromRoute('project/show', ['id' => $projectId]);
                                } else {
                                    $urlProject[$projectId] = null;
                                }
                            }

                            $json = $timesheet->toJson();
                            $json = array_merge($json, $timesheetsService->resolveTimeSheetCredentials($timesheet));
                            $json['url_activity'] = $urlActivity[$activityId];
                            $json['url_project'] = $urlProject[$projectId];
                            $organisationDatas['timesheets'][] = $json;
                            //$this->
                        }
                        $result[] = $organisationDatas;
                    }
                    return $this->ajaxResponse($result);
                } else {
                    return [];
                }
            case "POST":
                $action = $this->params()->fromPost('action', null);
                $timesheetId = $this->params()->fromPost('timesheet_id', null);
                if( !$timesheetId ){
                    return $this->getResponseInternalError("Erreur, impossible d'identifier le créneau à modifier");
                } else {
                    try {
                        $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
                    } catch( \Exception $e ){
                        return $this->getResponseNotFound('Impossible de trouver ce créneau.');
                    }
                }
                switch ( $action ){
                    case 'validateadm':
                        $timesheet = $timesheetsService->validateAdmin([$timesheet->toJson()], $this->getCurrentPerson());
                        return $this->ajaxResponse($timesheet);

                    case 'validatesci':
                        $timesheet = $timesheetsService->validateSci([$timesheet->toJson()], $this->getCurrentPerson());
                        return $this->ajaxResponse($timesheet);

                    case 'rejectadm':
                        $datas = [
                            'id' => $timesheet->getId(),
                            'rejectedAdminComment' => $this->params()->fromPost('rejectComment')
                        ];
                        $timesheet = $timesheetsService->rejectAdmin([$datas], $this->getCurrentPerson());
                        return $this->ajaxResponse($timesheet);

                    case 'rejectsci':
                        $datas = [
                            'id' => $timesheet->getId(),
                            'rejectedSciComment' => $this->params()->fromPost('rejectComment')
                        ];
                        $timesheet = $timesheetsService->rejectSci([$datas], $this->getCurrentPerson());
                        return $this->ajaxResponse($timesheet);
                }
            default :
                return $this->getResponseBadRequest();
        }
    }

    public function usurpationAction()
    {
        die("DESACTIVE");
//        // Méthode réél
//        $method = $this->getHttpXMethod();
//
//        /** @var Activity $activity */
//        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));
//
//        $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_USURPATION, $activity);
//
//        $person = $this->getEntityManager()->getRepository(Person::class)->find($this->params()->fromRoute('idperson'));
//
//        if (!$activity) {
//            return $this->getResponseNotFound("L'activité n'existe pas");
//        }
//
//        if (!$person) {
//            return $this->getResponseNotFound("La person %s n'existe pas");
//        }
//
//        /** @var TimesheetService $timeSheetService */
//        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');
//
//        $timesheets = [];
//
//        if ($method == 'GET') {
//            $timesheets = $timeSheetService->allByPerson($person, $person);
//        }
//
//        if ($method == 'POST') {
//
//            $datas = json_decode($this->getRequest()->getPost()['events'], true);
//            $action = $this->getRequest()->getPost()['do'];
//
//            if ($action == 'send') {
//                $timesheets = $timeSheetService->send($datas, $person);
//            } else if ( $action ){
//                if( !in_array($action, ['validatesci', 'validateadm', 'send', 'rejectsci','rejectadm'])) {
//                    return $this->getResponseBadRequest('Opération inconnue !');
//                }
//
//                foreach ($datas as $data) {
//                    if ($data['id'] && $data['id'] != 'null') {
//                        /** @var TimeSheet $timeSheet */
//                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
//                        $activity = null ;
//                        if( $timeSheet->getActivity() ){
//                            $activity = $timeSheet->getActivity();
//                        }
//                        elseif ($timeSheet->getWorkpackage()){
//                            $activity = $timeSheet->getWorkpackage()->getActivity();
//                        }
//                        if( !$activity ){
//                            // todo Ajouter un warning
//                            continue;
//                        }
//
//                        $timesheets = array_merge($timesheets, $this->processAction(
//                            $action, [$data], $timeSheetService, $activity, $person)
//                        );
//                    }
//                }
//            } else {
//                $timesheets = $timeSheetService->create($datas, $person);
//            }
//        }
//
//        if ($method == 'DELETE') {
//            $timesheetId = $this->params()->fromQuery('timesheet', null);
//
//            // UID de l'ICS
//            $icsUid = $this->params()->fromQuery('icsuid', null);
//
//            if ($timesheetId) {
//                if ($timeSheetService->delete($timesheetId,
//                    $this->getCurrentPerson())
//                ) {
//                    return $this->getResponseOk('Créneaux supprimé');
//                }
//            }
//            elseif ($icsUid) {
//                $this->getLogger()->info("Suppression d'un ICS");
//                try {
//                    $warnings = $timeSheetService->deleteIcsFileUid($icsUid, $person);
//                    $this->getLogger()->info("Suppression OK warn = " . count($warnings));
//                    foreach ($warnings as $w){
//                        $this->getLogger()->info($w);
//                    }
//                    return $this->getResponseOk(json_encode($warnings));
//                }
//                catch (\Exception $e ){
//                    $this->getLogger()->err($e->getMessage());
//                    return $this->getResponseInternalError("Impossible de supprimer ce calendrier : " . $e->getMessage());
//                }
//            }
//
//            return $this->getResponseBadRequest("Impossible de supprimer le créneau : créneau inconnu");
//        }
//
//        $wpDeclarants = [];
//        /** @var WorkPackage $workPackage */
//        foreach($activity->getWorkPackages() as $workPackage ){
//            if( $workPackage->hasPerson($person) ){
//                $wpDeclarants[$workPackage->getId()] = $workPackage;
//            }
//        }
//
//        foreach($timesheets as &$timesheet ){
//            if( !($timesheet['activity_id'] == null || $timesheet['activity_id'] == $activity->getId()) ){
//                $timesheet['credentials']['editable'] = false;
//                $timesheet['credentials']['deletable'] = false;
//                $timesheet['credentials']['sendable'] = false;
//            }
//        }
//
//        $datasView = [
//            'wpDeclarants' => $wpDeclarants,
//            'timesheets' => $timesheets,
//            'person' => $person,
//            'activity' => $activity,
//        ];
//
//        if ($this->getRequest()->isXmlHttpRequest()) {
//            $response = new JsonModel($datasView);
//            $response->setTerminal(true);
//
//            return $response;
//        }
//
//        return $datasView;
    }



    /**
     * Déclaration des heures.
     */
    public function declarationAction()
    {
        return $this->getResponseDeprecated("Cette fonctionnalité n'existe plus");
    }


    protected function getQueryData()
    {
        $datas = [
            'timesheetId' => $this->params()->fromPost('tsid', null),
            'time' => $this->params()->fromPost('t', 0),
            'start' => $this->params()->fromPost('s', null),
            'end' => $this->params()->fromPost('e', null),
            'date' => $this->params()->fromPost('d', null),
            'workpackage' => $this->params()->fromPost('wp', null),
        ];

        if ($datas['timesheetId']) {
            $datas['timesheet'] = $this->getEntityManager()->getRepository(TimeSheet::class)->find($datas['timesheetId']);
        }
    }

    /**
     * @return TimesheetService
     */
    protected function getTimesheetService(){
        return $this->getServiceLocator()->get('TimesheetService');
    }

    /**
     * todo Valide une déclaration de temps
     */
    public function validateTimesheetAction()
    {
        // Méthode effective
        $method = $this->getHttpXMethod();

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));

        if (!$activity) {
            return $this->getResponseNotFound(sprintf("L'activité %s n'existe pas",
                $activity));
        }

        if( !($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity)
            ||
            $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity)) ){
            throw new UnAuthorizedException();
        }

        $timeSheetService = $this->getTimesheetService();

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($method == 'GET') {

                // Récupération des déclarations pour cette activité
                $timesheets = [];

                foreach( $timeSheetService->allByActivity($activity) as $timesheet ){
                    // On désactive les fonctionnalités de déclaration
                    $timesheet['credentials']['deletable'] = false;
                    $timesheet['credentials']['editable'] = false;
                    $timesheet['credentials']['sendable'] = false;
                    $timesheets[] = $timesheet;
                }

                $response = new JsonModel([
                    'timesheets' => $timesheets
                ]);

                $response->setTerminal(true);

                return $response;

            } else {
                /** @var Request $request */
                $request = $this->getRequest();

                if ($method == 'POST') {
                    $events = json_decode($request->getPost('events', '[]'), true);
                    if (count($events) == 1 && $events[0]['id'] == 'null') {
                            throw new OscarException('A refactorer !');
                    } else {
                        $action = $this->getRequest()->getPost()['do'];
                        $events = json_decode($this->getRequest()->getPost()['events'], true);
                        $timesheets = $this->processAction($action, $events, $timeSheetService, $activity, $this->getOscarUserContext()->getCurrentPerson());
                        $response = new JsonModel([
                            'timesheets' => $timesheets
                        ]);
                        $response->setTerminal(true);
                        return $response;
                    }
                }
                return $this->getResponseBadRequest();
            }
        }

        $declarants = [];
        /** @var WorkPackage $workpackage */
        foreach ($activity->getWorkPackages() as $workpackage) {
            /** @var WorkPackagePerson $workpackageperson */
            foreach ($workpackage->getPersons() as $workpackageperson) {
                if( !array_key_exists($workpackageperson->getPerson()->getId(), $declarants) ){
                    $declarants[$workpackageperson->getPerson()->getId()] = $workpackageperson;
                }
            }
        }

        return [
            'activity' => $activity,
            'declarants' => $declarants
        ];
    }

    /**
     * API de validation des heures.
     *
     * @return Response
     */
    public function validatorAPIAction(){
        return $this->getResponseNotImplemented();
    }

    protected function getOthersWP(){
        return $this->getTimesheetService()->getOthersWP();
    }

    /**
     * API REST pour déclarer des heures.
     *
     */
    public function declarantAPIAction()
    {
        /** @var Person $person */
        $person = $this->getCurrentPerson();

        // JOUR
        $datas = json_decode($this->params()->fromPost('timesheets'));
        $action = $this->params()->fromPost('action');

        // Réenvois d'une déclaration
        if( $action == "fix-reject" ){
            return $this->getResponseNotImplemented("Le FIX des rejets n'est pas encore implanté.");
        }


        $timesheets = [];

        //
        if( count($datas) == 0 ){
            return $this->getResponseBadRequest("Aucun créneau à traiter");
        }

        foreach ($datas as $data){
            $day = new \DateTime($data->day);
            $dayBase = $day->format('Y-m-d'). ' %s:%s:00';
            $wpId = $data->wpId;
            $code = $data->code;
            $duration = (int)$data->duration;
            $heures = floor($duration/60);
            $minutes = $duration - ($heures*60);
            $status = TimeSheet::STATUS_DRAFT;
            $comment = $data->comment;
            $timesheetId = $data->id;
            $start = new \DateTime(sprintf($dayBase, 8, 0));
            $end = new \DateTime(sprintf($dayBase, 8+$heures, $minutes));
            $month = (integer)$start->format('m');
            $year = (integer)$start->format('Y');

            $wp = null;
            $label = "error";
            $validationPeriod = null;

            // Récupération des validations en cours pour la période
            $validationPeriods = $this->getTimesheetService()->getValidationPeriods($year, $month, $person);

            // Créneau "Hors-lot"
            if( !$data->wpId ){
                $other = $this->getOthersWP()[$data->code];

                // Récupération de la procédure de validation en cours
                $validationPeriod = $this->getTimesheetService()->getValidationPeriosOutOfWorkpackageAt($person, $year, $month, $label);

                $status = TimeSheet::STATUS_INFO;
                $label = $code;

                // On contrôle le code
                if( !$other ){
                    $msg = sprintf("Ce type de créneau '%s' n'est pas pris en charge dans cette version", $code);
                    $this->getLogger()->error($msg);
                    return $this->getResponseBadRequest($msg);
                }
            }
            // Créneau sur un lot
            else {
                /** @var WorkPackage $wp */
                $wp = $this->getEntityManager()->getRepository(WorkPackage::class)->find($wpId);
                if( !$wp ){
                    $msg = sprintf("Le lot de travail 'N°%s' n'existe plus", $wpId);
                    $this->getLogger()->error($msg);
                    return $this->getResponseInternalError($msg);
                }

                $validationPeriod = $this->getTimesheetService()->getValidationPeriodActivityAt($wp->getActivity(), $person, $year, $month);
                $label = (string)$wp;
            }

            // Il y'a une/plusieurs procédures de validation sur cette période ?
            if( count($validationPeriods) > 0 ){

                $hasConflict = false;
                $unauthorizedError = "Vous ne pouvez pas modifier une déclaration en cours de validation.";


                /** @var ValidationPeriod $vp */
                foreach ($validationPeriods as $vp) {
                    if( $vp->getStatus() == ValidationPeriod::STATUS_CONFLICT ){
                        $unauthorizedError = "Vous ne pouvez pas modifier une déclaration en cours de validation. Seul les créneaux marqués en erreur peuvent être modifiés";
                        $hasConflict = true;
                    }
                }

                // Aucune procédure de validation spécifique pour ce type de créneau
                if( !$validationPeriod || !$validationPeriod->hasConflict() ){
                    return $this->getResponseBadRequest($unauthorizedError);
                }
            }

            if( $timesheetId ){
                $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
                $credentials = $this->getTimesheetService()->resolveTimeSheetCredentials($timesheet, $person);

                if( !$credentials['editable'] ){
                    return $this->getResponseInternalError("Vous n'avez pas le droit de modififier le créneau");
                }

                if( !$timesheet ){
                    return $this->getResponseInternalError("Ce créneau n'existe plus.");
                }

            } else {
                $timesheet = new TimeSheet();
                $this->getEntityManager()->persist($timesheet);
            }


            $timesheet->setWorkpackage($wp)
                ->setComment($comment)
                ->setDateFrom($start)
                ->setDateTo($end)
                ->setLabel($label)
                ->setStatus($status)
                ->setPerson($person);

            $this->getLogger()->debug("Traitement du créneau " . $timesheet . ' créneaux');
            $timesheets[] = $timesheet;

        }

        $this->getEntityManager()->flush($timesheets);

        return $this->getResponseOk();

    }


    /**
     * RÉCUPÉRATION des DÉCLARATIONS.
     *
     * @return array|Response
     * @throws \Exception
     */
    public function declarantAction(){

        if( !$this->getOscarUserContext()->getCurrentPerson() ){
            return $this->getResponseInternalError("Vous avez été déconnecté de Oscar");
        }

        $output = [];

        $method = $this->getHttpXMethod();

        // Durée d'un jour "normal"
        $dayLength = 37/5;

        // Durée d'une journée de travail maximum légale
        $maxDays = 10.0;

        /** @var Person $currentPerson */
        $currentPerson = $this->getCurrentPerson();

        // Données sur la période
        $today = new \DateTime();
        $year = (int)$this->params()->fromQuery('year', $today->format('Y'));
        $month = (int)$this->params()->fromQuery('month', $today->format('m'));
        $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));

        // Nombre de jours dans le mois
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));
        $from = $dateRef->format('Y-m-01');
        $to = $dateRef->format('Y-m-' . $nbr);

        $periodLength = 0.0;
        $periodOpened = 0.0;
        $periodDeclarations = 0.0;

        $tsFrom = (new \DateTime($from))->getTimestamp();
        $tsTo = (new \DateTime($to))->getTimestamp();
        $tsNow = $today->getTimestamp();

        $output['periodFutur'] = false;
        $output['periodCurrent'] = false;
        $output['periodFinished'] = false;
        $output['periodInfos']    = "Ce mois est hors des limite de l'espace-temps";

        $output['submitable'] = false;
        $output['submitableInfos'] = 'Vous ne pouvez pas soumettre cette période pour une raison inconnue';

        $output['editable'] = false;
        $output['editableInfos'] = 'Vous ne pouvez pas éditer cette période pour une raison inconnue';

        $output['hasUnsend'] = false;



        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        // Récupération des validations pour cette période
        $periodValidations = $timesheetService->getPeriodValidation($currentPerson, $month, $year);

        $isPeriodSend = count($periodValidations);


        $periodValidationsDt = [];
        /** @var ValidationPeriod $periodValidation */
        foreach ($periodValidations as $periodValidation) {
            $data = $periodValidation->json();

            if( $periodValidation->getObjectId() > 0 ){
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($periodValidation->getObjectId());
                $label = (string) $activity;
                $data['label'] = "Déclaration pour " . $label;
            }
            $periodValidationsDt[] = $data;
        }

        $output['periodsValidations'] = $periodValidationsDt;

        // Mois non-terminé ou futur
        if( $tsFrom > $tsNow ){
            $output['periodFutur']      = true;;
            $output['periodInfos']    = "Ce mois n'a pas encore commencé";
            $output['submitableInfos']  = "Ce mois n'a pas encore commencé";
            $output['editableInfos']  = "Ce mois n'a pas encore commencé";
        }

        // Mois passé
        if( $tsTo <= $tsNow ){

            $output['periodFinished'] = true;
            $output['periodInfos']    = "Ce mois est terminé.";

            // Le mois a une/plusieurs procédure de validation en cours
            if( count($periodValidations) ){
                // TODO Afficher l'état générale de la validation
                $output['submitable'] = false;
                $output['submitableInfos'] = "Vous avez déja envoyé cette période pour validation";
                $output['editable'] = false;
                $output['editableInfos']  = "Vous avez déja envoyé cette période pour validation";
            } else {
                $output['submitable'] = true;
                $output['submitableInfos'] = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
                $output['editable'] = true;
                $output['editableInfos']  = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
            }
        }

        if( $tsFrom <= $tsNow && $tsTo >= $tsNow ){
            $output['periodCurrent'] = true;
            $output['periodInfos'] = "Mois en cours";
            $output['submitable'] = false;
            $output['submitableInfos'] = "mois en cours, vous ne pouvez soumettre vos déclarations qu'à la fin du mois";
            $output['editable'] = true;
            $output['editableInfos']  = "Mois en cours, vous pouvez commencer à compléter votre déclaration";
        }

        $output['person'] = (string) $currentPerson;
        $output['person_id'] = $currentPerson->getId();

        $output['days'] = [];

        // Mois / année (numérique)
        $output['month'] = $month;
        $output['year'] = $year;

        // Dates complètes
        $output['from'] = $from;
        $output['to'] = $to;
        //$output

        // Total du temps cummulé dans le mois
        $output['total'] = 0.0;

        // Durée d'une journée "normale"
        $output['daylength'] = $dayLength;

        // Nombre de jour
        $output['dayNbr'] = $nbr;

        // Limite légale d'une semaine de travail
        $output['weekExcess'] = 44.0;

        // Limite légale du mois
        $output['monthExcess'] = 176.0;

        // Limite légale du jour
        $output['dayExcess'] = $maxDays;



        // Récupération des lots
        $availableWorkPackages = $this->getActivityService()->getWorkPackagePersonPeriod($currentPerson, $year, $month);

        // Liste des activités du mois
        $activities = [];

        // Liste des lots du mois
        $workPackages = [];



        $lockedDays = $timesheetService->getLockedDays($year, $month);

        /** @var WorkPackagePerson $workPackagePerson */
        foreach ($availableWorkPackages as $workPackagePerson){
            $workPackage = $workPackagePerson->getWorkPackage();
            $activity = $workPackage->getActivity();

            /** @var ValidationPeriod $period */
            $period = $timesheetService->getValidationPeriodActivityAt($workPackage->getActivity(), $currentPerson, $year, $month);

            if( !array_key_exists($activity->getId()) ){
                $activities[$activity->getId()] = [
                    'id' => $activity->getId(),
                    'acronym' => $activity->getAcronym(),
                    'project' => (string)$activity->getProject(),
                    'project_id' => $activity->getProject()->getId(),
                    'label' => $activity->getLabel(),
                    'total' => 0.0,
                    'validation_state' => $period ? $period->json() : null
                ];
            }

            $validationUp = false;

            if( $isPeriodSend ){
                $validationUp = $period && $period->isOpenForDeclaration();
            } else {
                $validationUp = true;
            }

            $workPackages[$workPackage->getId()] = [
                'id' => $workPackage->getId(),
                'from' => DateTimeUtils::toStr($workPackage->getDateStart(), 'Y-m-d'),
                'to' => DateTimeUtils::toStr($workPackage->getDateEnd(), 'Y-m-d'),
                'label' => $workPackage->getLabel(),
                'code' => $workPackage->getCode(),
                'acronym' => $activity->getAcronym(),
                'description' => 'Lot dans ' . (string)$activity,
                'project' => (string)$activity->getProject(),
                'project_id' => $activity->getProject()->getId(),
                'activity' => (string)$activity,
                'activity_id' => $activity->getId(),
                'hours' => $workPackagePerson->getDuration(),
                'total' => 0.0,
                'validation_up' => $validationUp, //!$period || $period->isOpenForDeclaration(),
                'validation_state' => $period ? $period->json() : null
            ];
        }

        $output['activities'] = $activities;
        $output['workPackages'] = $workPackages;

        $timesheets = $timesheetService->getTimesheetsPersonPeriod($currentPerson, $from, $to);

        for( $i = 1; $i<=$nbr; $i++ ){
            $data = sprintf('%s-%s-%s', $year, $month, $i);
            $day = new \DateTime($data);

            // Journée vérrouillé (future/passée)
            $locked = false;

            // Journée fermée (feriès)
            $close = false;

            $editable = true;

            $lockedReason = "";

            $futur = $day->getTimestamp() > $today->getTimestamp();
            if( $futur ){
                $locked = true;
                $lockedReason = "Vous ne pouvez pas anticiper une déclaration";
                $editable = false;
            }

            $wDay = $day->format('w');

            $weekend = $wDay == 6 || $wDay == 0;
            if( $weekend ){
                $locked = true;
                $close = true;
                $editable = false;
                $lockedReason = "Vous ne pouvez pas déclarer le weekend";
            }

            if( array_key_exists($data, $lockedDays) ){
                $locked = true;
                $close = true;
                $editable = false;
                $lockedReason = $lockedDays[$data];
            }

            if( $output['editable'] == false ){

                $editable = false;
                $lockedReason = $output['editableInfos'];
            }




            if( !$close )
                $periodLength += $dayLength;

            if( !($locked || $close) ){
                $periodOpened += $dayLength;
            }

            $output['days'][$i] = [
                'date' => $data,
                'i' => $i,
                'day' => $day->format('N'),
                'week' => $day->format('W'),
                'data' => $data,
                'label' => $day->format('d'),
                'weekend' => $weekend,
                'editable' => $editable,
                'declarations' => [],
                'infos' => [],
                'duration' => 0.0,
                'conges' => [],
                'training' => [],
                'teaching' => [],
                'sickleave' => [],
                'absent' => [],
                'research' => [],

                // Durée "normale" de la journée
                'dayLength' => $dayLength,
                'maxDay' => $maxDays,
                'locked' => $locked,
                'closed' => $close,
                'lockedReason' => $lockedReason
            ];
        }

        $periodsValidations = [];

        /** @var TimeSheet $t */
        foreach ($timesheets as $t) {

            $dayTimesheet = (int)($t->getDateFrom()->format('d'));
            $period = $t->getDateFrom()->format('Y-m');


            if (!$t->getActivity()) {
                $periodKey = ValidationPeriod::GROUP_OTHER;


                    $datas = [
                        'id' => $t->getId(),
                        'label' => $t->getLabel(),
                        'description' => $t->getComment(),
                        'duration' => $t->getDuration(),
                        'status_id' => $t->getStatus(),
                        'status' => 'locked'
                    ];

                    switch( $t->getLabel() ){
                        case 'cours' :
                        case 'enseignement' :
                        case 'teaching' :
                            $output['days'][$dayTimesheet]['teaching'][] = $datas;
                            break;

                        case 'formation' :
                        case 'learning' :
                        case 'training' :
                            $output['days'][$dayTimesheet]['training'][] = $datas;
                            break;

                        case 'vacation' :
                        case 'conges' :
                            $output['days'][$dayTimesheet]['conges'][] = $datas;
                            break;

                        case 'sickleave' :
                            $output['days'][$dayTimesheet]['sickleave'][] = $datas;
                            break;

                        case 'absent' :
                            $output['days'][$dayTimesheet]['absent'][] = $datas;
                            break;

                        case 'research' :
                            $output['days'][$dayTimesheet]['research'][] = $datas;
                            break;

                        default:
                            $output['days'][$dayTimesheet]['infos'][] = $datas;
                    }
                    $output['days'][$dayTimesheet]['duration'] += (float)$t->getDuration();
                    $output['total'] += (float)$t->getDuration();


                continue;
            } else {
                $periodKey = "activity-" . $t->getActivity()->getId();
            }

            $projectAcronym = $t->getActivity()->getAcronym();
            $project = $t->getActivity()->getProject();
            $activity = $t->getActivity();
            $activityCode = $activity->getOscarNum();
            $workpackage = $t->getWorkpackage();
            $wpCode = $workpackage->getCode();
            $periodDeclarations += (float)$t->getDuration();

            $output['days'][$dayTimesheet]['duration'] += (float)$t->getDuration();
            $output['total'] += (float)$t->getDuration();
            $output['activities'][$activity->getId()]['total'] += $t->getDuration();
            $output['workPackages'][$workpackage->getId()]['total'] += $t->getDuration();

            $others = $this->getOthersWP();

            foreach ($others as $key=>&$datas) {
                $period = $this->getTimesheetService()->getValidationPeriosOutOfWorkpackageAt($currentPerson, $year, $month, $key);

                if( $isPeriodSend ){
                    $validationUp = $period && $period->isOpenForDeclaration();
                } else {
                    $validationUp = true;
                }

                $datas['validation_state'] = $period ? $period->json() : null;
                $datas['validation_up'] = $validationUp;
            }
            $output['otherWP'] = $others;

            if( $t->getStatus() == TimeSheet::STATUS_DRAFT ){
                $output['hasUnsend'] = true;
            }

            $output['days'][$dayTimesheet]['declarations'][] = [
                'id' => $t->getId(),
                'credentials' => $timesheetService->resolveTimeSheetCredentials($t),
                'validations' => $timesheetService->resolveTimeSheetValidation($t),
                'label' => $t->getLabel(),
                'comment' => $t->getComment(),
                'activity' => (string) $activity,
                'activity_code' => $activityCode,
                'acronym' => $projectAcronym,
                'project' => (string)$project,
                'status_id' => $t->getStatus(),
                'status' => 'locked',
                'wpCode' => $wpCode,
                'duration' => (float)$t->getDuration(),
                'wp_id' => $t->getWorkpackage()->getId(),
            ];
        }

        $output['periodLength'] = $periodLength;
        $output['periodOpened'] = $periodOpened;
        $output['periodDeclarations'] = $periodDeclarations;

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        if( $this->isAjax() ) {
            switch ($method) {
                case 'GET' :
                    return $this->ajaxResponse($output);
                    break;

                case 'POST' :
                    $action = $this->params()->fromPost('action', 'send');
                    if( $action == 'resend' ){
                        $periodId = $this->params()->fromPost('period_id');
                        $period = $this->getEntityManager()->getRepository(ValidationPeriod::class)->find($periodId);
                        if( $period->getDeclarer() == $currentPerson ){
                            try {
                                $timesheetService->reSendValidation($period);
                                return $this->getResponseOk();
                            } catch (\Exception $e ){
                                return $this->getResponseInternalError(sprintf('Impossible de réenvoyer la déclaration : %s', $e->getMessage()));
                            }
                        } else {
                            return $this->getResponseUnauthorized("Cette déclaration n'est pas la votre.");
                        }
                    }

                    $datas = json_decode($this->params()->fromPost('datas'));
                    $this->getLogger()->debug("Données recues : " . print_r($datas, true));
                    if( !$datas ){
                        return $this->getResponseBadRequest('Problème de transmission des données');
                    }

                    if( !$datas->from || !$datas->to ){
                        return $this->getResponseInternalError("La période soumise est incomplète");
                    }

                    $this->getLogger()->debug("##### ENVOI DES DECLARATIONS");

                    try {
                        $from = new \DateTime($datas->from);
                        $to = new \DateTime($datas->to);
                        $timesheetService->sendPeriod($from, $to, $currentPerson);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError('Erreur de soumission de la période : ' . $e->getMessage());
                    }
                    return $this->getResponseNotImplemented("Pas encore fait");
                    break;

                case 'DELETE' :
                    try {
                        $idsCreneaux = explode(',', $this->params()->fromQuery('id'));
                        $timesheetService->delete($idsCreneaux, $currentPerson);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }

                    break;
            }
        }

        return $output;
    }


    /**
     * @param $action
     * @param $events
     * @param TimesheetService $timeSheetService
     * @param $activity
     * @param $person
     * @return Response
     * @throws OscarException
     */
    protected function processAction( $action, $events, $timeSheetService, $activity, $person ){
        return $this->getResponseDeprecated("Cette fonctionnalité a été désactivée");
    }

    /**
     * todo Rejète une déclaration de temps
     */
    public function invalidateTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }
}