<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/11/15 11:17
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Tackable;
use Oscar\Form\RoleForm;
use Lamainas\View\Model\ViewModel;

class ActivityPersonController extends AbstractOscarController
{
    protected function getActivityPerson( $id )
    {
        return $this->getEntityManager()->getRepository(ActivityPerson::class)->find($id);
    }

    public function deleteAction()
    {
        $currentPerson = $this->getCurrentPerson();

        try {
            /** @var ActivityPerson $activityPerson */
            $activityPerson = $this->getActivityPerson($this->params()->fromRoute('idenroled'));
            $activityId = $activityPerson->getActivity()->getId();
            $activityPerson->setDateDeleted(new \DateTime())
                ->setStatus(Tackable::STATUS_DELETE)
                ->setDeletedBy($currentPerson);
            $this->getEntityManager()->flush($activityPerson);
            $this->redirect()->toRoute('contract/show', ['id'=>$activityId]);
            die(sprintf("Suppression de %s dans l'activité %s", $activityPerson->getPerson(), $activityPerson->getActivity()));
        } catch( \Exception $e ){
            die($e->getMessage());
        }
    }

    public function newAction()
    {
        $activityId = $this->params()->fromRoute('id');
        $activity = $this->getProjectGrantService()->getGrant($activityId);
        $form = new RoleForm(ActivityPerson::getRoles(), [
            'label' => 'Personne',
            'url' => $this->url()->fromRoute('person/search')
        ]);

        if( $this->getRequest()->isPost() ){
            $personId = intval($this->params()->fromPost('enroled'));
            $person = $this->getPersonService()->getPerson($personId);

            $roleIndex = intval($this->params()->fromPost('role'));
            $role = ActivityPerson::getRoles()[$roleIndex];

            $dateStart = $this->params()->fromPost('dateStart');
            if( $dateStart ){
                $dateStart = new \DateTime($dateStart);
            } else {
                $dateStart = null;
            }
            $dateEnd = $this->params()->fromPost('dateEnd');
            if( $dateEnd ){
                $dateEnd = new \DateTime($dateEnd);
            } else {
                $dateEnd = null;
            }

            $personActivity = new ActivityPerson();
            $this->getEntityManager()->persist($personActivity);
            $personActivity->setRole($role)
                ->setPerson($person)
                ->setActivity($activity)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);
            $this->getEntityManager()->flush($personActivity);
            $this->redirect()->toRoute('contract/show', ['id'=>$activity->getId()]);
        }

        $view = new ViewModel(array(
            'id' => null,
            'title'   => "Ajout d'un membre dans l'activité de recherche $activity",
            'form' => $form,
        ));

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }
}
