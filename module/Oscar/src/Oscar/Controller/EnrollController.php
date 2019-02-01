<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17/11/15 14:07
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\RoleOrganization;
use Oscar\Entity\RoleRepository;
use Oscar\Entity\TraitRole;
use Oscar\Exception\OscarException;
use Oscar\Form\RoleForm;
use Oscar\Provider\Privileges;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\View\Model\ViewModel;

/**
 * Ce controlleur centralise la gestion des rôles pour les projets et les
 * activités de recherche.
 *
 * Class EnrollController
 * @package Oscar\Controller
 */
class EnrollController extends AbstractOscarController
{


    /**
     * Retourne la liste des rôles éligibles selon l'association $class.
     *
     * @param $class
     * @return array|\Doctrine\ORM\QueryBuilder|null
     */
    protected function getRoles( $class ){
        $roles = [];

        /** @var RoleRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Role::class);

        switch( $class ){
            case ActivityOrganization::class:
            case ProjectPartner::class:
                $roles = $this->getOscarUserContext()->getRolesOrganizationInActivity();
                break;

            case ActivityPerson::class:
            case ProjectMember::class:
                $roles = $repo->getRolesAtActivityArray();

                break;
            case OrganizationPerson::class:
                $roles = $repo->getRolesAtOrganizationArray();
        }
        return $roles;
    }

    private function getRoleObj( $class, $id )
    {
        if ($class == ActivityOrganization::class || $class == ProjectPartner::class) {
            return $this->getEntityManager()->getRepository(OrganizationRole::class)->find($id);
        }
        else {
            return $this->getEntityManager()->getRepository(Role::class)->find($id);
        }
    }

    private function closeEnroll($class){

        $this->getOscarUserContext()->check(Privileges::PERSON_EDIT);

        /** @var OrganizationPerson $enroll */
        $enroll = $this->getEntityManager()->getRepository($class)->find($this->params()->fromRoute('idenroll'));

        $date = $this->params()->fromPost('at');
        if( !$date || !$enroll ){
            throw new OscarException("Erreur, données manquantes, veuillez reessayer");
        }

        try {
            $datet = new \DateTime($date);
        } catch (\Exception $e ){
            throw new OscarException("Erreur, données inconhérente");
        }

        try {
            $enroll->setDateEnd($datet);
            $this->getEntityManager()->flush($enroll);
        } catch (\Exception $e ){
            $msg = sprinf("Impossible de mettre le rôle de la person à jour : %s", $e->getMessage());
            $this->getActivityLogService()->addUserInfo($msg, "organizationperson", $enroll->getId());
            throw new OscarException($msg);
        }
        var_dump($datet);
        die(" / " . $date);
    }

    /**
     * Modification générique de l'Enroll.
     *
     * @param $class
     * @return ViewModel
     */
    private function editEnroll($class)
    {

        $enroll = $this->getEntityManager()->getRepository($class)->find($this->params()->fromRoute('idenroll'));


        $labelTpl = "Modification du rôle de <em>%s</em> dans <strong>%s</strong>";
        $label = sprintf($labelTpl, $enroll->getEnrolled(), $enroll->getEnroller());
        $form = new RoleForm($this->getRoles($class), [
            'label' => $label,
            'url' => ''
        ]);

        $form->setData([
            'dateStart' => $enroll->getDateStart() ? $enroll->getDateStart()->format('Y-m-d') : '',
            'dateEnd' => $enroll->getDateEnd() ? $enroll->getDateEnd()->format('Y-m-d') : '',
            'enroled' => $enroll->getEnrolled()->getId(),
            'role' => $enroll->getRoleObj() ? $enroll->getRoleObj()->getId() : 0
        ]);

        $form->setAttribute('action', $this->getRequest()->getRequestUri());

        if ($this->getRequest()->isPost()) {

            $roleIndex = intval($this->params()->fromPost('role'));
            $role = $this->getRoleObj($class, $roleIndex);

            $dateStart = $this->params()->fromPost('dateStart');
            if ($dateStart) {
                $dateStart = new \DateTime($dateStart);
            } else {
                $dateStart = null;
            }
            $dateEnd = $this->params()->fromPost('dateEnd');
            if ($dateEnd) {
                $dateEnd = new \DateTime($dateEnd);
            } else {
                $dateEnd = null;
            }

            $enroll->setRole("")
                ->setDateStart($dateStart)
                ->setRoleObj($role)
                ->setDateEnd($dateEnd);

            $enroll->getEnroller()->touch();



            $this->getEntityManager()->flush($enroll);

            // Mise à jour de l'index
            if( get_class($enroll->getEnroller()) == Activity::class ){
                $this->getActivityService()->searchUpdate($enroll->getEnroller());
            }
            if( get_class($enroll->getEnroller()) == Project::class ){
                foreach ($enroll->getEnroller()->getActivities() as $activity) {
                    $this->getActivityService()->searchUpdate($activity);
                }
            }

            $reflect = new \ReflectionClass($enroll);

            $this->getActivityLogService()->addUserInfo(sprintf(
                " a modifié %s, nouveau rôle '%s' dans %s", $enroll->getEnrolled()->log(), $role, $enroll->getEnroller()->log())
                , $reflect->getShortName(), $enroll->getEnroller()->getId());

            $activities = [];

            switch ($class) {
                case ProjectPartner::class:
                    $urlEnrollerShow = 'project/show';
                    break;

                case ProjectMember::class :
                    $urlEnrollerShow = 'project/show';
                    $this->getNotificationService()->generateNotificationsForProject($enroll->getProject(), $enroll->getPerson());
                    break;

                case ActivityPerson::class :
                    $urlEnrollerShow = 'contract/show';
                    $this->getNotificationService()->generateNotificationsForActivity($enroll->getProject(), $enroll->getPerson());
                    break;

                case ActivityOrganization::class :
                    $urlEnrollerShow = 'contract/show';
                    break;
            }
            $this->redirect()->toRoute($urlEnrollerShow, ['id'=> $enroll->getEnroller()->getId()]);
        }

        $view = new ViewModel(array(
            'id' => null,
            'title'   => $label,
            'form' => $form,
            'enroller' => $enroll->getEnroller(),
            'enrolled' => $enroll->getEnrolled()
        ));

        if ($this->getRequest()->isXmlHttpRequest()){
            $view->setTerminal(true);
        }

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }

    private function saveEnroll($class)
    {

        $labelTpl = "Ajout d'un <em>%s</em> dans <strong>%s</strong>";

        $enrollerId = $this->params()->fromRoute('idenroller', null);
        $enrolledId = $this->params()->fromQuery('idenrolled', null);
        //die("$enrolledId // $enrollerId");

        $roles = $this->getRoles($class);

        switch ($class) {
            case ProjectPartner::class:
                $enroller = $this->getProjectEntity();
                $urlEnrollerShow = 'project/show';
                $urlSearch = 'organization/search';
                $setEnrolled = 'setOrganization';
                $labelEnrolled = 'Organisation';
                $setEnroller = 'setProject';
                $enrolledClass = Organization::class;
                $textWhere = 'le projet '.$enroller;
                $textWhat = "partenaire";
                $context = 'Project';


                break;

            case ProjectMember::class :
                $enroller = $this->getProjectEntity();
                $urlEnrollerShow = 'project/show';
                $urlSearch = 'person/search';
                $setEnrolled = 'setPerson';
                $labelEnrolled = 'Personne';
                $setEnroller = 'setProject';
                $enrolledClass = Person::class;
                $textWhere = 'le projet '.$enroller;
                $textWhat = "membre";
                $context = 'Project';

                break;

            case ActivityPerson::class :
                $enroller = $this->getActivityEntity();
                $urlEnrollerShow = 'contract/show';
                $urlSearch = 'person/search';
                $setEnrolled = 'setPerson';
                $labelEnrolled = 'Personne';
                $setEnroller = 'setActivity';
                $enrolledClass = Person::class;
                $textWhere = 'l\'activité  '.$enroller;
                $textWhat = "membre";
                $context = 'Activity';
                break;

            case OrganizationPerson::class :

                $urlEnrollerShow = 'organization/show';

                if( $enrolledId ){
                    $enrolled = $this->getEntityManager()->getRepository(Person::class)->find($enrolledId);
                    $urlSearch = 'organization/search';
                    $labelEnrolled = 'Organisation';
                    $textWhere = 'la personne '.$enrolled;
                } else {
                    $enroller = $this->getEntityManager()->getRepository(Organization::class)->find($enrollerId);
                    $urlSearch = 'person/search';
                    $labelEnrolled = 'Personne';
                    $textWhere = 'l\'organisation  '.$enroller;
                }
                //$enrollerId = $this->params()->fromRoute('idenroller', null);


                $setEnrolled = 'setPerson';
                $setEnroller = 'setOrganization';
                $enrolledClass = Person::class;
                $textWhat = "";
                $context = 'Organisation';
                break;

            case ActivityOrganization::class :
                $enroller = $this->getActivityEntity();
                $urlEnrollerShow = 'contract/show';
                $urlSearch = 'organization/search';
                $context = 'Activity';
                $setEnrolled = 'setOrganization';
                $labelEnrolled = 'Organisation';
                $setEnroller = 'setActivity';
                $enrolledClass = Organization::class;
                $textWhere = 'l\'activité  '.$enroller;
                $textWhat = "partenaire";

                break;

            default:
                throw new \Exception('Bad usage');
                break;
        }

        if( $enrolledId ){
            $enrolled = $this->getEntityManager()->getRepository($enrolledClass)->find($enrolledId);
            $label = sprintf($labelTpl, " rôle au $textWhat $enrolled", $textWhere);
            //die($enrolled);
        } else {
            $label = sprintf($labelTpl, $textWhat, $textWhere);
            $enrolled = null;
        }



        $form = new RoleForm($roles, [
            'label' => $labelEnrolled,
            'url' => $this->url()->fromRoute($urlSearch)
        ]);


        $form->setData([
            'dateStart' => '',
            'enroled' => $enrolledId
        ]);

        $form->setAttribute('action', $this->getRequest()->getRequestUri());


        if ($this->getRequest()->isPost()) {

            $enrolledId = intval($this->params()->fromPost('enroled'));
            $enrolled = $this->getEntityManager()->getRepository($enrolledClass)->find($enrolledId);

            if( !$enrolled ){
                $this->flashMessenger()->addErrorMessage("Modification annulée, personne/organisation manquante");
            }
            else {
                $roleIndex = intval($this->params()->fromPost('role'));
                $role = $roles[$roleIndex];
                $roleObj = $this->getRoleObj($class, $roleIndex);

                $dateStart = $this->params()->fromPost('dateStart');
                if ($dateStart) {
                    $dateStart = new \DateTime($dateStart);
                } else {
                    $dateStart = null;
                }
                $dateEnd = $this->params()->fromPost('dateEnd');
                if ($dateEnd) {
                    $dateEnd = new \DateTime($dateEnd);
                } else {
                    $dateEnd = null;
                }

                switch ($class) {
                    case ProjectMember::class :
                        $this->getPersonService()->personProjectAdd($enroller, $enrolled, $roleObj, $dateStart, $dateEnd);
                        $this->redirect()->toRoute('project/show', ['id' => $enroller->getId()]);
                        return;
                        break;

                    case ActivityPerson::class :
                        $this->getPersonService()->personActivityAdd($enroller, $enrolled, $roleObj, $dateStart, $dateEnd);
                        $this->redirect()->toRoute('contract/show', ['id' => $enroller->getId()]);
                        return;
                        break;

                    case OrganizationPerson::class :
                        $this->getPersonService()->personOrganizationAdd($enroller, $enrolled, $roleObj, $dateStart, $dateEnd);
                        $this->redirect()->toRoute('organization/show', ['id' => $enroller->getId()]);
                        return;
                        break;

                    default:

                }



                $enrole = new $class();
                $this->getEntityManager()->persist($enrole);
                $enrole->setRole($role)
                    ->$setEnrolled($enrolled)
                    ->$setEnroller($enroller)
                    ->setRoleObj($roleObj)
                    ->setDateStart($dateStart)
                    ->setDateEnd($dateEnd);
                $enroller->touch();

                $this->getEntityManager()->flush($enrole);





                $this->updateIndex($context, $enroller);

                $this->getActivityLogService()->addUserInfo(sprintf("a ajouté %s", $enrole->log()), $context, $enroller->getId());


            }
            $this->redirect()->toRoute($urlEnrollerShow, ['id'=> $enroller->getId()]);
        }

        $view = new ViewModel(array(
            'id' => null,
            'title'   => $label,
            'form' => $form,
            'labelEnrolled' => $labelEnrolled,
            'enroller' => $enroller,
            'enrolled' => $enrolled
        ));

        if ($this->getRequest()->isXmlHttpRequest()){
            $view->setTerminal(true);
        }

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }

    private function deleteEnroll($type)
    {
        $idEnroll = $this->params()->fromRoute('idenroll');
        $enroll = $this->getEntityManager()->getRepository($type)->find($idEnroll);

        switch ($type) {

            case ProjectPartner::class:
                $getEnroller = 'getProject';
                $url = 'project/show';
                $context = 'Project';

                break;

            case ProjectMember::class :
                $project = $enroll->getProject();
                $this->getPersonService()->personProjectRemove($enroll);
                $this->updateIndex('Project', $project);
                return $this->redirect()->toRoute( 'project/show', ['id'=>$project->getId()]);
                break;

            case ActivityPerson::class :
                $activity = $enroll->getActivity();
                $this->getPersonService()->personActivityRemove($enroll);
                $this->updateIndex('Activity', $activity);
                return $this->redirect()->toRoute( 'contract/show', ['id'=>$activity->getId()]);
                break;

            case ActivityOrganization::class :
                $getEnroller = 'getActivity';
                $url = 'contract/show';
                $context = 'Activity';
                break;

            case OrganizationPerson::class :
                $organization = $enroll->getOrganization();
                $this->getPersonService()->personOrganizationRemove($enroll);
                return $this->redirect()->toRoute( 'organization/show', ['id'=>$organization->getId()]);
                break;

            default:
                throw new \Exception('Bad usage');
                break;
        }
        try {

            $enroller = $enroll->$getEnroller();
            $this->getEntityManager()->remove($enroll);
            $enroller->touch();

            $this->getActivityLogService()->addUserInfo(sprintf("a supprimé %s ", $enroll->log()), $context, $enroller->getId());

            $this->getEntityManager()->flush();



            if( in_array($context, ['Project', 'Activity'] ) ){
                $this->updateIndex($context, $enroller);
            }

            return $this->redirect()->toRoute($url, ['id'=>$enroller->getId()]);

        } catch (\Exception $e) {
            $this->getResponseInternalError("Impossible de supprimer");
        }
    }

    private function updateIndex($context, $enroller){
        $this->getEntityManager()->refresh($enroller);
        if( $context === 'Project' ){
            $this->getProjectService()->searchUpdate($enroller);
        } elseif($context === 'Activity' ){
            if( $enroller->getProject() ){
                $this->getProjectService()->searchUpdate($enroller->getProject());
            } else {
                $this->getActivityService()->searchUpdate($enroller);
            }
        } else {
            $this->getLogger()->error(sprintf("Impossible d'actualiser %s avec le context '%s", $enroller, $context));
        }
    }

    private function getProjectEntity()
    {
        $idProject = $this->params()->fromRoute('idenroller');
        $project = $this->getProjectService()->getProject($idProject);
        return $project;
    }

    private function getActivityEntity()
    {
        $id = $this->params()->fromRoute('idenroller');
        $enroller = $this->getProjectGrantService()->getGrant($id);
        return $enroller;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Person <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function personProjectNewAction()
    {
        return $this->saveEnroll(ProjectMember::class);
    }

    public function personProjectDeleteAction()
    {
        return $this->deleteEnroll(ProjectMember::class);
    }

    public function personProjectEditAction()
    {
        return $this->editEnroll(ProjectMember::class);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function organizationProjectNewAction()
    {
        return $this->saveEnroll(ProjectPartner::class);
    }

    public function organizationProjectDeleteAction()
    {
        return $this->deleteEnroll(ProjectPartner::class);
    }

    public function organizationProjectEditAction()
    {
        return $this->editEnroll(ProjectPartner::class);
    }


    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Person
    ////////////////////////////////////////////////////////////////////////////
    public function organizationPersonNewAction()
    {
        return $this->saveEnroll(OrganizationPerson::class);
    }

    public function organizationPersonDeleteAction()
    {
        return $this->deleteEnroll(OrganizationPerson::class);
    }


    ////////////////////////////////////////////////////////////////////////////
    // Person <> Activity
    ////////////////////////////////////////////////////////////////////////////
    public function personActivityNewAction()
    {
        return $this->saveEnroll(ActivityPerson::class);
    }

    public function personActivityDeleteAction()
    {
        return $this->deleteEnroll(ActivityPerson::class);
    }

    public function personActivityEditAction()
    {
        return $this->editEnroll(ActivityPerson::class);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Activity
    ////////////////////////////////////////////////////////////////////////////
    public function organizationActivityNewAction()
    {
        return $this->saveEnroll(ActivityOrganization::class);
    }

    public function organizationActivityDeleteAction()
    {
        return $this->deleteEnroll(ActivityOrganization::class);
    }

    public function organizationActivityEditAction()
    {
        return $this->editEnroll(ActivityOrganization::class);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Fin des rôles
    public function organizationPersonCloseAction(){
        return $this->closeEnroll(OrganizationPerson::class);
    }
}


