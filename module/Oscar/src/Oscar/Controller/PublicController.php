<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarUserContext;
use Oscar\Service\TimesheetService;
use Zend\EventManager\Event;
use Zend\Mvc\Application;
use Zend\View\Model\ViewModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class PublicController extends AbstractOscarController
{

    public function gitlogAction(){
        exec('git log --pretty=format:"<span class="hash">%h</span><span class="author">%an</span><time>%ai</time><span class="message">%s</span>"', $log);
        return ['log' => $log];
    }

    public function testCalendarAction(){
        return [];
    }

    public function parametersAction()
    {
        /** @var Authentification $auth */
        $auth = $this->getOscarUserContext()->getAuthentification();

        // Récupération des envois automatiques
        $forceSend = $this->getConfiguration('oscar.notifications.fixed');

        if( $this->getHttpXMethod() == "POST" ){
            $action = $this->params()->fromPost('action');

            switch ($action) {

            }
            $declarationsHours = $this->params()->fromPost('declarationsHours');

            if( $declarationsHours !== null ){
                if( !$this->getConfiguration('oscar.declarationsHoursOverwriteByAuth', false) ){
                    return $this->getResponseInternalError("Cette option ne peut pas être modifiée");
                }

                $settings = $auth->getSettings() ?: [];
                $declarationsHours = $this->params()->fromPost('declarationsHours') == 'on' ? true : false;
                $settings['declarationsHours'] = $declarationsHours;
                $auth->setSettings($settings);
                $this->getEntityManager()->flush($auth);
                return $this->getResponseOk();
            }

            $frequency = $this->params()->fromPost('frequency', null);
            if( $frequency ) {
                $parameters = explode(',', $this->params()->fromPost('frequency'));
                $this->getLogger()->debug("Save for = " . $auth->getDisplayName());
                $settings = $auth->getSettings() ?: [];
                $settings['frequency'] = $parameters;
                $auth->setSettings($settings);
                $this->getEntityManager()->flush($auth);
                return $this->getResponseOk();
            }

            return $this->getResponseBadRequest("");
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        $declarationsHours = $timesheetService->isDeclarationsHoursPerson($this->getCurrentPerson());
        $declarationsHoursOverwriteByAuth = $this->getConfiguration('oscar.declarationsHoursOverwriteByAuth');

        return [
            'subordinates' => $this->getPersonService()->getSubordinates($this->getCurrentPerson()),
            'managers' => $this->getPersonService()->getManagers( $this->getCurrentPerson()),
            'subordonates' => $this->getPersonService()->getSubordinates( $this->getCurrentPerson()),
            'declarationsConfiguration' => null, //$timesheetService->getDeclarationConfigurationPerson($this->getCurrentPerson()),
            'person' => $this->getCurrentPerson(),
            'declarationsHours' => $declarationsHours,
            'declarationsHoursOverwriteByAuth' => $declarationsHoursOverwriteByAuth,
            'parameters' => $auth->getSettings(),
            'forceSend' => $forceSend
        ];
    }

    public function accessAction()
    {
        $accessResolverService = $this->getAccessResolverService();
        $actions = $accessResolverService->getActions();
        return [
            'actions'   => $actions,
            'roles'     => ActivityPerson::getRoles(),
        ];
    }

    /**
     * Page d'accueil.
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        $person = null;
        /** @var TimesheetService  $timeSheetService */
        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');

//        // est déclarant
//
//        // est validateur (Application)
//        $isValidateurScientifique = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI);
//        $isValidateurAdministratif = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM);
//
//        $activitiesValidation = [];
//        $periodsRejected = [];
//        $periodHorsLotsUnvalid = [];
//
//        $validationsHorsLot = [];
//        if( $this->getCurrentPerson() ) {
//            $validationsHorsLot = $timeSheetService->getValidationHorsLotByReferent($this->getCurrentPerson(), true);
//        }


//        $activityWithValidationUp = $timeSheetService->getActivitiesWithTimesheetSend();
//
//        ///
//        /** @var Activity $activity */
//        foreach ($activityWithValidationUp as $activity ){
//            if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity)){
//                $activitiesValidation[] = $activity;
//                continue;
//            }
//            if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY, $activity)){
//                $activitiesValidation[] = $activity;
//                continue;
//            }
//            if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity)){
//                $activitiesValidation[] = $activity;
//                continue;
//            }
//        }

        $validations = [];

        try {
            $person = $this->getOscarUserContext()->getCurrentPerson();

            if( $person ){
                // Déclaration en conflit
                $periodsRejected = $timeSheetService->getValidationPeriodPersonConflict($person);
                $validations = $timeSheetService->getValidationToDoPerson($person);
            }

        } catch( \Exception $e ){
            $this->getLogger()->error("Impossible de charger les déclarations en conflit pour $person : " . $e->getMessage());
        }
        return [
//            'validationsHorsLot' => $validationsHorsLot,
//            'activitiesValidation' => $activitiesValidation,
            'validations' => $validations,
            'periodsRejected' => $periodsRejected,
            'user' => $person
        ];
    }

    public function changelogAction()
    {
        $parser = new \Parsedown();
        return [
            'content'   => $parser->text(file_get_contents(getcwd().'/changelog-public.md'))
        ];
    }
    
    protected function getSuperView($message)
    {
        $view = new ViewModel(['message'=>$message]);
        $view->setTemplate('/none');
        return $view;
    }

    public function forAllAction()
    {
        return $this->getSuperView('For All');
    }
    
    public function forUserAction()
    {
        return $this->getSuperView('For User');
    }
    
    public function forAdminAction()
    {
        return $this->getSuperView('For Admin');
    }

    public function documentationAction()
    {
        $this->getEventManager()->trigger(new Event('foo', 'bar'));
        $doc = $this->params()->fromRoute('doc');
        if( $doc ){
            return [
                'contenu' => "super doc"
            ];
        } else {
            return [
                'contenu' => 'foo'
            ];
        }
        return [];
    }
}
