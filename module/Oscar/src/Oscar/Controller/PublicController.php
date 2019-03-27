<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityRequestService;
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

    public function parametersAction()
    {
        /** @var Authentification $auth */
        $auth = $this->getOscarUserContext()->getAuthentification();

        if( !$this->getCurrentPerson() ){
            throw new OscarException("Votre compte n'est associé à aucune fiche Personne dans Oscar");
        }

        // Récupération des envois automatiques
        $forceSend = $this->getConfiguration('oscar.notifications.fixed');

        $method = $this->getHttpXMethod();


        // Traitment des horaires
        if( $this->isAjax() && $this->params()->fromQuery('a') == 'schedule' ){

            /** @var TimesheetService $timesheetService */
            $timesheetService = $this->getServiceLocator()->get('TimesheetService');

            if( $method == 'GET' ){
                $datas = $timesheetService->getDayLengthPerson($this->getCurrentPerson());
                return $this->ajaxResponse($datas);
            }
            elseif ($method == 'POST'){

                $schedule = $this->params()->fromPost('days');
                try {
                    $this->getUserParametersService()->performChangeSchedule($schedule, $this->getCurrentPerson(), false);
                    return $this->getResponseOk();
                } catch ( OscarException $e ){
                    return $this->getResponseInternalError(sprintf('%s : %s', _('Impossible de modifier la répartition horaire'), $e->getMessage()));
                }
            }

        }

        if( $this->getHttpXMethod() == "POST" ){
            $action = $this->params()->fromPost('action');

            switch ($action) {

                // Modification du mode de déclaration
                case 'declaration-mode' :
                    try {
                        $this->getUserParametersService()->performChangeDeclarationMode($this->params()->fromPost('declarationsHours'));
                        return $this->getResponseOk();
                    } catch ( OscarException $e ){
                        return $this->getResponseInternalError(sprintf('%s : %s', _('Impossible de modifier le mode de déclaration'), $e->getMessage()));
                    }
                    break;

                case 'frequency' :
                    try {
                        $this->getUserParametersService()->performChangeFrequency($this->params()->fromPost('frequency', null));
                        return $this->getResponseOk();
                    } catch ( OscarException $e ){
                        return $this->getResponseInternalError(sprintf('%s : %s', _('Impossible de modifier le mode de déclaration'), $e->getMessage()));
                    }
                    break;

                // todo Modification de la fréquence


                // todo Modification des horaires (soumission)
            }

            return $this->getResponseBadRequest("Erreur d'usage");
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        $declarationsHours = $timesheetService->isDeclarationsHoursPerson($this->getCurrentPerson());
        $declarationsHoursOverwriteByAuth = $this->getConfiguration('oscar.declarationsHoursOverwriteByAuth');

        return [
            'subordinates' => $this->getPersonService()->getSubordinates($this->getCurrentPerson()),
            'managers' => $this->getPersonService()->getManagers( $this->getCurrentPerson()),
            'subordonates' => $this->getPersonService()->getSubordinates( $this->getCurrentPerson()),
            'scheduleEditable' => $this->getUserParametersService()->scheduleEditable(),
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
        /** @var TimesheetService $timeSheetService */
        $timeSheetService = $this->getServiceLocator()->get('TimesheetService');

        $validations = [];
        $isValidator = false;
        $person = $this->getOscarUserContext()->getCurrentPerson();
        $isRequestValidator = false;
        $requestValidations = false;

        if ($person) {
            try {
                $periodsRejected = $timeSheetService->getPeriodsConflictPerson($person);
                $isValidator = $timeSheetService->isValidator($person);
                $validations = $timeSheetService->getValidationToDoPerson($person);
            } catch (\Exception $e) {
                $this->getLogger()->error("Impossible de charger les déclarations en conflit pour $person : " . $e->getMessage());
            }


            try {
                /** @var ActivityRequestService $serviceDemandeActivite */
                $serviceDemandeActivite = $this->getServiceLocator()->get('ActivityRequestService');
                $requests = null;

                // Accès globale
                if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_REQUEST_MANAGE) ){
                    $requests = $serviceDemandeActivite->getAllRequestActivityUnDraft();
                }
                elseif ( count($organizations = $this->getOscarUserContext()->getOrganizationsWithPrivilege(Privileges::ACTIVITY_REQUEST_MANAGE)) > 0 ){
                    //die('ICI');
                    $requests = $serviceDemandeActivite->getAllRequestActivityUnDraft($organizations);
                }

                if( $requests !== null ){
                    $isRequestValidator = true;
                    $requestValidations = count($requests);
                }

            } catch (\Exception $e) {
                $this->getLogger()->error("Impossible de charger les demandes d'activité pour $person : " . $e->getMessage());
            }
        }


        return [
            'isRequestValidator' => $isRequestValidator,
            'requestValidations' => $requestValidations,
            'validations' => $validations,
            'isValidator' => $isValidator,
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
