<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 02/11/15 14:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorActivityCSVWithConf;
use Oscar\Connector\ConnectorActivityJSON;
use Oscar\Connector\ConnectorAuthentificationJSON;
use Oscar\Connector\ConnectorOrganizationJSON;
use Oscar\Connector\ConnectorPersonHarpege;
use Oscar\Connector\ConnectorPersonHydrator;
use Oscar\Connector\ConnectorPersonJSON;
use Oscar\Connector\ConnectorRepport;
use Oscar\Connector\GetJsonDataFromFileStrategy;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\CategoriePrivilege;
use Oscar\Entity\Notification;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\Role;
use Oscar\Entity\RoleOrganization;
use Oscar\Entity\RoleRepository;
use Oscar\Exception\OscarException;
use Oscar\Formatter\ConnectorRepportToPlainText;
use Oscar\OscarVersion;
use Oscar\Provider\Privileges;
use Oscar\Service\ConnectorService;
use Oscar\Service\NotificationService;
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
use Zend\Console\Prompt\Select;
use Zend\Crypt\Password\Bcrypt;

class ConsoleController extends AbstractOscarController
{
    public function patch_debug()
    {
        /*$privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();

        foreach ( $privileges as $p ){
            echo $p->getId()." - ";
        }

        //
        $privilege = new Privilege();
        $privilege->setLibelle("TEST_PRIVILEGE")
            ->setCode("TEST_PRIVILEGE");
        $this->getEntityManager()->persist($privilege);
        $this->getEntityManager()->flush($privilege);

        die("OK");
        */
    }


    public function patch_fixSequenceAutoNum()
    {
        $sequences = [
            "activity",
            "activitydate",
            "activityorganization",
            "activitypayment",
            "activityperson",
            "activitytype",
            "administrativedocument",
            "authentification",
            "contractdocument",
            "currency",
            "datetype",
            "discipline",
            "logactivity",
            "notification",
            "notificationperson",
            "organization",
            "organizationperson",
            "organizationrole",
            "privilege",
            "project",
            "person",
        ];

        foreach ($sequences as $sequence) {
            $result = new Query\ResultSetMapping();
            echo "Update numeration for $sequence\n";
            $this->getEntityManager()->createNativeQuery(
                "select setval('" . $sequence . "_id_seq',(SELECT COALESCE((SELECT MAX(id)+1 FROM " . $sequence . "), 1)), false);",
                $result)->execute();
        }
    }


    /**
     * Retourne la liste des clefs utilisateurs disposant du privilège.
     */
    public function tokensWithPrivilegesAction()
    {
        $privilege = $this->params('privilege');

        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getEntityManager()->getRepository(Role::class);

        $privilege = $this->getEntityManager()->getRepository(Privilege::class)->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->where("CONCAT(c.code,'-',p.code) = :code")
            ->setParameter('code', $privilege)
            ->getQuery()
            ->getSingleResult();

        $roles = [];
        foreach ($privilege->getRole() as $r) {
            $roles[] = $r;
        }

        $authentifications = $this->getEntityManager()->getRepository(Authentification::class)
            ->createQueryBuilder('a')
            /* ->where('a.roles IN (:roles)')
             ->setParameter('roles', $roles) /****/
            ->getQuery()
            ->getResult();

        $secrets = [];
        /** @var Authentification $a */
        foreach ($authentifications as $a) {
            foreach ($roles as $r) {
                if ($a->getSecret() && !in_array($a->getSecret(),
                        $secrets) && in_array($r, $a->getRoles())) {
                    $secrets[] = $a->getSecret();
                }
            }
        }
        echo json_encode($secrets);
    }


    public function tokenHasPrivilegeAction()
    {
        $token = $this->params('token');
        $privilege = $this->params('privilege');

        /** @var Authentification $auth */
        $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['secret' => $token]);
        /** @var Role $role */
        foreach ($auth->getRoles() as $role) {
            /** @var Privilege $privilege */
            foreach ($role->getPrivileges() as $p) {
                if ($p->getFullCode() == $privilege) {
                    return json_encode(true);
                }
            }
        }

        return json_encode(false);
    }


    public function jsonUserAction()
    {
        $token = $this->params('token');
        try {
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['secret' => $token]);
            if (!$auth) {
                throw new \Exception("Not Auth");
            }
            $person = $this->getEntityManager()->getRepository(Person::class)->findOneBy(['ladapLogin' => $auth->getUsername()]);
            if ($person) {
                $data = [
                    "id" => $person->getId(),
                    "username" => $auth->getUsername(),
                    "fullname" => (string)$person
                ];
                echo json_encode($data);
            }
            exit(1);
        } catch (\Exception $e) {
            die();
        }
    }

    public function jsonNotificationsAction()
    {
        $ids = explode(',', $this->params('ids'));
        try {
            $notifications = $this->getEntityManager()->createQueryBuilder()
                ->select('n')
                ->from(Notification::class, 'n')
                ->where('n.id IN(:ids)')
                ->getQuery()
                ->setParameter('ids', $ids)
                ->getResult();

            $data = [];
            /** @var Notification $notification */
            foreach ($notifications as $notification) {
                foreach ($notification->getPersons() as $person) {
                    $data[] = [
                        'id' => $notification->getId(),
                        'message' => $notification->getMessage(),
                        'object' => $notification->getObject(),
                        'objectid' => $notification->getObjectId(),
                        'recipientid' => $person->getId(),
                        'context' => $notification->getContext(),
                        'serie' => $notification->getSerie(),
                        'date' => $notification->getDateEffective()->format('Y-m-d'),
                        'hash' => $notification->getHash(),
                    ];
                }
            }
            echo json_encode($data);
        } catch (\Exception $e) {
            die("ERROR! : " . $e->getMessage());
        }
    }

    /**
     * Affiche la liste des notifications
     */
    public function notificationsActivityListAction()
    {

    }

    public function notificationsPersonAction()
    {
        $personId = $this->params('idperson');
        $person = $this->getPersonService()->getPerson($personId);
        $this->getNotificationService()->generateNotificationsPerson($person);
        die("$person");
    }

//'route' => 'oscar notifications:person:purge <idperson> <idactivity></idactivity>',

    public function notificationsPersonActivityPurgeAction()
    {
        $personId = $this->params('idperson');
        $activityId = $this->params('idactivity');

        $person = $this->getPersonService()->getPerson($personId);
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activityId);

        $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $person);
    }

    public function notificationsActivityGenerateAction()
    {
        $id = $this->params('idactivity');

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');

        if ($id == 'all') {
            $notificationService->generateNotificationsActivities(true);
        } else {
            /** @var Activity $activity */
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($id);

            if (!$activity) {
                $this->consoleError("Impossible de charger l'activité '$id'");

                return;
            }
            $notificationService->generateNotificationsForActivity($activity,
                true);
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////
    ///
    ///  PATCH
    ///
    public function patchAction()
    {
        $patchName = $this->params()->fromRoute('patchname');
        $method = "patch_" . $patchName;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            die("Le patch '$patchName' n'existe pas/plus.");
        }
    }

    public function patch_test()
    {
        echo "TEST:\n";

    }

    private function getReadablePath($path)
    {
        $realpath = realpath($path);

        if (!$realpath) {
            throw new OscarException(sprintf("Le chemin '%s' n'a aucun sens...",
                $path));
        }


        if (!is_file($realpath)) {
            throw new OscarException(sprintf("Le chemin '%s' n'est pas un fichier... faites un effort.",
                $realpath));
        }

        if (!is_readable($realpath)) {
            throw new OscarException(sprintf("Le chemin '%s' n'est pas lisible....",
                $realpath));
        }

        return $realpath;
    }

    /**
     * Procédure d'importation initiale des activités dans Oscar.
     */
    public function importActivity2Action()
    {
        // Fichiers
        try {
            $sourceFilePath = $this->getReadablePath($this->params('fichier'));
            $configurationFilePath = $this->getReadablePath($this->params('config'));
            $skip = 1;


            $configuration = require($configurationFilePath);
            $source = fopen($sourceFilePath, 'r');

            while ($skip > 0) {
                fgetcsv($source);
                $skip--;
            }

            $sync = new ConnectorActivityCSVWithConf($source, $configuration,
                $this->getEntityManager());
            echo json_encode($sync->syncAll());

        } catch (\Exception $e) {
            $this->consoleError($e->getMessage());
        }
    }

    public function patch_generatePrivilegesJSON()
    {

        $privileges = [];
        /** @var Privilege $p */
        foreach ($this->getEntityManager()->getRepository(Privilege::class)->findAll() as $p) {
            $privilege = [
                'categorie_id' => $p->getCategorie()->getId(),
                'code' => $p->getCode(),
                'libelle' => $p->getLibelle(),
                'fullcode' => $p->getFullCode(),
            ];
            $privileges[$p->getFullCode()] = $privilege;
        }
        echo json_encode($privileges);
        die('Génération du fichier JSON à partir des données de la BDD courante.');
    }




    ////////////////////////////////////////////////////////////////////////////
    /// PRIVILEGES
    ////////////////////////////////////////////////////////////////////////////
    protected $reuireProperties = ['category_id', 'spot', 'code', 'libelle'];
    protected $cacheCategory = [];

    protected function getRootByFullCode($fullCode)
    {

        if (!array_key_exists($fullCode, $this->cacheCategory)) {
            $re = '/(\w*)-(.*)/';
            preg_match_all($re, $fullCode, $matches, PREG_SET_ORDER, 0);
            $codeCategory = $matches[0][1];
            $codePrivilege = $matches[0][2];
            $category = $this->getEntityManager()->getRepository(CategoriePrivilege::class)->findOneBy([
                'code' => $codeCategory
            ]);
            try {
                $this->cacheCategory[$fullCode] = $this->getEntityManager()->getRepository(Privilege::class)->findOneBy([
                    'code' => $codePrivilege,
                    'categorie' => $category
                ]);
            } catch (\Exception $e) {
                $this->cacheCategory[$fullCode] = null;
            }
        }

        return $this->cacheCategory[$fullCode];
    }

    protected function updatePrivilegeWitDatas(Privilege $privilege, $stdObject)
    {

        // On teste si le configuration est propre
        foreach ($this->requireProperties as $requireProperty) {
            if (!property_exists($stdObject, $requireProperty)) {
                throw new \Exception("La clef '$requireProperty' est manquant dans la configuration : " . print_r($stdObject,
                        true));
            }
        }

        $flush = false;

        if ($privilege->getCategorie()->getId() != $stdObject->category_id) {
            try {
                $privilege->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($stdObject->category_id));
            } catch (\Exception $e) {
                throw new \Exception("La catégorie " . $stdObject->category_id . " n'existe pas.");
            }
            $flush = true;
        }

        $privilegeRoot = $privilege->getRoot() ? $privilege->getRoot()->getFullCode() : null;

        if ($stdObject->root && $privilegeRoot != $stdObject->root ) {
            $privilege->setRoot($this->getRootByFullCode($stdObject->root));
            $flush = true;
        }

        if ($privilege->getCode() != $stdObject->code) {
            $privilege->setCode($stdObject->code);
            $flush = true;
        }

        if ($privilege->getSpot() != $stdObject->spot) {
            $privilege->setSpot($stdObject->spot);
            $flush = true;
        }

        if ($privilege->getLibelle() != $stdObject->libelle) {
            $privilege->setLibelle($stdObject->libelle);
            $flush = true;
        }

        if ($flush == true) {
            return $privilege;
        }


        return false;

    }

    public function versionAction()
    {
        $this->consoleKeyValue("Version", OscarVersion::getBuild());
    }

    public function patch_checkPrivilegesJSON()
    {
        $cheminFichier = realpath(__DIR__ . '/../../../../../install/privileges.json');
        if (!file_exists($cheminFichier)) {
            die("ERREUR : Fichier introuvable\n");
        }
        $contenuFichier = file_get_contents($cheminFichier);
        if (!$contenuFichier) {
            die("ERREUR : Impossible de lire le fichier : $contenuFichier\n");
        }
        $datas = json_decode($contenuFichier);
        if (!$datas) {
            die("ERREUR : Impossible de traiter les données du fichier ". json_last_error_msg()."\n");
        }

        // Mise à jour de la séquence
        $rsm = new Query\ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery("select setval('privilege_id_seq',(select max(id)+1 from privilege), false)", $rsm);
        $query->execute();

        $toRemove = [];
        $toAdd = [];
        $toUpdate = [];
        $verbose = false;

        $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();

        /** @var Privilege $p */
        foreach ($privileges as $p) {
            $property = $p->getFullCode();
            $do = "";
            try {
                if (property_exists($datas, $property)) {
                    // Mise à jour
                    $updatable = $this->updatePrivilegeWitDatas($p,
                        $datas->$property);
                    if (false !== $updatable) {
                        if ($verbose) {
                            $this->consoleUpdateToDo($property . ' va être mis à jour');
                        }
                        $toUpdate[] = $updatable;
                    } else {
                        if ($verbose) {
                            $this->consoleNothingToDo($property . ' est à jour');
                        }
                    }
                    unset($datas->$property);
                } else {
                    $toRemove[] = $p;
                    if ($verbose) {
                        $this->consoleDeleteToDo($property . ' va être supprimé');
                    }
                }
            } catch (\Exception $e) {
                $this->consoleError($e->getMessage());
                continue;
            }

        }

        $this->consoleHeader("Opérations de maintenance à faire : ");
        $anythingToDo = false;

        if (count($toUpdate)) {
            $this->consoleUpdateToDo("Il y'a " . count($toUpdate) . " privilèges à mettre à jour");
            $anythingToDo = true;
        }

        if (count($toRemove)) {
            $this->consoleDeleteToDo("Il y'a " . count($toRemove) . " privilèges à supprimer");
            $anythingToDo = true;
        }
        if (count(get_object_vars($datas))) {
            $this->consoleUpdateToDo("Il y'a " . count(get_object_vars($datas)) . " privilèges à ajouter");
            $anythingToDo = true;
        }

        if (!$anythingToDo) {
            $this->consoleSuccess("Les privilèges sont à jour.");

            return;
        }

        $confirm = new Confirm("Continuer ? (Y/n) : ");
        if (!$confirm->show()) {
            return;
        }


	foreach ($datas as $fullCode => $privilegeData) {
		try {
			if( !property_exists($privilegeData, 'category_id')){
				$this->consoleError('Propriété categorie_id manquante dans la configuration : ' . print_r($privilegeData, true));
				continue;
			}
            $newPrivilege = new Privilege();
            $this->getEntityManager()->persist($newPrivilege);
            $newPrivilege->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($privilegeData->category_id))
                ->setCode($privilegeData->code)
                ->setSpot($privilegeData->spot)
                ->setLibelle($privilegeData->libelle);
            
                $this->getEntityManager()->flush($newPrivilege);
                $this->consoleSuccess("Le privilège " . $privilegeData->code . " a bien été créé.");
            } catch (\Exception $e) {
                $this->consoleError("Impossible de créé le privilège " . $privilegeData->code . " : " . $e->getMessage());

            }
        }

        foreach ($toRemove as $privilege) {
            try {
                $this->getEntityManager()->remove($privilege);
                $this->getEntityManager()->flush($privilege);
                $this->consoleSuccess("Le privilège " . $privilegeData->code . " a bien été supprimé.");
            } catch (\Exception $e) {
                $this->consoleError("Impossible de supprimer le privilège " . $privilegeData->code . " : " . $e->getMessage());
            }
        }

        /** @var Privilege $privilege */
        foreach ($toUpdate as $privilege) {
            try {
                $this->getEntityManager()->flush($privilege);
                $this->consoleSuccess("Le privilège " . $privilege->getCode() . " a bien été mis à jour.");
            } catch (\Exception $e) {
                $this->consoleError("Impossible de mettre à jour le privilège " . $privilege->getCode() . " : " . $e->getMessage());
            }
        }


    }

    private function patch_connectors_person()
    {
        echo "PATCH 'connector_person'\n";
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        /** @var Person $person */
        foreach ($persons as $person) {
            $connectorsPerson = $person->getConnectors();
            if ($person->getConnectorID('rest')) {
                if (count($connectorsPerson) > 1) {
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
     * Synchronisation des personnes depuis un fichier JSON.
     */
    public function personJsonSyncAction()
    {
        try {
            $fichier = $this->getRequest()->getParam('fichier');

            if (!$fichier) {
                die("Vous devez spécifier le chemin complet vers le fichier JSON");
            }

            echo "Synchronisation depuis le fichier $fichier\n";
            echo "Lecture du fichier $fichier:\n";
            $fileContent = file_get_contents($fichier);
            if (!$fileContent) {
                die("Oscar n'a pas réussi à charger le contenu du fichier");
            }

            echo "Conversion du contenu de $fichier:\n";
            $datas = json_decode($fileContent);
            if (!$datas) {
                die("les données du fichier $fichier n'ont pas pu être converties.");
            }


            $connector = new ConnectorPersonJSON($datas,
                $this->getEntityManager());
            $repport = $connector->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            $connectorFormatter->format($repport);
        } catch (\Exception $e) {
            die("ERROR : " . $e->getMessage() . $e->getTraceAsString());
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SYNCHRONISATION DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /////////////////////////////////////////////////////////////////////////////////////////////////////// ORGANIZATION
    /**
     * Synchronisation des organisations depuis un fichier JSON.
     */
    public function organizationJsonSyncAction()
    {
        try {
            $fichier = $this->getRequest()->getParam('fichier');

            if (!$fichier)
                die("Vous devez spécifier le chemin complet vers le fichier JSON");

            echo "Synchronisation depuis le fichier $fichier\n";
            $sourceJSONFile = new GetJsonDataFromFileStrategy($fichier);
            try {
                $datas = $sourceJSONFile->getAll();
            } catch (\Exception $e) {
                die("ERR : Impossible de charger les ogranizations depuis $fichier : " . $e->getMessage());
            }

            $connector = new ConnectorOrganizationJSON($datas,
                $this->getEntityManager(), 'json');
            $repport = $connector->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            $connectorFormatter->format($repport);
        } catch (\Exception $e) {
            die("ERR : " . $e->getMessage());
        }
    }

    ///
    /////////////////////////////////////////////////////////////////////////////////////////////////////////// ACTIVITÉ
    /**
     * Synchronisation des activités depuis un fichier.
     */
    public function activityFileSyncAction()
    {
        echo "Synchronisation des activités : \n";

        try {
            $file = $this->getReadablePath($this->getRequest()->getParam('fichier'));
        } catch (\Exception $e ){
            $this->consoleError("Impossible de lire le fichier source : " . $e->getMessage());
        }

        echo "Importation des activités depuis $file : \n";

        $options = [
            'create-missing-project' => $this->getRequest()->getParam('create-missing-project',
                false),
            'create-missing-person' => $this->getRequest()->getParam('create-missing-person',
                false),
            'create-missing-person-role' => $this->getRequest()->getParam('create-missing-person-role',
                false),
            'create-missing-organization' => $this->getRequest()->getParam('create-missing-organization',
                false),
            'create-missing-organization-role' => $this->getRequest()->getParam('create-missing-organization-role',
                false),
            'create-missing-activity-type' => $this->getRequest()->getParam('create-missing-activity-type',
                false),
        ];

        $fileExtension = pathinfo($file)['extension'];

        if ($fileExtension == "csv") {
            $handler = fopen($file, 'r');
            $headers = fgetcsv($handler);

            /** @var RoleRepository $repositoryRole */
            $repositoryRole = $this->getEntityManager()->getRepository(Role::class);

            // Construction de la correspondance role > colonne
            $rolesPersons = $repositoryRole->getRolesAtActivityArray();
            $correspondanceRolesActivites = [];
            /** @var Role $role */
            foreach ($rolesPersons as $role) {
                $correspondanceRolesActivites[$role] = array_search($role,
                    $headers);
            }

            // Construction de la correspondance role > colonne
            $rolesOrganizations = $this->getEntityManager()->getRepository(OrganizationRole::class)->findAll();
            $correspondanceRolesOrga = [];
            /** @var OrganizationRole $role */
            foreach ($rolesOrganizations as $role) {
                $correspondanceRolesOrga[$role->getLabel()] = array_search($role->getLabel(),
                    $headers);
            }

            $converteur = new ActivityCSVToObject($correspondanceRolesActivites,
                $correspondanceRolesOrga);
            $json = $converteur->convert($file);
        } elseif ($fileExtension == "json") {
            $json = json_decode(file_get_contents($file));
        } else {
            die("ERROR : Format non pris en charge.");
        }

        $importer = new ConnectorActivityJSON($json, $this->getEntityManager(),
            $options);
        $repport = $importer->syncAll();

        $output = new ConnectorRepportToPlainText();
        $output->format($repport);
        /****/

    }


    /////////////////////////////////////////////////////////////////////////////////////////////////// AUTHENTIFICATION

    /**
     * Synchronisation des authentifications depuis un fichier JSON.
     */
    public function authentificationsSyncAction()
    {
        try {
            $jsonpath = $this->getRequest()->getParam('jsonpath');

            if (!$jsonpath)
                die("ERR : Vous devez spécifier le chemin complet vers le fichier JSON");


            $fileContent = file_get_contents($jsonpath);
            if (!$fileContent)
                die("ERR : Oscar n'a pas réussi à charger le contenu du fichier '$jsonpath'");

            $datas = json_decode($fileContent);
            if (!$datas)
                die("ERR : Les données du fichier '$jsonpath' n'ont pas pu être converties au format JSON.");

            // Système pour crypter les mots de pass (Zend)
            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $connectorAuthentification = new ConnectorAuthentificationJSON($datas,
                $this->getEntityManager(), $bcrypt);

            $repport = $connectorAuthentification->syncAll();
            $connectorFormatter = new ConnectorRepportToPlainText();

            echo $connectorFormatter->format($repport);

        } catch (\Exception $ex) {
            die("ERR : " . $ex->getMessage());
        }
    }

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
            $this->getConsole()->write($authentification->getId() . "\t",
                ColorInterface::CYAN);
            $this->getConsole()->write($authentification->getUsername());
            $this->getConsole()->writeLine(sprintf("(%s)",
                $authentification->getEmail()), ColorInterface::GRAY);
        }
    }

    /**
     * Ajoute une authentification.
     */
    public function authAddAction()
    {
        try {
            $login = $this->getRequest()->getParam('login');
            if (!$login) {
                $login = Line::prompt("Entrez l'identifiant : ", true, 64);
            }

            // Identifiant trop court
            if (strlen($login) < 4) {
                $this->consoleError("L'identifiant doit avoir au moins 4 caractères.");

                return;
            }

            // Identifiant déjà utilisé
            $checkLogin = $this->getEntityManager()
                ->getRepository(Authentification::class)
                ->findBy([
                    'username' => $login
                ]);

            if ($checkLogin) {
                $this->consoleError(sprintf("L'identifiant %s est déjà utilisé.",
                    $login));

                return;
            }

            $displayname = $this->getRequest()->getParam('displayname');
            if (!$displayname) {
                $displayname = Line::prompt("Nom affiché ($login) : ", true,
                    64);
                $displayname = $displayname ? $displayname : $login;
            }


            $email = $this->getRequest()->getParam('email');
            if (!$email) {
                $email = Line::prompt("Email (éviter de laisser vide) : ", true,
                    256);
            }

            $options = $this->getServiceLocator()->get('zfcuser_module_options');
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($options->getPasswordCost());

            $password = Password::prompt('Entrez le mot de passe (8 caractères minimum): ',
                true);

            if (strlen($password) < 8) {
                $this->getConsole()->writeLine("Le mot de passe est trop court :",
                    ColorInterface::WHITE, ColorInterface::RED);

                return;
            }

            // Récape :
            $this->getConsole()->writeLine("L'utilisateur suivant va être créé : ");

            $this->getConsole()->write("Identifiant de connexion : ",
                ColorInterface::GRAY);
            $this->getConsole()->writeLine($login, ColorInterface::WHITE);

            $this->getConsole()->write("Nom affiché : ", ColorInterface::GRAY);
            $this->getConsole()->writeLine($displayname, ColorInterface::WHITE);

            $this->getConsole()->write("Courriel : ", ColorInterface::GRAY);
            $this->getConsole()->writeLine($email, ColorInterface::WHITE);

            $confirm = Confirm::prompt("Créer l'utilisateur ? ");
            if ($confirm) {
                $auth = new Authentification();
                $auth->setPassword($bcrypt->create($password));
                $auth->setDisplayName($displayname);
                $auth->setUsername($login);
                $auth->setEmail($email);
                $this->getEntityManager()->persist($auth);
                $this->getEntityManager()->flush();
                $this->getConsole()->writeLine(sprintf("%s a été créé avec succès.",
                    $login), ColorInterface::WHITE, ColorInterface::GREEN);
            }

            return;
        } catch (\Exception $ex) {
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * Modification du mot de passe
     */
    public function authPassAction()
    {
        try {
            $login = $this->getRequest()->getParam('login');
            $pass = $this->getRequest()->getParam('newpass');
            $ldap = $this->getRequest()->getParam('ldap');

            /** @var Authentification $auth */
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $login]);
            if (!$auth) {
                $this->consoleError("Ce compte n'existe pas...");

                return;
            } else {
                $this->getConsole()->write("Modification du mot de passe pour ",
                    ColorInterface::GRAY);
                $this->getConsole()->write($auth->getUsername(),
                    ColorInterface::WHITE);
                $this->getConsole()->writeLine(" (" . $auth->getDisplayName() . ", " . $auth->getEmail() . ")",
                    ColorInterface::BLUE);
            }

            if (!$ldap) {
                $pass = Password::prompt("Entrez le nouveau mot de passe : ");
                if (strlen($pass) < 8) {
                    $this->consoleError("Le mot de passe doit faire au moins 8 caractères.");

                    return;
                }

                $confirm = Password::prompt("Confirmer le nouveau mot de passe : ");
                if ($confirm != $pass) {
                    $this->consoleError("Les mots de passe ne correspondent pas");

                    return;
                }

                $options = $this->getServiceLocator()->get('zfcuser_module_options');
                $bcrypt = new Bcrypt();
                $bcrypt->setCost($options->getPasswordCost());
                $password = $bcrypt->create($pass);

            } else {
                $password = 'ldap';
            }

            if (Confirm::prompt("Modifier le mot de passe ? (Y|n) ")) {
                $auth->setPassword($password);
                $this->getEntityManager()->flush();
                $this->consoleSuccess("Le mot de passe a été mis à jour");
            }
        } catch (\Exception $ex) {
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * Ajouter un rôle à une authentification
     */
    public function authPromoteAction()
    {
        try {
            $loginStr = $this->getRequest()->getParam('login');
            $roleStr = $this->getRequest()->getParam('role');

            /** @var Authentification $auth */
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $loginStr]);

            if (!$auth) {
                $this->consoleError("Aucune compte d'authentification d'a pour identifiant '$loginStr'");

                return;
            }

            if (!$roleStr) {
                $this->getConsole()->writeLine("Liste des rôles : ");
                $options = [];
                $codes = 'abcdefghifklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                $choose = 0;
                /** @var Role $role */
                foreach ($this->getEntityManager()->getRepository(Role::class)->findBy([],
                    ['roleId' => 'ASC']) as $role) {
                    $options[$codes[$choose++]] = $role->getRoleId();
                }

                $answer = Select::prompt(
                    'Quel rôle ajouter à ' . $auth->getDisplayname() . ' ?',
                    $options,
                    false,
                    false
                );

                $roleStr = $options[$answer];
            }

            $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $roleStr]);
            if (!$role) {
                $this->consoleError("Impossible de charge ce rôle.");

                return;
            }

            $userId = $auth->getId();
            $roleId = $role->getId();

            try {
                $query = $this->getEntityManager()->createNativeQuery("INSERT INTO authentification_role VALUES($userId, $roleId)",
                    new Query\ResultSetMapping());
                $query->execute();
            } catch (UniqueConstraintViolationException $e) {
                $this->consoleError(sprintf("Le compte '%s' a déjà ce rôle.",
                    $auth->getUsername()));

                return;
            }

            $this->consoleSuccess(sprintf("Le role '%s' a été ajouté à %s(%s) dans l'application.",
                $roleId, $auth->getUsername(), $auth->getDisplayName()));

            return;

        } catch (\Exception $ex) {
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * Afficher les informations d'un compte
     */
    public function authInfoAction()
    {
        try {
            $loginStr = $this->getRequest()->getParam('login');

            /** @var Authentification $auth */
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $loginStr]);
            if (!$auth) {
                $this->consoleError("Aucune compte d'authentification d'a pour identifiant '$loginStr'");

                return;
            }
            $this->getConsole(sprintf("Détails du compte %s : ",
                $auth->getUsername()));
            $this->consoleKeyValue('ID : ', $auth->getId());
            $this->consoleKeyValue('username (identifiant) : ',
                $auth->getUsername());
            $this->consoleKeyValue('displayName : ', $auth->getDisplayName());
            $this->consoleKeyValue('email : ', $auth->getEmail());
            $this->getConsole()->writeLine("Rôles : ", ColorInterface::GRAY);
            foreach ($auth->getRoles() as $role) {
                $this->getConsole()->writeLine(' - ' . $role,
                    ColorInterface::YELLOW);
            }

            try {
                $person = $this->getPersonService()->getPersonByLdapLogin($auth->getUsername());
                $this->consoleKeyValue('Person : ', $person);

                if ($this->params('org')) {
                    $this->getConsole()->writeLine("# Rôles dans des organisations : ",
                        ColorInterface::GRAY);
                    /** @var OrganizationPerson $op */
                    foreach ($person->getOrganizations() as $op) {
                        $this->getConsole()->write($op->getOrganization(),
                            ColorInterface::BLUE);
                        $this->getConsole()->write(" : ", ColorInterface::GRAY);
                        $this->getConsole()->writeLine($op->getRole(),
                            $op->getRoleObj()->isPrincipal() ? ColorInterface::WHITE : ColorInterface::GRAY);
                    }
                }
                if ($this->params('act')) {
                    $this->getConsole()->writeLine("# Rôles dans des activitès : ",
                        ColorInterface::GRAY);
                    /** @var ActivityPerson $op */
                    foreach ($person->getActivities() as $ap) {
                        $this->getConsole()->write($ap->getActivity(),
                            ColorInterface::BLUE);
                        $this->getConsole()->write(" : ", ColorInterface::GRAY);
                        $this->getConsole()->writeLine($ap->getRole(),
                            $ap->getRoleObj()->isPrincipal() ? ColorInterface::WHITE : ColorInterface::GRAY);
                    }
                }
            } catch (NoResultException $e) {
                $this->getConsole()->writeLine("Ce compte n'est pas associé à une personne physique dans oscar",
                    ColorInterface::BLACK, ColorInterface::YELLOW);
            }


            return;

        } catch (\Exception $ex) {
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// CADUCQUE
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @deprecated
     * @throws \Exception
     */
    public function shuffleAction()
    {
        throw new \Exception("Fonctionnalité dépréciée");
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// CONSOLE
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @return AdapterInterface
     */
    protected function getConsole()
    {
        return $this->getServiceLocator()->get('console');
    }

    /**
     * @param $msg Succes à afficher
     */
    protected function consoleSuccess($msg)
    {
        $this->getConsole()->writeLine($msg, ColorInterface::BLACK,
            ColorInterface::GREEN);
    }

    /**
     * @param $msg Succes à afficher
     */
    protected function consoleUpdateToDo($msg)
    {
        $this->getConsole()->writeLine($msg, ColorInterface::WHITE,
            ColorInterface::YELLOW);
    }

    /**
     * @param $msg
     */
    protected function consoleNothingToDo($msg)
    {
        $this->getConsole()->writeLine($msg, ColorInterface::WHITE,
            ColorInterface::GRAY);
    }

    /**
     * @param $msg
     */
    protected function consoleDeleteToDo($msg)
    {
        $this->getConsole()->writeLine($msg, ColorInterface::WHITE,
            ColorInterface::RED);
    }

    /**
     * @param $msg Succes à afficher
     */
    protected function consoleHeader($msg)
    {
        $this->getConsole()->write('# ', ColorInterface::WHITE);
        $this->getConsole()->writeLine($msg, ColorInterface::GRAY);
    }

    /**
     * @param $msg Succes à afficher
     */
    protected function consoleKeyValue($key, $value)
    {
        $this->getConsole()->write($key . ' ', ColorInterface::GRAY);
        $this->getConsole()->writeLine($value, ColorInterface::CYAN);
    }

    /**
     * @param $msg Erreur à afficher
     */
    protected function consoleError($msg)
    {
        $this->getConsole()->writeLine($msg, ColorInterface::WHITE,
            ColorInterface::RED);
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
            echo sprintf("Recherche '%s' pour les éléments %s dans les activités...\n",
                $what, strtoupper($obj));
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
            $repport = $this->getActivityService()->searchIndex_rebuild();
            $output = new ConnectorRepportToPlainText();
            $output->format($repport);

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
    public function organizationSyncAction()
    {
        $force = $this->getRequest()->getParam('force');
        $connectorName = $this->getRequest()->getParam('connectorkey');

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServiceLocator()->get('ConnectorService');

        $connector = $connectorService->getConnector('organization.' . $connectorName);
        echo "Execution de 'organization.$connectorName' (" . ($force ? 'FORCE' : 'NORMAL') . ") : \n";
        try {
            /** @var ConnectorRepport $repport */
            $repport = $connector->execute($force);
        } catch (\Exception $e) {
            die($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        foreach ($repport->getRepportStates() as $type => $out) {
            echo "Opération " . strtoupper($type) . " : \n";
            if ($type == "notices") {
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
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Recherche une personne dans la base de donnée à partir avec la valeur
     * donnée pour un connecteur donné.
     */
    public function personsSearchConnectorAction()
    {
        $connectorName = $this->getRequest()->getParam('connector');
        $value = $this->getRequest()->getParam('value');

        $this->getConsole()->clear();
        $this->getConsole()->writeLine(sprintf("Recherche de pour %s = '%s' : ",
            $connectorName, $value), ColorInterface::GRAY);

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        try {
            $persons = $personRepository->getPersonsByConnectorID($connectorName,
                $value);
            if (count($persons) == 0) {
                $this->getConsole()->writeLine(sprintf("Aucun résultat pour %s = '%s'",
                    $connectorName, $value), ColorInterface::YELLOW);
            }

            foreach ($persons as $person) {
                $this->getConsole()->write(sprintf(" [%s] ", $person->getId()),
                    ColorInterface::CYAN);
                $this->getConsole()->write(sprintf("%s", $person),
                    ColorInterface::NORMAL);
                $this->getConsole()->writeLine(sprintf(" (%s)",
                    $person->getEmail()), ColorInterface::GRAY);
            }
        } catch (\Exception $ex) {

            echo "############################ " . $ex->getMessage() . "\n"
                . $ex->getTraceAsString();
        }
    }

    public function personSyncAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            if (!$id) {
                throw new \Exception("Vous devez utiliser un ID oscar");
            }
            /**
             * @var $person Person
             */
            $person = $this->getEntityManager()->getRepository(Person::class)->find($id);

            foreach ($this->getConfiguration('oscar.connectors.person') as $key => $getConnector) {
                if (null === ($id = $person->getConnectorID($key))) {
                    throw new Exception(sprintf('Pas de connecteur %s pour %s ...',
                        $key, $person));
                }

//                $person = $this->getEntityManager()->getRepository(Person::class)->getPersonByConnectorID('toto', $person->getConnectorID($key));
                /** @var ConnectorPersonHarpege $connector */
                $connector = $getConnector();

                $connector->setServiceLocator($this->getServiceLocator());

                $connector->syncPerson($person);

            }
        } catch (\Exception $ex) {
            die($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    public function personsSyncAction()
    {
        $force = $this->getRequest()->getParam('force', false);
        $verbose = $this->getRequest()->getParam('verbose', false);
        $connectorName = $this->getRequest()->getParam('connectorkey');
        echo "Execution de 'person.$connectorName' (" . ($force ? 'FORCE' : 'NORMAL') . ") : \n";


        try {
            /** @var ConnectorService $connectorService */
            $connectorService = $this->getServiceLocator()->get('ConnectorService');

            // Récupération du connector
            $connector = $connectorService->getConnector('person.' . $connectorName);

            /** @var ConnectorRepport $repport */
            $repport = $connector->execute($force);

            foreach ($repport->getRepportStates() as $type => $out) {
                echo "Opération " . strtoupper($type) . " : \n";
                foreach ($out as $line) {
                    echo date('Y-m-d H:i:s',
                            $line['time']) . "\t" . $line['message'] . "\n";
                }
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            if ($verbose) {
                echo $e->getTraceAsString() . "\n";
            }
        }
    }

    public function checkPrivilegesAction()
    {
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
            foreach ($privileges as $privilege) {
                if ($categorie !== $privilege->getCategorie()) {
                    $categorie = $privilege->getCategorie();
                }
                $keyFile = $privilege->getCategorie()->getCode() . '_' . $privilege->getCode();
                $keyFile = strtoupper(str_replace('-', '_', $keyFile));
                if (array_key_exists($keyFile, $infile)) {
                    unset($infile[$keyFile]);
                }
                //echo (array_key_exists($keyFile, $infile) ? '/!\\' : ' - ') . " $keyFile $privilege\n";
            }
            if (count($infile)) {
                echo " ! DROITS MANQUANTS : \n";
                foreach ($infile as $key => $droit) {
                    echo " - $key = $droit\n";
                }
            }
        } catch (\Exception $e) {
            echo "!!!" . $e->getMessage() . " !!!\n";
            echo $e->getTraceAsString();
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
        foreach ($this->getEntityManager()->getRepository(Activity::class)->findAll() as $activity) {
            if ($activity->getDateStart() && $activity->getDateEnd()) {
                $text = sprintf("[%s] %s > %s", $activity->getOscarNum(),
                    $activity->getDateStart()->format('Y-m-d'),
                    $activity->getDateEnd()->format('Y-m-d'));
                if ($activity->getDateSigned()) {
                    if ($activity->getDateEnd() < $now) {
                        if ($activity->getStatus() != Activity::STATUS_TERMINATED) {
                            $this->getLogger()->warn($text);
                            $activity->setStatus(Activity::STATUS_TERMINATED);
                            $this->getEntityManager()->flush($activity);
                        }
                    } else {
                        if ($activity->getStatus() != Activity::STATUS_ACTIVE) {
                            $activity->setStatus(Activity::STATUS_ACTIVE);
                            $this->getEntityManager()->flush($activity);
                            $this->getLogger()->info($text);
                        }
                    }
                } else {
                    if ($activity->getDateEnd() && $activity->getDateEnd() < $now) {
                        if ($activity->getStatus() != Activity::STATUS_ABORDED) {
                            $this->getLogger()->error($text . ' ' . $activity->getStatusLabel());
                            $activity->setStatus(Activity::STATUS_ABORDED);
                            $this->getEntityManager()->flush($activity);
                        }
                    } else {
                        if ($activity->getStatus() != Activity::STATUS_PROGRESS) {
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
