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
use Oscar\Entity\Currency;
use Oscar\Exception\OscarException;
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityPaymentForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
use Oscar\Service\NotificationService;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ActivityPaymentController extends AbstractOscarController
{

    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES
    /** @var ProjectGrantService */
    private $projectGrantService;

    /** @var NotificationService */
    private $notificationService;

    /** @var PersonService */
    private $personService;

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->projectGrantService;
    }

    /**
     * @param ProjectGrantService $projectGrantService
     */
    public function setProjectGrantService(ProjectGrantService $projectGrantService): void
    {
        $this->projectGrantService = $projectGrantService;
    }

    /**
     * @return NotificationService
     */
    public function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function setNotificationService(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @return PersonService
     */
    public function getPersonService(): PersonService
    {
        return $this->personService;
    }

    /**
     * @param PersonService $personService
     */
    public function setPersonService(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////// SERVICES





    /**
     * Retourne les versements à venir dans les 15 jours à venir.
     */
    public function incomeAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsIncoming();
        return [
            'payments' => $payments,
            'getDate' => 'getDatePredicted'
        ];
    }

    /**
     * Liste des versements en retard.
     */
    public function lateAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsLate();
        return [
            'payments' => $payments,
            'getDate' => 'getDatePredicted'
        ];
    }

    /**
     * Liste des versements de type "écart".
     */
    public function differenceAction()
    {
        $payments = $this->getProjectGrantService()->getPaymentsDifference();
        return [
            'payments' => $payments,
            'getDate' => 'getDatePredicted'
        ];
    }



    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $idActivity = $this->params()->fromRoute('idactivity', null);
        $page = $this->params()->fromQuery('page', 1);

        // Appel avec une idActivity => appel ajax depuis la fiche détaillée de
        // l'activité.
        if( $idActivity ) {
            $activity = $this->getProjectGrantService()->getActivityById($idActivity);
            $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_SHOW, $activity);

            $method = $this->getHttpXMethod();
            $this->getLoggerService()->info($method);

            if( $method != "GET" && !$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity) ){
                $this->getResponseBadRequest("Vous ne disposez pas des droits suffisants pour gérer les versements");
            }

            switch($method){
                case 'DELETE':
                    try {
                        /** @var ActivityPayment $payment */
                        $payment = $this->getProjectGrantService()->getActivityPaymentById($this->params()->fromQuery('id'));
                        $this->getNotificationService()->purgeNotificationPayment($payment);
                        $this->getProjectGrantService()->deleteActivityPayment($payment);
                        return $this->getResponseOk("Le versement a bien été supprimé");
                    } catch ( \Exception $e ){
                        $this->getLoggerService()->error($e->getTraceAsString());
                        return $this->getResponseInternalError(sprintf(_("Impossible de supprimer le payment : %s"), $e->getMessage()));
                    }


                case 'PUT':
                    return $this->getResponseDeprecated();


                case 'POST':
                    $action = $this->params()->fromPost('action');

                    $postedDatas = [
                        'amount'            => $this->params()->fromPost('amount'),
                        'activity'          => $activity,
                        'comment'           => $this->params()->fromPost('comment'),
                        'codeTransaction'   => $this->params()->fromPost('codeTransaction'),
                        'currencyId'        => $this->params()->fromPost('currencyId'),
                        'status'            => $this->params()->fromPost('status'),
                        'rate'              => $this->params()->fromPost('rate'),
                        'datePredicted'     => $this->params()->fromPost('datePredicted'),
                        'datePayment'       => $this->params()->fromPost('datePayment'),
                    ];


                    if( $action == 'create' ){
                        try {
                            $this->getProjectGrantService()->addNewActivityPayment($postedDatas);
                            return $this->getResponseOk("Le versement a bien été ajouté");
                        } catch ( \Exception $e ){
                            $this->getLoggerService()->error($e->getTraceAsString());
                            return $this->getResponseInternalError(sprintf(_("Impossible d'ajouter le payment : %s"), $e->getMessage()));
                        }
                    }

                    elseif ($action == 'update' ){
                        try {
                            $postedDatas['id'] = $this->params()->fromPost('id');
                            $this->getProjectGrantService()->updateActivityPayment($postedDatas);
                            return $this->getResponseOk("Le versement a bien été modifié");
                        } catch ( \Exception $e ){
                            return $this->getResponseInternalError(sprintf(_("Impossible de modifier le payment : %s"), $e->getMessage()));
                        }
                    }

                    else {
                        return $this->getResponseBadRequest('Action inconnue');
                    }
            }

            $view = new JsonModel($this->getProjectGrantService()->getListActivityPaymentByActivity($activity));

            return $view;
        }

        // Page "Liste"
        else {
            $search = $this->params()->fromQuery('q', '');
            return $this->getProjectGrantService()->getListActivityPayment($search, $page);
        }

    }
    public function restAction(){
        //var_dump($this->getRequest());
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity);
        if( !$entity ){
            return $this->getResponseNotFound("Activité non trouvée");
        }

        $versement = $this->getEntityManager()->getRepository(ActivityPayment::class)->find($this->params()->fromRoute('id'));
        if( !$entity ){
            return $this->getResponseNotFound("Versement non trouvée");
        }

        switch( $this->getHttpXMethod() ){
            case 'DELETE' :
                return $this->getResponseOk('Le versement %s a bien été supprimé');
                break;
            case 'PUT' :
                var_dump($request->getPost());
//                return $this->getResponseOk('Le versement %s a bien été supprimé');
                break;
        }

        return $this->getResponseBadRequest("test");

        return $response;
    }

    public function indexRestAction(){
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity);

        if( !$entity ){
            return $this->getResponseNotFound("Activité non trouvée");
        }

        // Récupération des payements
        $payments = [];
        /** @var ActivityPayment $payment */
        foreach( $entity->getPayments() as $payment ){
            $payments[] = $payment->json();
        }

        $response->setVariable('method', $request->getMethod())
            ->setVariable('payments_status', ActivityPayment::getStatusPayments())
            ->setVariable('currencies', $this->getProjectGrantService()->getCurrencies(true))
            ->setVariable('payments', $payments);

        return $response;
    }

    public function changeAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var ActivityPayment $entity */
        $entity = $this->getProjectGrantService()->getActivityPayment($this->params()->fromRoute('id'));

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity->getActivity());

        if( $request->getMethod() === "DELETE" ){
            try {
                $entity->getActivity()->touch();
                $this->getProjectGrantService()->deleteActivityPayment($entity);
                $this->getEntityManager()->flush();

                $this->getActivityLogService()->addUserInfo(
                    sprintf(" a supprimé un %s dans l'activité %s", $entity, $entity->getActivity()->log()),
                    'Activity',
                    $entity->getActivity()->getId()
                );
            }
            catch( \Exception $e ){
                $this->getResponse()->setStatusCode(500);
                $response->setVariable('error', 'Impossible de supprimer cette échéance');
            }
            return $response;
        }
        else {
            /** @var ActivityPaymentForm $form */
            $form = new ActivityPaymentForm();
            $form->setAttribute('action', $this->url()->fromRoute(null, ['idactivity' => $entity->getActivity()->getId(), 'id' => $entity->getId()]));
            $form->setProjectGrantService($this->getProjectGrantService());
            $form->init();
            $form->bind($entity);
            if( $request->isPost() ){
                $form->setData($request->getPost());
                if($form->isValid()){
                    $entity->getActivity()->touch();

                    $this->getProjectGrantService()->getEntityManager()->flush();
                    $this->getActivityLogService()->addUserInfo(
                        sprintf(" a modifié le %s dans l'activité %s", $entity, $entity->getActivity()->log()),
                        'Activity',
                        $entity->getActivity()->getId()
                    );
                    $this->redirect()->toRoute('payment');
                }
            }

            $view = new ViewModel([
                'payment' => $entity,
                'activity' => $entity->getActivity(),
                'form' => $form,
            ]);
            if( $request->isXmlHttpRequest() ){
                $view->setTerminal(true);
            }
            $view->setTemplate('oscar/activity-payment/form.phtml');

            return $view;
        }

        die('<div class="alert alert-danger">' . $request->getMethod() . ' not implemented</div>');
    }
}