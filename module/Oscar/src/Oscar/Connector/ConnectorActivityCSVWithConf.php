<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 13:55
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Exception\OscarException;

class ConnectorActivityCSVWithConf implements ConnectorInterface
{
    private $csvDatas;
    private $config;
    private $entityManager;


    public function __construct( $csvDatas, array $config, EntityManager $entityManager )
    {
        $this->csvDatas = $csvDatas;
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    protected function checkData( $data ){
        return true;
    }

    /**
     * @param $uid
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getActivity( $uid ){
        /** @var Query $queryActivity */
        static $queryActivity;
        if( $queryActivity === null ){
            $queryActivity = $this->entityManager->getRepository(Activity::class)
                ->createQueryBuilder('a')
                ->where('a.centaureId = :uid')
                ->getQuery();
        }
        return $queryActivity->setParameter('uid', $uid)->getSingleResult();
    }

    /**
     * @param $roleId
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getType( $typeLabel ){
        /** @var Query $queryOrganization */
        static $queryType;
        if( $queryType === null ){
            $queryType = $this->entityManager->getRepository(ActivityType::class)
                ->createQueryBuilder('t')
                ->where('t.label = :label')
                ->getQuery();
        }
        try {
            return $queryType->setParameter('label', $typeLabel)->getSingleResult();
        }catch( \Exception $e ){
            return null;
        }
    }

    protected function getHandlerByKey( $key ){
        $split = explode('.', $key);
        switch( $split[0] ){
            case "project":
                return $this->getHandlerProject();
            case "persons":
                return $this->getHandlerPerson($split[1]);
            case "organizations":
                return $this->getHandlerOrganization($split[1]);
            case "payments":
                return function(){};
            case "milestones":
                return function(){};

            default:
                throw new OscarException(sprintf("Les traitements de type %s ne sont pas pris en charge", $split[0]));
        }
    }

    protected function getHandlerProject(){
        $entityManager = $this->entityManager;
        return function(&$activity, $datas, $index) use ($entityManager){
            $projectRepository = $entityManager->getRepository(Project::class);
            $projectRepository = $entityManager->getRepository(Project::class);
            $activity->setProject($projectRepository->getProjectByLabelOrCreate($datas[$index]));
            return $activity;
        };
    }

    protected function getHandlerOrganization( $role ){
        $entitymanager = $this->entityManager;
        /** @var Activity $activity */
        return function( &$activity, $datas, $index) use ($entitymanager, $role) {
            /** @var OrganizationRepository $organizationRepository */
            $organizationRepository = $entitymanager->getRepository(Organization::class);

            /** @var OrganizationRoleRepository $organizationRoleRepository */
            $organizationRoleRepository = $entitymanager->getRepository(OrganizationRole::class);

            $organizationName = $datas[$index];

            $roleObj = $organizationRoleRepository->getRoleByRoleIdOrCreate($role);

            $organization = $organizationRepository->getOrganisationByNameOrCreate($organizationName);
            if( !$activity->hasOrganization($organization, $role) ){
                $activityOrganization = new ActivityOrganization();
                $entitymanager->persist($activityOrganization);
                $activityOrganization->setOrganization($organization)
                    ->setActivity($activity)
                    ->setRoleObj($roleObj);
                $activity->getOrganizations()->add($activityOrganization);
            }
            return $activity;
        };
    }

    protected function getHandlerPerson( $role = "Role inconnu" ){

            $entityManager = $this->entityManager;

            /** @var Activity $activity */
            return function(&$activity, $datas, $index) use ($entityManager, $role){
                $displayName = $datas[$index];
                try {
                    /** @var PersonRepository $personRepo */
                    $personRepo = $entityManager->getRepository(Person::class);
                    $person = $personRepo->getPersonByDisplayNameOrCreate($datas[$index]);
                } catch (NonUniqueResultException $e ){
                    throw new OscarException(sprintf("Impossible d'ajouter la personne à l'activité car oscar a trouvé plusieurs correspondance pour '%s'."), $displayName);
                }

                $personActivity = new ActivityPerson();
                /** @var RoleRepository $roleRepository */
                $roleRepository = $entityManager->getRepository(Role::class);
                $entityManager->persist($personActivity);
                $personActivity->setPerson($person)->setActivity($activity)->setRoleObj($roleRepository->getRoleOrCreate($role));
                $activity->addActivityPerson($personActivity);
                return $activity;
            };

    }

    protected function getHandler( $index ){
        // Si la clef n'existe pas dans la conf on ne fait rien
        if( !array_key_exists($index, $this->config) )
            return;

        // Si la clef est une chaîne, on détermine si c'est un appel de setter
        // simple ou un mécanisme plus "avancé"
        $key = $this->config[$index];

        // Chaîne
        if( is_string($key) ){

            // Chaîne : setter avancé
            if( stripos($key, '.') > 0 ){
                return $this->getHandlerByKey( $key );
            }

            // Chaîne : setter simple
            else {
                return function( &$activity, $data, $index ) use ($key){
                    $setter = 'set'.ucfirst($key);
                    $activity->$setter($data[$index]);
                    return $activity;
                };
            }
        }

        // C'est une Closure
        elseif (is_callable($key)){
            return $key;
        }

        // Autre ...
        else {
            return null;
        }
    }

    /**
     * @return ConnectorRepport
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function syncAll()
    {
        $repport = new ConnectorRepport();

        // Devise par défaut
        $defaultCurrency = $this->entityManager->getRepository(Currency::class)->find(1);

        while($datas = fgetcsv($this->csvDatas)){
            $activity = new Activity();
            $this->entityManager->persist($activity);
            foreach ($datas as $index => $value ){
                echo "- " . $value . "\n";
                //if( $value == 0 ) continue;
                $handler = $this->getHandler($index);
                if( $handler != null )
                    $handler($activity, $datas, $index);
            }

            var_dump($activity->toArray(true));
            die();
        }

        /*
        foreach ($this->csvDatas as $data) {
            $this->checkData($data);

            // Récupération du projet
            $project = null;

            // Type d'activité
            $type = null;

            // Récupération du projet à partir de acronym ET projectlabel
            try {
                $project = $this->entityManager->getRepository(Project::class)->createQueryBuilder('p')
                    ->where('p.acronym = :projectacronym AND p.label = :projectlabel')
                    ->getQuery()
                    ->setParameters([
                        'projectacronym' => $data->acronym,
                        'projectlabel' => $data->projectlabel,
                    ])->getSingleResult();
            } catch( NoResultException $e ){
                try {
                    // Création du projet
                    $project = new Project();
                    $this->entityManager->persist($project);
                    $project->setAcronym($data->acronym)
                        ->setLabel($data->projectlabel);
                    $this->entityManager->flush($project);
                    $repport->addadded(sprintf("Projet créé : %s", $project));
                } catch( \Exception $e ){
                    $repport->adderror("Impossible de créer le projet " . $data->projectlabel . ": " . $e->getMessage());
                }
            }
            // todo Traiter les erreurs liées à la récupération du projet

            /** @var Activity $activity /
            try {
                $activity = $this->getActivity($data->uid);
            } catch (NoResultException $e) {
                try {
                    $activity = new Activity();
                    $this->entityManager->persist($activity);
                    $activity->setCentaureId($data->uid)
                        ->setProject($project)
                    ;

                    $this->entityManager->flush($activity);

                    $message = sprintf("Création de l'activité %s/%s", $activity->getProject(), $activity->getLabel());
                    $repport->addadded($message);
                }
                catch( \Exception $e ){
                    $repport->adderror("Impossible de créer l'activité " . $data->uid . ": " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    continue;
                }
            }
            // todo Traiter les erreurs liées à la récupération de l'activité

            $activity->setLabel($data->label)
                ->setCurrency($defaultCurrency)
                ->setDateStart($data->datestart ? new \DateTime($data->datestart) : null)
                ->setDateEnd($data->dateend ? new \DateTime($data->dateend) : null)
                ->setCodeEOTP($data->pfi)
                ->setActivityType($type)
                ->setDateSigned($data->datesigned ? new \DateTime($data->datesigned) : null)
                ->setAmount(((double)$data->amount));

            $this->entityManager->flush($activity);

            //// TRAITEMENT des ORGANISATIONS
            foreach( $data->organizations as $role=>$organizations ){
                try {
                    $roleObj = $this->entityManager->getRepository(OrganizationRole::class)->findOneBy(['label' => $role]);
                    foreach( $organizations as $fullName ){
                        try {
                            $organization = $this->getOrganization($fullName);
                            if( !$activity->hasOrganization($organization, $role) ){
                                $activityOrganization = new ActivityOrganization();
                                $this->entityManager->persist($activityOrganization);
                                $activityOrganization->setOrganization($organization)
                                    ->setActivity($activity)
                                    ->setRoleObj($roleObj);
                                $this->entityManager->flush($activityOrganization);
                                $repport->addadded(sprintf("L'oganisation %s a été ajoutée dans %s avec le rôle %s.", $fullName, $activity, $role));
                            }
                        } catch( \Exception $e ){
                            $repport->adderror(sprintf("Impossible d'affecter %s comme %s dans %s : %s.", $fullName, $role, $activity, $e->getMessage()));
                        }
                    }
                } catch( \Exception $e ){
                    $repport->adderror(sprintf("Le rôle d'organisation %s n'existe pas dans oscar.", $role));
                }
            }

            //// TRAITEMENT des PERSONNES
            foreach( $data->persons as $role=>$persons ){
                try {
                    $roleObj = $this->getRoleObj($role);
                    foreach( $persons as $fullName ){
                        // Récupération du rôle
                        try {
                            $person = $this->getPerson($fullName);
                            if( !$activity->hasPerson($person, $role) ){
                                try {
                                    $personActivity = new ActivityPerson();
                                    $this->entityManager->persist($personActivity);
                                    $personActivity->setPerson($person)
                                        ->setActivity($activity)
                                        ->setRoleObj($roleObj);
                                    $this->entityManager->flush($personActivity);
                                    $repport->addadded(sprintf("%s a été ajoutée dans %s avec le rôle %s.", $fullName, $activity, $role));

                                } catch( \Exception $e ){
                                    $repport->addadded(sprintf("Impossible d'ajouter %s dans %s avec le rôle %s : %s.", $fullName, $activity, $role, $e->getMessage()));
                                }
                            }
                        } catch( \Exception $e ){
                            $repport->adderror(sprintf("%s n'a pas été ajoutée dans %s avec le rôle %s : %s.", $fullName, $activity, $role, $e->getMessage()));
                        }

                    }
                } catch( \Exception $e ){
                    $repport->addwarning(sprintf("Le rôle %s n'existe pas dans Oscar : %s", $role, $e->getMessage()));
                }
            }
        }*/
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}