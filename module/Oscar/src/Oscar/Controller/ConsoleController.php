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
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Console;
use Zend\Console\Prompt\Confirm;
use Zend\Console\Prompt\Line;
use Zend\Console\Prompt\Password;
use Zend\Console\Prompt\PromptInterface;
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
    }

    public function patch_test()
    {
        echo "TEST:\n";

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
        $cheminFichier = realpath(__DIR__.'/../../../../../install/privileges.json');
        if( !file_exists($cheminFichier) ){
            die("ERREUR : Fichier introuvable\n");
        }
        $contenuFichier = file_get_contents($cheminFichier);
        if( !$contenuFichier ){
            die("ERREUR : Impossible de lire le fichier : $contenuFichier\n");
        }
        $datas = json_decode($contenuFichier);
        if( !$datas ){
            die("ERREUR : Impossible de traiter les données du fichier\n");
        }
        $toRemove = [];

        /** @var Privilege $p */
        foreach($this->getEntityManager()->getRepository(Privilege::class)->findAll() as $p ){
            $property = $p->getFullCode();
            if( property_exists($datas, $property) ){
                unset($datas->$property);
            } else {
                $toRemove[$p->getFullCode()] = $p;
            }
        }
        if( count(get_object_vars($datas)) ){
            echo "Le(s) privilège(s) suivant(s) vont/va être ajouté(s) ? \n";
            $created = [];
            foreach ($datas as $fullCode=>$privilegeData){
                $p = new Privilege();
                $this->getEntityManager()->persist($p);
                $p->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($privilegeData->categorie_id))
                    ->setCode($privilegeData->code)
                    ->setLibelle($privilegeData->libelle);
                $created[] = $p;
                echo sprintf(" - '%s' : %s\n", $p->getFullCode(), $p->getLibelle());
            }
            $confirm = new Confirm("Confirmer la création ? (Y/n) : ");
            if( $confirm->show() ) {
                try {
                    $this->getEntityManager()->flush($created);
                    echo sprintf("%s objet(s) créé(s).\n", count($created));
                } catch (\Exception $e ){
                    echo sprintf("Problème pendant l'enregistrement des privilèges manquants : %s.\n", $e->getMessage());
                }
            }
        } else {
            echo " - Aucun privilèges manquants\n";
        }

        if( count($toRemove) ){
            echo "\nIl y'a des privilèges obsolètes : \n";
            foreach ($toRemove as $fullCode=>$privilege ){
                echo sprintf(" - '%s' : %s\n", $fullCode, $p->getLibelle());
            }
            $confirm = new Confirm("Supprimer les privilèges obsolètes ? (Y/n) : ");
            if( $confirm->show() ) {
                try {
                    foreach ($toRemove as $fullCode=>$privilege ){
                        $this->getEntityManager()->remove($p);
                        $this->getEntityManager()->flush($p);
                        echo sprintf(" - '%s' supprimé\n", $fullCode);
                    }

                } catch (\Exception $e ){
                    echo sprintf("Problème pendant la suppression : %s.\n", $e->getMessage());
                }
            }
        } else {
            echo " - Aucun privilèges obsolètes\n";
        }
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


    protected function consoleError($msg){
        $this->getConsole()->writeLine($msg, ColorInterface::WHITE, ColorInterface::RED);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // AUTHENTIFICATION
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Affiche la liste des authentification présentes dans Oscar.
     */
    public function authListAction()
    {
        $authentifications = $this->getEntityManager()
            ->getRepository(Authentification::class)
            ->findAll();

        /** @var Authentification $authentification */
        foreach ($authentifications as $authentification) {
            $this->getConsole()->write($authentification->getId()."\t", ColorInterface::CYAN);
            $this->getConsole()->write($authentification->getUsername());
            $this->getConsole()->writeLine(sprintf("(%s)", $authentification->getEmail()), ColorInterface::GRAY);
        }
    }

    /**
     * Ajoute une authentification.
     */
    public function authAddAction()
    {
        try {
            $login = $this->getRequest()->getParam('login');
            if( !$login ){
                $login = Line::prompt("Entrez l'identifiant : ", true, 64);
            }

            // Identifiant trop court
            if( strlen($login) < 4 ){
                $this->consoleError("L'identifiant doit avoir au moins 4 caractères.");
                return;
            }

            // Identifiant déjà utilisé
            $checkLogin = $this->getEntityManager()
                ->getRepository(Authentification::class)
                ->findBy([
                    'username' => $login
                ]);

            if( $checkLogin ){
                $this->consoleError(sprintf("L'identifiant %s est déjà utilisé.", $login));
                return;
            }



            $displayname = $this->getRequest()->getParam('displayname');
            if( !$displayname ){
                $displayname = Line::prompt("Nom affiché ($login) : ", true, 64);
                $displayname = $displayname ? $displayname : $login;
            }


            $email = $this->getRequest()->getParam('email');
            if( !$email ){
                $email = Line::prompt("Email (éviter de laisser vide) : ", true, 256);
            }

            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $password = Password::prompt('Entrez le mot de passe (8 caractères minimum): ', true);

            if( strlen($password) < 8 ){
                $this->getConsole()->writeLine("Le mot de passe est trop court :", ColorInterface::WHITE, ColorInterface::RED);
                return;
            }

            // Récape :
            $this->getConsole()->writeLine("L'utilisateur suivant va être créé : ");

            $this->getConsole()->write("Identifiant de connexion : ", ColorInterface::GRAY);
            $this->getConsole()->writeLine($login, ColorInterface::WHITE);

            $this->getConsole()->write("Nom affiché : ", ColorInterface::GRAY);
            $this->getConsole()->writeLine($displayname, ColorInterface::WHITE);

            $this->getConsole()->write("Courriel : ", ColorInterface::GRAY);
            $this->getConsole()->writeLine($email, ColorInterface::WHITE);

            $confirm = Confirm::prompt("Créer l'utilisateur ? ");
            if( $confirm ){
                $auth = new Authentification();
                $auth->setPassword($bcrypt->create($password));
                $auth->setDisplayName($displayname);
                $auth->setUsername($login);
                $auth->setEmail($email);
                $this->getEntityManager()->persist($auth);
                $this->getEntityManager()->flush();
                $this->getConsole()->writeLine(sprintf("%s a été créé avec succès.", $login), ColorInterface::WHITE, ColorInterface::GREEN);
            }
            return;
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

    /**
     * @return AdapterInterface
     */
    protected function getConsole(){
        return $this->getServiceLocator()->get('console');
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // PERSON(S)
    //
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Recherche une personne dans la base de donnée à partir avec la valeur
     * donnée pour un connecteur donné.
     */
    public function personsSearchConnectorAction(){
        $connectorName = $this->getRequest()->getParam('connector');
        $value = $this->getRequest()->getParam('value');

        $this->getConsole()->clear();
        $this->getConsole()->writeLine(sprintf("Recherche de pour %s = '%s' : ", $connectorName, $value), ColorInterface::GRAY);

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        try {
            $persons = $personRepository->getPersonsByConnectorID($connectorName, $value);
            if( count($persons) == 0 ){
                $this->getConsole()->writeLine(sprintf("Aucun résultat pour %s = '%s'", $connectorName, $value), ColorInterface::YELLOW);
            }

            foreach( $persons as $person ){
                $this->getConsole()->write(sprintf(" [%s] ", $person->getId()), ColorInterface::CYAN);
                $this->getConsole()->write(sprintf("%s", $person), ColorInterface::NORMAL);
                $this->getConsole()->writeLine(sprintf(" (%s)", $person->getEmail()), ColorInterface::GRAY);
            }
        } catch( \Exception $ex ){

            echo "############################ " . $ex->getMessage() . "\n"
                . $ex->getTraceAsString();
        }
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

    ////////////////////////////////////////////////////////////////////////////
    //
    // AUTHENTIFICATION
    //
    ////////////////////////////////////////////////////////////////////////////


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
        }
    }
}
