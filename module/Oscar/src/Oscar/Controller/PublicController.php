<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRepository;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\TimesheetService;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseTimesheetService;
use Oscar\Traits\UseTimesheetServiceTrait;
use Oscar\Traits\UseUserParametersService;
use Oscar\Traits\UseUserParametersServiceTrait;
use Laminas\EventManager\Event;
use Laminas\View\Model\ViewModel;
use UnicaenSignature\Service\SignatureServiceAwareTrait;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class PublicController extends AbstractOscarController implements UseTimesheetService, UsePersonService,
                                                                  UseUserParametersService
{
    use UseTimesheetServiceTrait, UsePersonServiceTrait, UseUserParametersServiceTrait, SignatureServiceAwareTrait;

    /** @var ActivityRequestService */
    public $activityRequestService;

    /**
     * @return mixed
     */
    public function getActivityRequestService()
    {
        return $this->activityRequestService;
    }

    public function devVitejsAction()
    {
        return [];
    }

    /**
     * @param mixed $activityRequestService
     */
    public function setActivityRequestService($activityRequestService): void
    {
        $this->activityRequestService = $activityRequestService;
    }

    /**
     * PublicController constructor.
     * @param $timesheetService
     */
    public function __construct()
    {
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function gitlogAction()
    {
        $file = file_get_contents(__DIR__.'/../../../../../oscar-info.json');
        $infos = false;
        $error = false;
        if( !$file ){
            $error = '<div class="alert alert-danger">GITLOG non-disponible, vous pouvez le générer avec la commande <code>php bin/oscar.php infos</code></div>';
        } else {
            $infos = json_decode($file, true);
            if( !$infos ){
                $error = "Bad format JSON";
            }
        }
        return ['log' => $infos, 'error' => $error];
    }

    public function parametersAction()
    {
        /** @var Authentification $auth */
        $auth = $this->getOscarUserContextService()->getAuthentification();


        if (!$this->getCurrentPerson()) {
            throw new OscarException("Votre compte n'est associé à aucune fiche Personne dans Oscar");
        }

        // Récupération des envois automatiques
        $forceSend = $this->getOscarConfigurationService()->getConfiguration('notifications.fixed');

        $method = $this->getHttpXMethod();


        // Traitment des horaires
        if ($this->isAjax() && $this->params()->fromQuery('a') == 'schedule') {
            /** @var TimesheetService $timesheetService */
            $timesheetService = $this->getTimesheetService();

            if ($method == 'GET') {
                $datas = $timesheetService->getDayLengthPerson($this->getCurrentPerson());
                return $this->ajaxResponse($datas);
            }
            elseif ($method == 'POST') {
                $schedule = $this->params()->fromPost('days');
                try {
                    $this->getUserParametersService()->performChangeSchedule(
                        $schedule,
                        $this->getCurrentPerson(),
                        false
                    );
                    return $this->getResponseOk();
                } catch (OscarException $e) {
                    return $this->getResponseInternalError(
                        sprintf('%s : %s', _('Impossible de modifier la répartition horaire'), $e->getMessage())
                    );
                }
            }
        }

        if ($this->getHttpXMethod() == "POST") {
            $action = $this->params()->fromPost('action');

            switch ($action) {
                // Modification du mode de déclaration
                case 'declaration-mode' :
                    try {
                        $this->getUserParametersService()->performChangeDeclarationMode(
                            $this->params()->fromPost('declarationsHours')
                        );
                        return $this->getResponseOk();
                    } catch (OscarException $e) {
                        return $this->getResponseInternalError(
                            sprintf('%s : %s', _('Impossible de modifier le mode de déclaration'), $e->getMessage())
                        );
                    }
                    break;

                case 'frequency' :
                    try {
                        $this->getUserParametersService()->performChangeFrequency(
                            $this->params()->fromPost('frequency', null)
                        );
                        return $this->getResponseOk();
                    } catch (OscarException $e) {
                        return $this->getResponseInternalError(
                            sprintf(
                                '%s : %s',
                                _('Impossible de modifier la fréquence des notifications'),
                                $e->getMessage()
                            )
                        );
                    }
                    break;

                // todo Modification de la fréquence


                // todo Modification des horaires (soumission)
            }

            return $this->getResponseBadRequest("Erreur d'usage");
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getTimesheetService();

        $declarationsHours = $timesheetService->isDeclarationsHoursPerson($this->getCurrentPerson());
        $declarationsHoursOverwriteByAuth = $this->getOscarConfigurationService()->getConfiguration(
            'declarationsHoursOverwriteByAuth'
        );

        return [
            'subordinates'                     => $this->getPersonService()->getSubordinates($this->getCurrentPerson()),
            'managers'                         => $this->getPersonService()->getManagers($this->getCurrentPerson()),
            'subordonates'                     => $this->getPersonService()->getSubordinates($this->getCurrentPerson()),
            'scheduleEditable'                 => $this->getUserParametersService()->scheduleEditable(),
            'declarationsConfiguration'        => null,
            //$timesheetService->getDeclarationConfigurationPerson($this->getCurrentPerson()),
            'person'                           => $this->getCurrentPerson(),
            'declarationsHours'                => $declarationsHours,
            'declarationsHoursOverwriteByAuth' => $declarationsHoursOverwriteByAuth,
            'parameters'                       => $auth->getSettings() ?: [],
            'forceSend'                        => $forceSend
        ];
    }

    public function accessAction()
    {
        $accessResolverService = $this->getAccessResolverService();
        $actions = $accessResolverService->getActions();
        return [
            'actions' => $actions,
            'roles'   => ActivityPerson::getRoles(),
        ];
    }

    public function testAction()
    {
        if (DEBUG_OSCAR) {
//            $createRoot = new ActivityType();
//            $this->getEntityManager()->persist($createRoot);
//            $createRoot->setLft(1);
//            $createRoot->setRgt(2);
//            $createRoot->setLabel('ROOT');
//
//            $this->getLoggerService()->info("-----------------------------------------------------------");
//            $this->getLoggerService()->info("Création du Noeud ROOT : " . $createRoot->trac());
//
//            $this->getEntityManager()->flush($createRoot);

        }
        die("DEV ONLY");
    }

    /**
     * Page d'accueil.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $person = null;
        $validations = [];
        $isValidator = false;
        $person = $this->getOscarUserContextService()->getCurrentPerson();
        $isRequestValidator = false;
        $requestValidations = false;
        $periodsRejected = [];
        $documentsWait = [];


        if ($person) {
            try {
                $documentsWait = $this->getSignatureService()->getSignaturesRecipientsByEmailWaiting(
                    $person->getEmail(),
                    'internal'
                );
                foreach ($documentsWait as $d) {
                    $this->getLoggerService()->debug($d);
                }
            } catch (\Exception $e) {
            }


            $timeSheetService = $this->getTimesheetService();

            try {
                $periodsRejected = $timeSheetService->getPeriodsConflictPerson($person);
                $isValidator = $timeSheetService->isValidator($person);
                $validations = $timeSheetService->getValidationToDoPerson($person);
            } catch (\Exception $e) {
                $this->getLoggerService()->error(
                    "Impossible de charger les déclarations en conflit pour $person : " . $e->getMessage()
                );
            }

            $serviceDemandeActivite = $this->getActivityRequestService();

            try {
                /** @var ActivityRequestService $serviceDemandeActivite */
                $requests = null;

                // Accès globale
                if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_REQUEST_MANAGE)) {
                    $requests = $serviceDemandeActivite->getAllRequestActivityUnDraft();
                }
                elseif (count(
                        $organizations = $this->getOscarUserContextService()->getOrganizationsWithPrivilege(
                            Privileges::ACTIVITY_REQUEST_MANAGE
                        )
                    ) > 0) {
                    $requests = $serviceDemandeActivite->getAllRequestActivityUnDraft($organizations);
                }

                if ($requests !== null) {
                    $isRequestValidator = true;
                    $requestValidations = count($requests);
                }
            } catch (\Exception $e) {
                $this->getLoggerService()->error(
                    "Impossible de charger les demandes d'activité pour $person : " . $e->getMessage()
                );
            }
        }


        return [
            'isRequestValidator' => $isRequestValidator,
            'requestValidations' => $requestValidations,
            'validations'        => $validations,
            'isValidator'        => $isValidator,
            'periodsRejected'    => $periodsRejected,
            'documentsWait'      => $documentsWait,
            'user'               => $person
        ];
    }

    public function changelogAction()
    {
        $parser = new \Parsedown();
        return [
            'content' => $parser->text(file_get_contents(getcwd() . '/changelog-public.md'))
        ];
    }

    protected function getSuperView($message)
    {
        $view = new ViewModel(['message' => $message]);
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
        if ($doc) {
            return [
                'contenu' => "super doc"
            ];
        }
        else {
            return [
                'contenu' => 'foo'
            ];
        }
        return [];
    }
}
