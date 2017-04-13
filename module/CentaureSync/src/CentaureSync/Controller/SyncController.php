<?php
namespace CentaureSync\Controller;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Jacksay\PhpFileExtension\Dictonary\ArchiveDictonary;
use Jacksay\PhpFileExtension\Dictonary\DocumentDictionary;
use Jacksay\PhpFileExtension\Dictonary\ImageDictonary;
use Jacksay\PhpFileExtension\Dictonary\OfficeDocumentDictonary;
use Jacksay\PhpFileExtension\Exception\NotFoundExtension;
use Jacksay\PhpFileExtension\PhpFileExtension;
use Jacksay\PhpFileExtension\Strategy\MimeProvider;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\ContractType;
use Oscar\Entity\GrantSource;
use Moment\Moment;
use Monolog\Handler\StdoutHandler;
use Monolog\Logger;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Discipline;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\Activity;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\TypeDocument;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\LoggerStdoutColor;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Utils\EntityHydrator;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/10/15 15:17
 * @copyright Certic (c) 2015
 */
class SyncController extends AbstractActionController
{
    private $logLevel = self::DEBUG;

    // DONNÉES MOISIES
    private $discipline_null = ['0000000000', 'NA', 'ND'];
    private $organization_null = ['0000000000'];

    const DEBUG = 1;
    const PROD = 10;

    private $levels = [
        self::PROD => 'prod ',
        self::DEBUG => 'debug',
    ];

    private $simulate;


    private function cleanBullshitStr($bullshitStr)
    {
        return trim($bullshitStr);
    }

    private function cleanBullshitId($bullshitStr)
    {
        $bullshitStr = $this->cleanBullshitStr($bullshitStr);
        if ($bullshitStr == '' || $bullshitStr == '0000000000') {
            return null;
        }

        return $bullshitStr;
    }

    private function extractDate($bullshitDate)
    {
        $date = \DateTime::createFromFormat('Ymd', $bullshitDate);
        if ($date) {
            $date->setTime(0, 0, 0);
        }

        return $date ? $date : null;
    }

    private function cleanFileName($bullShitPath)
    {
        $sep = explode('\\', $bullShitPath);

        return $sep[count($sep) - 1];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

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
            $conn = \oci_connect($config['user'], $config['password'],
                $config['host'], 'AL32UTF8');
            if (!$conn) {
                $e = oci_error();
                throw new \Exception($e);
            }
        }

        return $conn;
    }

    protected function log($message, $level = self::PROD)
    {
        if ($level >= $this->logLevel) {
            error_log(sprintf("%s [%s] %s", date('H:i:s'),
                $this->levels[$this->logLevel], $message));
        }
    }

    public function execPresta()
    {
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des prestations');
        $stid = oci_parse($c, "SELECT * FROM CONVENTION");
        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            if( !$acronym ){
                continue;
            }
            $activity = $this->getActivityBycentaureId($centaureId);
            if( $activity && $activity->getProject() && !$activity->getProject()->getAcronym() ){
                $this->getLogger()->debug(sprintf("Update '%s' > '%s'", $activity->getProject()->getAcronym(), $acronym));
            }

        }
    }

    public function execAcronym()
    {
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des acronymes');
        $stid = oci_parse($c, "SELECT * FROM CONVENTION");
        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            if( !$acronym ){
                continue;
            }
            $activity = $this->getActivityBycentaureId($centaureId);
            if( $activity && $activity->getProject() && !$activity->getProject()->getAcronym() ){
                $this->getLogger()->debug(sprintf("Update '%s' > '%s'", $activity->getProject()->getAcronym(), $acronym));
            }

        }
    }

    public function execProjectOrphans()
    {

        $activitesCentaureSansProjet = $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->where('a.centaureId IS NOT NULL AND a.project IS NULL')
            ->getQuery()
            ->getResult();

        $this->getLogger()->info(sprintf("Il y'a %s activités orphelines issues de centaures...", count($activitesCentaureSansProjet)));

        $byPfi = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a')
            ->where('a.codeEOTP = :pfi');

        $byNumConvention = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a')
            ->where('a.centaureNumConvention = :num');




        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c, "SELECT * FROM CONVENTION");
        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $numConvention = $this->cleanBullshitStr($row['NUM_CONVENTION']);

            $previousCod = $this->cleanBullshitStr($row['NUM_AVENANT_PRECEDENT']);
            $nextCod = $this->cleanBullshitStr($row['NUM_AVENANT_SUIVANT']);

            $previousNum = $this->cleanBullshitStr($row['CONV_CLEUNIK_AV_PREC']);
            $nextNum = $this->cleanBullshitStr($row['CONV_CLEUNIK_AV_PREC']);
            $liee = $this->cleanBullshitStr($row['CONV_CLEUNIK_LIEE']);
            if( $liee == '0000000000' ){
                $liee = null;
            }

            $activity = $this->getActivityBycentaureId($centaureId);

            if( !$activity ){
                $this->getLogger()->error(sprintf("Aucune activité associé à l'identifiant centaure %s", $centaureId));
                continue;
            } else {
                if( !$activity->getProject() ){
                    $pfi = $this->cleanBullshitStr($row['E_VC_COD']);

                    if( $pfi ){
                        ////////////////////////////////////////////////////////
                        $activitiesMêmePFI = $byPfi->getQuery()->setParameter('pfi', $pfi)->getResult();
                        if($activitiesMêmePFI){
                            $project = null;
                            foreach( $activitiesMêmePFI as $a ){
                                if( !$a->getProject() ){
                                    continue;
                                }
                                if( $project && $project->getId() != $a->getProject()->getId() ){
                                    $this->getLogger()->error(sprintf("DEPLACEMENT de '%s' vers '%s'", $a->getProject(), $project));
                                    $a->setProject($project);
                                    $this->getEntityManager()->flush($a);
                                    continue;
                                }
                                if( $a->getProject() ){
                                    $project = $a->getProject();
                                }
                            }
                            if( $project ){
                                $this->getLogger()->info(sprintf("UPDATE activité '%s' placée dans le projet '%s'", $activity, $project));
                                $activity->setProject($project);
                                $this->getEntityManager()->flush($activity);

                            } else {
                                $this->getLogger()->info(sprintf("CREATION d'un projet pour %s et actualisation des activités", $activity));
                            }
                        } else {
                            $this->getLogger()->info(sprintf("CREATION d'un projet pour %s", $activity));
                        }
                        ////////////////////////////////////////////////////////
                    }

                    if($previousCod){
                        $this->getLogger()->info("Traitement à partir de l'Avenant précédent");
                        try {
                            $previous = $byNumConvention->setParameters(['num' =>$previousCod])->getQuery()->getSingleResult();
                            if( $previous->getProject() ){
                                if( $activity->getProject() != $previous->getProject() ){
                                    $this->getLogger()->info(sprintf("A partir de la convention précédente", $previousCod));
                                    $activity->setProject($previous->getProject());
                                    $this->getEntityManager()->flush($activity);
                                }
                            }
                            else {
                                $this->getLogger()->warn(sprintf("Création d'un projet pour y aggréger la convention avec la précédente", $previousCod));
                            }
                            continue;
                        }
                        catch( NoResultException $e ){
                            $this->getLogger()->error(sprintf("La convention %s semble absente d'OSCAR !!!", $previousCod));
                        }
                        catch( NonUniqueResultException $e ){
                            $this->getLogger()->error(sprintf("Plusieurs conventions partage ce numéro %s!!!", $previousCod));
                        }
                        catch( \Exception $e ){
                            $this->getLogger()->error(sprintf("Truc bizzar avec %s!!! > %s", $previousCod, $e->getTraceAsString()));
                        }
                    }


                    if( $nextCod ){
                        try {
                            $next = $byNumConvention->setParameters(['num' =>$nextCod])->getQuery()->getSingleResult();
                            if( $next->getProject() ){
                                if( $activity->getProject() != $next->getProject() ){
                                    $this->getLogger()->info(sprintf("A partir de la convention suivante", $nextCod));
                                    $activity->setProject($next->getProject());
                                    $this->getEntityManager()->flush($activity);
                                }
                            }
                            else {
                                $this->getLogger()->warn(sprintf("Création d'un projet pour y aggréger la convention avec la suivante", $nextCod));
                            }
                            continue;
                        }
                        catch( NoResultException $e ){
                            $this->getLogger()->error(sprintf("La convention %s semble absente d'OSCAR !!!", $nextCod));
                        }
                        catch( NonUniqueResultException $e ){
                            $this->getLogger()->error(sprintf("Plusieurs conventions partage ce numéro %s!!!", $nextCod));
                        }
                        catch( \Exception $e ){
                            $this->getLogger()->error(sprintf("Truc bizzar avec %s!!! > %s", $nextCod, $e->getTraceAsString()));
                        }
                    }

                    if( $previousNum ){
                        $this->getLogger()->info("Traitement à partir de l'NUM de convention précédent.");
                    }

                    if( $nextNum ){
                        $this->getLogger()->info(sprintf("Recherche du project à partir du numéro de convention suivant"));
                    }

                    if( $liee ){
                        $this->getLogger()->info(sprintf("Recherche du project à partir du numéro de convention liée '%s'.", $liee));
                    }
                }

            }
        }
    }

    /**
     * Cette méthode interroge Centaure pour affecter les disciplines trouvées
     * aux activités.
     *
     * @date 2016-03-22
     */
    public function execDisc()
    {
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c, "SELECT * FROM CONVENTION");
        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureDisciplineId = $this->cleanBullshitStr($row['C_D_CONV']);
            $discipline = $this->getDisciplineByCode($centaureDisciplineId);
            if( !$discipline ){
                continue;
            }
            $activity = $this->getActivityBycentaureId($row['CONV_CLEUNIK']);
            if( !$activity->hasDiscipline($discipline) ){
                $this->getLogger()->debug(sprintf("'%s' ajoutée à '%s'", $discipline, $activity));
                $activity->addDiscipline($discipline);
                $this->getEntityManager()->flush($activity);
            }
        }

        die("DISCIPLINE");
    }

    public function execNumber()
    {
        $this->getLogger()->debug("Numérotation");
        $activities = $this->getEntityManager()->getRepository(Activity::class)->findAll();
        $q = $this->getEntityManager()->createNativeQuery('SELECT activity_num_auto(:id)', new ResultSetMapping());
        /** @var Activity $a */
        foreach ($activities as $a) {
            if (!$a->getOscarNum()) {
                $q->execute(['id' => $a->getId()]);
            }
        }
    }

    /**
     * Syncronise les projets depuis la base de donénes Oracle.
     */
    public function syncAction()
    {
        $this->getLogger();
        if ($this->getRequest()->getParam('silent')) {
            $this->handler->setLevel(Logger::INFO);
        } else {
            $this->handler->setLevel(Logger::DEBUG);
        }

        $this->simulate = $this->getRequest()->getParam('simulate');

        // ACTION
        $action = $this->getRequest()->getParam('doWhat');
        $method = 'exec' . ucfirst($action);


        if (method_exists(get_class($this), $method)) {
            $this->log("Execution de la méthode $method");
            $this->$method();
        } else {
            $this->log("[ERROR] unknow method '$action'");
        }

        return;
    }

    public function execFixProject()
    {
        $q = "SELECT * FROM CONVENTION ORDER BY CONV_CLEUNIK";
        $c = $this->getConnexion();
        $stid = oci_parse($c, $q);
        oci_execute($stid);

        $bySaic = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(Activity::class, 'a')
            ->where('a.centaureNumConvention = :saic');

        $byPFI = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(Activity::class, 'a')
            ->where('a.codeEOTP = :pfi');

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $num = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $pfi = $this->cleanBullshitStr($row['E_VC_COD']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            $liee = $this->cleanBullshitStr($row['CONV_CLEUNIK_LIEE']);
            $prev = $this->cleanBullshitStr($row['CONV_CLEUNIK_AV_PREC']);
            $numP = $this->cleanBullshitStr($row['NUM_AVENANT_PRECEDENT']);

            if( $liee == '0000000000') $liee = null;

            $activity = $this->getActivityBycentaureId($num);

            if( !$num ){
                echo "ERR : pas de N° de convention";
                continue;
            }

            if( !$activity ){
                echo "ERROR : Not found $num\n";
                continue;
            }

            $project = $activity->getProject();
            $projectCreateFrom = null;
            $projectActivities = [$activity];

            // -----------------------------------------------------------------
            // A partir du numéro précédent
            if( $numP ){
                try {
                    $previous = $bySaic->getQuery()->setParameter('saic', $numP)->getSingleResult();
                    if ( $previous->getProject()){
                        if( $activity->getProject() != $previous->getProject() ){
                            echo "CHANGE : utilisation du projet de l'activité précédente.\n";
                            $project = $previous->getProject();
                        }
                    } else {
                        $projectActivities[] = $previous;
                    }
                } catch( \Exception $e ) {
                    echo "error : " . $e->getMessage() . "\n";
                    continue;
                }
            }

            if( $prev ){
                $previousActivity = $this->getActivityBycentaureId($prev);
                if( $previousActivity ){
                    $projectActivities[] = $previousActivity;
                    if( $previousActivity->getProject() ){
                        $project = $previousActivity->getProject();
                    } else {
                        $projectCreateFrom = $previousActivity;
                    }
                }
            }

            if( $liee ){
                $activiteLiee = $this->getActivityBycentaureId($liee);
                if( $activiteLiee ){
                    $projectActivities[] = $activiteLiee;
                    if( $activiteLiee->getProject() ){
                        $project = $activiteLiee->getProject();
                    } else {
                        $projectCreateFrom = $activiteLiee;
                    }
                }
            }

            if( $pfi ){
                // Activités avec le même PFI
                $activitiesPFI = $byPFI->getQuery()->setParameter('pfi', $pfi)->getResult();
                if( $activitiesPFI ){
                    foreach($activitiesPFI as $pfiP){
                        if( $project == null ){
                            $project = $pfiP->getProject();
                            $projectCreateFrom = $pfiP;
                        }
                        $projectActivities[] = $pfiP;
                    }
                }
            }

            if( !$project ){
                if( !$projectCreateFrom ){
                    $projectCreateFrom = $activity;
                }
                echo "CREATION DU PROJET $acronym : \n";
                $project = new Project();
                $this->getEntityManager()->persist($project);
                $project->setLabel($projectCreateFrom->getLabel())
                    ->setAcronym($acronym);
                $this->getEntityManager()->flush($project);
            }

            foreach( $projectActivities as $a ) {
                if ($a->getProject() != $project) {
                    echo "Mise à jour du projet de $a\n";
                    $a->setProject($project);
                    $this->getEntityManager()->flush($a);
                }
            }
        }
    }

    /**
     * @deprecated
     */#link_time=1449593640
    public function execAll()
    {
        $this->execPerson();
        $this->execPersonLdap();
        $this->execOrganization();
        $this->execActivityOrganization();
        $this->execActivityPerson();


        /*$this->execContract();



        $this->execProjectPerson();
        $this->execProject();
        $this->execProjectContract();
        $this->execProjectOrganization();*/
    }

    public function execPatch0405()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des ORGANISATIONS...');
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c, "SELECT * FROM CONVENTION");
        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $num = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $daf = $this->extractDate($row['DATE_PRIS_CHARGE_DAF']);
            if( $daf ){
                $a = $this->getActivityBycentaureId($num);
                $a->setDateOpened($daf);
                $this->getEntityManager()->flush($a);
                echo "update > $num : $a\n";
            }


        }


    }

    /**
     * Synchronisation des documents depuis le partage réseau Centaure.
     *
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function execOrganization()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des ORGANISATIONS...');
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c, "SELECT * FROM OSCAR_PARTENAIRE_AVEC_UFR");
        oci_execute($stid);

        $hydrator = new EntityHydrator([
            'CITY' => array(
                'property' => 'city',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'CODE' => array(
                'property' => 'code',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'DATECREATED' => array(
                'property' => 'dateCreated',
                'cleaner' => function ($data) {
                    return $this->extractDate($data);
                }
            ),
            'DATEUPDATED' => array(
                'property' => 'dateUpdated',
                'cleaner' => function ($data) {
                    return $this->extractDate($data);
                }
            ),
            'EMAIL' => array(
                'property' => 'email',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'FULLNAME' => array(
                'property' => 'fullName',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'SHORTNAME' => array(
                'property' => 'shortName',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'STREE1' => array(
                'property' => 'street1',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'STREE2' => array(
                'property' => 'street2',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'STREE3' => array(
                'property' => 'street3',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'PHONE' => array(
                'property' => 'phone',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'URL' => array(
                'property' => 'url',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
            'ZIPCODE' => array(
                'property' => 'zipCode',
                'cleaner' => function ($data) {
                    return $this->cleanBullshitStr($data);
                }
            ),
        ]);

        $processed = 0;
        $start = microtime(true);

        $qbOrganizationByCentaureId = $this->getEntityManager()->createQueryBuilder()
            ->select('o')
            ->from(Organization::class, 'o')
            ->where('o.centaureId = :centaureId');

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $type = $this->cleanBullshitStr($row['TYPE']);
            $centaureId = $this->cleanBullshitStr($row['CLEUNIK']);

            if (in_array($centaureId, $this->organization_null)) {
                $this->getLogger()->debug('Organization ignorée');
                continue;
            }

            // FIX Construction d'un ID unique en provenance de centaure
            // (les partenaires sont issues de plusieurs tables dont les ID
            // peuvent être identiques). Pour les SITES, l'ID est un code.
            if ($type == 'SITE') {
                $centaureId = 'S' . $centaureId;
            } else {
                $centaureId = substr($this->cleanBullshitStr($row['TYPE']), 0,
                        1) . substr($this->cleanBullshitStr($row['CLEUNIK']),
                        1);
            }

            $dateUpdated = $this->extractDate($row['DATEUPDATED']);

            ++$processed;

            /** @var Organization $organization */
            $organization = null;

            try {
                $organization = $qbOrganizationByCentaureId->setParameter('centaureId',
                    $centaureId)->getQuery()->getSingleResult();
                if ($dateUpdated < $organization->getDateUpdated()) {
                    $this->getLogger()->debug(sprintf("'%s' est à jour",
                        $organization));
                    continue;
                }
            } catch (NoResultException $e) {
                $organization = new Organization();
                $this->getLogger()->debug(sprintf("'%s' va être créé...",
                    $centaureId));
                $this->getEntityManager()->persist($organization);
                $organization->setCentaureId($centaureId);
            }

            $hydrator->hydrate($row, $organization);
            $this->getEntityManager()->flush($organization);
        }

        $this->getLogger()->notice(sprintf("%s traitement en %s secondes.",
            $processed, (microtime(true) - $start)));
    }


    protected function execPartners()
    {
        $c = $this->getConnexion();

        $this->getLogger()->debug("Récupération des données depuis la base CENTAURE");

        $query = "SELECT C.NUM_CONVENTION, P.CONV_CLEUNIK, P.PART_CLEUNIK, P.PRINCIPAL_SECONDAIRE, P.DATE_DEBUT, P.DATE_FIN, P.DATE_CREE, P.DATE_MAJ, P.CODE_ROLE_PART FROM PARTICIPANT P LEFT JOIN CONVENTION C ON C.CONV_CLEUNIK = P.CONV_CLEUNIK";

        $roles = [
            'CONS' => Organization::ROLE_CONSEILLER,
            'COORD' => Organization::ROLE_COORDINATEUR,
            '0000000000' => null,
            'ND' => null,
            'SCIENT' => Organization::ROLE_SCIENTIFIQUE,
            'LICENCIE' => Organization::ROLE_LICENCIE,
            'CLIENT' => Organization::ROLE_CLIENT,
            'SCIENT_R' => Organization::ROLE_SCIENTIFIQUE_R,
            'CO_CONT' => Organization::ROLE_CO_CONTRACTANT,
            'FINAN' => Organization::ROLE_CO_FINANCEUR,
            'NA' => null,
        ];
        $stid = oci_parse($c, $query);
        oci_execute($stid);
        $proceded = 0;

        $projectService = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Project::class, 'p')
            ->where('p.code = :numConvention');

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            // Numéro de convention côté centaure
            $centaureNumConvention = $this->cleanBullshitStr($row['NUM_CONVENTION']);

            // Projet correspondant côté Oscar
            try {
                $project = $projectService->setParameter('numConvention',
                    $centaureNumConvention)->getQuery()->getSingleResult();
            } catch (NoResultException $e) {
                $this->getLogger()->error(sprintf("Impossible de trouver le projet N°%s",
                    $centaureNumConvention));
                continue;
            }
        }
    }


    protected function execDiscipline()
    {
        $this->getLogger()->notice('+++ Syncronisation des Disciplines');

        $stid = oci_parse($this->getConnexion(), 'SELECT * FROM DOM_CONV');
        $em = $this->getEntityManager()->getRepository(Discipline::class);
        oci_execute($stid);

        $proceded = 0;
        $created = 0;
        $updated = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = $code = $this->cleanBullshitStr($row['C_D_CONV']);
            $label = $this->cleanBullshitStr($row['LIB_DOMAINE_CONVENTION']);
            $this->getLogger()->debug(sprintf("Traitement de '%s':'%s'",
                $centaureId, $label));

            if (in_array($code, $this->discipline_null)) {
                $this->getLogger()->notice(sprintf("La discipline '%s':'%s' a été ignorée (valeur null).",
                    $code, $label));
                continue;
            }

            /** @var Discipline $discipline */
            $discipline = null;

            // Récupération dans oscar
            try {
                $discipline = $this->getEntityManager()->getRepository(Discipline::class)->findOneBy(['centaureId' => $centaureId]);
            } catch (NoResultException $e) {
            }

            if (!$discipline) {
                $this->getLogger()->info(sprintf('Création de la discipline %s:%s',
                    $code, $label));
                $discipline = new Discipline();
                $this->getEntityManager()->persist($discipline);
                $discipline->setLabel($label)->setCentaureId($centaureId);
                $this->getEntityManager()->flush($discipline);
            }
        }
    }


    /**
     * Syncronisation des personnes depuis Centaure.
     *
     * @throws \Exception
     */
    protected function execPerson()
    {
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');
        $stid = oci_parse($c,
            "SELECT PER_CLEUNIK AS CLEUNIK, NOM_PATRO AS NOM, PRENOM, E_MAIL AS EMAIL, TELEPHONE AS PHONE, CODE_HARPEGE, DATE_MAJ, DATE_CREE FROM PERSONNEL");
        oci_execute($stid);

        // Pour le rapport
        $processed = 0;
        $created = 0;
        $updated = 0;
        $start = microtime(true);

        $byMail = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Person::class, 'p')
            ->where('p.email = :email');

        $byHarpege = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Person::class, 'p')
            ->where('p.codeHarpege = :codeHarpege');

        $byPrenomNom = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Person::class, 'p')
            ->where('LOWER(p.lastname) = LOWER(:lastname)')
            ->andWhere('LOWER(p.firstname) = LOWER(:firstname)');

        $byCentaureId = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Person::class, 'p')
            ->where("p.centaureId LIKE :centaureId");

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $centaureId = $this->cleanBullshitStr($row['CLEUNIK']);
            $nom = $this->cleanBullshitStr($row['NOM']);
            $prenom = $this->cleanBullshitStr($row['PRENOM']);
            $codeHarpege = $this->cleanBullshitStr($row['CODE_HARPEGE']);
            $telephone = $this->cleanBullshitStr($row['PHONE']);
            $email = $this->cleanBullshitStr(strtolower($row['EMAIL']));
            $dateCreated = $this->extractDate($row['DATE_CREE']);
            $dateUpdate = $this->extractDate($row['DATE_MAJ']);

            if ($codeHarpege == '0000000000') {
                $this->getLogger()->error('Enregistrement avec Code Harpège ' . $codeHarpege . ' ignoré !');
                continue;
            }

            if ($codeHarpege == '?') {
                $codeHarpege = '';
            }

            ////////////////////////////////////////////////////////////////////
            // Récupération dans oscar
            ////////////////////////////////////////////////////////////////////

            /** @var Person $person */
            $person = null;

            // On commence par tenter de récupérer la personne via d'ID centaure.
            try {
                $person = $byCentaureId->setParameter('centaureId',
                    "%$centaureId%")->getQuery()->getSingleResult();
            } catch (NonUniqueResultException $ex) {
                $this->getLogger()->error(sprintf("L'ID centaure %s doit être unique!!!",
                    $centaureId));
            } catch (NoResultException $ex) {
                $this->getLogger()->notice(sprintf("Personne dans Oscar n'est enregistré avec l'ID centaure '%s'",
                    $centaureId));
            }

            // Si la personne n'a pas d'ID centaure, on cherche à partir des autres informations
            if (!$person) {

                //// Via l'email
                $mailLog = $email;
                if (!$email) {
                    $email = strtolower($prenom . '.' . $nom . '@unicaen.fr');
                    $mailLog = "Généré avec pernom.nom : $email";
                }

                try {
                    $person = $byMail->setParameter('email',
                        $email)->getQuery()->getSingleResult();
                } catch (NonUniqueResultException $ex) {
                    $this->getLogger()->warn(sprintf("Le mail %s n'est pas unique dans Oscar (%s %s)",
                        $mailLog, $prenom, $nom));
                } catch (NoResultException $ex) {
                    $this->getLogger()->debug(sprintf("Personne dans Oscar n'est enregistré avec le mail %s",
                        $mailLog));
                }

                //// Via le code harpège
                if (!$person && $codeHarpege) {
                    try {
                        $person = $byHarpege->setParameter('codeHarpege',
                            $codeHarpege)->getQuery()->getSingleResult();
                    } catch (NonUniqueResultException $ex) {
                        $this->getLogger()->warn(sprintf("Le code harpège %s n'est pas unique dans Oscar (%s %s)",
                            $codeHarpege, $prenom, $nom));
                    } catch (NoResultException $ex) {
                        $this->getLogger()->debug(sprintf("Personne dans Oscar n'est enregistré avec le code harpège %s",
                            $codeHarpege));
                    }
                }

                // Via le nom + prénom
                // Douteux en raison des homonymes fréquents
                if (!$person) {
                    try {
                        $person = $byPrenomNom->setParameters([
                            'firstname' => $prenom,
                            'lastname' => $nom,
                        ])->getQuery()->getSingleResult();
                    } catch (NonUniqueResultException $ex) {
                        $this->getLogger()->warn(sprintf("La combinaison P:%s N:%s n'est pas unique dans Oscar",
                            $prenom, $nom));
                    } catch (NoResultException $ex) {
                        $this->getLogger()->debug(sprintf("P:%s N:%s n'existe pas dans Oscar",
                            $prenom, $nom));
                    }
                }

                //// ? Autre critère ?
            }


            // Personne n'a été trouvé, on va créer
            if (!$person) {
                $this->getLogger()->info(sprintf("Création de %s %s", $prenom,
                    $nom));
                $person = new Person();
                $this->getEntityManager()->persist($person);
                $person->setCentaureId($centaureId)
                    ->setCodeHarpege($codeHarpege)
                    ->setFirstname($prenom)
                    ->setLastname($nom)
                    ->setDateCreated($dateCreated)
                    ->setDateUpdated($dateUpdate)
                    ->setEmail($email);
                $this->getEntityManager()->flush($person);
                $created++;
            } // Personne trouvé, on met à jour si besoin
            else {
                $change = false;

                if (!$centaureId) {
                    $this->getLogger()->error("ID centaure non trouvé : $centaureId");
                    continue;
                } elseif (!$person->hasCentaureId($centaureId)) {
                    $person->setCentaureId($centaureId);
                    $change = true;
                }

                if ($change) {
                    $this->getLogger()->info("Mise à jour de $person ID(s)" . implode(',',
                            $person->getCentaureId()) . ")");
                    $person->setDateUpdated(new \DateTime());
                    $this->getEntityManager()->flush($person);
                    $this->getLogger()->info("----");
                    $updated++;
                }
            }
        }
        $this->getLogger()->notice(sprintf('%s traitement(s) en %s millsec.',
            $processed, (microtime(true) - $start)));
        $this->getLogger()->notice(sprintf(' - %s personne(s) ajoutée(s).',
            $created));
        $this->getLogger()->notice(sprintf(' - %s personne(s) mise à jour.',
            $updated));
    }

    /**
     * Cette méthode synchronise les projets enregistrés dans centaure.
     *
     * Note : Les projets sont construits à partir du premier contrat (sans
     * avenant précédent).
     */
    public function execProject()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des PROJETS...');

        // Récupération des projets dans centaure
        $processed = 0;
        $nbrProject = 0;
        $start = microtime(true);

        $this->getLogger()->notice('+++ Syncronisation des contrats');

        // Récupération des projets dans centaure
        $c = $this->getConnexion();
        $emGrant = $this->getEntityManager()->getRepository(Activity::class);
        $model = 'Activity';
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c,
            'SELECT CODE_UP, DUREE, MONTANT_A_JUSTIFIER, E_VC_COD, C_ST_CONV, C_ST_CONT, CODE_NATURE_CONT, NUM_AVENANT_PRECEDENT, NUM_CONVENTION, DATE_CREE, C_D_CONV, DATE_MAJ, CONV_CLEUNIK, DATE_DEBUT, DATE_OUVERTURE, DATE_FIN, DATE_SIGNATURE, MONTANT_FACTURE_HT, MONTANT_A_JUSTIFIER, E_VC_COD, ACRONYME_CONV, LIB_CONVENTION FROM CONVENTION');
        oci_execute($stid);

        $qbProject = $this->getEntityManager()->createQueryBuilder()->select('p')
            ->from(Project::class, 'p')
            ->where("p.eotp = :eotp");

        $qbActivities = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a')
            ->where("a.codeEOTP = :eotp");

        $coefUp = array(
            'S' => 7,
            'J' => 1,
            'A' => 365,
            'M' => 30,
            'H' => 0,
            '000000' => 0
        );

        $statusCor = array(
            'CAC' => 1,
            'CCL' => 2,
            'DAB' => 0,
            'CRE' => 0,
            'DEC' => 3,
        );

        $eotpExist = [];
        $processed = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $codeEOTP = $this->cleanBullshitStr($row['E_VC_COD']);

            if (!$codeEOTP) {
                continue;
            }

            echo "$codeEOTP";

            if (isset($eotpExist[$codeEOTP])) {
                $project = $eotpExist[$codeEOTP];
            } else {
                $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
                $libelle = $this->cleanBullshitStr($row['LIB_CONVENTION']);
                $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
                $code = $this->cleanBullshitStr($row['NUM_CONVENTION']);
                $dateCreated = $this->extractDate($row['DATE_CREE']);

                $project = $qbProject->setParameter('eotp',
                    $codeEOTP)->getQuery()->getFirstResult();
                if (!$project) {
                    echo " Create";
                    $project = new Project();
                    $this->getEntityManager()->persist($project);
                    $project->setAcronym($acronym)
                        ->setLabel($libelle)
                        ->setDateCreated($dateCreated)
                        ->setCode($code)
                        ->setEotp($codeEOTP)
                        ->setCentaureId($centaureId);
                    $this->getEntityManager()->flush($project);
                }
                $eotpExist[$codeEOTP] = $project;
            }

            if ($project) {
                $contracts = $qbActivities->setParameter('eotp',
                    $codeEOTP)->getQuery()->getResult();
                foreach ($contracts as $contract) {
                    if (!($contract->getProject() && $contract->getProject()->getId() == $project->getId())) {
                        echo " Assoc to project " . $project->getLabel();
                        $contract->setProject($project);
                        $this->getEntityManager()->flush($contract);
                    } else {
                        echo "Up to d.";
                    }
                }
            }


            echo "\n";
        }

        /*
                $qbActivities = $this->getEntityManager()->createQueryBuilder()->select('a')
                    ->from(Activity::class, 'a')
                    ->where("a.codeEOTP IS NOT NULL AND a.codeEOTP != ''")
                    ->orderBy('a.codeEOTP', 'DESC')
                    ->addOrderBy('a.centaureId');

                $qbProject = $this->getEntityManager()->createQueryBuilder()->select('p')
                    ->from(Project::class, 'p')
                    ->where("p.eotp = :eotp");

                $eotpExist = [];


                foreach( $qbActivities->getQuery()->getResult() as $activity ){
                    $processed++;
                    if( $activity->getProject() ){
                        continue;
                    }

                    $eotp = $activity->getCodeEOTP();

                    if( isset($eotpExist[$eotp]) ){
                        $project = $eotpExist[$eotp];
                    } else {
                        try {
                            $project = $qbProject->setParameter('eotp', $eotp)->getQuery()->getSingleResult();
                        } catch( NoResultException $e ){
                            echo "Create project from contract $eotp\n";
                            $project = new Project();
                            $this->getEntityManager()->persist($project);
                            $project->setEotp($eotp)
                                ->setAcronym('IMPORTED')
                                ->setLabel($activity->getLabel())
                                ->setDescription($activity->getDescription())
                            ;
                            $this->getEntityManager()->flush($project);
                        }
                        $nbrProject++;
                    }
                    $activity->setProject($project);
                    $this->getEntityManager()->flush($activity);

                }
                */

        $this->getLogger()->notice(sprintf('%s traitement(s) en %s millsec.',
            $processed, (microtime(true) - $start)));
        $this->getLogger()->notice(sprintf('%s projet(s).', $nbrProject));
    }

    /**
     * Factorisation des données projet
     */
    protected function execProjectFactor()
    {
        $qbProject = $this->getEntityManager()->createQueryBuilder()->select('p, a, m, pr')
            ->from(Project::class, 'p')
            ->leftJoin('p.grants', 'a')
            ->leftJoin('a.persons', 'm')
            ->leftJoin('m.person', 'pr');

        /** @var Project $project */
        foreach ($qbProject->getQuery()->getResult() as $project) {
            echo "##############################################################\n Traitement de $project \n";
            // Suppression des doublons (Person)
            foreach ($project->getGrants() as $grant) {
                foreach ($grant->getPersons() as $member) {
                    if ($project->hasPerson($member->getPerson(),
                        $member->getRole())
                    ) {
                        $this->getEntityManager()->remove($member);
                        $this->getEntityManager()->flush($member);
                        echo " - Suppression initiale de " . $member->getPerson() . " (déjà présent au niveau projet).\n";
                    }
                }
                foreach ($grant->getOrganizations() as $partner) {
                    if ($project->hasPartner($partner->getOrganization(),
                        $partner->getRole())
                    ) {
                        $this->getEntityManager()->remove($partner);
                        $this->getEntityManager()->flush($partner);
                        echo " - Suppression initiale de " . $partner->getOrganization() . " (déjà présent au niveau projet).\n";
                    }
                }
            }

            if (count($project->getGrants()) > 1) {
                $personsIds = [];
                $organizationsIds = [];

                /** @var Activity $grant */
                foreach ($project->getGrants() as $grant) {
                    foreach ($grant->getPersons() as $member) {
                        $key = $member->getPerson()->getId() . $member->getRole();

                        if (!isset($personsIds[$key])) {
                            $personsIds[$key] = [
                                'member' => [$member],
                                'person' => $member->getPerson(),
                                'role' => $member->getRole(),
                                'nbr' => 1
                            ];
                        } else {
                            $personsIds[$key]['nbr']++;
                            $personsIds[$key]['member'][] = $member;
                        }
                    }

                    /** @var ActivityOrganization $partner */
                    foreach ($grant->getOrganizations() as $partner) {
                        $key = $partner->getOrganization()->getId() . $partner->getRole();

                        if (!isset($organizationsIds[$key])) {
                            $organizationsIds[$key] = [
                                'partner' => [$partner],
                                'organization' => $partner->getOrganization(),
                                'role' => $partner->getRole(),
                                'nbr' => 1
                            ];
                        } else {
                            $organizationsIds[$key]['nbr']++;
                            $organizationsIds[$key]['member'][] = $partner;
                        }
                    }
                }
                foreach ($personsIds as $d) {
                    if ($d['nbr'] == count($project->getGrants())) {
                        if (!$project->hasPerson($d['person'], $d['role'])) {
                            //echo " - Déplacement de " . $d['person'] . '(' . $d['role'] . ") au niveau projet.\n";
                            $newmember = new ProjectMember();
                            $this->getEntityManager()->persist($newmember);
                            $newmember->setPerson($d['person'])->setRole($d['role'])->setProject($project);
                            $this->getEntityManager()->flush($newmember);
                        } else {
                            //echo " - Suppression de ".$d['person'] . '(' . $d['role'] . ") du contrat\n";
                            foreach ($d['member'] as $m) {
                                $this->getEntityManager()->remove($m);
                            }
                        }
                    } else {
                        //echo " - !!!" . $d['person'] . '(' . $d['role'] . ") laissé au niveau de l'activité\n";
                    }
                }

                foreach ($organizationsIds as $d) {
                    if ($d['nbr'] == count($project->getGrants())) {
                        if (!$project->hasPartner($d['organization'],
                            $d['role'])
                        ) {
                            echo " - Déplacement de " . $d['organization'] . '(' . $d['role'] . ") au niveau projet.\n";
                            $newPartner = new ProjectPartner();
                            $this->getEntityManager()->persist($newPartner);
                            $newPartner->setOrganization($d['organization'])->setRole($d['role'])->setProject($project);
                            $this->getEntityManager()->flush($newPartner);
                        } else {
                            //echo " - Suppression de ".$d['organization'] . '(' . $d['role'] . ") du contrat\n";
                            foreach ($d['member'] as $m) {
                                $this->getEntityManager()->remove($m);
                            }
                        }
                    } else {
                        //echo " - !!!" . $d['organization'] . '(' . $d['role'] . ") laissé au niveau de l'activité\n";
                    }
                }
            } else {
                /** @var Activity $grant */
                if (count($project->getGrants()) == 0) {
                    continue;
                }
                $grant = $project->getGrants()[0];

                foreach ($grant->getPersons() as $member) {
                    if (!$project->hasPerson($member->getPerson(),
                        $member->getRole())
                    ) {
                        $newmember = new ProjectMember();
                        $this->getEntityManager()->persist($newmember);
                        $newmember->setPerson($member->getPerson())->setRole($member->getRole())->setProject($project);
                        $this->getEntityManager()->flush($newmember);
                    }
                    $this->getEntityManager()->remove($member);
                }

                /** @var ActivityOrganization $partner */
                foreach ($grant->getOrganizations() as $partner) {
                    if (!$project->hasPartner($partner->getOrganization(),
                        $partner->getRole())
                    ) {
                        $newmember = new ProjectPartner();
                        $this->getEntityManager()->persist($newmember);
                        $newmember->setOrganization($partner->getOrganization())->setRole($partner->getRole())->setProject($project);
                        $this->getEntityManager()->flush($newmember);
                    }
                    $this->getEntityManager()->remove($partner);
                }
            }
            // echo "\n";
        }
        $this->getEntityManager()->flush();
    }


    protected function execPersonLdap()
    {
        /** @var PersonService $personService */
        $personService = $this->getServiceLocator()->get('PersonService');

        $persons = $personService->getPersons();
        foreach ($persons as $person) {
            $this->getLogger()->debug(sprintf("Syncronisation de '%s' depuis Ldap",
                $person));
            try {
                $personService->syncLdap($person);
            } catch (\Exception $e) {
                $this->getLogger()->debug(sprintf("Erreur '%s'",
                    $e->getMessage()));
            }
        }
    }

    protected function execProjectContract()
    {
        $this->getLogger()->notice('+++ SYNCRONISATION des relations PROJETS <> CONTRACT');

        // Récupération des convention dans centaure
        $c = $this->getConnexion();
        $stid = oci_parse($c,
            "SELECT E_VC_COD, CONV_CLEUNIK, CONV_CLEUNIK_LIEE, CONV_CLEUNIK_AV_PREC, CONV_CLEUNIK_AV_SUIV, NUM_CONVENTION, DATE_CREE, DATE_MAJ FROM CONVENTION WHERE CONV_CLEUNIK_AV_PREC != ' ' ORDER BY CONV_CLEUNIK");
        oci_execute($stid);

        $processed = 0;
        $start = microtime(true);


        $qBproject = $this->getEntityManager()->createQueryBuilder()->select('p')
            ->from(Project::class, 'p')
            ->where('p.centaureId = :centaureId');


        $qBcontract = $this->getEntityManager()->createQueryBuilder()->select('g')
            ->from(Activity::class, 'g')
            ->where('g.centaureId = :centaureId');

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;
            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $previousId = $this->cleanBullshitStr($row['CONV_CLEUNIK_AV_PREC']);

            try {
                /** @var Activity $contract */
                $contract = $qBcontract->setParameter('centaureId',
                    $centaureId)->getQuery()->getSingleResult();
            } catch (\Exception $e) {
                $this->getLogger()->error(sprintf("Le contract '%s' n'est pas syncronisé dans Oscar",
                    $centaureId));
                continue;
            }

            /** @var Project $project */
            $project = null;
            try {
                // A partir du projet...
                $project = $qBproject->setParameter('centaureId',
                    $previousId)->getQuery()->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                try {
                    // A partir du contrat
                    $subContract = $qBcontract->setParameter('centaureId',
                        $previousId)->getQuery()->getSingleResult();

                    if (!$subContract->getProject()) {
                        // TODO
                    }
                } catch (\Doctrine\ORM\NoResultException $e) {
                }
            }

            if (!$project) {
                $this->getLogger()->error(sprintf("Impossible de charger le projet ou le contract avec l'ID '%s'",
                    $previousId));
            }
            if ($contract->getProject() != $project) {
                $contract->setProject($project);
                $this->getEntityManager()->flush($contract);
            }
        }


        /*
        $contracts = $this->getEntityManager()->getRepository(Activity::class)->findAll();

        foreach ($contracts as $contract) {
            if ($contract->getCodeEOTP()) {
                $project = $this->getEntityManager()->getRepository(Project::class)->findBy([
                    'eotp' => $contract->getCodeEOTP()
                ]);
                if (count($project) == 1) {
                    if( $contract->getProject() !== $project[0] ) {
                        $this->getLogger()->info("Contrat associé.");
                        $contract->setProject($project[0]);
                        $this->getEntityManager()->flush($contract);
                    }
                    continue;
                } elseif (count($project) == 0) {
                    $this->getLogger()->warn("erreur EOTP, ".count($project). ' projet avec le N° ' . $contract->getCodeEOTP());
                }
            }

            $project = $this->getEntityManager()->getRepository(Project::class)->findOneBy([
                'centaureId' => $contract->getCentaureId()
            ]);
            if ($project) {
                if( $contract->getProject() !== $project ) {
                    $this->getLogger()->info("Contrat associé.");
                    $contract->setProject($project);
                    $this->getEntityManager()->flush($contract);
                }
                continue;
            } else {
                $this->getLogger()->error("improbable, pas de projet avec l'ID " . $contract->getCentaureId());
            }


        }
        */
    }


    private function getProjectRecursive($centaureId)
    {
        $contract = $this->getGrantByCentaureId($centaureId);

        return $contract->getProject();
    }

    /**
     * @param $centaureId
     * @return Project
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function getProjectByCentaureId($centaureId)
    {
        static $qbProjectCentaure;
        if ($qbProjectCentaure === null) {
            $qbProjectCentaure = $this->getEntityManager()->createQueryBuilder()
                ->select('p')
                ->from(Project::class, 'p')
                ->where('p.centaureId = :centaureId');
        }
        try {
            return $qbProjectCentaure->setParameter('centaureId',
                $centaureId)->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new \Exception(sprintf("Impossible de chargé le projet avec l'ID centaure '%s'",
                $centaureId));
        }
    }

    /**
     * @param $centaureId
     * @return Activity
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function getGrantByCentaureId($centaureId)
    {
        static $qbGrantCentaure;
        if ($qbGrantCentaure === null) {
            $qbGrantCentaure = $this->getEntityManager()->createQueryBuilder()
                ->select('g')
                ->from(Activity::class, 'g')
                ->where('g.centaureId = :centaureId');
        }
        try {
            return $qbGrantCentaure->setParameter('centaureId',
                $centaureId)->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new \Exception(sprintf("Impossible de chargé le contrat avec l'ID centaure '%s'",
                $centaureId));
        }
    }

    /**
     * @param $centaureId
     * @return Organization
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function getOrganizationByCentaureId($centaureId)
    {
        static $qbOrgaCentaure;
        if ($qbOrgaCentaure === null) {
            $qbOrgaCentaure = $this->getEntityManager()->createQueryBuilder()
                ->select('o')
                ->from(Organization::class, 'o')
                ->where('o.centaureId = :centaureId');
        }
        try {
            return $qbOrgaCentaure->setParameter('centaureId',
                $centaureId)->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new \Exception(sprintf("Impossible de chargé l'organisation avec l'ID centaure '%s'",
                $centaureId));
        }
    }

    /**
     * @param $centaureId
     * @return Organization
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function getOrganizationByCode($code)
    {
        static $qbOrgaCodeCentaure;
        if ($qbOrgaCodeCentaure === null) {
            $qbOrgaCodeCentaure = $this->getEntityManager()->createQueryBuilder()
                ->select('o')
                ->from(Organization::class, 'o')
                ->where('o.code = :code');
        }
        try {
            return $qbOrgaCodeCentaure->setParameter('code',
                $code)->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new \Exception(sprintf("Impossible de chargé l'organisation avec le code centaure '%s'",
                $code));
        }
    }




    /**
     * @return Person[]
     */
    private function personsDictonnary()
    {
        static $personsDictonary;
        if ($personsDictonary === null) {
            $personsDictonary = [
                "centaure" => [],
                "oscar" => [],
            ];
            /** @var Person $person */
            foreach ($this->getEntityManager()->getRepository(Person::class)->findAll() as $person) {
                foreach ($person->getCentaureId() as $idCentaure) {
                    $personsDictonary['centaure'][$idCentaure] = $person;
                }
                $personsDictonary['oscar'][$person->getId()] = $person;
            }
        }

        return $personsDictonary;
    }

    /**
     * @return Person[]
     */
    private function getPersonsOscar()
    {
        return $this->personsDictonnary()['oscar'];
    }

    /**
     * @return Person[]
     */
    private function getPersonsCentaure()
    {
        return $this->personsDictonnary()['centaure'];
    }

    /**
     * @param $centaureId
     * @return null|Person
     */
    private function getPersonCentaure($centaureId)
    {
        return isset($this->getPersonsCentaure()[$centaureId]) ? $this->getPersonsCentaure()[$centaureId] : null;
    }

    /**
     * @param $oscarId
     * @return null|Person
     */
    private function getPersonOscar($oscarId)
    {
        return isset($this->getPersonOscar()[$oscarId]) ? $this->getPersonOscar()[$oscarId] : null;
    }

    protected function execActivityPerson()
    {
        $valos = $this->getValosCode();

        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c, 'SELECT * FROM CONVENTION');
        oci_execute($stid);

        $processed = 0;
        $personnesManquantes = [];

        // Les valos europe
        $valosEurope = ['JAMME', 'OZOUF', 'BERNARD'];

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $bossId = $this->cleanBullshitId($row['PER_CLEUNIK']);
            $subbossId = $this->cleanBullshitId($row['PER_CLEUNIK2']);
            $contractCentaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $centaureChargeValoId = $this->cleanBullshitId($row['PER_CLEUNIK_VALO']);

            $boss = $this->getPersonCentaure($bossId);
            $subboss = $this->getPersonCentaure($subbossId);
            $valo = $this->getValo($centaureChargeValoId);

            if ($bossId && !$boss && !in_array($bossId, $personnesManquantes)) {
                $personnesManquantes[] = $bossId;
            }
            if ($subbossId && !$subboss && !in_array($subbossId,
                    $personnesManquantes)
            ) {
                $personnesManquantes[] = $subbossId;
            }
            if ($centaureChargeValoId && !$valo && !in_array($centaureChargeValoId,
                    $personnesManquantes)
            ) {
                $personnesManquantes[] = $centaureChargeValoId;
            }


            // Récupération de l'activité
            /** @var Activity $activity */
            $activity = $this->getEntityManager()->getRepository(Activity::class)->findOneBy([
                'centaureId' => $contractCentaureId
            ]);

            if (!$activity) {
                $this->getLogger()->error(sprintf("Pas d'activité de recherche avec l'ID '%s'",
                    $contractCentaureId));
                continue;
            }


            /////////////////////////////////// Chargé de valo
            if ($valo && in_array($centaureChargeValoId, $valosEurope)) {
                $roleValo = ProjectMember::ROLE_MISSION_EUROPE;
            } else {
                $roleValo = ProjectMember::ROLE_VALO;
            }
            if ($valo && !$activity->hasPerson($valo, $roleValo)) {
                $addValo = new ActivityPerson();
                $this->getEntityManager()->persist($addValo);
                $this->getLogger()->debug(sprintf("%s - %s", $valo->getId(),
                    $valo));
                $addValo->setPerson($valo)
                    ->setActivity($activity)
                    ->setRole($roleValo);
                $activity->getPersons()->add($addValo);
                $this->getEntityManager()->flush($addValo);
                $this->getLogger()->info(sprintf("%s ajouté comme %s à %s",
                    $valo, $roleValo, $activity));
            }


            /////////////////////////////////// Responsable
            if ($boss && !$activity->hasPerson($boss,
                    ProjectMember::ROLE_RESPONSABLE)
            ) {
                $add = new ActivityPerson();
                $this->getEntityManager()->persist($add);
                $add->setPerson($boss)
                    ->setActivity($activity)
                    ->setRole(ProjectMember::ROLE_RESPONSABLE);

                $activity->getPersons()->add($add);
                $this->getEntityManager()->flush($add);
                $this->getLogger()->info(sprintf("%s ajouté comme %s à %s",
                    $boss, ProjectMember::ROLE_RESPONSABLE, $activity));
            }


            /////////////////////////////////// Co-Responsable
            if ($subboss && !$activity->hasPerson($subboss,
                    ProjectMember::ROLE_CORESPONSABLE)
            ) {
                $add = new ActivityPerson();
                $this->getEntityManager()->persist($add);
                $add->setPerson($subboss)
                    ->setActivity($activity)
                    ->setRole(ProjectMember::ROLE_CORESPONSABLE);

                $activity->getPersons()->add($add);
                $this->getEntityManager()->flush($add);
                $this->getLogger()->info(sprintf("%s ajouté comme %s à %s",
                    $subboss, ProjectMember::ROLE_CORESPONSABLE, $activity));
            }
        }


        if (count($personnesManquantes)) {
            echo "Personnes manquantes : " . implode(',', $personnesManquantes);
        }
    }

    protected function execActivityOrganization()
    {
        // 913CA049
        //SELECT LABO_CLEUNIK, G_N2_COD, G_N3_COD, CONV_CLEUNIK FROM CONVENTION
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c,
            'SELECT C.CODE_GEST, C.LABO_CLEUNIK, C.G_N2_COD, C.G_N3_COD, C.CONV_CLEUNIK, C.NUM_CONVENTION, P.PART_CLEUNIK, P.CODE_ROLE_PART FROM CONVENTION C LEFT JOIN PARTICIPANT P  ON P.CONV_CLEUNIK = C.CONV_CLEUNIK');
        oci_execute($stid);

        $processed = 0;

        $roles = [
            'CONS' => Organization::ROLE_CONSEILLER,
            'COORD' => Organization::ROLE_COORDINATEUR,
            '0000000000' => null,
            'ND' => null,
            'SCIENT' => Organization::ROLE_SCIENTIFIQUE,
            'LICENCIE' => Organization::ROLE_LICENCIE,
            'CLIENT' => Organization::ROLE_CLIENT,
            'SCIENT_R' => Organization::ROLE_SCIENTIFIQUE_R,
            'CO_CONT' => Organization::ROLE_CO_CONTRACTANT,
            'FINAN' => Organization::ROLE_CO_FINANCEUR,
            'NA' => null,
        ];

            $gestionnaires = [
                'INSERM' => $this->getEntityManager()->getRepository(Organization::class)->find(9740),
                'CNRS' => $this->getEntityManager()->getRepository(Organization::class)->find(9748),
                'UCBN' => $this->getEntityManager()->getRepository(Organization::class)->find(9753),
                'IFREMER' => $this->getEntityManager()->getRepository(Organization::class)->find(9750),
                'ARCHADE' => $this->getEntityManager()->getRepository(Organization::class)->find(10442),
                'ENSICAEN' => $this->getEntityManager()->getRepository(Organization::class)->find(9785),
            ];


        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {

            // Contrat/projet
            try {
                $contract = $this->getGrantByCentaureId($row['CONV_CLEUNIK']);
                if (!$contract) {
                    throw new Exception(sprintf("Aucun contrat avec la clef '%s'",
                        $row['CONV_CLEUNIK']));
                }
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                continue;
            }

            $codeGest = $this->cleanBullshitStr($row['CODE_GEST']);


            if ( isset($gestionnaires[$codeGest]) ){
                $gestionnaire = $gestionnaires[$codeGest];

                try {

                    if (!$contract->hasOrganization($gestionnaire, Organization::ROLE_COMPOSANTE_GESTION)) {
                        $partner = new ActivityOrganization();
                        $this->getEntityManager()->persist($partner);
                        $partner->setOrganization($gestionnaire)
                            ->setActivity($contract)
                            ->setRole(Organization::ROLE_COMPOSANTE_GESTION)
                            ->setMain(true);
                        $contract->getOrganizations()->add($partner);
                        $this->getEntityManager()->flush($partner);
                        $this->getLogger()->info(sprintf("%s ajouté comme %s au contrat %s",
                            $gestionnaire,
                            Organization::ROLE_COMPOSANTE_GESTION,
                            $contract));
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->warn($e->getMessage());
                }
            }



            ////////////////////////////////////////////////////////////////////
            $laboCode = $this->cleanBullshitStr($row['LABO_CLEUNIK']);
            if ($laboCode) {
                $laboCode = 'L' . substr($laboCode, 1);
                try {
                    $organization = $this->getOrganizationByCentaureId($laboCode);

                    if (!$contract->hasOrganization($organization,
                        Organization::ROLE_LABORATORY)
                    ) {
                        $partner = new ActivityOrganization();
                        $this->getEntityManager()->persist($partner);
                        $partner->setOrganization($organization)
                            ->setActivity($contract)
                            ->setRole(Organization::ROLE_LABORATORY)
                            ->setMain(true);
                        $contract->getOrganizations()->add($partner);
                        $this->getEntityManager()->flush($partner);
                        $this->getLogger()->info(sprintf("%s ajouté comme %s au contrat %s",
                            $organization,
                            Organization::ROLE_COMPOSANTE_RESPONSABLE,
                            $contract));
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->warn($e->getMessage());
                }
            }

            // Composante responsable
            $compo = $this->cleanBullshitStr($row['G_N2_COD']);
            if ($compo) {
                try {
                    //$compo = 'S'.$this->cleanBullshitStr($row['G_N2_COD']);
                    $organization = $this->getOrganizationByCode($compo);
                    if (!$contract->hasOrganization($organization,
                        Organization::ROLE_COMPOSANTE_RESPONSABLE)
                    ) {
                        $partner = new ActivityOrganization();
                        $this->getEntityManager()->persist($partner);
                        $partner->setActivity($contract)
                            ->setOrganization($organization)
                            ->setRole(Organization::ROLE_COMPOSANTE_RESPONSABLE)
                            ->setMain(true);
                        $contract->getOrganizations()->add($partner);
                        $this->getEntityManager()->flush($partner);
                        $this->getLogger()->info(sprintf("%s ajouté comme labo au contrat %s",
                            $organization, $contract));
                    }
                    //echo "$laboCode : $labo\n";
                } catch (\Exception $e) {
                    $this->getLogger()->warn($e->getMessage());
                }
            }

            $part = $this->cleanBullshitStr($row['PART_CLEUNIK']);
            if ($part) {
                $part = 'P' . substr($part, 1);
                try {
                    $organization = $this->getOrganizationByCentaureId($part);
                    $role = $roles[$this->cleanBullshitStr($row['CODE_ROLE_PART'])];

                    if (!$contract->hasOrganization($organization, $role)) {
                        $partner = new ActivityOrganization();
                        $this->getEntityManager()->persist($partner);
                        $partner->setActivity($contract)
                            ->setOrganization($organization)
                            ->setRole($role)
                            ->setMain(true);
                        $contract->getOrganizations()->add($partner);
                        $this->getEntityManager()->flush($partner);
                        $this->getLogger()->info(sprintf("%s ajouté comme %s au projet %s",
                            $organization, $role, $contract));
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->warn($e->getMessage());
                }
            }
        }
    }

    protected function execProjectPerson()
    {
        $fieldPersonCentaure = 'PER_CLEUNIK';
        $valos = $this->getValosCode();

        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c, 'SELECT * FROM CONVENTION');
        oci_execute($stid);

        $processed = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $bossId = $this->cleanBullshitStr($row['PER_CLEUNIK']);
            $contractCentaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $centaureChargeValoId = $this->cleanBullshitStr($row['PER_CLEUNIK_VALO']);

            // Récupération du projet
            /** @var Project $project */
            $project = $this->getEntityManager()->getRepository(Project::class)->findOneBy([
                'centaureId' => $contractCentaureId
            ]);

            if (!$project) {
                $grant = $this->getEntityManager()->getRepository(Activity::class)->findOneBy([
                    'centaureId' => $contractCentaureId
                ]);
                if (!$grant || !$grant->getProject()) {
                    $this->getLogger()->error(sprintf("Pas de projet et de contrat sans projet avec l'ID '%s'",
                        $contractCentaureId));
                    continue;
                } else {
                    $project = $grant->getProject();
                }
            }

            /////////////////////////////////// Chargé de valo
            if (!$centaureChargeValoId || $centaureChargeValoId == '0000000000' || !isset($valos[$centaureChargeValoId])) {
                $this->getLogger()->error("Pas de chargé de valo pour $contractCentaureId");
            } else {
                $valo = $valos[$centaureChargeValoId];
                if (!$project->hasPerson($valo, ProjectMember::ROLE_VALO)) {
                    $addValo = new ProjectMember();
                    $this->getEntityManager()->persist($addValo);
                    $addValo->setPerson($valo)
                        ->setProject($project)
                        ->setRole('Chargé de valorisation');

                    $project->addMember($addValo);
                    $this->getEntityManager()->flush($addValo);
                }
            }

            /////////////////////////////////// Responsable
            if (!$bossId || $bossId == '0000000000') {
                $this->getLogger()->error("$contractCentaureId : Pas de responsable.");
            } else {
                try {
                    $boss = $this->getEntityManager()->createQueryBuilder()->select('p')
                        ->from(Person::class, 'p')
                        ->where('p.centaureId LIKE :centaureId')
                        ->setParameter('centaureId', '%' . $bossId . '%')
                        ->getQuery()->getSingleResult();
                } catch (\Exception $e) {
                    $this->getLogger()->error(sprintf("L'id %s est présent sur plusieurs personnes !",
                        $bossId));
                    continue;
                }

                if ($boss && !$project->hasPerson($boss,
                        ProjectMember::ROLE_RESPONSABLE)
                ) {
                    $add = new ProjectMember();
                    $this->getEntityManager()->persist($add);
                    $add->setPerson($boss)
                        ->setProject($project)
                        ->setRole(ProjectMember::ROLE_RESPONSABLE);

                    $project->addMember($add);
                    $this->getEntityManager()->flush($add);
                }
            }
        }
    }

    /**
     * Synchronisation des contracts.
     */
    protected function execContract()
    {
        $this->getLogger()->notice('+++ Syncronisation des contrats');

        // Récupération des projets dans centaure
        $c = $this->getConnexion();
        $this->getLogger()->debug('Récupération des données depuis la base CENTAURE');

        $stid = oci_parse($c,
            'SELECT CODE_TV, CODE_UP, DUREE, CODE_DEVISE, MONTANT_A_JUSTIFIER, E_VC_COD, C_ST_CONV, C_ST_CONT, CODE_NATURE_CONT, NUM_AVENANT_PRECEDENT, CONV_CLEUNIK_LIEE, NUM_CONVENTION, DATE_CREE, C_D_CONV, DATE_MAJ, CONV_CLEUNIK, DATE_DEBUT, DATE_OUVERTURE, DATE_FIN, DATE_SIGNATURE, MONTANT_FACTURE_HT, MONTANT_A_JUSTIFIER, E_VC_COD, ACRONYME_CONV, LIB_CONVENTION FROM CONVENTION ORDER BY NUM_AVENANT_PRECEDENT, CONV_CLEUNIK_LIEE');
        oci_execute($stid);


        $coefUp = array(
            'S' => 7,
            'J' => 1,
            'A' => 365,
            'M' => 30,
            'H' => 0,
            '000000' => 0
        );

        $statusCor = array(
            'CAC' => Activity::STATUS_ACTIVE,
            'CCL' => Activity::STATUS_CLOSED,
            'DAB' => Activity::STATUS_ABORDED,
            'CRE' => Activity::STATUS_TERMINATED,
            'DEC' => Activity::STATUS_PROGRESS,
        );

        /** @var ProjectGrantService $activityService */
        $activityService = $this->getServiceLocator()->get('ProjectGrantService');

        $qActivityByCode = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(
                Activity::class, 'a')
            ->where('a.centaureNumConvention = :num');

        $qActivityByCentaureId = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(
                Activity::class, 'a')
            ->where('a.centaureId = :centaureId');

        $qProjectByPFI = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT p')
            ->from(
                Project::class, 'p')
            ->innerJoin('p.grants', 'a')
            ->where('a.codeEOTP = :pfi');


        // CORRESPONDANCE des DONNÉES
        $currencies = [
            'EUR' => $activityService->getCurrency(1),
            'USD' => $activityService->getCurrency(2),
        ];

        $tvas = [
            '00' => $activityService->getTVA(1),
            '01' => $activityService->getTVA(2),
            '02' => $activityService->getTVA(3),
            '03' => $activityService->getTVA(4),
            '04' => $activityService->getTVA(5),
            '05' => $activityService->getTVA(6),
        ];


        $forceUpdate = false;
        $processed = 0;

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            ++$processed;

            $centaureId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $codeEOTP = $this->cleanBullshitStr($row['E_VC_COD']);
            $libelle = $this->cleanBullshitStr($row['LIB_CONVENTION']);
            $acronym = $this->cleanBullshitStr($row['ACRONYME_CONV']);
            $code = $this->cleanBullshitStr($row['NUM_CONVENTION']);
            $montant = (float)str_replace(',', '.', $row['MONTANT_FACTURE_HT']);
            $justifyCost = (float)str_replace(',', '.',
                $row['MONTANT_A_JUSTIFIER']);
            $label = $this->cleanBullshitStr($row['LIB_CONVENTION']);
            $duration = intval($row['DUREE']) * $coefUp[$this->cleanBullshitStr($row['CODE_UP'])];
            $statusRough = $this->cleanBullshitStr($row['C_ST_CONV']);
            $currency = $currencies[$this->cleanBullshitStr($row['CODE_DEVISE'])];
            $tva = $tvas[$this->cleanBullshitStr($row['CODE_TV'])];
            $codeActivityType = $this->cleanBullshitStr($row['C_ST_CONT']);
            $nature_conv = trim($row['CODE_NATURE_CONT']);
            $centaureDisciplineId = $this->cleanBullshitStr($row['C_D_CONV']);
            $discipline = $this->getDisciplineByCode($centaureDisciplineId);
            $flushed = [];


            if (!$currency) {
                $currency = $currencies['EUR'];
            }

            if (isset($statusCor[$statusRough])) {
                $status = $statusCor[$statusRough];
            } else {
                $status = Activity::STATUS_ERROR_STATUS;
            }

            if (!$montant) {
                $montant = 0.0;
            }
            if (!$justifyCost) {
                $justifyCost = 0.0;
            }

            $grantSource = $this->getEntityManager()->getRepository(GrantSource::class)->findOneBy(['centaureId' => $nature_conv]);
            if (!$grantSource) {
                $this->getLogger()->error(sprintf('Source %s inconnue',
                    $nature_conv));
            }
            /** @var ActivityTypeService $activityTypeService */
            $activityTypeService = $this->getServiceLocator()->get('ActivityTypeService');


            /** @var ActivityType $at */
            $at = $activityTypeService->getActivityTypeByCentaureId($codeActivityType);

            $dateDebut = $this->extractDate($row['DATE_DEBUT']);
            $dateFin = $this->extractDate($row['DATE_FIN']);
            $dateSignature = $this->extractDate($row['DATE_SIGNATURE']);
            $dateCreated = $this->extractDate($row['DATE_CREE']);
            $dateUpdate = $this->extractDate($row['DATE_MAJ']);
            $numAvenantPrecedent = $this->cleanBullshitStr($row['NUM_AVENANT_PRECEDENT']);
            $idConventionLiee = $this->cleanBullshitStr($row['CONV_CLEUNIK_LIEE']);
            $sousTypeConvention = $this->cleanBullshitStr($row['C_ST_CONT']);

            $stConv = $this->getEntityManager()->getRepository('Oscar\Entity\ContractType')->findOneBy(array(
                'code' => $sousTypeConvention,
            ));

            // Test de données vermoulues
            if (!$code) {
                $this->getLogger()->warn(sprintf("Enregistrement '%s', '%s' / '%s'  ignoré : (pas de N° de convention)",
                    $centaureId, $acronym, $libelle));
                continue;
            }

            ////////////////////////////////////////////////////////////////////
            ///////////////////////////// TRAITEMENT de la CONVENTION > ACTIVITÉ

            /** @var Activity $projectGrant */
            $projectGrant = $this->getActivityBycentaureId($centaureId);

            ////////////////////////////////////////////////////////////////////
            // Mise à jour de la discipline


            $savegrant = true;

            if (!$projectGrant) {
                $projectGrant = new Activity();
                $this->getEntityManager()->persist($projectGrant);
                $this->getLogger()->info(sprintf("Création de l'activité %s",
                    $centaureId));
            } else {
                if ($projectGrant->getDateUpdated() >= $dateUpdate && $forceUpdate === false) {
                    //$savegrant = false;
                } else {
                    $this->getLogger()->info(sprintf("Mise à jour de l'activité %s",
                        $centaureId));
                }
            }


            if ($savegrant) {

                $projectGrant->setAmount($montant)
                    ->setCurrency($currency)
                    ->setLabel($label)
                    ->setJustifyCost($justifyCost)
                    ->setHasSheet(false)
                    ->setJustifyWorkingTime(0)
                    ->setDuration($duration)
                    ->setStatus($status)
                    ->setSource($grantSource)
                    ->setCodeEOTP($codeEOTP)
                    ->setCentaureId($centaureId)
                    ->setActivityType($at)
                    ->setType($stConv)
                    ->setDateSigned($dateSignature)
                    ->setDateCreated($dateCreated)
                    ->setDateUpdated($dateUpdate)
                    ->setDateEnd($dateFin)
                    ->setTva($tva)
                    ->setDateStart($dateDebut)
                    ->setCentaureNumConvention($code);
                $this->getEntityManager()->flush($projectGrant);
            }


            ////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////// AFFECTATION À UN PROJET

            /** @var Project $project */
            $project = null;

            //
            $createProjectFrom = false;
            $activitiesToProject = [];
            //   $this->getLogger()->debug(sprintf("################################################## %s = %s", $centaureId, $codeEOTP));


            // Récupération du projet depuis les activité ayant le même PFI
            if ($codeEOTP) {
                // $this->getLogger()->debug(sprintf("# Récupération du projet à partir de l'EOTP %s", $codeEOTP));
                // Récupération du projet correspondant à partir du code EOTP
                $projects = $qProjectByPFI->setParameters(['pfi' => $codeEOTP])->getQuery()->getResult();
                if (count($projects) === 1) {
                    $project = $projects[0];
//                    $this->getLogger()->debug(sprintf(" - Projet trouvé %s", $project));
                } elseif (count($projects) > 1) {
                    $this->getLogger()->error(sprintf(" ! Plusieurs projets partage un même numéro PFI %s",
                        $codeEOTP));
                    foreach ($projects as $p) {
                        $this->getLogger()->error(sprintf(" - %s : %s",
                            $p->getId(), $p));
                    }
                    continue;
                } else {
                    $this->getLogger()->warn(sprintf(" ! Pas de projet trouvé avec %s",
                        $codeEOTP));
                    $createProjectFrom = $projectGrant;
                    $activitiesToProject[] = $projectGrant;
                }
                //    $this->getLogger()->debug("-----");
            }

            if ($idConventionLiee == "0000000000") {
                $idConventionLiee = null;
            }

            // Récupération à partir de la convention liée
            if ($idConventionLiee) {
                //    $this->getLogger()->debug(sprintf("# Récupération du projet à partir de la convention liée %s", $idConventionLiee));
                // Récupération du projet depuis la convention de référence
                $activityLiees = $qActivityByCentaureId->setParameter('centaureId',
                    $idConventionLiee)->getQuery()->getResult();
                if (count($activityLiees) == 1) {
                    $activityLiee = $activityLiees[0];
                    //       $this->getLogger()->debug(sprintf(" - Activité lié trouvée : %s", $activityLiee->getId()));

                    // Si on a pas obtenu de projet via le PFI
                    if ($project == null) {
                        if ($activityLiee->getProject()) {
                            //               $this->getLogger()->debug(sprintf(" - Projet obtenu depuis l'activité liée '%s'.", $activityLiee));
                            $project = $activityLiee->getProject();
                            $createProjectFrom = false;
                        }
                        // L'activité lié n'a pas de projet,
                        // On flag pour créer le projet à partir de l'activité lié
                        else {
                            //               $this->getLogger()->debug(sprintf(" - Projet créé depuis l'activité liée '%s'.", $activityLiee));
                            $createProjectFrom = $activityLiee;
                            $activitiesToProject[] = $activityLiee;
                        }
                    } // Sinon, on test un potentiel conflict
                    else {
                        if ($activityLiee->getProject()) {
                            if ($activityLiee->getProject() != $project) {
                                $this->getLogger()->error(" - Le projet obtenu via le PFI et via la convention liée diffère, utilisation du PFI !");
                            }
                        } else {
                            //            $this->getLogger()->debug(sprintf(" - L'activité lié n'a pas de projet, elle sera ajouté au projet créé.", $activityLiee));
                            $activitiesToProject[] = $activityLiee;
                        }
                    }
                } else {
                    $this->getLogger()->error(" L'activité $centaureId fait référence à une activité $idConventionLiee liée non référencée dans Oscar.");
                }
            }

            // Obtention du projet à partir du Numéro de cenvention
            if ($numAvenantPrecedent) {
                //    $this->getLogger()->debug(sprintf("# Obtention du projet à partir de l'avenant précédent .", $numAvenantPrecedent));
                $avenantPrecedents = $qActivityByCode->setParameter('num',
                    $numAvenantPrecedent)
                    ->getQuery()->getResult();
                if (count($avenantPrecedents) == 1) {
                    $avenantPrecedent = $avenantPrecedents[0];

                    // Récupération  du projet de l'avenant
                    if ($avenantPrecedent->getProject()) {
                        if ($project != $avenantPrecedent->getProject()) {
                            $this->getLogger()->err(" ! Conflit sur le projet à partir du numéro d'avenant précédent $numAvenantPrecedent");
                        }
                    } else {
                        //         $this->getLogger()->debug(" - L'avenant précédent n'a pas de projet, il sera ajouté au projet créé");
                        $createProjectFrom = $avenantPrecedent;
                        $activitiesToProject[] = $avenantPrecedent;
                    }
                } elseif (count($avenantPrecedents) === 0) {
                    $this->getLogger()->error(sprintf(" ! La convention %s a un aventant précédent (%s) non-référencé dans oscar",
                        $centaureId, $numAvenantPrecedent));

                } else {
                    $this->getLogger()->warn(sprintf(" ! La convention %s a plusieurs avenant précédent !??",
                        $centaureId));
                    continue;
                }
            }
            if ($project && $createProjectFrom) {
                $this->getLogger()->error(sprintf(" Création de projet demandé alors qu'un projet à été trouvé !"));
                $createProjectFrom = false;
            }

            if (!$project && $createProjectFrom) {

                $this->getLogger()->info(sprintf("Création d'un projet à partir de %s:%s",
                    $createProjectFrom, $createProjectFrom->getCodeEOTP()));
                $project = new Project();
                $this->getEntityManager()->persist($project);
                $project->setAcronym($acronym)
                    ->setLabel($createProjectFrom->getLabel())
                    ->setDateCreated($createProjectFrom->getDateCreated())
                    ->setCode($createProjectFrom->getCentaureNumConvention())
                    ->setCentaureId($createProjectFrom->getCentaureId());
            }

            if ($projectGrant->getProject() != $project) {
                $activitiesToProject[] = $projectGrant;
            }

            if ($project && $activitiesToProject) {
                foreach ($activitiesToProject as $a) {
                    if ($a->getProject() != $project) {
                        $this->getLogger()->info(sprintf("%s ARRAY - ajouté au projet %s",
                            $a->getId(), $project->getId()));
                        $a->setProject($project);
                    }
                }
            }

            if ($discipline && $projectGrant->getProject() && $projectGrant->getProject()->getDiscipline() != $discipline) {
                $projectGrant->getProject()->setDiscipline($discipline);
            }
            $this->getEntityManager()->flush();


        }
        $this->getLogger()->info('Terminé');
        $this->getLogger()->info($processed . ' entrée(s) traitée(s).');
        $this->getLogger()->debug('Syncronisation des contrats');
    }

    protected function execSimplify()
    {
        /** @var ProjectService $serviceProject */
        $serviceProject = $this->getServiceLocator()->get('ProjectService');

        $projects = $serviceProject->getProjects();
        /** @var Project $project */
        foreach ($projects as $project) {
            $this->getLogger()->debug(sprintf("Simplification de %s",
                $project));
            $serviceProject->simplifyMember($project->getId());
            $serviceProject->simplifyPartners($project->getId());
        }
    }

    protected function execTypeConv()
    {
        $typesCentaure = $this->getEntityManager()->getRepository(ContractType::class)->findAll();

        /** @var ActivityTypeService $activitytypeService */
        $activitytypeService = $this->getServiceLocator()->get('ActivityTypeService');


        $file = '/home/jacksay/Projets/oscar/trunk/conception/Copie de St_convention_lst -revu MC.csv';
        if( !file_exists($file) ){
            die('ERROR');
        }

        if( ($handler = fopen($file, "r")) !== FALSE ){
            while( ($data = fgetcsv($handler)) !== FALSE ){
                $class = $data[0];
                $stype = $data[1];
                $type = $data[2];
                $inOscar = $data[3];

                echo "$stype\t>>>$inOscar\n";//count($data);
            }
        }




        die("ok");
    }

    protected function execActivityType()
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

        /** @var ActivityTypeService $serviceActivityType */
        $serviceActivityType = $this->getServiceLocator()->get('ActivityTypeService');

        $root = $serviceActivityType->getActivityType(1);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $codeItem = $this->cleanBullshitStr($row['C_ST_CONT']);
            $libelle = $this->cleanBullshitStr($row['LIB_ST_CONVENTION']);
            ++$proceded;

            $this->getLogger()->debug(sprintf("Traitement de '%s':'%s'",
                $codeItem, $libelle));


            // Récupération de la catégorie
            if (trim($row['C_CL_CONTRAT']) !== $codeCategorie) {
                $codeCategorie = trim($row['C_CL_CONTRAT']);

                $categorie = $serviceActivityType->getActivityTypeByCentaureId($codeCategorie,
                    $root);

                // Si la catégorie n'existe pas on la cré
                if (!$categorie) {
                    $this->getLogger()->notice(sprintf("La catégorie '%s' va être ajoutée dans oscar",
                        $codeCategorie));
                    try {
                        $categorie = new ActivityType();
                        $this->getEntityManager()->persist($categorie);
                        $categorie->setCentaureId($codeCategorie)
                            ->setLabel($codeCategorie)
                            ->setNature(ActivityType::NATURE_RV);
                        $serviceActivityType->insertIn($categorie);

                        $this->getEntityManager()->flush($categorie);

                        $this->getEntityManager()->refresh($categorie);
                        $this->getEntityManager()->refresh($root);
                    } catch (\Exception $e) {
                        echo $e->getTraceAsString();
                        throw $e;
                    }
                }
            }

            if (trim($row['C_T_CONV']) !== $codeSousCategorie) {
                $codeSousCategorie = trim($row['C_T_CONV']);
                $sousCategorie = $serviceActivityType->getActivityTypeByCentaureId($codeSousCategorie);

                if (!$sousCategorie) {
                    $this->getLogger()->notice(sprintf("La sous-catégorie '%s' va être ajoutée dans oscar",
                        $codeSousCategorie));
                    $sousCategorie = new ActivityType();
                    $this->getEntityManager()->persist($sousCategorie);
                    $sousCategorie->setNature(ActivityType::NATURE_RV)
                        ->setCentaureId($codeSousCategorie)
                        ->setLabel($codeSousCategorie);
                    $serviceActivityType->insertIn($sousCategorie, $categorie);

                    $this->getEntityManager()->refresh($sousCategorie);
                    $this->getEntityManager()->refresh($categorie);
                    $this->getEntityManager()->refresh($root);
                }
            }


            $libelle = trim($row['LIB_ST_CONVENTION']);

            $item = $serviceActivityType->getActivityTypeByCentaureId($codeItem);
            if (!$item) {
                $this->getLogger()->notice(sprintf("Création de '%s'",
                    $codeItem));

                $item = new ActivityType();
                $this->getEntityManager()->persist($item);
                $item->setNature(ActivityType::NATURE_RV)
                    ->setCentaureId($codeItem)
                    ->setLabel($libelle);
                $serviceActivityType->insertIn($item, $sousCategorie);
                $this->getEntityManager()->refresh($item);
                $this->getEntityManager()->refresh($sousCategorie);
                $this->getEntityManager()->refresh($categorie);
                $this->getEntityManager()->refresh($root);
                //, $sousCategorie, $categorie);
            }
        }


        $this->getLogger()->notice(sprintf('%s traitement en %s secondes.',
            $proceded, (microtime(true) - $start)));
    }

    protected function execDates()
    {
        $q = 'SELECT CONV_CLEUNIK, DATE_PRIS_CHARGE_DAF, DATE_OUVERTURE, DATE_SIGNATURE, DATE_PRIS_CHARGE_DAF, DATE_DEBUT_INCUB, DATE_FIN_INCUB FROM CONVENTION';

        $c = $this->getConnexion();

        $stid = oci_parse($c, $q);

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $convId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $activity = $this->getActivityBycentaureId($convId);
            echo "$convId:$activity\n";
        }

    }

    protected function execTypes()
    {
        $this->getLogger()->notice("Synchronisation des types d'activités");

        //////////////////////:
        $filePath = realpath(__DIR__.'/../../../../../../conception'). '/correpondance-centaure-oscar-3.csv';

        $this->getLogger()->debug("Lecture du fichier '$filePath'");
        $handler = fopen($filePath, 'r');

        if( $handler === FALSE ){
            $this->getLogger()->error("Impossible d'accéder au fichier $filePath");
            die();
        }

        // Liste des types par ID
        $types = [];
        /** @var ActivityType $type */
        foreach($this->getEntityManager()->getRepository(ActivityType::class)->findAll() as $type){
            $types[$type->getId()] = $type;
        }

        /** @var Activity $activity */
        $aByCode = [];
        foreach($this->getEntityManager()->getRepository(Activity::class)->findAll() as $activity){
            $aByCode[$activity->getType()->getCode()][] = $activity;
        }

        while (($data = fgetcsv($handler, 0, "\t", '"')) !== FALSE) {
            $codeCentaure = trim($data[0]);
            $idOscar1 = trim($data[3]);
            $idOscar2 = trim($data[5]);

            $idOscar = $idOscar2 ? $idOscar2 : $idOscar1;
            $type = $idOscar ? $types[$idOscar] : null;
            echo "%%%%%%%%%%%%%%%%%%%%% $codeCentaure:\t$idOscar:$type\n";
            if( isset($aByCode[$codeCentaure]) ){
                /** @var Activity $activity */
                foreach( $aByCode[$codeCentaure] as $activity ){
                    $activity->setActivityType($type);
                }
            }
            $this->getEntityManager()->flush();
        }
        fclose($handler);
    }

    protected function execGrantSource()
    {
        $this->getLogger()->notice('+++ Syncronisation des sources de contrat (GrantSource)');
        $this->syncCachePdo();

        $stid = oci_parse($this->getConnexion(), 'SELECT * FROM NATURE_CONT');
        oci_execute($stid);

        $em = $this->getEntityManager()->getRepository(GrantSource::class);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $centaureId = trim($row['CODE_NATURE_CONT']);
            $this->getLogger()->debug(sprintf("Traitement de '%s'",
                $centaureId));
            $centaureLib = $this->cleanBullshitStr($row['LIB_NATURE_CONT']);

            $grantSource = $em->findOneBy(['centaureId' => $centaureId]);
            if (!$grantSource) {
                $this->getLogger()->info(sprintf("Création de %s",
                    $centaureId));
                $grantSource = new GrantSource();
                $this->getEntityManager()->persist($grantSource);
            }

            $grantSource->setLabel($centaureId)
                ->setCentaureId($centaureId)
                ->setDescription($centaureLib);

            $this->getEntityManager()->flush($grantSource);
        }
    }

    protected function execTest()
    {
        try {
        /** @var PersonService $personService */
        $personService = $this->getServiceLocator()->get('PersonService');
        foreach($this->getEntityManager()->getRepository(Person::class)->findAll() as $person){
            $personService->synchronize($person);
        }

        }catch(\Exception $e){
            die($e->getMessage());
        }
        die ('TEST');
    }

    private $codNatDocuConv = [
        '0000000000',
        'NA',
        'ND',
        'VERSION1',
        'VERSION2',
        'VERSION3',
        'VERSION4',
        'VERSION5'
    ];


    protected function execPersonToActivity()
    {
        /** @var Project $project */
        foreach( $this->getEntityManager()->getRepository(Project::class)->findAll() as $project ){
            if( count($project->getMembers()) > 0 ){
                echo "MOVE MEMBER : $project\n";
                /** @var ProjectMember $member */
                foreach($project->getMembers() as $member ){
                    /** @var Activity $activity */
                    foreach( $project->getActivities() as $activity ){
                        $am = new ActivityPerson();
                        $this->getEntityManager()->persist($am);
                        $am->setPerson($member->getPerson())
                            ->setActivity($activity)
                            ->setRole($member->getRole())
                            ->setDateStart($member->getDateStart())
                            ->setDateEnd($member->getDateEnd());
                    }
                    $this->getEntityManager()->remove($member);
                }
            }
            if( count($project->getPartners()) > 0 ){
                echo "MOVE PARTNER : $project\n";
                /** @var ProjectPartner $partner */
                foreach($project->getPartners() as $partner ){
                    /** @var Activity $activity */
                    foreach( $project->getActivities() as $activity ){
                        $ao = new ActivityOrganization();
                        $this->getEntityManager()->persist($ao);
                        $ao->setOrganization($partner->getOrganization())
                            ->setActivity($activity)
                            ->setRole($partner->getRole())
                            ->setDateStart($partner->getDateStart())
                            ->setDateEnd($partner->getDateEnd());
                    }
                    $this->getEntityManager()->remove($partner);
                }
            }
            $this->getEntityManager()->flush();

        }
    }

    protected function execTypeDocument()
    {
        $stid = oci_parse($this->getConnexion(),
            "SELECT * FROM NAT_DOCU_CONV");
        oci_execute($stid);

        $moment = new Moment();

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $codeCentaure = $this->cleanBullshitStr($row['COD_NAT_DOCU_CONV']);
            $label = $this->cleanBullshitStr($row['LIB_NAT_DOCU_CONV']);

            if (in_array($codeCentaure, $this->codNatDocuConv)) {
                continue;
            }

            // Récupération du type si il existe
            $type = $this->getEntityManager()->getRepository(TypeDocument::class)->findOneBy([
                'codeCentaure' => $codeCentaure
            ]);
            if (!$type) {
                $newType = new TypeDocument();
                $this->getEntityManager()->persist($newType);
                $newType->setLabel($label)
                    ->setDescription("Importé depuis centaure")
                    ->setCodeCentaure($codeCentaure);
                $this->getEntityManager()->flush($newType);
                echo "Création du type $label\n";
            }
        }
    }

    /**
     * Syncronisation des documents à partir de la base.
     * @throws \Exception
     */
    protected function execDocument()
    {
        $this->getLogger()->notice('Synchronisation des documents');

        $stid = oci_parse($this->getConnexion(),
            "SELECT dc.*, c.DATE_MAJ FROM DOCU_CONV dc LEFT JOIN CONVENTION c ON c.CONV_CLEUNIK = dc.CONV_CLEUNIK"); //" WHERE DOCU_CONV_CLEUNIK = '0000001393'");
        oci_execute($stid);

        // Pour les stats
        $proceded = 0;
        $total = 0;
        $start = microtime(true);

        $filepath = $this->getServiceLocator()->get('Config')['oscar']['paths']['document_centaure'];
        $oscarStore = $this->getServiceLocator()->get('Config')['oscar']['paths']['document_oscar'];


        $finfo = new \finfo(FILEINFO_MIME, "/usr/share/misc/magic");
        if (!$finfo) {
            throw new \Exception("Impossible d'ouvire la base de données Fileinfo");
        }

        // Utils
        $slugify = new Slugify();
        $moment = new Moment();
        $typesCheck = [];

        // Requêtes
        $valos = $this->getValosCode();

        // Requête pour obtenir le document à partir
        // de l'identifiant Centaure
        $qbDocument = $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from(ContractDocument::class, 'd')
            ->where('d.centaureId = :cid');

        $types = $this->getEntityManager()->getRepository(TypeDocument::class)->findAll();
        $typesDocuments = [];
        /** @var TypeDocument $type */
        foreach ($types as $type) {
            $typesDocuments[$type->getCodeCentaure()] = $type;
        }

        $typeVersion = $this->getEntityManager()->getRepository(TypeDocument::class)->find(9);

        $qbContract = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Activity::class, 'c')
            ->where('c.centaureId = :cid');

        $getMime = new MimeProvider();

        $mimeControl = new PhpFileExtension();
        (new ImageDictonary())->loadExtensions($mimeControl);
        (new OfficeDocumentDictonary())->loadExtensions($mimeControl);
        (new DocumentDictionary())->loadExtensions($mimeControl);
        (new ArchiveDictonary())->loadExtensions($mimeControl);

        $mimeControl->addExtension('application/vnd.ms-office', 'ods')
            ->addExtension('message/rfc822', 'eml')
            ->addExtension('type text/rtf', 'rtf')
            ->addExtension('text/plain', 'txt');


        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $total++;


            // Données brutes
            $centaureConventionId = $this->cleanBullshitStr($row['CONV_CLEUNIK']);
            $cod = $this->cleanBullshitStr($row['COD_NAT_DOCU_CONV']);
            $centaureId = $this->cleanBullshitStr($row['DOCU_CONV_CLEUNIK']);
            $fichier = $this->cleanFileName($row['NOM_FICHIER']);
            $blob = $row['DOCU_CONTRAT'];
            $dateUpload = $row['DATE_EMISSION'] == ' ' ? $this->extractDate($row['DATE_EMISSION']) : $this->extractDate($row['DATE_MAJ']);

            // Traitement : Personne qui a déposé le fichier
            $perValo = $this->cleanBullshitStr($row['PER_CLEUNIK_VALO']);
            $person = isset($valos[$perValo]) ? $valos[$perValo] : null;

            /** @var Activity $grant */
            $grant = null;

            // Emplacement de fichier issue de Centaure
            $path = null;

            $version = 1;

            // Message
            $information = "Fichier importé depuis centaure le " . $moment->format() . '. ';


            // TYPE
            $type = isset($typesDocuments[$cod]) ? $typesDocuments[$cod] : null;
            // cas particulier
            if (preg_match_all("/VERSION(.*)/i", $cod, $matches)) {
                $type = $typeVersion;
                $version = intval($matches[1][0]);
            }

            // CONTRAT
            try {
                $grant = $qbContract->setParameter('cid',
                    $centaureConventionId)->getQuery()->getSingleResult();
            } catch (\Exception $ex) {
                $this->getLogger()->warn(sprintf('Pas de contract %s',
                    $centaureConventionId));
            }

            ////////////////////////////////////////////////////////////////////
            // Traitement du fichier :
            // Dans centaure, le fichier semble stoqué sous 2 formes :
            // - Physique dans le champ NOM_FICHIER qui contient un chemin complet
            // - Champ DOCU_CONTRAT qui est de type Blob
            // 3 Cas possibles :
            // - Fichier absent, Blob renseigné
            // - Fichier présent, Blob vide
            // - Fichier et Blob présent sont identiques (même poid)
            // - Fichier et Blob présent sont différents (poids)

            // Base de nom pour le future fichier.
            $fileNameTemplate = 'oscar-%s-%s-%s'; // GrantID, Version, FileName slugué
            $fileNameGrantID = $grant ? $grant->getId() : '0';
            $fileInformation = "Importé depuis centaure.";

            $fileTmpFile = "centaure-doc-" . $centaureId;

            $fileSize = 0;
            $fileMime = '';


            // Création du fichier temporaire
            if (!file_exists('/tmp/' . $fileTmpFile)) {
                if ($blob) {
                    if (!file_exists('/tmp/' . $fileTmpFile)) {
                        $this->getLogger()->info("Create temporary file from Blob");
                        $blob->export('/tmp/' . $fileTmpFile);
                    }
                } else {
                    // Traitement à partir du fichier
                    if (file_exists($filepath . $fichier)) {
                        if (!copy($filepath . $fichier,
                            '/tmp/' . $fileTmpFile)
                        ) {
                            $this->getLogger()->error("Impossible de copier $filepath/$fichier dans /tmp");
                            continue;
                        }
                    } else {
                        $this->getLogger()->error("Pas de blob, pas de fichier physique pour le document $centaureId !");
                        continue;
                    }
                }
            }

            // Type de fichier

            $fileMime = $getMime->getMimeType("/tmp/$fileTmpFile");

            try {
                $ext = $mimeControl->getExtension($fileMime);
            } catch (NotFoundExtension $ex) {
                $this->getLogger()->warn(sprintf("Unsupported type %s with $centaureId",
                    $fileMime));
                continue;
            }


            // Nettoyage du nom de fichier
            if ($fichier == ' ') {
                $fichier = "FichierSansNom-$centaureId.$ext";
                echo "$fichier\n";
            }

            // Nom du fichier stoqué
            $fileNameSlug = $slugify->slugify($fichier);
            $fileName = sprintf($fileNameTemplate, $fileNameGrantID,
                $version, $fileNameSlug);
            // Copie du fichier temporaire
            if (!file_exists($oscarStore . $fileName)) {
                if (!copy('/tmp/' . $fileTmpFile, $oscarStore . $fileName)) {
                    $this->getLogger()->error("Impossible de copier $fileName");
                    continue;
                }
            }

            $fileSize = filesize($oscarStore . $fileName);

            $this->createDocument($person, $fileInformation,
                $dateUpload, $centaureId, $grant, $fileName, $fichier,
                $fileSize, $fileMime, $version, $type);

        }
        echo "\n---\n Fichiers manquants : $proceded/$total";
    }

    private function createDocument(
        $person,
        $informationCurrent,
        $dateUpload,
        $centaureId,
        $grant,
        $fileName,
        $userFileName,
        $fileSize,
        $fileMime,
        $fileVersion,
        $type
    ) {
        $document = $this->getEntityManager()->getRepository(ContractDocument::class)->findOneBy(['centaureId' => $centaureId]);
        if (!$document) {
            $document = new ContractDocument();
            $this->getEntityManager()->persist($document);
        }
        $document->setPerson($person)
            ->setInformation($informationCurrent)
            ->setDateUpdoad($dateUpload)
            ->setCentaureId($centaureId)
            ->setFileTypeMime($fileMime)
            ->setFileName($userFileName)
            ->setFileSize($fileSize)
            ->setVersion($fileVersion)
            ->setGrant($grant)
            ->setTypeDocument($type)
            ->setPath($fileName);
        $this->getEntityManager()->flush($document);
    }

    protected function getValosCode()
    {
        static $valos;
        if ($valos === null) {
            $valos = [];
            $this->getLogger()->notice('Récupération des valos à partir des services...');

            $persons = $this->getEntityManager()->createQueryBuilder()
                ->select('p')
                ->from(Person::class, 'p')
                ->where("p.ldapAffectation IN('SAIC','DSI','Dir Recherche Innov.','NORMANDIE INCUBATION')");

            /** @var Person $person */
            foreach ($persons->getQuery()->getResult() as $person) {
                $loginCentaure = strtoupper($person->getLastname());
                $loginCentaure2 = strtoupper($person->getLadapLogin());
                $valos[$loginCentaure] = $person;
                $valos[$loginCentaure2] = $person;
            }
        }

        return $valos;
    }

    protected function getValo($login)
    {
        return isset($this->getValosCode()[$login]) ? $this->getValosCode()[$login] : null;
    }

    /**
     * @return array
     */
    protected function getDisciplines()
    {
        static $cacheDisciplines;
        if ($cacheDisciplines === null) {
            $cacheDisciplines = [
                'byId' => [],
                'byCode' => [],
            ];
            /** @var Discipline $discipline */
            foreach ($this->getEntityManager()->getRepository(Discipline::class)->findAll() as $discipline) {
                $cacheDisciplines['byId'][$discipline->getId()] = $discipline;
                $cacheDisciplines['byCode'][$discipline->getCentaureId()] = $discipline;
            }
        }

        return $cacheDisciplines;
    }

    /**
     * @return Discipline|null
     */
    protected function getDisciplineByCode($code)
    {
        return (
        isset($this->getDisciplines()['byCode'][$code]) ?
            $this->getDisciplines()['byCode'][$code] :
            null
        );
    }

    protected function getActivities()
    {
        static $cacheActivities;
        if ($cacheActivities === null) {
            $cacheActivities = [
                'byId' => [],
                'byCentaureId' => [],
            ];
            /** @var Activity $activity */
            foreach ($this->getEntityManager()->getRepository(Activity::class)->findAll() as $activity) {
                $cacheActivities['byId'][$activity->getId()] = $activity;
                $cacheActivities['byCentaureId'][$activity->getCentaureId()] = $activity;
            }
        }

        return $cacheActivities;
    }

    /**
     * @param $id
     * @return null|Activity
     */
    protected function getActivityById($id)
    {
        return (
        isset($this->getActivities()['byId'][$id]) ?
            $this->getActivities()['byId'][$id] :
            null
        );
    }

    /**
     * @param $id
     * @return null|Activity
     */
    protected function getActivityBycentaureId($id)
    {
        return (
        isset($this->getActivities()['byCentaureId'][$id]) ?
            $this->getActivities()['byCentaureId'][$id] :
            null
        );
    }


    ////////////////////////////////////////////////////////////////////////////

    /** @var  Logger */
    private $logger;

    /** @var  StdoutHandler */
    private $handler;

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new LoggerStdoutColor('main');
            $this->handler = new StdoutHandler();
            $this->logger->pushHandler($this->handler);
        }

        return $this->logger;
    }
}
