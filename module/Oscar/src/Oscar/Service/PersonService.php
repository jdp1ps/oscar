<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\AuthentificationRepository;
use Oscar\Entity\Notification;
use Oscar\Entity\NotificationPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\PrivilegeRepository;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\Referent;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Strategy\Search\PersonSearchStrategy;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOrganizationService;
use Oscar\Traits\UseOrganizationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Logger;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Renderer\PhpRenderer;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class PersonService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService, UseOscarUserContextService, UseNotificationService, UseProjectGrantService, UseActivityLogService, UseServiceContainer
{
    use UseOscarConfigurationServiceTrait, UseEntityManagerTrait, UseLoggerServiceTrait, UseOscarUserContextServiceTrait, UseNotificationServiceTrait, UseProjectGrantServiceTrait, UseActivityLogServiceTrait, UseServiceContainerTrait;


    /**
     * @return PersonRepository
     */
    public function getRepository()
    {
        return $this->getEntityManager()->getRepository(Person::class);
    }

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getOscarUserContextService();
    }

    /**
     * @return Person
     */
    protected function getCurrentPerson()
    {
        return $this->getOscarUserContext()->getCurrentPerson();
    }

    /**
     * @return PersonSearchStrategy
     */
    public function getSearchEngineStrategy()
    {
        static $searchStrategy;
        if ($searchStrategy === null) {
            $opt = $this->getOscarConfigurationService()->getConfiguration('strategy.person.search_engine');
            $class = new \ReflectionClass($opt['class']);
            $searchStrategy = $class->newInstanceArgs($opt['params']);
        }
        return $searchStrategy;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SYSTÈME de RECHERCHE
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Supprimer la personne de l'index de recherche.
     * @param $id
     */
    public function searchDelete($id)
    {
        $this->getSearchEngineStrategy()->remove($id);
    }

    /**
     * Mise à jour de la personne dans l'index.
     * @param Person $person
     */
    public function searchUpdate(Person $person)
    {
        $this->getSearchEngineStrategy()->update($person);
    }

    public function jobSearchUpdate(Person $person)
    {
        $client = new \GearmanClient();
        $client->addServer($this->getOscarConfigurationService()->getGearmanHost());
        $client->doBackground('personSearchUpdate', json_encode([
            'personid' => $person->getId()
        ]),
            sprintf('personsearchupdate-%s', $person->getId())
        );
    }

    /**
     * Remise à zéro de l'index
     */
    public function searchIndex_reset()
    {
        $this->getSearchEngineStrategy()->resetIndex();
    }

    /**
     * Reconstruction de l'index de recherche
     * @return mixed
     */
    public function searchIndexRebuild()
    {
        $this->searchIndex_reset();
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        return $this->getSearchEngineStrategy()->rebuildIndex($persons);
    }

    /**
     * Recherche textuelle parmis les personnes.
     * @param $what
     * @return Person[]
     */
    public function search($what)
    {
        $ids = $this->getSearchEngineStrategy()->search($what);

        $query = $this->getRepository()->createQueryBuilder('p')
            ->where('p.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();

        return $query->getResult();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RECUPÉRATION des DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /// RECUPERATION d'OBJET (Person/Referent) /////////////////////////////////////////////////////////////////////////

    /**
     * Retourne la liste des Person via une liste d'ID.
     * @param array $ids
     * @return Person[]
     */
    public function getPersonsByIds(array $ids)
    {
        return $this->getPersonRepository()->getPersonsByIds($ids);
    }

    /**
     * Retourne l'objet Referent
     * @param $referentId
     * @param bool $throw
     * @return Referent|null
     * @throws OscarException
     */
    public function getReferentById($referentId, $throw = true)
    {
        $referent = $this->getEntityManager()->getRepository(Referent::class)->find($referentId);
        if ($throw === true && !$referent) {
            throw new OscarException("Impossible de charger le référent");
        }
        return $referent;
    }

    /// RECUPERATION de COLLECTION (Person/Referent) ///////////////////////////////////////////////////////////////////

    /**
     * Retourne les N+1 (Référents) de la personne.
     * @param Person $person
     * @return Person[]
     */
    public function getManagers(Person $person)
    {
        if (!$person) return [];

        $qb = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin(Referent::class, 'r', 'WITH', 'r.referent = p')
            ->where('r.person = :person');

        return $qb->setParameter('person', $person)->getQuery()->getResult();
    }

    /**
     * Retourne les personnes référentes de la personne.
     * @param $personId ID du subordonné
     * @return Referent[]
     */
    public function getReferentsPerson($personId)
    {
        $query = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->where('r.person = :personId')
            ->setParameter('personId', $personId);
        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les N-1 (Subordonnés) de la personne.
     * @param Person $person
     * @return Person[]
     */
    public function getSubordinates(Person $person)
    {
        if (!$person) return [];

        $qb = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin(Referent::class, 'r', 'WITH', 'r.person = p')
            ->where('r.referent = :person');

        return $qb->setParameter('person', $person)->getQuery()->getResult();
    }

    /**
     * Retourne les personnes subordonnées du référent.
     * @param $personId ID du référents
     * @return Referent[]
     */
    public function getSubordinatesPerson($personId)
    {
        $query = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->where('r.referent = :personId')
            ->setParameter('personId', $personId);
        return $query->getQuery()->getResult();
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AJOUT
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Ajoute un référent à la personne
     * @param $referent_id ID du référent
     * @param $person_id ID du subordonné
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addReferent($referent_id, $person_id)
    {
        $referent = $this->getPersonById($referent_id, true);
        $person = $this->getPersonById($person_id, true);

        // @todo Vérifier si le référent n'est pas déjà identifié
        $verif = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->where('r.referent = :referent AND r.person = :person')
            ->setParameters([
                'referent' => $referent,
                'person' => $person
            ])->getQuery()->getResult();
        if (count($verif) > 0) {
            throw new OscarException("$referent est déjà identifié comme référent pour $person");
        }


        $referentRec = new Referent();
        $this->getEntityManager()->persist($referentRec);
        $referentRec->setPerson($person)->setReferent($referent);
        $this->getEntityManager()->flush($referentRec);

        return true;
    }

    public function addReferentToDeclarerHorsLot(Person $declarer, Person $referent, $flush = false)
    {
        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceContainer()->get(TimesheetService::class);

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotToValidateByPerson($declarer, true);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $this->getLoggerService()->notice(sprintf("$referent est maintenant validateur administratif pour $validationPeriod"));
            $validationPeriod->addValidatorAdm($referent);
        }

        if ($flush == true && count($validationPeriods) > 0)
            $this->getEntityManager()->flush();
    }

    /**
     * Remplace la personne référente par une autre personne.
     * @param Person $personNewReferent
     * @param Person $fromPerson
     * @return bool
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function refererentReplaceBy(Person $personNewReferent, Person $fromPerson)
    {
        // Liste des Referent existant
        $referents = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->select('r')
            ->where('r.referent = :personReferent')
            ->setParameters(['personReferent' => $fromPerson])
            ->getQuery()
            ->getResult();

        /** @var Referent $referent */
        foreach ($referents as $referent) {
            $this->getLoggerService()->notice(sprintf("$personNewReferent a remplacé $fromPerson pour " . $referent->getPerson()));
            $referent->setReferent($personNewReferent);
        }

        /** @var TimesheetService $timesheetService */
        $timesheetService = $this->getServiceContainer()->get(TimesheetService::class);

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotByReferent($fromPerson, true);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $this->getLoggerService()->notice(sprintf("$personNewReferent est maintenant validateur administratif pour $$validationPeriod"));
            $validationPeriod->removeValidatorAdm($fromPerson);
            $validationPeriod->addValidatorAdm($personNewReferent);
        }
        $this->getEntityManager()->flush();
        return true;
    }

    public function refererentAddFromReferent(Person $personNewReferent, Person $fromPerson)
    {
        try {
            // Liste des subordonnés / création des référents
            $subordinates = $this->getSubordinates($fromPerson);

            foreach ($subordinates as $subordinate) {
                $this->getLoggerService()->notice(sprintf("$personNewReferent est maintenant référent pour $subordinate"));
                $r = new Referent();
                $this->getEntityManager()->persist($r);
                $r->setPerson($subordinate)
                    ->setReferent($personNewReferent);
            }

            // Déclarations
            /** @var TimesheetService $timesheetService */
            $timesheetService = $this->getServiceContainer()->get(TimesheetService::class);

            $validationPeriods = $timesheetService->getValidationHorsLotByReferent($fromPerson, true);
            /** @var ValidationPeriod $validationPeriod */
            foreach ($validationPeriods as $validationPeriod) {
                $this->getLoggerService()->notice(sprintf("$personNewReferent est maintenant validateur administratif pour $$validationPeriod"));
                $validationPeriod->addValidatorAdm($personNewReferent);
            }
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            throw new OscarException("Impossible d'ajouter le référent '$personNewReferent' à partir de '$fromPerson' : " . $e->getMessage());
        }
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SUPPRESSION
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Supprime le référent.
     * @param $referent_id
     * @return bool
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeReferentById($referent_id)
    {
        try {
            $referent = $this->getReferentById($referent_id);
            $this->getEntityManager()->remove($referent);
            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $e) {
            throw new OscarException(sprintf(_('Impossible de supprimer le référent(%s) : %s', $referent_id, $e->getMessage())));
        }
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AUTRES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Retourne la liste des organizations où la personne a un rôle principale.
     * @param Person $person
     * @return Organization[]
     */
    public function getOrganizationsPersonWithPrincipalRole(Person $person)
    {
        return $this->getOrganizationService()->getOrganizationsWithPersonRolled($person, null, true);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// MAILINGS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Lance la procédure de relance par email pour les personnes ayant souscrit à
     * la relance automatique et ayant des notifications non-lues.
     *
     * @param string $dateRef
     */
    public function mailPersonsWithUnreadNotification($dateRef = "")
    {
        $date = new \DateTime($dateRef);

        $rel = [
            'Mon' => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mer',
            'Thu' => 'Jeu',
            'Fri' => 'Ven',
            'Sat' => 'Sam',
            'Sun' => 'Dim',
        ];

        // Fromat du cron
        $cron = $rel[$date->format('D')] . $date->format('G');

        $this->getLoggerService()->info("Notifications des inscrits à '$cron'");

        // Liste des personnes ayant des notifications non-lues
        $persons = $this->getRepository()->getPersonsWithUnreadNotificationsAndAuthentification();
        $this->getLoggerService()->info(sprintf(" %s personne(s) ont des notifications non-lues", count($persons)));

        /** @var Person $person */
        foreach ($persons as $person) {
            /** @var Authentification $auth */
            $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $person->getLadapLogin()]);
            $settings = $auth->getSettings();

            if (!$settings) {
                $settings = [];
            }

            if (!array_key_exists('frequency', $settings)) {
                $settings['frequency'] = [];
            }

            $settings['frequency'] = array_merge($settings['frequency'], $this->getOscarConfigurationService()->getConfiguration('notifications.fixed'));

            if (in_array($cron, $settings['frequency'])) {
                $this->mailNotificationsPerson($person);
            } else {
                $this->getLoggerService()->info(sprintf('%s n\'est pas inscrite à ce crénaux', $person));
            }
        }
    }

    public function mailNotificationsPerson($person, $debug = true)
    {
        /** @var ConfigurationParser $configOscar */
        $configOscar = $this->getOscarConfigurationService();

        $conf = $this->getOscarConfigurationService()->getConfigArray();
        $appName = $conf['unicaen-app']['app_infos']['nom'];

        if ($debug) {
            $log = function ($msg) {
                $this->getLoggerService()->debug($msg);
            };
        } else {
            $log = function () {
            };
        }

        $datas = $this->getNotificationService()->getNotificationsPerson($person->getId(), true);
        $notifications = $datas['notifications'];

        if (count($notifications) == 0) {
            $log(sprintf(" - Pas de notifications non-lues pour %s", $person));
            return;
        }

        $url = $this->getServiceContainer()
            ->get('ViewHelperManager')
            ->get('url');

        $reg = '/(.*)\[Activity:([0-9]*):(.*)\](.*)/';

        $content = "Bonjour $person, <br>\n";
        $content .= "Vous avez des notifications non-lues sur $appName : \n";
        $content .= "<ul>\n";

        Moment::setLocale('fr_FR');

        foreach ($notifications as $n) {
            $moment = new Moment($n['dateEffective']);
            $formatted = $moment->format('l d F Y');
            $since = $moment->from('now')->getRelative();

            $message = $n['message'];
            if (preg_match($reg, $n['message'], $matches)) {
                $link = $configOscar->getConfiguration("urlAbsolute") . $url('contract/show', array('id' => $matches[2]));
                $message = preg_replace($reg, '$1 <a href="' . $link . '">$3</a> $4', $n['message']);
            }
            $content .= "<li><strong>" . $formatted . " (" . $since . ") : </strong> " . $message . "</li>\n";
        }

        //  TODO vérifier que ça fonctionne
        /** @var MailingService $mailer */
        $mailer = $this->getServiceContainer()->get(MailingService::class);
        $to = $person->getEmail();
        $content .= "</ul>\n";
        $mail = $mailer->newMessage("Notifications en attente", ['body' => $content]);
        $mail->setTo([$to => (string)$person]);
        $mailer->send($mail);
    }


    /**
     * Charge en profondeur la liste des personnes disposant du privilége sur une
     * activité. (Beaucoup de requêtes, attention ux perfs)
     *
     * @param $privilegeFullCode
     * @param $activity
     */
    public function getAllPersonsWithPrivilegeInActivity($privilegeFullCode, Activity $activity, $includeApp = false)
    {
        // Résultat
        $persons = [];

        /** @var PrivilegeRepository $privilegeRepository */
        $privilegeRepository = $this->getEntityManager()->getRepository(Privilege::class);

        try {
            $rolesIds = []; // rôles
            $ldapFilters = []; // filtre LDAP
            // 1. Récupération des rôles associès au privilège
            $privilege = $privilegeRepository->getPrivilegeByCode($privilegeFullCode);


            foreach ($privilege->getRole() as $role) {
                $rolesIds[] = $role->getRoleId();
                if ($role->getLdapFilter()) {
                    $ldapFilters[] = preg_replace('/\(memberOf=(.*)\)/',
                        '$1', $role->getLdapFilter());
                }
            }

            if ($includeApp) {

                // Selection des personnes qui ont le filtre LDAP (Niveau applicatif)
                if ($ldapFilters) {
                    $clause = [];
                    foreach ($ldapFilters as $f) {
                        $clause[] = "p.ldapMemberOf LIKE '%$f%'";
                    }

                    $personsLdap = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
                        ->where(implode(' OR ', $clause))
                        ->getQuery()
                        ->getResult();

                    foreach ($personsLdap as $p) {
                        $persons[$p->getId()] = $p;
                    }
                }

                // Selection des personnes via l'authentification (Affectation en dur, niveau applicatif)
                $authentifications = $this->getEntityManager()->createQueryBuilder()
                    ->select('a, r')
                    ->from(Authentification::class, 'a')
                    ->innerJoin('a.roles', 'r')
                    ->getQuery()
                    ->getResult();

                foreach ($authentifications as $auth) {
                    if ($auth->hasRolesIds($rolesIds)) {
                        try {
                            $person = $this->getEntityManager()->getRepository(Person::class)->findOneBy(['ladapLogin' => $auth->getUsername()]);
                            if ($person) {
                                $persons[$person->getId()] = $person;
                            }
                        } catch (\Exception $e) {
                            echo "Error : " . $e->getMessage() . "<br>\n";
                        }
                    }
                }
            }

            // Selection des personnes associées via le Projet/Activité

            foreach ($activity->getPersonsDeep() as $p) {
                if (in_array($p->getRole(), $rolesIds)) {
                    $persons[$p->getPerson()->getId()] = $p->getPerson();
                }
            }

            // Selection des personnes via l'oganisation assocociée au Projet/Activité
            /** @var Organization $organization */
            foreach ($activity->getOrganizationsDeep() as $organization) {

                /** @var OrganizationPerson $personOrganization */
                if ($organization->isPrincipal()) {
                    foreach ($organization->getOrganization()->getPersons(false) as $personOrganization) {
                        if (in_array($personOrganization->getRole(), $rolesIds)) {
                            $persons[$personOrganization->getPerson()->getId()] = $personOrganization->getPerson();
                        }
                    }
                }
            }

            return $persons;


        } catch (\Exception $e) {
            throw new OscarException("Impossible de trouver les personnes : " . $e->getMessage());
        }
    }


    private $_cachePersonLdapLogin;

    /**
     * @param $login
     * @return Person
     */
    public function getPersonByLdapLogin($login)
    {
        if ($this->_cachePersonLdapLogin === null) {
            $this->_cachePersonLdapLogin = [];
        }

        if (!isset($this->_cachePersonLdapLogin[$login])) {
            $query = $this->getBaseQuery()
                ->setParameter('login', $login);

            $normalize = $this->getOscarConfigurationService()->getConfiguration('authPersonNormalize', false);

            if ($normalize == true) {

                $query->where('LOWER(p.ladapLogin) = :login')
                    ->setParameter('login', strtolower($login));
            } else {
                $query->where('p.ladapLogin = :login')
                    ->setParameter('login', $login);
            }

            $this->_cachePersonLdapLogin[$login] = $query->getQuery()->getSingleResult();
        }
        return $this->_cachePersonLdapLogin[$login];
    }

    /**
     * @param $email
     * @return Person
     */
    public function getPersonByEmail($email)
    {
        return $this->getBaseQuery()
            ->where('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRolesByAuthentification()
    {
        try {
            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('login', 'login');
            $rsm->addScalarResult('roleid', 'roleid');

            $native = $this->getEntityManager()->createNativeQuery(
                'SELECT a.username as login, ur.role_id as roleid FROM authentification a
                    INNER JOIN authentification_role ar
                    ON ar.authentification_id = a.id
                    INNER JOIN user_role ur
                    ON ar.role_id = ur.id',
                $rsm
            );

            $out = [];

            foreach ($native->getResult() as $row) {
                if (!array_key_exists($row['login'], $out)) {
                    $out[$row['login']] = [];
                }
                $out[$row['login']][] = $row['roleid'];
            }

            return $out;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retourne la liste des différents rôles endossés par le personne dans les activités/Projet de recherche.
     *
     * @param Person $person
     * @return Role[]
     */
    public function getRolesPersonInActivities(Person $person)
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getEntityManager()->getRepository(Role::class);
        return $roleRepository->getDistinctRolesPersonInActivities($person);
    }

    /**
     * Retourne la liste des différents rôles endossés par le personne dans les activités/Projets de recherche.
     *
     * @param Person $person
     * @return Role[]
     */
    public function getRolesPersonInOrganizations(Person $person)
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getEntityManager()->getRepository(Role::class);
        return $roleRepository->getDistinctRolesPersonInOrganizations($person);
    }

    /**
     * Retourne la liste des rôles de la personne dans l'application.
     *
     * @param Person $person
     * @return mixed
     * @see getRolesPersonInApplication
     */
    public function getRolesPersonInApplication(Person $person)
    {
        return $this->getRolesApplication($person);
    }

    /**
     * Retourne l'Authentification associée à la personne. Cela se base sur le champ 'ladaplogin' de l'objet personne.
     * qui correspond au champ 'username' dans Authentification. Si la personne ne s'est jamais connectée, il est possible
     * qu'il n'y ai pas d'objet Authentification pour la personne.
     *
     * @param Person $person
     * @return Authentification
     */
    public function getPersonAuthentification(Person $person)
    {
        /** @var AuthentificationRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Authentification::class);

        return $repo->getAuthentificationPerson($person);
    }

    /**
     * Retourne la liste des roles de la person définit manuellement sur l'authentification ou obtenu via les
     * groupes LDAP
     *
     * @return Role[]
     */
    public function getRolesApplication(Person $person)
    {

        /** @var Role[] $inRoles */
        $inRoles = [];

        // Récupération des rôles via l'authentification
        $authentification = $this->getPersonAuthentification($person);

        /** @var Role $role */
        foreach ($authentification->getRoles() as $role) {
            $inRoles[$role->getRoleId()] = $role;
        }

        if ($person->getLdapMemberOf()) {

            // Récupération des rôles avec des filtres LDAP
            $roles = $this->getEntityManager()->getRepository(Role::class)->getRolesLdapFilter();

            /** @var Role $role */
            foreach ($roles as $role) {

                // Le rôle est déjà présent "en dur"
                if (array_key_exists($role->getRoleId(), $inRoles)) continue;

                // Test des rôles via le filtreLDAP
                $roleLdapFilter = $role->getLdapFilter();

                foreach ($person->getLdapMemberOf() as $memberOf) {
                    if (strpos($roleLdapFilter, $memberOf)) {
                        $inRoles[$role->getRoleId()] = $role;
                        continue 2;
                    }
                }
            }
        }

        return $inRoles;
    }

    /**
     * @param Person $person
     * @return string[]
     */
    public function getRolesApplicationArray(Person $person)
    {
        return array_keys($this->getRolesApplication($person));
    }

    public function getRolesAuthentification(Authentification $authentification)
    {
        return $authentification->getRoles();
    }

    public function getCoWorkerIds($idPerson)
    {
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getEntityManager()->getRepository(Organization::class);

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        // Organisations où la person est "principale"
        $orgaIds = $organizationRepository->getOrganizationsIdsForPerson($idPerson, true);

        if (count($orgaIds) == 0) {
            return [];
        }

        // IDS des membres des organisations
        $coWorkersIds = $personRepository->getPersonIdsInOrganizations($orgaIds);

        // Inclue les personnes impliquées dans des activités ?
        $listPersonIncludeActivityMember = $this->getOscarConfigurationService()->getConfiguration('listPersonnel');
        if ($listPersonIncludeActivityMember == 3) {
            $engaged = $personRepository->getPersonIdsForOrganizationsActivities($orgaIds);

            $coWorkersIds = array_unique(array_merge($coWorkersIds, $engaged));
        }

        return $coWorkersIds;
    }

    public function getSubordinateIds($idPerson)
    {

        // Récupération des subordonnés
        $nm1 = $this->getEntityManager()->getRepository(Referent::class)->createQueryBuilder('r')
            ->innerJoin('r.person', 'p')
            ->select('p.id')
            ->where('r.referent = :person')
            ->setParameters([
                'person' => $this->getCurrentPerson()
            ])
            ->getQuery()
            ->getResult();

        return array_map('current', $nm1);
    }

    /**
     * Retourne la liste des IDS des personnes qui ont autorisé la délégation du remplissage des feuilles de temps.
     * @param $idPerson
     */
    public function getTimesheetDelegationIds($idPerson)
    {
        /** @var Person $person */
        $person = $this->getPersonRepository()->find($idPerson);
        $ids = [];
        foreach ($person->getTimesheetsFor() as $p) {
            $ids[] = $p->getId();
        }
        return $ids;
    }

    /**
     * @param int $currentPage
     * @param int $resultByPage
     *
     * @return UnicaenDoctrinePaginator
     */
    public function getPersonsPaged($currentPage = 1, $resultByPage = 50)
    {
        return new UnicaenDoctrinePaginator($this->getBaseQuery(), $currentPage,
            $resultByPage);
    }

    /**
     * @return PersonRepository
     */
    protected function getPersonRepository()
    {
        return $this->getEntityManager()->getRepository(Person::class);
    }

    public function searchPersonnel(
        $search = null,
        $currentPage = 1,
        $filters = [],
        $resultByPage = 50
    )
    {
        $query = $this->getBaseQuery();

        if ($search) {
            /** @var ProjectGrantService $activityService */
            $activityService = $this->getProjectGrantService();

            $ids = $activityService->search($search);

            $idsPersons = $this->getPersonRepository()->getPersonsIdsForActivitiesids($ids);


            $query->leftJoin('p.organizations', 'o')
                ->leftJoin('p.activities', 'a')
                ->where('p.id IN(:ids)')
                ->setParameter('ids', $idsPersons);
        }

        if (array_key_exists('ids', $filters)) {
            $query->andWhere('p.id IN(:filterIds)')
                ->setParameter('filterIds', $filters['ids']);
        }

        return new UnicaenDoctrinePaginator($query, $currentPage,
            $resultByPage);
    }


    /**
     * Retourne la liste des identifiants des personnes qui déclarent des feuilles de temps.
     *
     * @return array
     */
    public function getDeclarersIds()
    {
        $persons = $this->getEntityManager()->createQueryBuilder()->select('DISTINCT(p.id)')
            ->from(Person::class, 'p')
            ->innerJoin('p.workPackages', 'wp')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        $declarersIds = array_map('current', $persons);

        return $declarersIds;
    }

    /**
     * Retourne la liste des identifiants des personnes référentes (N+1 - Ils assurent la validation administrative d'une
     * ou plusieurs personnes).
     */
    public function getNp1Ids()
    {
        $persons = $this->getEntityManager()->createQueryBuilder()->select('DISTINCT(p.id)')
            ->from(Referent::class, 'r')
            ->innerJoin('r.referent', 'p')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        $np1Ids = array_map('current', $persons);
        return $np1Ids;
    }

    /**
     * @param string|null $search
     * @param int $currentPage
     * @param int $resultByPage
     *
     * @return UnicaenDoctrinePaginator
     */
    public function getPersonsSearchPaged(
        $search = null,
        $currentPage = 1,
        $filters = [],
        $resultByPage = 50
    )
    {

        $query = $this->getBaseQuery();

        // PATCH : Visiblement, ces INNER JOIN provoquent un delais
        // de requêtage de l'espace
        /*
         $query->leftJoin('p.organizations', 'o')
            ->leftJoin('p.activities', 'a');
        */
        if ($filters['declarers'] == 'on') {
            $ids = $this->getDeclarersIds();
            if (array_key_exists('ids', $filters)) {
                $filters['ids'] = array_intersect($filters['ids'], $ids);
            } else {
                $filters['ids'] = $ids;
            }
        }

        // FIltrer les IDS des personnes nommées N+1 explicitement (Objet 'Referent')
        if ($filters['np1'] == 'on') {
            $ids = $this->getNp1Ids();
            if (array_key_exists('ids', $filters)) {
                $filters['ids'] = array_intersect($filters['ids'], $ids);
            } else {
                $filters['ids'] = $ids;
            }
        }

        if ($filters['leader']) {
            $query = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
                ->innerJoin('p.organizations', 'o')
                ->innerJoin('o.roleObj', 'r')
                ->innerJoin('p.activities', 'a')
                ->where('r.principal = true');
        }

        if (array_key_exists('order_by', $filters)) {
            $query->addOrderBy('p.' . $filters['order_by'], 'ASC');
        }

        // RECHERCHE sur le connector
        // Ex: rest:p00000001
        if (preg_match('/(([a-z]*):(.*))/', $search, $matches)) {
            $connector = $matches[2];
            $connectorValue = $matches[3];
            try {
                $query = $this->getEntityManager()->getRepository(Person::class)->getPersonByConnectorQuery($connector, $connectorValue);
            } catch (\Exception $e) {
                $this->getLoggerService()->error("Requête sur le connecteur : " . $e->getMessage());
                throw new OscarException("Impossible d'obtenir les personnes via l'UI de connector");
            }
        } // RECHERCHE sur le nom/prenom/email
        else {
            if ($search != "") {

                try {
                    $ids = $this->getSearchEngineStrategy()->search($search);

                    if (array_key_exists('ids', $filters)) {
                        $filters['ids'] = array_intersect($filters['ids'], $ids);
                    } else {
                        $filters['ids'] = $ids;
                    }
                } catch (\Exception $e) {
                    $this->getLoggerService()->error(sprintf("Méthode de recherche des personnes non-disponible : %s", $e->getMessage()));
                    // Ancienne méthode
                    $searchR = str_replace('*', '%', $search);
                    $query->where('lower(p.firstname) LIKE :search OR lower(p.lastname) LIKE :search OR lower(p.email) LIKE :search OR LOWER(CONCAT(CONCAT(p.firstname, \' \'), p.lastname)) LIKE :search OR LOWER(CONCAT(CONCAT(p.lastname, \' \'), p.firstname)) LIKE :search')
                        ->setParameter('search', '%' . strtolower($searchR) . '%');
                }
            }
        }

        // FILTRE : Application des filtres sur les rôles
        if (isset($filters['filter_roles']) && count($filters['filter_roles']) > 0) {

            // Liste des ID person retenus
            $ids = [];

            ////////////////////////////////////////////////////////////////////
            // IDPERSON à partir des filtres LDAP

            // Obtention des groupes LDAP fixes sur les rôles
            // BUT : Obtenir la liste des filtres LDAP pour les rôles selectionnés
            $roles = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('r')
                ->where('r.roleId IN(:roles)')
                ->setParameter('roles', $filters['filter_roles']);

            // Rôles attribuès en dur
            /** @var SELECT * FROM person p
             * INNER JOIN authentification a
             * ON p.ladaplogin = a.username $fixed */
            try {
                $rsm = new Query\ResultSetMapping();
                $rsm->addScalarResult('person_id', 'person_id');;
                $native = $this->getEntityManager()->createNativeQuery(
                    'SELECT p.id as person_id FROM person p
                    INNER JOIN authentification a
                    ON p.ladaplogin = a.username

                    INNER JOIN authentification_role ar
                    ON ar.authentification_id = a.id

                    INNER JOIN user_role ur
                    ON ar.role_id = ur.id

                    WHERE ur.role_id IN (:roles)',
                    $rsm
                );

                foreach ($native->setParameter('roles', $filters['filter_roles'])->getResult() as $row) {
                    $ids[] = $row['person_id'];
                }

            } catch (\Exception $e) {
                $this->getLoggerService()->error("Impossible de charger les personnes via les rôles des authentifications : " . $e->getMessage());
                throw new OscarException("Erreur de chargement des rôles via l'authentification");
            }


            $filterLdap = [];

            // Création de la cause pour la selection des personnes niveau Application
            foreach ($roles->getQuery()->getResult() as $role) {
                if ($role->getLdapFilter())
                    $filterLdap[] = "ldapmemberof LIKE '%" . preg_replace('/\(memberOf=(.*)\)/', '$1', $role->getLdapFilter()) . "%'";
            }

            if ($filterLdap) {


                // Récupération des IDPERSON avec les filtres LDAP
                $rsm = new Query\ResultSetMapping();
                $rsm->addScalarResult('person_id', 'person_id');
                $native = $this->getEntityManager()->createNativeQuery(
                    'select distinct id as person_id from person where ' . implode(' OR ',
                        $filterLdap),
                    $rsm
                );

                try {
                    foreach ($native->getResult() as $row) {
                        $ids[] = $row['person_id'];
                    }
                } catch (\Exception $e) {
                    $this->getLoggerService()->error("Impossible de charger les personnes via les filtres LDAP : " . $e->getMessage());
                    throw new OscarException("Impossible de charger les personnes via les filtres LDAP");
                }
            }


            ////////////////////////////////////////////////////////////////////
            // IDPERSON à partir des rôles dans :
            // - les projets
            // - les activités
            // - les organisations

            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('person_id', 'person_id');

            try {
                $native = $this->getEntityManager()->createNativeQuery(
                    'select person_id from organizationperson j inner join user_role r on r.id = j.roleobj_id where r.role_id IN (:roles)
                    UNION
                    select person_id from activityperson ap inner join user_role r on r.id = ap.roleobj_id where r.role_id IN (:roles)
                    UNION
                    select person_id from projectmember pm inner join user_role r on r.id = pm.roleobj_id where r.role_id IN (:roles)
                    ',
                    $rsm
                );

                foreach ($native->setParameter('roles',
                    $filters['filter_roles'])->getResult() as $row) {
                    $ids[] = $row['person_id'];
                };
            } catch (\Exception $e) {
                $msg = "Impossible de charger les personnes via leur implication dans les organisations/activités";
                $this->getLoggerService()->error("$msg : " . $e->getMessage());
                throw new OscarException($msg);
            }

            // On compète la requète en réduisant les résultats à la liste
            // d'ID caluclée
            $query->andWhere('p.id IN (:ids)')
                ->setParameter(':ids', $ids);
        }


        if (array_key_exists('ids', $filters)) {
            $query->andWhere('p.id IN(:filterIds)')
                ->setParameter('filterIds', $filters['ids']);

            if ($search && count($filters['ids']) > 0) {
                // On ne trie que les 30 premiers
                $limit = 30;
                $case = '(CASE ';
                $i = 0;
                foreach ($filters['ids'] as $id) {
                    if ($i++ < $limit)
                        $case .= sprintf('WHEN p.id = \'%s\' THEN %s ', $id, $i++);
                }
                $case .= " ELSE $id END) AS HIDDEN ORD";
                $query->addSelect($case);
                $query->orderBy("ORD", 'ASC');
            }
        }

        return new UnicaenDoctrinePaginator($query, $currentPage, $resultByPage);
    }

    /**
     * Retourne l'objet Person à partir de l'identifiant.
     *
     * @param $id
     *
     * @return Person
     */
    public function getPerson($id)
    {
        return $this->getBaseQuery()
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function getPersons()
    {
        return $this->getBaseQuery()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $connectorName Nom de connecteur
     * @param $uid Valeur dans ce connecteur
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPersonByConnectorUid($connectorName, $uid)
    {
        return $this->getPersonRepository()->getPersonByConnectorID($connectorName, $uid);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// JOBS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function jobIndexPerson(Person $person)
    {
        $client = new \GearmanClient();
        $client->addServer($this->getOscarConfigurationService()->getGearmanHost());
        $client->doBackground('indexPerson', json_encode(['personid' => $person->getId()]), sprintf('personsearchupdate-%s', $person->getId()));
    }

    public function jobNotificationActivityPerson(Activity $activity, Person $person)
    {
        $client = new \GearmanClient();
        $client->addServer($this->getOscarConfigurationService()->getGearmanHost());
        $client->doBackground('notificationActivityPerson', json_encode([
            'activityid' => $activity->getId(),
            'personid' => $person->getId(),
        ]),
            sprintf('notificationactivity-%s-%s', $activity->getId(), $person->getId())
        );
    }


    /**
     * Retourne la liste des rôles disponibles niveau activité.
     *
     * @return Role[]
     */
    public function getAvailableRolesPersonActivity()
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getEntityManager()->getRepository(Role::class);
        $roles = $roleRepository->getRolesAtActivityArray();
        return $roles;
    }

    /**
     * @param $id
     * @param bool $throw
     * @return null|Person
     * @throws OscarException
     */
    public function getPersonById($id, $throw = false)
    {
        $person = $this->getEntityManager()->getRepository(Person::class)->find($id);
        if ($throw === true && $person == null) {
            throw new OscarException(sprintf(_("La personne avec l'identifiant %s n'est pas présente dans la base de données."), $id));
        }
        return $person;
    }

    /**
     * @param $id
     * @param bool $throw
     * @return null|Role
     * @throws OscarException
     */
    public function getRolePersonById($id, $throw = false)
    {
        $role = $this->getEntityManager()->getRepository(Role::class)->find($id);
        if ($throw === true && $role == null) {
            throw new OscarException(sprintf(_("Le rôle avec l'identifiant %s n'est pas présente dans la base de données."), $id));
        }
        return $role;
    }


    public function getPersonsPrincipalInActivityIncludeOrganization(Activity $activity)
    {
        $persons = [];

        /** @var ActivityPerson $activityperson */
        foreach ($activity->getPersonsDeep() as $activityperson) {
            if ($activityperson->isPrincipal() && !$activityperson->isOutOfDate()) {
                if (!in_array($activityperson->getPerson(), $persons))
                    $persons[] = $activityperson->getPerson();
            }
        }

        /** @var ActivityOrganization $activityOrganization */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            if ($activityOrganization->isPrincipal() && !$activityOrganization->isOutOfDate()) {

                /** @var OrganizationPerson $organizationPerson */
                foreach ($activityOrganization->getOrganization()->getPersons() as $organizationPerson) {
                    if ($organizationPerson->isPrincipal() && !$organizationPerson->isOutOfDate() && !in_array($organizationPerson->getPerson(), $persons)) {
                        $persons[] = $organizationPerson->getPerson();
                    }
                }
            }
        }

        return $persons;
    }



    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            // ->leftJoin('p.timesheetsBy', 'tb')
            ->from(Person::class, 'p');

        return $queryBuilder;
    }






    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AFFECTATIONS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Organization
    public function personOrganizationAdd(Organization $organization, Person $person, Role $role, $dateStart = null, $dateEnd = null)
    {
        if (!$organization->hasPerson($person, $role)) {
            $message = sprintf("a ajouté %s(%s) dans l'organisation %s", $person->log(), $role->getRoleId(), $organization->log());
            $this->getLoggerService()->info($message);
            $op = new OrganizationPerson();
            $this->getEntityManager()->persist($op);

            $op->setPerson($person)
                ->setOrganization($organization)
                ->setRoleObj($role)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);

            $this->getEntityManager()->flush($op);
            $this->getEntityManager()->refresh($organization);
            $this->getEntityManager()->refresh($person);

            if ($role->isPrincipal()) {
                $this->getLoggerService()->info("Role principal");
                /** @var ActivityOrganization $oa */
                foreach ($organization->getActivities() as $oa) {
                    $this->getLoggerService()->info("Activité : " . $oa->getActivity());
                    if ($oa->isPrincipal()) {
                        $this->getLoggerService()->info("Activités, rôle principal");
                        $this->getEntityManager()->refresh($oa->getActivity());
                        $this->jobNotificationActivityPerson($oa->getActivity(), $person);
                    }
                }
                foreach ($organization->getProjects() as $op) {
                    $this->getLoggerService()->info("Projet : " . $op->getProject());
                    if ($op->isPrincipal()) {
                        foreach ($op->getProject()->getActivities() as $a) {
                            $this->getLoggerService()->info("Project > Activités, rôle principal");
                            $this->getEntityManager()->refresh($a);
                            $this->jobNotificationActivityPerson($a, $person);
//                            $this->getNotificationService()->generateNotificationsForActivity($a, $person);
                        }
                    }
                }
            }
        }
    }


    public function personOrganizationRemove(OrganizationPerson $organizationPerson)
    {
        if ($organizationPerson->isPrincipal()) {
            /** @var OrganizationService $os */
            $os = $this->getOrganizationService();


            foreach ($os->getOrganizationActivititiesPrincipalActive($organizationPerson->getOrganization()) as $activity) {
                // $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $organizationPerson->getPerson());
                $this->getNotificationService()->jobNotificationsPersonActivity($activity, $organizationPerson->getPerson());
            }
        }
        $this->getEntityManager()->remove($organizationPerson);
        $this->getEntityManager()->flush();
    }


    /**
     * Retourne la liste des organisations de la personne.
     *
     * @param Person $person
     * @param bool $date Si $date est non-false, on test la date donnée
     * @param bool $pincipal TRUE : Tiens compte uniquement des rôles 'principaux'
     * @return array
     */
    public function getPersonOrganizations(Person $person, $date = false, $pincipal = false)
    {

        $qb = $this->getEntityManager()->getRepository(Organization::class)
            ->createQueryBuilder('o')
            ->innerJoin('o.persons', 'op')
            ->where('op.person = :person')
            ->setParameter('person', $person);

        if ($date !== false) {
            $date = $date === true ? new \DateTime() : $date;
            $qb->andWhere('op.dateStart IS NULL OR op.dateStart <= :date');
            $qb->andWhere('op.dateEnd >= :date OR op.dateEnd IS NULL');
            $qb->setParameter('date', $date);
        }

        if ($pincipal === true) {
            $qb->innerJoin('op.roleObj', 'r')
                ->andWhere('r.principal = true');
        }

        return $qb->getQuery()->getResult();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AFFECTATION AUX ACTIVITÉS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// PERSON <> ACTIVITY
    public function personActivityAdd(Activity $activity, Person $person, Role $role, $dateStart = null, $dateEnd = null)
    {
        if (!$activity->hasPerson($person, $role, $dateStart, $dateEnd)) {


            $personActivity = new ActivityPerson();
            $this->getEntityManager()->persist($personActivity);

            $personActivity->setPerson($person)
                ->setActivity($activity)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush($personActivity);

            // LOG
            $this->getActivityLogService()->addUserInfo(
                sprintf("a ajouté %s(%s) dans l'activité %s ", $person->log(), $role->getRoleId(), $activity->log()),
                'Activity:person', $activity->getId()
            );

            // Si le rôle est principal, on actualise les notifications de la personne
            if ($role->isPrincipal()) {
                $this->getEntityManager()->refresh($activity);
                $this->getNotificationService()->jobNotificationsPersonActivity($activity, $person);
            }

            $this->getProjectGrantService()->jobSearchUpdate($activity);
        }
    }

    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    public function personActivityRemove(ActivityPerson $activityPerson)
    {
        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();
        $roleId = $activityPerson->getRole();
        $updateNotification = $activityPerson->isPrincipal();
        $this->getEntityManager()->remove($activityPerson);

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $activity->log()),
            'Activity:person', $activity->getId()
        );

        // Si le rôle est principal, on actualise les notifications de la personne
        if ($updateNotification) {
            $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $person);
            //$this->getNotificationService()->jobNotificationsPersonActivity($activity, $person);
        }

        $this->jobSearchUpdate($person);
    }

    public function personActivityChangeRole(ActivityPerson $activityPerson, Role $newRole, $dateStart, $dateEnd)
    {

        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();

        $updateNotification = $activityPerson->isPrincipal() || $newRole->isPrincipal();
        $activityPerson->setRoleObj($newRole);
        $activityPerson->setDateStart($dateStart)->setDateEnd($dateEnd);
        $this->getEntityManager()->flush($activityPerson);
        $this->getLoggerService()->info(sprintf("Le rôle de personne %s a été modifié dans l'activité %s", $person, $activity));

        // Si le rôle est principal, on actualise les notifications de la personne
        if ($updateNotification) {
            $this->getEntityManager()->refresh($activity);
            $this->getNotificationService()->purgeNotificationsPersonActivity($activity, $person);
            $this->getNotificationService()->jobNotificationsPersonActivity($activity, $person);
        }

        $this->jobSearchUpdate($person);
    }

    // PROJECT

    /**
     * @param Project $project
     * @param Person $person
     * @param Role $role
     * @param null $dateStart
     * @param null $dateEnd
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function personProjectAdd(Project $project, Person $person, Role $role, $dateStart = null, $dateEnd = null)
    {
        if (!$project->hasPerson($person, $role, $dateStart, $dateEnd)) {

            $personProject = new ProjectMember();
            $this->getEntityManager()->persist($personProject);

            $personProject->setPerson($person)
                ->setProject($project)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush($personProject);
            $this->getLoggerService()->info(sprintf("La personne %s a été ajouté au projet %s", $person, $project));

            // Si le rôle est principal, on actualise les notifications de la personne
            if ($role->isPrincipal()) {
                foreach ($project->getActivities() as $activity) {
                    $this->getNotificationService()->jobNotificationsPersonActivity($activity, $person);
                }
            }
            $this->jobSearchUpdate($person);
        }
    }

    public function personProjectRemove(ProjectMember $projectPerson)
    {
        $person = $projectPerson->getPerson();
        $project = $projectPerson->getProject();

        $roleId = $projectPerson->getRole();
        $updateNotification = $projectPerson->isPrincipal();

        $this->getEntityManager()->remove($projectPerson);

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $project->log()),
            'Project:person', $project->getId()
        );

        // Si le rôle est principal, on actualise les notifications de la personne
        if ($updateNotification) {
            $this->getEntityManager()->refresh($project);
            $this->getNotificationService()->purgeNotificationsPersonProject($project, $person);
        }
    }

    // PROJECT
    public function personProjectChangeRole(ProjectMember $personProject, Role $newRole, $dateStart = null, $dateEnd = null)
    {

        if ($newRole == $personProject->getRoleObj()) return;

        $person = $personProject->getPerson();
        $project = $personProject->getProject();

        $updateNotification = $personProject->isPrincipal() || $newRole->isPrincipal();
        $personProject->setRoleObj($newRole);
        $project->touch();

        $this->getEntityManager()->flush($personProject);
        $this->getEntityManager()->flush($project);

        $this->getLoggerService()->info(sprintf("Le rôle de personne %s a été modifié dans le projet %s", $person, $project));

        // Si le rôle est principal, on actualise les notifications de la personne
        if ($updateNotification) {
            $this->getEntityManager()->refresh($project);
            $this->getNotificationService()->purgeNotificationsPersonProject($project, $person);
            $this->getNotificationService()->generateNotificationsForProject($project, $person);
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return OrganizationService
     */
    public function getOrganizationService()
    {
        return $this->getServiceContainer()->get(OrganizationService::class);
    }
}
