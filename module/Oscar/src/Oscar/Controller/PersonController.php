<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/06/15 17:24
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

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


        if( $access > 1 ){
            $idCoWorkers = $this->getPersonService()->getCoWorkerIds($this->getCurrentPerson()->getId());
        }




        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_INDEX) ){
            $params['ids'] = array_merge($idCoWorkers, $idSubordinates);
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
        $datas = $this->getPersonService()->searchStaff($search);

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

    /**
     * Synchronise la personne avec LDap
     */
    public function syncLdap2Action()
    {
        die("OUT OF DATE");
        // Pile pour les résultats
        $created = [];
        $updated = [];
        $errors = [];

        $ldapDatas = $this->getPersonService()->getLdapPersons();

        $re = "/\\[role=\\{SUPANN\\}([A-Z][0-9]{1,3}).*\\[code=HS_([A-Z][0-9]{1,3}).*\\[libelle=([\\w]*)\\]/";
        $roles = [
            'R00' => ProjectMember::ROLE_RESPONSABLE, //'Responsable',
            'D30' => ProjectMember::ROLE_RESPONSABLE, //'Directeur',
            'J60' => ProjectMember::ROLE_CORESPONSABLE, //'Directeur-ajoint',
            'F40' => ProjectMember::ROLE_RESPONSABLE, //'Directeur de département',
        ];
        $rolesIgnored = [
            'T87' // Informaticien
        ];

        $findPerson = $this->getEntityManager()->createQueryBuilder()->select('p')
            ->from(Person::class, 'p')
            ->where('p.codeHarpege = :harpege OR p.codeLdap = :ldap');

        foreach( $ldapDatas as $data ){
            $datasKeep = [
                 'uid' => $this->extractArrayKeyValue($data, 'uid'),
                 'givenname' => $this->extractArrayKeyValue($data, 'givenname'),
                 'sn' => $this->extractArrayKeyValue($data, 'sn'),
                 'mail' => $this->extractArrayKeyValue($data, 'mail'),
                 'mailforwardingaddress' => $this->extractArrayKeyValue($data, 'mailforwardingaddress'),
            ];
            $clean = $this->getPersonService()->convertCodeLdapToCodeHarpege($datasKeep['uid']);

            $persons = $findPerson->setParameters([
                'harpege' => $clean,
                'ldap' => $datasKeep['uid']
            ])->getQuery()->getResult();

            // Une seule personne (à mettre à jour)
            if( count($persons) == 1 ){
                $person = $persons[0];
            }
            // Aucun résultat (à créer)
            elseif( count($persons) == 0 ){
                // CREATE
                $person = $this->getPersonService()->createPersonFromLdapDatas($data, true);
                $created[] = "La personne " . $person->log() . " a été créée dans Oscar.";
            }
            // Hum... plusieurs personnes, doublon ?
            else {
                //// Erreur, plusieurs personne
                $ps = [];
                /** @var Person $p */
                foreach( $persons as $p ){
                    $ps[] = $p->log();
                }
                $errors[] = "Plusieurs personnes semblent partager un même numéro Ldap/Harpège : " . implode(', ', $ps);
                continue;
            }

            if( array_key_exists('supannroleentite', $data) ){
                $rolesAdded = [];
                $givenRoles = [];

                if( is_string($data['supannroleentite']) ){
                    $givenRoles[] = $data['supannroleentite'];
                } else {
                    $givenRoles = $data['supannroleentite'];
                }

                foreach( $givenRoles as $role ){
                    if( preg_match($re, $role, $matches) ){
                        $codeRole = $matches[1];
                        $codeOrga = $matches[2];
                        if( !isset($roles[$codeRole]) ){
                            if( !in_array($codeRole, $rolesIgnored) ){
                                $errors[] = "Code rôle $codeRole non traité";
                            }
                            continue;
                        }

                        $roleToAdd = $roles[$codeRole];
                        $orga = $this->getOrgaByCode($codeOrga);

                        if( !$orga ){
                            $errors[] = "Impossible de mettre à jour le role de " . $person->log() . " car l'organisation $codeOrga est absente de oscar.";
                            continue;
                        }

                        if( !$orga->hasPerson($person, $roleToAdd) ){
                            $rolesAdded[] = $roleToAdd .' dans ' . $orga->log();

                            $member = new OrganizationPerson();
                            $this->getEntityManager()->persist($member);
                            $member->setOrganization($orga)
                                ->setPerson($person)
                                ->setRole($roles[$codeRole]);
                            $this->getEntityManager()->flush($member);
                            $this->getEntityManager()->refresh($orga);
                        }
                    }
                }
                if( count($rolesAdded) ){
                    $updated[] = $person->log() . " a aquis les rôles " . implode(", ", $rolesAdded);
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    private $_cacheGetOrgaByCode = [];

    /**
     * @param $code
     * @return Organization
     */
    private function getOrgaByCode($code){
        if( !array_key_exists($code, $this->_cacheGetOrgaByCode) ){
            $this->_cacheGetOrgaByCode[$code] = $this->getEntityManager()->getRepository(Organization::class)->findOneBy([
                    'code' => $code]
            );
        }
        return $this->_cacheGetOrgaByCode[$code];
    }

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
                if( ! in_array($id, $coworkerIds) ){
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

                    if( $method == "POST" ){
                        $this->getOscarUserContext()->check(Privileges::PERSON_MANAGE_SCHEDULE);

                        try {
                            $daysLength = $this->params()->fromPost('days');
                            $this->getUserParametersService()->performChangeSchedule($daysLength, $person);
                            return $this->getResponseOk("Heures enregistrées");
                        } catch (\Exception $e) {
                            return $this->getResponseInternalError("Impossible d'enregistrer les paramètres : " . $e->getMessage());
                        }
                        return $this->getResponseOk();
                    }

                    $datas = $timesheetService->getDayLengthPerson($person);
                    return $this->ajaxResponse($datas);
                    break;
                default:
                    return $this->getResponseInternalError("Action non-reconnue");
                    break;

            }
        }

        if( $method == "POST" ){
            if( !$manageHierarchie ){
                return $this->getResponseUnauthorized();
            }
            $action = $this->params()->fromPost('action', null);
            switch( $action ) {
                case 'referent' :
                    $referent_id = $this->params()->fromPost('referent_id', null);
                    $person_id = $this->params()->fromPost('person_id', null);
                    $this->getPersonService()->addReferent($referent_id, $person_id);
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
            'subordinates' => $subordinates,
            'authentification' => $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $person->getLadapLogin()]),
            'auth' => $auth,
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
