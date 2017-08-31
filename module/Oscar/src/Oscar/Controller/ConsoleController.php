<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 02/11/15 14:59
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Monolog\Logger;
use Oscar\Connector\ConnectorAuthentificationJSON;
use Oscar\Connector\ConnectorPersonHarpege;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Authentification;
use Oscar\Entity\CategoriePrivilege;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Formatter\ConnectorRepportToPlainText;
use Oscar\Provider\AbstractOracleProvider;
use Oscar\Provider\Person\SyncPersonHarpege;
use Oscar\Provider\Privileges;
use Oscar\Provider\SifacBridge;
use Oscar\Service\ConnectorService;
use Oscar\Service\PersonService;
use Oscar\Service\ShuffleDataService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Yaml;
use UnicaenApp\Entity\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Authentication\Adapter\Ldap;
use Zend\Authentication\AuthenticationService;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class ConsoleController extends AbstractOscarController
{

    ///////////////////////////////////////////////////////////////////////////////////////
    ///
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
        //$serviceShuffle->shuffleOrganizations();
        //$serviceShuffle->shuffleProjects();
        $serviceShuffle->shuffleActivity();
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


    ////////////////////////////////////////////////////////////////////////////

    /**
     * Renseigne le champ LDAP<>ID Harpège en fonction de l'autre si manquant
     */
    public function evalHarpegeLdapPersonsAction()
    {
        /** @var Logger $logger */
        $logger = $this->getServiceLocator()->get('Logger');
        $logger->notice(" + Analyse des personnes dans Oscar...");
        /** @var Person $person */
        foreach ($this->getEntityManager()->getRepository(Person::class)->findAll() as $person) {
            $ldap = $person->getCodeLdap();
            $harpege = $person->getCodeHarpege();
            if( !$ldap && !$harpege ){
                $logger->warning(sprintf("Personne freestyle '%s'", $person));
            }
            elseif( $ldap && $harpege && SyncPersonHarpege::getLdapIdFromHarpegeId($harpege) == $ldap ){
                //$logger->debug(sprintf("Données cohérentes pour '%s'", $person));
            }
            elseif( !$ldap ){
                $person->setCodeLdap(SyncPersonHarpege::getLdapIdFromHarpegeId($person->getCodeHarpege()));
                $this->getEntityManager()->flush($person);
                //$logger->debug(sprintf("création du code ldap à partir de harpège", $person));
            }
            elseif( !$harpege ){
                $person->setCodeHarpege(SyncPersonHarpege::getHarpegeIdFromLdapId($person->getCodeLdap()));
                $this->getEntityManager()->flush($person);
                //$logger->debug(sprintf("création du code harpège à partir de ldap", $person));
            }
        }
        $logger->notice("... analyse terminée.");
    }

    /**
     * Synchronise les données Harpège dans Oscar :
     * - Mise à jour de l'INM si la personne est trouvée.
     * - Création si besoin
     */
    public function harpegePersonsAction()
    {
        /** @var SyncPersonHarpege $harpege */
        $harpege = $this->getServiceLocator()->get('Harpege');

        /** @var Logger $logger */
        $logger = $this->getServiceLocator()->get('Logger');

        /** @var Person[] $persons */
        $persons = [];

        $async = [];
        $datasUpdated = [];
        $datasCreated = [];

        $logger->notice(" + Analyse des données déjà présentes dans Oscar...");
        /** @var Person $person */
        foreach ($this->getEntityManager()->getRepository(Person::class)->findAll() as $person) {
            if ($person->getCodeHarpege()) {
                $key = $person->getCodeHarpege();
            } elseif ($person->getCodeLdap()) {
                $key = $harpege->getHarpegeIdFromLdapId($person->getCodeLdap());
            } else {
                $logger->info(sprintf("'%s' ne sera pas synchronisé à via Harpège", $person));
                $async[] = $person;
                continue;
            }

            if (isset($persons[$key])) {

                $logger->warning(sprintf("La personne '%s' avec la clef '%s' semble apparaître plusieurs fois dans Oscar (même identifiant Harpège)", $person, $key));
                continue;
            }
            $persons[$key] = $person;
        }
        $logger->notice("... analyse terminée.");


        $logger->notice(" + Analyse des données HARPEGE...");
        /// Personnes dans Harpège
        $personsHarpege = $harpege->queryPersons();
        while ($row = oci_fetch_array($personsHarpege,
            OCI_ASSOC + OCI_RETURN_NULLS)) {
            $harpegeId = $row['HARPEGEID'];

            if (!isset($persons[$harpegeId])) {
                $logger->info(sprintf("La personne '%s' var être ajoutée dans Oscar", $harpegeId));
                $datasCreated[$harpegeId] = [];
                $data = &$datasCreated[$harpegeId];
            } else {
                $datasUpdated[$harpegeId] = [
                    'person' => $persons[$harpegeId]
                ];
                $data = &$datasUpdated[$harpegeId];
            }

            $data['NOM'] = $row['NOM'];
            $data['PRENOM'] = $row['PRENOM'];
            $data['EMAIL'] = $row['EMAIL'];
            $data['DATEUPDATED'] = $row['DATEUPDATED'];
            $data['DATECREATED'] = $row['DATECREATED'];
            $data['INM'] = !isset($data['INM']) ?
                $row['INM'] :
                max($data['INM'], $row['INM']);
        }
        $logger->notice(" + ...Analyse terminée.");


        $logger->notice(sprintf(" %s enregistrement évaluer avant la mise à jour...", count($datasUpdated)));
        // On parse les données pour voir si une mise à jour est necessaire
        foreach ($datasUpdated as $id => $data) {
            if (isset($data['INM']) && $data['INM'] > $data['person']->getHarpegeINM()) {
                $logger->info(sprintf("L'INM de '%s:%s' var être mis à jour dans Oscar (%s > %s)", $id, $data['person'], $data['person']->getHarpegeINM(), $data['INM']));
                $data['person']->setHarpegeINM($data['INM']);
                $this->getEntityManager()->flush($data['person']);
            } else {
                unset($data['INM']);
            }
        }
        $logger->notice(sprintf(" %s enregistrement à créer...", count($datasCreated)));
        // On parse les données à créer
        foreach ($datasCreated as $id => $data) {
            $person = new Person();
            $this->getEntityManager()->persist($person);
            $person->setFirstname(ucfirst($data['PRENOM']))
                ->setLastname(ucfirst($data['NOM']))
                ->setCodeHarpege(ucfirst($id))
                ->setCodeLdap($harpege->getLdapIdFromHarpegeId($id))
                ->setEmailPrive($data['EMAIL'])
                ->setHarpegeINM($data['INM']);
            $this->getEntityManager()->flush($person);
            $logger->info(sprintf("Création de la personne '%s:%s' depuis les données Harpège.", $id, $person));
        }
    }

    public function harpegeINMAction()
    {
        /** @var \Oscar\Provider\Person\SyncPersonHarpege $harpege */
        $harpege = $this->getServiceLocator()->get('Harpege');
        $persons = [];

        /** @var Person $p */
        foreach ($this->getEntityManager()->getRepository(Person::class)->findAll() as $p) {
            if ($p->getCodeLdap()) {
                $codeLdap = $p->getCodeLdap();
                $codeHarpege = \Oscar\Provider\Person\SyncPersonHarpege::getHarpegeIdFromLdapId($codeLdap);
                $persons[$codeHarpege] = $p;
            }
        }

        $inms = $harpege->loadINM();
        foreach ($inms as $id => $inm) {
            if (isset($persons[$id]) && $persons[$id]->getHarpegeINM() != $inm) {
                echo sprintf(" - Mise à jour de l'INM de %s => %s\n",
                    $persons[$id], $inm);
                $persons[$id]->setHarpegeINM($inm);
                $this->getEntityManager()->flush($persons[$id]);
            } else {
                echo "$id n'est pas dans Oscar.\n";
            }
        }
        die("Sync");
    }

    public function syncOrganisationAction()
    {
        try {
            /** @var SifacBridge $sifac */
            $sifac = $this->getServiceLocator()->get('Sifac');

            /** @var Logger $logger */
            $logger = $this->getServiceLocator()->get('logger');

            $logger->notice('Synchronisation des organisations avec SIFAC');
die();
            $stid = $sifac->getOrganizations();

            $byId = [];

            $byName = $this->getEntityManager()->createQueryBuilder()
                ->select('o')
                ->from(Organization::class, 'o')
                ->where('o.shortName = :name OR o.fullName = :name');

            $bySifacId = $this->getEntityManager()->createQueryBuilder()
                ->select('o')
                ->from(Organization::class, 'o')
                ->where('o.sifacId = :sifacId');

            while ($row = oci_fetch_array($stid,
                OCI_ASSOC + OCI_RETURN_NULLS)) {
                if (!isset($byId[$row['ID']])) {

                    $id = AbstractOracleProvider::cleanBullshitStr($row['ID']);
                    $byId[$row['ID']] = $id;

                    $name = AbstractOracleProvider::cleanBullshitStr($row['name']);
                    $datas = [
                        'email' => AbstractOracleProvider::cleanBullshitStr($row['email']),
                        'street1' => AbstractOracleProvider::cleanBullshitStr($row['rue1']),
                        'city' => AbstractOracleProvider::cleanBullshitStr($row['city']),
                        'zipCode' => AbstractOracleProvider::cleanBullshitStr($row['zipcode']),
                        'phone' => AbstractOracleProvider::cleanBullshitStr($row['tel']),
                        'sifacId' => AbstractOracleProvider::cleanBullshitStr($row['ID']),
                        'codePays' => AbstractOracleProvider::cleanBullshitStr($row['country']),
                        'siret' => AbstractOracleProvider::cleanBullshitStr($row['siret']),
                        'bp' => AbstractOracleProvider::cleanBullshitStr($row['bp']),
                        'type' => AbstractOracleProvider::cleanBullshitStr($row['type']),
                        'sifacGroup' => AbstractOracleProvider::cleanBullshitStr($row['group']),
                        'sifacGroupId' => AbstractOracleProvider::cleanBullshitStr($row['groupid']),
                        'numTVACA' => AbstractOracleProvider::cleanBullshitStr($row['numtvaca']),
                    ];

                    $organization = null;

                    try {
                        $organization = $bySifacId->getQuery()->setParameter('sifacId',
                            $id)->getSingleResult();
                    } catch (NonUniqueResultException $e) {
                        die('WTF ?');
                    } catch (NoResultException $e) {
                        // ok
                    }

                    if (!$organization) {
                        $search = $byName->getQuery()->setParameter('name',
                            $name)->execute();
                        if ($search && count($search) === 1) {
                            /** @var Organization $organization */
                            $organization = $search[0];
                            $organization->setSifacId($id)
                                ->setDateUpdated(new \DateTime());
                        } elseif ($search && count($search) > 1) {
                            $logger->warning(sprintf(" !!! Hum... mitose detectée pour l'oganisation '%s'",
                                $name));
                            continue;
                        }
                    }

                    if (!$organization) {
                        $logger->info(sprintf("CREATE '%s'", $name));
                        $organization = new Organization();
                        $this->getEntityManager()->persist($organization);
                        $organization->setShortName($name)
                            ->setSifacId($id)
                            ->setDateCreated(new \DateTime())
                            ->setDateUpdated(new \DateTime());
                    }


                    $change = [];
                    foreach ($datas as $key => $value) {
                        $getter = 'get' . ucfirst($key);
                        $oldValue = $organization->$getter();

                        if ($value && $oldValue != $value) {
                            $setter = 'set' . ucfirst($key);
                            $organization->$setter($value);
                            $change[] = sprintf('%s ::: "%s" >> "%s"', $key,
                                $oldValue, $value);
                        }
                    }
                    if (count($change)) {
                        $logger->debug(sprintf("Ecriture de données pour '%s'",
                            $organization));
                        $this->getEntityManager()->flush($organization);
                    }
                }
            }

            $logger->notice("Nombres d'enregistements : " . count($byId));
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
        }

    }

    public function privilegeAction()
    {
        $what = $this->getRequest()->getParam('what');
        $path = realpath(__DIR__ . '/../../../../../data') . 'privilege_backup';
        $strEscape = function ($str) {
            return str_replace("'", "''", $str);
        };
        ob_start();
        ?>
        -- {roleId}
        UPDATE user_role SET id = {id}, role_id = {roleId}, is_default = {isDefault}, ldap_filter={ldapFilter} WHERE id={id};
        INSERT INTO user_role (id, role_id, is_default, ldap_filter) SELECT {id}, {roleId}, {isDefault}, {ldapFilter} WHERE NOT EXISTS (SELECT 1 FROM user_role WHERE id={id});

        <?php $tplRole = ob_get_clean();
        $tplPrivilegeRole = "INSERT INTO role_privilege (role_id, privilege_id) VALUES ({roleId},{privilegeId}); -- {role}";
        ob_start();
        ?>

        -- {code} : {libelle}
        UPDATE categorie_privilege SET id = {id}, code = {code}, libelle = {libelle} WHERE id={id};
        INSERT INTO categorie_privilege (id, code, libelle) SELECT {id}, {code}, {libelle} WHERE NOT EXISTS (SELECT 1 FROM categorie_privilege WHERE id={id});

        <?php $tplCategoriePrivilege = ob_get_clean();

        ob_start();
        ?>

        -- {code} : {libelle}
        UPDATE privilege SET id = {id}, categorie_id = {categorieId}, code={code}, libelle = {libelle} WHERE id={id};
        INSERT INTO privilege (id, categorie_id, code, libelle) SELECT {id}, {categorieId}, {code}, {libelle} WHERE NOT EXISTS (SELECT 1 FROM privilege WHERE id={id});

        <?php $tplPrivilege = ob_get_clean();

        if ($what === 'dump') {
            // Traitement des rôles
            $roles = $this->getEntityManager()->createQueryBuilder()->select('r')->from(Role::class,
                'r')
                ->orderBy('r.parent', 'DESC')
                ->getQuery()->getResult();
            ob_start(); ?>
            -------------------------------------------------------------------
            -- Script de maintenance Oscar© Université de Normandie 2016
            -- généré avec la commande 'php public/index.php oscar droits dump'
            -- le <?= date('Y-m-d H:i:s') ?>

            -------------------------------------------------------------------
            <?php
            /** @var Role $role */
            echo "--\n-- Synchronisation des rôles\n--\n\n";
            foreach ($roles as $role) {
                echo strtr($tplRole, [
                    '{id}' => $role->getId(),
                    '{roleId}' => "'" . $strEscape($role->getRoleId()) . "'",
                    '{parentId}' => $role->getParent() ? $role->getParent()->getId() : 'null',
                    '{isDefault}' => $role->getIsDefault() ? 'true' : 'false',
                    '{ldapFilter}' => $role->getLdapFilter() ? "'" . $role->getLdapFilter() . "'" : 'null',
                ]);
            }
            echo "\n\n\n--\n-- Synchronisation des catégories de privilèges\n--\n\n";

            $categories = $this->getEntityManager()->createQueryBuilder()->select('c')->from(CategoriePrivilege::class,
                'c')
                ->getQuery()->getResult();
            /** @var CategoriePrivilege $category */
            foreach ($categories as $category) {
                echo strtr($tplCategoriePrivilege, [
                    '{id}' => $category->getId(),
                    '{code}' => "'" . $category->getCode() . "'",
                    '{libelle}' => "'" . $strEscape($category->getLibelle()) . "'",
                ]);

            }

            $privileges = $this->getEntityManager()->createQueryBuilder()->select('p')->from(Privilege::class,
                'p')
                ->getQuery()->getResult();

            $privilegesRoles = [];
            /** @var Privilege $privilege */
            foreach ($privileges as $privilege) {
                echo strtr($tplPrivilege, [
                    '{id}' => $privilege->getId(),
                    '{code}' => "'" . $privilege->getCode() . "'",
                    '{categorieId}' => $privilege->getCategorie() ? $privilege->getCategorie()->getId() : 'null',
                    '{libelle}' => sprintf("'%s'",
                        $strEscape($privilege->getLibelle())),
                ]);
                $privilegesRoles[] = "\n-- %%%% " . $privilege->getFullCode() . " ~ " . $privilege->getLibelle();
                foreach ($privilege->getRole() as $role) {
                    $privilegesRoles[] = strtr($tplPrivilegeRole, [
                        '{privilegeId}' => $privilege->getId(),
                        '{roleId}' => $role->getId(),
                        '{role}' => $role->getRoleId()
                    ]);
                }

            }
        }
        echo ob_get_clean();

        echo "-- SYNCHRONISATION des ROLES <> PRIVILEGES\n";
        echo "DELETE FROM role_privilege; \n";
        echo implode("\n", $privilegesRoles);


    }

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



    public function autoMerge(){
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        $byemail = [];
        $connectors = ['ldap', 'harpege'];
        $byconnectors = [
            'ldap' => [],
            'harpege' => [],
        ];
        $byId = [];

        /** @var Person $person */
        foreach( $persons as $person ){
            $byId[$person->getId()] = $person;


            if( $person->getEmail() ){
                if( !isset($byemail[$person->getEmail()]) ){
                    $byemail[$person->getEmail()] = [];
                }
                $byemail[$person->getEmail()][] = $person;
            }

            foreach($connectors as $connector ){
                $valueConnector = $person->getConnectorID($connector);
                if( $valueConnector ){
                    if( !isset($byconnectors[$connector][$valueConnector]) ){
                        $byconnectors[$connector][$valueConnector] = [];
                    }
                    $byconnectors[$connector][$valueConnector][] = $person;
                }
            }
        }

        foreach( $connectors as $connector ){
            foreach($byconnectors[$connector] as $value=>$persons ){
                if( count($persons) > 1 ){
                    /** @var Person $keep */
                    $keep = $persons[0];

                    echo "fusion de $keep ($connector) avec : ";

                    for($i=1; $i<count($persons); $i++ ){

                        /** @var Person $other */
                        $other = $persons[$i];

                        echo $other.", " . $other->getConnectorID('harpege');
                        $other->mergeTo($keep);
                        $documents = $this->getEntityManager()->getRepository(ContractDocument::class)->findBy([
                            'person' => $other
                        ]);
                        /** @var ContractDocument $doc */
                        foreach( $documents as $doc ){
                            $doc->setPerson($keep);
                        }

                        $this->getEntityManager()->remove($other);
                    }
                }
            }
        }
        $this->getEntityManager()->flush();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // PATCHS
    //
    ////////////////////////////////////////////////////////////////////////////
    public function patchAction(){

        $this->patchPersonConnectorRest();
        // $this->patchData_fieldsToConnectorAction();
        //$this->patchData_fieldsToConnectorAction();
        // $this->patchData_organisationType();
        // $this->autoMerge();
        //$this->patchData_PersonneConnectorID();
//        $this->patchData_OrganisationConnector();
//        $this->patchData_OrganisationDoublon();
    }

    public function patchPersonConnectorRest(){
        /** @var Person $person */
        try {
            $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
            foreach ($persons as $person) {
                $person->setConnectorID('rest', $person->getConnectorID('ldap'));
            }
            $this->getEntityManager()->flush();
        } catch( \Exception $e ){
            echo $e->getMessage();
        }
    }

    public function patchData_OrganisationDoublon(){
        try {
            $organisations = $this->getEntityManager()->getRepository(Organization::class)->findAll();
            $packByCode = [];

            /** @var Organization $organisation */
            foreach( $organisations as $organisation ){
                if ( $organisation->getCode() ){
                    if( !array_key_exists($organisation->getCode(), $packByCode) ){
                        $packByCode[$organisation->getCode()] = [];
                    }
                    $packByCode[$organisation->getCode()][] = $organisation;

                }
            }

            foreach( $packByCode as $pack=>$organisations ){
                if( count($organisations) > 1 ){
                    $main = $organisations[0];
                    for( $i=1; $i<count($organisations); $i++ ){
                        /** @var Organization $organisation */
                        $organisation = $organisations[$i];
                        echo " = Merge des données de $organisation dans $main \n";

                        /** @var OrganizationPerson $organisationPerson */
                        foreach( $organisation->getPersons() as $organisationPerson ){
                            $organisationPerson->setOrganization($main);
                        }
                        /** @var ActivityOrganization $organizationActivity */
                        foreach( $organisation->getActivities() as $organizationActivity ){
                            $organizationActivity->setOrganization($main);
                        }

                        /** @var ProjectPartner $projectPartner */
                        foreach( $organisation->getProjects() as $projectPartner ){
                            $projectPartner->setOrganization($main);
                        }
                        $this->getEntityManager()->remove($organisation);
                    }
                    $this->getEntityManager()->flush();

                }
            }

        }catch( \Exception $e ){
            die( $e->getMessage());
        }
    }

    public function patchData_PersonneConnectorID(){
        echo "# PATCH numbers 2017-04-11\n";

        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();

        /** @var Person $person */
        foreach( $persons as $person ){
            if( $person->getCodeHarpege() && !$person->getConnectorID('harpege') ){
                echo "Mise à jour du connector HARPEGE\n";
                $person->setConnectorID('harpege', $person->getCodeHarpege());
            }
            if( $person->getCodeLdap() && !$person->getConnectorID('ldap') ){
                echo "Mise à jour du connector LDAP\n";
                $person->setConnectorID('ldap', $person->getCodeLdap());
            }
        }
    }

    public function patchData_organisationType(){
        echo "# PATCH numbers 2016-10-14\n";

        $roles = [
            Organization::ROLE_COMPOSANTE_GESTION,
            Organization::ROLE_LABORATORY,
            Organization::ROLE_COMPOSANTE_RESPONSABLE,
        ];

        $roleskey = [
            Organization::ROLE_COMPOSANTE_GESTION=> 1,
            Organization::ROLE_COMPOSANTE_RESPONSABLE=> 1,
            Organization::ROLE_LABORATORY => 2,
        ];

        $qb = $this->getEntityManager()->getRepository(ActivityOrganization::class)->createQueryBuilder('ao');
        $qb->andWhere('ao.role IN(:roles)')->setParameter('roles', $roles);

        $partners = $qb->getQuery()->getResult();
        /** @var ActivityOrganization $partner */
        foreach( $partners as $partner ){
            if( ! $partner->getOrganization()->getType() ){
                echo "up " . $partner->getRole() . " > " . $partner->getOrganization() . "\n";
                $partner->getOrganization()->setType($roleskey[$partner->getRole()]);
                $this->getEntityManager()->flush($partner->getOrganization());
            }
        }
    }

    /**
     * Ce patch permet de ranger dans le champ 'numbers' les différentes
     * numérotations préexistantes dans les données de Oscar :
     * - SIFAC
     * - CENTAURE
     */
    public function patchData_numbersUpdate(){
        echo "# PATCH numbers 2016-10-14\n";

        /** @var Activity $a */
        foreach( $this->getEntityManager()->getRepository(Activity::class)->findAll() as $a ){
            $change = false;
            $centaureId = $a->getCentaureId();
            $saic = $a->getCentaureNumConvention();

            if( $centaureId && $a->getNumber('centaure') != $centaureId ){
                $change = true;
                $a->addNumber("centaure", $centaureId);
                echo " update centaure = $centaureId\n" ;
            }
            if( $saic && $a->getNumber('saic') != $saic ){
                $change = true;
                $a->addNumber("saic", $saic);
                echo " update saic = $saic" ;
            }

            if( $change ){
                $this->getEntityManager()->flush($a);
                echo "\n";
            }
        }
    }

    public function patchData_OrganisationConnector(){
        echo "# PATCH connector 2016-09-22\n";

        /** @var Organization $organization */
        foreach( $this->getEntityManager()->getRepository(Organization::class)->findAll() as $organization ){
            if( $organization->getCode() ){
                $organization->setConnectorID('ldap', $organization->getLdapSupannCodeEntite());
                $this->getEntityManager()->flush($organization);
            }
        }
    }

    public function patchData_fieldsToConnectorAction(){
        echo "# PATCH connector 2016-09-15\n";

        /** @var Person $person */
        foreach( $this->getEntityManager()->getRepository(Person::class)->findAll() as $person ){

            $ldap = $person->getCodeLdap();
            $harpege = $person->getCodeHarpege();


            if( $ldap == $person->getConnectorID('ldap') && $harpege == $person->getConnectorID('harpege') ){
                continue;
            }

            if( !$ldap && !$harpege ){
                echo sprintf(" ! Pas de connector pour %s\n", $person->log());
                continue;
            }
            elseif( $ldap ){
                $harpege = self::getHarpegeIdFromLdapId($ldap);
            }
            elseif( $harpege ){
                $ldap = self::getLdapIdFromHarpegeId($harpege);
            }
            else {
                if( $ldap != self::getLdapIdFromHarpegeId($harpege) ){
                    echo "ERROR : Incohérence entre LDAP et Harpège !\n";
                }
                continue;
            }

            $person->setConnectorID('ldap', $ldap);
            $person->setConnectorID('harpege', $harpege);
            $person->setCodeLdap($ldap);
            $person->setCodeHarpege($harpege);

            $this->getEntityManager()->flush($person);
            echo sprintf("Update %s/%s pour : %s\n", $harpege, $ldap, $person->log());

        }
    }


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
            foreach( $out as $line ){
                echo date('Y-m-d H:i:s', $line['time']) . "\t" . $line['message'] . "\n";
            }
        }
    }

    public function personsOrganizationSyncAction(){
        // todo
        echo "TODO";
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
        $connectorName  = $this->getRequest()->getParam('connectorkey');

        /** @var ConnectorService $connectorService */
        $connectorService = $this->getServiceLocator()->get('ConnectorService');

        $connector = $connectorService->getConnector('person.' . $connectorName);
        echo "Execution de 'person.$connectorName' (".($force ? 'FORCE' : 'NORMAL').") : \n";
        try {
            /** @var ConnectorRepport $repport */
            $repport = $connector->execute($force);
        } catch( \Exception $e ){
            die($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        foreach( $repport->getRepportStates() as $type => $out ){
            echo "Opération " . strtoupper($type) . " : \n";
            foreach( $out as $line ){
                echo date('Y-m-d H:i:s', $line['time']) . "\t" . $line['message'] . "\n";
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