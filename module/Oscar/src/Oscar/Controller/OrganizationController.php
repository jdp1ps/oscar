<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/06/15 17:24
 *
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\Query;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Oscar\Connector\IConnector;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Form\OrganizationIdentificationForm;
use Oscar\Provider\Privileges;
use Oscar\Service\SessionService;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseProjectService;
use Oscar\Traits\UseProjectServiceTrait;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class OrganizationController extends AbstractOscarController implements UseOrganizationService, UseProjectService,
                                                                        UseProjectGrantService, UseActivityLogService
{
    use UseOrganizationServiceTrait, UseProjectServiceTrait, UseProjectGrantServiceTrait, UseActivityLogServiceTrait;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /** @var SessionService */
    private $sessionService;

    /**
     * @return SessionService
     */
    public function getSessionService(): SessionService
    {
        return $this->sessionService;
    }

    /**
     * @param SessionService $sessionService
     * @return OrganizationController
     */
    public function setSessionService(SessionService $sessionService): self
    {
        $this->sessionService = $sessionService;
        return $this;
    }

    public function deleteAction()
    {
        $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_DELETE);

        $id = $this->params()->fromRoute('id');
        $organization = $this->getOrganizationService()->getOrganization($id);

        if ($this->getRequest()->isPost()) {
            $tokenName = $this->params()->fromPost('tokenname');
            $tokenValue = $this->params()->fromPost('tokenvalue');

            if ($this->getSessionService()->checkToken($tokenName, $tokenValue)) {
                try {
                    $this->getOrganizationService()->deleteOrganization($id);
                    $this->redirect()->toRoute("organization");
                } catch (ForeignKeyConstraintViolationException $e) {
                    throw new OscarException(
                        "Vous devez supprimer cette organisation des activités et supprimer ces membres avant de la supprimer.",
                        0,
                        $e
                    );
                }
            }
        }
        else {
            $token = $this->getSessionService()->createToken();
        }

        return [
            'organization' => $organization,
            'token'        => $token
        ];
    }

    /**
     * Liste des organisations.
     *
     * @return array
     * @throws \Exception
     */
    public function indexAction()
    {
        $format = $this->getRequestFormat();
        $allow = false;
        $justXHR = true;


        // On test les accès
        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ORGANIZATION_SHOW)) {
            $allow = true;
            $justXHR = false;
        }
        else {
            $allow = $this->getOscarUserContextService()
                ->hasOneOfPrivilegesInAnyRoles([
                                                   Privileges::ACTIVITY_ORGANIZATION_MANAGE,
                                                   Privileges::PROJECT_ORGANIZATION_MANAGE,
                                                   Privileges::ACTIVITY_INDEX,
                                               ]);
        }

        if (!$allow) {
            throw new UnAuthorizedException();
        }

        $page = (int)$this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        $type = $this->params()->fromQuery('t', []);
        $active = $this->params()->fromQuery('active', '');
        $sort = $this->params()->fromQuery('sort', 'hit');
        $direction = $this->params()->fromQuery('direction', 'ASC');
        $error = null;
        $organizations = null;

        $sorting = [
            'hit'         => 'Pertinence (recherche textuelle)',
            'shortName'   => 'Nom court',
            'fullName'    => 'Nom long',
            'code'        => 'Code',
            'dateUpdated' => 'Date de mise à jour',
            'dateEnd'     => 'Date de fermeture',
            'dateCreated' => 'Date de création',
        ];

        $directions = [
            'ASC'  => "Croissant",
            'DESC' => "Décroissant"
        ];

        // Controle du trie/direction
        if (!in_array($sort, array_keys($sorting))) {
            $sort = 'hit';
        }

        if (!in_array($direction, array_keys($directions))) {
            $direction = 'ASC';
        }

        $filter = [
            'roles'     => $this->params()->fromQuery('roles', []),
            'type'      => $type,
            'sort'      => $sort,
            'active'    => $active,
            'direction' => $direction,
        ];


        $organizations = [];

        if ($justXHR === false && $this->getRequest()->getQuery('action') == 'export-csv') {
            $query = $this->getOrganizationService()->getSearchQuery($search, $filter);
            $organizations = $query->getQuery()->getResult();
            $this->getOrganizationService()->exportCsv($organizations);
            die();
        }

        try {
            $organizations = $this->getOrganizationService()->getOrganizationsSearchPaged($search, $page, $filter);
        } catch (BadRequest400Exception $e) {
            $error = _("Expression de recherche incorrecte") . ' : ' . $e->getMessage();
        } catch (NoNodesAvailableException $e) {
            $error = "Le moteur de recherche est introuvable";
        } catch (\Exception $exception) {
            $this->getLoggerService()->error($exception->getMessage());
            return $this->jsonError("Quelquechose c'est mal passé...");
        }

        if ($this->getRequest()->isXmlHttpRequest() || $this->params()->fromQuery('f') === 'json') {
            // test : return $this->getResponseUnauthorized("nop");
            $result = ['datas' => []];
            foreach ($organizations as $data) {
                $result['datas'][] = $data->toArray();
            }
            $view = new JsonModel();
            $view->setVariables($result);

            return $view;
        }

        if ($justXHR == true) {
            throw new UnAuthorizedException();
        }

        return array(
            'entities'   => $organizations,
            'error'      => $error,
            'sorting'    => $sorting,
            'sort'       => $sort,
            'direction'  => $direction,
            'directions' => $directions,
            'search'     => $search,
            'types'      => $this->getOrganizationService()->getOrganizationTypesSelect(),
            'type'       => $type,
            'active'     => $active,
        );
    }

    /**
     * Retourne la liste des organisations recherchées.
     */
    public function searchAction()
    {
        if (
            !$this->getOscarUserContextService()->hasPrivilegeDeep(Privileges::PROJECT_ORGANIZATION_MANAGE) &&
            !$this->getOscarUserContextService()->hasPrivilegeDeep(Privileges::ACTIVITY_ORGANIZATION_MANAGE) &&
            !$this->getOscarUserContextService()->hasPrivilegeDeep(Privileges::ACTIVITY_INDEX)
        ) {
            return $this->getResponseUnauthorized(
                "Vous n'avez pas l'authorisation d'accéder à la  liste des organisations"
            );
        }

        $page = (int)$this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 3) {
            return $this->getResponseBadRequest("Not enough chars (3 required)");
        }

        $organizations = $this->getOrganizationService()->getOrganizationsSearchPaged($search, $page);

        $result = ['datas' => []];
        foreach ($organizations as $data) {
            $result['datas'][] = $data->toArray();
        }
        $view = new JsonModel();
        $view->setVariables($result);

        return $view;
    }

    /**
     * Fusion d'au moins 2 organisations en une seule.
     *
     * @return array
     */
    public function mergeAction()
    {
        // IDS des organisations à fusionner
        $ids = explode(',', $this->params()->fromQuery('ids'));

        // Propriétés à traiter
        $properties = [
            'centaureId',
            'shortName',
            'fullName',
            'code',
            'email',
            'url',
            'description',
            'street1',
            'street2',
            'street3',
            'city',
            'zipCode',
            'phone',
            'ldapSupannCodeEntite',
            'country',
            'sifacId',
            'codePays',
            'siret',
            'bp',
            'type',
            'sifacGroup',
            'sifacGroupId',
            'numTVACA',
        ];

        if ($ids && count($ids) > 1) {
            // Get organizations in the picture
            $query = $this->getEntityManager()->createQueryBuilder();
            $query->select('o')
                ->from('Oscar\Entity\Organization', 'o')
                ->add('where', $query->expr()->in('o.id', ':ids'));
            $organizations = $query->getQuery()->execute(['ids' => $ids]);

            /** @var $request Request */
            if (($request = $this->getRequest()) && $request->getMethod() === Request::METHOD_POST) {
                // On cré la nouvelle organisation
                $newOrganization = new Organization();
                $this->getEntityManager()->persist($newOrganization);

                // On place une date de fin aux organisations fusionnées
                /** @var Organization $organization */
                foreach ($organizations as $organization) {
                    $organization->setDateEnd(new \DateTime());
                    /** @var ProjectPartner $projectPartner */
                    foreach ($organization->getProjects() as $projectPartner) {
                        $newPartner = new ProjectPartner();
                        $newPartner->setDateStart(new \DateTime())
                            ->setDateEnd($projectPartner->getDateEnd())
                            ->setMain($projectPartner->isMain())
                            ->setRoleObj($projectPartner->getRoleObj())
                            ->setOrganization($newOrganization)
                            ->setProject($projectPartner->getProject());
                        $this->getEntityManager()->persist($newPartner);
                        $this->getEntityManager()->remove($projectPartner);
                    }
                    /** @var ActivityOrganization $activityPartner */
                    foreach ($organization->getActivities() as $activityPartner) {
                        $newPartner = new ActivityOrganization();
                        $newPartner->setDateStart(new \DateTime())
                            ->setDateEnd($activityPartner->getDateEnd())
                            ->setMain($activityPartner->isMain())
                            ->setMain($activityPartner->isMain())
                            ->setRoleObj($activityPartner->getRoleObj())
                            ->setOrganization($newOrganization)
                            ->setActivity($activityPartner->getActivity());
                        $this->getEntityManager()->persist($newPartner);
                        $this->getEntityManager()->remove($activityPartner);
                    }
                    /** @var OrganizationPerson $organizationPerson */
                    foreach ($organization->getPersons() as $organizationPerson) {
                        $newPartner = new OrganizationPerson();
                        $this->getEntityManager()->persist($newPartner);
                        $newPartner->setRoleObj($organizationPerson->getRoleObj())
                            ->setOrganization($newOrganization)
                            ->setPerson($organizationPerson->getPerson());
                        $this->getEntityManager()->remove($organizationPerson);
                    }
                    $this->getEntityManager()->remove($organization);
                }

                // On push les données reçues
                foreach ($request->getPost()->toArray() as $key => $value) {
                    if (in_array($key, $properties)) {
                        $method = 'set' . ucfirst($key);
                        $newOrganization->$method($value);
                    }
                }

                // @todo On affecte les projets
                $this->getEntityManager()->flush();

                $this->redirect()->toRoute('organization/show', ['id' => $newOrganization->getId()]);
            }

            $propertiesValues = [];

            foreach ($organizations as $organization) {
                foreach ($properties as $property) {
                    $method = 'get' . ucfirst($property);
                    $value = $organization->$method();
                    if (!$value) {
                        continue;
                    }

                    if (!isset($propertiesValues[$property])) {
                        $propertiesValues[$property] = [];
                    }

                    if (!in_array($value, $propertiesValues[$property])) {
                        $propertiesValues[$property][] = $value;
                    }
                }
            }

            return [
                'organizations' => $organizations,
                'datas'         => $propertiesValues,
            ];
        }
    }

    /**
     * Gestion des sous-structures
     * Route : /organization/ID/substructure/new
     * @return void
     */
    public function suborganizationAction()
    {
        try {
            $method = $this->getRequest()->getMethod();
            $organizationId = $this->params()->fromRoute('idorganization', null);

            switch ($method) {
                case 'GET':
                    $output = $this->baseJsonResponse();
                    $subStructures = $this->getOrganizationService()->getSubStructure($organizationId);
                    foreach ($subStructures as &$organization) {
                        $organization['show'] = $this->url()->fromRoute(
                            'organization/show',
                            ['id' => $organization['id']]
                        );
                    }
                    $output['organizations'] = $subStructures;
                    $output['manage'] = $this->getOscarUserContextService()->hasPrivileges(
                        Privileges::ORGANIZATION_EDIT
                    );
                    return $this->jsonOutput($output);
                    break;

                case 'POST':
                    $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT);
                    $idSubStructure = $this->getRequest()->getPost('idSubStructure');
                    if (!$idSubStructure) {
                        return $this->getResponseBadRequest("Vous devez selectionner une organisation");
                    }
                    $this->getOrganizationService()->saveSubStructure($organizationId, $idSubStructure);
                    return $this->getResponseOk("Sous structure enregistrée");
                    break;

                case 'DELETE':
                    $this->getOscarUserContextService()->check(Privileges::ORGANIZATION_EDIT);
                    $idSubStructure = $this->getRequest()->getQuery('idsubstructure');
                    if (!$idSubStructure) {
                        return $this->getResponseBadRequest("Données manquante");
                    }
                    $this->getOrganizationService()->removeSubStructure($organizationId, $idSubStructure);
                    return $this->getResponseOk("Sous structure retirée");
                    break;

                default:
                    return $this->getResponseBadRequest();
            }
        } catch (\Exception $e) {
            return $this->getResponseInternalError("Erreur : " . $e->getMessage());
        }
    }

    public function ficheAction()
    {
        $organizationId = $this->params()->fromRoute('id');

        try {
            $organization = $this->getOrganizationService()->getOrganization($organizationId);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger l'organisation");
        }

        if ($this->isAjax()) {
            $action = $this->getRequest()->getQuery('a', '');
            $response = $this->baseJsonResponse();
            switch ($action) {
                case 'infos':
                    $response['infos'] = $organization->toArray();
                    return $this->jsonOutput($response);
                default:
                    return $this->getResponseBadRequest();
            }
        }

        return [
            'id' => $organizationId
        ];
    }

    public function showAction()
    {
        // TODO
        // Gérer les accès à cette partie pour la gestion des structures
        // de recherche.
        $organizationId = $this->params()->fromRoute('id');
        $page = $this->params()->fromQuery('page', 1);

        $output = [
            'connectors'    => $this->getOrganizationService()->getConnectorsList(),
            'organization'  => $this->getOrganizationService()->getOrganization($organizationId),
            'ancestors'     => $this->getOrganizationService()->getAncestors($organizationId),
            'subStructures' => $this->getOrganizationService()->getSubStructure($organizationId),
            'projects'      => new UnicaenDoctrinePaginator(
                $this->getProjectService()->getProjectOrganization($organizationId), $page
            ),
            'activities'    => $this->getProjectGrantService()->byOrganizationWithoutProject($organizationId, true),
        ];

        return $output;
    }

    public function newAction()
    {
        $form = new OrganizationIdentificationForm(
            $this->getOrganizationService(),
            $this->getOrganizationService()->getOrganizationTypesObject()
        );
        $entity = new Organization();
        $form->init();
        $form->bind($entity);


        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();
            $form->setData($posted);
            if ($form->isValid()) {
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush($entity);
                $this->getOrganizationService()->getSearchEngineStrategy()->add($entity);
                $this->redirect()->toRoute('organization/show', ['id' => $entity->getId()]);
            }
        }

        $view = new ViewModel(array(
                                  'form'       => $form,
                                  'id'         => null,
                                  'types'      => $this->getOrganizationService()->getTypes(),
                                  'connectors' => $this->getOrganizationService()->getConnectorsList(),
                              ));
        $view->setTemplate('oscar/organization/form');

        return $view;
    }

    public function synchronizeConnectorAction()
    {
        $idOrganization = $this->params()->fromRoute('id');
        $connector = $this->params()->fromRoute('connector');

        // Récupération de l'organisation
        /** @var Organization $organization */
        $organization = $this->getOrganizationService()->getOrganization($idOrganization);

        $config = $this->getOscarConfigurationService()->getConfiguration('connectors.organization');
        if (array_key_exists($connector, $config)) {
            /** @var IConnector, IConnectorOrganization $connector */
            $connector = $this->getConnectorService()->getConnector('organization.' . $connector);

            try {
                $organization = $connector->syncOrganization($organization);
                $this->getEntityManager()->flush($organization);

                return $this->redirect()->toRoute(
                    'organization/show',
                    ['id' => $organization->getId()]
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }
        else {
            die('Bad connector ' . $connector);
        }
    }

    public function scissionAction()
    {
        /**
         * @var Request
         */
        $request = $this->getRequest();

        // Étape en cours
        $etape = 1;

        // HASH code pour valider
        $hash = null;

        // IDS des destinations
        $to = [];

        // ID de départ
        $from = null;

        // Date de la fusion
        $at = new \DateTime();

        // Activités à traiter mise à jour
        $activities = [];

        $errors = [];

        $organisationsTo = [];


        if ($request->isPost()) {
            if ($this->params()->fromPost('etape', 1) == 3) {
                die('DO');
                if (isset($_SESSION['fusion_hash']) && $_SESSION['fusion_hash'] == $this->params()->fromPost(
                        'hash',
                        ''
                    )) {
                    if (!isset($_SESSION['fusion_data'])) {
                        $this->flashMessenger()->addErrorMessage("Erreur de transmission des données.");
                        $this->redirect()->toRoute('organization/fusion');
                    }
                    else {
                        $organizationsFrom = $this->getEntityManager()->createQueryBuilder()
                            ->select('o')
                            ->from(Organization::class, 'o')
                            ->where('o.id IN (:ids)')
                            ->setParameter('ids', $_SESSION['fusion_data']['from'])
                            ->getQuery()
                            ->getResult();

                        $to = $this->getEntityManager()->getRepository(Organization::class)->find(
                            $_SESSION['fusion_data']['to']
                        );

                        $date = $_SESSION['fusion_data']['at'];

                        /** @var Organization $organization */
                        foreach ($organizationsFrom as $organization) {
                            echo $organization . "<br>";
                            /** @var ActivityOrganization $activityOrganization */
                            foreach ($organization->getActivities() as $activityOrganization) {
                                $activityOrganization->setDateEnd($date);
                                $activityOrganization->getOrganization()->setDateEnd($date);
                                $newRole = $activityOrganization->fusionTo($to, $date);
                                $this->getEntityManager()->persist($newRole);
                            }
                        }
                        $this->getEntityManager()->flush();
                        $this->flashMessenger()->addSuccessMessage("Fusion des organisations réussie.");
                        $this->redirect()->toRoute('organization/show', ['id' => $to->getId()]);
                    }
                }
                else {
                    $this->flashMessenger()->addErrorMessage("La procédure de fusion a été interrompue.");
                    $this->redirect()->toRoute('organization/fusion');
                    return;
                }
            }
            else {
                $etape = 2;
                $hash = uniqid('fusion_');
                $_SESSION['fusion_hash'] = $hash;
                $fusionDatas = [];

                // tester source vide
                $_SESSION['fusion_dat'] = $hash;
                $fusionDatas['from'] = $from = $this->params()->fromPost('from', null);
                if (!$from) {
                    $errors[] = "Vous devez spécifer l'organisation à scinder.";
                }
                // ...

                // tester destination vide
                $fusionDatas['to'] = $to = $this->params()->fromPost('to', []);
                if (count($to) == 0) {
                    $errors[] = "Vous devez spécifer les organisations cibles.";
                }

                // ...

                // tester date vide
                $fusionDatas['at'] = $at = new \DateTime($this->params()->fromPost('dateFusion', null));

                if ($at == null) {
                    $errors[] = "Vous devez renseigner la date de la fusion";
                }
                // ...

                // Récupération des activités

                $_SESSION['fusion_data'] = $fusionDatas;
                $activities = [];

                $organisationsTo = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder(
                    'a'
                )->where('a.id IN (:ids)')
                    ->setParameter('ids', $to)
                    ->getQuery()
                    ->getResult();

                $organisationFrom = $this->getEntityManager()->getRepository(Organization::class)->find($from);

                /** @var ActivityOrganization $activity */
                foreach ($organisationFrom->getActivities() as $activity) {
                    $activities[$activity->getActivity()->getId()] = $activity;
                }
            }
        }
        return [
            'activities'      => $activities,
            'organizationsTo' => $organisationsTo,
            'from'            => $from,
            'to'              => $to,
            'etape'           => $etape,
            'hash'            => $hash,
        ];
    }

    public function fusionAction()
    {
        /**
         * @var Request
         */
        $request = $this->getRequest();

        // Étape en cours
        $etape = 1;

        // HASH code pour valider la fusion
        $hash = null;

        // IDS des organisation à fusionner
        $from = [];

        // ID de l'organisation de destination
        $to = null;

        // Date de la fusion
        $at = new \DateTime();

        // Activités mise à jour
        $activities = [];

        $errors = [];


        if ($request->isPost()) {
            if ($this->params()->fromPost('etape', 1) == 3) {
                if (isset($_SESSION['fusion_hash']) && $_SESSION['fusion_hash'] == $this->params()->fromPost(
                        'hash',
                        ''
                    )) {
                    if (!isset($_SESSION['fusion_data'])) {
                        $this->flashMessenger()->addErrorMessage("Erreur de transmission des données.");
                        $this->redirect()->toRoute('organization/fusion');
                    }
                    else {
                        $organizationsFrom = $this->getEntityManager()->createQueryBuilder()
                            ->select('o')
                            ->from(Organization::class, 'o')
                            ->where('o.id IN (:ids)')
                            ->setParameter('ids', $_SESSION['fusion_data']['from'])
                            ->getQuery()
                            ->getResult();

                        $to = $this->getEntityManager()->getRepository(Organization::class)->find(
                            $_SESSION['fusion_data']['to']
                        );

                        $date = $_SESSION['fusion_data']['at'];

                        /** @var Organization $organization */
                        foreach ($organizationsFrom as $organization) {
                            echo $organization . "<br>";

                            /** @var ActivityOrganization $activityOrganization */
                            foreach ($organization->getActivities() as $activityOrganization) {
                                $activityOrganization->setDateEnd($date);
                                $activityOrganization->getOrganization()->setDateEnd($date);
                                $newRole = $activityOrganization->fusionTo($to, $date);
                                $this->getEntityManager()->persist($newRole);
                            }

                            /** @var ProjectPartner $projectPartner */
                            foreach ($organization->getProjects() as $projectPartner) {
                                $projectPartner->setDateEnd($date);
                                $projectPartner->getOrganization()->setDateEnd($date);
                                $newPartner = $projectPartner->fusionTo($to, $date);
                                $this->getEntityManager()->persist($newPartner);
                            }
                        }
                        $this->getEntityManager()->flush();
                        $this->flashMessenger()->addSuccessMessage("Fusion des organisations réussie.");
                        $this->redirect()->toRoute('organization/show', ['id' => $to->getId()]);
                    }
                }
                else {
                    $this->flashMessenger()->addErrorMessage("La procédure de fusion a été interrompue.");
                    $this->redirect()->toRoute('organization/fusion');
                    return;
                }
            }
            else {
                $etape = 2;
                $hash = uniqid('fusion_');
                $_SESSION['fusion_hash'] = $hash;
                $fusionDatas = [];

                // tester source vide
                $_SESSION['fusion_dat'] = $hash;
                $fusionDatas['from'] = $from = $this->params()->fromPost('from', []);
                if (count($from) == 0) {
                    $errors[] = "Vous devez spécifer une ou plusieurs organisations à fusionner.";
                }
                // ...

                // tester destination vide
                $fusionDatas['to'] = $to = $this->params()->fromPost('to', null);
                if (count($to) == 0) {
                    $errors[] = "Vous devez spécifer une organisation cible.";
                }

                // ...

                // tester date vide
                $fusionDatas['at'] = $at = new \DateTime($this->params()->fromPost('dateFusion', null));

                if ($at == null) {
                    $errors[] = "Vous devez renseigner la date de la fusion";
                }
                // ...

                // Récupération des activités

                $_SESSION['fusion_data'] = $fusionDatas;
                $activities = [];

                $organisations = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder(
                    'a'
                )->where('a.id IN (:ids)')
                    ->setParameter('ids', $from)
                    ->getQuery()
                    ->getResult();

                /** @var Organization $organisation */
                foreach ($organisations as $organisation) {
                    /** @var ActivityOrganization $activity */
                    foreach ($organisation->getActivities() as $activity) {
                        $idActivity = $activity->getActivity()->getId();
                        $activities[$idActivity] = $activity;
                    }
                    /** @var ProjectPartner $project */
                    foreach ($organisation->getProjects() as $projectpartner) {
                        foreach ($projectpartner->getProject()->getActivities() as $activity) {
                            $idActivity = $activity->getId();
                            $activities[$idActivity] = $activity;
                        }
                    }
                }
            }
        }
        return [
            'activities' => $activities,
            'from'       => $from,
            'to'         => $to,
            'etape'      => $etape,
            'hash'       => $hash,
        ];
    }

    public function closeAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        /** @var Organization $organization */
        $organization = $this->getEntityManager()->getRepository(Organization::class)->find($id);
        if ($organization) {
            $organization->setDateEnd(new \DateTime());
            $this->getEntityManager()->flush($organization);
        }
        $referer = $this->getRequest()->getHeader('referer');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die();
    }

    /**
     * Édition des informations de base.
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $em = $this->getEntityManager();
        $result = $em->createQueryBuilder()
            ->select('p')
            ->from(Organization::class, 'p')
            ->where('p.id = :id')
            ->setParameter('id', $id);

        $entity = null;
        if ($id) {
            /** @var Organization $entity */
            $entity = $result->getQuery()->getSingleResult();
        }

        $form = new OrganizationIdentificationForm(
            $this->getOrganizationService(),
            $this->getOrganizationService()->getOrganizationTypesObject()
        );
        $form->init();
        $form->bind($entity);


        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->flush($entity);
                $this->getActivityLogService()->addUserInfo(
                    sprintf('a modifié les informations pour %s', $entity->log()),
                    $this->getDefaultContext(),
                    $entity->getId(),
                    LogActivity::LEVEL_INCHARGE
                );
                $em->flush($entity);
                $this->getOrganizationService()->getSearchEngineStrategy()->update($entity);
                $this->flashMessenger()->addSuccessMessage(_('Données sauvegardées.'));
                $this->redirect()->toRoute('organization/show', ['id' => $id]);
            }
        } // Affichage
        else {
            $datas = $result->getQuery()->getResult(Query::HYDRATE_ARRAY)[0];
            foreach ($this->getOrganizationService()->getConnectorsList() as $connector) {
                $datas['connector_' . $connector] = $entity->getConnectorID($connector);
            }
            $form->setData($datas);
        }


        $view = new ViewModel(array(
                                  'id'           => $id,
                                  'organization' => $entity,
                                  'types'        => $this->getOrganizationService()->getTypes(),
                                  'form'         => $form,
                                  'connectors'   => $this->getOrganizationService()->getConnectorsList()
                              ));
        $view->setTemplate('oscar/organization/form');

        return $view;
    }
}
