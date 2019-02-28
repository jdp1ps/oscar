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
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityType;
use Oscar\Entity\Currency;
use Oscar\Entity\DateType;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
use Oscar\Entity\TVA;
use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;
use Oscar\Import\Data\DataExtractorFullname;

class ConnectorActivityJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $entityManager;
    private $options;

    private $createdLog;


    public function __construct( array $jsonData, EntityManager $entityManager, $options = null )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
        $this->createdLog = [];

        if( $options == null ){
            $this->options = [
                "create-missing-organization"       => false,
                "create-missing-organization-role"  => false,
                "create-missing-person"             => false,
                "create-missing-person-role"        => false,
                "create-missing-project"            => false,
                "create-missing-activity-type"      => false,
            ];
        } else {
            $this->options = $options;
        }
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

    }

    protected function getOrganizationOrCreate( $fullname, ConnectorRepport $repport ){

        try {
            return $this->entityManager->getRepository(Organization::class)
                ->createQueryBuilder('o')
//                ->where('o.fullName = :fullname OR o.shortName = :fullname')
                ->where('o.fullName = :fullname')
                ->getQuery()
                ->setParameter('fullname', $fullname)
                ->getSingleResult();
        } catch (NoResultException $e) {
            try {
                $organization = new Organization();
                $this->entityManager->persist($organization);
                $organization->setFullName($fullname);
                $this->entityManager->flush($organization);
                $repport->addadded(sprintf("ORGANIZA '%s'", $organization));
                return $organization;
            } catch (\Exception $e) {
                $error = sprintf("Impossible de créer l'organisation '%s'", $fullname);
            }
        } catch (NonUniqueResultException $e ){
            $error = sprintf("ATTENTION, l'organisation '%s' est présente dans la base en plusieurs exemplaire", $fullname);
        }
        //$this->createdLog[] = sprintf('!ORG %s', $organization);
        throw new ConnectorException($error);
    }

    /**
     * @param $role
     * @return Role
     * @throws ConnectorException
     */
    protected function getRolePersonOrCreate( $role, ConnectorRepport $repport ){
        try {
            return $this->entityManager->getRepository(Role::class)
                ->createQueryBuilder('r')
                ->where('r.roleId = :roleId')
                ->getQuery()
                ->setParameter('roleId', $role)
                ->getSingleResult();

        } catch ( NoResultException $e ) {
            try {
                $roleObj = new Role();
                $this->entityManager->persist($roleObj);
                $roleObj->setRoleId($role);
                $this->entityManager->flush($roleObj);
                $repport->addadded(sprintf("ROLEPERS '%s'", $roleObj));
                return $roleObj;
            } catch (\Exception $e ){
               $error = sprintf("Impossible de créer le rôle (Role) '%s' : %s", $role, $e->getMessage());
            }
        } catch (NonUniqueResultException $e){
            $error = sprintf("ATTENTION ! Le rôle (Role) '%s' est présent plusieurs fois dans la base de données", $role);
        }
        //$this->createdLog[] = sprintf('!ROLEPERS %s : %s', $roleObj, $error);
        throw new ConnectorException($error);
    }

    /**
     * @param $role
     * @return OrganizationRole
     * @throws ConnectorException
     */
    protected function getRoleOrganizationOrCreate( $role, ConnectorRepport $repport ){
        try {
            $roleObj =  $project = $this->entityManager->getRepository(OrganizationRole::class)->createQueryBuilder('r')
                ->where('r.label = :label')
                ->getQuery()
                ->setParameters([
                    'label' => $role
                ])->getSingleResult();
            return $roleObj;
        } catch ( NoResultException $e ) {
            try {
                $roleObj = new OrganizationRole();
                $this->entityManager->persist($roleObj);
                $roleObj->setLabel($role);
                $this->entityManager->flush($roleObj);
                $repport->addadded(sprintf("ROLEORGA '%s'", $roleObj));
                return $roleObj;
            } catch (\Exception $e ){
                throw new ConnectorException(sprintf("Impossible de créer le rôle '%s' : %s", $role, $e->getMessage()));
            }
        } catch (NonUniqueResultException $e){
            throw new ConnectorException(sprintf("ATTENTION ! Le rôle '%s' est présent plusieurs fois dans la base de données", $role));
        }
    }

    protected function getPersonOrCreate( $personDatas, ConnectorRepport $repport ){

        $fullname = $personDatas['firstname']. ' ' . $personDatas['lastname'] . ($personDatas['email'] ? '<'.$personDatas['email'].'>' : '');
        try {
            $query = $this->entityManager->getRepository(Person::class)->createQueryBuilder('p')
                ->where('CONCAT(p.firstname, \' \', p.lastname) = :fullname')
                ->setParameter('fullname', $personDatas['fullname']);

            $person = $query->getQuery()->getSingleResult();
            return $person;
        } catch ( NoResultException $e ) {
            try {
                $person = new Person();
                $this->entityManager->persist($person);
                $person->setFirstname($personDatas['firstname'])
                    ->setLastname($personDatas['lastname'])
                    ->setEmail($personDatas['email']);
                $this->entityManager->flush($person);

                $repport->addadded(sprintf("PERSONNE '%s' (depuis : %s)", $person, $personDatas['fullname']));
                return $person;

            } catch (\Exception $e ){
                throw new ConnectorException(sprintf("Impossible de créer la personne '%s' : %s", $fullname, $e->getMessage()));
            }
        } catch (NonUniqueResultException $e){
            throw new ConnectorException(sprintf("ATTENTION ! La personne '%s' est présente plusieurs fois dans la base de données", $fullname));
        }
    }

    /**
     * @param $acronym
     * @param $label
     * @param bool $doNotCreate
     * @return Project
     */
    protected function getProjectOrCreate( $acronym, $label="", ConnectorRepport $repport, $doNotCreate = false ){
        try {
            // Obtention du projet si il existe
            $project = $this->entityManager->getRepository(Project::class)->createQueryBuilder('p')
                ->where('p.acronym = :projectacronym AND p.label = :projectlabel')
                ->getQuery()
                ->setParameters([
                    'projectacronym' => $acronym,
                    'projectlabel' => $label,
                ])->getSingleResult();

            return $project;
        } catch ( NoResultException $e ){

            try {
                // Création du projet
                $project = new Project();
                $this->entityManager->persist($project);
                $project->setAcronym($acronym)
                    ->setLabel($label);
                $this->entityManager->flush($project);

                $repport->addadded(sprintf("Le projet '%s' a été créé", $project));
                return $project;
            } catch (\Exception $e ){
                $repport->adderror(sprintf("Impossible de créé le projet '[%s] %s' : %s", $acronym, $label, $e->getMessage()));
            }

        } catch ( NonUniqueResultException $e ){
            $repport->addwarning(sprintf("Le projet '[%s] %s' n'est pas unique...", $acronym, $label));
        }
        catch (\Exception $e) {
            $repport->adderror(sprintf("Impossible de trouver/créer le projet '[%s] %s' : %s", $acronym, $label, $e->getMessage()));
        }
        return null;
    }

    /**
     * @param $label
     * @return DateType
     * @throws ConnectorException
     */
    protected function getMilestoneTypeOrCreate( $label, ConnectorRepport $repport ){
        try {
            // Obtention du projet si il existe
            return $this->entityManager->getRepository(DateType::class)->createQueryBuilder('d')
                ->where('d.label = :label')
                ->getQuery()
                ->setParameters([
                    'label' => $label,
                ])->getSingleResult();

        } catch ( NoResultException $e ){
            try {
                // Création du projet
                $milestoneType = new DateType();
                $this->entityManager->persist($milestoneType);
                $milestoneType->setLabel($label);
                $this->entityManager->flush($milestoneType);

                $repport->addadded(sprintf("JALOTYPE '%s'", $milestoneType));

                return $milestoneType;
            } catch (\Exception $e ){
                throw new ConnectorException(sprintf("Impossible de créé le type de jalon '%s' : %s", $label, $e->getMessage()));
            }

        } catch ( NonUniqueResultException $e ){
            throw new ConnectorException(sprintf("Le type de jalon '%s' est présent en plusieurs exemplaire dans la base ! : %s", $label, $e->getMessage()));
        }
        catch (\Exception $e) {
            throw new ConnectorException(sprintf("Erreur de la récupération du type de jalon '%s' : %s", $label, $e->getMessage()));
        }
        return null;
    }

    /**
     * @param $roleId
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getType( $typeLabel){
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


    /**
     * @return OrganizationRoleRepository
     */
    protected function getOrganizationRepository() {
        return $this->entityManager->getRepository(Organization::class);
    }

    /**
     * @param $symbolOrName
     * @return Currency
     * @throws OscarException
     */
    protected function getCurrency( $symbolOrName ){
        static $currencies;
        if( $currencies === null ){
            $currencies = $this->entityManager->getRepository(Currency::class)->findAll();
        }
        /** @var Currency $currency */
        foreach ($currencies as $currency ){
            if( $currency->getLabel() == $symbolOrName || $currency->getSymbol() == $symbolOrName ){
                return $currency;
            }
        }
        throw new OscarException("Impossible de trouver la devise '$symbolOrName'");
    }

    /**
     * @param $tauxTVA
     * @return TVA
     * @throws OscarException
     */
    protected function getTva( $tauxTVA ){
        static $tvas;
        if( $tvas === null ){
            $tvas = $this->entityManager->getRepository(TVA::class)->findAll();
        }
        /** @var TVA $tva */
        foreach ($tvas as $tva ){
            if( $tva->getRate() == $tauxTVA ){
                return $tva;
            }
        }
        throw new OscarException("Impossible de trouver la TVA '$tauxTVA'");
    }

    protected function getPropertyObject( $object, $property, $required = true, $type=null ){
        if( !property_exists($object, $property) ){
            if( $required === true )
                throw new ConnectorException(sprintf("La propriété '%s' attendue n'est pas disponible !", $property));

            $value = null;
        } else {
            $value = $object->$property;
        }

        if( $type == 'datetime' ){
            return new \DateTime($value);
        }

        elseif ($type == "number") {
            return doubleval($value);
        }

        return $value;
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

        foreach ($this->jsonDatas as $data) {
            $this->checkData($data);

            // Récupération du projet
            $project = null;

            // Type d'activité
            $type = null;

            // Pas d'info sur le type, on ne fait rien
            if ($data->type) {

                // On tente de récupérer le type d'activité depuis la BDD
                $type = $this->getType($data->type, $repport);

                // Invalid, on ignore
                if (!$type) {
                    $repport->addwarning(sprintf("Le type d'activité %s n'existe pas dans oscar",
                            $data->type));
                }
            }

            // -----------------------------------------------------------------
            // Projet de l'activité
            $projectAcronym = $data->acronym;

            if( $data->project || $data->projectlabel )
                $projectLabel = $data->project ?: $data->projectlabel;


            $project = $this->getProjectOrCreate( $projectAcronym, $projectLabel, $repport);

            // todo Traiter les erreurs liées à la récupération du projet

            /** @var Activity $activity */
            $activities =  $this->entityManager->getRepository(Activity::class)
                ->findBy(['centaureId' => $data->uid]);

            if( count($activities) == 0 ){
                    $activity = new Activity();
                    $this->entityManager->persist($activity);
                    $activity->setCentaureId($data->uid)
                        ->setProject($project);

                    $repport->addadded(sprintf("Création de l'activité '%s'.", $activity));

            } else {
                $activity = $activities[0];
                $repport->addupdated(sprintf("Mise à jour de l'activité '%s'.", $activity));
            }
            // todo Traiter les erreurs liées à la récupération de l'activité

            // DATES
            $dateStart = null;
            $dateEnd = null;

            if( $data->currency ){
                $currency = $this->getCurrency($data->currency);
            } else {
                $currency = $defaultCurrency;
            }

            if( $data->currency ){
                $tva = $this->getTva($data->tva);
            } else {
                $tva = null;
            }

            if( $data->status ){
                $status = (int)$data->status;
            } else {
                $status = Activity::STATUS_ERROR_STATUS;
            }



            $activity
                ->setLabel($this->getPropertyObject($data, 'label'))
                ->setDescription($this->getPropertyObject($data, 'description', false))
                ->setFinancialImpact($this->getPropertyObject($data, 'financialImpact', false))
                ->setAssietteSubventionnable($this->getPropertyObject($data, 'assietteSubventionnable', false))
                ->setCurrency($currency)
                ->setTva($tva)
                ->setCodeEOTP($data->pfi)
                ->setActivityType($type)
                ->setDateSigned($data->datesigned ? new \DateTime($data->datesigned) : null)
                ->setDateOpened($data->datePFI ? new \DateTime($data->datePFI) : null)
                ->setStatus($status)
                ->setAmount(((double)$data->amount));

            if( $data->datestart ){
                try {
                    $dateStart = new \DateTime($data->datestart);
                } catch (\Exception $e ){
                    $repport->adderror(sprintf("Impossible d'extraire une date depuis la valeur %s pour l'activité %s",
                        $data->datestart, $activity));
                }
            }

            if( $data->dateend ){
                try {
                    $dateEnd = new \DateTime($data->dateend);
                } catch (\Exception $e ){
                    $repport->adderror(sprintf("Impossible d'extraire une date depuis la valeur %s pour l'activité %s",
                        $data->dateend, $activity));
                }
            }

            $activity
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);

            $this->entityManager->flush($activity);



            //// TRAITEMENT des ORGANISATIONS
            foreach( $data->organizations as $role=>$organizations ){
                try {

                    $roleObj = $this->getRoleOrganizationOrCreate( $role, $repport );

                    foreach( $organizations as $fullName ){
                        try {
                            $organization = $this->getOrganizationOrCreate($fullName, $repport);

                            if( !$activity->hasOrganization($organization, $roleObj->getLabel()) ){
                                $activityOrganization = new ActivityOrganization();
                                $this->entityManager->persist($activityOrganization);
                                $activityOrganization->setOrganization($organization)
                                    ->setActivity($activity)
                                    ->setRoleObj($roleObj);
                                $this->entityManager->flush($activityOrganization);
                                $repport->addadded(sprintf("L'oganisation '%s' a été ajoutée dans %s avec le rôle '%s'.", $fullName, $activity, $role));
                            }
                        } catch( \Exception $e ){
                            $repport->adderror(sprintf("Impossible d'affecter %s comme %s dans %s : %s.", $fullName, $role, $activity, $e->getMessage()));
                            return $repport;
                        }
                    }
                } catch( \Exception $e ){
                    $repport->adderror($e->getMessage());
                    return $repport;
                }
            }

            //// TRAITEMENT des PERSONNES
            foreach( $data->persons as $role=>$persons ){
                try {

                    ////////////////////////////////////////////////////////////
                    $roleObj = $this->getRolePersonOrCreate( $role, $repport );


                    foreach( $persons as $fullName ){
                        $datasPerson = (new DataExtractorFullname())->extract($fullName);
                        if( $datasPerson ) {
                            $person = $this->getPersonOrCreate($datasPerson, $repport);
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
                        } else {
                            $repport->adderror(sprintf("Impossible de traiter la personne '%s'", $fullName));
                        }
                    }

                } catch( \Exception $e ){
                    $repport->addwarning(sprintf("Impossible d'ajouter la personne '%s' avec le rôle '%s' dans l'activité '%s' : %s",
                        $fullName, $role, $activity, $e->getMessage()));
                }
            }

            ///////////////////////////////////////////////////////// MILESTONES
            foreach ( $data->milestones as $milestone ){
                try {
                    $type = $this->getMilestoneTypeOrCreate($milestone->type, $repport);
                    try {
                        $date = new \DateTime($milestone->date);
                    } catch (\Exception $e) {
                        throw new \Exception(sprintf("Impossible de convertir '%s' en objet Date : %s", $milestone->date, $e->getMessage()));
                    }

                    if( !$activity->hasMilestoneAt( $type, $date ) ){
                        $milestoneActivity = new ActivityDate();
                        $this->entityManager->persist($milestoneActivity);
                        $milestoneActivity->setType($type)
                            ->setActivity($activity)
                            ->setDateStart($date);
                        $this->entityManager->flush($milestoneActivity);
                        $repport->addadded(sprintf("Jalon '%s'(date : %s) ajouté dans '%s'", $milestone->type, $milestone->date, $activity));
                    }
                } catch (\Exception $e ){
                    $repport->adderror(sprintf("Impossible d'ajouter le jalon '%s'(date : %s) dans '%s' : %s", $milestone->type, $milestone->date, $activity, $e->getMessage()));
//                        $fullName, $role, $activity, $e->getMessage()));
                }
            }
            foreach ($data->payments as $paymentData) {

                try {
                    $amount = doubleval($paymentData->amount);
                    if( !$amount ){
                        throw new \Exception(sprintf("La valeur de montant '%s' n'a pas put être convertie en nombre.",
                            $paymentData->amount));
                    }

                    try {
                        $datePayment = $paymentData->date ?
                            new \DateTime($paymentData->date) :
                            null;

                        $datePredicted = $paymentData->predicted ?
                            new \DateTime($paymentData->predicted) :
                            null;
                    } catch (\Exception $e) {
                        throw new \Exception(sprintf("Impossible de convertir '%s' en objet Date : %s",
                            $paymentData->date, $e->getMessage()));
                    }

                    if( !$datePayment && !$datePredicted ){
                        throw new \Exception("Impossible de créer un versement sans date");
                    }

                    if( $datePredicted && !$datePayment){
                        $paymentStatus = ActivityPayment::STATUS_PREVISIONNEL;
                    } else {
                        $paymentStatus = ActivityPayment::STATUS_REALISE;
                    }

                    if( !$activity->hasPaymentAt( $amount, $datePayment, $datePredicted) ){
                        $payment = new ActivityPayment();
                        $this->entityManager->persist($payment);
                        $payment->setDatePayment($datePayment)
                            ->setDatePredicted($datePredicted)
                            ->setCurrency($defaultCurrency)
                            ->setStatus($paymentStatus)
                            ->setActivity($activity)
                            ->setAmount($amount);
                        $this->entityManager->flush($payment);

                        $repport->addadded(sprintf("Ajout d'un versement de '%s' €, effectué le '%s' (Prévu le '%s) dans '%s'",
                            $amount,
                            $datePayment ? $datePayment->format('D M Y') : 'N.D',
                            $datePredicted ? $datePredicted->format('D M Y') : 'N.D',
                            $activity));
                    }

                } catch ( \Exception $e ){
                    $repport->adderror(sprintf("Impossible d'ajouter le versement de '%s'€ (le : '%s') dans '%s' : %s",
                        $paymentData->amount, $paymentData->date, $activity, $e->getMessage()));
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