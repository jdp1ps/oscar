<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/06/15 17:24
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Doctrine\ORM\Query;
use Oscar\Connector\IConnector;
use Oscar\Connector\IConnectorOrganization;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Form\OrganizationIdentificationForm;
use Oscar\Service\OrganizationService;
use Oscar\Utils\EntityHydrator;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OrganizationController extends AbstractOscarController
{
    public function syncLdapAction()
    {
        return $this->getOrganizationService()->syncLdap($this->plugin('url'));
    }
    /**
     * @return OrganizationService
     */
    protected function getOrganizationService()
    {
        return $this->getServiceLocator()->get('OrganizationService');
    }

    /**
     * Liste des organisations.
     *
     * @return array
     */
    public function indexAction()
    {
        $page = (int) $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        $type = $this->params()->fromQuery('t', []);
        $active = $this->params()->fromQuery('active', '');

        $filter = [
            'roles' => $this->params()->fromQuery('roles', []),
            'type' => $type,
            'active' => $active,
        ];

        $organizations = $this->getOrganizationService()->getOrganizationsSearchPaged($search, $page, $filter);


        if ($this->getRequest()->isXmlHttpRequest()) {
            $result = [ 'datas' => []];
            foreach ($organizations as $data) {
                $result['datas'][] = $data->toArray();
            }
            $view = new JsonModel();
            $view->setVariables($result);

            return $view;
        }

        return array(
            'entities' => $organizations,
            'search' => $search,
            'type' => $type,
            'active' => $active,
        );
    }

    /**
     * Liste des organisations.
     *
     * @return array
     */
    public function exportCsvAction()
    {
        $type = $this->params()->fromQuery('t', []);

        $filter = [
            'roles' => $this->params()->fromQuery('roles', []),
            'type' => $type
        ];

        $organizations = $this->getOrganizationService()->getOrganizations();

        // Fichier temporaire
        $filename = uniqid('oscar_export_organization_') . '.csv';
        $handler = fopen('/tmp/' . $filename, 'w');

        $headers = ['ID','NomCourt','NomLong','Code','Email','URL','rue1','rue2','rue3','CP','BP','ville','Pays','CodePays','Téléphone','SIFAC','SIRET','Type','TVA'];

        fputcsv($handler, $headers);



        $i = 0;
        /** @var Organization $organization */
        foreach( $organizations as $organization ){
            $datas = [
                'ID' => $organization->getId(),
                'NomCourt' => $organization->getShortName(),
                'NomLong' => $organization->getFullName(),
                'Code' => $organization->getCode(),
                'Email' => $organization->getEmail(),
                'URL' => $organization->getUrl(),
                'rue1' => $organization->getStreet1(),
                'rue2' => $organization->getStreet2(),
                'rue3' => $organization->getStreet3(),
                'CP' => $organization->getZipCode(),
                'BP' => $organization->getBp(),
                'ville' => $organization->getCity(),
                'Pays' => $organization->getCountry(),
                'CodePays' => $organization->getCodePays(),
                'Téléphone' => $organization->getPhone(),
                'SIFAC' => $organization->getSifacId(),
                'SIRET' => $organization->getSiret(),
                'Type' => $organization->getType(),
                'TVA' => $organization->getNumTVACA(),
            ];
            $activities = [];
            /** @var ActivityOrganization $activity */
            foreach( $organization->getActivities() as $activity ){
                $activities[] = $activity->getActivity()->getOscarNum();
            }
            /** @var ProjectPartner $project */
            foreach( $organization->getProjects() as $project ){
                /** @var Activity $activity */
                foreach( $project->getProject()->getActivities() as $activity ){
                    $activities[] = $activity->getOscarNum();
                }
            }
            $datas['activities'] = implode(', ', $activities);

            fputcsv($handler, $datas);
        }

        fclose($handler);

        header('Content-Disposition: attachment; filename=oscar-export-organisations.csv');
        header('Content-Length: ' . filesize('/tmp/' . $filename));
        header('Content-type: plain/text');

        die(file_get_contents('/tmp/' . $filename));
    }

    /**
     * Retourne la liste des organisations recherchées.
     */
    public function searchAction()
    {
        $page = (int) $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 3) {
            return $this->getResponseBadRequest("Not enough chars (3 required)");
        }
        $organizations = $this->getOrganizationService()->getOrganizationsSearchPaged($search, $page);

        $result = [ 'datas' => []];
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
                    foreach ($organization->getPersons() as $organizationPerson ) {
                        $newPartner = new OrganizationPerson();
                        $this->getEntityManager()->persist($newPartner);
                        $newPartner->setRoleObj($organizationPerson->getRoleObj())
                            ->setOrganization($newOrganization)
                            ->setPerson($organizationPerson->getPerson())
                            ;
                        $this->getEntityManager()->remove($organizationPerson);
                    }
                    $this->getEntityManager()->remove($organization);
                }

                // On push les données reçues
                foreach ($request->getPost()->toArray() as $key => $value) {
                    if (in_array($key, $properties)) {
                        $method = 'set'.ucfirst($key);
                        $newOrganization->$method($value);
                    }
                }

                // @todo On affecte les projets
                $this->getEntityManager()->flush();

                $this->redirect()->toRoute('organization/show', ['id'=>$newOrganization->getId()]);
            }

            $propertiesValues = [];

            foreach ($organizations as $organization) {
                foreach ($properties as $property) {
                    $method = 'get'.ucfirst($property);
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
                'datas' => $propertiesValues,
            ];
        }
    }

    public function showAction()
    {
        $organizationId = $this->params()->fromRoute('id');
        $page = $this->params()->fromQuery('page', 1);
        return [
            'organization' => $this->getOrganizationService()->getOrganization($organizationId),
            'projects' => new UnicaenDoctrinePaginator($this->getProjectService()->getProjectOrganization($organizationId), $page),
            'activities' => $this->getActivityService()->byOrganizationWithoutProject($organizationId),
        ];
    }

    public function newAction()
    {
        $form = new OrganizationIdentificationForm($this->getOrganizationService()->getConnectorsList());

        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();
            $form->setData($posted);
            $entity = new Organization();
            $this->getEntityManager()->persist($entity);

            // VALIDATION

            // HYDRATATION
            $hydrator = new EntityHydrator();

            $connectorsFields = [];
            foreach( $this->getOrganizationService()->getConnectorsList() as $connector ){
                $connectorsFields[] = 'connector_' . $connector;
                $entity->setConnectorID($connector, $posted['connector_' . $connector]);
            }
            $hydrator->hydrateAuto($posted->toArray(), $entity, $connectorsFields);

            $this->getEntityManager()->flush($entity);
            $this->redirect()->toRoute('organization/show', ['id'=>$entity->getId()]);
        }

        $view = new ViewModel(array(
            'form' => $form,
            'id' => null,
            'connectors' => $this->getOrganizationService()->getConnectorsList(),
        ));
        $view->setTemplate('oscar/organization/form');

        return $view;
    }

    public function syncAction()
    {
        try {
            foreach( $this->getConfiguration('oscar.connectors.organization') as $key=>$getConnector ){
                $connector = $getConnector();

                if( $connector instanceof ServiceLocatorAwareInterface ){
                    $connector->setServiceLocator($this->getServiceLocator());
                }


                return [ 'repport' => $connector->syncOrganizations($this->getEntityManager()->getRepository(Organization::class), false ) ];
            }
        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }

    }

    public function synchronizeConnectorAction(){
        $idOrganization = $this->params()->fromRoute('id');
        $connector = $this->params()->fromRoute('connector');

        // Récupération de l'organisation
        /** @var Organization $organization */
        $organization = $this->getOrganizationService()->getOrganization($idOrganization);

        $config = $this->getConfiguration('oscar.connectors.organization');
        if( array_key_exists($connector, $config) ){
            /** @var IConnector, IConnectorOrganization $connector */
            $connector = $this->getServiceLocator()->get('ConnectorService')->getConnector('organization.'.$connector);

            try {
                $organization = $connector->syncOrganization($organization);
                $this->getEntityManager()->flush($organization);

                return $this->redirect()->toRoute('organization/show',
                    ['id' => $organization->getId()]);
            } catch( \Exception $e ){
                throw $e;
            }
        } else {
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


        if( $request->isPost() ){

            if( $this->params()->fromPost('etape', 1) == 3 ){
                die('DO');
                if( isset($_SESSION['fusion_hash']) && $_SESSION['fusion_hash'] == $this->params()->fromPost('hash', '') ){
                    if( !isset($_SESSION['fusion_data']) ){
                        $this->flashMessenger()->addErrorMessage("Erreur de transmission des données.");
                        $this->redirect()->toRoute('organization/fusion');
                    } else {
                        $organizationsFrom = $this->getEntityManager()->createQueryBuilder()
                            ->select('o')
                            ->from(Organization::class, 'o')
                            ->where('o.id IN (:ids)')
                            ->setParameter('ids', $_SESSION['fusion_data']['from'])
                            ->getQuery()
                            ->getResult();

                        $to = $this->getEntityManager()->getRepository(Organization::class)->find($_SESSION['fusion_data']['to']);

                        $date = $_SESSION['fusion_data']['at'];

                        /** @var Organization $organization */
                        foreach( $organizationsFrom as $organization ){
                            echo $organization."<br>";
                            /** @var ActivityOrganization $activityOrganization */
                            foreach( $organization->getActivities() as $activityOrganization ){
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
                } else {
                    $this->flashMessenger()->addErrorMessage("La procédure de fusion a été interrompue.");
                    $this->redirect()->toRoute('organization/fusion');
                    return;
                }

            } else {
                $etape = 2;
                $hash = uniqid('fusion_');
                $_SESSION['fusion_hash'] = $hash;
                $fusionDatas = [];

                // tester source vide
                $_SESSION['fusion_dat'] = $hash;
                $fusionDatas['from'] = $from = $this->params()->fromPost('from', null);
                if( !$from ){
                    $errors[] = "Vous devez spécifer l'organisation à scinder.";
                }
                // ...

                // tester destination vide
                $fusionDatas['to'] = $to = $this->params()->fromPost('to', []);
                if( count($to) == 0 ){
                    $errors[] = "Vous devez spécifer les organisations cibles.";
                }

                // ...

                // tester date vide
                $fusionDatas['at'] = $at = new \DateTime($this->params()->fromPost('dateFusion', null));

                if( $at == null ){
                    $errors[] = "Vous devez renseigner la date de la fusion";
                }
                // ...

                // Récupération des activités

                $_SESSION['fusion_data'] = $fusionDatas;
                $activities = [];

                $organisationsTo = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder('a')->where('a.id IN (:ids)')
                    ->setParameter('ids', $to)
                    ->getQuery()
                    ->getResult();

                $organisationFrom = $this->getEntityManager()->getRepository(Organization::class)->find($from);

                /** @var ActivityOrganization $activity */
                foreach( $organisationFrom->getActivities() as $activity ){
                    $activities[$activity->getActivity()->getId()] = $activity;
                }

            }
        }
        return [
            'activities' => $activities,
            'organizationsTo' => $organisationsTo,
            'from' => $from,
            'to' => $to,
            'etape' => $etape,
            'hash' => $hash,
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


        if( $request->isPost() ){

            if( $this->params()->fromPost('etape', 1) == 3 ){
                if( isset($_SESSION['fusion_hash']) && $_SESSION['fusion_hash'] == $this->params()->fromPost('hash', '') ){
                    if( !isset($_SESSION['fusion_data']) ){
                        $this->flashMessenger()->addErrorMessage("Erreur de transmission des données.");
                        $this->redirect()->toRoute('organization/fusion');
                    } else {
                        $organizationsFrom = $this->getEntityManager()->createQueryBuilder()
                            ->select('o')
                            ->from(Organization::class, 'o')
                            ->where('o.id IN (:ids)')
                            ->setParameter('ids', $_SESSION['fusion_data']['from'])
                            ->getQuery()
                            ->getResult();

                        $to = $this->getEntityManager()->getRepository(Organization::class)->find($_SESSION['fusion_data']['to']);

                        $date = $_SESSION['fusion_data']['at'];

                        /** @var Organization $organization */
                        foreach( $organizationsFrom as $organization ){
                            echo $organization."<br>";

                            /** @var ActivityOrganization $activityOrganization */
                            foreach( $organization->getActivities() as $activityOrganization ){
                                $activityOrganization->setDateEnd($date);
                                $activityOrganization->getOrganization()->setDateEnd($date);
                                $newRole = $activityOrganization->fusionTo($to, $date);
                                $this->getEntityManager()->persist($newRole);
                            }

                            /** @var ProjectPartner $projectPartner */
                            foreach( $organization->getProjects() as $projectPartner ){
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
                } else {
                    $this->flashMessenger()->addErrorMessage("La procédure de fusion a été interrompue.");
                    $this->redirect()->toRoute('organization/fusion');
                    return;
                }

            } else {
                $etape = 2;
                $hash = uniqid('fusion_');
                $_SESSION['fusion_hash'] = $hash;
                $fusionDatas = [];

                // tester source vide
                $_SESSION['fusion_dat'] = $hash;
                $fusionDatas['from'] = $from = $this->params()->fromPost('from', []);
                if( count($from) == 0 ){
                    $errors[] = "Vous devez spécifer une ou plusieurs organisations à fusionner.";
                }
                // ...

                // tester destination vide
                $fusionDatas['to'] = $to = $this->params()->fromPost('to', null);
                if( count($to) == 0 ){
                    $errors[] = "Vous devez spécifer une organisation cible.";
                }

                // ...

                // tester date vide
                $fusionDatas['at'] = $at = new \DateTime($this->params()->fromPost('dateFusion', null));

                if( $at == null ){
                    $errors[] = "Vous devez renseigner la date de la fusion";
                }
                // ...

                // Récupération des activités

                $_SESSION['fusion_data'] = $fusionDatas;
                $activities = [];

                $organisations = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder('a')->where('a.id IN (:ids)')
                    ->setParameter('ids', $from)
                    ->getQuery()
                    ->getResult();

                /** @var Organization $organisation */
                foreach( $organisations as $organisation ){
                    /** @var ActivityOrganization $activity */
                    foreach( $organisation->getActivities() as $activity ){
                        $idActivity = $activity->getActivity()->getId();
                        $activities[$idActivity] = $activity;
                    }
                    /** @var ProjectPartner $project */
                    foreach( $organisation->getProjects() as $projectpartner ){
                        foreach( $projectpartner->getProject()->getActivities() as $activity ){
                            $idActivity = $activity->getId();
                            $activities[$idActivity] = $activity;
                        }
                    }
                }
            }
        }
        return [
            'activities' => $activities,
            'from' => $from,
            'to' => $to,
            'etape' => $etape,
            'hash' => $hash,
        ];
    }

    public function closeAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        /** @var Organization $organization */
        $organization = $this->getEntityManager()->getRepository(Organization::class)->find($id);
        if( $organization ){
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
        if( $id ){
            /** @var Organization $entity */
            $entity = $result->getQuery()->getSingleResult();
        }

        $form = new OrganizationIdentificationForm($this->getOrganizationService()->getConnectorsList());

        if ($this->getRequest()->isPost()) {
            $posted = $this->getRequest()->getPost();

            $form->setData($posted);

            // VALIDATION

            // HYDRATATION
            $hydrator = new EntityHydrator();
            $connectorsFields = [];
            foreach( $this->getOrganizationService()->getConnectorsList() as $connector ){
                $connectorsFields[] = 'connector_' . $connector;
                $entity->setConnectorID($connector, $posted['connector_' . $connector]);
            }
            $hydrator->hydrateAuto($posted->toArray(), $entity, $connectorsFields);

            $em->flush($entity);

            $this->redirect()->toRoute('organization/show', ['id' => $id]);

            //$this->getSearchProjectService()->update($entity);
        } // Affichage
        else {
            $datas = $result->getQuery()->getResult(Query::HYDRATE_ARRAY)[0];
            foreach( $this->getOrganizationService()->getConnectorsList() as $connector ){
                $datas['connector_' . $connector] = $entity->getConnectorID($connector);
            }
            $form->setData($datas);
        }

//        $this->saveIfNeeded($entity, $this->getRequest(), $form);


        $view = new ViewModel(array(
            'id' => $id,
            'organization' => $entity,
            'form' => $form,
            'connectors' => $this->getOrganizationService()->getConnectorsList()
        ));
        $view->setTemplate('oscar/organization/form');

        return $view;
    }
}
