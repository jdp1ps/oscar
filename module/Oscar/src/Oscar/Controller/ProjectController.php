<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 12:03
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Query;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\LogActivity;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Project;
use Oscar\Entity\Activity;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\ProjectRepository;
use Oscar\Form\ProjectForm;
use Oscar\Form\ProjectIdentificationForm;
use Oscar\Provider\Privileges;
use Oscar\Utils\EntityHydrator;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Oscar\Validator\EOTP;
use UnicaenAuth\Entity\Ldap\People;
use Zend\Http\Request;
use Zend\Stdlib\RequestInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcUser\Entity\UserInterface;

class ProjectController extends AbstractOscarController
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project');
    }

    /**
     *
     * @return \Oscar\Service\ProjectService
     */
    protected function getProjectService()
    {
        return $this->getServiceLocator()->get('ProjectService');
    }

    ////////////////////////////////////////////////////////////////////////////
    // ACTIONS                                                                //
    ////////////////////////////////////////////////////////////////////////////

    public function rebuildIndexAction()
    {
        $projects = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->all();
        $serviceSearch = $this->getServiceLocator()->get('Search');
        $serviceSearch->resetIndex();

        foreach ($projects as $project) {
            $serviceSearch->addNewProject($project);
        }

        return 'INDEX REBUILD';
    }

    protected function htmlProjectDetail($project)
    {
        $view = new ViewModel([
            'project' => $project
        ]);
        $view->setTerminal(true);
        $view->setTemplate('/oscar/project/details');

        return $view;
    }

    protected function getRouteProject()
    {
        $id = $this->params()->fromRoute('id', 0);
        return $this->getRepository(Project::class)->find($id);

    }

    public function deleteAction()
    {
        $p = $this->getRouteProject();
        try {
            $this->getEntityManager()->remove($p);
            $this->getEntityManager()->flush();
            //$this->getProjectService()->
        } catch (\Exception $e) {
            die(sprintf("Impossible de supprimer le projet %s : %s", $p, $e->getMessage()));
        }
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé le projet %s", $p->log())
        );
        $this->redirect()->toRoute('project/mine');

    }

    /**
     * Fiche projet.
     *
     * @Route /project/show/:id
     */
    public function showAction()
    {
        // Critères par défaut pour la requète
        $queryOptions = [
            'ignoreDateMember' => true //$this->isAllow('admin'),
        ];

        try {
            $id = $this->params()->fromRoute('id', 0);
            $documents = $this->getEntityManager()->getRepository(ContractDocument::class)
                ->createQueryBuilder('d')
                ->innerJoin('d.grant', 'a')
                ->innerJoin('a.project', 'p', Query\Expr\Join::WITH, 'p.id = :id')
                ->orderBy('d.dateUpdoad', 'DESC')
                ->setParameters(['id' => $id])
                ->getQuery()->getResult();
            $entity = $this->getProjectRepository()->getSingle($id,
                $queryOptions);

            $this->getOscarUserContext()->check(Privileges::PROJECT_SHOW, $entity);

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->htmlProjectDetail($entity);
            }


            return array(
                'access' => $this->getAccessResolverService()->getProjectAccess($entity),
                'project' => $entity,
                'documents' => $documents,
                'logs' => $this->getActivityLogService()->projectActivities($entity->getId())->getQuery()->getResult()
            );
        } catch (UnAuthorizedException $e) {
            throw $e;
        } catch (\Exception $ex) {
            $this->getResponse()->setContent('Projet introuvable');
            $this->getResponse()->setStatusCode(404);
        }
    }

    private $disc = null;

    private function getDisciplineArray()
    {
        if (null === $this->disc) {
            $disciplines = $this->getEntityManager()->getRepository('Oscar\Entity\Discipline')->findAll();
            foreach ($disciplines as $discipline) {
                $this->disc[$discipline->getId()] = $discipline->getLabel();
            }
        }

        return $this->disc;
    }


    public function emptyAction()
    {
        $projects = $this->getProjectService()->getBaseQuery()
            ->where('g.id IS NULL');
        $orderBy = $this->params()->fromQuery('sort', 'p.dateUpdated');
        $projects->orderBy($orderBy, 'DESC');


        return array(
            'projects' => $projects->getQuery()->getResult(),
            'search' => '',
        );
    }

    /**
     * Nouvelle description de Projet.
     *
     * @Route /project/new
     */
    public function newAction()
    {
        $entity = new Project();
        $form = new ProjectIdentificationForm();
        $form->init();
        $form->bind($entity);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($this->getRequest()->isPost()) {

            $form->setData($request->getPost());

            if($form->isValid()) {
                $this->getEntityManager()->persist($entity);

                $entity->setDateUpdated(new \DateTime())
                    ->setDateCreated(new \DateTime());

                $this->getEntityManager()->flush();

                $this->getActivityLogService()->addUserInfo(
                    sprintf('a créé le projet %s', $entity->log()),
                    'Project',
                    $entity->getId(),
                    LogActivity::LEVEL_INCHARGE
                );

                $this->flashMessenger()->addSuccessMessage(sprintf("Le projet '%s' a bien été créé.",
                    $entity->log()));

                return $this->redirect()->toRoute('project/show',
                    array('id' => $entity->getId()));
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form,
        );
    }

    protected function saveIfNeeded(
        Project $entity,
        RequestInterface $request,
        ProjectForm $form
    )
    {
        $form->setData($entity->toArray());
        if ($request->isPost()) {
            $posted = $this->getRequest()->getPost();
            $entity->setLabel($posted['label']);
            $entity->setDescription($posted['description']);
            $entity->setAcronym($posted['acronym']);
            //var_dump($posted);
            $grants = $posted['grants'];
            $grantSources = $this->getEntityManager()->getRepository('Oscar\Entity\GrantSource')->findAll();

            foreach ($grants as $grant) {
                if ($grant['id']) {
                    $g = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant')->find($grant['id']);
                } else {
                    $g = new Activity();
                    $this->getEntityManager()->persist($g);
                    $entity->addGrant($g);
                }
                $g->setAmount($grant['amount'])
                    ->setProject($entity)
                    ->setSource($this->getEntityManager()->getRepository('Oscar\Entity\GrantSource')->find($grant['source']));
            }
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Liste des projets de l'utilisateur courant.
     *
     * @return array
     */
    public function currentUserProjectsAction()
    {
        /** @var UserInterface $currentUser */
        $currentUser = $this->getOscarUserContext()->getDbUser();
        
	/** @var Person|null $currentPerson */
        $currentPerson = $this->getOscarUserContext()->getCurrentPerson();


        if ($currentUser === null) {
            die("Bad move, checkmate !");
        }

        $email = $currentPerson ? $currentPerson->getEmail() : $currentUser->getEmail();

        /** @var $projectRepo ProjectRepository */
        $projectRepo = $this->getEntityManager()->getRepository('Oscar\Entity\Project');
	
	try {
		$projects = $projectRepo->getByUserEmail($email);
	} catch( \Exception $e ) {
		die($e->getMessage());
		$projects = [];
	}

        return [
            'email' => $email,
            'projects' => $projects,
        ];
    }

    public function currentUserStructureProjectsAction()
    {
        /** @var Person|null $currentPerson */
        $currentPerson = $this->getOscarUserContext()->getCurrentPerson();

        $roles = $this->getOscarUserContext()->getRoleIdPrimary();

        $structures = $this->getEntityManager()->getRepository(OrganizationPerson::class)->createQueryBuilder('s')
            ->where('s.person = :person AND s.role IN(:roles)')
            ->setParameters([
                'person'    => $currentPerson,
                'roles'     => $roles,
            ])
            ->getQuery()
            ->getResult()
        ;


        $projects = [];

        /** @var OrganizationPerson $organizationPerson */
        foreach( $structures as $organizationPerson ){
            $orgaId = $organizationPerson->getOrganization()->getId();
            if( !isset($projects[$orgaId]) ){
                $projects[$orgaId] = [
                    'organization' => $organizationPerson->getOrganization(),
                    'projects' => []
                ];
            }
            /** @var ProjectPartner $partner */
            foreach($organizationPerson->getOrganization()->getProjects() as $partner ){
                if( in_array($partner->getRole(), $this->getOscarUserContext()->getRolesOrganisationLeader()))
                    $projects[$orgaId]['projects'][] = $partner->getProject();
            }
            /** @var ActivityOrganization $activityPartner */
            foreach($organizationPerson->getOrganization()->getActivities() as $activityPartner ){
                // Cas des activités sans projet
                if( $activityPartner->getActivity()->getProject() && in_array($activityPartner->getRole(), $this->getOscarUserContext()->getRolesOrganisationLeader()) )
                    $projects[$orgaId]['projects'][] = $activityPartner->getActivity()->getProject();
            }
        }

        return [
            'email' => $currentPerson->getEmail(),
            'projects' => $projects
        ];
    }

    /**
     * @return ViewModel
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $entity = $this->getEntityManager()->getRepository(Project::class)->find($id);

        $this->getOscarUserContext()->check(Privileges::PROJECT_EDIT, $entity);

        $form = new ProjectIdentificationForm();
        $form->init();
        $form->bind($entity);

        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();
            $form->setData($posted);

            if( $form->isValid() ){
                $entity->touch();
                $this->getEntityManager()->flush($entity);
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a mis à jour les informations du projet %s.", $entity->log()),
                    $this->getDefaultContext(),
                    $entity->getId()
                );
            }
        }

        $view = new ViewModel(array(
            'id' => $id,
            'project' => $entity,
            'form' => $form,
        ));

        $view->setTemplate('oscar/project/new');

        return $view;
    }

    /**
     * Affiche l'écran de gestion des membres.
     *
     * @return ViewModel
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function managememberAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $project = $this->getEntityManager()
            ->getRepository('Oscar\Entity\Project')
            ->getSingle($id, ['ignoreDateMember' => true]);

        $view = new ViewModel(array(
            'id' => $id,
            'project' => $project
        ));
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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
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


        $view = new ViewModel([
            'tokenName' => $tokenName,
            'tokenValue' => $tokenValue,
            'urlCancel' => $this->url()->fromRoute('project/show', ['id' => $projectId]),
            'message' => 'Cette opération va supprimer les partenaires des activités déjà présents dans le projet et déplacer dans le projet les partenaires communs à toutes les activités',
        ]);
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


        $view = new ViewModel([
            'tokenName' => $tokenName,
            'tokenValue' => $tokenValue,
            'urlCancel' => $this->url()->fromRoute('project/show', ['id' => $projectId]),
            'message' => 'Cette opération va supprimer les membres des activités déjà présents dans le projet et déplacer dans le projet les membres communs à toutes les activités',
        ]);
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
     */
    public function searchAction()
    {
        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 2) {
            return $this->getResponseBadRequest("Not enough chars (4 required");
        }
        $datas = $this->getProjectService()->search($search)->getQuery()->getResult();

        $json = [
            'datas' => []
        ];

        foreach ($datas as $data) {
            $json['datas'][] = [
                'id' => $data->getId(),
                'label' => $data->getLabel(),
                'acronym' => $data->getAcronym(),
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

        $viewModel = new ViewModel(array(
            'organization' => $organisation[0],
            'projects' => $organisation[0]->getProjects(),
        ));
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

        $person = $qb->getQuery()->setParameter('id',
            $id)->getOneOrNullResult();
        // $projects = $em->getRepository('Oscar\Entity\Project')->allByPerson($id);

        $viewModel = new ViewModel(array(
            'person' => $person,
            'search' => '',
            'projects' => $person->getProjectAffectations(),
        ));
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
            } else {
                $projects = $this->getProjectService()->search($search);
            }
        } else {
            $projects = $this->getProjectService()->getBaseQuery();
        }

        $orderBy = $this->params()->fromQuery('sort', 'p.dateUpdated');

        $sort = 'activityDateCreated';
        $projects->orderBy($orderBy, 'DESC');


        return array(
            'projects' => new UnicaenDoctrinePaginator($projects, $page),
            'search' => $search,
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
        $project = $this->getProjectById($projectId);

        if( !$project ){
            return $this->getResponseInternalError(sprintf("Projet de destination '%s' inconnu.", $projectId));
        }

        $activitiesIds = $this->params()->fromPost('activities_ids', []);
        if( !count($activitiesIds) ){
            return $this->getResponseInternalError("Aucune activité ne correspond.");
        }

        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $this->params()->fromPost('activities_ids', []))
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach( $activities as $activity ){
            if( $activity->getProject() != $project ){
                $activity->touch();
                $activity->setProject($project);
            }
        }

        $project->touch();
        $this->getEntityManager()->flush();
        $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);
    }
}
