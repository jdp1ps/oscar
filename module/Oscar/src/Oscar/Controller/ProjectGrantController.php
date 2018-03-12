<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityNotification;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Notification;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Entity\TypeDocument;
use Oscar\Exception\OscarException;
use Oscar\Form\ProjectGrantForm;
use Oscar\Formatter\ActivityPaymentFormatter;
use Oscar\Provider\Privileges;
use Oscar\Service\NotificationService;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Oscar\Validator\EOTP;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controlleur pour les Activités de recherche. Le nom du controlleur est (il
 * faut bien en convenir) boiteux car il correspond à l'ancien nom de l'object
 * 'ProjectGrant'.
 *
 * @package Oscar\Controller
 */
class ProjectGrantController extends AbstractOscarController
{
    ////////////////////////////////////////////////////////////////////////////
    // ACTIONS
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Permet de lancer la numérotation automatique.
     *
     * @throws \Exception
     * @deprecated
     */
    public function numberAction()
    {
        return $this->getResponseDeprecated();
    }

    /**
     * @return \Zend\Http\Response
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $projectGrant = $this->getProjectGrantService()->getGrant($id);
        $form = new ProjectGrantForm();
        $form->setServiceLocator($this->getServiceLocator());
        $form->init();
        $form->bind($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->flush($projectGrant);
                $this->getActivityService()->searchUpdate($projectGrant);
                $this->redirect()->toRoute(
                    'contract/show',
                    ['id' => $projectGrant->getId()]
                );
            }
        }

        $view = new ViewModel([
            'form' => $form,
            'activity' => $projectGrant,
            'numbers_keys' => $keys = $this->getActivityService()->getDistinctNumbersKey()
        ]);
        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function duplicateAction()
    {
        try {
            $id = $this->params()->fromRoute('id');
            $projectGrant = $this->getProjectGrantService()->getGrant($id);
            $duplicated = $this->getActivityService()->duplicate($projectGrant);
            $this->redirect()->toRoute('contract/edit',
                ['id' => $duplicated->getId()]);

        } catch (\Exception $e) {
            die("<pre>ERROR\n : " . $e->getTraceAsString());
        }
    }

    /**
     * Création d'un nouveau projet à partir de l'activité.
     */
    public function makeProjectAction()
    {
        $activity = $this->getActivityFromRoute();

        // Contrôle des droits
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_CHANGE_PROJECT,
            $activity);

        // Création du projet
        $project = new Project();
        $this->getEntityManager()->persist($project);
        $project->setLabel($activity->getLabel())->setAcronym('');

        // Mise à jour de l'activité
        $activity->setProject($project);

        // Sauvegarde
        $this->getEntityManager()->flush();
        $this->getProjectService()->searchUpdate($project);

        // Reroutage
        $this->redirect()->toRoute('project/show', ['id' => $project->getId()]);
    }

    /**
     * @param string $fieldName
     * @return null|Activity
     */
    private function getActivityFromRoute($fieldName = 'id')
    {
        $id = $this->params()->fromRoute($fieldName);
        if (!($activity = $this->getEntityManager()->getRepository(Activity::class)->find($id))) {
            throw new OscarException(sprintf("Impossible de charger l'activité '%s'",
                $id));
        }
        return $activity;
    }

    public function generateNotificationsAction(){

        $entity = $this->getActivityFromRoute();

        $this->getOscarUserContext()->check(Privileges::ACTIVITY_NOTIFICATIONS_GENERATE, $entity);
        // $this->getM
        $this->flashMessenger()->addSuccessMessage('Les notifications ont été mises à jour');

        /** @var NotificationService $serviceNotification */
        $serviceNotification = $this->getServiceLocator()->get('NotificationService');

        $serviceNotification->generateNotificationsForActivity($entity);

        return $this->redirect()->toRoute('contract/notifications', ['id' => $entity->getId()]);
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        try {
            $projectGrant = $this->getActivityFromRoute();
            $this->getOscarUserContext()->check(Privileges::ACTIVITY_DELETE,
                $projectGrant);
            $project = $projectGrant->getProject();
            $this->getLogger()->info(sprintf('Suppression de %s - %s', $projectGrant, $projectGrant->getId()));
            $activity_id = $projectGrant->getId();
            try {
                $this->getActivityService()->searchDelete($activity_id);
            } catch ( \Exception $e ) {}
            $this->getEntityManager()->remove($projectGrant);

            $this->getEntityManager()->flush();

            if (!$project) {
                $this->redirect()->toRoute('contract/advancedsearch');
            } else {
                $this->getEntityManager()->refresh($project);

                $this->redirect()->toRoute('project/show',
                    ['id' => $projectGrant->getProject()->getId()]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function csvPaymentsAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $organizations = null;

        // Récupération des Id des activités
        if ($request->isPost()) {
            $paramID = $this->params()->fromPost('ids', '');
        } else {
            $paramID = $this->params()->fromQuery('ids', '');
        }


        if (!$paramID) {
            return $this->getResponseBadRequest();
        }

        if (!$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {
            // Croisement
            $this->organizationsPerimeter = $this->getOscarUserContext()->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
                true);
            if ($this->getOrganizationPerimeter()) {
                $organizations = $this->getOrganizationPerimeter();
            } else {
                throw new UnAuthorizedException('Droits insuffisants');
            }
        }

        $ids = explode(',', $paramID);

        $payments = $this->getProjectGrantService()->getPaymentsByActivityId($ids,
            $organizations);
        $formatter = new ActivityPaymentFormatter();
        $csv = [];

        // Fichier temporaire
        $filename = uniqid('oscar_export_activities_payment_') . '.csv';
        $handler = fopen('/tmp/' . $filename, 'w');

        fputcsv($handler, $formatter->csvHeaders());

        /** @var ActivityPayment $payment */
        foreach ($payments as $payment) {
            fputcsv($handler, $formatter->format($payment));
        }

        fclose($handler);

        header('Content-Disposition: attachment; filename=oscar-export-versements.csv');
        header('Content-Length: ' . filesize('/tmp/' . $filename));
        header('Content-type: plain/text');

        die(file_get_contents('/tmp/' . $filename));
    }

    /** Export les données en CSV. */
    public function csvAction()
    {

        /** @var Request $request */
        $request = $this->getRequest();


        $perimeter = $this->params()->fromQuery('perimeter', '');
        $fields = $this->params()->fromPost('fields', null);


        $qb = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a');

        $parameters = [];

        if ($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT)) {

        } else {
            $this->organizationsPerimeter = $this->getOscarUserContext()
                ->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
                true);

            $qb->leftJoin('a.project', 'pr')
                ->leftJoin('pr.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->where('o1.organization IN(:perimeter) OR o2.organization IN(:perimeter)');

            $parameters = [
                'perimeter' => $this->organizationsPerimeter
            ];
        }

        // NOUVELLE VERSION
        if ($request->isPost()) {
            $paramID = $this->params()->fromPost('ids', '');
        } else {
            $paramID = $this->params()->fromQuery('ids', '');
        }

        if ($paramID) {
            $ids = explode(',', $paramID);
            $qb->andWhere('a.id IN (:ids)');
            $parameters['ids'] = $ids;
        }

        $entities = $qb->getQuery()->setParameters($parameters)->getResult();

        if (!count($entities)) {
            return $this->getResponseBadRequest("Aucun résultat à exporter");
        }

        $keep = true;
        if( $fields ){
            $keep = explode(',', $fields);
        }

        $columns = [];

        // Fichier temporaire
        $csv = uniqid('oscar_export_activities_') . '.csv';
        $handler = fopen('/tmp/' . $csv, 'w');
        $headers = [];

        foreach(Activity::csvHeaders() as $header){
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
        }



        $rolesOrganizationsQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('r.label')
            ->from(OrganizationRole::class, 'r')
            ->getQuery()
            ->getResult();
        $rolesOrganisations = [];

        foreach( $rolesOrganizationsQuery as $role ){
            $header = $role['label'];
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesOrganisations[$header] = [];
        }


        $rolesOrga = $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray();
        $rolesPersons = [];

        foreach( $rolesOrga as $role ){
            $header = $role;
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $rolesPersons[$role] = [];
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // --- JALONS
        // Récupération des différents types de jalons
        $jalonsQuery = $this->getEntityManager()->getRepository(DateType::class)->findAll();
        $jalons = [];

        /** @var DateType $jalon */
        foreach ($jalonsQuery as $jalon) {
            $jalons[$jalon->getLabel()] = [];

            $header = $jalon->getLabel();
            if( $keep === true || in_array($header, $keep) ){
                $columns[$header] = true;
                $headers[] = $header;
            } else {
                $columns[$header] = false;
            }
            $jalons[$header] = [];
        }



        fputcsv($handler, $headers);
        /** @var Activity $entity */
        foreach ($entities as $entity) {
            $datas = [];
            $rolesCurrent = $rolesOrganisations;
            $rolesPersonsCurrent = $rolesPersons;
            $jalonsCurrent = $jalons;

            if ($this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EXPORT,
                $entity)
            ) {
                foreach( $entity->getOrganizationsDeep() as $org ){
                     $rolesCurrent[$org->getRole()][] = (string)$org->getOrganization();
                }

                foreach( $entity->getPersonsDeep() as $per ){
                     $rolesPersonsCurrent[$per->getRole()][] = (string)$per->getPerson();
                }
                /** @var ActivityDate $mil */
                foreach( $entity->getMilestones() as $mil ){

                    $jalonsCurrent[$mil->getType()->getLabel()][] = $mil->getDateStart() ?
                        $mil->getDateStart()->format('Y-m-d') :
                        'nop';
                }



                foreach ( $entity->csv() as $col=>$value ){
                    if( $columns[$col] === true )
                        $datas[] = $value;
                }


                foreach( $rolesCurrent as $role=>$organisations ){
                    if( $columns[$role] === true )
                        $datas[] = $organisations ? implode('|', array_unique($organisations)) : ' ';
                }

                foreach( $rolesPersonsCurrent as $role=>$persons ){
                    if( $columns[$role] === true )
                        $datas[] = $persons ? implode('|', array_unique($persons)) : ' ';
                }


                foreach( $jalonsCurrent as $jalon2=>$date ){
                    if( $columns[$jalon2] === true )
                        $datas[] = $date ? implode('|', array_unique($date)) : ' ';
                }
                fputcsv($handler, $datas);
            }
        }
        fclose($handler);
        header('Content-Disposition: attachment; filename=oscar-export.csv');
        header('Content-Length: ' . filesize('/tmp/' . $csv));
        header('Content-type: text/csv');

        die(file_get_contents('/tmp/' . $csv));
    }

    /**
     * Nouvelle activité de recherche.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        // Récupération du projet (si précisé)
        $projectId = $this->params()->fromRoute('projectid', null);

        // Contrôle des droits
        if ($projectId) {
            $project = $this->getProjectService()->getProject($projectId);
            $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_EDIT,
                $project);
        } else {
            $project = null;
            $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_EDIT);
        }

        $projectGrant = new Activity();
        $projectGrant->setProject($project);
        $form = new ProjectGrantForm();
        $form->setServiceLocator($this->getServiceLocator());
        $form->init();
        $form->setObject($projectGrant);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->getHydrator()->hydrate($request->getPost()->toArray(),
                $projectGrant);

            if ($form->isValid()) {
                if ($projectGrant->getId()) {
                    $projectGrant->setDateUpdated(new \DateTime());
                }
                $this->getEntityManager()->persist($projectGrant);
                if ($project) {
                    $project->touch();
                }
                $this->getEntityManager()->flush($projectGrant);

                $this->redirect()->toRoute('contract/show',
                    ['id' => $projectGrant->getId()]);
            }
        }

        $view = new ViewModel([
            'form' => $form,
            'activity' => $projectGrant,
            'project' => $project,
        ]);

        $view->setTemplate('oscar/project-grant/form');

        return $view;
    }

    /**
     * Fiche pour une activité de recherche.
     */
    public function documentsJsonAction()
    {
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_DOCUMENT_SHOW,
            $entity);
        $deletable = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $uploadable = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_DOCUMENT_MANAGE);
        $personShow = $this->getOscarUserContext()->hasPrivileges(Privileges::PERSON_SHOW);

        $out = [];
        /** @var ContractDocument $doc */
        foreach ($entity->getDocuments() as $doc) {
            $docDt = $doc->toJson([
                'urlDelete' => $deletable ?
                    $this->url()->fromRoute('contractdocument/delete',['id' => $doc->getId()])
                    : false,
                'urlDownload' => $this->url()->fromRoute('contractdocument/download', ['id' => $doc->getId()]),
                'urlReupload' => $this->url()->fromRoute('contractdocument/upload',
                        ['idactivity' => $entity->getId()]) . "?id=" . $doc->getId(),
                'urlPerson' => $personShow && $doc->getPerson() ? $this->url()->fromRoute('person/show',
                    ['id' => $doc->getPerson()->getId()]) : false,
            ]);
            $out[] = $docDt;
        }

        return new JsonModel($out);

    }

    /*
    protected function getActivityFromRoute( $field = 'id' ){

    }
    /****/

    public function notificationsAction(){

        /** @var Activity $entity */
        $entity = $this->getActivityFromRoute();

        // Check access
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_MENU_ADMIN);

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        $notificationJson = [];
        foreach ($notificationService->notificationsActivity($entity) as $n) {
            $notification = $n->toArray();
            $notification['persons'] = [];
            foreach ($n->getPersons() as $personNotification) {
                $notification['persons'][] = [
                    'person' => (string)$personNotification->getPerson(),
                    'read' => $personNotification->getRead() ? $personNotification->getRead()->format('Y-m-d') : false,
                ];
            }
            $notificationJson[] = $notification;
        }

        return [
            'activity' => $entity,
            'notifications' => $notificationJson
        ];
    }

    public function show2Action(){
        $method = $this->getHttpXMethod();


        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_SHOW, $entity);

        switch ($method) {
            case 'GET' :
                if( $this->isAjax() )
                    return $this->getResponseOk('RETOUR AJAX');
                else
                    return [
                        'activity' => $entity,
                        'json' => $this->getActivityService()->getActivityJson($id, $this->getOscarUserContext())
                    ];
                break;
            default :
                return $this->getResponseBadRequest('Bad Method ' . $method);
        }
    }

    /**
     * Fiche pour une activité de recherche.
     */
    public function showAction()
    {
        // Identifiant de l'activité
        $id = $this->params()->fromRoute('id');

        /** @var Activity $entity */
        $entity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

        // Check access
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_SHOW, $entity);


        //////////////////////////////////////////////////////////// Passage WTF
        // Si l'on supprime le bloc suivant, l'affichage des partenaires
        // part en sucette sur certaines activités... on en est là...
        /** @var ActivityOrganization $o */
        foreach ($entity->getOrganizations() as $o) {
            // $orgas[] = $o->getOrganization()->displayName();
        }
        ////////////////////////////////////////////////////////////////////////


        $activityTypeChain = $this->getActivityTypeService()->getActivityTypeChain($entity->getActivityType());

        $documentTypes = [];

        /** @var TypeDocument $type */
        foreach ($this->getEntityManager()->getRepository(TypeDocument::class)->findAll() as $type) {
            $documentTypes[$type->getId()] = $type->getLabel();
        }

        $activity = $this->getProjectGrantService()->getGrant($id);

        $involvedPersons = null; $involvedPersonsJSON = null;
        if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PERSON_ACCESS, $activity) ){
            $involved = $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_SHOW, $activity, true);
            foreach ($involved as $p){
                $involvedPersons[] = $p->toJson();
            }
            $involvedPersonsJSON = json_encode($involvedPersons);
        }

        $currencies = [];
        /** @var Currency $currency */
        foreach( $this->getEntityManager()->getRepository(Currency::class)->findAll() as $currency ){
            $currencies[] = $currency->asArray();
        }

        return [
            'entity' => $activity,

            'currencies' => $currencies,

            // Jeton de sécurité
            'tokenValue' => $this->getOscarUserContext()->getTokenValue(true),

            // Personnes pouvant voir cette activité
            'involvedPerson' => $involvedPersonsJSON,

            // Notifications précalculées
            'notifications' => $this->getEntityManager()->getRepository(Notification::class)
                ->findBy(['object' => Notification::OBJECT_ACTIVITY, 'objectId' => $activity->getId()]),


            'documentTypes' => json_encode($documentTypes),
            'activityTypeChain' => $activityTypeChain,
            'traces' => $this->getActivityLogService()->activityActivities($id)->getQuery()->getResult(),
        ];
    }

    /**
     * Procédure pour modifier le projet d'un activité de recherche.
     */
    public function changeProjectAction()
    {
        /** @var Activity $entity */
        $entity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));

        if ($entity) {
            /** @var \Zend\Http\Request $request */
            $request = $this->getRequest();
            if ($request->isPost()) {
                $project = $this->getProjectService()->getProject($request->getPost('project_id'));
                if (!$project) {
                    throw new \Exception('Aucun projet ne correspond');
                }
                if ($entity->getProject()) {
                    $entity->getProject()->touch();
                }
                $entity->setProject($project);
                $entity->touch();
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('contract/show',
                    ['id' => $entity->getId()]);
            }
            $view = new ViewModel(['activity' => $entity]);
            $view->setTemplate('/oscar/project/project-selector.phtml');

            return $view;
        } else {
            throw new \Exception(sprintf("L'activité n'existe pas"));
        }
    }

    /**
     * Expérimentation pour afficher l'activité sous une forme plus graphique.
     */
    public function visualizationAction()
    {
        return [
            'entity' => $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'))
        ];
    }

    /**
     * Expérimentation pour afficher l'activité sous une forme plus graphique.
     */
    public function personsAction()
    {

        $activity = $this->getProjectGrantService()->getGrant($this->params()->fromRoute('id'));
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PERSON_SHOW,
            $activity);
        $out = [];

        $editableA = $deletableA = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PERSON_MANAGE,
            $activity);
        $editableP = $deletableP = $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_PERSON_MANAGE,
            $activity->getProject());

        /**
         * @var ActivityPerson $activityPerson
         */
        foreach ($activity->getPersonsDeep() as $activityPerson) {


            if (get_class($activityPerson) == ActivityPerson::class) {
                $urlDelete = $deletableA ? $this->url()->fromRoute('personactivity/delete',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute('personactivity/edit',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
            } else {
                $urlDelete = $deletableA ? $this->url()->fromRoute('personproject/delete',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $urlEdit = $editableA ? $this->url()->fromRoute('personproject/edit',
                    ['idenroll' => $activityPerson->getId()]) : false;
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
            }

            $out[] = [
                'id' => $activityPerson->getId(),
                'role' => $activityPerson->getRole(),
                'roleLabel' => $activityPerson->getRole(),
                'rolePrincipal' => $activityPerson->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'enroller' => $activity->getId(),
                'enrollerLabel' => $activity->getLabel(),
                'editable' => $editable,
                'deletable' => $deletable,
                'enrolled' => $activityPerson->getPerson()->getId(),
                'enrolledLabel' => $activityPerson->getPerson()->getDisplayName(),
                'start' => $activityPerson->getDateStart(),
                'end' => $activityPerson->getDateEnd()
            ];
        }

        echo json_encode($out);
        die();
    }

    public function organizationsAction()
    {

        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('id'));
        $this->getOscarUserContext()->check(Privileges::ACTIVITY_PERSON_SHOW,
            $activity);
        $out = [];

        $editableA = $deletableA = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_ORGANIZATION_MANAGE,
            $activity);
        $editableP = $deletableP = $this->getOscarUserContext()->hasPrivileges(Privileges::PROJECT_ORGANIZATION_MANAGE,
            $activity->getProject());

        $classRoutes = [
            ActivityOrganization::class => 'organizationactivity',
            ActivityPerson::class => 'personactivity',
            ProjectMember::class => 'personproject',
            ProjectPartner::class => 'organizationproject'
        ];

        /**
         * @var ActivityOrganization $activityOrganization
         */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {

            $class = get_class($activityOrganization);

            if ($class == ActivityOrganization::class || get_class($activityOrganization) == $class) {
                $editable = $editableA;
                $deletable = $deletableA;
                $context = "activity";
            } else {
                $editable = $editableP;
                $deletable = $deletableP;
                $context = "project";
            }

            $urlDelete = $deletableA ? $this->url()->fromRoute($classRoutes[$class] . '/delete',
                ['idenroll' => $activityOrganization->getId()]) : false;
            $urlEdit = $editableA ? $this->url()->fromRoute($classRoutes[$class] . '/edit',
                ['idenroll' => $activityOrganization->getId()]) : false;

            $out[] = [
                'id' => $activityOrganization->getId(),
                'role' => $activityOrganization->getRole(),
                'roleLabel' => $activityOrganization->getRole(),
                'rolePrincipal' => $activityOrganization->isPrincipal(),
                'urlDelete' => $urlDelete,
                'context' => $context,
                'urlEdit' => $urlEdit,
                'enroller' => $activity->getId(),
                'enrollerLabel' => (string)$activity,
                'editable' => $editable,
                'deletable' => $deletable,
//                'hash' => (string)$activityOrganization,
                'enrolled' => $activityOrganization->getOrganization()->getId(),
                'enrolledLabel' => $activityOrganization->getOrganization()->getFullName(),
                'start' => $activityOrganization->getDateStart(),
                'end' => $activityOrganization->getDateEnd()
            ];
        }

        echo json_encode($out);
        die();
    }

    /**
     * Retourne la liste des activités de recherche sans projets.
     */
    public function orphansAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->where('c.project IS NULL')
            ->orderBy('c.dateCreated', 'DESC');


        $persons = $this->filterPersons($qb);

        return [
            'contracts' => $paginator = new UnicaenDoctrinePaginator($qb, $page,
                20),
            'persons' => $persons
        ];
    }

    /**
     * @deprecated
     */
    protected function filterPersons(&$qb)
    {
        // Persons
        $persons = [];
        $filterPersons = $this->params()->fromQuery('persons', []);
        $page = $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');

        if (count($filterPersons)) {
            foreach ($this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')->where('p.id IN (:persons)')->setParameter('persons',
                $filterPersons)->getQuery()->getResult() as $p) {
                $persons[] = $p;
            }
            $qb->innerJoin('c.persons', 'm')
                ->leftJoin('m.person', 'p')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'pm')
                ->leftJoin('pm.person', 'p2')
                ->andWhere('p.id in (:personIds) OR p2.id IN (:personIds)')
                ->setParameter('personIds', $filterPersons);
        }

        return $persons;
    }


    private $organizationsPerimeter;

    private function getOrganizationPerimeter()
    {
        return $this->organizationsPerimeter;
    }

    public function activitiesOrganizationsAction()
    {
        $this->organizationsPerimeter = $this->getOscarUserContext()->getOrganisationsPersonPrincipal($this->getOscarUserContext()->getCurrentPerson(),
            true);

        return $this->advancedSearchAction();
    }

    /**
     * Nouveau système de recherche des activités.
     *
     * @return array
     */
    public function applyAdvancedSearch($qb)
    {
        try {
            $page = $this->params()->fromQuery('page', 1);
            $search = $this->params()->fromQuery('q', null);
            $include = null;
            $error = "";

            if( $search === null ){
                $startEmpty = true;
            } else {
                $startEmpty = false;
            }

            if ($this->getOrganizationPerimeter()) {

                $include = $this->params()->fromQuery('include', null);
                if ($include) {
                    foreach ($include as $index => $value) {
                        $include[$index] = intval($value);
                    }
                    $include = array_intersect($include,
                        $this->getOrganizationPerimeter());
                } else {
                    $include = $this->getOrganizationPerimeter();
                }
            }

            // Type de recherche supportée
            $filtersType = [
                'ap' => "Impliquant la personne",
                'sp' => "N'impliquant pas la personne",
                'pm' => "Impliquant une de ces personnes",

                'ao' => "Impliquant l'organisation",
                'so' => "N'impliquant pas l'organisation",

                'as' => 'Ayant le statut',
                'ss' => 'N\'ayant pas le statut',

                'cnt' => "Pays (d'une organisation)",

                'af' => 'Ayant comme incidence financière',
                'sf' => 'N\'ayant pas comme incidence financière',

                'mp' => 'Montant prévu',

                'at' => 'est de type',
                'st' => 'n\'est pas de type',

                'add' => 'Date de début',
                'adf' => 'Date de fin',
                'adc' => 'Date de création',
                'adm' => 'Date de dernière mise à jour',
                'ads' => 'Date de signature',
                'adp' => 'Date d\'ouverture du PFI dans SIFAC',

                'pp' => 'Activités sans projet',
            ];

            // Correspondance des champs de type date
            $dateFields = [
                'add' => 'dateStart',
                'adc' => 'dateCreated',
                'adf' => 'dateEnd',
                'adm' => 'dateUpdated',
                'ads' => 'dateSigned',
                'adp' => 'dateOpened',
            ];

            // Critères de trie
            $sortCriteria = [
                'dateCreated' => 'Date de création',
                'dateStart' => 'Date début',
                'dateEnd' => 'Date fin',
                'dateUpdated' => 'Date de mise à jour',
                'dateSigned' => 'Date de signature',
                'dateOpened' => "Date d'ouverture du PFI dans SIFAC",
            ];

            // Trie
            $sortDirections = [
                'desc' => 'Décroissant',
                'asc' => 'Croissant'
            ];

            $sort = $this->params()->fromQuery('sort', 'dateUpdated');
            $sortIgnoreNull = $this->params()->fromQuery('sortIgnoreNull', null);
            $sortDirection = $this->params()->fromQuery('sortDirection',
                'desc');

            // Récupération des critères GET
            $criteria = $this->params()->fromQuery('criteria', []);

            // Critères rangés (pour les réafficher)
            $criterias = [];

            $personsId = [];
            $organizationId = [];

            // Liste des IDS à prendre en compte dans le requète finale
            $ids = null;

            // Liste des IDS à exclure de la requète final
            $notIds = null;

            // Variables temporaires pour stoquer les ids
            $filterIds = null;
            $filterNotIds = [];
            $filterStatus = [];
            $filterNoStatus = [];
            $filterPersons = [];

            $organizations = [];
            $persons = [];


            $queryPersons = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('m1.person in(:ids) OR m2.person in (:ids)');

            // QueryBuilder utilisés pour récupérer les IDS des activités pour
            // les filtres de personne avec ou sans rôle, idem pour les
            // organisations.
            $queryPersonNoRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('(m1.person = :id OR m2.person = :id)');

            $queryPersonRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('((m1.person = :id AND m1.roleObj = :roleObj) OR (m2.person = :id AND m2.roleObj = :roleObj))');

            $queryOrganisationNoRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('(o1.organization = :id OR o2.organization = :id)');

            $queryOrganisationRole = $this->getEntityManager()->createQueryBuilder()
                ->select('a.id')
                ->from(Activity::class, 'a', 'a.id')
                ->leftJoin('a.project', 'p')
                ->leftJoin('p.partners', 'o1')
                ->leftJoin('a.organizations', 'o2')
                ->leftJoin('p.members', 'm1')
                ->leftJoin('a.persons', 'm2')
                ->where('((o1.organization = :id AND o1.roleObj = :roleObj) OR (o2.organization = :id AND o2.roleObj = :roleObj))');

            // Paramètres de la requête finale
            $parameters = [];


            if (!$search && count($criteria) === 0) {
                $ids = [];
                // Requêtes de base
                /** @var QueryBuilder $qb */
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->select('c, m1, p1, pr, m2, p2, d1, t1, orga1, orga2, pers1, pers2, dis')
                    ->from(Activity::class, 'c')
                    ->leftJoin('c.persons', 'm1')
                    ->leftJoin('m1.person', 'pers1')
                    ->leftJoin('c.disciplines', 'dis')
                    ->leftJoin('c.activityType', 't1')
                    ->leftJoin('c.organizations', 'p1')
                    ->leftJoin('p1.organization', 'orga1')
                    ->leftJoin('c.documents', 'd1')
                    ->leftJoin('c.project', 'pr')
                    ->leftJoin('pr.members', 'm2')
                    ->leftJoin('pr.partners', 'p2')
                    ->leftJoin('m2.person', 'pers2')
                    ->leftJoin('p2.organization', 'orga2')
                ;

                if ($include) {
                    $organizationsPerimeterIds = implode(',', $include);

                    $qb->andWhere('p1.organization IN('
                            . $organizationsPerimeterIds
                            . ') OR p2.organization IN('
                            . $organizationsPerimeterIds
                            . ')');
                }
                /*
                else {
                    $startEmpty = true;
                }
                /****/

            } else {

                // Traitement : Champ de recherche libre

                if ($search) {
                    // La saisie est un PFI
                    if (preg_match($this->getServiceLocator()->get("Config")['oscar']['validation']['pfi'], $search)) {
                        $parameters['search'] = $search;
                        $qb->andWhere('c.codeEOTP = :search');
                    } elseif (preg_match('/(.*)=(.*)/', $search, $result)) {
                        $key = $result[1];
                        $value = $result[2];
                        $qb->andWhere('c.numbers LIKE :numbersearch');
                        $parameters['numbersearch'] = '%"' . $key . '";s:%:"' . $value . '";%';
                    } else {
                        // La saisie est un numéro SAIC
                        if (preg_match("/^[0-9]{4}SAIC.*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.centaureNumConvention LIKE :search');
                        } // La saisie est un numéro OSCAR©
                        elseif (preg_match("/^[0-9]{4}DRI.*/mi", $search)) {
                            $parameters['search'] = $search . '%';
                            $qb->andWhere('c.oscarNum LIKE :search');
                        } // Saisie 'libre'
                        else {
                            try {
                                $filterIds = $this->getActivityService()->search($search);
                            } catch (\Zend_Search_Lucene_Exception $e ){
                                if( stripos($e->getMessage(), 'non-wildcard') > 0 ){
                                    $error = "Les motifs de recherche doivent commencer par au moins 3 caractères non-wildcard.";
                                } else {
                                    $error = "Motif de recherche incorrecte : " . $e->getMessage();
                                }
                                $filterIds = [];

                            }
                        }
                    }
                }

                // Analyse des critères de recherche
                foreach ($criteria as $c) {

                    // Découpage et récupération des critères de filtre
                    $params = explode(';', $c);
                    $type = $params[0];

                    $crit = [
                        'type' => $type
                    ];

                    $value1 = intval($params[1]);
                    $value2 = intval($params[2]);

                    $crit['val1'] = $value1;
                    $crit['val2'] = $value2;

                    $queryParam = [
                        'id' => $value1
                    ];

                    $filterKey = uniqid('filter_');

                    switch ($type) {
                        case 'vp':
                            $qb->addSelect('c.payments', 'p');
                            break;
                        case 'mp':
                            $clause = [];

                            if ($value1) {
                                $clause[] = 'c.amount >= :amountMin';
                                $parameters['amountMin'] = $value1;
                            }
                            if ($value2) {
                                $clause[] = 'c.amount <= :amountMax';
                                $parameters['amountMax'] = $value2;
                            }

                            if (!$value1 && !$value2) {
                                $crit['error'] = 'Plage numérique farfelue...';
                            } else {
                                $qb->andWhere(implode(' AND ', $clause));
                            }
                            break;
                        case 'pp' :
                            $qb->andWhere('c.project IS NULL');
                            break;
                        // Personne
                        case 'pm' :
                            $value1 = explode(',', $params[1]);
                            $crit['val1'] = $value1;
                            $personsQuery = $this->getEntityManager()->createQueryBuilder()
                                ->select('pr')
                                ->from(Person::class, 'pr')
                                ->where('pr.id IN(:idsPersons)');

                            foreach ($personsQuery->getQuery()->setParameter('idsPersons',
                                $value1)->getResult() as $person) {
                                $filterPersons[$person->getId()] = (string)$person;
                            }

                            $ids = array_keys($queryPersons->setParameter('ids',
                                $value1)->getQuery()->getArrayResult());
                            break;
                        case 'ap' :
                        case 'sp' :
                            try {
                                $personsId[] = $value1;
                                $person = $this->getPersonService()->getPerson($value1);
                                $persons[$person->getId()] = $person;
                                $crit['val1Label'] = $person->getDisplayName();
                                $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContext()->getAllRoleIdPerson()[$value2] : '';
                                $query = $queryPersonNoRole;
                                if ($value2 >= 0) {
                                    $queryParam['roleObj'] = $this->getEntityManager()->getRepository(Role::class)->find($value2);
                                    $query = $queryPersonRole;
                                }
                                $ids = array_keys($query->setParameters($queryParam)->getQuery()->getArrayResult());
                            } catch (\Exception $e) {
                                $crit['error'] = "Impossible de filtrer sur la personne";
                            }
                            break;

                        case 'ao' :
                        case 'so' :
                            $organizationId[] = $value1;
                            try {
                                $organization = $this->getOrganizationService()->getOrganization($value1);
                                $organizations[$organization->getId()] = $organization;
                                $crit['val1Label'] = (string)$organization;
                                $crit['val2Label'] = $value2 >= 0 ? $this->getOscarUserContext()->getRolesOrganizationInActivity()[$value2] : '';
                                if ($value2 > 0) {
                                    $roleOrganisation = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($value2);
                                    $queryParam['roleObj'] = $roleOrganisation;
                                    $query = $queryOrganisationRole;
                                } else {
                                    $query = $queryOrganisationNoRole;
                                }
                                $ids = array_keys($query->setParameters($queryParam)->getQuery()->getArrayResult());

                            } catch (\Exception $e) {
                                $crit['error'] = "Impossible de filtrer sur l'organisation (" . $e->getMessage() . ")";
                            }
                            break;

                        // Filtre sur le statut de l'activité
                        case 'as' :
                            if (!isset($parameters['withstatus'])) {
                                $parameters['withstatus'] = [];
                            }
                            $parameters['withstatus'][] = $value1;
                            $qb->andWhere('c.status IN (:withstatus)');
                            break;
                        case 'ss' :
                            if (!isset($parameters['withoutstatus'])) {
                                $parameters['withoutstatus'] = [];
                            }
                            $parameters['withoutstatus'][] = $value1;
                            $qb->andWhere('c.status NOT IN (:withoutstatus)');
                            break;

                        // Filtre sur le type de l'activité
                        case 'at' :
                            if (!isset($parameters['withtype'])) {
                                $parameters['withtype'] = [];
                                $qb->andWhere('c.activityType IN (:withtype)');
                            }
                            $parameters['withtype'] = array_merge($parameters['withtype'],
                                $this->getActivityTypeService()->getTypeIdsInside($value1));
                            break;
                        case 'st' :
                            if (!isset($parameters['withouttype'])) {
                                $parameters['withouttype'] = [];
                                $qb->andWhere('c.activityType NOT IN (:withouttype)');
                            }
                            $parameters['withouttype'] = array_merge($parameters['withouttype'],
                                $this->getActivityTypeService()->getTypeIdsInside($value1));
                            break;

                        // Filtre sur la/les incidences financière
                        case 'af' :
                            if (!isset($parameters['withfinancial'])) {
                                $parameters['withfinancial'] = [];
                                $qb->andWhere('c.financialImpact IN (:withfinancial)');
                            }
                            $parameters['withfinancial'][] = Activity::getFinancialImpactValues()[$value1];
                            break;

                        case 'sf' :
                            if (!isset($parameters['withoutfinancial'])) {
                                $parameters['withoutfinancial'] = [];
                                $qb->andWhere('c.financialImpact NOT IN (:withoutfinancial)');
                            }
                            $parameters['withoutfinancial'][] = Activity::getFinancialImpactValues()[$value1];
                            break;
                        case 'cnt' :
                            if( $params[1] ){
                                if( !isset($parameters['countries']) ){
                                    $parameters['countries'] = [];
                                }
                                $value1 = $crit['val1'] = explode(',', $params[1]);
                                $qb->andWhere('orga1.country IN (:countries) OR orga2.country IN (:countries)');
                                $parameters['countries'] = $value1;
                            }
                            break;
                        case 'add' :
                        case 'adf' :
                        case 'adm' :
                        case 'adc' :
                        case 'ads' :
                        case 'adp' :
                            $field = $dateFields[$type];

                            $start = DateTimeUtils::toDatetime($params[1]);
                            $end = DateTimeUtils::toDatetime($params[2]);
                            $value1 = $start ? $start->format('Y-m-d') : '';
                            $value2 = $end ? $end->format('Y-m-d') : '';
                            $crit['val1'] = $value1;
                            $crit['val2'] = $value2;
                            $clause = [];

                            if ($value1) {
                                $clause[] = 'c.' . $field . ' >= :' . $filterKey . 'start';
                                $parameters[$filterKey . 'start'] = $value1;
                            }
                            if ($value2) {
                                $clause[] = 'c.' . $field . ' <= :' . $filterKey . 'end';
                                $parameters[$filterKey . 'end'] = $value2;
                            }

                            if ($clause) {
                                $qb->andWhere(implode(' AND ', $clause));
                            } else {
                                $crit['error'] = 'Plage de date invalide';
                            }

                            break;

                    }

                    $criterias[] = $crit;


                    if ($type == 'ap' || $type == 'ao' || $type == 'pm' ) {

                        if ($filterIds === null) {
                            $filterIds = $ids;
                        } else {
                            $filterIds = array_intersect($filterIds, $ids);
                        }
                    }

                    if ($type == "sp" || $type == 'so') {
                        $filterNotIds = array_merge($filterNotIds, $ids);
                    }
                }

                if ($filterNotIds) {
                    $qb->andWhere('c.id NOT IN(:not)');
                    $parameters['not'] = $filterNotIds;
                }

                if ($filterIds !== null) {
                    $qb->andWhere('c.id IN(:ids)');
                    $parameters['ids'] = $filterIds;
                }

                $qb->setParameters($parameters);


                // FILTRE STATIC SUR LES ORGA
                if ($this->getOrganizationPerimeter()) {

                    $organizationsPerimeterIds = implode(',', $include);

                    $qb->andWhere('p1.organization IN('
                        . $organizationsPerimeterIds
                        . ') OR p2.organization IN('
                        . $organizationsPerimeterIds
                        . ')');
                }
            }

            $projectview = $this->params()->fromQuery('projectview', '');

            $activities = null;
            if( $startEmpty === false ) {
                $qbIds = $qb->select('DISTINCT c.id');


                $ids = [];
                foreach ($qbIds->getQuery()->getResult() as $row) {
                    $ids[] = $row['id'];
                }
                if ( $projectview == 'on' )
                    $qb = $this->getEntityManager()
                        ->getRepository(Project::class)
                        ->createQueryBuilder('pr')
                        ->innerJoin('pr.grants', 'c')
                        ->where('c.id IN (:ids)')
                        ->setParameter('ids', $ids);

                else
                    $qb->select('c');

                $qb->orderBy('c.' . $sort, $sortDirection);
                if( $sortIgnoreNull ){
                    $qb->andWhere('c.' . $sort . ' IS NOT NULL');
                }
                $activities = new UnicaenDoctrinePaginator($qb, $page);
            }


            $view = new ViewModel([
                'projectview' => $projectview,
                'exportIds' => implode(',', $ids),
                'filtersType' => $filtersType,
                'error' => $error,
                'criteria' => $criterias,
                'countries' => $this->getOrganizationService()->getCountriesList(),
                'fieldsCSV' => $this->getActivityService()->getFieldsCSV(),
                'persons' => $persons,
                'activities' => $activities,
                'search' => $search,
                'filterPersons' => $filterPersons,
                'include' => $include,
                'organizationsPerimeter' => $this->getOrganizationPerimeter(),
                'sort' => $sort,
                'sortCriteria' => $sortCriteria,
                'sortDirection' => $sortDirection,
                'sortIgnoreNull' => $sortIgnoreNull,
                'types' => $this->getActivityTypeService()->getActivityTypes(true),
            ]);


            $view->setTemplate('oscar/project-grant/advanced-search.phtml');

            return $view;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Nouveau système de recherche des activités.
     *
     * @return array
     */
    public function advancedSearchAction()
    {

        // Requêtes de base
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c, m1, p1, pr, m2, p2, d1, t1, orga1, orga2, pers1, pers2, dis')
            ->from(Activity::class, 'c')
            ->leftJoin('c.persons', 'm1')
            ->leftJoin('m1.person', 'pers1')
            ->leftJoin('c.disciplines', 'dis')
            ->leftJoin('c.activityType', 't1')
            ->leftJoin('c.organizations', 'p1')
            ->leftJoin('p1.organization', 'orga1')
            ->leftJoin('c.documents', 'd1')
            ->leftJoin('c.project', 'pr')
            ->leftJoin('pr.members', 'm2')
            ->leftJoin('pr.partners', 'p2')
            ->leftJoin('m2.person', 'pers2')
            ->leftJoin('p2.organization', 'orga2');

        return $this->applyAdvancedSearch($qb);
    }

    /**
     * Liste des activités de recherche.
     */
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $search = $this->params()->fromQuery('q', '');
        $filterStatus = $this->params()->fromQuery('filter_status', []);
        $filterType = $this->params()->fromQuery('filter_type', []);
        $filterYears = $this->params()->fromQuery('filter_year', []);
        $filterPersons = $this->params()->fromQuery('persons', []);

        $datefilter_type = $this->params()->fromQuery('datefilter_type', '');
        $datefilter_from = $this->params()->fromQuery('datefilter_from', '');
        $datefilter_to = $this->params()->fromQuery('datefilter_to', '');


        $sortCriteria = [
            'dateCreated' => 'Date de création',
            'dateStart' => 'Date début',
            'dateEnd' => 'Date fin',
            'dateUpdated' => 'Date de mise à jour',
            'dateSigned' => 'Date de signature',
            'dateOpened' => "Date d'ouverture",
        ];

        $sortDirections = [
            'desc' => 'Décroissant',
            'asc' => 'Croissant'
        ];

        $sort = $this->params()->fromQuery('sort', 'dateUpdated');
        $sortDirection = $this->params()->fromQuery('sortDirection', 'desc');

        if (!key_exists($sort, $sortCriteria)) {
            $sort = 'dateCreated';
        }
        if (!key_exists($datefilter_type, $sortCriteria)) {
            $datefilter_type = '';
        }

        if (!key_exists($sortDirection, $sortDirections)) {
            $sortDirection = 'desc';
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->orderBy('c.' . $sort, $sortDirection);

        $displayFilters = false;

        if ($search) {
            $displayFilters = true;
            if (preg_match(EOTP::REGEX_EOTP, $search)) {
                $qb->where('c.codeEOTP = :search')
                    ->setParameter('search', $search);
            } else {
                if (preg_match("/^[0-9]{4}SAIC.*/mi", $search)) {
                    $qb->where('c.centaureNumConvention LIKE :search')
                        ->setParameter('search', $search . '%');
                } elseif (preg_match("/^[0-9]{4}DRI.*/mi", $search)) {
                    $qb->where('c.oscarNum LIKE :search')
                        ->setParameter('search', $search . '%');
                } else {

                    $qb->where('c.id IN(:ids)')
                        ->setParameter('ids',
                            $this->getActivityService()->search($search));
                }
            }
        }

        // Filtre sur le status de l'activités
        if (count($filterStatus)) {
            $displayFilters = true;
            $qb->andWhere('c.status IN(:status)')
                ->setParameter('status', $filterStatus);
        }

        // Filtre sur les types d'activités
        if (count($filterType)) {
            $displayFilters = true;
            $selectedTypes = $this->getActivityTypeService()->getActivityTypesById($filterType);
            $qb->innerJoin('c.activityType', 't');
            /** @var ActivityType $selectedType */
            foreach ($selectedTypes as $selectedType) {
                $qb->andWhere('t.lft >= :lft AND t.rgt <= :rgt')
                    ->setParameter('lft', $selectedType->getLft())
                    ->setParameter('rgt', $selectedType->getRgt());

            }
        }

        // Filtre sur les types d'activités
        if (count($filterYears)) {
            $displayFilters = true;
            $clause = [];
            $values = [];

            foreach ($filterYears as $year) {
                $values['start_' . $year] = $year . '-01-01';
                $values['end_' . $year] = $year . '-12-31';
                $clause[] = "(c.dateStart <= '$year-12-31' AND c.dateEnd >= '$year-01-01')";
            }

            $qb->andWhere(implode(' OR ', $clause));
        }

        // Persons
        $persons = [];
        if (count($filterPersons)) {
            $displayFilters = true;
            foreach ($this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')->where('p.id IN (:persons)')->setParameter('persons',
                $filterPersons)->getQuery()->getResult() as $p) {
                $persons[] = $p;
            }
            $qb->innerJoin('c.persons', 'm')
                ->leftJoin('m.person', 'p')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'pm')
                ->leftJoin('pm.person', 'p2')
                ->andWhere('p.id in (:personIds) OR p2.id IN (:personIds)')
                ->setParameter('personIds', $filterPersons);
        }

        if ($datefilter_type != '' && ($datefilter_from != '' || $datefilter_to != '')) {
            $displayFilters = true;
            if ($datefilter_from != '') {
                $qb->andWhere('c.' . $datefilter_type . ' >= :from')
                    ->setParameter('from', $datefilter_from);
            }
            if ($datefilter_to != '') {
                $qb->andWhere('c.' . $datefilter_type . ' <= :to')
                    ->setParameter('to', $datefilter_to);
            }
        }
        try {
            // IDS
            $qbIds = $qb->select('c.id');
            $ids = [];
            foreach ($qbIds->getQuery()->getResult() as $row) {
                $ids[] = $row['id'];
            }
            $qb->select('c');

            $paginator = new UnicaenDoctrinePaginator($qb, $page, 20);

        } catch (\Exception $e) {
            die(sprintf("<h1>%s</h1><pre>%s</pre>", $e->getMessage(),
                $e->getTraceAsString()));
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $json = [
                'datas' => []
            ];
            /** @var Activity $activity */
            foreach ($paginator as $activity) {
                $json['datas'][] = $activity->toJson();
            }

            return $this->ajaxResponse($json);
            // die('Recherche');
        }


        return [
            'exportIds' => implode(',', $ids),
            'contracts' => $paginator,
            'search' => $search,
            'filterStatus' => $filterStatus,
            'filterType' => $filterType,
            'filterYear' => $filterYears,
            'sorts' => $sortCriteria,
            'directions' => $sortDirections,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'filterPersons' => $persons,
            'displayFilters' => $displayFilters,
            'datefilter_type' => $datefilter_type,
            'datefilter_from' => $datefilter_from,
            'datefilter_to' => $datefilter_to,
            'types' => $this->getActivityTypeService()->getActivityTypes(true),
        ];
    }



    public function almostStartAction()
    {
        $qb = $this->getActivityService()->getActivityBeginsSoon();
        $persons = $this->filterPersons($qb);
        $activities = $qb->orderBy('c.dateStart',
            'DESC')->getQuery()->getResult();

        $view = new ViewModel([
            'entities' => $activities,
            'filterLabel' => "débutant prochainement",
            'datePrefix' => "Débute",
            'getDateGroup' => 'getDateStart',
            'persons' => $persons,

        ]);


        $view->setTemplate('oscar/activity/list-view.phtml');

        return $view;
    }


    public function almostDoneAction()
    {
        $qb = $this->getActivityService()->getActivityAlmostDone();
        $persons = $this->filterPersons($qb);
        $activities = $qb->orderBy('c.dateEnd')->getQuery()->getResult();

        $view = new ViewModel([
            'entities' => $activities,
            'filterLabel' => "se terminant bientôt",
            'datePrefix' => "Se termine",
            'getDateGroup' => 'getDateEnd',
            'persons' => $persons,
        ]);
        $view->setTemplate('oscar/activity/list-view.phtml');

        return $view;
    }

    public function mergeAction()
    {
        return $this->getResponseNotImplemented();
    }

    ////////////////////////////////////////////////////////////////////////////


}
