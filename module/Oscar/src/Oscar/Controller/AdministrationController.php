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
use Oscar\Service\OscarConfigurationService;
use PhpOffice\PhpWord\Writer\Word2007\Part\DocumentTest;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Zend\Config\Writer\Yaml;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Http\Request;
use Oscar\Entity\TypeDocument;
use Zend\View\Model\ViewModel;

class AdministrationController extends AbstractOscarController
{
    public function indexAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);
        return [];
    }

    public function documentSectionsAction()
    {
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_DOCPUBSEC_MANAGE);
        return $this->oscarRest(
            function(){
                return [

                ];
            },
            function(){

                return [
                    'sections' => $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->createQueryBuilder('s')->getQuery()->getArrayResult()
                ];
            },
            function(){
                $id     = $this->params()->fromPost('id', null);
                if( $id ){
                    $section = $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class)->find($id);
                    if( !$section ){
                        throw new \Exception("Section introuvable");
                    }
                } else {
                    $section = new AdministrativeDocumentSection();
                    $this->getEntityManager()->persist($section);
                }

                $label  = $this->params()->fromPost('label');
                $section->setLabel($label);
                try {
                    $this->getEntityManager()->flush($section);
                    return ["response" => "Enregistrement terminé"];
                } catch (\Exception $e ){
                    throw new \Exception($e->getMessage());
                }
            }
        );
    }

    public function parametersAction()
    {
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

                default:
                    return $this->getResponseBadRequest("Paramètres non-reconnue");
            }

            echo $option;
            die();
        }

        return [
            'allow_numerotation_custom' => $this->getOscarConfigurationService()->getNumerotationEditable(),
            'themes' => $this->getOscarConfigurationService()->getConfiguration('themes'),
            'theme' => $this->getOscarConfigurationService()->getTheme(),
            'export' => [
                'separator' => $this->getOscarConfigurationService()->getExportSeparator(),
                'dateformat' => $this->getOscarConfigurationService()->getExportDateFormat()
            ]
        ];
    }

    protected function saveEditableConfKey($key, $value){
        $this->getOscarConfigurationService()->saveEditableConfKey($key, $value);
    }

    public function numerotationAction()
    {

        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_NUMEROTATION_MANAGE);


        $invalidActivityNumbers = $this->getActivityService()->getActivitiesWithUnreferencedNumbers();

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
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_TVA_MANAGE);
        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch ( $method ){
                case 'GET':
                    return $this->jsonOutput(['tvas' => $this->getActivityService()->getTVAsForJson()]);
                    break;

                case 'DELETE':
                    try {
                        $id = $this->params()->fromQuery('id');

                        if( $id ){
                            $tva = $this->getActivityService()->getTVA($id);
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
                    return $this->jsonOutput(['tvas' => $this->getActivityService()->getTVAsForJson()]);
                    break;

                case 'POST':

                    try {
                        $id = intval($this->params()->fromPost('id', null));
                        $active = boolval($this->params()->fromPost('active', false));
                        if( $active == 'false' ) $active = false;
                        $label = $this->params()->fromPost('label', "PAS d'INTITULÉ");
                        $rate = floatval($this->params()->fromPost('rate', 0.0));

                        if( $id ){
                            $tva = $this->getActivityService()->getTVA($id);
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
        return [];
    }

    public function disciplineAction()
    {
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_DISCIPLINE_MANAGE);

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
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_SEARCH_BUILD);
        return [
            'repport' => $this->getActivityService()->searchIndex_rebuild()
        ];
    }

    public function organizationTypeAction(){
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_ORGANIZATIONTYPE_MANAGE);
        $datas = [];

        if( ($action = $this->params()->fromQuery('action')) ){
            if( $action == 'generate' ){
                return $this->getResponseDeprecated("Cette fonctionnalité a été retiré");
            }
            return $this->getResponseBadRequest();
        }

        if( $this->isAjax() ){
            $method = $this->getHttpXMethod();
            switch( $method ){
                case 'GET' :
                    $datas['organizationtypes'] = $this->getOrganizationService()->getOrganizationTypes();
                    return $this->ajaxResponse($datas);

                case 'DELETE' :
                    $id = $this->params()->fromRoute('id');
                    if( $id ){
                        $type = $this->getEntityManager()
                            ->getRepository(OrganizationType::class)
                            ->findOneBy(['id' => $id]);
                        if( $type ){
                            try {
                                foreach ($type->getChildren() as $t ){
                                    $t->setRoot(null);
                                }
                                $this->getEntityManager()->flush();
                                $this->getEntityManager()->remove($type);
                                $this->getEntityManager()->flush();
                            } catch (ForeignKeyConstraintViolationException $e ){
                                $this->getLogger()->error("Impossible de supprimer le type d'organisation: " . $e->getMessage());
                                return $this->getResponseInternalError("Erreur : ce type d'organisation est encore utilisé.");
                            }
                            return $this->getResponseOk("Type supprimé");
                        } else {
                           return $this->getResponseInternalError("Impossible de supprimer de type");
                        }

                    }
                    return $this->getResponseNotImplemented("En cours de développement");

                case 'POST' :
                    $id = $this->params()->fromPost('id', null);
                    $type = null;
                    if( $id ){
                        $type = $this->getEntityManager()
                            ->getRepository(OrganizationType::class)
                            ->findOneBy(['id' => $id]);
                    }

                    if( !$type ){
                        $type = new OrganizationType();
                        $this->getEntityManager()->persist($type);
                    }

                    $type->setLabel($this->params()->fromPost('label'));
                    $type->setDescription($this->params()->fromPost('description'));
                    $root = null;
                    $root_id = intval($this->params()->fromPost('root_id'));

                    if( $root_id && $root_id != $type->getId() )
                            $root = $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(['id' => $root_id]);

                    $type->setRoot($root);
                    $this->getEntityManager()->flush();
                    return $this->ajaxResponse([$type->toJson()]);

                case 'PUT' :
                    return $this->getResponseInternalError("La mise à jour n'est pas prise en charge.");

                default:
                    return $this->getResponseInternalError("Mauvaise utilisation de l'API");
            }

        }


        return $datas;
    }

    public function connectorsHomeAction(){
        $configOscar = $this->getServiceLocator()->get('OscarConfig');
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
        $configOscar = $this->getServiceLocator()->get('OscarConfig');

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
                ->get("ConnectorService")
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
        $connectorType = $this->params()->fromRoute('connectortype');
        $connectorName = $this->params()->fromRoute('connectorname');

        $configOscar = $this->getServiceLocator()->get('OscarConfig');
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
        $connectorType = $this->params()->fromRoute('connectortype');
        $connectorName = $this->params()->fromRoute('connectorname');
        $force = $this->params()->fromQuery('force', false);
        $configOscar = $this->getServiceLocator()->get('OscarConfig');

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


            $connector = $this->getServiceLocator()->get("ConnectorService")->getConnector($connectorType.'.'.$connectorName);
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

        ///////////////////////////////////// Connecteurs PERSON <> ORGANIZATION
        $personOrganizationConnectors = $this->getServiceLocator()
            ->get('OscarConfig')
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

        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);

        $idRolePrivilege = $this->params()->fromRoute('idroleprivilege', null);
        $method = $this->getHttpXMethod();

        if ($idRolePrivilege) {
            switch ($this->getHttpXMethod()) {

            }
        } else {
            switch ($this->getHttpXMethod()) {
                case 'PATCH':
                    $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_EDITION);
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
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
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
                $p = $this->getOscarUserContext()->getPersonFromAuthentification($auth);
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
        if( !$this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_USER_EDITION) ){
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
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
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
        $manage = $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
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
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
        /** @var Request $request */
        $request = $this->getRequest();
        try {
            $roleId = $this->params()->fromRoute('idrole');

            if ($roleId) {

                $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);

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
                    $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
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

                        return $this->ajaxResponse($role->asArray());
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
        $authenticated = $this->getEntityManager()->getRepository(Role::class)->findAll();
        $out = [];
        return ["roles" => json_encode($out)];
    }

    public function rolesEditAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLE_EDITION);
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
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        return [];
    }


    public function organizationRoleApiAction(){
        $this->getLogger()->debug("> ORGANISATIONROLE API");
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        $roleId = $this->params('roleid', null);
        /** @var Request $request */
        $request = $this->getRequest();
        if( $roleId == null ){
            $this->getLogger()->debug("Pas de ROLEID");
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
                $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_EDITION);
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
            $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_EDITION);
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
        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_DOCUMENTTYPE_MANAGE);
        return new ViewModel(array(

        ));

    }

    public function typeDocumentApiAction() {

        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_DOCUMENTTYPE_MANAGE);
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
                    $this->getLogger()->info("INFO : typeDocumentActionApi() PUT mise à jour du type de document $typeDocumentId");
                    $this->getLogger()->info(print_r($_POST, true));
                    $typeDocument = $this->getActivityService()->getTypeDocument($typeDocumentId, true);
                    $typeDocument->setLabel($_PUT['label'])
                        ->setDescription($_PUT['description']);
                    $this->getEntityManager()->persist($typeDocument);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch (\Exception $e ){
                    $msg = sprintf(_("Impossible de mettre à jour le type de document %s : %s"), $typeDocument, $e->getMessage());
                    $this->getLogger()->error($msg);
                    return $this->getResponseInternalError($msg);
                }

            case 'DELETE' :
                $typeDocumentId = $this->params()->fromQuery('typedocumentid');
                $typeDocument = $this->getActivityService()->getTypeDocument($typeDocumentId, true);
                $this->getEntityManager()->remove($typeDocument);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('le type de document a été supprimé.');

        }

        return $this->getResponseBadRequest("Accès à l'API improbable...");
    }

}