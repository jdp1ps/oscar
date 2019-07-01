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
use Oscar\Formatter\TimesheetActivityPeriodFormatter;
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

    public function synthesisActivityPeriodAction()
    {

        // Données reçues
        $activity_id = $this->params()->fromQuery('activity_id', null);
        $format = $this->params()->fromQuery('format', '');
        $period = $this->params()->fromQuery('period', null);
        $error = null;

        $output = $this->getTimesheetService()->getSynthesisActivityPeriod($activity_id, $period);
        if( $format == 'json' ){
            return $this->jsonOutput($output);
        }
        elseif ($format == "excel") {
            $formatter = new TimesheetActivityPeriodFormatter();
            $formatter->output($output, 'excel');
        }
        elseif ($format == "pdf") {
            $formatter = new TimesheetActivityPeriodFormatter();
            $formatter->output($output, 'pdf');
        }
        else {
            return $output;
        }
    }

    /**
     * Centralisation de la consultation des feuilles de temps d'une personne / activité
     * Selon les critères envoyés.
     *
     * @return array|JsonModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function synthesisAllAction(){

        // Données reçues
        $activity_id    = $this->params()->fromQuery('activity_id', null);
        $person_id      = $this->params()->fromQuery('person_id', null);
        $format         = $this->params()->fromQuery('format', '');
        $period         = $this->params()->fromQuery('period', null);
        $error          = null;
        $validations = null;

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Synthèse pour une activités
        if( $activity_id != null ){

            $personsIds = [];

            /** @var Activity $activity */
            $activity = $this->getEntityManager()->find(Activity::class, $activity_id);

            if( !$activity ){
                return $this->getResponseInternalError(sprintf(_("Activité introuvable")));
            }

            // Contrôle des droits d'accès
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity);

            // Obtention des IDS des déclarants
            foreach ($activity->getDeclarers() as $person) {
                $personsIds[] = $person->getId();
            }

            // Période
            $start  = $activity->getDateStart()->format('Y-m');
            $end    = $activity->getDateEnd()->format('Y-m');



            if( count($personsIds) == 0 ){
                return $this->getResponseInternalError(sprintf(_("Il n'y a pas de déclarants dans cette activité")));
            }

            $validations = $this->getTimesheetService()->getDatasValidationPersonsPeriod($personsIds, $start, $end);
            $datas = $this->getTimesheetService()->getDatasDeclarersSynthesis($personsIds);

            $horslots = $this->getTimesheetService()->getOthersWP();
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Synthèse pour la personne
        elseif ( $person_id != null ){

            $person = $this->getPersonService()->getPerson($person_id);

            if( !$person ){
                return $this->getResponseInternalError(_("La personne est introuvable"));
            }

            // Contrôle des droits d'accès
            $global = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VIEW);
            if( !$global ){
                if( !in_array($this->getCurrentPerson(), $this->getPersonService()->getManagers($person)) ){
                    return $this->getResponseUnauthorized("Vous n'avez pas les droits pour voir la feuille de temps de $person");
                }
            }

            if( !$period ){
                return $this->getResponseBadRequest(_("Vous devez spécifier la période"));
            }

            $split = explode('-', $period);
            $periodOk = DateTimeUtils::getCodePeriod($split[0], $split[1]);
            $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($person, $period);
        }

        else {
            return $this->getResponseBadRequest("Paramètres de l'API insuffisants");
        }





        $output = [
            'activityId' => $activity_id,
            'activity' => $activity,
            'horslot' => $horslots,
            'datas' => $datas,
            'validations' => $validations
        ];

        if( $format == "excel" ){

            $modele = $this->getConfiguration('oscar.paths.timesheet_synthesis_modele');

            $cellDays = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U', 'V', 'W','X','Y','Z','AA', 'AB', 'AC', 'AD', 'AE','AF','AG'];
            $lineWpFormula = '=SUM(C%s:AG%s)';
            $rowWpFormula = '=SUM(%s10:%s%s)';

            // Début des LOTS
            $lineWpStart = 10;

            /** @var \PHPExcel $spreadsheet */
            $spreadsheet = \PHPExcel_IOFactory::load($modele);


            $spreadsheet->getActiveSheet()->setCellValue('A1', "Déclaration");
            $spreadsheet->getActiveSheet()->setCellValue('C3', (string)$person);
            $spreadsheet->getActiveSheet()->setCellValue('C4', 'Université de Caen');
            $spreadsheet->getActiveSheet()->setCellValue('C5', $datas['acronyms']);
            $spreadsheet->getActiveSheet()->setCellValue('C15', $datas['commentaires']);

            $spreadsheet->getActiveSheet()->setCellValue('U3', $datas['debut']); //$fmt->format($activity->getDateStart()));
            $spreadsheet->getActiveSheet()->setCellValue('U4', $datas['fin']); // $fmt->format($activity->getDateEnd()));
            $spreadsheet->getActiveSheet()->setCellValue('U5', $datas['num']); //$activity->getOscarNum());
            $spreadsheet->getActiveSheet()->setCellValue('U6', $datas['pfi']); //$activity->getCodeEOTP());

            $spreadsheet->getActiveSheet()->setCellValue('C6', $period);
            $spreadsheet->getActiveSheet()->setCellValue('B8', $period);

            $edited = \PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

            $spreadsheet->getActiveSheet()->insertNewColumnBefore('D');

            $name = "TEST_EXPORT.xls";
            $filepath = '/tmp/'. $name;

            $edited->save($filepath);

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $name . "\"");

            $contentfile = readfile($filepath);

            unlink($filepath);

            die($contentfile);


        }

        if( $format == "json" ){
            return $this->jsonOutput($datas);
        }

        return [
            'activityId' => $activity_id,
            'activity' => $activity,
            'horslot' => $horslots,
            'datas' => $datas,
            'validations' => $validations
        ];
    }

    public function syntheseActivityAction(){

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VIEW);

        $currentActivityId = $this->params()->fromRoute('id');
        $month = $this->params()->fromQuery('month', date('m'));
        $year = $this->params()->fromQuery('year', date('Y'));


        $enteteLots = [];
        $enteteProjets = [];
        $enteteOthers = [];
        $entetePerson = [];

        $totalLots = [];
        $totalProjets = [];
        $totalOthers = [];
        $totalPersons = [];



        $lots = []; // Données des lots
        $others = []; // Données des hors-lots
        $projects = []; // Données des autres projets

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($currentActivityId);
        $period = DateTimeUtils::getCodePeriod($year, $month);

        /** @var WorkPackage $wp */
        foreach ($activity->getWorkPackages() as $wp ){
            $wpId = $wp->getId();
            $enteteLots[$wpId] = sprintf("%s - %s", $wp->getActivity()->getAcronym(), $wp->getCode());
            $totalLots[$wpId] = 0.0;
        }

        foreach ($this->getTimesheetService()->getOthersWP() as $other) {
            $code = $other['code'];
            $enteteOthers[$code] = $other['label'];
            $totalOthers[$code] = 0.0;
        }


        foreach ($activity->getDeclarers() as $person ){
            $personId = $person->getId();
            $totalPersons[$personId] = 0.0;
            $lots[$personId] = [];
            $entetePerson[$personId] = (string)$person;

            foreach ($enteteLots as $wpId=>$wpLabel){
                $lots[$personId][$wpId] = 0.0;
            }

            $others[$personId] = [];
            foreach ($enteteOthers as $otherCode=>$otherLabel){
                $others[$personId][$otherCode] = 0.0;
            }
            $projects[$personId] = [];
        }



        foreach ($activity->getDeclarers() as $person ){
            
            $timePerson = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($person, $period);
            $personId = $person->getId();

            foreach ($timePerson['workpackages'] as $idActivityPerson=>$activityDetails){
                $acronym = $activityDetails['acronym'];
                $activity_id = $activityDetails['activity_id'];
                $workpackage_id = $activityDetails['id'];
                $total = $activityDetails['total'];

                if( $activity_id != $currentActivityId ){
                    if( !array_key_exists($activity_id, $enteteProjets) ){
                        $enteteProjets[$activity_id] = $acronym;
                        $totalProjets[$activity_id] = 0.0;
                        foreach ($projects as $p=>$d){
                            $projects[$p][$activity_id] = 0.0;
                        }
                    }
                    $totalProjets[$activity_id] += $total;
                    $projects[$personId][$activity_id] += $total;

                } else {
                    $lots[$personId][$workpackage_id] += $total;
                    $totalPersons[$personId] += $total;
                    $totalLots[$workpackage_id] += $total;
                }
            }

            var_dump($timePerson);

            foreach ($timePerson['otherWP'] as $codeOther=>$dataOther) {
                $total = $dataOther['total'];
                $totalOthers[$codeOther] += $total;
                $others[$personId][$codeOther] += $total;
                $totalPersons[$personId] += $total;
            }
        }

        var_dump($projects);

        echo "<table border='1'>";
        /*
         *
         * $enteteLots = [];
        $enteteProjets = [];
        $enteteOthers = [];
         */
        echo "<thead><tr><th>Personne</th>";
        foreach ($enteteLots as $id=>$label){
            echo "<th class='lot heading active'>$label</th>";
        }
        foreach ($enteteProjets as $id=>$label){
            echo "<th class='lot heading off'>$label</th>";
        }
        foreach ($enteteOthers as $id=>$label){
            echo "<th class='hors-lot heading off'>$label</th>";
        }
        echo "<th>Total</th>";
        echo "</tr></thead>";

        echo "<tbody>";

        $totalMonth = 0.0;

        foreach ($entetePerson as $idPerson=>$labelPerson) {
            echo "<tr><th>$labelPerson</th>";

            foreach ($lots[$idPerson] as $id=>$label){
                echo "<th class='lot heading active'>$label</th>";
            }
            foreach ($projects[$idPerson] as $id=>$label){
                echo "<th class='lot heading off'>$label</th>";
            }
            foreach ($others[$idPerson] as $id=>$label){
                echo "<th class='hors-lot heading off'>$label</th>";
            }

            echo "<th>" . $totalPersons[$idPerson] . "</th>";

            $totalMonth += $totalPersons[$idPerson];

            echo "</tr>";
        }

        echo "<tr><th>Total</th>";
        foreach ($totalLots as $id=>$total){
            echo "<th class='lot total active'>$total</th>";
        }

        foreach ($totalProjets as $id=>$total){
            echo "<th class='lot total'>$total</th>";
        }

        foreach ($totalOthers as $id=>$total){
            echo "<th class='hors-lot total'>$total</th>";
        }
        echo "<th class='hors-lot total'>$totalMonth</th>";
        echo "</tr>";

        echo "</tbody>";


        echo "</table>";
        var_dump($lots);
//        var_dump($lots);
//        var_dump($projects);
//        var_dump($others);
        die("Synthèse activité " . $activity);
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

//            /** @var Activity $activity */
//            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

            $cellDays = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U', 'V', 'W','X','Y','Z','AA', 'AB', 'AC', 'AD', 'AE','AF','AG'];
            $lineWpFormula = '=SUM(C%s:AG%s)';
            $rowWpFormula = '=SUM(%s10:%s%s)';

            // Début des LOTS
            $lineWpStart = 10;

            /** @var \PHPExcel $spreadsheet */
            $spreadsheet = \PHPExcel_IOFactory::load($modele);

            $dateStart  = new \DateTime($period.'-01');
            $dateEnd    = new \DateTime($period.'-01');

            $spreadsheet->getActiveSheet()->setCellValue('A1', "Déclaration");
            $spreadsheet->getActiveSheet()->setCellValue('C3', (string)$person);
            $spreadsheet->getActiveSheet()->setCellValue('C4', 'Université de Caen');
            $spreadsheet->getActiveSheet()->setCellValue('C5', $datas['acronyms']);
            $spreadsheet->getActiveSheet()->setCellValue('C15', $datas['commentaires']);

            $spreadsheet->getActiveSheet()->setCellValue('U3', $datas['debut']); //$fmt->format($activity->getDateStart()));
            $spreadsheet->getActiveSheet()->setCellValue('U4', $datas['fin']); // $fmt->format($activity->getDateEnd()));
            $spreadsheet->getActiveSheet()->setCellValue('U5', $datas['num']); //$activity->getOscarNum());
            $spreadsheet->getActiveSheet()->setCellValue('U6', $datas['pfi']); //$activity->getCodeEOTP());

            $spreadsheet->getActiveSheet()->setCellValue('C6', $period);
            $spreadsheet->getActiveSheet()->setCellValue('B8', $period);

            $line = $lineWpStart;

            foreach ($datas['declarations']['activities'] as $groupData) {
                $labelG = $groupData['label'];

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
        if( !$this->getOscarConfigurationService()->getConfiguration('importEnable') ){
            throw new OscarException("Cette option n'est activée");
        }

        if( !$period )
            return $this->getResponseBadRequest("La période est non définit");

        $personId = $this->params()->fromQuery('person', null);

        if( $personId && $personId != $this->getCurrentPerson()->getId() ) {
            $person = $this->getPersonService()->getPersonById($personId, true);
            if( !($this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_FEED_TIMESHEET) || $person->getTimesheetsBy()->contains($this->getCurrentPerson())) ){
                throw new UnAuthorizedException("Vous n'êtes pas authorisé à compléter la feuille de temps de $person");
            }
        } else {
            $person = $this->getCurrentPerson();
        }

        // Liste des types de créneau valide
        $resume = $this->getTimesheetService()->getPersonPeriods($person, $period);

        // par défaut, mois qui précède
        if( $period == null ){
            $now = new \DateTime();
            $now->sub( new \DateInterval('P1M'));
            $period = $now->format('Y-m');
        }

        if( $this->getHttpXMethod() == "POST" ){

            $request = $this->getRequest();
            $events = json_decode($request->getPost('timesheets', '[]'), true);
            $removePrevious = $request->getPost('previousicsuidremove', null) == 'remove';

            if( $removePrevious === true ){
                $uid = $request->getPost('previousicsuid', null);
                if( $uid ){
                    $periodInfos = DateTimeUtils::periodBounds($period);

                    $qb = $this->getEntityManager()->createQueryBuilder();
                    $qb->delete(TimeSheet::class, 't');
                    $qb->where('t.icsFileUid = :icsuid');
                    $qb->andWhere('t.person = :person');
                    $qb->andWhere('t.dateFrom >= :dateFrom');
                    $qb->andWhere('t.dateTo <= :dateTo');

                    $qb->setParameters([
                        'icsuid' => $uid,
                        'person' => $person,
                        'dateFrom' => $periodInfos['start'],
                        'dateTo' => $periodInfos['end'],
                    ]);

                    $qb->getQuery()->execute();
                }
            }

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
                            ->setPerson($person)
                            ->setComment($event['summary'])
                            ->setDateTo($to);

                        if ($event['destinationId']) {
                            $wp = $this->getEntityManager()->getRepository(WorkPackage::class)->findOneBy(['id' => $event['destinationId'] ]);
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

        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($person, $period);
        $correspondances = $this->getTimesheetService()->getAllTimesheetTypes($person);

//        var_dump($correspondances); die();

        return [
            'exists' => $resume,
            'period' => $period,
            'periodMax' => $datas['periodMax'],
            'datas' => $datas,
            'person' => $person,
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

    public function resumeActivityAction(){

        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('id'));

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_VIEW, $activity);


        $format = $this->params()->fromQuery('format', null);
        $method = $this->getHttpXMethod();

        if( $this->isAjax() || $format == 'json' ){
            switch ($method) {
                case 'GET' :
                    $datas = $this->getTimesheetService()->getResumeActivity($activity);
                    if( $format == 'json' ){
                        return $this->jsonOutput($datas);

                    }
                    return $this->ajaxResponse($datas);
            }
        }
        return [];
    }

    public function resumeAction(){

        $personId = $this->params()->fromQuery('person_id', null);
        if( $personId ){
            $person = $this->getPersonService()->getPerson($personId);
        } else {
            $person = $this->getCurrentPerson();
        }

        return [
            'datas' => $this->getTimesheetService()->getResumePerson($person),
            'person' => $personId ? $person : null
        ];
    }

    public function validationsAction() {


        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            $serviceTimesheet = $this->getTimesheetService();

            switch ($method) {
                case 'GET' :
                    $datas = $serviceTimesheet->getValidationsForValidator2($this->getCurrentPerson());
                    return $this->ajaxResponse($datas);
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
                            $this->getTimesheetService()->validation($period, $this->getCurrentPerson(), $message);
                        else
                            $this->getTimesheetService()->reject($period, $this->getCurrentPerson(), $message);

                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    return $this->getResponseOk();
                    break;

                default:
                    return $this->getResponseNotFound();
            }
        }
        return [];
    }

    public function resolveInvalidLabelsAction(){

        // TODO
        // $this->getOscarUserContext()->check(Privileges::)

        $invalidLabels = $this->getTimesheetService()->getInvalidLabels();
        $destinations = $this->getTimesheetService()->getOthersWP();
        $message = "";

        if( $this->getHttpXMethod() == 'POST' ){

            $correspondances = $this->params()->fromPost('labels');
            $resolve = [];

            foreach( $correspondances as $correspondance=>$destination ){
                if( $destination == "" ) continue;

                if( !in_array($correspondance, $invalidLabels) ){
                    throw new OscarException(sprintf("Opération interrompue : L'intitulé %s n'existe pas", $correspondance));
                }
                if( !array_key_exists($destination, $destinations) ){
                    throw new OscarException(sprintf("Opération interrompue : La destination %s n'existe pas", $correspondance));
                }

                $resolve[$correspondance] = $destination;
            }
            $this->getTimesheetService()->maintenanceConvertHorsLots($resolve);
            $message = "Opération terminée";
        }

        return [
            'message' => $message,
            'datas' => $invalidLabels,
            'othersWP'=> $destinations
        ];
    }

    /**
     * Déclaration des heures.
     */
    public function declarationAction()
    {
        die("test");
        return [];
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


    public function sendTimesheet(Person $person)
    {

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
                return $this->getResponseBadRequest('Vous ne pouvez pas anticiper une déclaration');
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

        // Méthode d'accès
        $method = $this->getHttpXMethod();

        // Durée d'un jour "normal"
        $today = new \DateTime();
        $year = (int)$this->params()->fromQuery('year', $today->format('Y'));
        $month = (int)$this->params()->fromQuery('month', $today->format('m'));
        $format = $this->params()->fromQuery('format', null);
        $period = sprintf('%s-%s', $year, $month);
        $declarerId = $this->params()->fromQuery('person', null);
        $usurpation = false;

        if( $declarerId ){
            $usurpation = $declarerId;
        } else {
            $declarerId = $this->getCurrentPerson()->getId();
        }

        /** @var Person $currentPerson */
        $currentPerson = $this->getPersonService()->getPersonById($declarerId); //$this->getCurrentPerson();

        // On test l'authorisation à l'usurpation si besoin
        if( $declarerId != $this->getCurrentPerson()->getId() ){
            if( !($this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_FEED_TIMESHEET) || $currentPerson->getTimesheetsBy()->contains($this->getCurrentPerson())) ){
                throw new UnAuthorizedException("Vous n'êtes pas authorisé à compléter la feuille de temps de $currentPerson");
            }
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');


        if( $this->isAjax() || $format == 'json' ) {
            switch ($method) {

                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // Récupération des créneaux de la période
                case 'GET' :
                    $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($currentPerson, $period);
                    if( $usurpation )
                        $datas['usurpation'] = $usurpation;

                    return $this->ajaxResponse($datas);
                    break;

                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // Envoi de données
                case 'POST' :
                    $action = $this->params()->fromPost('action', 'send');
                    $comments = $this->params()->fromPost('comments', null);

                    if( $comments ){
                        $comments = json_decode($comments, JSON_OBJECT_AS_ARRAY);
                    }

                    // Ajout des créneaux
                    if( $action == 'add' ){
                        return $this->sendTimesheet($currentPerson);
                    }

                    // Réenvoi de la déclaration
                    if( $action == 'resend' ){
                        $timesheetService->reSendPeriod($year, $month, $currentPerson);
                        return $this->getResponseOk();
//
//                        $periodId = $this->params()->fromPost('period_id');
//                        /** @var ValidationPeriod $period */
//                        $period = $this->getEntityManager()->getRepository(ValidationPeriod::class)->find($periodId);
//                        if( $period->getDeclarer() == $currentPerson ){
//                            try {
//                                $timesheetService->verificationPeriod($currentPerson, $period->getYear(), $period->getMonth());
//                                $timesheetService->reSendValidation($period, $comments);
//                                return $this->getResponseOk();
//                            } catch (\Exception $e ){
//                                return $this->getResponseInternalError(sprintf('Impossible de réenvoyer la déclaration : %s', $e->getMessage()));
//                            }
//                        } else {
//                            return $this->getResponseUnauthorized("Cette déclaration n'est pas la votre.");
//                        }
                    }

                    $datas = json_decode($this->params()->fromPost('datas'));

                    if( !$datas ){
                        return $this->getResponseBadRequest('Problème de transmission des données');
                    }

                    if( !$datas->from || !$datas->to ){
                        return $this->getResponseInternalError("La période soumise est incomplète");
                    }

                    try {
                        $firstDay = new \DateTime($datas->from);
                        $this->getTimesheetService()->verificationPeriod($currentPerson, $firstDay->format('Y'), $firstDay->format('m'));
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError("Déclaration invalide : " . $e->getMessage());
                    }

                    try {
                        $from = new \DateTime($datas->from);
                        $to = new \DateTime($datas->to);
                        $timesheetService->sendPeriod($from, $to, $currentPerson, $comments);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError('Erreur de soumission de la période : ' . $e->getMessage());
                    }
                    return $this->getResponseNotImplemented("Erreur inconnue");
                    break;

                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // Suppression
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
        $datas = $this->getTimesheetService()->getTimesheetDatasPersonPeriod($currentPerson, $period);
        if( $usurpation )
            $datas['usurpation'] = $usurpation;
        return $datas;
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
               // $return['validatorsEdit'] = true;
                return $this->ajaxResponse($return);

                case 'POST' :
                    $action = $this->params()->fromPost('action', 'add');
                    $person_id = $this->params()->fromPost('person_id');

                    $person = $this->getPersonService()->getPerson($person_id);

                    // Modification des heures pour la périodes
                    if( $action == 'changeschedule') {
                        $days = json_decode($this->params()->fromPost('days'));
                        $period = $this->params()->fromPost('period');

                        try {
                            $this->getTimesheetService()->changePersonSchedulePeriod($person, $days, $period);
                            return $this->getResponseOk();
                        } catch (OscarException $e){
                            return $this->getResponseInternalError($e->getMessage());
                        }
                    }

                    //
                    else {

                        $type = $this->params()->fromPost('type');
                        $declaration_id = $this->params()->fromPost('declaration_id');
                        $validation = $this->getTimesheetService()->getValidationPeriod($declaration_id);


                        if( !in_array($type, ['adm', 'sci', 'prj']) ){
                            return $this->getResponseInternalError('Type de validation inconnu');
                        }

                        if( $action == 'delete' ){
                            $this->getTimesheetService()->removeValidatorToValidation($type, $person, $validation);
                            return $this->getResponseOk();
                        } else {
                            $this->getTimesheetService()->addValidatorToValidation($type, $person, $validation);
                            $return = [
                                'person' => (string) $person,
                                'id' => $person->getId()
                            ];
                            return $this->ajaxResponse($return);
                        }
                    }

                    return $this->getResponseBadRequest("Erreur d'API");

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