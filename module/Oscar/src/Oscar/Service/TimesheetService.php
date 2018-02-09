<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\TimesheetRepository;
use Oscar\Entity\WorkPackage;
use Oscar\Exception\OscarCredentialException;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Form\Element\Time;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class TimesheetService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
    }

    public function getTimesheetToValidateByOrganization( Organization $organization ){
        // Activities

        $query = $this->getEntityManager()->createQueryBuilder()->select('ts')
            ->from(TimeSheet::class, 'ts')
            ->innerJoin('ts.activity', 'a')
            ->innerJoin('a.project', 'p')
            ->leftJoin('a.organizations', 'o1')
            ->leftJoin('o1.roleObj', 'roleo1')
            ->leftJoin('p.partners', 'o2')
            ->leftJoin('o2.roleObj', 'roleo2')

            ->where('ts.status = :status')
            ->andWhere('(o1.organization = :organization AND roleo1.principal = true) OR (o2.organization = :organization AND roleo2.principal = true)')

            ->setParameters([
                'status' => TimeSheet::STATUS_TOVALIDATE,
                'organization' => $organization
            ]);
        return $query->getQuery()->getResult();
    }


    /**
     * Retourne les créneaux de la personne regroupès par activité
     * @param Person $person
     */
    public function getPersonTimesheetsCSV( Person $person, Activity $activity, $validatedOnly = false){
        $fmt = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'd MMMM Y');

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->andWhere('t.activity = :activity')
            ->orderBy('t.activity, t.dateFrom')
            ;

        $datas = [];

        $workpackages = [];

        /** @var WorkPackage $workPackage */
        foreach ($activity->getWorkPackages() as $workPackage ){
            $workpackages[] = $workPackage->getCode();
        }
        sort($workpackages);

        $ligneHeader = [ (string)$person ];
        foreach ($workpackages as $wpCode) {
            $ligneHeader[] = $wpCode;
        }

        $ligneHeader[] = "Commentaires";
        $ligneHeader[] = "Nbr créneaux";
        $ligneHeader[] = "Total jour";

        $datas[] = $ligneHeader;

        $days = [];
        $yearStart = (int) $activity->getDateStart()->format('Y');
        $yearEnd = (int) $activity->getDateEnd()->format('Y');

        // Prétraitement des créneaux
        $creneaux = [];
        $timesheets = $query->getQuery()->setParameters([
            'person' => $person,
            'activity' => $activity
        ])->getResult();

        /** @var TimeSheet $timesheet */
        foreach( $timesheets as $timesheet ){

            $dayStr =   $fmt->format($timesheet->getDateFrom());
            $code =     $timesheet->getWorkpackage()->getCode();
            $comment =  trim($timesheet->getComment());
            $duration = (float) $timesheet->getDuration();

            if( !array_key_exists($dayStr, $creneaux) ){
                $creneaux[$dayStr] = [];
                foreach ($workpackages as $wpCode) {
                    $creneaux[$dayStr][$wpCode] = 0.0;
                }
                $creneaux[$dayStr]['total'] = 0.0;
                $creneaux[$dayStr]['qte'] = 0;
                $creneaux[$dayStr]['commentaire'] = [];
            }

            $creneaux[$dayStr][$code] += $duration;
            $creneaux[$dayStr]['commentaire'][] = $comment;
            $creneaux[$dayStr]['total'] += $duration;
            $creneaux[$dayStr]['qte'] += 1;
        }


        $content = [];

        foreach ($creneaux as $dayStr=>$dayData) {
            $ligne = [$dayStr];
            foreach ($workpackages as $wpCode) {
                $ligne[] = $creneaux[$dayStr][$wpCode];
            }
            $ligne[] = implode(" - ", array_unique($creneaux[$dayStr]['commentaire']));
            $ligne[] = $creneaux[$dayStr]['qte'];
            $ligne[] = $creneaux[$dayStr]['total'];
            $datas[] = $ligne;
        }

        return $datas;
    }

    /**
     * Retourne les créneaux de la personne regroupès par activité
     * @param Person $person
     */
    public function getPersonTimesheets( Person $person, $validatedOnly = false, $periodFilter = null, $activity = null ){

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->orderBy('t.activity, t.dateFrom')
            ->setParameter('person', $person);

        if( $activity != null ){
            $query->andWhere('t.activity = :activity')
                ->setParameter('activity', $activity);
        }

        $datas = [];

        /** @var TimeSheet $timesheet */
        foreach( $query->getQuery()->getResult() as $timesheet ){
            if( !$timesheet->getActivity() ) continue;

            $activityId = $timesheet->getActivity()->getId();
            $period = $timesheet->getDateFrom()->format('Y-m');

            if( $periodFilter!== null && $periodFilter != $period )
                continue;

            if( !array_key_exists($activityId, $datas) ){
                $datas[$activityId] = [
                  'activityObj' => $timesheet->getActivity(),
                  'activity' => (string) $timesheet->getActivity(),
                  'project' => (string) $timesheet->getActivity()->getProject(),
                  'activity_id' => $timesheet->getActivity()->getId()
                ];
            }

            if( !array_key_exists($period, $datas[$activityId]) ){
                $datas[$activityId][$period] = [
                    'unvalidate' => false,
                    'total' => 0.0,
                ];
                /** @var WorkPackage $wp */
                foreach ($timesheet->getActivity()->getWorkPackages() as $wp ){
                    if( !array_key_exists($wp->getCode(), $datas[$activityId]['timesheets'][$period]) )
                        $datas[$activityId]['timesheets'][$period][$wp->getCode()] = [
                            'total' => 0.0
                        ];
                }
            }
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]['total'] += $timesheet->getDuration();
            $datas[$activityId]['timesheets'][$period]['total'] += $timesheet->getDuration();
            if( $timesheet->getStatus() != TimeSheet::STATUS_ACTIVE ){
                $datas[$activityId]['timesheets'][$period]['unvalidate'] = true;
            }

            $day = (string) $timesheet->getDateFrom()->format('j');
            if (!array_key_exists($day, $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()])){
                $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()][$day] = 0.0;
                $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]["crenaux_".$day] = 0;
            }
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]["crenaux_".$day]++;
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()][$day] += $timesheet->getDuration();

        }
        return $datas;
    }

    public function getDeclarers(){
        // Récupération des IDS des déclarants
        $timesheets = $this->getTimesheetRepository()->getTimesheetsWithWorkPackage();
        $out = [];
        $persons = [];
        $activities = [];
        /** @var TimeSheet $timesheet */
        foreach( $timesheets as $timesheet ){
            /** @var Person $currentPerson */
            $currentPerson = $timesheet->getPerson();

            if( !array_key_exists($currentPerson->getId(), $persons) ){
                $persons[$currentPerson->getId()] = $currentPerson->toJson();
                $persons[$currentPerson->getId()]['declarations'] = [];
            }

            /** @var Activity $currentActivity */
            $currentActivity = $timesheet->getActivity();

            if( !array_key_exists($currentActivity->getId(), $persons[$currentPerson->getId()]['declarations']) ){
                $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()] = $currentActivity->toJson();
                $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()]['timesheets'] = [];
            }

            $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()]['timesheets'][] = $timesheet->toJson();

            $out[] = $timesheet->toJson();

        }
        return [
            'persons' => $persons,
        ];
    }



    public function getActivitiesWithTimesheetSend(){
        $activities = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a')
            ->innerJoin('a.workPackages', 'w')
            ->innerJoin('w.timesheets', 't');
        return $activities->getQuery()->getResult();
    }

    public function getTimesheetRejected( Person $person ){
         $timesheets = $this->getEntityManager()->createQueryBuilder()->select('t')
            ->from(TimeSheet::class, 't')
            ->where('t.person = :person AND t.status = :status')
             ->setParameter('person', $person)
             ->setParameter('status', TimeSheet::STATUS_CONFLICT);
        return $timesheets->getQuery()->getResult();
    }


    /**
     * Envoi des déclarations.
     *
     * @param $data TimeSheet|TimeSheet[]
     * @param $by
     * @throws OscarException
     */
    public function send($datas, $by)
    {
        $timesheets = [];
        if( !$datas ){
            throw new \Exception("Invalid datas");
        }

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');
        $activityNotification = [];

        foreach ($datas as $data) {
            if ($data['id']) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE)
                    ->setSendBy((string)$this->getOscarUserContext()->getCurrentPerson())
                    ->setRejectedSciComment(null)
                    ->setRejectedSciAt(null)
                    ->setRejectedSciBy(null)
                    ->setRejectedSciById(null)
                    ->setRejectedAdminComment(null)
                    ->setRejectedAdminAt(null)
                    ->setRejectedAdminBy(null)
                    ->setRejectedAdminById(null)
                    ->setValidatedSciAt(null)
                    ->setValidatedSciBy(null)
                    ->setValidatedSciById(null)
                    ->setValidatedAdminAt(null)
                    ->setValidatedAdminBy(null)
                    ->setValidatedAdminById(null)
                ;
                $activityNotification[$timeSheet->getActivity()->getId()] = $timeSheet->getActivity();

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        try {
            $notificationService->notifyActivitiesTimesheetSend($activityNotification);
        } catch ( \Exception $e ){
            //$this->getServiceLocator()->get('Logger')->error($e->getMessage() ." - " . $e->getTraceAsString());
        }
        return $timesheets;
    }

    /**
     * Retourne la liste des déclaration de la personne.
     *
     * @param Person $person
     * @return array
     */
    function allByPerson(Person $person, $personCredentials = null)
    {
        $timesheets = [];
        $datas = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $person]);

        /** @var TimeSheet $data */
        foreach ($datas as $data) {
            $json = $data->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($data, $personCredentials);
            $timesheets[] = $json;
        }

        return $timesheets;
    }

    public function allByActivity( Activity $activity ){
        $timesheets = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(TimeSheet::class, 't')
            ->where('t.activity = :activity')
            ->setParameters([
                'activity' => $activity
            ])
            ->getQuery()
            ->getResult();

        $declaration = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            $json = $timesheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timesheet);
            $declaration[] = $json;
        }

        return $declaration;
    }

    /**
     * Résolution des droits sur une déclaration
     * @param TimeSheet $timeSheet
     * @return array
     */
    public function resolveTimeSheetCredentials(TimeSheet $timeSheet, $person = null)
    {
        if ($person == null) {
            $person = $this->getOscarUserContext()->getCurrentPerson();
        }

        $deletable = false;
        $isOwner = $timeSheet->getPerson() == $person;

        // Le créneau ne peut être envoyé que par sont propriétaire et si
        // le status est "Bouillon"
        $sendable = $isOwner && $timeSheet->getStatus() == TimeSheet::STATUS_DRAFT;

        // Seul les déclarations en brouillon peuvent être éditées
        $editable = in_array($timeSheet->getStatus(), [TimeSheet::STATUS_DRAFT, TimeSheet::STATUS_INFO]) && $isOwner;

        // Validation scientifique (déja validé ou la personne a les droits)

        $validableSci = false;
        $validableAdm = false;

        if ($timeSheet->getStatus() == TimeSheet::STATUS_TOVALIDATE){
            $validableSci = $timeSheet->getValidatedSciAt() ?
                false :
                $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI,
                    $timeSheet->getActivity());

            // Validation administrative
            $validableAdm = $timeSheet->getValidatedAdminAt() ?
                false :
                $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM,
                    $timeSheet->getActivity());
        }

        // En fonction du status
        switch ($timeSheet->getStatus()) {
            case TimeSheet::STATUS_DRAFT :
                $deletable = $isOwner;
                break;

            case TimeSheet::STATUS_INFO :
                $deletable = true;
                break;

            case TimeSheet::STATUS_CONFLICT :
                $deletable = $isOwner;
                $editable = $isOwner;
                break;

            case TimeSheet::STATUS_TOVALIDATE :
                $deletable = false;
                $editable = false;
                break;
        }

        return [
            'deletable' => $deletable,
            'editable' => $editable,
            'sendable' => $sendable,
            'validableSci' => $validableSci,
            'validableAdm' => $validableAdm
        ];
    }

    public function createOrMerge( $datas ){

    }

    /**
     * Création des déclarations.
     *
     * @param $datas
     * @param $by
     * @return array
     */
    public function create($datas, $by)
    {
        foreach ($datas as $data) {
            if (array_key_exists('id', $data) && $data['id'] != null) {
                $this->getServiceLocator()->get('Logger')->info("MAJ " . $data['id']);
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
            } else {
                $this->getServiceLocator()->get('Logger')->info("ADD " . $data['id']);
                $timeSheet = new TimeSheet();
                $this->getEntityManager()->persist($timeSheet);
            }

            $this->getServiceLocator()->get('Logger')->info("owner " . $by);
            $status = TimeSheet::STATUS_INFO;

            $this->getServiceLocator()->get('Logger')->info(print_r($data, true));

            if (isset($data['idworkpackage']) && $data['idworkpackage'] != 'null') {
                /** @var WorkPackage $workPackage */
                $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                $timeSheet->setWorkpackage($workPackage);
                $status = TimeSheet::STATUS_DRAFT;
            } elseif (isset($data['idactivity']) && $data['idactivity'] != 'null') {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                $timeSheet->setActivity($activity);
                $status = TimeSheet::STATUS_DRAFT;
            } else {
                $timeSheet->setWorkpackage(null)->setActivity(null);
            }

            $timeSheet->setComment($data['description'])
                ->setIcsFileUid($data['icsfileuid'] ? $data['icsfileuid'] : '')
                ->setIcsFileDateAdded($data['icsfileuid'] ? new \DateTime() : null)
                ->setIcsFileName($data['icsfilename'] ? $data['icsfilename'] : '')
                ->setIcsUid($data['icsuid'] ? $data['icsuid'] : '')
                ->setLabel($data['label'])
                ->setCreatedBy($by)
                ->setPerson($by)
                ->setStatus($status)
                ->setDateFrom(new \DateTime($data['start']))
                ->setDateTo(new \DateTime($data['end']));

            if( $status == TimeSheet::STATUS_INFO ){
                $timeSheet->setActivity(null)
                    ->setWorkpackage(null)
                    ->setSendBy(null);
                $timeSheet = $this->resetValidationData($timeSheet);
            }

            $json = $timeSheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet, $by);
            $timesheets[] = $json;

        }
        $this->getEntityManager()->flush($timeSheet);

        return $timesheets;
    }


    /**
     * Supprimer les données des champs de validation/rejet dans la déclaration.
     *
     * @param TimeSheet $timeSheet
     * @return TimeSheet
     */
    public function resetValidationData( TimeSheet $timeSheet ){
        return $timeSheet
            ->setRejectedAdminById(null)
            ->setRejectedAdminBy(null)
            ->setRejectedAdminAt(null)
            ->setRejectedAdminComment(null)
            ->setRejectedSciAt(null)
            ->setRejectedSciBy(null)
            ->setRejectedSciById(null)
            ->setRejectedSciComment(null)
            ->setRejectedAdminComment(null);
    }

    /**
     * Suppression des créneaux issues de l'ICS.
     *
     * @param $icsUid UID du fichier ICAL source
     * @param $by Person à l'origine de la suppression
     */
    public function deleteIcsFileUid($icsUid, $by){
        // Récupération des créneaux correspondant
        $timesheets = $this->getTimesheetRepository()->getTimesheetsByIcsFileUid($icsUid);


        $this->getServiceLocator()->get('Logger')->info("Nombre de créneaux à traiter : " . count($timesheets));

        // Liste des problèmes
        $warnings = [];

        // Status éligibles à la suppression
        $status = [TimeSheet::STATUS_DRAFT, TimeSheet::STATUS_TOVALIDATE_ADMIN, TimeSheet::STATUS_TOVALIDATE, TimeSheet::STATUS_TOVALIDATE_SCI];


        /** @var TimeSheet $timesheet */
        foreach( $timesheets as $timesheet ){
            if($timesheet->getPerson() != $by ){
                $warnings[] = sprintf("Le créneau '%s' n'a pas été supprimé (owner error).", $timesheet);
                continue;
            }
            if( !in_array($timesheet->getStatus(), $status) ){
                $warnings[] = sprintf("Le créneau '%s' n'a pas été supprimé (statut error).", $status);
                continue;
            }
            $this->getEntityManager()->remove($timesheet);
        }
        $this->getEntityManager()->flush();
        $this->getServiceLocator()->get('Logger')->info("warnings : " . count($warnings));
        return $warnings;
    }


    /**
     * @return TimesheetRepository
     */
    protected function getTimesheetRepository(){
        return $this->getEntityManager()->getRepository(TimeSheet::class);
    }

    /**
     * Suppression du créneau.
     *
     * @param $timesheetId Identifiant du créneau à supprimer
     * @param $currentPerson
     * @return bool
     * @throws OscarException
     */
    public function delete($timesheetId, $currentPerson)
    {
        $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
        if (!$timesheet) {
            throw new OscarException("Créneau introuvable.");
        }

        try {
            $this->getEntityManager()->remove($timesheet);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            throw new OscarException("BD Error : Impossible de supprimer le créneau.");
        }

        return true;
    }

    public function rejectSci( $datas, $by ){
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus(TimeSheet::STATUS_CONFLICT)
                    ->setRejectedSciAt(new \DateTime())
                    ->setRejectedSciBy($currentPersonName)
                    ->setRejectedSciById($currentPersonId)
                    ->setRejectedSciComment($data['rejectedSciComment'])
                    ->setValidatedSciAt(null)
                    ->setValidatedSciBy(null)
                    ->setValidatedSciById(null);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été rejetées scientifiquement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'rejectsci',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        $this->sendStackedNotifications();


        return $timesheets;
    }


    private $notificationsDatas;

    protected function stackNotification( $message, Activity $activity, $action, array $persons )
    {
        if( $this->notificationsDatas === null ){
            $this->notificationsDatas = [];
        }
        $key = 'Activity:' . $action . ':' . $activity->getId();
        if( !array_key_exists($key, $this->notificationsDatas) ){
            $this->notificationsDatas[$key] = [
                'message' => $message,
                'action' => $action,
                'persons' => $persons,
                'activity' => $activity
            ];
        } else {
            $this->notificationsDatas[$key]['persons'] = array_unique(array_merge($this->notificationsDatas[$key]['persons'], $persons));
        }
    }

    protected function getStackedNotifications()
    {
        return $this->notificationsDatas;
    }

    /**
     * @return NotificationService
     */
    protected function getServiceNotification(){
        return $this->getServiceLocator()->get("NotificationService");
    }

    protected function sendStackedNotifications(){
        if( $this->notificationsDatas ){
            foreach ($this->notificationsDatas as $activityKey=>$datas) {
                $this->getServiceNotification()->notification(
                    sprintf($datas['message'], $datas['activity']->log()),
                    $datas['persons'],
                    'Activity',
                    $datas['activity']->getId(),
                    $datas['action'],
                    new \DateTime(),
                    new \DateTime()
                );
            }
        }
    }

    public function validateSci($datas, $by)
    {
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;

        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus($timeSheet->getValidatedAdminAt() ? TimeSheet::STATUS_ACTIVE : TimeSheet::STATUS_TOVALIDATE)
                    // On supprime les informations de rejet
                    ->setRejectedSciAt(null)
                    ->setRejectedSciBy(null)
                    ->setRejectedSciComment(null)

                    ->setValidatedSciAt(new \DateTime())
                    ->setValidatedSciBy($currentPersonName)
                    ->setValidatedSciById($currentPersonId);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été validés scientifiquement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'validatesci',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        $this->sendStackedNotifications();


        return $timesheets;
    }

    /**
     * Déclenchement du rejet administratif.
     *
     * @param $datas
     * @param $by
     * @return array
     */
    public function rejectAdmin( $datas, $by ){
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus(TimeSheet::STATUS_CONFLICT)
                    ->setRejectedAdminAt(new \DateTime())
                    ->setRejectedAdminBy($currentPersonName)
                    ->setRejectedAdminComment($data['rejectedAdminComment'])
                    ->setRejectedAdminById($currentPersonId);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été rejetées administrativement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'rejectadmin',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        $this->sendStackedNotifications();

        return $timesheets;
    }

    /**
     * Déclenchement de la validation administrative.
     *
     * @param $datas
     * @param $by
     * @return array
     */
    public function validateAdmin($datas, $by)
    {
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus($timeSheet->getValidatedSciAt() ? TimeSheet::STATUS_ACTIVE : TimeSheet::STATUS_TOVALIDATE)
                    ->setValidatedAdminAt(new \DateTime())
                    ->setValidatedAdminBy($currentPersonName)
                    ->setValidatedAdminById($currentPersonId)
                    ->setRejectedAdminAt(null)
                    ->setRejectedAdminBy(null)
                    ->setRejectedAdminComment(null);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été validées administrativement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'validateadmin',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }
        $this->sendStackedNotifications();
        return $timesheets;
    }


}
