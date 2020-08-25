<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-12-22 11:29
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Monolog\Formatter\JsonFormatter;
use Oscar\Entity\AdministrativeDocumentSection;
use Oscar\Entity\Authentification;
use Oscar\Entity\Discipline;
use Oscar\Entity\LogActivity;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\Person;
use Oscar\Entity\Privilege;
use Oscar\Entity\Role;
use Oscar\Entity\TVA;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\ConnectorService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Traits\UseAdministrativeDocumentService;
use Oscar\Traits\UseAdministrativeDocumentServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseProjectServiceTrait;
use Oscar\Traits\UseTypeDocumentService;
use Oscar\Traits\UseTypeDocumentServiceTrait;
use PhpOffice\PhpWord\Writer\Word2007\Part\DocumentTest;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Zend\Config\Writer\Yaml;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Http\Request;
use Oscar\Entity\TypeDocument;
use Zend\View\Model\ViewModel;

class AdministrationController extends AbstractOscarController implements UseProjectGrantService, UseTypeDocumentService, UseAdministrativeDocumentService, UseOrganizationService, UseOscarConfigurationService
{
    use UseProjectGrantServiceTrait, UseTypeDocumentServiceTrait, UseAdministrativeDocumentServiceTrait, UseOrganizationServiceTrait, UseOscarConfigurationServiceTrait;

    private $serviceLocator;

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator(){
        return $this->serviceLocator;
    }

    private $connectorService;

    /**
     * @return mixed
     */
    public function getConnectorService()
    {
        return $this->connectorService;
    }

    /**
     * @param mixed $connectorService
     */
    public function setConnectorService( ConnectorService $connectorService)
    {
        $this->connectorService = $connectorService;
    }



    public function setServiceLocator(ContainerInterface $s) {
        $this->serviceLocator = $s;
    }

    public function indexAction()
    {
        $this->getOscarUserContextService()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);
        return [];
    }

    public function documentSectionsAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_DOCPUBSEC_MANAGE);


        return $this->oscarRest(
            function(){
                return [

                ];
            },
            // GET
            function(){
                return [
                    'sections' => $this->getAdministrativeDocumentService()->getSections(true)
                ];
            },
            // POST
            function(){
                $this->getAdministrativeDocumentService()->createOrUpdateSection($this->params()->fromPost());
                return ["response" => "Section enregistrée"];
            },
            function(){
                try {
                    $id     = $this->params()->fromPost('id', null);
                    $this->getAdministrativeDocumentService()->removeSection($id);
                    return ["response" => "Section supprimée"];
                } catch (\Exception $e) {
                    return $this->getResponseInternalError($e->getMessage());
                }

            }
        );
    }

    public function testconfigAction(){
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);
        var_dump($this->getOscarConfigurationService()->getConfigArray()['oscar']);
        die();
    }

    public function privileges2Action(){
        $this->getOscarUserContextService()->check(Privileges::DROIT_ROLE_VISUALISATION);
        if( $this->isAjax() ){

        }
        return [];
    }

    public function oscarWorkerStatusAction(){

        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

        if( $this->isAjax() ){
            $response = shell_exec('journalctl --unit=oscarworker.service -n 25 --no-pager');
            if( $response === null ){
                $response = "Impossible de charger le status de OscarWorker";
            }
            return $this->getResponseOk($response);
        }
        return [];
    }

    public function parametersAction()
    {

        // Récupération des rôles en fonction d'un privilège :

        $roles = $this->getOscarUserContextService()->getRolesWithPrivileges(Privileges::ACTIVITY_EDIT);
        $rolesOrganisationPrincipal = $this->getOscarUserContextService()->getRolesOrganisationLeader();
        $config = $this->getOscarConfigurationService()->getOrganizationLeaderRole();

        $rolesInOrganization = [];
        foreach ($roles as $role) {
            $rolesInOrganization[$role->getId()] = (string)$role;
        }

        $organization_leader_role = [
            'config' => $config,
            'rolesInOrganization' => $rolesInOrganization,
            'roleOrganizationPrincipal' => $rolesOrganisationPrincipal
        ];

        if( $this->getHttpXMethod() == "POST" ){
            $option = $this->params()->fromPost('parameter_name');
            switch ($option) {
                case OscarConfigurationService::allow_numerotation_custom:
                    $value = $this->params()->fromPost('parameter_value') == "on";
                    $this->getOscarConfigurationService()->setNumerotationEditable($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case OscarConfigurationService::theme:
                    $value = $this->params()->fromPost('parameter_value', '');
                    $this->getOscarConfigurationService()->setTheme($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case "export_options":
                    $separator = $this->params()->fromPost('separator');
                    $dateFormat = $this->params()->fromPost('dateformat');
                    $this->getOscarConfigurationService()->setExportSeparator($separator);
                    $this->getOscarConfigurationService()->setExportDateFormat($dateFormat);
                    return $this->redirect()->toRoute('administration/parameters');

                case "timesheet_preview":
                    $value = $this->params()->fromPost('parameter_value') == "on";
                    $this->getOscarConfigurationService()->setTimesheetPreview($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case "timesheet_excel":
                    $value = $this->params()->fromPost('parameter_value') == "on";
                    $this->getOscarConfigurationService()->setTimesheetExcel($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case "organization_leader_role":
                    $value = $this->params()->fromPost('role_organization');
                    $this->getOscarConfigurationService()->setOrganizationLeaderRole($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case OscarConfigurationService::spents_account_filter:
                    $value = $this->params()->fromPost(OscarConfigurationService::spents_account_filter);
                    $this->getOscarConfigurationService()->setSpentAccountFilter($value);
                    return $this->redirect()->toRoute('administration/parameters');

                case OscarConfigurationService::activity_request_limit:
                    $value = intval($this->params()->fromPost('parameter_value'));
                    $this->getOscarConfigurationService()->setActivityRequestLimit($value);
                    return $this->redirect()->toRoute('administration/parameters');


                default:
                    return $this->getResponseBadRequest("Paramètres non-reconnue");
            }
        }

        return [
            OscarConfigurationService::spents_account_filter => implode(', ', $this->getOscarConfigurationService()->getSpentAccountFilter()),
            OscarConfigurationService::activity_request_limit => $this->getOscarConfigurationService()->getActivityRequestLimit(),
            'timesheet_preview' => $this->getOscarConfigurationService()->getTimesheetPreview(),
            'timesheet_excel' => $this->getOscarConfigurationService()->getTimesheetExcel(),
            'allow_numerotation_custom' => $this->getOscarConfigurationService()->getNumerotationEditable(),
            'themes' => $this->getOscarConfigurationService()->getConfiguration('themes'),
            'theme' => $this->getOscarConfigurationService()->getTheme(),
            'export' => [
                'separator' => $this->getOscarConfigurationService()->getExportSeparator(),
                'dateformat' => $this->getOscarConfigurationService()->getExportDateFormat()
            ],
            'organization_leader_role' => $organization_leader_role
        ];
    }

    public function accessAction(){
        return [];
    }

    protected function saveEditableConfKey($key, $value){
        $this->getOscarConfigurationService()->saveEditableConfKey($key, $value);
    }

    public function numerotationAction()
    {

        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_NUMEROTATION_MANAGE);


        $invalidActivityNumbers = $this->getProjectGrantService()->getActivitiesWithUnreferencedNumbers();

        if( $this->isAjax() ) {
            $method = $this->getHttpXMethod();
            switch ($method) {
                case 'GET':
                    $numerotation = $this->getOscarConfigurationService()->getEditableConfKey('numerotation', []);
                    return $this->jsonOutput($numerotation);
                    break;

                case 'DELETE':
                    $numerotation = $this->getOscarConfigurationService()->getEditableConfKey('numerotation', []);
                    $deleted = $this->params()->fromQuery('str');
                    if( !in_array($deleted, $numerotation) ){
                        return $this->getResponseBadRequest("Impossible de supprimer '$deleted'.'");
                    }

                    $index = array_search($deleted, $numerotation);

                    array_splice($numerotation, $index, 1);

                    try {
                        $this->getOscarConfigurationService()->saveEditableConfKey('numerotation', $numerotation);
                        $this->getResponseOk();
                    } catch ( \Exception $e ){
                        return $this->getResponseInternalError("Impossible de supprimer le type '$deleted' : " . $e->getMessage());
                    }

                    break;

                case 'POST':
                    $numerotation = $this->getOscarConfigurationService()->getEditableConfKey('numerotation', []);
                    $added = trim($this->params()->fromPost('str'));

                    if( $added == "" ){
                        return $this->getResponseInternalError("Impossible d'ajouter une valeur vide.");
                    }

                    if( in_array($added, $numerotation) ){
                        return $this->getResponseInternalError("Le type '$added' existe déjà.");
                    }
                    $numerotation[] = $added;

                    try {
                        $this->getOscarConfigurationService()->saveEditableConfKey('numerotation', $numerotation);
                        $this->getResponseOk();
                    } catch ( \Exception $e ){
                        return $this->getResponseInternalError("Impossible d'ajouter le type '$added' : " . $e->getMessage());
                    }
                    break;

                default:
                    return $this->getResponseInternalError();
            }
        }
        return [
            "invalidActivityNumbers" => $invalidActivityNumbers
        ];
    }

    public function tvaAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_TVA_MANAGE);
        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch ( $method ){
                case 'GET':
                    return $this->jsonOutput(['tvas' => $this->getProjectGrantService()->getTVAsForJson()]);
                    break;

                case 'DELETE':
                    try {
                        $id = $this->params()->fromQuery('id');

                        if( $id ){
                            $tva = $this->getProjectGrantService()->getTVA($id);
                            if( !$tva ){
                                return $this->getResponseInternalError("Impossible de charger la TVA '$id'");
                            }
                        } else {
                            return $this->getResponseBadRequest("");
                        }
                        $this->getEntityManager()->remove($tva);
                        return $this->getResponseOk('TVA supprimée');
                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    return $this->jsonOutput(['tvas' => $this->getProjectGrantService()->getTVAsForJson()]);
                    break;

                case 'POST':

                    try {
                        $id = intval($this->params()->fromPost('id', null));
                        $active = boolval($this->params()->fromPost('active', false));
                        if( $active == 'true' ) $active = true;
                        $label = $this->params()->fromPost('label', "PAS d'INTITULÉ");
                        $rate = floatval($this->params()->fromPost('rate', 0.0));

                        if( $id ){
                            $tva = $this->getProjectGrantService()->getTVA($id);
                            if( !$tva ){
                                throw new OscarException("Impossible de charger la TVA '$id'");
                            }
                        } else {
                            $tva = new TVA();
                            $this->getEntityManager()->persist($tva);
                        }

                        $tva->setLabel($label)
                            ->setRate($rate)
                            ->setActive($active);

                        $this->getEntityManager()->flush($tva);

                        return $this->getResponseOk('TVA créée');

                    } catch (\Exception $e ){
                        return $this->getResponseInternalError($e->getMessage());
                    }
                    break;

                default:
                    return $this->getResponseBadRequest("Erreur d'API");
            }

        }
        return [];
    }

    public function accueilAction(){
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);
        return [];
    }

    public function disciplineAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_DISCIPLINE_MANAGE);

        $disciplines = $this->getEntityManager()->getRepository(Discipline::class)->getDisciplinesCounted();
        $method = $this->getHttpXMethod();

        switch ($method) {
            case 'PUT' :
                $label = $this->params()->fromPost('label');
                $discipline = new Discipline();
                $this->getEntityManager()->persist($discipline);
                $discipline->setLabel($label);

                try {
                    $this->getEntityManager()->flush($discipline);
                    $data = $discipline->toJson();
                    $data['actyivitiesLng'] = 0;
                    return $this->ajaxResponse(['discipline' => $data]);
                } catch (\Exception $e ) {
                    return $this->getResponseInternalError("Impossible d'ajouter la discipline : " . $e->getMessage());
                }


                break;

            case 'POST' :
                $label = $this->params()->fromPost('label');
                $id = $this->params()->fromPost('id');

                try {
                    $discipline = $this->getEntityManager()->getRepository(Discipline::class)->find($id);
                    $discipline->setLabel($label);
                    $this->getEntityManager()->flush($discipline);
                    $data = $discipline->toJson();
                    return $this->ajaxResponse(['discipline' => $data]);
                } catch (\Exception $e ) {
                    return $this->getResponseInternalError("Impossible d'ajouter la discipline : " . $e->getMessage());
                }
                return $this->getResponseNotImplemented("MODIFICATION Pas encore implanté");
                break;

            case 'DELETE' :
                $id = $this->params()->fromQuery('id');
                try {
                    $discipline = $this->getEntityManager()->getRepository(Discipline::class)->find($id);
                    $this->getEntityManager()->remove($discipline);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch (\Exception $exception) {
                    return $this->getResponseInternalError("Impossible de supprimer la discipline : " . $exception->getMessage());
                }

        }

        $datas = [
          'disciplines' => $disciplines
        ];
        return $datas;
    }


    public function activityIndexBuildAction(){
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_SEARCH_BUILD);
        return [
            'repport' => $this->getProjectGrantService()->searchIndex_rebuild()
        ];
    }

    public function organizationTypeAction(){
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_ORGANIZATIONTYPE_MANAGE);
        $datas = [];

        if( ($action = $this->params()->fromQuery('action')) ){
            if( $action == 'generate' ){
                return $this->getResponseDeprecated("Cette fonctionnalité a été retiré");
            }
            return $this->getResponseBadRequest();
        }

        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            try {
                switch( $method ){
                    case 'GET' :
                        $datas['organizationtypes'] = $this->getOrganizationService()->getOrganizationTypes();
                        return $this->ajaxResponse($datas);

                    case 'DELETE' :
                        $id = $this->params()->fromRoute('id');
                        $this->getOrganizationService()->removeOrganizationType($id);
                        return $this->getResponseOk("Type d'organisation supprimée");

                    case 'POST' :
                        $type = $this->getOrganizationService()->updateOrCreateOrganizationType($this->params()->fromPost());
                        return $this->ajaxResponse([$type->toJson()]);

                    default:
                        return $this->getResponseInternalError("Mauvaise utilisation de l'API");
                }
            } catch (\Exception $e) {
                return $this->getResponseInternalError($e->getMessage());
            }
        }
        return $datas;
    }

    public function connectorsHomeAction(){

        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);

        $configOscar = $this->getOscarConfigurationService();
        $configConnectors = $configOscar->getConfiguration('connectors');

        $labels = [
            'person_organization' => "Affection des personnes aux organisations",
            'organization' => "Organisations/Structures",
            'person' => "Personnes",
        ];

        $out = [];

        foreach( $configConnectors as $connectorType=>$connectorsConfig ){
            if( !array_key_exists($connectorType, $out) ){
                $out[$connectorType] = [];
            }
            foreach( $connectorsConfig as $tag=>$data){
                if( !array_key_exists($tag, $out[$connectorType]) ){
                    $out[$connectorType][$tag] = [
                        'label' => $tag,
                        'class' => array_key_exists('class', $data) ? $data['class'] : null,
                        'params' => array_key_exists('params', $data) ? $data['params'] : null,
                    ];
                }
            }
        }
        return [
            'connectors' => $out,
            'labels' => $labels
        ];
    }


    private function getRouteConnector($connectorType, $connectorName)
    {
        /** @var ConfigurationParser $configOscar */
        $configOscar = $this->getOscarConfigurationService();

        try {
            $connectorsConfig = $configOscar->getConfiguration('connectors');
            if( !array_key_exists($connectorType, $connectorsConfig) ){
                throw new \Exception(sprintf("Aucun connecteur de type %s n'est définit dans la configuration", $connectorType));
            }

            // Configuration du type de connector
            $connectorTypeConfig = $connectorsConfig[$connectorType];

            // Configuration du connector donné
            if( !array_key_exists($connectorName, $connectorTypeConfig) ){
                throw new \Exception(sprintf("Aucun connecteur %s n'est définit dans la configuration", $connectorName));
            }

            $connectorConfig = $connectorTypeConfig[$connectorName];

            return $this->getServiceLocator()
                ->get(ConnectorService::class)
                ->getConnector($connectorType.'.'.$connectorName);

        } catch(\Exception $e ){
            throw new OscarException("Impossible de trouver la configuration du connecteur $connectorType/$connectorName");
        }
    }

    /**
     * Écran de configuration des connecteurs.
     */
    public function connectorConfigureAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);

        $connectorType = $this->params()->fromRoute('connectortype');
        $connectorName = $this->params()->fromRoute('connectorname');

        $configOscar = $this->getOscarConfigurationService();
        $connector = $this->getRouteConnector($connectorType, $connectorName);

        if( $this->getHttpXMethod() == "POST" ){
            $connector->updateParameters($this->getRequest()->getPost("$connectorType"."_"."$connectorName"));
        }

        $config = $connector->getConfigData(true);
        return [
            'config' => $config,
            'connectorType' => $connectorType,
            'connectorName' => $connectorName
        ];

    }

    /**
     * Exécution du connecteur.
     * @return array
     */
    public function connectorExecuteAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);

        $connectorType = $this->params()->fromRoute('connectortype');
        $connectorName = $this->params()->fromRoute('connectorname');
        $force = $this->params()->fromQuery('force', false);
        $configOscar = $this->getOscarConfigurationService();

        try {
            $connectorsConfig = $configOscar->getConfiguration('connectors');
            if( !array_key_exists($connectorType, $connectorsConfig) ){
                throw new \Exception(sprintf("Aucun connecteur de type %s n'est définit dans la configuration", $connectorType));
            }

            // Configuration du type de connector
            $connectorTypeConfig = $connectorsConfig[$connectorType];

            // Configuration du connector donné
            if( !array_key_exists($connectorName, $connectorTypeConfig) ){
                throw new \Exception(sprintf("Aucun connecteur %s n'est définit dans la configuration", $connectorName));
            }

            $connectorConfig = $connectorTypeConfig[$connectorName];


            $connector = $this->getConnectorService()->getConnector($connectorType.'.'.$connectorName);
            $repport = $connector->execute(true);
            return [
                'repport' => $repport,
                'connectorType' => $connectorType,
                'connectorName' => $connectorName,
            ];
        } catch(\Exception $e ){
            throw $e;
        }
    }

    /**
     * Affiche l'écran de configuration des connecteurs.
     *
     * @return array
     */
    public function connectorsConfigAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);

        ///////////////////////////////////// Connecteurs PERSON <> ORGANIZATION
        $personOrganizationConnectors = $this->getOscarConfigurationService()
            ->getConfiguration('connectors.person_organization');




        // Configurations disponibles
        $configs = [];


        foreach($personOrganizationConnectors as $connector ){

            $class  = $connector['class'];
            $connectorInstance = new $class();
            $connectorInstance->init($this->getServiceLocator(), $connector['params']);
            if( $this->getHttpXMethod() == "POST" && $this->getRequest()->getPost($connectorInstance->getType()) ){
                $connectorInstance->updateParameters($this->getRequest()->getPost($connectorInstance->getType()));
            }
            $config = $connectorInstance->getConfigData(true);
            $configs[] = $config;
        }

        return ['configs'=>$configs];
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // API
    //
    ////////////////////////////////////////////////////////////////////////////
    public function accessAPIAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $this->getOscarUserContextService()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);

        $idRolePrivilege = $this->params()->fromRoute('idroleprivilege', null);
        $method = $this->getHttpXMethod();

        if ($idRolePrivilege) {
            switch ($this->getHttpXMethod()) {

            }
        } else {
            switch ($this->getHttpXMethod()) {
                case 'PATCH':
                    $this->getOscarUserContextService()->check(Privileges::DROIT_PRIVILEGE_EDITION);
                    $privilegeId = $request->getPost('privilegeid');
                    $roleId = $request->getPost('roleid');

                    /** @var Privilege $privilege */
                    $privilege = $this->getEntityManager()->getRepository(Privilege::class)->find($privilegeId);
                    if (!$privilege) {
                        return $this->getResponseBadRequest(sprintf("Le privilège %s n'existe pas/plus",
                            $privilegeId));
                    }


                    /** @var Role $role */
                    $role = $this->getEntityManager()->getRepository(Role::class)->find($roleId);
                    if (!$role) {
                        return $this->getResponseBadRequest(sprintf("Le rôle %s n'existe pas/plus",
                            $roleId));
                    }


                    if ($privilege->hasRole($role)) {
                        $privilege->removeRole($role);
                    } else {
                        $privilege->addRole($role);
                    }

                    $this->getEntityManager()->flush();

                    return $this->ajaxResponse($privilege->asArray());
                // LISTE COMPLETE
                case 'GET' :
                    $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();
                    $roles = $this->getEntityManager()->getRepository(Role::class)->findAll();

                    $out = [
                        'privileges' => [],
                        'roles' => []
                    ];

                    /** @var Privilege $privilege */
                    foreach ($privileges as $privilege) {
                        if( !$privilege->getRoot() )
                            $out['privileges'][] = $privilege->asArray();
                    }
                    /** @var Role $role */
                    foreach ($roles as $role) {
                        $out['roles'][] = $this->getJsonRole($role);
                    }

                    return $this->ajaxResponse($out);
            }
        }

        return $this->getResponseNotImplemented(sprintf("La méthode %s n'est pas prise en charge.",
            $method));
    }

    ////////////////////////////////////////////////////////////////////////////

    public function usersAction()
    {
        $this->getOscarUserContextService()->check(Privileges::DROIT_USER_VISUALISATION);
        $authenticated = $this->getEntityManager()->getRepository(Authentification::class)->findAll();
        $roleDb = $this->getEntityManager()->getRepository(Role::class)->findAll();

        $out = [
            'users' => [],
            'roles' => []
        ];

        /** @var Authentification $auth */
        foreach ($authenticated as $auth) {
            $d = $auth->toJson();
            $person = null;
            try {
                $p = $this->getOscarUserContextService()->getPersonFromAuthentification($auth);
                if( $p )
                    $person = $p->toJson();
            } catch ( \Exception $e ){
               // Pas de Personne associée à cette authentification
            }

            $d['person'] = $person;
            $out['users'][] = $d;
        }

        /** @var Role $role */
        foreach ($roleDb as $role) {
            $out['roles'][] = $this->getJsonRole($role);
        }

        return $out;
    }

    public function userRolesAction(){
        if( !$this->getOscarUserContextService()->hasPrivileges(Privileges::DROIT_USER_EDITION) ){
            return $this->getResponseUnauthorized();
        }

        $authentificationId = $this->params()->fromPost('authentification_id');
        $roleId = $this->params()->fromPost('role_id');

        try {
            /** @var Authentification $authentification */
            $authentification = $this->getEntityManager()->getRepository(Authentification::class)->find($authentificationId);

            /** @var Role $role */
            $role = $this->getEntityManager()->getRepository(Role::class)->find($roleId);
            if( !$authentification ){
                return $this->getResponseNotFound("Compte introuvable.");
            }
            if( !$role ){
                return $this->getResponseNotFound("Rôle '$roleId' introuvable.");
            }
        } catch ( \Exception $e ){
            return $this->getResponseInternalError("Rôle/Authentification introuvable : " . $e->getMessage());
        }

        $method = $this->getHttpXMethod();

        switch( $method ){
            case 'POST':
                try {
                    $authentification->addRole($role);
                    $this->getEntityManager()->flush();
                } catch (UniqueConstraintViolationException $e ){
                    return $this->getResponseInternalError("Ce compte a déjà ce rôle.");
                }
                return $this->ajaxResponse($authentification->toJson());
            case 'DELETE':
                try {
                    $authentification->removeRole($role);
                    $this->getEntityManager()->flush();
                } /*catch (Doct $e ){
                    return $this->getResponseInternalError("Impossible de supprimer le role : " . $e->getMessage());
                }*/ catch (\Exception $e ){
                    return $this->getResponseInternalError(get_class($e) . " - Impossible de supprimer le role : " . $e->getMessage());
                }
                return $this->ajaxResponse($authentification->toJson());
        }

        return $this->getResponseBadRequest("Erreur");
    }

    public function userLogsAction(){
        $this->getOscarUserContextService()->check(Privileges::DROIT_USER_VISUALISATION);
        $userid = $this->params('userid');
        $logs=[];
        $activitiesLog = $this->getEntityManager()->getRepository(LogActivity::class)->findBy(['userId' => $userid],['dateCreated'=>'DESC'], 100);
        foreach( $activitiesLog as $log ){
            $logs[] = $log->toArray();
        }
       return $this->ajaxResponse($logs);
    }


    private function hydrateRolewithPost(Role &$role, Request $request)
    {
        $ldapfilter = $request->getPost('ldapFilter');

        if (trim($ldapfilter) == '') {
            $ldapfilter = null;
        }

        $role->setRoleId($request->getPost('roleId'))
            ->setLdapFilter($ldapfilter)
            ->setDescription($request->getPost('description'))
            ->setSpot($request->getPost('spot'))
            ->setPrincipal($request->getPost('principal'));
    }

    /**
     * Prépare les données pour le réstitution en JSON.
     *
     * @param Role $role
     */
    private function getJsonRole(Role $role)
    {
        $manage = $this->getOscarUserContextService()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
        $datas = $role->asArray();
        $datas['editable'] = $role->getRoleId() == "Administrateur" ? false : $manage;
        $datas['deletable'] = $role->getRoleId() == "Administrateur" ? false : $manage;

        return $datas;
    }

    /**
     * Gestion/visualisation des rôles.
     *
     * @return \Zend\Http\Response|\Zend\View\Model\JsonModel
     */
    public function roleAPIAction()
    {
        $this->getOscarUserContextService()->check(Privileges::DROIT_USER_VISUALISATION);
        /** @var Request $request */
        $request = $this->getRequest();
        try {
            $roleId = $this->params()->fromRoute('idrole');

            if ($roleId) {

                $this->getOscarUserContextService()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);

                /** @var Role $role */
                $role = $this->getEntityManager()->getRepository(Role::class)->find($roleId);
                if (!$role) {
                    return $this->getResponseBadRequest("Ce rôle n'existe plus.");
                }

                switch ($this->getHttpXMethod()) {
                    // Mise à jour
                    case "PUT" :
                        $this->hydrateRolewithPost($role, $request);
                        /** @var Role $otherRole */
                        $otherRole = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $role->getRoleId()]);
                        if ($otherRole && $role->getId() != $otherRole->getId()) {
                            return $this->getResponseBadRequest("Un rôle a déjà cet identifiant.");
                        }

                        $this->getEntityManager()->flush();
                        $this->getActivityLogService()->addUserInfo(
                            "a mis à jour le rôle " . $role->getRoleId()
                        );

                        return $this->ajaxResponse($this->getJsonRole($role));
                        break;
                    // Suppression
                    case "DELETE" :
                        $this->getActivityLogService()->addUserInfo(
                            "a supprimé le rôle " . $role->getRoleId()
                        );
                        try {
                            $this->getEntityManager()->remove($role);
                            $this->getEntityManager()->flush();
                            return $this->getResponseOk("Rôle supprimé");
                        } catch (ForeignKeyConstraintViolationException $e ){
                            return $this->getResponseInternalError("Impossible de supprimer le rôle '$role', il est encore utilisé et doit être conservé pour préserver l'historique");
                        }

                        break;
                }

                return $this->getResponseBadRequest();
            } else {
                // Création
                if ($this->getHttpXMethod() == "POST") {
                    $this->getOscarUserContextService()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
                    $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $request->getPost('roleId')]);
                    if ($role) {
                        return $this->getResponseBadRequest(sprintf("le nom de rôle '%s' est déjà utilisé",
                            $roleId));
                    } else {
                        $role = new Role();
                        $this->hydrateRolewithPost($role, $request);
                        $this->getEntityManager()->persist($role);
                        $this->getEntityManager()->flush();

                        $this->getActivityLogService()->addUserInfo(
                            "a ajouté le rôle " . $role->getRoleId()
                        );

                        return $this->ajaxResponse($this->getJsonRole($role));
                    }
                }

                return $this->getResponseNotImplemented('A FAIRE');
            }


        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }

    }

    public function rolesAction()
    {
        $this->getOscarUserContextService()->check(Privileges::DROIT_ROLE_VISUALISATION);
        $out = [];
        return ["roles" => json_encode($out)];
    }

    public function rolesEditAction()
    {
        $this->getOscarUserContextService()->check(Privileges::DROIT_ROLE_EDITION);
        /** @var Request $request */
        $request = $this->getRequest();
        try {
            $role = $this->getEntityManager()->getRepository(Role::class)->find($this->params()->fromRoute('id'));
            if (!$role) {
                return $this->getResponseInternalError("Rôle inconnu");
            }
            $spot = $request->getPost('spot');
            $role->setSpot($spot);
            $this->getEntityManager()->flush();

            return $this->getResponseOk();
        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }

        return $this->getResponseNotImplemented();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // RÔLE des ORGANISATIONS dans les ACTIVITÈS
    //
    ////////////////////////////////////////////////////////////////////////////
    public function organizationRoleAction(){
        $this->getOscarUserContextService()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        return [];
    }


    public function organizationRoleApiAction(){
        $this->getLoggerService()->debug("> ORGANISATIONROLE API");
        $this->getOscarUserContextService()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        $roleId = $this->params('roleid', null);
        /** @var Request $request */
        $request = $this->getRequest();
        if( $roleId == null ){
            $this->getLoggerService()->debug("Pas de ROLEID");
            ////////////////////////////////////////////////////////////////////
            // GET : Liste des rôles
            if( $this->getHttpXMethod() == 'GET' ){
                $roles = $this->getEntityManager()->getRepository(OrganizationRole::class)->findBy([], ['label' => 'ASC']);
                $out = [];
                /** @var OrganizationRole $role */
                foreach( $roles as $role ){
                    $out[] = $role->toArray();
                }
                return $this->ajaxResponse($out);
            }
            ////////////////////////////////////////////////////////////////////
            // POST : Nouveau rôle
            elseif( $this->getHttpXMethod() == 'POST' ){
                $this->getOscarUserContextService()->check(Privileges::DROIT_ROLEORGA_EDITION);
                $role = new OrganizationRole();
                $role->setLabel($request->getPost('label'))
                    ->setDescription($request->getPost('description'))
                    ->setPrincipal($request->getPost('principal') == 'true');
                $this->getEntityManager()->persist($role);
                $this->getEntityManager()->flush();
                return $this->ajaxResponse($role->toArray());
            }
        }
        else {
            $this->getOscarUserContextService()->check(Privileges::DROIT_ROLEORGA_EDITION);
            $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($roleId);
            if( !$role ){
                return $this->getResponseInternalError("Ce rôle est introuvable dans la base de données.");
            }

            if( $this->getHttpXMethod() == 'PUT' ){
                $role->setLabel($request->getPost('label'))
                    ->setDescription($request->getPost('description'))
                    ->setPrincipal($request->getPost('principal') == 'true');
                $this->getEntityManager()->persist($role);
                $this->getEntityManager()->flush();
                return $this->ajaxResponse($role->toArray());
            }
            ////////////////////////////////////////////////////////////////////
            // POST : Nouveau rôle
            elseif( $this->getHttpXMethod() == 'DELETE' ){
                $this->getEntityManager()->remove($role);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('le rôle a été supprimé.');
            }
        }
        return $this->getResponseBadRequest("Accès à l'API improbable...");
    }

    /**
     * Gestion des types de documents.
     */
    public function typeDocumentAction() {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_DOCUMENTTYPE_MANAGE);
        return new ViewModel([]);
    }


    public function typeDocumentApiAction() {

        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_DOCUMENTTYPE_MANAGE);
        $method = $this->getHttpXMethod();

        switch ($method) {
            case 'GET' :
                $entityRepos =  $this->getEntityManager()->getRepository(TypeDocument::class)->createQueryBuilder('d')->orderBy('d.label');
                $results = $entityRepos->getQuery()->getResult();
                $out = [];
                /** @var OrganizationRole $role */
                foreach ($results as $row) {
                    $out[] = $row->toArray();
                }
                return $this->ajaxResponse($out);

            case 'POST' :
                $label = $this->params()->fromPost('label');
                $description = $this->params()->fromPost('description');
                $type = new TypeDocument();
                $type->setLabel($label)
                    ->setDescription($description);
                $this->getEntityManager()->persist($type);
                $this->getEntityManager()->flush();
                return $this->getResponseOk();
                break;

            case 'PUT' :
                try {
                    $_PUT = $_POST;
                    $typeDocumentId = $_PUT['typedocumentid'];
                    $this->getLoggerService()->info("INFO : typeDocumentActionApi() PUT mise à jour du type de document $typeDocumentId");
                    $this->getLoggerService()->info(print_r($_POST, true));
                    $typeDocument = $this->getProjectGrantService()->getTypeDocument($typeDocumentId, true);
                    $typeDocument->setLabel($_PUT['label'])
                        ->setDescription($_PUT['description']);
                    $this->getEntityManager()->persist($typeDocument);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch (\Exception $e ){
                    $msg = sprintf(_("Impossible de mettre à jour le type de document %s : %s"), $typeDocument, $e->getMessage());
                    $this->getLoggerService()->error($msg);
                    return $this->getResponseInternalError($msg);
                }

            case 'DELETE' :
                $typeDocumentId = $this->params()->fromQuery('typedocumentid');
                $typeDocument = $this->getProjectGrantService()->getTypeDocument($typeDocumentId, true);
                $this->getEntityManager()->remove($typeDocument);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('le type de document a été supprimé.');

        }

        return $this->getResponseBadRequest("Accès à l'API improbable...");
    }

}