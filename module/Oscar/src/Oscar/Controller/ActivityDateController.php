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
use Oscar\Form\ActivityDateForm;
use Oscar\Form\ActivityTypeForm;
use Oscar\Provider\Privileges;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ActivityDateController extends AbstractOscarController
{
    public function indexAction()
    {
        $idActivity = $this->params()->fromRoute('idactivity');
        if( $idActivity ) {
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);
            $view = new JsonModel(array_values($this->getActivityService()->getMilestones($idActivity)));
            return $view;
        } else {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('m')
                ->from(ActivityDate::class, 'm')
                ->orderBy('m.dateStart', 'DESC');
            return [
                'milestones' => $qb->getQuery()->getResult()
            ];
        }
    }

    public function changeAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new JsonModel();

        /** @var ActivityDate $activityDate */
        $activityDate = $this->getActivityService()->getActivityDate($this->params()->fromRoute('id'));

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_MILESTONE_MANAGE, $activityDate->getActivity());


        if( $request->getMethod() === "DELETE" ){
            try {
                $activityDate->getActivity()->touch();
                $this->getActivityService()->deleteActivityDate($activityDate);
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a supprimé la date %s dans  l'activité %s", $activityDate, $activityDate->getActivity()->log()),
                    'Activity',
                    $activityDate->getActivity()->getId()
                );
                $this->getEntityManager()->flush();
            }
            catch( \Exception $e ){
                $this->getResponse()->setStatusCode(500);
                $response->setVariable('error', 'Impossible de supprimer cette échéance');
            }
            return $response;
            throw new \Exception('Impossible de supprimer...');
        } else {
            $form = new ActivityDateForm();
            $idActivity = $this->params()->fromRoute('idactivity');
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $form->setServiceLocator($this->getServiceLocator());

            $form->init();
            $form->bind($activityDate);
            $message = false;

            if( $request->isPost() ){
                $form->setData($request->getPost());
                if($form->isValid()){
                    $this->getEntityManager()->flush($activityDate);
                    $message = "Je jalon a bien été enregistré";
                }
            }

            $view = new ViewModel([
                'title' => 'Modification du jalon',
                'message' => $message,
                'activity' => $activity,
                'form' => $form,
            ]);

            if( $request->isXmlHttpRequest() ){
                $view->setTerminal(true);
            }

            $view->setTemplate('oscar/activity-date/form.phtml');

            return $view;
        }



        die('Traitement ' . $request->getMethod());
    }

    public function newAction()
    {
        $form = new ActivityDateForm();
        $idActivity = $this->params()->fromRoute('idactivity');

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
        $activityDate = new ActivityDate();
        $activityDate->setActivity($activity)
            ->setDateStart(new \DateTime())
        ;

        $form->setServiceLocator($this->getServiceLocator());
        $form->setObject($activityDate);
        $form->init();

        /** @var Request $request */
        $request = $this->getRequest();

        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ){
                $this->getEntityManager()->persist($activityDate);
                $activity->touch();
                $this->getEntityManager()->flush();
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a ajouté le jalon %s à l'activité %s", $activityDate, $activity->log()),
                    'Activity',
                    $activityDate->getActivity()->getId()
                );
                die('OK');
            }
        }

        $view = new ViewModel([
            'title' => 'Nouveau jalon',
            'activity' => $activity,
            'form' => $form,
        ]);

        if( $request->isXmlHttpRequest() ){
            $view->setTerminal(true);
        }

        $view->setTemplate('oscar/activity-date/form.phtml');

        return $view;
    }
}