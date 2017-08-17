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
use Oscar\Entity\ActivityType;
use Oscar\Entity\Person;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
use Oscar\Service\TimesheetService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Http\Request;
use Zend\Http\Response;
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

            $datas = $this->getRequest()->getPost()['events'];
            $action = $this->getRequest()->getPost()['do'];

            // Ajouter un test sur ACTION et EVENTS

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
            $timesheetId = $this->params()->fromQuery('timesheet');
            if ($timesheetId) {
                if ($timeSheetService->delete($timesheetId, $person)){
                    return $this->getResponseOk('Créneaux supprimé');
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
//        $wpDeclarants = $activity->getWorkPackages();

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

        if ($method == 'POST') {

            $datas = $this->getRequest()->getPost()['events'];
            $action = $this->getRequest()->getPost()['do'];

            // Ajouter un test sur ACTION et EVENTS

            if ($action == 'send') {
                $timesheets = $timeSheetService->send($datas,
                    $this->getCurrentPerson());
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
                            $action, [$data], $timeSheetService, $activity, $this->getOscarUserContext()->getCurrentPerson())
                        );
                    }
                }
            } else {
                $timesheets = $timeSheetService->create($datas,
                    $this->getCurrentPerson());
            }
        }


        if ($method == 'GET') {
            $timesheets = $timeSheetService->allByPerson($this->getCurrentPerson());
        }

        if ($method == 'DELETE') {

            $timesheetId = $this->params()->fromQuery('timesheet');
            if ($timesheetId) {
                if ($timeSheetService->delete($timesheetId,
                    $this->getCurrentPerson())
                ) {
                    return $this->getResponseOk('Créneaux supprimé');
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
                $timesheets = $timeSheetService->allByActivity($activity);
                $response = new JsonModel([
                    'timesheets' => $timesheets
                ]);
                $response->setTerminal(true);

                return $response;

            } else {
                /** @var Request $request */
                $request = $this->getRequest();

                if ($method == 'POST') {
                    $events = $request->getPost('events', []);
                    if (count($events) == 1 && $events[0]['id'] == 'null') {
                            throw new OscarException('A refactorer !');
                    } else {
                        $action = $this->getRequest()->getPost()['do'];
                        $events = $this->getRequest()->getPost()['events'];
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