<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
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

        // est déclarant

        // est validateur (Application)
        $isValidateurScientifique = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI);
        $isValidateurAdministratif = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM);

        $activitiesValidation = [];
        $timesheetRejected = [];
        $activityWithValidationUp = $timeSheetService->getActivitiesWithTimesheetSend();

        /** @var Activity $activity */
        foreach ($activityWithValidationUp as $activity ){
            if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity) && $activity->hasTimesheetsUpForValidationSci() ){
                $activitiesValidation[] = $activity;
                continue;
            }
            if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity) && $activity->hasTimesheetsUpForValidationAdmin() ){
                $activitiesValidation[] = $activity;
                continue;
            }
        }


        try {
            $person = $this->getOscarUserContext()->getCurrentPerson();
            if( $person )
                $timesheetRejected = $timeSheetService->getTimesheetRejected($person);


        } catch( \Exception $e ){
        }
        return [
            'activitiesValidation' => $activitiesValidation,
            'timesheetRejected' => $timesheetRejected,
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
