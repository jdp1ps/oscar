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
use PhpOffice\PhpWord\Style\Fill;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Router\Http\Method;
use Zend\Validator\Date;
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
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPersonFromRoute(){
        $idPerson = $this->params()->fromRoute('idperson');
        $person = $this->getEntityManager()->getRepository(Person::class)->find($idPerson);
        return $person;
    }

    public function validatePersonPeriodAction(){
        $person = $this->getPersonFromRoute();
        // TODO
        return [
            'person' => $person
        ];
    }

    public function validationActivityAction(){

        // Récupération de l'activité
        $activityId = $this->params()->fromRoute('idactivity');
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

        // Mode de déclaration

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

                        $this->getLogger()->debug("VALIDATION SCIENTIFIQUE");

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

    public function validationActivity2Action(){

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

                        $this->getLogger()->debug("VALIDATION SCIENTIFIQUE");

                        return $this->getResponseDeprecated("En cours de modification SCI");

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

                        return $this->getResponseDeprecated("En cours de modification ADMIN");

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


        //$this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM);



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
                        if( $action == 'valid' )
                            $this->getTimesheetService()->validationAdm($period, $this->getCurrentPerson(), $message);
                        else
                            $this->getTimesheetService()->rejectAdm($period, $this->getCurrentPerson(), $message);

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
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity);
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

            die("Cette fonctionnalité est provisoirement indisponible.");

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

        if( $action == "export" ){
            $datas = $timesheetService->getPersonTimesheetsDatas($person, $period);

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
            $rowWpFormula = '=SUM(%s10:%s%s)';

            // Début des LOTS
            $lineWpStart = 10;

            /** @var \PHPExcel $spreadsheet */
            $spreadsheet = \PHPExcel_IOFactory::load($modele);

            $dateStart  = new \DateTime($period.'-01');
            $dateEnd    = new \DateTime($period.'-01');

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
            //$spreadsheet->getActiveSheet()->setCellValue('A9', "UE - " . $activity->getAcronym());


            $this->getLogger()->debug(print_r($datas, true));
            $line = $lineWpStart;

            foreach ($datas['declarations']['activities'] as $groupData) {
                $labelG = $groupData['label'];
                $this->getLogger()->debug($labelG);

                $spreadsheet->getActiveSheet()->insertNewRowBefore(($line + 1));
                $spreadsheet->getActiveSheet()->setCellValue('A'.$line, $labelG);
                $spreadsheet->getActiveSheet()->mergeCells('A'.$line.':'.'AG'.$line);
                $spreadsheet->getActiveSheet()->getCell('A'.$line)->getStyle()->applyFromArray([]);
                $spreadsheet->getActiveSheet()->setCellValue('B'.$line, "");

                $line++;

                foreach ($groupData['subgroup'] as $subGroupData) {
                    $labelSG = $subGroupData['label'];
                    $spreadsheet->getActiveSheet()->insertNewRowBefore(($line + 1));
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$line, $labelSG);
                    for( $i=0; $i<count($cellDays); $i++ ){

                        // Mise en forme du jour pour obtenir les clefs
                        $day = $i+1;
                        if( $day < 10 ) $day = '0'.$day;

                        $cellIndex = $cellDays[$i].$line;
                        $value = 0.0;
                        $style = [
                            'fill' => [
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => 'ffffff']
                            ]
                        ];

                        if( array_key_exists($day, $subGroupData['days']) ){
                            $value = $subGroupData['days'][$day];
                            $style['fill']['color']['rgb'] = 'bbf776';
                        }

                        if( $datas['daysInfos'][$day]['close'] ){
                            $style['fill']['color']['rgb'] = 'cccccc';
                        }

                        $spreadsheet->getActiveSheet()->getCell($cellIndex)->getStyle()->applyFromArray($style);
                        $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $value);
                    }
                    $spreadsheet->getActiveSheet()->setCellValue('AH'.$line, sprintf($lineWpFormula, $line, $line));
                    $line++;
                }
            }

            $lineTotalWP = $line;

            for( $i=0; $i<count($cellDays); $i++ ){
                $cell = $cellDays[$i];
                $sum = sprintf($rowWpFormula, $cell, $cell, $line);
                $spreadsheet->getActiveSheet()->setCellValue($cell.($line+1), $sum);
            }

            $spreadsheet->getActiveSheet()->setCellValue('AH'.($line+1), sprintf($rowWpFormula, 'AH', 'AH', $line));

            $line += 2;
            $spreadsheet->getActiveSheet()->insertNewRowBefore(($line +1));
            $line++;

            foreach( $this->getOthersWP() as $other ){
                $this->getLogger()->debug(print_r($other, true));
                // foreach ($datas['declarations']['activities'] as $groupData) {
                $spreadsheet->getActiveSheet()->insertNewRowBefore(($line +1));
                $spreadsheet->getActiveSheet()->setCellValue('B'.$line, $other['label']);
                $daysDatas = $datas['declarations']['others']['Hors-lot']['subgroup'][$other['label']]['days'];

                for( $i=0; $i<count($cellDays); $i++ ){

                    // Mise en forme du jour pour obtenir les clefs
                    $day = $i+1;
                    if( $day < 10 ) $day = '0'.$day;

                    $cellIndex = $cellDays[$i].$line;
                    $value = 0.0;
                    $style = [
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'ffffff']
                        ]
                    ];

                    if( array_key_exists($day, $daysDatas) ){
                        $value = $daysDatas[$day];
                        $style['fill']['color']['rgb'] = 'bbf776';
                    }

                    if( $datas['daysInfos'][$day]['close'] ){
                        $style['fill']['color']['rgb'] = 'cccccc';
                    }

                    $spreadsheet->getActiveSheet()->getCell($cellIndex)->getStyle()->applyFromArray($style);
                    $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $value);
                }
                $spreadsheet->getActiveSheet()->setCellValue('AH'.$line, sprintf($lineWpFormula, $line, $line));
                $line++;
            }

            $line += 1;



            $startTotal = $lineTotalWP;
            $end = $line-1;

            for( $i=0; $i<count($cellDays); $i++ ){

                // Mise en forme du jour pour obtenir les clefs
                $day = $i+1;
                if( $day < 10 ) $day = '0'.$day;

                $col = $cellDays[$i];
                $cellIndex = $cellDays[$i].$line;

                $value = sprintf("=SUM(%s%s:%s%s)", $col, $startTotal, $col, $end); //$lineTotalWP';
                $style = [
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => 'ffffff']
                    ]
                ];

                if( $datas['daysInfos'][$day]['close'] ){
                    $style['fill']['color']['rgb'] = 'cccccc';
                }

                $spreadsheet->getActiveSheet()->getCell($cellIndex)->getStyle()->applyFromArray($style);
                $spreadsheet->getActiveSheet()->setCellValue($cellIndex, $value);
            }

            $spreadsheet->getActiveSheet()->setCellValue('AH'.$line, sprintf($lineWpFormula, $line, $line));

            // TOTAL


            $edited = \PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

            $name = ($person->getLadapLogin())."-" . $period . ".xls";
            $filepath = '/tmp/'. $name;

            $edited->save($filepath);

                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $name . "\"");
                die(readfile($filepath));

        }


        $datas = $timesheetService->getPersonTimesheets($person, false, $period, null);


        return [
            "datas" => $datas,
            "person" => $person,

        ];
    }

    public function importIcalAction(){

        // -------------------------------------------------------------------------------------------------------------
        // Période URL
        $period = $this->params()->fromQuery('period', null);

        if( !$period )
            return $this->getResponseBadRequest("La période est non définit");

        // Liste des types de créneau valide
        $resume = $this->getTimesheetService()->getPersonPeriods($this->getCurrentPerson(), $period);

        if( $this->getHttpXMethod() == "POST" ){

            $request = $this->getRequest();
            $events = json_decode($request->getPost('timesheets', '[]'), true);
            if (count($events)) {

                try {
                    foreach ($events as $event) {
                        // Récupération des dates
                        $to = DateTimeUtils::toDatetime($event['end']);
                        $from = DateTimeUtils::toDatetime($event['start']);

                        if( !$from || !$to ){
                            throw new OscarException("Problème de format des dates : " . $event['form'] . " / " . $event['end']);
                        }

                        /** @var TimeSheet $timesheet */
                        $timesheet = new TimeSheet();
                        $this->getEntityManager()->persist($timesheet);
                        $timesheet->setIcsUid($event['icsuid'])
                            ->setIcsFileUid($event['icsfileuid'])
                            ->setIcsFileName($event['icsfilename'])
                            ->setIcsFileDateAdded(new \DateTime())
                            ->setDateFrom($from)
                            ->setPerson($this->getCurrentPerson())
                            ->setComment($event['summary'])
                            ->setDateTo($to);

                        if ($event['destinationId']) {
                            $wp = $this->getEntityManager()->getRepository(WorkPackage::class)->find($event['destinationId']);
                            if (!$wp) {
                                return $this->getResponseInternalError("Lot de travail inconnue");
                            }
                            $timesheet->setWorkPackage($wp);
                        } elseif ($event['destinationCode']) {
                            $other = $this->getTimesheetService()->getOthersWPByCode($event['destinationCode']);
                            if ($other) {
                                $timesheet->setLabel($other['code']);
                            } else {
                                $timesheet->setLabel($event['destinationCode']);
                            }
                        }

                        $this->getEntityManager()->flush($timesheet);
                    }

                    $dayRef = new \DateTime($period.'-01');
                    return $this->redirect()->toRoute('timesheet/declarant', [], ['query' => ['month' => $dayRef->format('m'), 'year' => $dayRef->format('Y')]]);
                    die();
                } catch (\Exception $e) {
                    return $this->getResponseInternalError($e->getMessage());
                }
            }

        }

        // par défaut, mois qui précède
        if( $period == null ){
            $now = new \DateTime();
            $now->sub( new \DateInterval('P1M'));
            $period = $now->format('Y-m');
        }

        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($this->getCurrentPerson(), $period);
        $correspondances = $this->getTimesheetService()->getAllTimesheetTypes($this->getCurrentPerson());


        return [
            'exists' => $resume,
            'period' => $period,
            'periodMax' => $datas['periodMax'],
            'datas' => $datas,
            'correspondances' => $correspondances
        ];
    }

    /**
     * Retourne la liste des déclarants actifs
     */
    public function declarersAction()
    {
        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch( $method ){
                case 'GET' :
                    $action = $this->params()->fromQuery('a');
                    try {
                        if( $action == "declarers")
                            return $this->ajaxResponse(['declarers' => $this->getTimesheetService()->getDeclarersList() ]);
                    } catch (\Exception $e) {
                        return $this->getResponseInternalError("Impossible de charger les déclarants : " . $e->getMessage());
                    }
            }
            return $this->getResponseBadRequest();
        }

        return [

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

        $now = new \DateTime();

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
            $dayKey = $start->format('Y-n-j');

            // Récupération des jours bloqués
            $locked = $this->getTimesheetService()->getLockedDays($year, $month);

            if( array_key_exists($dayKey, $locked) ){
                return $this->getResponseBadRequest("Vous ne pouvez pas déclarer ce jour : " . $locked[$dayKey]);
            }

            if( $start > $now ){
                return $this->getResponseBadRequest('Vous ne pouvez pas anticiper votre déclaration');
            }

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

                try {
                    $this->getTimesheetService()->checkAllowedAddedTimesheetInWorkPackage($person, $start, $end, $wp);
                } catch ( OscarException $e ){
                    return $this->getResponseInternalError($e->getMessage());
                }


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


        $today = new \DateTime();
        $year = (int)$this->params()->fromQuery('year', $today->format('Y'));
        $month = (int)$this->params()->fromQuery('month', $today->format('m'));
        $period = sprintf('%s-%s', $year, $month);


        /** @var Person $currentPerson */
        $currentPerson = $this->getCurrentPerson();

        $output = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($currentPerson, $period);

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

                    if( !$datas ){
                        return $this->getResponseBadRequest('Problème de transmission des données');
                    }

                    if( !$datas->from || !$datas->to ){
                        return $this->getResponseInternalError("La période soumise est incomplète");
                    }

                    $this->getLogger()->debug("##### ENVOI DES DECLARATIONS");

                    try {
                        $firstDay = new \DateTime($datas->from);
                        $this->getTimesheetService()->verificationPeriod($currentPerson, $firstDay->format('Y'), $firstDay->format('m'));
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError("Déclaration invalide : " . $e->getMessage());
                    }


                    try {
                        $from = new \DateTime($datas->from);
                        $to = new \DateTime($datas->to);
                        $timesheetService->sendPeriod($from, $to, $currentPerson);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError('Erreur de soumission de la période : ' . $e->getMessage());
                    }
                    return $this->getResponseNotImplemented("Erreur inconnue");
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

    public function declarationsAction()
    {
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_VALIDATION_MANAGE);
        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch ($method) {
                case 'GET' :
                $return = $this->getTimesheetService()->getDatasDeclarations();
                return $this->ajaxResponse($return);

                case 'DELETE':
                    $person_id = $this->params()->fromQuery('person_id');
                    $period = $this->params()->fromQuery('period');
                    if( $person_id ){
                        $person = $this->getPersonService()->getPerson($person_id);
                    }
                    $this->getTimesheetService()->deleteValidationPeriodPerson($person, $period);
                    return $this->getResponseOk();

                default:
                    return $this->getResponseInternalError("Non pris en charge");
            }
        }
        return [];
    }
}