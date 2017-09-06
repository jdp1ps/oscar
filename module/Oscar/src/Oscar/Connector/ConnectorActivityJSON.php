<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 13:55
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Project;
use Oscar\Entity\Role;

class ConnectorActivityJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $entityManager;


    public function __construct( array $jsonData, EntityManager $entityManager )
    {
        $this->jsonDatas = $jsonData;
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
     * @param $displayName
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getPerson( $displayName ){
        /** @var Query $queryPerson */
        static $queryPerson;
        if( $queryPerson === null ){
            $queryPerson = $this->entityManager->getRepository(Person::class)
                ->createQueryBuilder('p')
                ->where('CONCAT(p.firstname, \' \', p.lastname) = :displayName')
                ->getQuery();
        }
        return $queryPerson->setParameter('displayName', $displayName)->getSingleResult();
    }

    /**
     * @param $fullName
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getOrganization( $fullName ){
        /** @var Query $queryOrganization */
        static $queryOrganization;
        if( $queryOrganization === null ){
            $queryOrganization = $this->entityManager->getRepository(Organization::class)
                ->createQueryBuilder('o')
                ->where('o.fullName = :fullName')
                ->getQuery();
        }
        return $queryOrganization->setParameter('fullName', $fullName)->getSingleResult();
    }

    /**
     * @param $roleId
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getRoleObj( $roleId ){
        /** @var Query $queryOrganization */
        static $queryRole;
        if( $queryRole === null ){
            $queryRole = $this->entityManager->getRepository(Role::class)
                ->createQueryBuilder('r')
                ->where('r.roleId = :roleId')
                ->getQuery();
        }
        return $queryRole->setParameter('roleId', $roleId)->getSingleResult();
    }

    /**
     * @return ConnectorRepport
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function syncAll()
    {
        $repport = new ConnectorRepport();
        foreach ($this->jsonDatas as $data) {
            $this->checkData($data);

            // Récupération du projet
            $project = null;
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

            /** @var Activity $activity */
            try {
                $activity = $this->getActivity($data->uid);
            } catch (NoResultException $e) {
                try {
                    $activity = new Activity();
                    $this->entityManager->persist($activity);
                    $activity->setCentaureId($data->uid)
                        ->setProject($project)
                        ->setLabel($data->label)
                        ->setDateStart($data->datestart ? new \DateTime($data->datestart) : null)
                        ->setDateEnd($data->dateend ? new \DateTime($data->dateend) : null)
                        ->setCodeEOTP($data->pfi)
                        ->setDateSigned($data->datesigned ? new \DateTime($data->datesigned) : null)
                        ->setAmount(((double)$data->amount))

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
                        /****/
                    }
                } catch( \Exception $e ){
                    $repport->addwarning(sprintf("Le rôle %s n'existe pas dans Oscar : %s", $role, $e->getMessage()));
                }
            }
        }
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}