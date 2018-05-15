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
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ActivityPaymentController extends AbstractOscarController
{
    /**
     * Retourne les versements à venir dans les 15 jours à venir.
     */
    public function incomeAction()
    {
        $payments = $this->getActivityService()->getPaymentsIncoming();
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
        $payments = $this->getActivityService()->getPaymentsLate();
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
        $payments = $this->getActivityService()->getPaymentsDifference();
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
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_PAYMENT_SHOW, $activity);

            $method = $this->getHttpXMethod();
            $this->getLogger()->info($method);

            if( $method != "GET" && !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity) ){
                $this->getResponseBadRequest("Vous ne disposez pas des droits suffisants pour gérer les versements");
            }

            switch($method){
                case 'DELETE':
                    /** @var ActivityPayment $payment */
                    $payment = $this->getEntityManager()->getRepository(ActivityPayment::class)->find($this->params()->fromQuery('id'));
                    $this->getNotificationService()->purgeNotificationPayment($payment);
                    $this->getEntityManager()->remove($payment);
                    $this->getEntityManager()->flush();

                    return $this->getResponseOk("Le versement a bien été supprimé");

                case 'PUT':
                    /** @var ActivityPayment $payment */
                    $payment = new ActivityPayment();
                    $this->getEntityManager()->persist($payment);

                    $payment->setAmount($this->params()->fromPost('amount'))
                        ->setComment($this->params()->fromPost('comment'))
                        ->setActivity($activity)
                        ->setCodeTransaction($this->params()->fromPost('codeTransaction'))
                        ->setCurrency($this->getEntityManager()
                            ->getRepository(Currency::class)
                            ->find($this->params()->fromPost('currencyId')));


                    $status = $this->params()->fromPost('status');
                    $rate = $this->params()->fromPost('rate');
                    $datePredicted = $this->params()->fromPost('datePredicted');
                    $datePayment = $this->params()->fromPost('datePayment');

                    if( $datePayment )
                        $payment->setDatePayment(new \DateTime($datePayment));

                    if( $datePredicted )
                        $payment->setDatePredicted(new \DateTime($datePredicted));

                    $payment->setRate($rate)
                        ->setStatus($status);


                    $this->getEntityManager()->flush($payment);
                    $this->getNotificationService()->generatePaymentsNotifications($payment);
                    return $this->getResponseOk("Le versement a bien été ajouté");

                case 'POST':
                    /** @var ActivityPayment $payment */
                    $payment = $this->getEntityManager()->getRepository(ActivityPayment::class)->find($this->params()->fromPost('id'));

                    $payment->setAmount($this->params()->fromPost('amount'))
                        ->setComment($this->params()->fromPost('comment'))
                        ->setActivity($activity)
                        ->setCodeTransaction($this->params()->fromPost('codeTransaction'))
                        ->setCurrency($this->getEntityManager()
                            ->getRepository(Currency::class)
                            ->find($this->params()->fromPost('currencyId')));


                    $status = $this->params()->fromPost('status');
                    $rate = $this->params()->fromPost('rate');
                    $datePredicted = $this->params()->fromPost('datePredicted');
                    $datePayment = $this->params()->fromPost('datePayment');

                    if( $datePayment )
                        $payment->setDatePayment(new \DateTime($datePayment));
                    else
                        $payment->setDatePayment(null);

                    if( $datePredicted )
                        $payment->setDatePredicted(new \DateTime($datePredicted));
                    else
                        $payment->setDatePredicted(null);

                    $payment->setRate($rate)
                        ->setStatus($status);


                    $this->getEntityManager()->flush($payment);

                    $this->getNotificationService()->purgeNotificationPayment($payment);
                    $this->getNotificationService()->generatePaymentsNotifications($payment);

                    return $this->getResponseOk("Le versement a bien été modifié");
            }

            $qb = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
                ->addSelect('c')
                ->innerJoin('p.activity', 'a')
                ->leftJoin('p.currency', 'c')
                ->where('a.id = :idactivity')
                ->orderBy('p.status', 'DESC')
                ->addOrderBy('p.datePayment');
            $entities = $qb->setParameter('idactivity', $idActivity)->getQuery()->getResult(Query::HYDRATE_ARRAY);
            $view = new JsonModel($entities);

            return $this->getResponseInternalError("ERREUR TEST");
            return $view;
        }

        // Page "Liste"
        else {




            $search = $this->params()->fromQuery('q', '');

            $qb = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
                ->addSelect('c, COALESCE(p.datePredicted, p.datePayment) as HIDDEN dateSort')
                ->innerJoin('p.activity', 'a')
                ->innerJoin('p.currency', 'c')
                ->addOrderBy('dateSort', 'DESC');

            if( $search ){
                if( !$this->getActivityService()->specificSearch($search, $qb, 'a') ){
                    $ids = $this->getActivityService()->search($search);
                    $qb->andWhere('a.id in (:ids)')
                        ->setParameter('ids', $ids);
                }
            }

            return [
                'search' => $search,
                'payments' => new UnicaenDoctrinePaginator($qb, $page, 50)
            ];
        }

    }
    public function restAction(){
        //var_dump($this->getRequest());
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity);
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
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity);

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
        $entity = $this->getActivityService()->getActivityPayment($this->params()->fromRoute('id'));

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $entity->getActivity());

        if( $request->getMethod() === "DELETE" ){
            try {
                $entity->getActivity()->touch();
                $this->getActivityService()->deleteActivityPayment($entity);
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
            $form->setServiceLocator($this->getServiceLocator());
            $form->init();
            $form->bind($entity);
            if( $request->isPost() ){
                $form->setData($request->getPost());
                if($form->isValid()){
                    $entity->getActivity()->touch();

                    $this->getEntityManager()->flush();
                    $this->getActivityLogService()->addUserInfo(
                        sprintf(" a modifié le %s dans l'activité %s", $entity, $entity->getActivity()->log()),
                        'Activity',
                        $entity->getActivity()->getId()
                    );
                    die('<div class="alert alert-success">Modification terminée</div>');
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

    public function newAction()
    {

        $form = new ActivityPaymentForm();
        $form->setServiceLocator($this->getServiceLocator());
        $form->init();
        $idActivity = $this->params()->fromRoute('idactivity');
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PAYMENT_MANAGE, $activity);


        $entity = new ActivityPayment();
        $entity->setActivity($activity);
        $form->setObject($entity);
        $form->setData($form->getHydrator()->extract($entity));

        /** @var Request $request */
        $request = $this->getRequest();

        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ){
                $entity->getActivity()->touch();
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
                $form->get('id')->setValue($entity->getId());
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a ajouté un %s sur l'activité %s", $entity, $entity->getActivity()->log()),
                    'Activity',
                    $entity->getActivity()->getId()
                );
                $this->redirect()->toRoute('activitypayment/edit', ['idactivity'=>$activity->getId(), 'id'=>$entity->getId()]);
            }
        }

        $view = new ViewModel([
            'activity' => $activity,
            'form' => $form,
        ]);

        if( $request->isXmlHttpRequest() ){
            $view->setTerminal(true);
        }

        $view->setTemplate('oscar/activity-payment/form.phtml');

        return $view;
    }
}