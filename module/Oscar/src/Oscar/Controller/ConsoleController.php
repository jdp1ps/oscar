<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 02/11/15 14:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorActivityJSON;
use Oscar\Connector\ConnectorAuthentificationJSON;
use Oscar\Connector\ConnectorPersonHarpege;
use Oscar\Connector\ConnectorPersonHydrator;
use Oscar\Connector\ConnectorPersonJSON;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Activity;
use Oscar\Entity\Authentification;
use Oscar\Entity\CategoriePrivilege;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\Role;
use Oscar\Entity\RoleOrganization;
use Oscar\Entity\RoleRepository;
use Oscar\Formatter\ConnectorRepportToPlainText;
use Oscar\Provider\Privileges;
use Oscar\Service\ConnectorService;
use Oscar\Service\ShuffleDataService;
use Oscar\Utils\ActivityCSVToObject;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Yaml;
use UnicaenAuth\Authentication\Adapter\Ldap;
use Zend\Crypt\Password\Bcrypt;

class ConsoleController extends AbstractOscarController
{

    ///////////////////////////////////////////////////////////////////////////////////////
    ///
    ///  PATCH
    ///
    public function patchAction()
    {
        $patchName = $this->params()->fromRoute('patchname');
        $method = "patch_" . $patchName;
        if( method_exists($this, $method) ){
            $this->$method();
        } else {
            die("Le patch '$patchName' n'existe pas/plus.");
        }
        die('Execution du patch ' . $patchName);
    }

    public function patch_generatePrivilegesJSON(){

        $privileges = [];

        /** @var Privilege $p */
        foreach($this->getEntityManager()->getRepository(Privilege::class)->findAll() as $p ){
            $privilege = [
                'categorie_id'  => $p->getCategorie()->getId(),
                'code'          => $p->getCode(),
                'libelle'       => $p->getLibelle(),
                'fullcode'      => $p->getFullCode(),
            ];
            $privileges[$p->getFullCode()] = $privilege;
        }
        echo json_encode($privileges);
        die('Génération du fichier JSON à partir des données de la BDD courante.');
    }

    public function patch_checkPrivilegesJSON(){
        $cheminFichier = realpath(__DIR__.'/../../../../../data/privileges.json');
        echo "$cheminFichier\n";
        $datas = json_decode(file_get_contents($cheminFichier));
        var_dump($datas);
        die();
        $donneesFichier = json_decode(file_get_contents());


        /** @var Privilege $p */
        foreach($this->getEntityManager()->getRepository(Privilege::class)->findAll() as $p ){
            $privilege = [
                'categorie_id'  => $p->getCategorie()->getId(),
                'code'          => $p->getCode(),
                'libelle'       => $p->getLibelle(),
                'fullcode'      => $p->getFullCode(),
            ];
            $privileges[$p->getFullCode()] = $privilege;
        }
        echo json_encode($privileges);
        die('Génération du fichier JSON à partir des données de la BDD courante.');
    }

    private function patch_connectors_person(){
        echo "PATCH 'connector_person'\n";
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        /** @var Person $person */
        foreach ($persons as $person) {
            $connectorsPerson = $person->getConnectors();
            if( $person->getConnectorID('rest') ){
                if( count($connectorsPerson) > 1 ){
                    echo "Traitement de $person \n";
                    $newConnector = [
                        'rest' => $connectorsPerson['rest']
                    ];
                    $person->setConnector($newConnector);
                    $this->getEntityManager()->flush($person);
                }
            }
        }
    }

    /**
     * Synchronisation des activités depuis un fichier.
     */
    public function activityFileSyncAction(){
        echo "Synchronisation des activités : \n";

        $file = realpath($this->getRequest()->getParam('fichier'));
        echo "Importation des activités depuis $file : \n";

        $fileExtension = pathinfo($file)['extension'];

        if( $fileExtension == "csv" ){
            $handler = fopen($file, 'r');
            $headers = fgetcsv($handler);

            /** @var RoleRepository $repositoryRole */
            $repositoryRole = $this->getEntityManager()->getRepository(Role::class);

            // Construction de la correspondance role > colonne
            $rolesPersons = $repositoryRole->getRolesAtActivityArray();
            $correspondanceRolesActivites = [];
            /** @var Role $role */
            foreach ($rolesPersons as $role ){
                $correspondanceRolesActivites[$role] = array_search($role, $headers);
            }

            // Construction de la correspondance role > colonne
            $rolesOrganizations = $this->getEntityManager()->getRepository(OrganizationRole::class)->findAll();
            $correspondanceRolesOrga = [];
            /** @var OrganizationRole $role */
            foreach ($rolesOrganizations as $role ){
                $correspondanceRolesOrga[$role->getLabel()] = array_search($role->getLabel(), $headers);
            }

            $converteur = new ActivityCSVToObject($correspondanceRolesActivites, $correspondanceRolesOrga);
            $json = $converteur->convert($file);
        }
        elseif ($fileExtension == "json" ){
            $json = json_decode(file_get_contents($file));
        }
        else {
            die("ERROR : Format non pris en charge.");
        }
        $importer = new ConnectorActivityJSON($json, $this->getEntityManager());
        $repport = $importer->syncAll();

        $output = new ConnectorRepportToPlainText();
        $output->format($repport);
        /****/

    }



    /**
     * Synchronisation des personnes depuis un fichier JSON.
     */
    public function personJsonSyncAction(){
        try {
            $fichier = $this->getRequest()->getParam('fichier');

            if( !$fichier )
                die("Vous devez spécifier le chemin complet vers le fichier JSON");

            echo "Synchronisation depuis le fichier $fichier\n";
            echo "Lecture du fichier $fichier:\n";
            $fileContent = file_get_contents($fichier);
            if( !$fileContent )
                die("Oscar n'a pas réussi à charger le contenu du fichier");

            echo "Conversion du contenu de $fichier:\n";
            $datas = json_decode($fileContent);
            if( !$datas )
                die("les données du fichier $fichier n'ont pas pu être converties.");


            $connector = new ConnectorPersonJSON($datas, $this->getEntityManager());
            $repport = $connector->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            $connectorFormatter->format($repport);
        }
        catch( \Exception $e ){
            die("ERROR : " . $e->getMessage());
        }
    }


    /**
     * Synchronisation des authentifications depuis un fichier JSON.
     */
    public function authentificationsSyncAction(){
        try {
            $jsonpath = $this->getRequest()->getParam('jsonpath');
            $force = $this->getRequest()->getParam('force', false);

            if( !$jsonpath ){
                die("Vous devez spécifier le chemin complet vers le fichier JSON");
            }

            echo "Read $jsonpath:\n";
            $fileContent = file_get_contents($jsonpath);
            if( !$fileContent ){
                die("Oscar n'a pas réussi à charger le contenu du fichier");
            }

            echo "Convert $jsonpath:\n";
            $datas = json_decode($fileContent);
            if( !$datas ){
                die("les données du fichier $jsonpath n'ont pas pu être converties.");
            }

            echo "Process datas...\n";
            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $connectorAuthentification = new ConnectorAuthentificationJSON($datas, $this->getEntityManager(), $bcrypt);
            $repport = $connectorAuthentification->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();
            echo $connectorFormatter->format($repport);

        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }



    public function shuffleAction(){
        /** @var ShuffleDataService $serviceShuffle */
        $serviceShuffle = $this->getServiceLocator()->get('ShuffleService');

        //$serviceShuffle->shufflePersons();
        $serviceShuffle->shuffleOrganizations();
//        $serviceShuffle->shuffleProjects();
//        $serviceShuffle->shuffleActivity();
        // Mélange des personnes

        // Mélange des sociétés

        // Mélange des projets

        // Mélange des activités
        die("SUFFLE");
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    // AUTHENTIFICATION
    //
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Ajoute une authentification.
     */
    public function authAddAction()
    {
        try {
            $login = $this->getRequest()->getParam('login');
            $pass = $this->getRequest()->getParam('pass');
            $displayname = $this->getRequest()->getParam('displayname');
            $email = $this->getRequest()->getParam('email');

            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $auth = new Authentification();
            $auth->setPassword($bcrypt->create($pass));
            $auth->setDisplayName($displayname);
            $auth->setUsername($login);
            $auth->setEmail($email);

            $this->getEntityManager()->persist($auth);
            $this->getEntityManager()->flush();

            die(sprintf('User created : %s(%s) %s:%s', $displayname,$email, $login, $pass));
        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // INDEX DE RECHERCHE
    //
    ////////////////////////////////////////////////////////////////////////////



    public function confAction()
    {
        $what = $this->getRequest()->getParam('what');
        $path = realpath(__DIR__ . '/../../../config');
        $code = Yaml::parse(file_get_contents($path . '/bjyauthorize.yml'));
        echo var_export($code);
    }

    public function searchActivityAction()
    {
        $what = $this->getRequest()->getParam('exp');
        $obj = $this->getRequest()->getParam('obj', 'activity');
        if (!$what) {
            die("Vous ne cherchez rien...\n");
        } else {
            echo sprintf("Recherche '%s' pour les éléments %s dans les activités...\n", $what, strtoupper($obj));
            switch ($obj) {
                case 'activity':
                    $activities = $this->getActivityService()->activitiesByIds($this->getActivityService()->search($what));
                    foreach ($activities as $activity) {
                        echo sprintf(" - [%s] %s\n", $activity->getId(),
                            (string)$activity);
                    }
                    break;
                case 'project':
                    $projects = $this->getProjectService()->search($what)->getQuery()->getResult();
                    foreach ($projects as $project) {
                        echo sprintf(" - [%s] %s\n", $project->getId(),
                            (string)$project);
                    }
                    break;
                case 'person':
                    try {
                        $persons = $this->getPersonService()->search($what)->getQuery()->getResult();
                        foreach ($persons as $person) {
                            echo sprintf(" - [%s] %s\n", $person->getId(),
                                (string)$person);
                        }
                    } catch (\Exception $e) {
                        die(sprintf("Erreur %s, %s", $e->getMessage(),
                            $e->getTraceAsString()));
                    }

                    break;
                default:
                    die("type '$obj' inconnu... les types sont activity, project ou person\n");
            }
        }
    }

    public function updateIndexAction()
    {
        $what = $this->getRequest()->getParam('id');
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($what);

        if (!$activity) {
            die(sprintf("L'activité '%s' n'existe pas/plus", $what));
        } else {
            echo "Mise à jour de $activity\n";
            $this->getActivityService()->searchUpdate($activity);
        }
    }

    public function deleteIndexAction()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $this->getActivityService()->searchDelete($id);
            die("Index à jour.\n");
        } catch (\Exception $e) {
            die(sprintf("ERROR, impossible de supprimer l'index '%s' : \n %s\nDONE\n",
                $id, $e->getMessage()));
        }
    }


    public function buildSearchActivityAction()
    {
        try {
            $this->getActivityService()->searchIndex_rebuild();
        } catch (\Exception $e) {
            die(sprintf("ERROR '%s' : \n %s\nDONE\n", $e->getMessage(),
                $e->getTraceAsString()));
        }
    }

    /** #####################################################################
     *
     * Les scripts qui suivent sont utilisés (ou ont été) pour la maintenance
     * des données.
     *
     * ###################################################################### */



    private function triggerConnector( $connector ){

    }

    /**
     * Lancement de la synchronisation des organisations avec le connecteur spécifié.
     */
    public function organizationSyncAction(){
        $force      = $this->getRequest()->getParam('force');
        $connectorName  = $this->getRequest()->getParam('connectorkey');

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServiceLocator()->get('ConnectorService');

        $connector = $connectorService->getConnector('organization.' . $connectorName);
        echo "Execution de 'organization.$connectorName' (".($force ? 'FORCE' : 'NORMAL').") : \n";
        try {
            /** @var ConnectorRepport $repport */
            $repport = $connector->execute($force);
        } catch( \Exception $e ){
            die($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        foreach( $repport->getRepportStates() as $type => $out ){
            echo "Opération " . strtoupper($type) . " : \n";
            if( $type == "notices" ){
                echo " - " . count($out) . " notice(s) - Rien à faire\n";
            } else {
                foreach ($out as $line) {
                    echo date('Y-m-d H:i:s',
                            $line['time']) . "\t" . $line['message'] . "\n";
                }
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // PERSON(S)
    //
    public function personsSearchConnectorAction(){
        $connectorName = $this->getRequest()->getParam('connector');
        $value = $this->getRequest()->getParam('value');

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        try {
            $persons = $personRepository->getPersonsByConnectorID($connectorName, $value);
            foreach( $persons as $person ){
                echo sprintf(" - [%s] %s (%s)\n", $person->getId(), $person, $person->getEmail());
            }
        } catch( \Exception $ex ){
            echo "############################ " . $ex->getMessage() . "\n"
                . $ex->getTraceAsString();
        }

        echo $connectorName." = ".$value;
    }



    public function personSyncAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            if( !$id ){
                throw new \Exception("Vous devez utiliser un ID oscar");
            }
            /**
             * @var $person Person
             */
            $person = $this->getEntityManager()->getRepository(Person::class)->find($id);

            foreach( $this->getConfiguration('oscar.connectors.person') as $key=>$getConnector ){
                if( NULL === ($id = $person->getConnectorID($key)) ){
                    throw new Exception(sprintf('Pas de connecteur %s pour %s ...', $key, $person));
                }

//                $person = $this->getEntityManager()->getRepository(Person::class)->getPersonByConnectorID('toto', $person->getConnectorID($key));
                /** @var ConnectorPersonHarpege $connector */
                $connector = $getConnector();

                $connector->setServiceLocator($this->getServiceLocator());

                $connector->syncPerson($person);

            }
        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    public function personsSyncAction()
    {
        $force = $this->getRequest()->getParam('force', false);
        $verbose = $this->getRequest()->getParam('verbose', false);
        $connectorName  = $this->getRequest()->getParam('connectorkey');
          echo "Execution de 'person.$connectorName' (".($force ? 'FORCE' : 'NORMAL').") : \n";



        try {
            /** @var ConnectorService $connectorService */
            $connectorService = $this->getServiceLocator()->get('ConnectorService');

            // Récupération du connector
            $connector = $connectorService->getConnector('person.' . $connectorName);

            /** @var ConnectorRepport $repport */
            $repport = $connector->execute($force);

            foreach( $repport->getRepportStates() as $type => $out ){
                echo "Opération " . strtoupper($type) . " : \n";
                foreach( $out as $line ){
                    echo date('Y-m-d H:i:s', $line['time']) . "\t" . $line['message'] . "\n";
                }
            }

        } catch( \Exception $e ){
          echo $e->getMessage() . "\n";
          if( $verbose ){
            echo $e->getTraceAsString()."\n";
          }
        }
    }

    public function checkPrivilegesAction(){
        echo "Vérification des privilèges installés...\n";
        $class = new \ReflectionClass(Privileges::class);
        $infile = $class->getConstants();

        try {
            $privileges = $this->getEntityManager()
                ->getRepository(Privilege::class)
                ->createQueryBuilder('p')
//                ->indexBy('code', 'p.code')
                ->leftJoin('p.categorie', 'c')
                ->addOrderBy('c.id')
                ->getQuery()->getResult();

            $categorie = null;
            /** @var Privilege $privilege */
            foreach( $privileges as $privilege ){
                if( $categorie !== $privilege->getCategorie() ){
                    $categorie = $privilege->getCategorie();
                }
                $keyFile = $privilege->getCategorie()->getCode() . '_' . $privilege->getCode();
                $keyFile = strtoupper(str_replace('-', '_', $keyFile));
                if( array_key_exists($keyFile, $infile) ){
                    unset($infile[$keyFile]);
                }
                //echo (array_key_exists($keyFile, $infile) ? '/!\\' : ' - ') . " $keyFile $privilege\n";
            }
            if( count($infile) ){
                echo " ! DROITS MANQUANTS : \n";
                foreach( $infile as $key=>$droit ){
                    echo " - $key = $droit\n";
                }
            }
        } catch( \Exception $e ){
            echo "!!!" . $e->getMessage()." !!!\n";
            echo $e->getTraceAsString();
        }
    }

    private function getMissingPrivilege(){

    }

    public function authPassAction()
    {
        try {
            $login = $this->getRequest()->getParam('login');
            $pass = $this->getRequest()->getParam('newpass');

            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $login]);
            $auth->setPassword($bcrypt->create($pass));
            $this->getEntityManager()->flush();

            die(sprintf("User pass updated %s:%s\n", $login, $pass));
        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    public function authPromoteAction()
    {
        try {
            $loginStr = $this->getRequest()->getParam('login');
            $roleStr = $this->getRequest()->getParam('role');


            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $loginStr]);
            if( !$auth ){
                die("Aucune compte d'authentification d'a pour identifiant '$loginStr'");
            }
            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $roleStr ]);

            $userId =  $auth->getId();
            $roleId = $role->getId();

            $query = $this->getEntityManager()->createNativeQuery("INSERT INTO authentification_role VALUES($userId, $roleId)", new Query\ResultSetMapping());
            $query->execute();

        } catch( \Exception $ex ){
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * Recalcule les status.
     */
    public function recalculateStatusAction()
    {
        $this->getLogger()->info("Recalcule des status");
        $now = new \DateTime();
        /** @var Activity $activity */
        foreach( $this->getEntityManager()->getRepository(Activity::class)->findAll() as $activity ){
            if( $activity->getDateStart() && $activity->getDateEnd() ){
                $text = sprintf("[%s] %s > %s", $activity->getOscarNum(), $activity->getDateStart()->format('Y-m-d'), $activity->getDateEnd()->format('Y-m-d'));
                if( $activity->getDateSigned() ){
                    if( $activity->getDateEnd() < $now ){
                        if( $activity->getStatus() != Activity::STATUS_TERMINATED ){
                            $this->getLogger()->warn($text);
                            $activity->setStatus(Activity::STATUS_TERMINATED);
                            $this->getEntityManager()->flush($activity);
                        }
                    } else {
                        if( $activity->getStatus() != Activity::STATUS_ACTIVE ){
                            $activity->setStatus(Activity::STATUS_ACTIVE);
                            $this->getEntityManager()->flush($activity);
                            $this->getLogger()->info($text);
                        }
                    }
                } else {
                    if( $activity->getDateEnd() && $activity->getDateEnd() < $now ){
                        if( $activity->getStatus() != Activity::STATUS_ABORDED ) {
                            $this->getLogger()->error($text .' ' . $activity->getStatusLabel());
                            $activity->setStatus(Activity::STATUS_ABORDED);
                            $this->getEntityManager()->flush($activity);
                        }
                    } else {
                        if( $activity->getStatus() != Activity::STATUS_PROGRESS ) {
                            $activity->setStatus(Activity::STATUS_PROGRESS);
                            $this->getEntityManager()->flush($activity);
                            $this->getLogger()->debug($text);
                        }
                    }
                }
            }
//            $this->getLogger()->info($activity);
        }
    }

    public function checkAuthentificationAction(){
        echo "START : Test de configuration\n";
        $login = $this->getRequest()->getParam('login');
        $pass = $this->getRequest()->getParam('pass');

        try {
            $ldapOpt = $this->getServiceLocator()->get('unicaen-app_module_options')->getLdap();
            foreach ($ldapOpt['connection'] as $name => $connection) {
                $options[$name] = $connection['params'];
            }
            /** @var Ldap $ldapUnicaenAuth */
            $ldapUnicaenAuth = new Ldap();
            $ldapUnicaenAuth->setServiceManager($this->getServiceLocator());
            $ldapUnicaenAuth->setEventManager($this->getEventManager());
            $ldapUnicaenAuth->authenticateUsername($login, $pass);
            echo " # ACCOUND OBJECT : \n";
            //var_dump($ldapUnicaenAuth->getLdapAuthAdapter()->getAccountObject());
            var_dump($ldapUnicaenAuth->getLdapAuthAdapter()->getIdentity());

            /** @var \UnicaenApp\Mapper\Ldap\People $ldapmapper */
            $ldapmapper = $this->getServiceLocator()->get('ldap_people_service')->getMapper();

            $people = $ldapmapper->findOneByUsername($ldapUnicaenAuth->getLdapAuthAdapter()->getIdentity());
            var_dump($people);

            /*
            $ldapAuthAdapter = new \Zend\Authentication\Adapter\Ldap($options); // NB: array(array)
            $result = $ldapAuthAdapter->setPassword($pass)->setUsername($login)->authenticate();

            if( $result->isValid() ){
                echo "Authentification OK : \n";
                var_dump($result);
                echo "Get userContext : \n";
                $context = $this->getServiceLocator()->get('userContext');
            } else {
                echo "Authentification FAIL : \n";
                var_dump($result);
            }
            */

        } catch( \Exception $e ){
            echo "ERROR : " . $e->getMessage() . "\n";
        }


        echo "DONE\n";
    }
}
