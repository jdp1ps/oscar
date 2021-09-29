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

    /**
     * Retourne la liste des rôles éligibles selon l'association $class.
     *
     * @param $class
     * @return array|\Doctrine\ORM\QueryBuilder|null
     */
    protected function getRoles($class)
    {
        $roles = [];

        /** @var RoleRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Role::class);

        switch ($class) {
            case ActivityOrganization::class:
            case ProjectPartner::class:
                $roles = $this->getOscarUserContextService()->getRolesOrganizationInActivity();
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

    private function getRoleObj($class, $id)
    {
        if ($class == ActivityOrganization::class || $class == ProjectPartner::class) {
            return $this->getEntityManager()->getRepository(OrganizationRole::class)->find($id);
        } else {
            return $this->getEntityManager()->getRepository(Role::class)->find($id);
        }
    }

    private function closeEnroll($class)
    {
        $this->getOscarUserContextService()->check(Privileges::PERSON_EDIT);

        /** @var OrganizationPerson $enroll */
        $enroll = $this->getEntityManager()->getRepository($class)->find($this->params()->fromRoute('idenroll'));

        $date = $this->params()->fromPost('at');
        if (!$date || !$enroll) {
            throw new OscarException("Erreur, données manquantes, veuillez reessayer");
        }

        switch ($class) {
            case OrganizationPerson::class :
                $route = 'organization/show';
                $routeOpt = ['id' => $enroll->getOrganization()->getId()];
                break;
            default:
                return $this->getResponseInternalError("Objet $class non pris en charge");
        }

        try {
            $datet = new \DateTime($date);
        } catch (\Exception $e) {
            throw new OscarException("Erreur, données inconhérente");
        }

        try {
            $enroll->setDateEnd($datet);
            $this->getEntityManager()->flush($enroll);

            $this->redirect()->toRoute($route, $routeOpt);
        } catch (\Exception $e) {
            $msg = sprinf("Impossible de mettre le rôle de la person à jour : %s", $e->getMessage());
            $this->getActivityLogService()->addUserInfo($msg, "organizationperson", $enroll->getId());
            throw new OscarException($msg);
        }
    }

    /**
     * Modification générique de l'Enroll.
     *
     * @param $class
     * @return ViewModel
     */
    private function editEnroll($class)
    {
        $this->getLoggerService()->debug("EDITENROLL $class");
        $enroll = $this->getEntityManager()->getRepository($class)->find($this->params()->fromRoute('idenroll'));
        $enrolled = null;

        $labelTpl = "Modification du rôle de <em>%s</em> dans <strong>%s</strong>";
        $label = sprintf($labelTpl, $enroll->getEnrolled(), $enroll->getEnroller());
        $form = new RoleForm(
            $this->getRoles($class), [
                                       'label' => $label,
                                       'url' => ''
                                   ]
        );

        $form->setData(
            [
                'dateStart' => $enroll->getDateStart() ? $enroll->getDateStart()->format('Y-m-d') : '',
                'dateEnd' => $enroll->getDateEnd() ? $enroll->getDateEnd()->format('Y-m-d') : '',
                'enroled' => $enroll->getEnrolled()->getId(),
                'role' => $enroll->getRoleObj() ? $enroll->getRoleObj()->getId() : 0
            ]
        );

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
            if (get_class($enroll->getEnroller()) == Activity::class) {
                $this->getProjectGrantService()->jobSearchUpdate($enroll->getEnroller());
                $this->getNotificationService()->jobUpdateNotificationsActivity($enroll->getEnroller());
            }
            if (get_class($enroll->getEnroller()) == Project::class) {
                foreach ($enroll->getEnroller()->getActivities() as $activity) {
                    $this->getProjectGrantService()->jobSearchUpdate($activity);
                    $this->getNotificationService()->jobUpdateNotificationsActivity($activity);
                }
            }

            $reflect = new \ReflectionClass($enroll);

            $this->getActivityLogService()->addUserInfo(
                sprintf(
                    " a modifié %s, nouveau rôle '%s' dans %s",
                    $enroll->getEnrolled()->log(),
                    $role,
                    $enroll->getEnroller()->log()
                )
                ,
                $reflect->getShortName(),
                $enroll->getEnroller()->getId()
            );

            $activities = [];

            switch ($class) {
                case ProjectPartner::class:
                    $urlEnrollerShow = 'project/show';
                    break;

                case ProjectMember::class :
                    $urlEnrollerShow = 'project/show';
                    $this->getNotificationService()->jobUpdateNotificationsProject($enroll->getProject());
                    break;

                case ActivityPerson::class :
                    $urlEnrollerShow = 'contract/show';
                    $this->getNotificationService()->jobUpdateNotificationsActivity($enroll->getActivity());
                    break;

                case ActivityOrganization::class :
                    $urlEnrollerShow = 'contract/show';
                    break;
            }
            $this->redirect()->toRoute($urlEnrollerShow, ['id' => $enroll->getEnroller()->getId()]);
        }

        $view = new ViewModel(
            array(
                'id' => null,
                'title' => $label,
                'form' => $form,
                'enroller' => $enroll->getEnroller(),
                'enrolled' => $enroll->getEnrolled()
            )
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }

    private function saveEnroll($class, $exist = null)
    {
        $labelTpl = ($exist ? "Modification " : "Ajout") . " d'un <em>%s</em> dans <strong>%s</strong>";

        $enrollerId = $this->params()->fromRoute('idenroller', null);
        $enrolledId = $this->params()->fromQuery('idenroled', null);
        if (!$enrolledId) {
            $enrolledId = $this->params()->fromPost('enrolled', null);
        }
        if (!$enrolledId) {
            $enrolledId = $this->params()->fromPost('enroled', null);
        }
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
                $textWhere = 'le projet ' . $enroller;
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
                $textWhere = 'le projet ' . $enroller;
                $textWhat = "membre";
                $context = 'Project';

                break;

            case ActivityPerson::class :
                if ($exist) {
                    $enroller = $exist->getActivity();
                    $enrolled = $exist->getPerson();
                } else {
                    $enroller = $this->getActivityEntity();
                }
                $urlEnrollerShow = 'contract/show';
                $urlSearch = 'person/search';
                $setEnrolled = 'setPerson';
                $labelEnrolled = 'Personne';
                $setEnroller = 'setActivity';
                $enrolledClass = Person::class;
                $textWhere = 'l\'activité  ' . $enroller;
                $textWhat = "membre";
                $context = 'Activity';
                break;

            case OrganizationPerson::class :

                $urlEnrollerShow = 'organization/show';

                if ($enrolledId) {
                    $enrolled = $this->getEntityManager()->getRepository(Person::class)->find($enrolledId);
                    $urlSearch = 'organization/search';
                    $labelEnrolled = 'Organisation';
                    $textWhere = 'la personne ' . $enrolled;
                } else {
                    $enroller = $this->getEntityManager()->getRepository(Organization::class)->find($enrollerId);
                    $urlSearch = 'person/search';
                    $labelEnrolled = 'Personne';
                    $textWhere = 'l\'organisation  ' . $enroller;
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
                $textWhere = 'l\'activité  ' . $enroller;
                $textWhat = "partenaire";

                break;

            default:
                throw new \Exception('Bad usage');
                break;
        }

        if ($enrolledId) {
            $enrolled = $this->getEntityManager()->getRepository($enrolledClass)->find($enrolledId);
            $label = sprintf($labelTpl, " rôle au $textWhat $enrolled", $textWhere);
        } else {
            $label = sprintf($labelTpl, $textWhat, $textWhere);
        }

        $form = new RoleForm(
            $roles, [
                      'label' => $labelEnrolled,
                      'url' => $this->url()->fromRoute($urlSearch)
                  ]
        );


        $form->setData(
            [
                'dateStart' => '',
                'enroled' => $enrolledId
            ]
        );

        $form->setAttribute('action', $this->getRequest()->getRequestUri());


        if ($this->getRequest()->isPost()) {
            // $enrolledId = intval($this->params()->fromPost('enroled'));
            $enrolled = $this->getEntityManager()->getRepository($enrolledClass)->find($enrolledId);


            if (!$enrolled) {
                $this->flashMessenger()->addErrorMessage("Modification annulée, personne/organisation manquante");
                return $this->getResponseInternalError("Personne introuvable");
            } else {
                $roleIndex = intval($this->params()->fromPost('role'));
                $role = $roles[$roleIndex];
                $roleObj = $this->getRoleObj($class, $roleIndex);

                if (!$roleObj) {
                    return $this->getResponseInternalError("Rôle inconnu");
                }

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


                if (!$exist) {
                    switch ($class) {
                        case ProjectPartner::class :
                            $this->getProjectService()->addProjectOrganisation(
                                $enroller,
                                $enrolled,
                                $roleObj,
                                $dateStart,
                                $dateEnd
                            );
                            $this->redirect()->toRoute('project/show', ['id' => $enroller->getId()]);
                            return;

                        case ProjectMember::class :
                            $this->getPersonService()->personProjectAdd(
                                $enroller,
                                $enrolled,
                                $roleObj,
                                $dateStart,
                                $dateEnd
                            );
                            $this->redirect()->toRoute('project/show', ['id' => $enroller->getId()]);
                            return;
                            break;

                        case ActivityPerson::class :
                            $this->getPersonService()->personActivityAdd(
                                $enroller,
                                $enrolled,
                                $roleObj,
                                $dateStart,
                                $dateEnd
                            );
                            $this->redirect()->toRoute('contract/show', ['id' => $enroller->getId()]);
                            return;
                            break;

                        case OrganizationPerson::class :

                            // PATCH 2021-04-14 : à nettoyer
                            if ($enroller == null) {
                                $enroller = $this->getOrganizationService()->getOrganization(
                                    $this->params()->fromRoute('idenroller')
                                );
                            }
                            $this->getPersonService()->personOrganizationAdd(
                                $enroller,
                                $enrolled,
                                $roleObj,
                                $dateStart,
                                $dateEnd
                            );
                            $this->redirect()->toRoute('organization/show', ['id' => $enroller->getId()]);
                            return;
                            break;

                        default:
                    }

                    $enrole = new $class();
                    $this->getEntityManager()->persist($enrole);
                    $enrole->setRole($role)
                        ->$setEnrolled(
                            $enrolled
                        )
                        ->$setEnroller(
                            $enroller
                        )
                        ->setRoleObj($roleObj)
                        ->setDateStart($dateStart)
                        ->setDateEnd($dateEnd);
                    $enroller->touch();

                    $this->getEntityManager()->flush($enrole);
                } else {
                    $exist->setRoleObj($roleObj)
                        ->setDateStart($dateStart)
                        ->setDateEnd($dateEnd);
                    $this->getEntityManager()->flush();

                    $enrole = $exist;
                }

                $this->getActivityLogService()->addUserInfo(
                    sprintf("a ajouté %s", $enrole->log()),
                    $context,
                    $enroller->getId()
                );

                switch ($class) {
                    case ProjectMember::class :
                        $this->getNotificationService()->jobUpdateNotificationsProject($enroller);
                        $this->getPersonService()->jobSearchUpdate($enrolled);
                        break;

                    case ActivityPerson::class :
                        $this->getNotificationService()->jobUpdateNotificationsActivity($enroller);
                        $this->getPersonService()->jobSearchUpdate($enrolled);
                        break;

                    case OrganizationPerson::class :
                        // PATCH 2021-04-14 : à nettoyer
                        if ($enroller == null) {
                            $enroller = $this->getOrganizationService()->getOrganization(
                                $this->params()->fromRoute('idenroller')
                            );
                        }
                        $this->getPersonService()->personOrganizationAdd(
                            $enroller,
                            $enrolled,
                            $roleObj,
                            $dateStart,
                            $dateEnd
                        );
                        break;

                    default:
                }
            }
            $this->redirect()->toRoute($urlEnrollerShow, ['id' => $enroller->getId()]);
        }

        $view = new ViewModel(
            array(
                'id' => null,
                'title' => $label,
                'form' => $form,
                'labelEnrolled' => $labelEnrolled,
                'enroller' => $enroller,
                'enrolled' => $enrolled
            )
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setTemplate('partials/role-form.phtml');

        return $view;
    }

    private function getEnrollDatas($type)
    {
        $datas = [

        ];
        $datas['idenroll'] = $idEnroll = $this->params()->fromRoute('idenroll');
        $datas['enroll'] = $enroll = $this->getEntityManager()->getRepository($type)->find($idEnroll);

        $postDateStart = $this->params()->fromPost('dateStart', null);
        $dateStart = null;
        if ($postDateStart != null) {
            $dateStart = DateTimeUtils::toDatetime($postDateStart);
        }
        $datas['dateStart'] = $dateStart;

        $postDateEnd = $this->params()->fromPost('dateEnd', null);
        $dateEnd = null;
        if ($postDateEnd != null) {
            $dateEnd = DateTimeUtils::toDatetime($postDateEnd);
        }
        $datas['dateEnd'] = $dateEnd;

        switch ($type) {
            case ProjectPartner::class:
                $datas['enroller'] = $enroll->getProject();
                $datas['enrolled'] = $enroll->getOrganization();
                $datas['role'] = $this->getEntityManager()->getRepository(Role::class)->find(
                    $this->params()->fromPost('role')
                );
                $datas['url_show_enroller'] = 'project/show';
                $datas['url_show_enrolled'] = 'organization/show';
                $datas['context'] = 'Project';
                $datas['privilege'] = Privileges::PROJECT_ORGANIZATION_MANAGE;
                break;

            case ProjectMember::class :
                $datas['enroller'] = $enroll->getProject();
                $datas['enrolled'] = $enroll->getPerson();
                $datas['role'] = $this->getEntityManager()->getRepository(Role::class)->find(
                    $this->params()->fromPost('role')
                );
                $datas['url_show_enroller'] = 'project/show';
                $datas['url_show_enrolled'] = 'person/show';
                $datas['context'] = 'Project';
                $datas['privilege'] = Privileges::PROJECT_PERSON_MANAGE;
                break;

            case ActivityPerson::class :
                $datas['enroller'] = $enroll->getActivity();
                $datas['enrolled'] = $enroll->getPerson();
                $datas['role'] = $enroll->getRoleObj();
                $datas['url_show_enroller'] = 'contract/show';
                $datas['url_show_enrolled'] = 'person/show';
                $datas['context'] = 'Activity';
                $datas['privilege'] = Privileges::ACTIVITY_PERSON_MANAGE;
                break;

            case ActivityOrganization::class :
                $datas['enroller'] = $enroll->getActivity();
                $datas['enrolled'] = $enroll->getOrganization();
                $datas['role'] = $this->params()->fromPost('role', null) ?
                    $this->getEntityManager()->getRepository(OrganizationRole::class)->find(
                        $this->params()->fromPost('role')
                    ) :
                    null;
                $datas['url_show_enroller'] = 'contract/show';
                $datas['url_show_enrolled'] = 'organization/show';
                $datas['context'] = 'Activity';
                $datas['privilege'] = Privileges::ACTIVITY_ORGANIZATION_MANAGE;
                break;

            case OrganizationPerson::class :
                $datas['enroller'] = $enroll->getOrganization();
                $datas['enrolled'] = $enroll->getPerson();
                $datas['role'] = $this->getEntityManager()->getRepository(Role::class)->find(
                    $this->params()->fromPost('role')
                );
                $datas['url_show_enroller'] = 'organization/show';
                $datas['url_show_enrolled'] = 'person/show';
                $datas['context'] = 'Organization';
                $datas['privilege'] = Privileges::ORGANIZATION_EDIT;
                break;

            default:
                throw new \Exception('Bad usage');
                break;
        }

        $this->getOscarUserContextService()->check($datas['privilege'], $datas['enroller']);

        return $datas;
    }

    private function deleteEnroll($type)
    {
        $idEnroll = $this->params()->fromRoute('idenroll');

        if (!$idEnroll) {
            throw new OscarException("IDENROLL manquant");
        }

        $enroll = $this->getEntityManager()->getRepository($type)->find($idEnroll);

        switch ($type) {
            case ProjectPartner::class:
                $project = $enroll->getProject();
                $this->getProjectService()->removeProjectOrganization($enroll);
                return $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);


            case ProjectMember::class :
                $project = $enroll->getProject();
                $this->getPersonService()->personProjectRemove($enroll);
                return $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);
                break;

            case ActivityPerson::class :
                $activity = $enroll->getActivity();
                $this->getPersonService()->personActivityRemove($enroll);
                return $this->redirect()->toRoute('contract/show', ['id' => $activity->getId()]);
                break;

            case ActivityOrganization::class :
                throw new OscarException("Suppression d'une organisation d'une activité");
                $getEnroller = 'getActivity';
                $url = 'contract/show';
                $context = 'Activity';
                break;

            case OrganizationPerson::class :
                $organization = $enroll->getOrganization();
                $this->getPersonService()->personOrganizationRemove($enroll);
                return $this->redirect()->toRoute('organization/show', ['id' => $organization->getId()]);
                break;

            default:
                throw new \Exception('Bad usage');
                break;
        }
    }

    private function updateIndex($context, $enroller)
    {
        $this->getEntityManager()->refresh($enroller);
        if ($context === 'Project') {
            $this->getProjectService()->searchUpdate($enroller);
        } elseif ($context === 'Activity') {
            if ($enroller->getProject()) {
                $this->getProjectService()->searchUpdate($enroller->getProject());
            } else {
                $this->getProjectGrantService()->jobSearchUpdate($enroller);
//                $this->getProjectGrantService()->searchUpdate($enroller);
            }
        } else {
            $this->getLoggerService()->error(
                sprintf("Impossible d'actualiser %s avec le context '%s", $enroller, $context)
            );
        }
    }

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
    // Person <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function personProjectNewAction()
    {
        $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $this->getProjectEntity());

        return $this->saveEnroll(ProjectMember::class);
    }

    public function personProjectDeleteAction()
    {
        try {
            $rolledId = $this->params()->fromRoute('idenroll');
            /** @var ProjectMember $rolled */
            $rolled = $this->getEntityManager()->getRepository(ProjectMember::class)->find($rolledId);
            $project = $rolled->getProject();
            $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $project);
            return $this->deleteEnroll(ProjectMember::class);
        } catch (\Exception $e) {
            return $this->getResponseInternalError(
                "Impossible de supprimer l'affectation de cette personne dans le projet : " . $e->getMessage()
            );
        }
    }

    public function personProjectEditAction()
    {
        /** @var ProjectMember $personProject */
        $personProject = $this->getEntityManager()->getRepository(ProjectMember::class)->find(
            $this->params()->fromRoute('idenroll')
        );

        $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_MANAGE, $personProject->getProject());
        return $this->editEnroll(ProjectMember::class);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Project
    ////////////////////////////////////////////////////////////////////////////
    public function organizationProjectNewAction()
    {
        $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_MANAGE, $this->getProjectEntity());
        return $this->saveEnroll(ProjectPartner::class);
    }

    public function organizationProjectDeleteAction()
    {
        $enroll = $this->getEntityManager()->getRepository(ProjectPartner::class)->find(
            $this->params()->fromRoute('idenroll')
        );
        $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_MANAGE, $enroll->getProject());
        return $this->deleteEnroll(ProjectPartner::class);
    }

    public function organizationProjectEditAction()
    {
        $enroll = $this->getEntityManager()->getRepository(ProjectPartner::class)->find(
            $this->params()->fromRoute('idenroll')
        );
        $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_MANAGE, $enroll->getProject());
        return $this->editEnroll(ProjectPartner::class);
    }


    ////////////////////////////////////////////////////////////////////////////
    // Organization <> Person
    ////////////////////////////////////////////////////////////////////////////
    public function organizationPersonNewAction()
    {
        $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT, $this->getOrganizationEntity());
        return $this->saveEnroll(OrganizationPerson::class);
    }

    public function organizationPersonDeleteAction()
    {
        /** @var OrganizationPerson $organizationPerson */
        $organizationPerson = $this->getEntityManager()->getRepository(OrganizationPerson::class)->find(
            $this->params()->fromRoute('idenroll')
        );

        $this->getOscarUserContextService()->check(
            Privileges::ORGANIZATION_EDIT,
            $organizationPerson->getOrganization()
        );
        return $this->deleteEnroll(OrganizationPerson::class);
    }

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
        if ($postedDate != null && $postedDate != 'null') {
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
            $activityOrganization = $this->getOrganizationService()->getActivityOrganization($this->getRoutedInteger('idenroll'));

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

    public function activityOrganizationEditAction()
    {
        try {
            /** @var ActivityOrganization $activityOrganization */
            $activityOrganization = $this->getOrganizationService()->getActivityOrganization($this->getRoutedInteger('idenroll'));

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

    ////////////////////////////////////////////////////////////////////////////
    // Fin des rôles
    public function organizationPersonCloseAction()
    {
        $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT, $this->getActivityEntity());
        return $this->closeEnroll(OrganizationPerson::class);
    }
}


