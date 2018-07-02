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
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
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
        // Méthode réél
        $method = $this->getHttpXMethod();

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_TIMESHEET_USURPATION, $activity);

        $person = $this->getEntityManager()->getRepository(Person::class)->find($this->params()->fromRoute('idperson'));

        if (!$activity) {
            return $this->getResponseNotFound("L'activité n'existe pas");
        }

        if (!$person) {
            return $this->getResponseNotFound("La person %s n'existe pas");
        }

        /** @var TimesheetService $timeSheetService */
        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');

        $timesheets = [];

        if ($method == 'GET') {
            $timesheets = $timeSheetService->allByPerson($person, $person);
        }

        if ($method == 'POST') {

            $datas = json_decode($this->getRequest()->getPost()['events'], true);
            $action = $this->getRequest()->getPost()['do'];

            if ($action == 'send') {
                $timesheets = $timeSheetService->send($datas, $person);
            } else if ( $action ){
                if( !in_array($action, ['validatesci', 'validateadm', 'send', 'rejectsci','rejectadm'])) {
                    return $this->getResponseBadRequest('Opération inconnue !');
                }

                foreach ($datas as $data) {
                    if ($data['id'] && $data['id'] != 'null') {
                        /** @var TimeSheet $timeSheet */
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                        $activity = null ;
                        if( $timeSheet->getActivity() ){
                            $activity = $timeSheet->getActivity();
                        }
                        elseif ($timeSheet->getWorkpackage()){
                            $activity = $timeSheet->getWorkpackage()->getActivity();
                        }
                        if( !$activity ){
                            // todo Ajouter un warning
                            continue;
                        }

                        $timesheets = array_merge($timesheets, $this->processAction(
                            $action, [$data], $timeSheetService, $activity, $person)
                        );
                    }
                }
            } else {
                $timesheets = $timeSheetService->create($datas, $person);
            }
        }

        if ($method == 'DELETE') {
            $timesheetId = $this->params()->fromQuery('timesheet', null);

            // UID de l'ICS
            $icsUid = $this->params()->fromQuery('icsuid', null);

            if ($timesheetId) {
                if ($timeSheetService->delete($timesheetId,
                    $this->getCurrentPerson())
                ) {
                    return $this->getResponseOk('Créneaux supprimé');
                }
            }
            elseif ($icsUid) {
                $this->getLogger()->info("Suppression d'un ICS");
                try {
                    $warnings = $timeSheetService->deleteIcsFileUid($icsUid, $person);
                    $this->getLogger()->info("Suppression OK warn = " . count($warnings));
                    foreach ($warnings as $w){
                        $this->getLogger()->info($w);
                    }
                    return $this->getResponseOk(json_encode($warnings));
                }
                catch (\Exception $e ){
                    $this->getLogger()->err($e->getMessage());
                    return $this->getResponseInternalError("Impossible de supprimer ce calendrier : " . $e->getMessage());
                }
            }

            return $this->getResponseBadRequest("Impossible de supprimer le créneau : créneau inconnu");
        }

        $wpDeclarants = [];
        /** @var WorkPackage $workPackage */
        foreach($activity->getWorkPackages() as $workPackage ){
            if( $workPackage->hasPerson($person) ){
                $wpDeclarants[$workPackage->getId()] = $workPackage;
            }
        }

        foreach($timesheets as &$timesheet ){
            if( !($timesheet['activity_id'] == null || $timesheet['activity_id'] == $activity->getId()) ){
                $timesheet['credentials']['editable'] = false;
                $timesheet['credentials']['deletable'] = false;
                $timesheet['credentials']['sendable'] = false;
            }
        }

        $datasView = [
            'wpDeclarants' => $wpDeclarants,
            'timesheets' => $timesheets,
            'person' => $person,
            'activity' => $activity,
        ];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = new JsonModel($datasView);
            $response->setTerminal(true);

            return $response;
        }

        return $datasView;
    }



    /**
     * Déclaration des heures.
     */
    public function declarationAction()
    {
        // Méthode réél
        $method = $this->getHttpXMethod();
        // Déclarant
        $person = $this->getOscarUserContext()->getCurrentPerson();

        /** @var TimesheetService $timeSheetService */
        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');

        // Retour
        $timesheets = [];

        try {
            if ($method == 'POST') {
                $datas = json_decode($this->getRequest()->getPost()['events'],true);
                $action = $this->getRequest()->getPost()['do'];

                if ($action == 'send') {
                    $timesheets = $timeSheetService->send($datas, $this->getCurrentPerson());
                } else {
                    if ($action) {
                        if (!in_array($action, [
                            'validatesci',
                            'validateadm',
                            'send',
                            'rejectsci',
                            'rejectadm'
                        ])) {
                            return $this->getResponseBadRequest('Opération inconnue !');
                        }

                        foreach ($datas as $data) {
                            if ($data['id'] && $data['id'] != 'null') {
                                /** @var TimeSheet $timeSheet */
                                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                                $activity = null;

                                // idactivity
                                // idworkpackage


                                if ($timeSheet->getActivity()) {
                                    $activity = $timeSheet->getActivity();
                                } elseif ($timeSheet->getWorkpackage()) {
                                    $activity = $timeSheet->getWorkpackage()->getActivity();
                                }
                                if (!$activity) {
                                    // todo Ajouter un warning
                                    continue;
                                }

                                $timesheets = array_merge($timesheets,
                                    $this->processAction(
                                        $action, [$data], $timeSheetService,
                                        $activity,
                                        $this->getOscarUserContext()->getCurrentPerson())
                                );
                            }
                        }
                    } else {
                        $this->getLogger()->info('CREATE OR UPDATE FROM IMPORT !');
                        $timesheets = $timeSheetService->create($datas,
                            $this->getCurrentPerson());
                    }
                }
            }
        } catch (\Exception $e ){
            return $this->getResponseInternalError("ERROR : " . $e->getMessage() . " - " . $e->getTraceAsString());
        }

        if ($method == 'GET') {
            $timesheets = $timeSheetService->allByPerson($this->getCurrentPerson());
        }

        if ($method == 'DELETE') {
            // Identifiant de l'événement
            $timesheetId = $this->params()->fromQuery('timesheet', null);

            // UID de l'ICS
            $icsUid = $this->params()->fromQuery('icsuid', null);

            if ($timesheetId) {
                if ($timeSheetService->delete($timesheetId,
                    $this->getCurrentPerson())
                ) {
                    return $this->getResponseOk('Créneaux supprimé');
                }
            }
            elseif ($icsUid) {
                try {
                    $warnings = $timeSheetService->deleteIcsFileUid($icsUid, $this->getCurrentPerson());
                    foreach ($warnings as $w){
                        $this->getLogger()->info($w);
                    }
                    return $this->getResponseOk(json_encode($warnings));
                }
                catch (\Exception $e ){
                    return $this->getResponseInternalError("Impossible de supprimer ce calendrier : " . $e->getMessage());
                }
            }

            return $this->getResponseBadRequest("Impossible de supprimer le créneau : créneau inconnu");
        }


        $wpDeclarants = $this->getEntityManager()->createQueryBuilder()
            ->select('wp')
            ->from(WorkPackage::class, 'wp')
            ->innerJoin('wp.persons', 'wpp')
            ->where('wpp.person = :person')
            ->setParameters([
                'person' => $this->getOscarUserContext()->getCurrentPerson()
            ])
            ->getQuery()
            ->getResult();

        $datasView = [
            'wpDeclarants' => $wpDeclarants,
            'timesheets' => $timesheets
        ];

        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = new JsonModel($datasView);
            $response->setTerminal(true);

            return $response;
        }

        return $datasView;
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

        /** @var TimesheetService $timeSheetService */
        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');

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
        static $others;
        if( $others == null ){
            $others = [
               'conges' => [ 'code' => 'conges',  'label' => 'Congès',  'description' => 'Congès, RTT, récupération', 'icon' => true ],
               'learning' => [ 'code' => 'learning',  'label' => 'Formation',  'description' => 'Vous avez suivi un formation, DIFF, etc...', 'icon' => true ],
               'teaching' => [ 'code' => 'teaching',  'label' => 'Enseignement',  'description' => 'Cours, TD, fonction pédagogique', 'icon' => true ],
               'sickleave' => [ 'code' => 'sickleave', 'label' => 'Arrêt maladie',  'description' => '', 'icon' => true ],
               'absent' => [ 'code' => 'absent',  'label' => 'Absent',  'description' => '', 'icon' => true ],
               'research' => [ 'code' => 'research', 'label' => 'Autre recherche',  'description' => 'Autre projet de recherche (sans feuille de temps)', 'icon' => true ],
            ];
        }
        return $others;
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
        $timesheets = [];

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

            $start = new \DateTime(sprintf($dayBase, 8, 0));
            $end = new \DateTime(sprintf($dayBase, 8+$heures, $minutes));
            $wp = null;
            $label = "error";


            if( !$data->wpId ){
                // Spécial
                $other = $this->getOthersWP()[$data->code];
                $status = TimeSheet::STATUS_INFO;
                $label = $data->code;

                if( !$other ){
                    return $this->getResponseBadRequest(sprintf("Ce type de créneau '%s' n'est pas pris en charge dans cette version", $data->code));
                }
            } else {
                /** @var WorkPackage $wp */
                $wp = $this->getEntityManager()->getRepository(WorkPackage::class)->find($wpId);
                $label = (string)$wp;
            }







            $timesheet = new TimeSheet();
            $this->getEntityManager()->persist($timesheet);

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



    public function declarantAction(){
        $output = [];

        $method = $this->getHttpXMethod();

        // Durée d'un jour "normal"
        $dayLength = 37/5;

        // Durée d'une journée de travail maximum légale
        $maxDays = 12.0;

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

        $tsFrom = (new \DateTime($from))->getTimestamp();
        $tsTo = (new \DateTime($to))->getTimestamp();
        $tsNow = $today->getTimestamp();

        $output['periodFutur'] = false;
        $output['periodCurrent'] = false;
        $output['periodFinished'] = false;

        if( $tsFrom > $tsNow ){
            $output['periodFutur'] = true;
            $output['periodMessage'] = "Ce mois n'est pas encore passé";
        }

        if( $tsTo < $tsNow ){
            $output['periodFinished'] = true;
            $output['periodMessage'] = "Ce mois est passé";
        }

        if( $tsFrom <= $tsNow && $tsTo >= $tsNow ){
            $output['periodCurrent'] = true;
            $output['periodMessage'] = "Mois en cours";
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

        // Total du temps cummulé dans le mois
        $output['total'] = 0.0;

        // Durée d'une journée "normale"
        $output['daylength'] = $dayLength;
        $output['dayNbr'] = $nbr;

        // Récupération des lots
        $availableWorkPackages = $this->getActivityService()->getWorkPackagePersonPeriod($currentPerson, $year, $month);

        // Liste des activités du mois
        $activities = [];

        // Liste des lots du mois
        $workPackages = [];

        $lockedDays = $this->getServiceLocator()->get('TimesheetService')->getLockedDays($year, $month);
        $this->getLogger()->debug(print_r($lockedDays, true));

        /** @var WorkPackagePerson $workPackagePerson */
        foreach ($availableWorkPackages as $workPackagePerson){
            $workPackage = $workPackagePerson->getWorkPackage();
            $activity = $workPackage->getActivity();

            if( !array_key_exists($activity->getId()) ){
                $activities[$activity->getId()] = [
                    'id' => $activity->getId(),
                    'acronym' => $activity->getAcronym(),
                    'project' => (string)$activity->getProject(),
                    'project_id' => $activity->getProject()->getId(),
                    'label' => $activity->getLabel(),
                    'total' => 0.0
                ];
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
                'total' => 0.0
            ];
        }

        $output['activities'] = $activities;
        $output['workPackages'] = $workPackages;

        // Récupération des créneaux présents dans Oscar
        $query = $this->getEntityManager()->getRepository(TimeSheet::class)->createQueryBuilder('t');
        $query->where('t.dateFrom >= :start AND t.dateTo <= :end AND t.person = :person')
            ->setParameters([
                'start' => $from,
                'end' => $to,
                'person' => $currentPerson,
            ]);

        $timesheets = $query->getQuery()->getResult();

        for( $i = 1; $i<=$nbr; $i++ ){
            $data = sprintf('%s-%s-%s', $year, $month, $i);
            $day = new \DateTime($data);

            // Journée vérrouillé (future/passée)
            $locked = false;

            // Journée fermée (feriès)
            $close = false;

            $lockedReason = "";

            $futur = $day->getTimestamp() > $today->getTimestamp();
            if( $futur ){
                $locked = true;
                $lockedReason = "Vous ne pouvez pas anticiper une déclaration";
            }

            $wDay = $day->format('w');

            $weekend = $wDay == 6 || $wDay == 0;
            if( $weekend ){
                $locked = true;
                $close = true;
                $lockedReason = "Vous ne pouvez pas déclarer le weekend";
            }

            if( array_key_exists($data, $lockedDays) ){
                $locked = true;
                $close = true;
                $lockedReason = $lockedDays[$data];
            }

            $output['days'][$i] = [
                'date' => $data,
                'i' => $i,
                'day' => $day->format('N'),
                'week' => $day->format('W'),
                'data' => $data,
                'label' => $day->format('d'),
                'weekend' => $weekend,
                'declarations' => [],
                'infos' => [],
                'duration' => 0.0,
                'vacations' => [],
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

        /** @var TimeSheet $t */
        foreach ($timesheets as $t) {

            $dayTimesheet = (int)($t->getDateFrom()->format('d'));

            if (!$t->getActivity()) {
                if ($t->getStatus() == TimeSheet::STATUS_INFO) {

                    $datas = [
                        'id' => $t->getId(),
                        'label' => $t->getLabel(),
                        'description' => $t->getComment(),
                        'duration' => $t->getDuration(),
                        'status_id' => $t->getStatus(),
                        'status' => TimeSheet::getStatusLabel($t->getStatus())
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

                        case 'vacations' :
                        case 'conges' :
                            $output['days'][$dayTimesheet]['vacations'][] = $datas;
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

                }
                continue;
            }

            $projectAcronym = $t->getActivity()->getAcronym();
            $project = $t->getActivity()->getProject();
            $activity = $t->getActivity();
            $activityCode = $activity->getOscarNum();
            $workpackage = $t->getWorkpackage();
            $wpCode = $workpackage->getCode();

            $output['days'][$dayTimesheet]['duration'] += (float)$t->getDuration();
            $output['total'] += (float)$t->getDuration();

            $output['activities'][$activity->getId()]['total'] += $t->getDuration();
            $output['workPackages'][$workpackage->getId()]['total'] += $t->getDuration();

            $output['days'][$dayTimesheet]['declarations'][] = [
                'id' => $t->getId(),
                'label' => $t->getLabel(),
                'comment' => $t->getComment(),
                'activity' => (string) $activity,
                'activity_code' => $activityCode,
                'acronym' => $projectAcronym,
                'project' => (string)$project,
                'status_id' => $t->getStatus(),
                'status' => TimeSheet::getStatusLabel($t->getStatus()),
                'wpCode' => $wpCode,
                'duration' => (float)$t->getDuration(),
                'wp_id' => $t->getWorkpackage()->getId(),
            ];
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        if( $this->isAjax() ) {
            switch ($method) {
                case 'GET' :
                    return $this->ajaxResponse($output);
                    break;

                case 'POST' :
                    $datas = json_decode($this->params()->fromPost('datas'));
                    $this->getLogger()->debug("Données recues : " . print_r($datas, true));
                    if( !$datas ){
                        return $this->getResponseBadRequest('Problème de transmission des données');
                    }

                    if( !$datas->from || !$datas->to ){
                        return $this->getResponseInternalError("La période soumise est incomplète");
                    }

                    try {
                        $from = new \DateTime($datas->from);
                        $to = new \DateTime($datas->to);

                        $timesheets = $timesheetService->getTimesheetsPersonPeriodArrayId($currentPerson, $from, $to);

                        if( count($timesheets) == 0 ){
                            throw new \Exception("Aucun créneau à soumettre pour cette période.");
                        }

                        $this->getLogger()->debug(print_r($timesheets, true));
                        $timesheetService->send($timesheets, $currentPerson);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError('Erreur de soumission de la période : ' . $e->getMessage());
                    }

                    $this->getLogger()->debug(print_r($datas, true));
                    return $this->getResponseNotImplemented("Pas encore fait");
                    break;

                case 'DELETE' :
                    try {
                        $idsCreneaux = explode(',', $this->params()->fromQuery('id'));
                        $timesheetService->delete($idsCreneaux, $currentPerson);
                        return $this->getResponseOk();
                    } catch (\Exception $e ){
                        return $this->getResponseNotImplemented($e->getMessage());
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
        if( !in_array($action, ['validatesci', 'validateadm', 'send', 'rejectsci','rejectadm'])) {
            return $this->getResponseBadRequest('Opération inconnue !');
        }

        if( in_array($action, ['validatesci', 'rejectsci' ]) &&
            !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity)) {
            throw new OscarException("Vous n'avez les droits pour la validation scientifique.");
        }

        if( in_array($action, ['validateadm', 'rejectadm' ]) &&
            !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity)) {
            throw new OscarException("Vous n'avez les droits pour la validation administrative.");
        }

        switch($action){
            case 'validatesci';
                $timesheets = $timeSheetService->validateSci($events, $person);
                break;
            case 'validateadm';
                $timesheets = $timeSheetService->validateAdmin($events, $person);
                break;
            case 'rejectsci';
                $timesheets = $timeSheetService->rejectSci($events, $person);
                break;
            case 'rejectadm';
                $timesheets = $timeSheetService->rejectAdmin($events, $person);
                break;
            case 'send';
                $timesheets = $timeSheetService->send($events, $person);
                break;
        }

        return $timesheets;
    }

    /**
     * todo Rejète une déclaration de temps
     */
    public function invalidateTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }
}