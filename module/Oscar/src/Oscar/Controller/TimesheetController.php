<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Person;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
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
     * Enregistrement des heures.
     */
    public function sauvegardeAction()
    {
        return $this->getResponseBadRequest('NOT READY');
    }

    /**
     * Déclaration test
     */
    public function declarationAction()
    {
        $method = $this->getHttpXMethod();
        $person = $this->getOscarUserContext()->getCurrentPerson();
        $timesheets = [];

        if( $method == 'POST' ) {

            $datas = $this->getRequest()->getPost()['events'];
            $action = $this->getRequest()->getPost()['do'];


            if( $action == 'validate' ){
                foreach ($datas as $data) {
                    if ( $data['id'] ) {
                        /** @var TimeSheet $timeSheet */
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                        $timeSheet->setStatus(TimeSheet::STATUS_ACTIVE);
                        $json = $timeSheet->toJson();
                        $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                        $timesheets[] = $json;
                    } else {
                        return $this->getResponseBadRequest("DOBEFORE");
                    }
                }
                $this->getEntityManager()->flush();
            }

            else if( $action == 'send' ){
                foreach ($datas as $data) {
                    if ( $data['id'] ) {
                        /** @var TimeSheet $timeSheet */
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                        $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE);
                        $json = $timeSheet->toJson();
                        $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                        $timesheets[] = $json;
                    } else {
                        return $this->getResponseBadRequest("DOBEFORE");
                    }
                }
                $this->getEntityManager()->flush();

            } else {

                foreach ($datas as $data) {
                    if ($data['id'] && $data['id'] != 'null') {
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                    } else {
                        $timeSheet = new TimeSheet();
                        $this->getEntityManager()->persist($timeSheet);
                    }

                    $status = TimeSheet::STATUS_INFO;

                    if( isset($data['idworkpackage']) && $data['idworkpackage'] != 'null' ){
                        $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                        $timeSheet->setWorkpackage($workPackage);
                        $status = TimeSheet::STATUS_DRAFT;
                    } elseif ( isset($data['idactivity']) && $data['idactivity'] != 'null' ){
                        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                        $timeSheet->setActivity($activity);
                        $status = TimeSheet::STATUS_DRAFT;
                    }

                    $timeSheet->setComment($data['description'])
                        ->setLabel($data['label'])
                        ->setCreatedBy($person)
                        ->setPerson($person)
                        ->setStatus($status)
                        ->setDateFrom(new \DateTime($data['start']))
                        ->setDateTo(new \DateTime($data['end']));

                    $json = $timeSheet->toJson();
                    $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                    $timesheets[] = $json;

                }
                $this->getEntityManager()->flush($timeSheet);
            }
        }


        if( $method == 'GET' ) {
            $timesheets = [];
            $datas = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $this->getCurrentPerson()]);

            /** @var TimeSheet $data */
            foreach($datas as $data ){
                $json = $data->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($data);
                $timesheets[] = $json;
            }
        }

        if( $method == 'DELETE' ){
            $timesheetId = $this->params()->fromQuery('timesheet');
            if( $timesheetId ){
                $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
                $this->getEntityManager()->remove($timesheet);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('Créneaux supprimé');
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

        if( $this->getRequest()->isXmlHttpRequest() ){
            $response = new JsonModel($datasView);
            $response->setTerminal(true);
            return $response;
        }

        return $datasView;
    }

    /**
     * Déclaration test
     */
    public function declaration2Action()
    {
        return $this->indexActivityAction();
    }

    public function resolveTimeSheetCredentials( TimeSheet $timeSheet ){

        $deletable = false;
        $sendable = false;
        $editable = false;
        $validable = false;

        // En fonction du status
        switch( $timeSheet->getStatus() ){
            case TimeSheet::STATUS_DRAFT :
                $deletable = true;
                $sendable = true;
                $editable = true;
                $validable = false;
                break;

            case TimeSheet::STATUS_INFO :
                $deletable = true;
                $sendable = false;
                $editable = true;
                $validable = false;
                break;

            case TimeSheet::STATUS_TOVALIDATE :
                $deletable = false;
                $sendable = false;
                $editable = false;

                $validable = false; //$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_VALIDATE, $timeSheet->getWorkpackage())true; //$this->getCurrentPerson();
                break;
        }

        return [
            'deletable' => $deletable,
            'editable' => $editable,
            'sendable' => $sendable,
            'validable' => $validable
        ];
    }

    /**
     * Retourne la liste des déclarations pour une activités.
     */
    public function indexActivityAction()
    {
        $activityId = $this->params()->fromRoute('idactivity', null);
        $method = $this->getHttpXMethod();


        if (!$activityId) {
            return $this->getResponseBadRequest();
        }

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);
        $person = $this->getOscarUserContext()->getCurrentPerson();
        $timesheets = [];

        if (!$activity) {
            return $this->getResponseBadRequest("Activité introuvable");
        }

        if( $method == 'POST' ) {

            $datas = $this->getRequest()->getPost()['events'];
            $action = $this->getRequest()->getPost()['do'];


            if( $action == 'validate' ){
                foreach ($datas as $data) {
                    if ( $data['id'] ) {
                        /** @var TimeSheet $timeSheet */
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                        $timeSheet->setStatus(TimeSheet::STATUS_ACTIVE);
                        $json = $timeSheet->toJson();
                        $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                        $timesheets[] = $json;
                    } else {
                        return $this->getResponseBadRequest("DOBEFORE");
                    }
                }
                $this->getEntityManager()->flush();
            }

            else if( $action == 'send' ){
                foreach ($datas as $data) {
                    if ( $data['id'] ) {
                        /** @var TimeSheet $timeSheet */
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                        $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE);
                        $json = $timeSheet->toJson();
                        $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                        $timesheets[] = $json;
                    } else {
                        return $this->getResponseBadRequest("DOBEFORE");
                    }
                }
                $this->getEntityManager()->flush();

            } else {

                foreach ($datas as $data) {
                    if ($data['id'] && $data['id'] != 'null') {
                        $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                    } else {
                        $timeSheet = new TimeSheet();
                        $this->getEntityManager()->persist($timeSheet);
                    }

                    $status = TimeSheet::STATUS_INFO;

                    if( isset($data['idworkpackage']) && $data['idworkpackage'] != 'null' ){
                        $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                        $timeSheet->setWorkpackage($workPackage);
                        $status = TimeSheet::STATUS_DRAFT;
                    } elseif ( isset($data['idactivity']) && $data['idactivity'] != 'null' ){
                        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                        $timeSheet->setActivity($activity);
                        $status = TimeSheet::STATUS_DRAFT;
                    }

                    $timeSheet->setComment($data['description'])
                        ->setLabel($data['label'])
                        ->setCreatedBy($person)
                        ->setPerson($person)
                        ->setStatus($status)
                        ->setDateFrom(new \DateTime($data['start']))
                        ->setDateTo(new \DateTime($data['end']));

                    $json = $timeSheet->toJson();
                    $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                    $timesheets[] = $json;

                }
                $this->getEntityManager()->flush($timeSheet);
            }
        }


        if( $method == 'GET' ) {
            $timesheets = [];
            $datas = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $this->getCurrentPerson()]);

            /** @var TimeSheet $data */
            foreach($datas as $data ){
                $json = $data->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($data);
                $timesheets[] = $json;
            }
        }

        if( $method == 'DELETE' ){
            $timesheetId = $this->params()->fromQuery('timesheet');
            if( $timesheetId ){
                $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
                $this->getEntityManager()->remove($timesheet);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('Créneaux supprimé');
            }
            return $this->getResponseBadRequest("Impossible de supprimer le créneau : créneau inconnu");
        }


        $workpackages = $this->getEntityManager()->createQueryBuilder()
            ->select('wp')
            ->from(WorkPackage::class, 'wp')
            ->innerJoin('wp.persons', 'wpp')
            ->where('wpp.person = :person')
            ->setParameters([
                'person' => $this->getOscarUserContext()->getCurrentPerson()
            ])
            ->getQuery()
            ->getResult();

        $declarants = [];
        /** @var WorkPackage $workpackage */
        foreach( $workpackages as $workpackage ){
            /** @var WorkPackagePerson $workpackageperson */
            foreach( $workpackage->getPersons() as $workpackageperson ){
                echo $workpackageperson->getPerson()."<br>";
                $declarants[$workpackageperson->getPerson()->getId()] = $workpackageperson;
            }
        }

        $datasView = [
            'workpackages' => $workpackages,
            'declarants' => $declarants,
            'activity' => $activity,
            'message' => sprintf('[%s] Déclaration pour %s par %s', $method, $activity, $person),
            'dataReceived' => $this->getRequest()->getPost(),
            'timesheets' => $timesheets
        ];

        if( $this->getRequest()->isXmlHttpRequest() ){
            $response = new JsonModel($datasView);
            $response->setTerminal(true);
            return $response;
        }

        return $datasView;
    }

    /**
     * Retourne la liste des déclarations pour une personne.
     */
    public function indexPersonAction()
    {
        // todo Implémenter la récupération des déclarations de temps pour une person
        return $this->getResponseNotImplemented();
    }

    /**
     * Affiche l'écran de déclaration de temps pour la personne et l'activité
     * donnée.
     *
     *
     */
    public function declarationPersonActivityAction()
    {
        die('TODO');
    }

    /**
     * Retourne la liste des déclarations pour une personne dans une activité.
     */
    public function indexPersonActivityAction()
    {
        // Récupération de la
        $personId = $this->params()->fromRoute('idperson', null);
        $activityId = $this->params()->fromRoute('idactivity', null);

        if (!$personId || !$activityId) {
            return $this->getResponseBadRequest();
        }
        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

        /** @var Person $person */
        $person = $this->getEntityManager()->getRepository(Person::class)->find($personId);

        if (!$activity || !$person) {
            return $this->getResponseBadRequest();
        }

        if (!$activity->hasPerson($person)) {
            die("Vous n'êtes pas impliqué dans cette activité");
        }

        // Déclarations précédentes
        $timesheets = $this->getEntityManager()->createQueryBuilder()
            ->from(TimeSheet::class, 't')
            ->select('t')
            ->innerJoin('t.person', 'p')
            ->innerJoin('t.workpackage', 'w')
            ->where('w.activity = :activity')
            ->andWhere('t.person = :person')
            ->setParameters([
                'person' => $person,
                'activity' => $activity
            ])->getQuery()->getResult();

        /** @var Request $request */
        $request = $this->getRequest();

        $declarations = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            $period = sprintf('period-%s', $timesheet->getDateFrom()->format('Y-n'));
            $wp = $timesheet->getWorkpackage()->getId();
            if (!isset($declarations[$period])) {
                $declarations[$period] = [];
            }

            $declarations[$period][$wp] = [
                'id' => $timesheet->getId(),
                'time' => $timesheet->getTime(),
                'comment' => $timesheet->getComment(),
                'status' => $timesheet->getStatus(),
                'ts' => $timesheet
            ];
        }

        if (count($activity->getWorkPackages()) <= 0) {
            throw new \Exception("Aucun lot de travail dans cette activité.");
        }

        if ($request->isPost()) {

            // Récupération des lots de travail ordonnés par ID
            $wps = $this->getEntityManager()->createQueryBuilder()
                ->from(WorkPackage::class, 'w', 'w.id')
                ->select('w')
                ->where('w.activity = :activity')
                ->setParameter('activity', $activity)
                ->getQuery()
                ->getResult();


            $datas = $request->getPost()->toArray();

            foreach ($datas['time'] as $periodTag => $data) {

                $period = str_replace('period-', '', $periodTag);
                $submit = false;
                if( isset($data['submit']) ){
                    $submit = true;
                    unset($data['submit']);
                }

                foreach ($data as $wpID => $timeData) {

                    $time = intval($timeData['time']);
  
                    $comment = $timeData['comment'];

                    if( isset($declarations[$periodTag]) && isset($declarations[$periodTag][$wpID]) ){
                        $timesheet = $declarations[$periodTag][$wpID]['ts'];
                    } else {
                        $timesheet = new TimeSheet();
                        $this->getEntityManager()->persist($timesheet);
                    }

                    if ( $time === "" ) {
                        if( $timesheet->getId() ){
                            $this->getEntityManager()->remove($timesheet);
                        }
                        continue;
                    }

                    $start = new \DateTime($period . '-01');
                    $end = new \DateTime(date('Y-m-t', strtotime($start->format('Y-m-d'))));
                    $wp = $wps[$wpID];

                    $timesheet->setDateFrom($start)
                        ->setDateTo($end)
                        ->setWorkpackage($wp)
                        ->setPerson($person)
                        ->setComment($comment)
                        ->setTime($time);
                    if( $submit === true ){
                        $timesheet->setStatus(TimeSheet::STATUS_TOVALIDATE);
                    }
                }
            }

            $this->getEntityManager()->flush();

            return $this->redirect()->toRoute('timesheet/activityperson', [
                'idactivity' => $activity->getId(),
                'idperson' => $person->getId()
            ]);
        }

        $wps = [];

        return [
            'declarations' => $declarations,
            'activity' => $activity,
            'person' => $person,
        ];
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
     * @todo Enregistre une déclaration de temps
     */
    public function saveTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }

    /**
     * @todo Soumet une déclaration de temps
     */
    public function submitTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }

    /**
     * todo Valide une déclaration de temps
     */
    public function validateTimesheetAction()
    {
        $method = $this->getHttpXMethod();

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));

        if( !$activity ){
            return $this->getResponseNotFound(sprintf("L'activité %s n'existe pas", $activity));
        }

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_WORKPACKAGE_VALIDATE, $activity);

        if( $this->getRequest()->isXmlHttpRequest() ) {
            if ($method == 'GET') {

                // Récupération des déclarations pour cette activité
                $timesheets = $this->getEntityManager()->createQueryBuilder()
                    ->select('t')
                    ->from(TimeSheet::class, 't')
                    ->where('t.activity = :activity')
                    ->andWhere('t.status != :status')
                    ->setParameters([
                        'activity' => $activity,
                        'status' => TimeSheet::STATUS_DRAFT
                    ])
                    ->getQuery()
                    ->getResult()
                    ;//->findBy(['activity' => $activity]);

                $declaration = [];
                /** @var TimeSheet $timesheet */
                foreach ($timesheets as $timesheet) {
                    $json = $timesheet->toJson();
                    $json['credentials'] = [
                        'deletable' => true,
                        'editable' => true,
                        'sendable' => $timesheet->getStatus() == TimeSheet::STATUS_DRAFT,
                        'validable' => $timesheet->getStatus() == TimeSheet::STATUS_TOVALIDATE
                    ];
                    $declaration[] = $json;
                }
                $response = new JsonModel([
                    'timesheets' => $declaration
                ]);
                $response->setTerminal(true);

                return $response;

            } else {
                /** @var Request $request */
                $request = $this->getRequest();

                if ($method == 'POST') {
                    $events = $request->getPost('events', []);
                    if( count($events) == 1 && $events[0]['id'] == 'null'  ){
                        $event = $events[0];
                        $person = $this->getEntityManager()->getRepository(Person::class)->find($event['owner_id']);

                        /** @var WorkPackage $workpackage */
                        $workpackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($event['idworkpackage']);

                        if( !$person ){
                            return $this->getResponseBadRequest('Personne inconnue !');
                        }

                        if( !$workpackage ){
                            return $this->getResponseBadRequest('Lot de travail inconnu !');
                        }

                        try {
                            $timesheet = new TimeSheet();
                            $this->getEntityManager()->persist($timesheet);
                            $timesheet->setPerson($person)
                                ->setActivity($activity)
                                ->setLabel((string)$workpackage)
                                ->setDateFrom(new \DateTime($event['start']))
                                ->setDateTo(new \DateTime($event['end']))
                                ->setStatus(TimeSheet::STATUS_TOVALIDATE)
                                ->setWorkpackage($workpackage);
                            $this->getEntityManager()->flush($timesheet);
                            $json = $timesheet->toJson();
                            $json['credentials'] = [
                                'deletable' => true,
                                'editable' => true,
                                'sendable' => $timesheet->getStatus() == TimeSheet::STATUS_DRAFT,
                                'validable' => $timesheet->getStatus() == TimeSheet::STATUS_TOVALIDATE
                            ];
                            $response = new JsonModel([
                                'timesheets' => [$json]
                            ]);
                            $response->setTerminal(true);
                            return $response;

                        } catch( \Exception $e ){
                            return $this->getResponseBadRequest("Errur " . $e->getMessage());
                        }



                    }
                    else {
                        return $this->performRestDo();
                    }

                } else if ($method == 'DELETE') {
                    $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($this->params()->fromQuery('timesheet'));
                    if( $timesheet ){
                        $this->getEntityManager()->remove($timesheet);
                        $this->getEntityManager()->flush();
                        return $this->getResponseOk("Créneau supprimé");
                    }
                    return $this->getResponseBadRequest();
                }
                return $this->getResponseBadRequest();
            }
        }

        $declarants = [];
        /** @var WorkPackage $workpackage */
        foreach( $activity->getWorkPackages() as $workpackage ){
            /** @var WorkPackagePerson $workpackageperson */
            foreach( $workpackage->getPersons() as $workpackageperson ){
                if( !array_key_exists($workpackageperson->getPerson()->getId(), $declarants) ) {
                    echo $workpackageperson->getPerson() . "<br>";
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
     * Cette méthode gère les changements d'état transmis par l'interface.
     */
    protected function performRestDo(){
        $datas = $this->getRequest()->getPost()['events'];
        $action = $this->getRequest()->getPost()['do'];

        // Résultat à retourner
        $timesheets = [];
        $newStatus = null;

        // Status à obtenir
        switch( $action ){
            case 'validate' :
                $newStatus = TimeSheet::STATUS_ACTIVE;
                break;
            case 'send' :
                $newStatus = TimeSheet::STATUS_TOVALIDATE;
                break;
            case 'reject' :
                $newStatus = TimeSheet::STATUS_CONFLICT;
                break;
            default :
                //return $this->getResponseBadRequest('Opération inconnue !');
        }

        // Traitement
        foreach ($datas as $data) {
            if ( $data['id'] ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                if ($newStatus) {
                    $timeSheet->setStatus($newStatus);
                } else {
                    $timeSheet->setDateFrom(new \DateTime($data['start']))
                        ->setDateTo(new \DateTime($data['end']));
                }

                $json = $timeSheet->toJson();
                $json['credentials'] = [
                    'deletable' => true,
                    'editable' => true,
                    'sendable' => $timeSheet->getStatus() == TimeSheet::STATUS_DRAFT,
                    'validable' => $timeSheet->getStatus() == TimeSheet::STATUS_TOVALIDATE
                ];
                $timesheets[] = $json;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        // Enregistrement
        $this->getEntityManager()->flush();

        // Retour
        $response = new JsonModel([
            'timesheets' => $timesheets
        ]);
        $response->setTerminal(true);
        return $response;
    }

    /**
     * todo Rejète une déclaration de temps
     */
    public function invalidateTimesheetAction()
    {
        return $this->getResponseNotImplemented();
    }
}