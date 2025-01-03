<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:03
 *
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Laminas\Http\Response;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\LogActivity;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Project;
use Oscar\Entity\Activity;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Form\ProjectIdentificationForm;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Provider\Privileges;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Oscar\Validator\EOTP;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class ProjectController extends AbstractOscarController
{
    /** @var ProjectService */
    private ProjectService $projectService;

    /** @var ProjectGrantService */
    private ProjectGrantService $projectGrantService;

    /**
     * @return ProjectService
     */
    public function getProjectService(): ProjectService
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     */
    public function setProjectService(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
    }

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

    ////////////////////////////////////////////////////////////////////////////
    // ACTIONS                                                                //
    ////////////////////////////////////////////////////////////////////////////

    protected function htmlProjectDetail($project): ViewModel
    {
        $view = new ViewModel(
            [
                'project' => $project
            ]
        );
        $view->setTerminal(true);
        $view->setTemplate('/oscar/project/details');

        return $view;
    }

    /**
     * @throws OscarException
     */
    protected function getRouteProject($throw = false): ?Project
    {
        $id = $this->params()->fromRoute('id', null);
        if (!$id && $throw) {
            throw new OscarException(sprintf("Impossible de charger le projet, paramètre ID manquant."));
        }
        try {
            return $this->getProjectService()->getProject($id);
        } catch (\Exception $e) {
            if ($throw) {
                throw new OscarException(sprintf("Impossible de charger le projet(%s)", $id));
            }
            return null;
        }
    }

    /**
     * @throws OscarException
     */
    public function deleteAction(): void
    {
        $p = $this->getRouteProject();
        $this->getOscarUserContextService()->check(Privileges::PROJECT_EDIT, $p);
        $this->getProjectService()->deleteProject($p);
        $this->redirect()->toRoute('project/mine');
    }

    public function exportManyAction()
    {
        try {
            // Récupération des données
            $ids = $this->params()->fromPost('ids', '');
            $format = $this->params()->fromPost('format', OscarFormatterConst::FORMAT_IO_JSON);

            if ($this->params()->fromQuery('f')) {
                $format = $this->params()->fromQuery('f');
            }

            $allowedFormat = [OscarFormatterConst::FORMAT_IO_CSV, OscarFormatterConst::FORMAT_IO_JSON, OscarFormatterConst::FORMAT_IO_EXCEL];

            if (!in_array($format, $allowedFormat)) {
                return $this->getResponseInternalError(sprintf(_("Format '%s' inconnue"), $format));
            }


            $projectIds = explode(',', $ids);
            if (count($projectIds) == 0) {
                return $this->getResponseInternalError("Aucun projet à exporter");
            }

            // Récupération des projets
            $projects = $this->getProjectService()->getProjectsByIds($projectIds);
            $formatter = $this->getProjectService()->getFormatter($format == OscarFormatterConst::FORMAT_IO_JSON ?: OscarFormatterConst::FORMAT_IO_CSV);

            // Fichier temporaire
            $filename = uniqid('oscar_export_project_') . '.csv';
            $filePath = '/tmp/' . $filename;

            $handler = fopen($filePath, 'w');


            $delimiter = "\t";

            fputcsv($handler, $formatter->headers(), $delimiter);

            foreach ($projects as $p) {
                fputcsv($handler, $formatter->format($p), $delimiter);
            }

            fclose($handler);

            $downloader = new CSVDownloader();
            if( $format == OscarFormatterConst::FORMAT_IO_CSV ){
                $downloader->downloadCSV($filePath);
            } else {
                $downloader->downloadCSVToExcel($filePath);
            }
            unlink($filePath);
            die();
        } catch (\Exception $e) {
            throw new OscarException($e->getMessage());
        }
    }

    public function emptyAction()
    {
        $projects = $this->getProjectService()->getEmptyProject();
        return array(
            'projects' => $projects,
            'search' => '',
        );
    }

    /**
     * Fiche projet.
     *
     * @Route /project/show/:id
     */
    public function showAction()
    {
        try {
            $id = $this->params()->fromRoute('id', 0);
            $entity = $this->getProjectService()->getProject($id, true);
            $this->getOscarUserContextService()->check(Privileges::PROJECT_SHOW, $entity);

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->htmlProjectDetail($entity);
            }

            $documents = [];
            $documentsActivities = $this->getProjectService()->getProjectDocumentsVersionned($entity);
            foreach ($documentsActivities as $document) {
                if ($this->getOscarUserContextService()->contractDocumentRead($document)) {
                    $documents[] = $document;
                }
            }

            $rolesOrganizations = $this->getOscarUserContextService()->getRolesOrganizationInActivity();
            $rolesPersons = $this->getOscarUserContextService()->getAllRoleIdPersonInActivity();

            // Calcule de l'accès aux dépenses
            $nbrSpent = 0;
            $nbrSpentAllow = 0;
            $spentActivitiesIds = [];
            foreach ($entity->getActivities() as $activity) {
                if ($activity->getCodeEOTP()) {
                    $nbrSpent++;
                    if ($this->getOscarUserContextService()->hasPrivileges(Privileges::DEPENSE_SHOW, $activity)) {
                        $nbrSpentAllow++;
                        $spentActivitiesIds[] = $activity->getId();
                    }
                }
            }

            return array(
                'spentActivitiesIds' => $spentActivitiesIds,
                'spentMissingAcces'  => $nbrSpentAllow < $nbrSpent,
                'project'            => $entity,
                'documents'          => $documents,
                'rolesOrganizations' => $rolesOrganizations,
                'rolesPersons'       => $rolesPersons,
                'logs'               => $this->getActivityLogService()->projectActivities($entity->getId())->getQuery(
                )->getResult()
            );
        } catch (UnAuthorizedException $e) {
            throw $e;
        } catch (\Exception $ex) {
            $this->getLoggerService()->critical($ex->getMessage());
            $this->getResponse()->setContent('Projet introuvable');
            $this->getResponse()->setStatusCode(404);
        }
    }

    /**
     * Nouvelle description de Projet.
     *
     * @Route /project/new
     */
    public function newAction()
    {
        // Récupération de/des activités à associer
        $activitiesIds = $this->params()->fromQuery('ids', null);
        $activities = [];

        if ($activitiesIds) {
            // Récupération des activités et évaluation des droits d'accès
            $ids = explode(',', $activitiesIds);

            $activities = $this->getProjectGrantService()->getActivitiesByIds($ids);

            if (count($ids) != count($activities)) {
                return $this->getResponseInternalError("Une ou plusieurs activités sont manquantes");
            }

            foreach ($activities as $activity) {
                if (!$this->getOscarUserContextService()->hasPrivileges(
                    Privileges::ACTIVITY_CHANGE_PROJECT,
                    $activity
                )) {
                    throw new UnAuthorizedException(
                        _("Vous n'avez les les droits suffisant pour modifier le projet de l'activité '%s'", $activity)
                    );
                }
                $activities[] = $activity;
            }
        }

        $entity = new Project();
        $form = new ProjectIdentificationForm();
        $form->init();
        $form->bind($entity);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $organizations = [];
        $organizationRoles = [];
        $organizationRolesError = null;


        // ACCÈS GÉNÉRALE
        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::PROJECT_CREATE)) {
            // VIA ORGANISATION
            if (!$this->getOscarUserContextService()->hasPrivilegeInOrganizations(Privileges::PROJECT_CREATE)) {
                throw new UnAuthorizedException(_("Vous n'avez pas les droits pour créer des nouveau projets"));
            }

            // Liste des organisations de l'utilisateur
            $organizations = $this->getOscarUserContextService()->getCurrentUserOrganisationWithPrivilege(
                Privileges::PROJECT_CREATE
            );

            foreach (
                $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy(
                    ['principal' => true]
                ) as $role
            ) {
                if ($role->isPrincipal()) {
                    $organizationRoles[$role->getId()] = $role;
                }
            }
        }


        /** @var Request $request */
        $request = $this->getRequest();
        if ($this->getRequest()->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $assoDatas = [];

                if ($organizations) {
                    $organizationRolesError = "Vous devez selectionner un rôle.";

                    foreach ($this->params()->fromPost('organizationsRoles') as $idOrganization => $idRole) {
                        if ($idRole) {
                            $assoDatas[] = [
                                'organization' => $organizations[$idOrganization],
                                'role'         => $organizationRoles[$idRole],
                            ];
                        }
                    }

                    if (count($assoDatas)) {
                        $organizationRolesError = "";
                    }
                }

                if (!$organizationRolesError) {
                    $em = $this->getProjectService()->getEntityManager();
                    $em->persist($entity);

                    $entity->setDateUpdated(new \DateTime())
                        ->setDateCreated(new \DateTime());

                    $em->flush();

                    foreach ($assoDatas as $ass) {
                        $projectOrg = new ProjectPartner();
                        $em->persist($projectOrg);

                        $projectOrg->setRoleObj($ass['role'])
                            ->setOrganization($ass['organization'])
                            ->setProject($entity);

                        $em->flush($projectOrg);
                    }

                    /** @var Activity $activity */
                    foreach ($activities as $activity) {
                        $activity->setProject($entity);
                    }

                    $this->getEntityManager()->flush($activities);

                    $this->getActivityLogService()->addUserInfo(
                        sprintf('a créé le projet %s', $entity->log()),
                        'Project',
                        $entity->getId(),
                        LogActivity::LEVEL_INCHARGE
                    );

                    $this->flashMessenger()->addSuccessMessage(
                        sprintf(
                            "Le projet '%s' a bien été créé.",
                            $entity->log()
                        )
                    );

                    return $this->redirect()->toRoute(
                        'project/show',
                        array('id' => $entity->getId())
                    );
                }
            }
        }

        return array(
            'organizations'          => $organizations,
            'organizationRoles'      => $organizationRoles,
            'entity'                 => $entity,
            'form'                   => $form,
            'organizationRolesError' => $organizationRolesError,
        );
    }


    /**
     * Liste des projets de l'utilisateur courant.
     *
     * @return array
     * @throws OscarException
     */
    public function currentUserProjectsAction(): array
    {
        return [
            'person' => $this->getCurrentPerson(),
            'q'      => $this->params()->fromQuery('q', '')
        ];
    }

    public function organizationsAction(): JsonModel|Response
    {
        try {
            // Récupération de l'activité
            $project = $this->getProjectService()->getProject($this->params()->fromRoute('idproject'), true);

            // Accès
            $this->getOscarUserContextService()->check(Privileges::PROJECT_ORGANIZATION_SHOW, $project);
            $out = $this->baseJsonResponse();
            $this->getProjectService()->getOrganizationsProjectsAPI($project, $out, $this->url());
            $out['manage'] = $this->getOscarUserContextService()->hasPrivileges(Privileges::PROJECT_ORGANIZATION_MANAGE, $project);
            return $this->ajaxResponse($out);
        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }
    }

    public function personsAction(): JsonModel|Response
    {
        try {
            // Récupération de l'activité
            $project = $this->getProjectService()->getProject($this->params()->fromRoute('idproject'), true);

            // Accès
            $this->getOscarUserContextService()->check(Privileges::PROJECT_PERSON_SHOW, $project);

            $out = $this->baseJsonResponse();
            $this->getProjectService()->getPersonsProjectsAPI($project, $out, $this->url());
            $out['manage'] = $this->getOscarUserContextService()->hasPrivileges(Privileges::PROJECT_PERSON_MANAGE, $project);
            return $this->ajaxResponse($out);
        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }
    }

    public function currentUserStructureProjectsAction(): array
    {
        die("ICI");
        /** @var Person|null $currentPerson */
        $currentPerson = $this->getOscarUserContextService()->getCurrentPerson();

        $roles = $this->getOscarUserContextService()->getRoleIdPrimary();

        $structures = $this->getEntityManager()->getRepository(OrganizationPerson::class)->createQueryBuilder('s')
            ->where('s.person = :person AND s.role IN(:roles)')
            ->setParameters(
                [
                    'person' => $currentPerson,
                    'roles'  => $roles,
                ]
            )
            ->getQuery()
            ->getResult();


        $projects = [];

        /** @var OrganizationPerson $organizationPerson */
        foreach ($structures as $organizationPerson) {
            $orgaId = $organizationPerson->getOrganization()->getId();
            if (!isset($projects[$orgaId])) {
                $projects[$orgaId] = [
                    'organization' => $organizationPerson->getOrganization(),
                    'projects'     => []
                ];
            }
            /** @var ProjectPartner $partner */
            foreach ($organizationPerson->getOrganization()->getProjects() as $partner) {
                if (in_array($partner->getRole(), $this->getOscarUserContextService()->getRolesOrganisationLeader())) {
                    $projects[$orgaId]['projects'][] = $partner->getProject();
                }
            }
            /** @var ActivityOrganization $activityPartner */
            foreach ($organizationPerson->getOrganization()->getActivities() as $activityPartner) {
                // Cas des activités sans projet
                if ($activityPartner->getActivity()->getProject() && in_array(
                        $activityPartner->getRole(),
                        $this->getOscarUserContextService()->getRolesOrganisationLeader()
                    )) {
                    $projects[$orgaId]['projects'][] = $activityPartner->getActivity()->getProject();
                }
            }
        }

        return [
            'email'    => $currentPerson->getEmail(),
            'projects' => $projects
        ];
    }

    /**
     * @return ViewModel
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $entity = $this->getProjectService()->getProject($id, true);

        $this->getOscarUserContextService()->check(Privileges::PROJECT_EDIT, $entity);

        $form = new ProjectIdentificationForm();
        $form->init();
        $form->bind($entity);

        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();
            $form->setData($posted);

            if ($form->isValid()) {
                $entity->touch();
                $this->getProjectService()->getEntityManager()->flush($entity);
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a mis à jour les informations du projet %s.", $entity->log()),
                    $this->getDefaultContext(),
                    $entity->getId()
                );
            }
        }

        $view = new ViewModel(
            array(
                'id'      => $id,
                'project' => $entity,
                'form'    => $form,
            )
        );

        $view->setTemplate('oscar/project/new');

        return $view;
    }

    /**
     * Affiche l'écran de gestion des membres.
     *
     * @return ViewModel
     *
     * @throws NoResultException
     * @throws NonUniqueResultException|NotSupported
     */
    public function managememberAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $project = $this->getEntityManager()
            ->getRepository('Oscar\Entity\Project')
            ->getSingle($id, ['ignoreDateMember' => true]);

        $view = new ViewModel(
            array(
                'id'      => $id,
                'project' => $project
            )
        );
        $view->setTemplate('oscar/project/managemembers');

        return $view;
    }

    public function managePartnersAction()
    {
        $projectId = $this->params()->fromRoute('id', 0);

        $projectRepository = $this->getProjectRepository();
        /** @var \Oscar\Entity\Project $project */
        $project = $projectRepository->getById($projectId);

        return [
            'project' => $project
        ];
    }

    /** Retourne un projet.
     * @param $id
     *
     * @return Project
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function getProjectById($id)
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->getSingle($id);
    }

    /**
     * Lance la simplification des partenaires (organisation) pour un projet.
     */
    public function simplifyPartnersAction()
    {
        $projectId = $this->params()->fromRoute('id', 0);
        $tokenName = md5($projectId);


        if ($this->getRequest()->isPost()) {
            try {
                $this->getProjectService()->simplifyPartners($projectId);
                $this->redirect()->toRoute('project/show', ['id' => $projectId]);
            } catch (\Exception $e) {
//            $this->set
            }
        }

        $tokenValue = crypt(date('H:i:s'), uniqid('simplify-partner'));
        $_SESSION['token_' . $tokenName] = $tokenValue;


        $view = new ViewModel(
            [
                'tokenName'  => $tokenName,
                'tokenValue' => $tokenValue,
                'urlCancel'  => $this->url()->fromRoute('project/show', ['id' => $projectId]),
                'message'    => 'Cette opération va supprimer les partenaires des activités déjà présents dans le projet et déplacer dans le projet les partenaires communs à toutes les activités',
            ]
        );
        $view->setTemplate('/oscar/prototype/confirm.phtml');
        return $view;
    }

    /**
     * Lance la simplification des membres (Person) pour un projet.
     */
    public function simplifyMembersAction()
    {
        $projectId = $this->params()->fromRoute('id', 0);
        $tokenName = md5($projectId);


        if ($this->getRequest()->isPost()) {
            try {
                $this->getProjectService()->simplifyMember($projectId);
                $this->redirect()->toRoute('project/show', ['id' => $projectId]);
            } catch (\Exception $e) {
//            $this->set
            }
        }

        $tokenValue = crypt(date('H:i:s'), uniqid('simplify-member'));
        $_SESSION['token_' . $tokenName] = $tokenValue;


        $view = new ViewModel(
            [
                'tokenName'  => $tokenName,
                'tokenValue' => $tokenValue,
                'urlCancel'  => $this->url()->fromRoute('project/show', ['id' => $projectId]),
                'message'    => 'Cette opération va supprimer les membres des activités déjà présents dans le projet et déplacer dans le projet les membres communs à toutes les activités',
            ]
        );
        $view->setTemplate('/oscar/prototype/confirm.phtml');
        return $view;
    }


    public function fusionAction()
    {
        $main = $this->getProjectById($this->params()->fromRoute('mainId'));
        $fusionned = $this->getProjectById($this->params()->fromRoute('fusionnedId'));
        $this->getProjectService()->fusion($main, $fusionned);
        $this->redirect()->toRoute('project/show', ['id' => $main->getId()]);
    }


    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQuery()
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('p, pg, d, t, m, mp')
            ->from('Oscar\Entity\Project', 'p')
            ->leftJoin('p.grants', 'pg')
            ->leftJoin('pg.type', 't')
            ->leftJoin('p.members', 'm')
            ->leftJoin('m.person', 'mp')
            ->leftJoin('p.discipline', 'd');

        return $query;
    }

    /**
     * Lance la recherche sur la requête envoyée.
     *
     * @return array
     * @throws OscarException
     */
    public function searchAction()
    {
        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_CHANGE_PROJECT)) {
            if (!$this->getOscarUserContextService()->hasPrivilegeInOrganizations(
                Privileges::ACTIVITY_CHANGE_PROJECT
            )) {
                return $this->getResponseUnauthorized("Vous n'avez pas accès à la liste des projets.");
            }
        }

        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 2) {
            return $this->getResponseBadRequest("Not enough chars (2 required");
        }
        $datas = $this->getProjectService()->search($search)->getQuery()->getResult();

        $json = [
            'datas' => []
        ];

        /** @var Project $data */
        foreach ($datas as $data) {
            $json['datas'][] = [
                'id'          => $data->getId(),
                'label'       => $data->getLabel() . " - " . count($data->getActivities()) . " activité(s)",
                'acronym'     => $data->getAcronym(),
                'description' => $data->getDescription(),
            ];
        }

        $view = new JsonModel();

        $view->setVariables($json);

        return $view;
    }

    public function byOrganizationAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o, p, m, pj, ps, pr, gr, og, ty, ds')
            ->from('Oscar\Entity\Organization', 'o')
            ->leftJoin('o.projects', 'p')
            ->leftJoin('p.project', 'pj')
            ->leftJoin('pj.discipline', 'ds')
            ->leftJoin('pj.grants', 'gr')
            ->leftJoin('gr.type', 'ty')
            ->leftJoin('pj.partners', 'pr')
            ->leftJoin('pr.organization', 'og')
            ->leftJoin('pj.members', 'm')
            ->leftJoin('m.person', 'ps')
            ->orderBy('pj.dateCreated', 'DESC')
            ->where('o.id = :id');

        $organisation = $qb->getQuery()->execute(array('id' => $id));

        $viewModel = new ViewModel(
            array(
                'organization' => $organisation[0],
                'projects'     => $organisation[0]->getProjects(),
            )
        );
        $viewModel->setTemplate('oscar/project/search');

        return $viewModel;
    }

    /**
     * Affiche les projets pour une personne.
     *
     * @return ViewModel
     */
    public function byPersonAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('pj, p, pr')
            ->from('Oscar\Entity\Person', 'p')
            ->leftJoin('p.projectAffectations', 'pr')
            ->leftJoin('pr.project', 'pj')
            ->groupBy('pj')
            ->orderBy('pj.dateCreated', 'DESC')
            ->where('p.id = :id');

        $person = $qb->getQuery()->setParameter(
            'id',
            $id
        )->getOneOrNullResult();
        // $projects = $em->getRepository('Oscar\Entity\Project')->allByPerson($id);

        $viewModel = new ViewModel(
            array(
                'person'   => $person,
                'search'   => '',
                'projects' => $person->getProjectAffectations(),
            )
        );
        $viewModel->setTemplate('oscar/project/search');

        return $viewModel;
    }

    /**
     * Liste des organisations.
     *
     * @return array
     */
    public function indexAction()
    {
        $page = (int)$this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');

        if ($search) {
            // Recherche EOTP strict
            if (preg_match(EOTP::REGEX_EOTP, $search)) {
                $projects = $this->getProjectService()->getProjectByEOTP($search);
            }
            else {
                $projects = $this->getProjectService()->search($search);
            }
        }
        else {
            $projects = $this->getProjectService()->getBaseQuery();
        }

        $orderBy = $this->params()->fromQuery('sort', 'p.dateUpdated');

        $sort = 'activityDateCreated';
        $projects->orderBy($orderBy, 'DESC');

        return array(
            'projects' => new UnicaenDoctrinePaginator($projects, $page),
            'search'   => $search,
        );
    }

    /**
     * Utilisé pour déplacer une ou plusieurs activités dans un projet.
     *
     * @Route /deplacer-activites/:id
     */
    public function addActivitiesAction()
    {
        $projectId = $this->params()->fromRoute('id');
        try {
            $project = $this->getProjectService()->getProject($projectId, true);
        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }

        $activitiesIds = $this->params()->fromPost('activities_ids', []);
        if (!count($activitiesIds)) {
            return $this->getResponseInternalError("Aucune activité ne correspond.");
        }

        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $this->params()->fromPost('activities_ids', []))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            if ($activity->getProject() != $project) {
                $activity->touch();
                $activity->setProject($project);
            }
        }

        $project->touch();
        $this->getEntityManager()->flush();
        foreach ($activities as $activity) {
            $this->getProjectGrantService()->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity(
                $activity
            );
            $this->getProjectGrantService()->getGearmanJobLauncherService()->triggerUpdateNotificationActivity(
                $activity
            );
        }
        $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);
    }
}
