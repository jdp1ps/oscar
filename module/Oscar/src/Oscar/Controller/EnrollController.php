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
use Oscar\Entity\LogActivity;
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
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseActivityService;
use Oscar\Traits\UseActivityServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseProjectServiceTrait;
use Oscar\Utils\DateTimeUtils;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\View\Model\ViewModel;

/**
 * Ce controlleur centralise la gestion des rôles pour les projets et les
 * activités de recherche.
 *
 * Class EnrollController
 * @package Oscar\Controller
 */
class EnrollController extends AbstractOscarController implements UsePersonService, UseProjectService,
                                                                  UseProjectGrantService, UseNotificationService,
                                                                  UseActivityService, UseOrganizationService
{
    use UsePersonServiceTrait, UseProjectServiceTrait, UseProjectGrantServiceTrait, UseNotificationServiceTrait, UseActivityServiceTrait, UseOrganizationServiceTrait;

    private function getProjectEntity(): Project
    {
        $idProject = $this->params()->fromRoute('idenroller');
        $this->getLoggerService()->debug("Chargement du Projet");
        $project = $this->getProjectService()->getProject($idProject);
        return $project;
    }

    private function getActivityEntity(): Activity
    {
        $id = $this->params()->fromRoute('idenroller');
        $enroller = $this->getProjectGrantService()->getGrant($id);
        return $enroller;
    }

    private function getOrganizationEntity(): Organization
    {
        $id = $this->params()->fromRoute('idenroller');
        $enrolled = $this->getOrganizationService()->getOrganization($id);
        return $enrolled;
    }

    private function getOrganizationRoleEntity(): OrganizationRole
    {
        $id = $this->params()->fromPost('role');
        $role = $this->getProjectGrantService()->getRoleOrganizationById($id);
        return $role;
    }



    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function organizationProjectNewAction()
    {
        $project = $this->getProjectEntity();
        $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_SHOW, $project);

        try {
            $organization = $this->getOrganizationService()->getOrganization($this->getPostedInteger('enroled'));
            $role = $this->getOrganizationService()->getRoleOrganizationById($this->getPostedInteger('role'), true);
            $this->getProjectService()->addProjectOrganisation(
                $project,
                $organization,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("L'organisation a bien été ajouté au projet");
        } catch (\Exception $e) {
            $msg = "Impossible d'ajouter l'organisation au projet";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    public function organizationProjectDeleteAction()
    {
        try {
            /** @var ProjectPartner $enroll */
            $enroll = $this->getEntityManager()->getRepository(ProjectPartner::class)->find(
                $this->params()->fromRoute('idenroll')
            );
            $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_MANAGE, $enroll->getProject());

            $this->getProjectService()->removeProjectOrganization($enroll);
            return $this->getResponseOk("L'organisation a lien été supprimée");
        } catch (\Exception $e) {
            return $this->getResponseInternalError("Impossible de supprimer l'organisation : " . $e->getMessage());
        }
    }

    public function organizationProjectEditAction()
    {
        try {
            $rolledId = $this->params()->fromRoute('idenroll');
            /** @var ProjectPartner $rolled */
            $rolled = $this->getEntityManager()->getRepository(ProjectPartner::class)->find($rolledId);
            $project = $rolled->getProject();
            $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_MANAGE, $project);
            $role = $this->getOrganizationService()->getRoleOrganizationById($this->getPostedInteger('role'), true);
            $this->getProjectService()->editProjectOrganisation(
                $rolled,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("L'organisation a bien été modifée dans le projet");
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de modifier l'affectation de cette orgaisation dans le projet : " . $e->getMessage()
            );
        }
    }


    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Person
    ////////////////////////////////////////////////////////////////////////////
    protected function getOrganizationPersonForm(Organization $enroller)
    {
        $form = new RoleForm(
            $this->getOscarUserContextService()->getAvailabledRolesPersonOrganization(),
            $this->getPersonService(),
            $enroller,
            [
                'label' => 'Personne',
                'url' => $this->url()->fromRoute('person/search')
            ]
        );

        return $form;
    }

    /**
     * Ajout d'un association entre une Person et une Oragnization avec un Role.
     *
     * @return ViewModel
     */
    public function organizationPersonNewAction()
    {
        $organization = $this->getOrganizationEntity();
        $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT, $organization);
        $organizationPerson = new OrganizationPerson();
        $form = $this->getOrganizationPersonForm($organization);
        $form->bind($organizationPerson);

        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();
            $form->setData($posted);
            if ($form->isValid()) {
                try {
                    $this->getPersonService()->personOrganizationAdd(
                        $organization,
                        $organizationPerson->getPerson(),
                        $organizationPerson->getRoleObj(),
                        $organizationPerson->getDateStart(),
                        $organizationPerson->getDateEnd()
                    );
                    $this->redirect()->toRoute('organization/show', ['id' => $organization->getId()]);
                } catch (\Exception $e){
                    $msg = "Impossible d'ajouter la personne dans l'organisation";
                    $this->getLoggerService()->error("$msg : " . $e->getMessage());
                    throw new OscarException($msg);
                }
            }
        }

        $view = new ViewModel(
            array(
                'id' => null,
                'title' => "Nouvelle personne dans $organization",
                'form' => $form,
                'labelEnrolled' => "Personne",
                'enroller' => $organization,
                'enrolled' => null,
                'backlink' => $this->url('organization/show', ['id' => $organization->getId()])
            )
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }

    /**
     * Suppression d'une association entre une Person et une Oragnization.
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function organizationPersonDeleteAction()
    {
        try {
            /** @var OrganizationPerson $organizationPerson */
            $organizationPerson = $this->getEntityManager()->getRepository(OrganizationPerson::class)->find(
                $this->params()->fromRoute('idenroll')
            );

            $organization = $organizationPerson->getOrganization();

            $this->getOscarUserContextService()->check(
                Privileges::ORGANIZATION_EDIT,
                $organization
            );

            $this->getPersonService()->personOrganizationRemove($organizationPerson);
            $this->redirect()->toRoute('organization/show', ['id' => $organization->getId()]);
        } catch (\Exception $e) {
            $msg = "Impossible de supprimer l'affectation de la personne dans l'organisation";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            $this->getResponseInternalError($msg);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Person <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function personProjectNewAction()
    {
        $project = $this->getProjectEntity();
        $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $project);

        try {
            $person = $this->getPersonService()->getPersonById($this->getPostedInteger('enroled'), true);
            $role = $this->getPersonService()->getRolePersonById($this->getPostedInteger('role'), true);
            $this->getPersonService()->personProjectAdd(
                $project,
                $person,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("La personne a bien été ajouté au projet");
        } catch (\Exception $e) {
            $msg = "Impossible d'ajouter la personne au projet";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    public function personProjectDeleteAction()
    {
        try {
            $rolledId = $this->params()->fromRoute('idenroll');
            /** @var ProjectMember $rolled */
            $rolled = $this->getEntityManager()->getRepository(ProjectMember::class)->find($rolledId);
            $project = $rolled->getProject();
            $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $project);
            $this->getPersonService()->personProjectRemove($rolled);
            return $this->getResponseOk("La personne a bien été supprimée du projet");
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de supprimer l'affectation de cette personne dans le projet : " . $e->getMessage()
            );
        }
    }

    public function personProjectEditAction()
    {
        try {
            $rolledId = $this->params()->fromRoute('idenroll');
            /** @var ProjectMember $rolled */
            $rolled = $this->getEntityManager()->getRepository(ProjectMember::class)->find($rolledId);
            $project = $rolled->getProject();
            $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $project);
            $role = $this->getPersonService()->getRolePersonById(
                $this->getPostedInteger('role'),
                true
            );
            $this->getPersonService()->personProjectChangeRole(
                $rolled,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("La personnes a bien été modifée dans le projet");
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de modifier l'affectation de cette personne dans le projet : " . $e->getMessage()
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Person <> Activity
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Ajout d'une nouvelle personne dans une activité.
     *
     * @return \Zend\Http\Response
     * @throws OscarException
     */
    public function personActivityNewAction()
    {
        $activity = $this->getActivityEntity();
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_PERSON_MANAGE, $activity);

        try {
            $datas = $this->getPostedNew();
            $person = $this->getPersonService()->getPersonById($datas['enroled'], true);
            $role = $this->getPersonService()->getRolePersonById($datas['role'], true);
            $this->getPersonService()->personActivityAdd(
                $activity,
                $person,
                $role,
                $datas['dateStart'],
                $datas['dateEnd']
            );
            return $this->getResponseOk("La personne a bien été ajouté");
        } catch (\Exception $e) {
            $msg = "Impossible d'ajouter la personne à l'activité";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    /**
     * Suppression d'une personne dans une activité.
     *
     * @return \Zend\Http\Response
     */
    public function personActivityDeleteAction()
    {
        try {
            /** @var ActivityPerson $personActivity */
            $activityPerson = $this->getPersonService()->getPersonActivityById($this->getRoutedInteger('idenroll'));

            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_PERSON_MANAGE,
                $activityPerson->getActivity()
            );
            $this->getPersonService()->personActivityRemove($activityPerson);
            return $this->getResponseOk("La personnes a bien été supprimée de l'activité");
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de supprimer l'affectation de cette personne dans l'activité : " . $e->getMessage()
            );
        }
    }

    /**
     * Modification d'une personne dans une activité.
     *
     * @return \Zend\Http\Response
     */
    public function personActivityEditAction()
    {
        try {
            /** @var ActivityPerson $personActivity */
            $activityPerson = $this->getPersonService()->getPersonActivityById($this->getRoutedInteger('idenroll'));

            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_PERSON_MANAGE,
                $activityPerson->getActivity()
            );
            $role = $this->getPersonService()->getRolePersonById(
                $this->getPostedInteger('role'),
                true
            );
            $this->getPersonService()->personActivityChangeRole(
                $activityPerson,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("La personnes a bien été modifiée dans l'activité");
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de modifier l'affectation de cette personne dans l'activité : " . $e->getMessage()
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Activity
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \Zend\Http\Response
     */
    public function organizationActivityNewAction()
    {
        $activity = $this->getActivityEntity();
        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_ORGANIZATION_MANAGE, $activity);

        try {
            $organization = $this->getOrganizationService()->getOrganization(
                $this->getPostedInteger('enroled')
            );
            $role = $this->getOrganizationService()->getRoleOrganizationById($this->getPostedInteger('role'));
            $dateStart = $this->getPostedDateTime('dateStart');
            $dateEnd = $this->getPostedDateTime('dateEnd');

            $this->getProjectGrantService()->organizationActivityAdd(
                $organization,
                $activity,
                $role,
                $dateStart,
                $dateEnd
            );
            return $this->getResponseOk("La personne a bien été ajouté");
        } catch (\Exception $e) {
            $msg = "Impossible d'ajouter l'organisation à l'activité";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    /**
     * @return \Zend\Http\Response
     */
    public function activityOrganizationDeleteAction()
    {
        try {
            /** @var ActivityPerson $personActivity */
            $activityOrganization = $this->getOrganizationService()->getActivityOrganization(
                $this->getRoutedInteger('idenroll')
            );

            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_PERSON_MANAGE,
                $activityOrganization->getActivity()
            );
            $this->getProjectGrantService()->activityOrganizationRemove($activityOrganization);
            return $this->getResponseOk("L'organisation a bien été supprimée de l'activité");
        } catch (\Exception $e) {
            $msg = "Impossible de supprimer l'affectation de cette organisation dans l'activité";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    /**
     * @return \Zend\Http\Response
     */
    public function activityOrganizationEditAction()
    {
        try {
            /** @var ActivityOrganization $activityOrganization */
            $activityOrganization = $this->getOrganizationService()->getActivityOrganization(
                $this->getRoutedInteger('idenroll')
            );

            $this->getOscarUserContextService()->check(
                Privileges::ACTIVITY_PERSON_MANAGE,
                $activityOrganization->getActivity()
            );
            $role = $this->getOrganizationService()->getRoleOrganizationById(
                $this->getPostedInteger('role'),
                true
            );
            $this->getProjectGrantService()->organizationActivityEdit(
                $activityOrganization,
                $role,
                $this->getPostedDateTime('dateStart'),
                $this->getPostedDateTime('dateEnd')
            );
            return $this->getResponseOk("L'organisation a bien été modifiée dans l'activité");
        } catch (\Exception $e) {
            $msg = "Impossible de modifier l'affectation de cette organisation dans l'activité";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            return $this->getResponseInternalError($msg);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Récupération des données postées
    ///

    /**
     * Extraction du champ $name des données postées en tant qu'entier.
     *
     * @param string $name
     * @return int
     * @throws OscarException
     */
    protected function getPostedInteger(string $name): int
    {
        $input = $this->params()->fromPost($name, null);
        return $this->getInteger($input);
    }

    /**
     * Extraction du champ $name des données d'URL en tant qu'entier.
     *
     * @param string $name
     * @return int
     * @throws OscarException
     */
    protected function getRoutedInteger(string $name): int
    {
        $input = $this->params()->fromRoute($name, null);
        return $this->getInteger($input);
    }


    /**
     * @param $value
     * @return int
     * @throws OscarException
     */
    protected function getInteger($value): int
    {
        if ($value == null) {
            throw new OscarException("Aucune valeur donnée");
        } else {
            $input = intval($value);
        }
        if (!is_int($input)) {
            throw new OscarException("La valeur '$input' n'est pas un entier.");
        }
        return $value;
    }

    /**
     * Extraction du champ $name comme DateTime (ou NULL).
     *
     * @param string $name
     * @return int
     * @throws OscarException
     */
    protected function getPostedDateTime(string $name): ?\DateTime
    {
        $postedDate = $this->params()->fromPost($name, null);
        $date = null;
        if ($postedDate != null && $postedDate != 'null' && $postedDate != 'undefined') {
            $date = DateTimeUtils::toDatetime($postedDate);
        }
        return $date;
    }

    /**
     * Récupération des données POST pour une création.
     *
     * @return array
     * @throws OscarException
     */
    protected function getPostedNew(): array
    {
        $datas = [
            "role" => $this->getPostedInteger('role'),
            "enroled" => $this->getPostedInteger('enroled'),
            "dateStart" => $this->getPostedDateTime('dateStart'),
            "dateEnd" => $this->getPostedDateTime('dateEnd'),
        ];
        return $datas;
    }

    /**
     * @return ActivityOrganization
     * @throws OscarException
     */
    public function getPostedActivityOrganization()
    {
        return $this->getPostedEnroll(ActivityOrganization::class);
    }

    public function getPostedEnroll($type)
    {
        $idEnroll = $this->params()->fromRoute('idenroll');
        try {
            return $this->getEntityManager()->getRepository($type)->find($idEnroll);
        } catch (\Exception $e) {
            throw new OscarException(
                sprintf(_("Impossible de charger l'affectation [%s]%s : %s", $idEnroll, $type, $e->getMessage()))
            );
        }
    }

    /**
     * @return Role
     * @throws OscarException
     */
    public function getPostedRole()
    {
        $role = $this->params()->fromRoute('role');
        try {
            if (!$role) {
                throw new \Exception("Vous devez choisir un rôle.");
            }
            return $this->getEntityManager()->getRepository(Role::class)->find($role);
        } catch (\Exception $e) {
            throw new OscarException(sprintf(_("Impossible de charger le rôle '%s' : %s.", $role, $e->getMessage())));
        }
    }

    /**
     * @return OrganizationRole
     * @throws OscarException
     */
    public function getPostedOrganizationRole()
    {
        $role = $this->params()->fromPost('role', null);
        try {
            if (!$role) {
                throw new \Exception("Vous devez choisir un rôle.");
            }
            return $this->getEntityManager()->getRepository(OrganizationRole::class)->find($role);
        } catch (\Exception $e) {
            throw new OscarException(sprintf(_("Impossible de charger le rôle (%s).", $e->getMessage())));
        }
    }

    /**
     * @throws OscarException
     */
    public function organizationPersonCloseAction()
    {
        $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT);

        /** @var OrganizationPerson $enroll */
        $enroll = $this->getEntityManager()->getRepository(OrganizationPerson::class)->find($this->params()->fromRoute('idenroll'));

        $organization = $enroll->getOrganization();
        $date = $this->getPostedDateTime('at');

        if (!$date || !$enroll) {
            throw new OscarException("Erreur, données manquantes, veuillez reessayer");
        }

        try {
            $this->getOrganizationService()->closeOrganizationPerson($enroll, $date);
            $this->redirect()->toRoute('organization/show', ['id' => $organization->getId()]);
        } catch (\Exception $e) {
            $msg = "Impossible de cloturer le rôle de la personne dans l'organisation";
            throw new OscarException($e->getMessage());
        }
    }
}


