<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 15/06/15 10:05
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Monolog\Handler\StdoutHandler;
use Monolog\Logger;
use Oscar\Entity\ContractType;
use Oscar\Entity\ContractTypeRepository;
use Oscar\Entity\Discipline;
use Oscar\Entity\GrantSource;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\Activity;
use Oscar\Entity\ProjectMember;
use Oscar\Import\ImportPartners;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Request;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

/**
 * Script de syncronisation des données issues de la base de données Centaure.
 *
 * Ce script s'appuis sur un système de cache géré avec une BDD SQLLite (dans le
 * dossier /data du projet).
 */
class CentaureSyncController extends AbstractOscarController implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    private $verbose = false;
    private $simulate = true;

    /**
     * Retourne la connexion à Centaure. (Base ORACLE).
     *
     * @return resource
     *
     * @throws \Exception
     */
    protected function getConnexion()
    {
        static $conn;
        if (null === $conn) {
            $config = $this->getServiceLocator()->get('Config')['doctrine']['connection']['centaure']['params'];
            $this->getLogger()->info('Connexion à '.$config['host']);
            $conn = \oci_connect($config['user'], $config['password'],
                $config['host'], 'AL32UTF8');
            if (!$conn) {
                $e = oci_error();
                throw new \Exception($e);
            }
        }

        return $conn;
    }

    /**
     * Syncronise les projets depuis la base de donénes Oracle.
     */
    public function syncProjectsAction()
    {
        if (!$this->getRequest() instanceof Request) {
            throw new \RuntimeException('Console usage only');
        }

        ////////////////////////////////////////////////////////////////////////
        // MODE VERBEUX (affiche les Debug)

        $this->verbose = $this->getRequest()->getParam('verbose');
        if (!$this->verbose) {
            $this->getLogger()->popHandler();
            $handler = new StdoutHandler(Logger::INFO);
            $this->getLogger()->pushHandler($handler);
        }

        // ACTION
        $action = $this->getRequest()->getParam('doWhat');
        $method = 'sync'.ucfirst($action);

        if (method_exists(get_class($this), $method)) {
            echo "Execution de la méthode $method";
            $this->$method();
        } elseif ($action == 'all') {
            $this->syncDiscipline();
            $this->syncContractType();
            $this->syncGrantSource();
            $this->syncProjects();
            $this->syncContrat();
            $this->syncPersonneProject();
            //$this->syncLaboratoires();
            $this->syncPartenaires();
        } else {
            echo "[ERROR] unlnow method '$action'";
        }
        return;
    }

    /**
     * Synchronisation des laboratoire.
     *
     * @throws \Exception
     */
    public function syncPartenaires()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des PARTENAIRES...');

        $import = new ImportPartners($this->getConnexion(), $this->getEntityManager(), $this->getLogger());
        $import->import();
    }

    /**
     * Synchronisation des laboratoire.
     *
     * @throws \Exception
     */
    public function syncLaboratoire()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des LABORATOIRE...');

        $c = $this->getConnexion();
        /* @var ContractTypeRepository $contractTypeRepo */
        $repo = $this->getEntityManager()->getRepository('Oscar\Entity\Organization');

        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c, 'SELECT * FROM LABORATOIRE');
        oci_execute($stid);

        $proceded = 0;
        $start = microtime(true);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$proceded;

            // Récupération des données
            $centaureID = $this->cleanBullshitStr($row['LABO_CLEUNIK']);
            $shortName = $this->cleanBullshitStr($row['ACRONYME']);
            $code = $this->cleanBullshitStr($row['CODE_LABORATOIRE']);
            $fullName = $this->cleanBullshitStr($row['DENOMINATION']);
            $email = $this->cleanBullshitStr($row['E_MAIL']);
            $url = $this->cleanBullshitStr($row['URL']);
            $type = Organization::TYPE_LABORATORY;
            $street1 = $this->cleanBullshitStr($row['RUE1']);
            $street2 = $this->cleanBullshitStr($row['RUE2']);
            $street3 = $this->cleanBullshitStr($row['RUE3']);
            $city = $this->cleanBullshitStr($row['VILLE']);
            $zipCode = $this->cleanBullshitStr($row['CODE_POSTAL']);
            $phone = $this->cleanBullshitStr($row['TELEPHONE']);
            $dateCreated = $this->extractDate($row['DATE_CREATION']);
            $dateUpdated = $this->extractDate($row['DATE_MAJ']);
            $oscarId = $this->getOscarId('Laboratoire', $centaureID);

            // Création / Récupération de l'entité
            if ($oscarId) {

                // Test du cache
                $sum = $this->getSum('Laboratoire', $centaureID);
                if ($sum && $sum == serialize($row)) {
                    $this->getLogger()->info(sprintf(" - Données de '%s : %s' a jour.", $centaureID, $shortName));
                    continue;
                }

                $this->getLogger()->info(sprintf(" - Mise à jour pour '%s : %s'.", $centaureID, $shortName));
                $organisation = $repo->find($oscarId);
                if (!$organisation) {
                    $this->getLogger()->warn(sprintf(" - OscarID présent en cache mais pas en BDD pour '%s : %s' !", $centaureID, $shortName));
                    $organisation = new Organization();
                    $this->getEntityManager()->persist($organisation);
                }
            } else {
                $this->getLogger()->info(sprintf(" - Création de '%s : %s'.", $centaureID, $shortName));
                $organisation = new Organization();
                $this->getEntityManager()->persist($organisation);
            }

            $organisation->setPhone($phone)
                ->setCity($city)
                ->setEmail($email)
                ->setCode($code)
                ->setFullName($fullName)
                ->setShortName($shortName)
                ->setStreet1($street1)
                ->setStreet2($street2)
                ->setStreet3($street3)
                ->setUrl($url)
                ->setEmail($email)
                ->setZipCode($zipCode)
                ;
            if ($dateCreated) {
                $organisation->setDateCreated($dateCreated);
            }
            if ($dateUpdated) {
                $organisation->setDateUpdated($dateUpdated);
            }

            $this->getEntityManager()->flush($organisation);
            $this->setCorrespondance('Laboratoire', $organisation->getId(), $centaureID, serialize($row));
        }
        $this->getLogger()->notice(sprintf('%s traitement en %s secondes.', $proceded, (microtime(true) - $start)));
    }

    protected function purge()
    {
        $this->cleanCache();
    }

    /**
     * Synchronisation des types de contrat.
     *
     * @throws \Exception
     */
    public function syncContractType()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des TYPE de CONTRAT...');

        $c = $this->getConnexion();
        /** @var ContractTypeRepository $contractTypeRepo */
        $contractTypeRepo = $this->getEntityManager()->getRepository('Oscar\Entity\ContractType');
        $root = $contractTypeRepo->getRoot();

        // Premier niveau
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        //;
        $stid = oci_parse($c,
            'SELECT * FROM ST_CONVENTION ORDER BY C_CL_CONTRAT, LIB_ST_CONVENTION');
        oci_execute($stid);

        $codeItem = null;
        $codeCategorie = null;
        $codeSousCategorie = null;

        $categorie = null;
        $sousCategorie = null;
        $proceded = 0;
        $start = microtime(true);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $codeItem = trim($row['C_ST_CONT']);
            ++$proceded;

            $this->getLogger()->debug(sprintf("Traitement de '%s'", $codeItem));

            // Récupération de la catégorie
            if (trim($row['C_CL_CONTRAT']) !== $codeCategorie) {
                $codeCategorie = trim($row['C_CL_CONTRAT']);
                $categorie = $contractTypeRepo->findOneBy(array('code' => $codeCategorie));

                // Si la catégorie n'existe pas on la cré
                if (!$categorie) {
                    $this->getLogger()->notice(sprintf("La catégorie '%s' va être ajoutée dans oscar", $codeCategorie));
                    $categorie = new ContractType();
                    $categorie->setLabel($codeCategorie)
                        ->setDescription('Description pour la catégorie '.$codeCategorie)
                        ->setCode($codeCategorie)
                        ->setLabel('Label '.$codeCategorie);
                    $contractTypeRepo->addTo($categorie, $root);
                }
            }

            if (trim($row['C_T_CONV']) !== $codeSousCategorie) {
                $codeSousCategorie = trim($row['C_T_CONV']);
                $sousCategorie = $contractTypeRepo->findOneBy(array('code' => $codeSousCategorie));
                if (!$sousCategorie) {
                    $this->getLogger()->notice(sprintf("La spus-catégorie '%s' va être ajoutée dans oscar", $codeSousCategorie));
                    $sousCategorie = new ContractType();
                    $sousCategorie->setLabel($codeSousCategorie)
                        ->setDescription('Description pour la sous-catégorie '.$codeSousCategorie)
                        ->setCode($codeSousCategorie)
                        ->setLabel('Label '.$codeSousCategorie);
                    $contractTypeRepo->addTo($sousCategorie, $categorie);
                }
            }

            $libelle = trim($row['LIB_ST_CONVENTION']);

            $item = $contractTypeRepo->findOneBy(array('code' => $codeItem));
            if (!$item) {
                $this->getLogger()->notice(sprintf("Création de '%s'", $codeItem));
                $item = new ContractType();
                $item->setLabel($libelle)
                    ->setDescription('Description pour la rubrique '.$libelle)
                    ->setCode($codeItem);
                $contractTypeRepo->addTo($item, $sousCategorie);
            }
        }
        $this->getLogger()->notice(sprintf('%s traitement en %s secondes.', $proceded, (microtime(true) - $start)));
    }

    /**
     * Cette méthode synchronise les projets enregistrés dans centaure.
     *
     * Note : Les projets sont construits à partir du premier contrat (sans
     * avenant précédent).
     */
    public function syncProjects()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des PROJETS...');
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use in command line');
        }

        // Récupération des projets dans centaure
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c,
            "SELECT G_N1_COD, C_ST_CONT, NUM_CONVENTION, DATE_CREE, C_D_CONV, DATE_MAJ, CONV_CLEUNIK, DATE_DEBUT, DATE_OUVERTURE, DATE_FIN, DATE_SIGNATURE, MONTANT_FACTURE_HT, MONTANT_A_JUSTIFIER, E_VC_COD, ACRONYME_CONV, LIB_CONVENTION FROM CONVENTION WHERE NUM_AVENANT_PRECEDENT = ' '  ORDER BY DATE_MAJ");
        oci_execute($stid);

        $processed = 0;
        $start = microtime(true);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $libelle = $this->cleanBullshitStr($row['LIB_CONVENTION']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            $code = $this->cleanBullshitStr($row['NUM_CONVENTION']);
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $oscarId = $this->getOscarId('Project', $centaureId);
            $dateCreated = $this->extractDate($row['DATE_CREE']);
            $dateUpdate = $this->extractDate($row['DATE_MAJ']);
            $this->getLogger()->debug(sprintf("Traitement de '%s'", $code));
            $composantePrincipal = $this->cleanBullshitStr($row['G_N1_COD']);

            // Discipline
            $centaureDisciplineId = $this->cleanBullshitStr($row['C_D_CONV']);
            $idDiscipline = $this->getOscarId('Discipline', $centaureDisciplineId);
            if ($idDiscipline) {
                $discipline = $this->getEntityManager()->getRepository('Oscar\Entity\Discipline')->find($idDiscipline);
                $this->getLogger()->debug(sprintf(" - discipline : '%s'", $discipline));
            } else {
                $this->getLogger()->debug(sprintf(' - discipline : PAS DE DISCIPLINE'));
                $discipline = null;
            }

            // Test de données vermoulues
            if (!$code) {
                $this->getLogger()->warn(sprintf("Projet ignoré : ID:'%s' => ACR:'%s' / LIB:'%s' (pas de N° de convention)",
                    $centaureId, $acronym, $libelle));
                continue;
            }

            if (!$oscarId) {
                $this->getLogger()->info(sprintf("Création du projet '%s'", $code));
                $project = new Project();
                $this->getEntityManager()->persist($project);
            } else {

                // On test le cache
                $cache = $this->getSum('Project', $centaureId);
                if ($cache == serialize($row)) {
                    $this->getLogger()->debug(sprintf(' + Pas de changement dans Centaure pour %s.',
                        $code));
                    continue;
                }
                $this->getLogger()->info(sprintf("Mise à jour pour '%s'",
                    $code));

                $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($oscarId);
            }

            $project->setCode($code)
                ->setDateCreated($dateCreated)
                ->setComposantePrincipal($composantePrincipal)
                ->setDateUpdated($dateUpdate)
                ->setDiscipline($discipline)
                ->setAcronym($acronym)
                ->setDescription($libelle)
                ->setLabel($libelle);

            $this->getEntityManager()->flush($project);

            $this->setCorrespondance('Project', $project->getId(), $centaureId,
                serialize($row));
        }
        $this->getLogger()->notice(sprintf('%s traitement(s) en %s millsec.', $processed, (microtime(true) - $start)));
    }

    public function atomAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use in command line');
        }

        $this->syncDiscipline();
    }

    protected function syncPersonnel()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use in command line');
        }

        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $em = $this->getEntityManager()->getRepository('Oscar\Entity\Person');
        $stid = oci_parse($c,
            "SELECT PER_CLEUNIK AS CLEUNIK, NOM_PATRO AS NOM, PRENOM, E_MAIL AS EMAIL, TELEPHONE AS PHONE, CODE_HARPEGE FROM PERSONNEL
UNION
SELECT PER_CLEUNIK_VALO AS CLEUNIK, NOM_PERS as NOM, PREN_PERS AS PRENOM, E_MAIL AS EMAIL, '' AS PHONE, '?' AS CODE_HARPEGE FROM PERS_VALO");
        oci_execute($stid);

        $processed = 0;
        $start = microtime(true);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $centaureId = $this->cleanBullshitStr($row['CLEUNIK']);
            $nom = $this->cleanBullshitStr($row['NOM']);
            $prenom = $this->cleanBullshitStr($row['PRENOM']);
            $codeHarpege = $this->cleanBullshitStr($row['CODE_HARPEGE']);
            $telephone = $this->cleanBullshitStr($row['PHONE']);
            $email = $this->cleanBullshitStr($row['EMAIL']);
            $oscarId = $this->getOscarId('Person', $centaureId);

            if (!$oscarId) {
                $this->getLogger()->notice(sprintf("%s va être ajouté (n'est pas dans le cache)", $centaureId));
                $person = new Person();
            } else {
                $sum = serialize($row);
                $person = $em->find($oscarId);

                // Le cache existe mais la données est absente de la BDD
                if (!$person) {
                    $this->getLogger()->error(sprintf('Erreur de cache, la donnée %s est obsolète, Création d\'une nouvelle entrée.',
                        $oscarId));
                    $person = new Person();
                } // Les données de centaure on changé depuis la dernière syncro
                else {
                    if ($sum != $this->getSum('Person', $centaureId)) {
                        $this->getLogger()->notice(sprintf('la personne %s va être mise à jour.',
                            $centaureId));
                    } // Les données sont à jour
                    else {
                        $this->getLogger()->info(sprintf('la personne %s est à jour.',
                            $centaureId));
                        continue;
                    }
                }
            }
            $person->setCodeHarpege($codeHarpege)
                ->setEmail($email)
                ->setFirstname($prenom)
                ->setLastname($nom)
                ->setPhone($telephone);

            if (!$person->getId()) {
                $this->getEntityManager()->persist($person);
            }

            $this->getEntityManager()->flush($person);
            $this->setCorrespondance('Person', $person->getId(), $centaureId,
                serialize($row));
        }
        $this->getLogger()->notice(sprintf('%s traitement(s) en %s millsec.', $processed, (microtime(true) - $start)));
    }

    /**
     * BUT :
     * 1. Récupérer les différents acteurs des contrats.
     * 2. Associer les personnes au projet(lié au contrat)
     * 3. Eviter les doublons.
     *
     * @throws \Exception
     */
    protected function syncPersonneProject()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use in command line');
        }
        $personRepo = $this->getEntityManager()->getRepository('Oscar\Entity\Person');
        $contractRepo = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant');

        $fieldPersonCentaure = 'PER_CLEUNIK';

        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c, 'SELECT * FROM CONVENTION');
        oci_execute($stid);

        $processed = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $this->cleanBullshitStr($row[$fieldPersonCentaure]);
            $contractCentaureId = $this->cleanBullshitStr($row['NUM_CONVENTION']);
            ++$processed;

            $oscarId = $this->getOscarId('Person', $centaureId);
            $pValo = null;

            $centaureChargeValoId = $this->cleanBullshitStr($row['PER_CLEUNIK_VALO']);

            if ($centaureChargeValoId) {
                $pValoId = $this->getOscarId('Person', $centaureChargeValoId);
                if ($pValoId) {
                    $pValo = $personRepo->find($pValoId);
                }
            }

            if (!$oscarId) {
                $this->getLogger()->error(sprintf("%s n'a pas de correspondance dans oscar",
                    $centaureId));
                continue;
            } else {
                $person = $personRepo->find($oscarId);
                if (!$person) {
                    $this->getLogger()->error(sprintf("l'ID %s n'existe pas dans oscar",
                        $oscarId));
                }
            }

            $contract = $contractRepo->findOneBy(array(
                'centaureNumConvention' => $contractCentaureId,
            ));
            if (!$contract) {
                $this->getLogger()->error(sprintf('Impossible de trouver le contrat %s',
                    $contractCentaureId));
                continue;
            }

            $project = $contract->getProject();
            if (!$project) {
                $this->getLogger()->error(sprintf('Impossible de trouver le projet associé au contrat %s',
                    $contractCentaureId));
                continue;
            }

            if ($pValo && !$project->hasPerson($pValo,
                    'Chargé de valorisation')
            ) {
                $addValo = new ProjectMember();
                $this->getEntityManager()->persist($addValo);
                $addValo->setPerson($pValo)
                    ->setProject($project)
                    ->setRole('Chargé de valorisation');
                $project->addMember($addValo);
                $this->getLogger()->info(sprintf("Ajout de '%s'(%s) dans le projet '%'.",
                    $pValo, 'Chargé de Valorisation', $project));
                $this->getEntityManager()->flush($addValo);
            }

            if ($person && !$project->hasPerson($person, 'Responsable')) {
                $projectMember = new ProjectMember();
                $this->getEntityManager()->persist($projectMember);
                $projectMember->setPerson($person)
                    ->setProject($project)
                    ->setRole('Responsable');

                $project->addMember($projectMember);
                $this->getLogger()->info(sprintf("Ajout de '%s'(%s) dans le projet '%'.",
                    $person, 'Responsable', $project));

                $this->getEntityManager()->flush($projectMember);
            }
        }
    }

    protected function syncContrat()
    {
        $this->getLogger()->notice('+++ Syncronisation des contrats');
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use in command line');
        }

        // Récupération des projets dans centaure
        $c = $this->getConnexion();
        $model = 'Activity';
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $emGrant = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant');
        $stid = oci_parse($c,
            'SELECT E_VC_COD, C_ST_CONV, C_ST_CONT, CODE_NATURE_CONT, NUM_AVENANT_PRECEDENT, NUM_CONVENTION, DATE_CREE, C_D_CONV, DATE_MAJ, CONV_CLEUNIK, DATE_DEBUT, DATE_OUVERTURE, DATE_FIN, DATE_SIGNATURE, MONTANT_FACTURE_HT, MONTANT_A_JUSTIFIER, E_VC_COD, ACRONYME_CONV, LIB_CONVENTION FROM CONVENTION ORDER BY NUM_AVENANT_PRECEDENT');
        oci_execute($stid);

        $statusCor = array(
            'CAC' => Activity::STATUS_ACTIVE,
            'CCL' => Activity::STATUS_TERMINATED,
            'DAB' => Activity::STATUS_CANCEL,
            'CRE' => Activity::STATUS_CANCEL,
            'DEC' => Activity::STATUS_ACTIVE,
        );

        $processed = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $codeEOTP = $this->cleanBullshitStr($row['E_VC_COD']);
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $this->getLogger(sprintf("[Contrat] Traitement de '%s", $centaureId));
            $oscarId = $this->getOscarId('Grant', $centaureId);
            $code = $this->cleanBullshitStr($row['NUM_CONVENTION']);
            $libelle = $this->cleanBullshitStr($row['LIB_CONVENTION']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            $code = $this->cleanBullshitStr($row['NUM_CONVENTION']);
            $montant = (float) str_replace(',', '.', $row['MONTANT_FACTURE_HT']);

            $statusRough = $this->cleanBullshitStr($row['C_ST_CONV']);
            if (isset($statusCor[$statusRough])) {
                $status = $statusCor[$statusRough];
            } else {
                $status = Activity::STATUS_DRAFT;
            }

            if (!$montant) {
                $montant = 0.0;
            }
            $nature_conv = trim($row['CODE_NATURE_CONT']);
            $oid_nature_conv = $this->getOscarId('GrantSource', $nature_conv);
            $grantSource = $oid_nature_conv ? $this->getEntityManager()->getRepository('Oscar\Entity\GrantSource')->find($oid_nature_conv) : null;
            $dateCreated = $this->extractDate($row['DATE_CREE']);
            $dateUpdate = $this->extractDate($row['DATE_MAJ']);
            $dateDebut = $this->extractDate($row['DATE_DEBUT']);
            $dateFin = $this->extractDate($row['DATE_FIN']);
            $dateSignature = $this->extractDate($row['DATE_SIGNATURE']);
            $dateCreated = $this->extractDate($row['DATE_CREE']);
            $dateUpdate = $this->extractDate($row['DATE_MAJ']);
            $sousTypeConvention = $this->cleanBullshitStr($row['C_ST_CONT']);

            $stConv = $this->getEntityManager()->getRepository('Oscar\Entity\ContractType')->findOneBy(array(
                'code' => $sousTypeConvention,
            ));

            // Test de données vermoulues
            if (!$code) {
                $this->getLogger()->warn(sprintf("Enregistrement ignoré : ID:'%s' => ACR:'%s' / LIB:'%s' (pas de N° de convention)",
                    $centaureId, $acronym, $libelle));
                continue;
            }

            ////////////////////////////////////////////////////////////////////
            // Premier contrat (même ID que le projet)
            $project = null;

            if ($oscarId) {
                $sum = serialize($row);
                $cache = $this->getSum('Grant', $centaureId);
                $grant = $emGrant->find($oscarId);
                $project = $grant->getProject();

                if ($cache && $cache == $sum) {
                    $this->getLogger()->debug('La données est à jour');
                    continue;
                }
            } else {
                $grant = new Activity();
                $this->getEntityManager()->persist($grant);

                // Trouver le projet
                $previous = $this->cleanBullshitStr($row['NUM_AVENANT_PRECEDENT']);
                if ($previous) {
                    $this->getLogger()->notice("A partir d'un précédent contrat");
                    $avenantPrecedent = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant')->findOneBy(array(
                        'centaureNumConvention' => $previous,
                    ));

                    if (!$avenantPrecedent) {
                        $this->getLogger()->error(sprintf('Impossible de récupérer le projet à partir de l\'avenant précédent ayant le code "%s"',
                            $previous));
                        continue;
                    }

                    $project = $avenantPrecedent->getProject();
                    if (!$project) {
                        $this->getLogger()->error(sprintf("Avenant '%s' sans projet !!!", $avenantPrecedent));
                        continue;
                    }
                } else {
                    // Projet et Contrat à partir de la même source
                    $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->findOneBy(array(
                        'code' => $code,
                    ));

                    if (!$project) {
                        $this->getLogger()->error(sprintf('Impossible de récupérer le projet ayant le code "%s"',
                            $previous));
                        continue;
                    }
                }
            }
            $this->getLogger()->info('Sauvegarde de '.$code);

            $grant->setAmount($montant)
                ->setStatus($status)
                ->setSource($grantSource)
                ->setCodeEOTP($codeEOTP)
                ->setProject($project)
                ->setType($stConv)
                ->setDateSigned($dateSignature)
                ->setDateCreated($dateCreated)
                ->setDateUpdate($dateUpdate)
                ->setDateEnd($dateFin)
                ->setDateStart($dateDebut)
                ->setCentaureNumConvention($code);
            try {
                $this->getEntityManager()->flush($grant);
                $this->setCorrespondance('Grant', $grant->getId(), $centaureId, serialize($row));
            } catch (\Exception $e) {
                var_dump($montant);
                $this->getLogger()->error($e->getMessage());
                throw $e;
            }
        }
        $this->getLogger()->info('Terminé');
        $this->getLogger()->info($processed.' entrée(s) traitée(s).');
        $this->getLogger()->debug('Syncronisation des contrats');
    }

    protected function getProjectFromContractWithPrevious($dataContact)
    {
        $previousContrat = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant')->findOneBy(array(
            'centaureNumConvention' => $dataContact,
        ));

        return $previousContrat;
//        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')
    }

    private $project_cache = array();

    protected function getProjectByNumConvention($numConvention)
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->findOneBy(array('code' => $numConvention));
    }

    protected function syncGrantSource()
    {
        $this->getLogger()->notice('+++ Syncronisation des sources de contrat (GrantSource)');
        $this->syncCachePdo();

        $stid = oci_parse($this->getConnexion(), 'SELECT * FROM NATURE_CONT');
        oci_execute($stid);

        $em = $this->getEntityManager()->getRepository('Oscar\Entity\GrantSource');

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = trim($row['CODE_NATURE_CONT']);
            $centaureLib = $this->cleanBullshitStr($row['LIB_NATURE_CONT']);
            $oscarId = $this->getOscarId('GrantSource', $centaureId);

            if ($centaureId == '') {
                continue;
            }

            if (!$oscarId) {
                $this->getLogger()->info(sprintf('Création de `%s`',
                    $centaureId));
                $grantSource = new GrantSource();
                $this->getEntityManager()->persist($grantSource);
            } else {
                $sum = $this->getSum('GrantSource', $centaureId);
                if ($sum != serialize($row)) {
                    $this->getLogger()->info(sprintf('Mise à jour de `%s`',
                        $centaureId));
                    $grantSource = $em->find($oscarId);
                } else {
                    $this->getLogger()->debug(sprintf('Aucun changement pour `%s`',
                        $centaureId));
                    continue;
                }
            }
            $grantSource->setLabel($centaureId)
                ->setDescription($centaureLib);

            $this->getEntityManager()->flush($grantSource);
            $this->setCorrespondance('GrantSource', $grantSource->getId(),
                $centaureId, serialize($row));
        }
    }

    protected function syncDiscipline()
    {
        $this->getLogger()->notice('+++ Syncronisation des Disciplines');

        $stid = oci_parse($this->getConnexion(), 'SELECT * FROM DOM_CONV');
        $em = $this->getEntityManager()->getRepository('Oscar\Entity\Discipline');
        $model = 'Discipline';
        oci_execute($stid);

        $nullValue = ['0000000000', 'NA', 'ND'];
        $proceded = 0;
        $created = 0;
        $updated = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $code = $this->cleanBullshitStr($row['C_D_CONV']);
            $label = $this->cleanBullshitStr($row['LIB_DOMAINE_CONVENTION']);
            $this->getLogger()->debug(sprintf("Traitement de '%s':'%s'", $centaureId, $label));

            if (in_array($code, $nullValue)) {
                $this->getLogger()->notice(sprintf("La discipline '%s':'%s' a été ignorée (valeur null).",
                    $code, $label));
                continue;
            }

            $oscarId = $this->getOscarId($model, $centaureId);
            if (!$oscarId) {
                $entity = new Discipline();
                $this->getEntityManager()->persist($entity);
                $this->getLogger()->info(sprintf('Création de la discipline %s:%s', $code, $label));
            } else {
                $sum = $this->getSum($model, $centaureId);
                if ($sum != serialize($row)) {
                    $this->getLogger()->info(sprintf('Mise à jour de la discipline %s:%s', $code, $label));
                    $entity = $em->find($oscarId);
                } else {
                    $this->getLogger()->debug(sprintf('La discipline %s:%s est à jour', $code, $label));
                    continue;
                }
            }
            // Mise à jour des données
            $entity->setLabel($label);

            // Enregistrement en base
            $this->getEntityManager()->flush($entity);

            // Ecriture du cache
            $this->setCorrespondance($model, $entity->getId(), $centaureId,
                serialize($row));
        }
    }

    private function cleanCache()
    {
        $this->syncCachePdo()->query('DELETE FROM Discipline; DELETE FROM Person; DELETE FROM Project; DELETE FROM GrantSource; DELETE FROM Grant; ');
    }

    ////////////////////////////////////////////////////////////////////////////

    private function syncCachePdo()
    {
        static $pdo;
        if (null === $pdo) {
            $this->getLogger()->debug('Récupération de la connection au cache');
            $sqllitePath = realpath(__DIR__.'/../../../../../data/oscar_centaure.sqlite');
            if (!file_exists($sqllitePath)) {
                throw new \Exception(sprintf('Base de données de synchronisation %s absente ! ',
                    $sqllitePath));
            }

            $pdo = new \PDO('sqlite:'.$sqllitePath);
        }

        return $pdo;
    }

    private $_correspondance = array();

    private function getCentaureId($dataType, $oscarId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['OC'][$oscarId])) {
            return $this->_correspondance[$dataType]['OC'][$oscarId];
        }

        return;
    }

    private function getOscarId($dataType, $centaureId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['CO'][$centaureId])) {
            return $this->_correspondance[$dataType]['CO'][$centaureId];
        }

        return;
    }

    /**
     * @param $dataType Nom du model
     * @param $centaureId ID côté centaure
     *
     * @return string | null
     *
     * @throws \Exception
     */
    private function getSum($dataType, $centaureId)
    {
        if (!isset($this->_correspondance[$dataType])) {
            $this->_correspondance[$dataType] = $this->getCorrespondance($dataType);
        }
        if (isset($this->_correspondance[$dataType]['SUM'][$centaureId])) {
            return $this->_correspondance[$dataType]['SUM'][$centaureId];
        }

        return;
    }

    private function getCorrespondance($data)
    {
        $pdo = $this->syncCachePdo();

        $stt = $pdo->query('SELECT * FROM '.$data);
        if (!$stt) {
            throw new \Exception('Impossible de charger le cache pour '.$data);
        }
        $table = [
            'SUM' => [],
            'CO' => [],
            'OC' => [],
        ];
        $datas = $stt->fetchAll(\PDO::FETCH_ASSOC);
        $this->getLogger()->info(sprintf("Cache pour '%s' chargé avec %s enregistrement(s).", $data, count($datas)));

        foreach ($datas as $row) {
            $table['SUM'][$row['centaure_id']] = $row['checksum'];
            $table['CO'][$row['centaure_id']] = $row['oscar_id'];
            $table['OC'][$row['oscar_id']] = $row['centaure_id'];
        }

        return $table;
    }

    private function setCorrespondance($data, $oscarId, $centaureId, $sum)
    {
        $this->getLogger()->debug(sprintf('Écriture du cache %s : oscar:%s <> centaure:%s', $data, $oscarId, $centaureId));
        $pdo = $this->syncCachePdo();
        $stt = $pdo->prepare('INSERT INTO '.$data.'(centaure_id, oscar_id, checksum) VALUES(:c, :o, :s)');

        $stt->execute(array(
            'c' => $centaureId,
            'o' => $oscarId,
            's' => $sum,
        ));
    }

    ////////////////////////////////////////////////////////////////////////////
    private function cleanBullshitStr($bullshitStr)
    {
        return trim($bullshitStr);
    }

    private function extractDate($bullshitDate)
    {
        $date = \DateTime::createFromFormat('Ymd', $bullshitDate);

        return $date ? $date : null;
    }

    /**
     * Returns a string containing a banner text, that describes the module and/or the application.
     * The banner is shown in the console window, when the user supplies invalid command-line parameters or invokes
     * the application with no parameters.
     *
     * The method is called with active Zend\Console\Adapter\AdapterInterface that can be used to directly access Console and send
     * output.
     *
     * @param AdapterInterface $console
     *
     * @return string|null
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        return 'Oscar Syncronizer v1.0';
    }

    /**
     * Returns an array or a string containing usage information for this module's Console commands.
     * The method is called with active Zend\Console\Adapter\AdapterInterface that can be used to directly access
     * Console and send output.
     *
     * If the result is a string it will be shown directly in the console window.
     * If the result is an array, its contents will be formatted to console window width. The array must
     * have the following format:
     *
     *     return array(
     *                'Usage information line that should be shown as-is',
     *                'Another line of usage info',
     *
     *                '--parameter'        =>   'A short description of that parameter',
     *                '-another-parameter' =>   'A short description of another parameter',
     *                ...
     *            )
     *
     * @param AdapterInterface $console
     *
     * @return array|string|null
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'oscar sync' => 'Syncronisation des données avec centaure',
        );
    }
}
