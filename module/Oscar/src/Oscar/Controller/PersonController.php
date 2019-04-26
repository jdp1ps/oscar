<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/06/15 17:24
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Unauthorized401Exception;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\LogActivity;
use Oscar\Entity\ActivityLogRepository;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\Referent;
use Oscar\Entity\Role;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Form\MergeForm;
use Oscar\Form\PersonForm;
use Oscar\Hydrator\PersonFormHydrator;
use Oscar\Provider\Person\SyncPersonHarpege;
use Oscar\Provider\Privileges;
use Oscar\Service\PersonnelService;
use Oscar\Service\PersonService;
use Oscar\Service\TimesheetService;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class PersonController extends AbstractOscarController
{
    /**
     * Liste des personnes issue de Ldap.
     *
     * @return JsonModel
     *
     * @deprecated
     */
    public function apiLdapAction()
    {
        $this->getResponseDeprecated();
    }

    public function deleteAction(){
        $method = $this->getHttpXMethod();
        $this->getOscarUserContext()->check(Privileges::PERSON_EDIT);
        try {
            if( $method != 'POST' ){
                return $this->getResponseBadRequest("Opération non-authorisée");
            }
            $person = $this->getPersonService()->getPersonById($this->params()->fromRoute('id'), true);

            $del = $this->getEntityManager()->createQueryBuilder()->delete(NotificationPerson::class, 'n')
                ->where('n.person = :person')
                ->setParameter('person', $person);

            $del->getQuery()->execute();

            $del = $this->getEntityManager()->createQueryBuilder()->delete(ProjectMember::class, 'n')
                ->where('n.person = :person')
                ->setParameter('person', $person);

            $del->getQuery()->execute();

            $del = $this->getEntityManager()->createQueryBuilder()->delete(ActivityPerson::class, 'n')
                ->where('n.person = :person')
                ->setParameter('person', $person);

            $del->getQuery()->execute();

            $del = $this->getEntityManager()->createQueryBuilder()->delete(OrganizationPerson::class, 'n')
                ->where('n.person = :person')
                ->setParameter('person', $person);

            $del->getQuery()->execute();

            $this->getEntityManager()->remove($person);
            $this->getEntityManager()->flush();
            if( $this->getOscarUserContext()->check(Privileges::PERSON_INDEX) ){
                $this->redirect()->toRoute('person/index');
            }
            $this->redirect()->toRoute('home');

        }
        catch (ForeignKeyConstraintViolationException $e) {
            $this->getLogger()->error($e->getMessage());
            throw new OscarException("Impossible de supprimer $person, elle est utilisée.");
        }
        catch (\Exception $e) {
            throw new OscarException("PAS POSSIBLE : " . $e->getMessage());
        }
    }

    public function accessAction(){


        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);

        $person = $this->getPersonService()->getPerson($this->params()->fromRoute('id'));

        $privilegesDT = $this->getEntityManager()->getRepository(Privilege::class)->findBy([], ['root' => 'DESC']);

        $privileges = [];
        /** @var Privilege $privilege */
        foreach ($privilegesDT as $privilege) {
            $p = [
                'id' => $privilege->getId(),
                'label' => $privilege->getLibelle(),
                'category' => $privilege->getCategorie()->getLibelle(),
                'spot' => $privilege->getSpot(),
                'roleIds' => $privilege->getRoleIds(),
                'root' => $privilege->getRoot() ? $privilege->getRoot()->getId() : null,
                'enabled' => false
            ];
            $privileges[$privilege->getId()] = $p;
        }
        $rolesApplication = [];
        try {
            $roles = $this->getPersonService()->getRolesApplication($person);
            foreach ($roles as $role) {
                $rolesApplication[] = $role->getId();
            }

        } catch (\Exception $e ){}

        $rolesOrganisation = [];

        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization) {
            $organizationId = $personOrganization->getOrganization()->getId();
            $organization = (string)$personOrganization->getOrganization();
            $roleId = $personOrganization->getRoleObj()->getId();
            $role = $personOrganization->getRoleObj()->getRoleId();

            if( !array_key_exists($organizationId, $rolesOrganisation) ){
                $rolesOrganisation[$organizationId] = [
                    'id' => $organizationId,
                    'label' => $organization,
                    'roles' => [],
                ];
                $rolesOrganisation[$organizationId]['roles'][$roleId] = $role;
            }

        }

        return [
            'person' => $person,
            'application' => $rolesApplication,
            'organizations' => $rolesOrganisation,
            'privileges' => $privileges
        ];
    }

    public function personnelAction(){

        $access = $this->getConfiguration('oscar.listPersonnel');

        if( $access == 0 ){
            throw new BadRequest400Exception("Cette fonctionnalité n'est pas activé");
        }

        $q = $this->params()->fromQuery('q', "");
        $page = $this->params()->fromQuery('p', 1);
        $params = [
            'filter_roles' => [],
            'order_by' => 'lastname',
            'leader' => null
        ];

        $idCoWorkers = [];

        $idSubordinates = $this->getPersonService()->getSubordinateIds($this->getCurrentPerson()->getId());
        $idTimesheet = $this->getPersonService()->getTimesheetDelegationIds($this->getCurrentPerson()->getId());


        if( $access > 1 ){
            $idCoWorkers = $this->getPersonService()->getCoWorkerIds($this->getCurrentPerson()->getId());
        }




        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_INDEX) ){
            $params['ids'] = array_merge($idCoWorkers, $idSubordinates, $idTimesheet);
        }


        // TODO Verifier configuration + DROIT sur le Rôle dans l'oganisation

        $extended = $this->params()->fromQuery('extended', 0);
        if( $extended ){
            $datas = $this->getPersonService()->searchPersonnel($q, $page, $params);
        } else {
            $datas = $this->getPersonService()->getPersonsSearchPaged($q, $page, $params);
        }

        $output = [
            'total'=> count($datas),
            'search' => $q,
            'persons' => [],
            'extended' => $extended,
            'page' => $page,
            'resultbypage' => 50,
            'coworkers' => [],
            'subordinates' => [],
        ];



        /** @var Person $person */
        foreach ($datas as $person) {
            $datasPerson = $person->toArrayList();
            $datasPerson['sub'] = false;
            $datasPerson['coworker'] = false;
            $datasPerson['activities'] = count($person->getActivities());

            if( in_array($person->getId(), $idSubordinates ) ){
                $datasPerson['sub'] = true;
            }
            if( in_array($person->getId(), $idCoWorkers ) ){
                $datasPerson['coworker'] = true;
            }
            $output['persons'][] = $datasPerson;
        }

        $output['subordinates'] = $idSubordinates;
        $output['coworkers'] = $idCoWorkers;

        if( $this->isAjax() || $this->params()->fromQuery('format') == 'json' ){
            return $this->ajaxResponse($output);
        }

        return ['result' => $output ];
    }

    public function grantAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);
        $person = $this->getPersonService()->getPerson($this->params()->fromRoute('id'));

        $organizations = [];

        /** @var OrganizationPerson $personOrganization */
        foreach( $person->getOrganizations() as $personOrganization ){
            $organizations[$personOrganization->getOrganization()->getId()] = [
                'rôles' => $this->getOscarUserContext()->getRolesPersonInOrganization($person, $personOrganization->getOrganization()),
                'privileges' => $this->getOscarUserContext()->getPersonPrivilegesInOrganization($person, $personOrganization->getOrganization())
            ];

        }

        var_dump($organizations);

        //$userCOntext = $this->getOscarUserContext()->getPri
        return [
            'person' => $person
        ];
    }

    public function viewsAction()
    {
        $view = $this->params()->fromQuery('view', 'almoststart');
        $warning = null;
        $activities = [];

        switch( $view ){
            case 'almoststart':
                $activities = $this->getActivityService()->getActivityBeginsSoon();
                break;
            default :

                break;
        }
        return [
            'entities' => $activities
        ];
    }

    /**
     * @deprecated
     */
    public function syncAction()
    {
        /** @var $personnelService PersonnelService */
        $personnelService = $this->getServiceLocator()->get('PersonnelService');
        $personRepo = $this->getEntityManager()->getRepository('\Oscar\Entity\Person');

        $persons = $personnelService->searchStaff('l', false);

        // Passage par ID LDAP
        foreach ($persons as $key => $person) {
            $inOscar = $personRepo->findOneBy([
                'codeLdap' => $person['id'],
            ]);
            if ($inOscar) {
                echo $person['displayname']." existe déjà avec l'identifiant LDAP valide.\n";
                unset($persons[$key]);
            }
        }
        // Passage par EMAIL LDAP
        foreach ($persons as $key => $person) {
            $inOscar = $personRepo->findOneBy([
                'email' => $person['mail'],
            ]);
            if ($inOscar) {
                if (!$inOscar->getCodeLdap()) {
                    echo "Ajout de l'ID LDAP à ".$person['displayname'].' (mail: '.$person['mail'].')';
                    $inOscar->setCodeLdap($person['id']);
                    $this->getEntityManager()->flush($inOscar);
                    unset($persons[$key]);
                }
            }
        }

        foreach ($persons as $key => $person) {
            echo 'Création de '.$person['displayname']." dans Oscar...\n";
            $new = new Person();
            $this->getEntityManager()->persist($new);
            $new->setCodeLdap($person['id'])
                ->setEmail($person['mail'])
                ->setFirstname($person['prenom'])
                ->setLastname($person['nom']);
            $this->getEntityManager()->flush($new);
        }

        die('DONE');
    }

    public function notificationPersonAction(){
        $id = $this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($id);
        return [
            'person' => $person,
            'notifications' => $this->getNotificationService()->getAllNotificationsPerson($person->getId())
        ];
    }

    public function notificationPersonGenerateAction(){
        $id = $this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($id);
        $this->getNotificationService()->generateNotificationsPerson($person);
        $this->redirect()->toRoute('person/notification', ['id'=>$person->getId()]);
    }



    /**
     * Affiche la liste des personnes.
     *
     * @return array
     */
    public function indexAction()
    {
        // Donnèes GET
        $page = (int) $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        $filterRoles = $this->params()->fromQuery('filter_roles', []);
        $orderBy = $this->params()->fromQuery('orderby', 'lastname');
        $leader = $this->params()->fromQuery('leader', '');
        $format = $this->params()->fromQuery('format', '');

        // Liste des critères de trie disponibles
        $orders = [
            'lastname' => 'Nom de famille',
            'firstname' => 'Prénom',
            'email' => 'Email',
            'dateCreated' => 'Date de création',
            'dateUpdated' => 'Date de mise à jour'
        ];

        if( !array_key_exists($orderBy, $orders) ){
            $orderBy = $orders[0];
        }



        $datas = $this->getPersonService()->getPersonsSearchPaged($search, $page, [
            'filter_roles' => $filterRoles,
            'order_by' => $orderBy,
            'leader' => $leader
        ]);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Export CSV
        ///
        if( $format == "csv" ){
            // Fichier temporaire
            $baseFileName = 'oscar-export-persons';
            $filename = uniqid($baseFileName) . '.csv';
            $handler = fopen('/tmp/' . $filename, 'w');

            fputcsv($handler, [
                'ID Oscar',
                'Prénom',
                'Nom',
                'Courriel',
                'Téléphone',
                'Affectation',
                'Localisation'
            ]);

            /** @var Person $person */
            foreach ($datas->getQueryBuilder()->getQuery()->getResult() as $person) {
                fputcsv($handler, [
                    $person->getId(),
                    $person->getFirstname(),
                    $person->getLastname(),
                    $person->getEmail(),
                    $person->getPhone(),
                    $person->getLdapAffectation(),
                    $person->getLdapSiteLocation()
                ]);
            }

            fclose($handler);

            header('Content-Disposition: attachment; filename='.$baseFileName.'.csv');
            header('Content-Length: ' . filesize('/tmp/' . $filename));
            header('Content-type: plain/text');
            echo file_get_contents('/tmp/' . $filename);
            @unlink('/tmp/' . $filename);
            die();
        }



        if ($this->getRequest()->isXmlHttpRequest()) {
            $json = [
                'datas' => []
            ];
            foreach ($datas as $data) {
                $json['datas'][] = $data->toArray();
            }
            $view = new JsonModel();
            $view->setVariables($json);

            return $view;
        }

        $roles = $this->getEntityManager()->getRepository(Person::class)->getRolesLdapUsed();

        $dbroles =$this->getPersonService()->getRolesByAuthentification();

        return array(
            'dbroles' => $dbroles,
            'roles' => $roles,
            'search' => $search,
            'persons' => $datas,
            'filter_roles' =>  $filterRoles,
            'orderBy' => $orderBy,
            'orders' => $orders,
            'leader' => $leader
        );
    }

    /**
     * Recherche les personnes.
     *
     * @return array
     */
    public function searchAction()
    {
        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 2) {
            return $this->getResponseBadRequest("Not enough chars (4 required");
        }
        $datas = $this->getPersonService()->search($search);

        $json = [
            'datas' => []
        ];
        foreach ($datas as $data) {
            $json['datas'][] = $data->toArray();
        }
        $view = new JsonModel();
        $view->setVariables($json);

        return $view;
    }

    /**
     * Recherche les personnes.
     *
     * @return array
     */
    public function old_searchAction()
    {
        $page = (int) $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        if (strlen($search) < 4) {
            return $this->getResponseBadRequest("Not enough chars (4 required");
        }
        $datas = $this->getPersonService()->getPersonsSearchPaged($search, $page);

        $json = [
            'datas' => []
        ];
        foreach ($datas as $data) {
            $json['datas'][] = $data->toArray();
        }
        $view = new JsonModel();
        $view->setVariables($json);

        return $view;
    }

    /**
     * Synchronise la personne avec LDap
     */
    public function syncLdapAction()
    {
        $personId = (int)$this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($personId);
        if ($person && $this->getPersonService()->syncLdap($person)) {
            $this->getActivityLogService()->addUserInfo(sprintf("a synchronisé la fiche %s", $person->log()), $this->getDefaultContext(), $personId);
            $this->flashMessenger()->addSuccessMessage(sprintf("La personne '%s' a bien été synchronisé avec LDap.",
                $person));
            return $this->redirect()->toRoute('person/show', ['id'=>$person->getId()]);
        }
        die("DONE");
    }

    private $_cacheGetOrgaByCode = [];

    private function extractArrayKeyValue( $array, $key ){
        if( array_key_exists($key, $array) ){
            return $array[$key];
        } else {
            return null;
        }
    }

    public function bossAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $persons = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin('p.organizations', 'o')
            ->innerJoin('o.roleObj', 'r')
            ->where('r.principal = true')
            ;

        $dbroles =$this->getPersonService()->getRolesByAuthentification();

        return [
            'dbroles' => $dbroles,
            'ldapFilters' => $this->getEntityManager()->getRepository(Person::class)->getRolesLdapUsed(),
            'persons' => new UnicaenDoctrinePaginator($persons, $page)
        ];
    }

    public function synchronizeAction()
    {
        $personId = (int)$this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($personId);
        if( $person ){
            $this->getPersonService()->synchronize($person);
            return $this->redirect()->toRoute('person/show', ['id'=>$person->getId()]);
        } else {
            return $this->getResponseNotFound('Personne introuvable');
        }
    }

    /**
     * Fiche pour une personne.
     *
     * @return array
     */
    public function showAction()
    {
        $id = $this->params()->fromRoute('id');
        $page = $this->params()->fromQuery('page', 1);
        $person = $this->getPersonService()->getPerson($id);


        $manageHierarchie = $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_EDIT);
        $manageUsurpation = $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_EDIT);
        $allowTimesheet = false;

        if( $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_FEED_TIMESHEET) || $person->getTimesheetsBy()->contains($this->getCurrentPerson()) ){
            $allowTimesheet = true;
        }


        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_SHOW) ){

            // N+1 ?
            /** @var PersonRepository $personRepository */
            $personRepository = $this->getEntityManager()->getRepository(Person::class);

            // Subordonnées de la personne connectée
            $subordinatesIds = $personRepository->getSubordinatesIds($this->getCurrentPerson()->getId());

            if( !in_array($person->getId(), $subordinatesIds) ){
                /** @var OrganizationRepository $organizationRepository */
                $organizationRepository = $this->getEntityManager()->getRepository(Organization::class);
                $organizationIds = $organizationRepository->getOrganizationsIdsForPerson($this->getCurrentPerson()->getId(), true);
                $coworkerIds = $this->getPersonService()->getCoWorkerIds($this->getCurrentPerson()->getId());

                if( ! (in_array($id, $coworkerIds) || $this->getCurrentPerson()->getTimesheetsFor()->contains($person)) ){
                    throw new Unauthorized401Exception("Vous n'avez pas accès à la fiche de cette personne");
                }
            }
        }

        $auth = null;
        $activities = null;
        $traces = null;

        $method = $this->getHttpXMethod();

        if( $this->isAjax() ){
            $action = $this->params()->fromQuery('a');

            switch( $action ){
                case 'schedule':
                    /** @var TimesheetService $timesheetService */
                    $timesheetService = $this->getServiceLocator()->get('TimesheetService');

                    $models = $this->getConfiguration('oscar.scheduleModeles');

                    if( $method == "POST" ){
                        $this->getOscarUserContext()->check(Privileges::PERSON_MANAGE_SCHEDULE);

                        try {
                            $daysLength = $this->params()->fromPost('days');
                            $model = $this->params()->fromPost('model');

                            if( $model == 'default' ){
                                $this->getLogger()->info("Remise par défaut des horaires de $person");

                                $custom = $person->getCustomSettingsObj();
                                $this->getLogger()->info(print_r($custom, true));
                                unset($custom['days']);
                                unset($custom['scheduleModele']);
                                $person->setCustomSettingsObj($custom);
                                $this->getEntityManager()->flush($person);
                                $this->getLogger()->info(print_r($custom, true));
                            }
                            elseif ($daysLength != null) {
                                $this->getUserParametersService()->performChangeSchedule($daysLength, $person);
                                return $this->getResponseOk("Heures enregistrées");
                            }
                            else {
                                if( !array_key_exists($model, $models) ){
                                    return $this->getResponseBadRequest("Modèle inconnu");
                                }
                                $custom = $person->getCustomSettingsObj();
                                unset($custom['days']);
                                $custom['scheduleModele'] = $model;
                                $person->setCustomSettingsObj($custom);
                                $this->getEntityManager()->flush($person);
                                $this->getLogger()->info(print_r($custom, true));

                            }

                        } catch (\Exception $e) {
                            return $this->getResponseInternalError("Impossible d'enregistrer les paramètres : " . $e->getMessage());
                        }
                        return $this->getResponseOk();
                    }

                    $datas = $timesheetService->getDayLengthPerson($person);
                    $datas['models'] = $models;

                    return $this->ajaxResponse($datas);
                    break;
                default:
                    return $this->getResponseInternalError("Action non-reconnue");
                    break;

            }
        }

        if( $method == "POST" ){

            $action = $this->params()->fromPost('action', null);

            if( in_array($action, ['addusurpation', 'removeusurpation'] ) ){
                $person_id = $this->params()->fromPost('person_id', null);
                if( !$person_id ){
                    throw new OscarException("Impossible de gérer la délagation des feuilles de temps, l'identifiant de la personne manquant");
                }
                $other = $this->getPersonService()->getPersonById($person_id);

                if( !$other ) {
                    throw new OscarException("Impossible de gérer la délagation des feuilles de temps, la personne n'a pas été trouvée.");
                }

                if( $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_FEED_TIMESHEET) ){

                    switch( $action ) {
                        case 'addusurpation' :
                            try {
                                $person->addTimesheetUsurpation($other);
                                $this->getEntityManager()->flush($person);
                                $this->flashMessenger()->addSuccessMessage("$other est maintenant autorisé à remplir les feuilles de temps de $person");
                                return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);
                            } catch (\Exception $exception) {
                                return $this->getResponseInternalError($exception->getMessage());
                            }

                        case 'removeusurpation' :
                            try {
                                $person->removeTimesheetUsurpation($other);
                                $this->getEntityManager()->flush([$person, $other]);
                                $this->flashMessenger()->addSuccessMessage(sprintf(_('%s ne peut plus remplir les feuilles de temps de %s.'), $other, $person));
                                return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);
                            } catch (\Exception $exception) {
                                return $this->getResponseInternalError($exception->getMessage());
                            }

                        default:
                            throw new OscarException("Opération inconnue");
                    }
                } else {
                    return $this->getResponseUnauthorized("Vous n'avez pas le droit de déléguer la déclaration d'une personne à une autre.");
                }

            }
            if( !$manageHierarchie ){
                return $this->getResponseUnauthorized();
            }
            switch( $action ) {
                case 'referent' :
                    $referent_id = $this->params()->fromPost('referent_id', null);
                    $person_id = $this->params()->fromPost('person_id', null);
                    $this->getPersonService()->addReferent($referent_id, $person_id);
                    return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);

                case 'addusurpation' :
                    $person_id = $this->params()->fromPost('person_id', null);
                    $other = $this->getPersonService()->getPersonById($person_id);
                    $person->addTimesheetUsurpation($this->getPersonService()->getPersonById($person_id));
                    $this->getEntityManager()->flush([$person, $other]);
                    $this->flashMessenger()->addSuccessMessage("$other est autorisé à replir les feuilles de temps de $person");
                    return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);

                case 'removeusurpation' :
                    $person_id = $this->params()->fromPost('person_id', null);
                    $other = $this->getPersonService()->getPersonById($person_id);
                    $person->removeTimesheetUsurpation($other);
                    $this->getEntityManager()->flush([$person, $other]);
                    $this->flashMessenger()->addSuccessMessage(sprintf(_('%s ne peut plus remplir les feuilles de temps de %s.'), $other, $person));
                    return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);

                case 'removereferent' :
                    $referent_id = $this->params()->fromPost('referent_id', null);
                    $this->getPersonService()->removeReferentById($referent_id);
                    return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);

                default:
                    return $this->getResponseInternalError("Opération inconnue");
            }
        }

        if ($person && $person->getLadapLogin()) {
            $auth = $this->getEntityManager()
                ->getRepository('Oscar\Entity\Authentification')
                ->findOneBy(['username'=>$person->getLadapLogin()]);
            if ($auth) {
                /** @var ActivityLogRepository $activityRepo */
                $activityRepo = $this->getEntityManager()->getRepository(LogActivity::class);

                $traces = $activityRepo->getUserActivity($auth->getId(), 20);
            }
        }

        $ldapRoles = $this->getEntityManager()
            ->createQueryBuilder('r', 'r.ldapFilter')
            ->select('r')
            ->from(Role::class, 'r', 'r.ldapFilter')
            ->where('r.ldapFilter IS NOT NULL')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        $roles = [];
        $re = '/\(memberOf=(.*)\)/';
        foreach ($ldapRoles as $role ){
            $roles[preg_replace($re, '$1', $role['ldapFilter'])] = $role;
        }

        // Récupération des référents
        $referents = $this->getPersonService()->getReferentsPerson($person);
        $subordinates = $this->getPersonService()->getSubordinatesPerson($person);

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceLocator()->get('TimesheetService');

        return [
            'schedule'  => $timesheetService->getDayLengthPerson($person),
            'entity' => $person,
            'ldapRoles' => $roles,
            'scheduleEditable' => $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_MANAGE_SCHEDULE),
            'referents' => $referents,
            'manageHierarchie' => $manageHierarchie,
            'manageUsurpation' => $manageUsurpation,
            'subordinates' => $subordinates,
            'authentification' => $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $person->getLadapLogin()]),
            'auth' => $auth,
            'allowTimesheet' => $allowTimesheet,
            'projects'  => new UnicaenDoctrinePaginator($this->getProjectService()->getProjectUser($person->getId()), $page),
            'activities' => $this->getProjectGrantService()->personActivitiesWithoutProject($person->getId()),
            'traces' => $traces,
            'connectors' =>array_keys($this->getConfiguration('oscar.connectors.person'))
        ];
    }


    public function mergeAction()
    {
        // Récupération des personnes à fusionner
        $personIds = explode(',', $this->params()->fromQuery('ids', ''));
        $persons = $this->getPersonService()->getByIds($personIds);
        $personConnector = array_keys($this->getServiceLocator()->get('Config')['oscar']['connectors']['person']);
        $hydrator = new PersonFormHydrator($personConnector);
        $form = new MergeForm;
        $form->preInit($hydrator, $persons);
        $form->init();

        $request = $this->getRequest();
        $newPerson = new Person();
        $form->setObject($newPerson);

        if( $request->isPost() ){
            $form->setData($request->getPost());

            if($form->isValid()){

                //
                $this->getEntityManager()->persist($newPerson);
                $this->getEntityManager()->flush($newPerson);

                $conn = $this->getEntityManager()->getConnection();


                /** @var Person $person */
                foreach( $persons as $person){
                    // $person->mergeTo($newPerson);

                    $this->getLogger()->info('Transfert de ' . $person->getId() . ' vers ' . $newPerson->getId());
                    // Notification
                    $conn->executeUpdate(
                        'UPDATE notificationperson SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);

                    // Affectation
                    $conn->executeUpdate(
                        'UPDATE activityperson SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE administrativedocument SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE contractdocument SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE notificationperson SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE organizationperson SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE projectmember SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);

                    // Feuille de temps
                    $conn->executeUpdate(
                        'UPDATE timesheet SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);
                    $conn->executeUpdate(
                        'UPDATE timesheet SET createdby_id = ? WHERE createdby_id = ?',
                        [$newPerson->getId(), $person->getId()]);

                    // Lot de travail
                    $conn->executeUpdate(
                        'UPDATE workpackageperson SET person_id = ? WHERE person_id = ?',
                        [$newPerson->getId(), $person->getId()]);

                    $conn->executeQuery('DELETE FROM person WHERE id = ? ', [$person->getId()]);


                }

                $this->redirect()->toRoute('person/show', ['id'=>$newPerson->getId()]);
            }
        }

        return [
            'form'  => $form
        ];
    }


    /**
     * Modification d'une personne.
     *
     * @return ViewModel
     */
    public function organizationRoleAction()
    {
        $id = $this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($id);


        $request = $this->getRequest();
        if( $request->isPost() ){
            var_dump($request->getPost());
        }

        $view = new ViewModel([
            'entity'    => $person,
            'id'        => $id,
        ]);

        $view->setTemplate('/oscar/person/organizationrole');

        return $view;
    }

    /**
     * Modification d'une personne.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $person = $this->getPersonService()->getPerson($id);
        $form = new \Oscar\Form\PersonForm();

        try {
            $connectors =  $this->getConfiguration('oscar.connectors.person');
            $personConnector = array_keys($connectors);


            $form->setConnectors($personConnector);
        } catch( \Exception $e ){

        }

        $form->init();
        $form->bind($person);

        $request = $this->getRequest();
        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ){
                $this->getEntityManager()->flush($person);
                $this->getActivityLogService()->addUserInfo(
                    sprintf('a modifié les informations pour %s', $person->log()),
                    $this->getDefaultContext(), $person->getId(),
                    LogActivity::LEVEL_INCHARGE
                );
                $this->flashMessenger()->addSuccessMessage(_('Données sauvegardées.'));
                $this->redirect()->toRoute('person/show', ['id'=>$person->getId()]);
            }
        }

        $view = new ViewModel([
            'connectors' => $personConnector,
            'person'    => $person,
            'id'        => $id,
            'form'      => $form
        ]);

        $view->setTemplate('/oscar/person/form');

        return $view;
    }

    /**
     * Modification d'une personne.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        $person = new Person();
        $form = new \Oscar\Form\PersonForm();
        $form->init();
        $form->bind($person);

        $request = $this->getRequest();
        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ){
                $this->getEntityManager()->persist($person);
                $form->getHydrator()->hydrate($request->getPost()->toArray(), $person);
                $this->getEntityManager()->flush($person);
                $this->getActivityLogService()->addUserInfo(
                    sprintf('a ajouté %s à la liste des personnes', $person->log()),
                    $this->getDefaultContext(), $person->getId(),
                    LogActivity::LEVEL_INCHARGE
                );
                $this->flashMessenger()->addSuccessMessage(_('Données sauvegardées.'));
                $this->redirect()->toRoute('person/show', ['id'=>$person->getId()]);
            }
        }

        $view = new ViewModel([
            'person'    => $person,
            'id'        => null,
            'form'      => $form
        ]);

        $view->setTemplate('/oscar/person/form');

        return $view;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // Usuals Getters
    /**
     * @return PersonService
     */
    protected function getPersonService()
    {
        return $this->getServiceLocator()->get('PersonService');
    }
}
