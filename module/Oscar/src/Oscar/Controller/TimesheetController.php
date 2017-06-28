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
//                        $event = $events[0];
//                        $person = $this->getEntityManager()->getRepository(Person::class)->find($event['owner_id']);
//
//                        /** @var WorkPackage $workpackage */
//                        $workpackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($event['idworkpackage']);
//
//                        if (!$person) {
//                            return $this->getResponseBadRequest('Personne inconnue !');
//                        }
//
//                        if (!$workpackage) {
//                            return $this->getResponseBadRequest('Lot de travail inconnu !');
//                        }
//
//                        try {
//                            $timesheet = new TimeSheet();
//                            $this->getEntityManager()->persist($timesheet);
//                            $timesheet->setPerson($person)
//                                ->setActivity($activity)
//                                ->setLabel((string)$workpackage)
//                                ->setCreatedBy($this->getCurrentPerson())
//                                ->setDateFrom(new \DateTime($event['start']))
//                                ->setValidatedAt(new \DateTime())
//                                ->setValidatedBy((string)$this->getCurrentPerson())
//                                ->setDateTo(new \DateTime($event['end']))
//                                ->setStatus(TimeSheet::STATUS_TOVALIDATE)
//                                ->setWorkpackage($workpackage);
//                            $this->getEntityManager()->flush($timesheet);
//                            $json = $timesheet->toJson();
//                            $json['credentials'] = [
//                                'deletable' => true,
//                                'editable' => true,
//                                'sendable' => $timesheet->getStatus() == TimeSheet::STATUS_DRAFT,
//                                'validable' => $timesheet->getStatus() == TimeSheet::STATUS_TOVALIDATE
//                            ];
//                            $response = new JsonModel([
//                                'timesheets' => [$json]
//                            ]);
//                            $response->setTerminal(true);
//
//                            return $response;
//
//                        } catch (\Exception $e) {
//                            return $this->getResponseBadRequest("Errur " . $e->getMessage());
//                        }


                    } else {
                        $action = $this->getRequest()->getPost()['do'];
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

                        $events = $this->getRequest()->getPost()['events'];
                        $timesheets = [];



                        switch($action){
                            case 'validatesci';
                                $timesheets = $timeSheetService->validateSci($events, $this->getCurrentPerson());
                                break;
                            case 'validateadm';
                                $timesheets = $timeSheetService->validateAdmin($events, $this->getCurrentPerson());
                                break;
                            case 'rejectsci';
                                $timesheets = $timeSheetService->rejectSci($events, $this->getCurrentPerson());
                                break;
                            case 'rejectadm';
                                $timesheets = $timeSheetService->rejectAdmin($events, $this->getCurrentPerson());
                                break;
                            case 'send';
                                throw new OscarException("Vous n'avez les droits pour soumettre.");
                                break;
                        }

                        $response = new JsonModel([
                            'timesheets' => $timesheets
                        ]);
                        $response->setTerminal(true);

                        return $response;
                    }

                } else {
                    if ($method == 'DELETE') {
                        $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($this->params()->fromQuery('timesheet'));
                        if ($timesheet) {
                            $this->getEntityManager()->remove($timesheet);
                            $this->getEntityManager()->flush();

                            return $this->getResponseOk("Créneau supprimé");
                        }

                        return $this->getResponseBadRequest();
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
                if (!array_key_exists($workpackageperson->getPerson()->getId(),
                    $declarants)
                ) {
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
     * todo Rejète une déclaration de temps
     */
    public function invalidateTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }
}