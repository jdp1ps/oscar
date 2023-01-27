<?php

namespace Oscar\Service;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Authentification;
use Oscar\Entity\AuthentificationRepository;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Privilege;
use Oscar\Entity\PrivilegeRepository;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\RecallException;
use Oscar\Entity\RecallExceptionRepository;
use Oscar\Entity\Referent;
use Oscar\Entity\Role;
use Oscar\Entity\RoleRepository;
use Oscar\Entity\ValidationPeriod;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Strategy\Search\PersonSearchStrategy;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseGearmanJobLauncherService;
use Oscar\Traits\UseGearmanJobLauncherServiceTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\PeriodInfos;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class PersonService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService,
                               UseOscarUserContextService, UseNotificationService, UseProjectGrantService,
                               UseActivityLogService, UseServiceContainer, UseGearmanJobLauncherService
{
    use UseOscarConfigurationServiceTrait, UseEntityManagerTrait, UseLoggerServiceTrait, UseOscarUserContextServiceTrait, UseNotificationServiceTrait, UseProjectGrantServiceTrait, UseActivityLogServiceTrait, UseServiceContainerTrait, UseGearmanJobLauncherServiceTrait;

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
        $this->getLoggerService()->debug("[elastic:person:update] Person:" . $person->getId());
        $this->getSearchEngineStrategy()->update($person);
    }

    /**
     * Envoi à Gearman une demande d'actualisation du moteur de recherche.
     *
     * @param Person $person
     */
    public function jobSearchUpdate(Person $person)
    {
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }

    /**
     * Remise à zéro de l'index
     */
    public function searchIndex_reset()
    {
        $this->getLoggerService()->debug("[elastic:person:reset]");
        $this->getSearchEngineStrategy()->resetIndex();
    }

    /**
     * Reconstruction de l'index de recherche
     * @return mixed
     */
    public function searchIndexRebuild()
    {
        $this->getLoggerService()->debug("[elastic:person:rebuild]");
        $this->searchIndex_reset();
        $persons = $this->getEntityManager()->getRepository(Person::class)->findAll();
        return $this->getSearchEngineStrategy()->rebuildIndex($persons);
    }

    /**
     * Recherche textuelle parmis les personnes.
     * @param $what
     * @return Person[]
     */
    public function search($what): array
    {
        return $this->getPersonRepository()->getPersonsByIds($this->searchIds($what));
    }

    const SEARCH_ID_PATTERN = '/id:(([0-9]+,?)*)/';

    public function searchIds($what): array
    {
        if (preg_match_all(self::SEARCH_ID_PATTERN, $what, $matches, PREG_SET_ORDER, 0)) {
            $idsStr = $matches[0][1];
            if ($idsStr) {
                return explode(',', $idsStr);
            }
        }
        return $this->getSearchEngineStrategy()->search($what);
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
        if (!$person) {
            return [];
        }

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
        if (!$person) {
            return [];
        }

        $qb = $this->getEntityManager()->getRepository(Person::class)->createQueryBuilder('p')
            ->innerJoin(Referent::class, 'r', 'WITH', 'r.person = p')
            ->where('r.referent = :person');

        return $qb->setParameter('person', $person)->getQuery()->getResult();
    }

    /**
     * Retourne les personnes subordonnées du référent.
     *
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

    /**
     * Cloture le role d'une personne sur une organisation.
     *
     * @param Person $person
     * @param Organization $organization
     * @param Role $roleObj
     * @param bool $hardRemove Type de suppression
     */
    public function removePersonOrganizationWithRole(
        Person $person,
        Organization $organization,
        Role $roleObj,
        bool $hardRemove = false
    ): void {
        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization) {
            if (
                $personOrganization->getOrganization()->getId() == $organization->getId() &&
                $personOrganization->getRoleObj()->getId() == $roleObj->getId()
            ) {
                if ($hardRemove == true) {
                    $this->personOrganizationRemove($personOrganization);
                } else {
                    $this->getOrganizationService()->closeOrganizationPerson($personOrganization, new \DateTime());
                }
            }
        }
    }


    /**
     * Transfert les affectations actives d'une personne vers une autre. Concerne :
     *  - Affectation Projet
     *  - Activité
     *  - Structure
     *  - Validation
     *  - N+1 (TODO)
     *
     * @param Person $fromPerson
     * @param array $rules
     * @throws NoResultException
     * @throws OscarException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function affectationsReplace(Person $fromPerson, array $rules): void
    {
        try {
            $replacer = $this->getPersonById($rules['replacer_id'], true);
        } catch (\Exception $exception) {
            throw new OscarException("Le remplaçant '%s' est introuvable.", $rules['replacer_id']);
        }

        foreach ($rules['projects'] as $projectId => $projectDatas) {
            if ($projectDatas['apply']) {
                try {
                    $project = $this->getProjectGrantService()->getProjectService()->getProject($projectId, true);
                    $this->getProjectGrantService()->getProjectService()->replacePerson(
                        $fromPerson,
                        $replacer,
                        $project
                    );
                    $this->getLoggerService()->info(
                        "Remplacement de $fromPerson par $replacer dans le projet $project"
                    );
                } catch (\Exception $e) {
                }
            }
        }

        foreach ($rules['activities'] as $activityId => $activityDatas) {
            if ($activityDatas['apply']) {
                $activity = $this->getProjectGrantService()->getActivityById($activityId);
                $this->getProjectGrantService()->replacePerson($fromPerson, $replacer, $activity);
                $this->getLoggerService()->info("Replacer $fromPerson par $replacer dans l'activité $activity");
            }
        }

        foreach ($rules['structures'] as $organizationId => $organizationDatas) {
            if ($organizationDatas['apply']) {
                $organization = $this->getProjectGrantService()->getOrganizationService()->getOrganization(
                    $organizationId
                );
                foreach ($organizationDatas['roles'] as $roleDatas) {
                    if ($roleDatas['active']) {
                        $roleObj = $this->getRoleRepository()->getRoleByRoleId($roleDatas['roleId']);
                        $this->personOrganizationAdd(
                            $organization,
                            $replacer,
                            $roleObj
                        );

                        $this->removePersonOrganizationWithRole($fromPerson, $organization, $roleObj);

                        $this->getLoggerService()->info(
                            "Replacer $fromPerson par $replacer dans la structure $organization"
                        );
                    }
                }
            }
        }

        // Validation PROJET
        /** @var ActivityPerson $activityPerson */
        foreach ($rules['validations']['prj'] as $activityId => $validationDatas) {
            if ($validationDatas['active']) {
                try {
                    $activity = $this->getProjectGrantService()->getActivityById($activityId);
                    $this->getTimesheetService()->addValidatorActivity($replacer->getId(), $activity->getId(), 'prj');
                    $this->getTimesheetService()->removeValidatorActivity(
                        $fromPerson->getId(),
                        $activity->getId(),
                        'prj'
                    );
                } catch (\Exception $e) {
                    $this->getLoggerService()->warning("Accès à l'activité $activityId impossible");
                    continue;
                }
            }
        }

        // Validation SCI
        /** @var ActivityPerson $activityPerson */
        foreach ($rules['validations']['sci'] as $activityId => $validationDatas) {
            if ($validationDatas['active']) {
                try {
                    $activity = $this->getProjectGrantService()->getActivityById($activityId);
                    $this->getTimesheetService()->addValidatorActivity($replacer->getId(), $activity->getId(), 'sci');
                    $this->getTimesheetService()->removeValidatorActivity(
                        $fromPerson->getId(),
                        $activity->getId(),
                        'sci'
                    );
                } catch (\Exception $e) {
                    $this->getLoggerService()->warning("Accès à l'activité $activityId impossible");
                    continue;
                }
            }
        }

        // Validation ADM
        /** @var ActivityPerson $activityPerson */
        foreach ($rules['validations']['adm'] as $activityId => $validationDatas) {
            if ($validationDatas['active']) {
                try {
                    $activity = $this->getProjectGrantService()->getActivityById($activityId);
                    $this->getTimesheetService()->addValidatorActivity($replacer->getId(), $activity->getId(), 'adm');
                    $this->getTimesheetService()->removeValidatorActivity(
                        $fromPerson->getId(),
                        $activity->getId(),
                        'adm'
                    );
                } catch (\Exception $e) {
                    $this->getLoggerService()->warning("Accès à l'activité $activityId impossible");
                    continue;
                }
            }
        }

        foreach ($rules['referents'] as $referentId => $referentDatas) {
            try {
                $referent = $this->getPersonById($referentId, true);
                $this->addReferent($referent->getId(), $replacer->getId());
                $this->removeReferentOnPerson($referent, $fromPerson);
            } catch (\Exception $e) {
                $this->getLoggerService()->warning("Impossible d'ajouter le référent $referentId à $replacer");
            }
        }

        foreach ($rules['subordinates'] as $subordinateId => $subordinateDatas) {
            try {
                $subordinate = $this->getPersonById($subordinateId, true);
                $this->addReferent($replacer->getId(), $subordinate->getId());
                $this->removeReferentOnPerson($fromPerson, $subordinate);
            } catch (\Exception $e) {
                $this->getLoggerService()->warning("Impossible d'ajouter le subordonné $subordinateId à $replacer");
            }
        }
    }


    /**
     * Aggrégation des données d'affectation d'une personne. Le modèle inclus un calcule d'état sur les assignations en
     * précisant si l'objet est ACTIF :
     *  - Projet : Au moins une activité a le status Actif
     *  - Activité : A le status Actif
     *  - Structure : A une date de fin null ou > à NOW
     *  - Validation : L'activité associée a le status Actif
     *  - N+1
     *
     * @param Person $person
     * @return array
     */
    public function getPersonAffectationsArray(Person $person, ?Person $replacer = null): array
    {
        if ($replacer && $replacer == $person) {
            throw new OscarException("Vous ne pouvez pas remplacer une personne par elle-même");
        }
        $output = $person->toArray();
        $output['structures'] = [];
        $output['activities'] = [];
        $output['projects'] = [];
        $output['validations'] = [
            'prj' => [],
            'sci' => [],
            'adm' => [],
        ];
        $output['np1'] = [];

        if ($replacer) {
            $output['replacer'] = $replacer->toArray();
        }

        $formatApplyable = function (&$output, $itemId, $itemLabel, $itemActive, $roleId, $roleActive) {
            $apply = $itemActive && $roleActive;
            if (!array_key_exists($itemId, $output)) {
                $output[$itemId] = [
                    'label' => $itemLabel,
                    'active' => $itemActive,
                    'roleActive' => $roleActive,
                    'apply' => false,
                    'roles' => []
                ];
            }
            if ($apply) {
                $output[$itemId]['apply'] = true;
            }

            if (!in_array($roleId, $output[$itemId]['roles'])) {
                $output[$itemId]['roles'][$roleId] = [
                    'roleId' => $roleId,
                    'active' => false
                ];
            }
            $output[$itemId]['roles'][$roleId]['active'] |= $roleActive;
        };

        /** @var OrganizationPerson $personOrganization */
        foreach ($person->getOrganizations() as $personOrganization) {
            /** @var Organization $organization */
            $organization = $personOrganization->getOrganization();

            $itemId = $organization->getId();
            $itemLabel = (string)$organization;
            $itemActive = !$organization->isClose();

            /** @var Role $role */
            $role = $personOrganization->getRoleObj();
            $roleId = $role->getRoleId();
            $roleActive = !$personOrganization->isOutOfDate();

            $formatApplyable($output['structures'], $itemId, $itemLabel, $itemActive, $roleId, $roleActive);
        }

        /** @var ProjectMember $personProject */
        foreach ($person->getProjectAffectations() as $personProject) {
            $item = $personProject->getProject();
            $itemId = $item->getId();
            $itemLabel = (string)$item;
            $itemActive = $item->isActive();

            /** @var Role $role */
            $role = $personProject->getRoleObj();
            $roleId = $role->getRoleId();
            $roleActive = !$personProject->isOutOfDate();

            $formatApplyable($output['projects'], $itemId, $itemLabel, $itemActive, $roleId, $roleActive);
        }

        /** @var ActivityPerson $activityPerson */
        foreach ($person->getActivities() as $activityPerson) {
            $item = $activityPerson->getActivity();
            $itemId = $item->getId();
            $itemLabel = (string)$item;
            $itemActive = $item->isActive();

            /** @var Role $role */
            $role = $activityPerson->getRoleObj();
            $roleId = $role->getRoleId();
            $roleActive = !$activityPerson->isOutOfDate();

            $formatApplyable($output['activities'], $itemId, $itemLabel, $itemActive, $roleId, $roleActive);
        }

        // Validation PROJET
        /** @var ActivityPerson $activityPerson */
        foreach ($person->getValidatorActivitiesPrj() as $activity) {
            if (!array_key_exists($activity->getId(), $output['validations']['prj'])) {
                $output['validations']['prj'][$activity->getId()] = [
                    'id' => $activity->getId(),
                    'label' => (string)$activity,
                    'apply' => $activity->isActive(),
                    'active' => $activity->isActive()
                ];
            }
        }

        // Validation SCIENTIFIQUE
        /** @var ActivityPerson $activityPerson */
        foreach ($person->getValidatorActivitiesSci() as $activity) {
            if (!array_key_exists($activity->getId(), $output['validations']['sci'])) {
                $output['validations']['sci'][$activity->getId()] = [
                    'id' => $activity->getId(),
                    'label' => (string)$activity,
                    'apply' => $activity->isActive(),
                    'active' => $activity->isActive()
                ];
            }
        }

        // Validation ADMINISTRATIVE
        /** @var ActivityPerson $activityPerson */
        foreach ($person->getValidatorActivitiesAdm() as $activity) {
            if (!array_key_exists($activity->getId(), $output['validations']['adm'])) {
                $output['validations']['adm'][$activity->getId()] = [
                    'id' => $activity->getId(),
                    'label' => (string)$activity,
                    'apply' => $activity->isActive(),
                    'active' => $activity->isActive()
                ];
            }
        }

        $referents = $this->getReferentsPerson($person);
        $subordinates = $this->getSubordinatesPerson($person);

        $output['referents'] = [];
        $output['subordinates'] = [];

        foreach ($referents as $referent) {
            if ($replacer && $referent->getReferent()->getId() == $replacer->getId()) {
                continue;
            }
            $output['referents'][$referent->getReferent()->getId()] = [
                'id' => $referent->getReferent()->getId(),
                'label' => (string)$referent->getReferent(),
                'mail' => $referent->getReferent()->getEmail(),
                'mailmd5' => md5($referent->getReferent()->getEmail()),
                'apply' => true
            ];
        }

        foreach ($subordinates as $subordinate) {
            if ($replacer && $subordinate->getPerson()->getId() == $replacer->getId()) {
                continue;
            }
            $output['subordinates'][$subordinate->getPerson()->getId()] = [
                'id' => $subordinate->getPerson()->getId(),
                'label' => (string)$subordinate->getPerson(),
                'mail' => $subordinate->getPerson()->getEmail(),
                'mailmd5' => md5($subordinate->getPerson()->getEmail()),
                'apply' => true
            ];
        }


        return $output;
    }

    public function validatorReplace(Person $replaced, Person $replacer): void
    {
        foreach ($replaced->getValidatorActivitiesPrj() as $activity) {
            $activity->getValidatorsPrj()->add($replacer);
            $activity->getValidatorsPrj()->removeElement($replaced);
        }
        foreach ($replaced->getValidatorActivitiesSci() as $activity) {
            $activity->getValidatorsSci()->add($replacer);
            $activity->getValidatorsSci()->removeElement($replaced);
        }
        foreach ($replaced->getValidatorActivitiesAdm() as $activity) {
            $activity->getValidatorsAdm()->add($replacer);
            $activity->getValidatorsAdm()->removeElement($replaced);
        }
        $this->getEntityManager()->flush();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AJOUT
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function removeAddReferent(
        int $subordinateId,
        ?int $addedReferentId = null,
        ?int $removedReferentId = null,
        bool $flush = true
    ): bool {
        /** @var Person $subordinate */
        $subordinate = $this->getPersonById($subordinateId, true);

        /** @var int[] $referentsIds */
        $referentsIds = $this->getPersonRepository()->getReferentsIdsPerson($subordinate->getId());

        // Référent à ajouter
        $added = null;

        // Référent à supprimer
        $removed = null;

        if ($addedReferentId == null && $removedReferentId == null) {
            $this->getLoggerService()->warning("Bad call : removeAddReferent (no ids)");
            return false;
        }

        if ($addedReferentId) {
            try {
                $added = $this->getPersonById($addedReferentId, true);
                if (in_array($added->getId(), $referentsIds)) {
                    $this->getLoggerService()->warning(sprintf("%s est déjà référent pour '%s'", $added, $subordinate));
                } else {
                    $this->getLoggerService()->info(sprintf("ajout de %s référent pour '%s'", $added, $subordinate));
                    $referentAdd = new Referent();
                    $referentAdd->setReferent($added)->setPerson($subordinate);
                    $this->getEntityManager()->persist($referentAdd);
                    $this->getEntityManager()->flush($referentAdd);
                }
            } catch (\Exception $e) {
                throw new OscarException(
                    sprintf("Impossible de charger la personne référente à ajouter '%s'", $addedReferentId)
                );
            }
        }

        if ($removedReferentId) {
            try {
                $removed = $this->getPersonById($removedReferentId, true);
                if (!in_array($removed->getId(), $referentsIds)) {
                    $this->getLoggerService()->warning(
                        sprintf("%s n'est déjà plus référent pour '%s'", $removed, $subordinate)
                    );
                } else {
                    $this->getLoggerService()->info(
                        sprintf("suppression de %s référent pour '%s'", $removed, $subordinate)
                    );
                    $this->getPersonRepository()->removeReferent($removed->getId(), $subordinate->getId());
                    $this->getEntityManager()->flush();
                }
            } catch (\Exception $e) {
                throw new OscarException(
                    sprintf(
                        "Impossible de supprimer la personne référente '%s' : ''",
                        $addedReferentId,
                        $e->getMessage()
                    )
                );
            }
        }

        // Validation en cours à mettre à jour
        $timesheetService = $this->getTimesheetService();

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotToValidateByPerson($subordinate, true);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            if ($added) {
                $validationPeriod->addValidatorAdm($added);
                $this->getLoggerService()->notice(
                    sprintf("$added est maintenant validateur administratif pour $validationPeriod")
                );
            }
            if ($removed) {
                $validationPeriod->addValidatorAdm($removed);
                $this->getLoggerService()->notice(
                    sprintf("$removed est n'est plus validateur administratif pour $validationPeriod")
                );
            }
        }

        if ($flush == true && count($validationPeriods) > 0) {
            $this->getEntityManager()->flush();
        }

        return true;
    }


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
        $this->removeAddReferent($person_id, $referent_id, null, true);
    }

    /**
     * @param Person $declarer
     * @param Person $referent
     * @param false $flush
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addReferentToDeclarationHorsLot(Person $declarer, Person $referent, $flush = false)
    {
        $timesheetService = $this->getTimesheetService();

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotToValidateByPerson($declarer);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $this->getLoggerService()->notice(
                sprintf("$referent est maintenant validateur administratif pour $validationPeriod")
            );
            $validationPeriod->addValidatorAdm($referent);
        }

        if ($flush == true && count($validationPeriods) > 0) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Person $declarer
     * @param Person $referent
     * @param false $flush
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function removeReferentToDeclarationHorsLot(Person $declarer, Person $referent, $flush = false)
    {
        $timesheetService = $this->getTimesheetService();

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotToValidateByPerson($declarer, true);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $this->getLoggerService()->notice(
                sprintf(
                    "$referent a été retiré de la validation administrative pour la validation hors-lot $validationPeriod"
                )
            );
            $validationPeriod->addValidatorAdm($referent);
        }

        if ($flush == true && count($validationPeriods) > 0) {
            $this->getEntityManager()->flush();
        }
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
            $this->getLoggerService()->notice(
                sprintf("$personNewReferent a remplacé $fromPerson pour " . $referent->getPerson())
            );
            $referent->setReferent($personNewReferent);
        }

        $timesheetService = $this->getTimesheetService();

        // Mise à jour des déclarations en attentes
        $validationPeriods = $timesheetService->getValidationHorsLotByReferent($fromPerson, true);

        /** @var ValidationPeriod $validationPeriod */
        foreach ($validationPeriods as $validationPeriod) {
            $this->getLoggerService()->notice(
                sprintf("$personNewReferent est maintenant validateur administratif pour $validationPeriod")
            );
            $validationPeriod->removeValidatorAdm($fromPerson);
            $validationPeriod->addValidatorAdm($personNewReferent);
        }
        $this->getEntityManager()->flush();
        return true;
    }

    /**
     * Ajout un référent à partir d'un autre référent.
     *
     * @param Person $personNewReferent
     * @param Person $fromPerson
     * @throws OscarException
     */
    public function refererentAddFromReferent(Person $personNewReferent, Person $fromPerson): void
    {
        try {
            // Liste des subordonnés / création des référents
            $subordinates = $this->getSubordinates($fromPerson);

            foreach ($subordinates as $subordinate) {
                $this->removeAddReferent($subordinate->getId(), $personNewReferent->getId(), null, false);
            }

            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            throw new OscarException(
                "Impossible d'ajouter le référent '$personNewReferent' à partir de '$fromPerson' : " . $e->getMessage()
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SUPPRESSION
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Supprime le référent.
     *
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
            $this->removeAddReferent($referent->getPerson()->getId(), null, $referent->getReferent()->getId());
            return true;
        } catch (\Exception $e) {
            throw new OscarException(
                sprintf(_('Impossible de supprimer le référent(%s) : %s', $referent_id, $e->getMessage()))
            );
        }
    }

    public function removeReferentOnPerson(Person $referent, Person $on)
    {
        $this->removeAddReferent($on->getId(), null, $referent->getId());
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
    public function mailPersonsWithUnreadNotification($dateRef = "", SymfonyStyle $io = null)
    {
        $logger = $this->getLoggerService();

        if ($io) {
            $log = function ($msg) use ($io, $logger) {
                $logger->info($msg);
                $io->writeln($msg);
            };
        } else {
            $log = function ($msg) use ($logger) {
                $logger->info($msg);
            };
        }

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

        $log("Notifications des inscrits à '$cron'");

        $authPersonNormalize = $this->getOscarConfigurationService()->getAuthPersonNormalize();

        // Liste des personnes ayant des notifications non-lues
        $persons = $this->getPersonRepository()->getPersonsWithUnreadNotificationsAndAuthentification(
            $authPersonNormalize
        );

        $log(sprintf(" %s personne(s) ont des notifications non-lues", count($persons)));

        /** @var Person $person */
        foreach ($persons as $person) {
            try {
                /** @var Authentification $auth */
                $auth = $this->getPersonAuthentification($person);
                $settings = $auth->getSettings();

                if (!$settings) {
                    $settings = [];
                }

                if (!array_key_exists('frequency', $settings)) {
                    $settings['frequency'] = [];
                }

                $settings['frequency'] = array_merge(
                    $settings['frequency'],
                    $this->getOscarConfigurationService()->getConfiguration(
                        'notifications.fixed'
                    )
                );

                $text = sprintf(
                    '%s %s (%s)',
                    strtoupper($person->getLastname()),
                    $person->getFirstname(),
                    $person->getEmail()
                );

                if (in_array($cron, $settings['frequency'])) {
                    $log(sprintf(" + >>> Envoi de mail pour %s", $text));
                    $this->mailNotificationsPerson($person);
                } else {
                    $log(sprintf(' - %s n\'est pas inscrite à ce crénaux', $text));
                }
            } catch (\Exception $e) {
                $this->getLoggerService()->error("Impossible de récupérer l'authentification d'un personne.");
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
                $link = $configOscar->getConfiguration("urlAbsolute") . $url(
                        'contract/show',
                        array('id' => $matches[2])
                    );
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
     * @param Activity $activity
     * @param bool $includeApp
     * @return array
     * @throws OscarException
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
                    $ldapFilters[] = preg_replace(
                        '/\(memberOf=(.*)\)/',
                        '$1',
                        $role->getLdapFilter()
                    );
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
                            $person = $this->getEntityManager()->getRepository(Person::class)->findOneBy(
                                ['ladapLogin' => $auth->getUsername()]
                            );
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


    /**
     * Charge en profondeur la liste des personnes sur une
     * activité (structure, projet). (Beaucoup de requêtes, attention aux perfs)
     * Retourne un tableau de tableaux id activité -> array ids roles -> arrayIdsPersons
     *
     * @param Activity $activity
     * @return array
     * @throws OscarException
     */
    public function getAllPersonsWithRolesInActivity(Activity $activity): array
    {
        //Résultat
        $persons = [];
        try {
            // Selection des personnes associées via le Projet/Activité
            /** @var ActivityPerson $p */
            foreach ($activity->getPersonsDeep() as $p) {
                $persons[$p->getRoleObj()->getId()] [] = $p->getPerson()->getId();
            }
            // Selection des personnes via l'organisation associée au Projet/Activité
            /** @var ActivityOrganization $organization */
            foreach ($activity->getOrganizationsDeep() as $organization) {
                if ($organization->isPrincipal()) {
                    /** @var OrganizationPerson $personOrganization */
                    foreach ($organization->getOrganization()->getPersons(false) as $personOrganization) {
                        $persons[$personOrganization->getRoleObj()->getId()] [] = $personOrganization->getPerson(
                        )->getId();
                    }
                }
            }
            return $persons;
        } catch (\Exception $e) {
            throw new OscarException("Impossible de trouver les personnes : " . $e->getMessage());
        }
    }

    /**
     * Retourne la liste des validateurs EFFECTIFS impliqués dans la validation des heures du déclarant pour la
     * période.
     *
     * @param int $delcarerId
     * @param string $periodCod
     * @return array
     */
    public function getValidatorsIdsPersonPeriod(int $declarerId, string $periodCode): array
    {
        $output = [];
        $validations = $this->getTimesheetService()->getValidationsPeriodPersonAt($declarerId, $periodCode);
        /** @var ValidationPeriod $validation */
        foreach ($validations as $validation) {
            /** @var Person $validator */
            foreach ($validation->getCurrentValidators() as $validator) {
                $output[$validator->getId()] = [
                    'id' => $validator->getId(),
                    'fullname' => $validator->getFullname(),
                    'email' => $validator->getEmail(),
                    'current' => "foo"
                ];
            }
        }
        return $output;
    }

    /**
     * Retourne la liste des déclarants impliqués dans des retards pour une période (périodes précédentes inclues).
     * Les données tiennent compte des retards de validation et propose des données de synthèse sur les retards.
     *
     * @param string $period (YYYY-MM)
     * @param Url $urlHelper Plugin pour générer les URL (optionnel)
     * @param bool $includeNonActive Inclus les données des activités autres que le status ACTIVE(101)
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getPersonsHighDelay(string $period, ?Url $urlHelper = null, bool $includeNonActive = false)
    {
        // Liste des déclarants
        $declarers = $this->getDeclarersIdsBeforePeriod($period, $includeNonActive);

        $output = [];

        foreach ($declarers as $personId => $infos) {
            /** @var Person $person */
            $person = $infos['person'];

            $personName = (string)$person;
            $personId = $person->getId();
            $personEmail = $person->getEmail();
            $personPeriods = $this->getTimesheetService()->getPeriodsPerson($person, true, $includeNonActive);
            $repport = $this->getHighDelayForPerson($personId, $includeNonActive);
            $validatorsPerson = [];
            $validatorsOthers = [];
            $personActivities = [];

            $validatorsOthersPerson = $this->getReferentsPerson($personId);

            /** @var Referent $v */
            foreach ($validatorsOthersPerson as $v) {
                $validatorsOthers[$v->getReferent()->getId()] = [
                    'fullname' => $v->getReferent()->getFullname(),
                    'email' => $v->getReferent()->getEmail()
                ];
            }

            $periods = [];

            $urlShow = false;
            if ($urlHelper) {
                $urlShow = $urlHelper->fromRoute('person/show', ['id' => $personId]);
            }

            $output[$personId] = [
                'person_id' => $personId,
                'fullname' => $personName,
                'emailmd5' => $person->getMd5Email(),
                'url_show' => $urlShow,
                'email' => $personEmail,
                'np1' => $validatorsOthers,
                'total_periods' => count($personPeriods),
                'total_declarations' => count($repport),
                'valid' => false,
                'send' => true,
                'notif' => true,
                'require_alert_declarer' => count($repport) < count($personPeriods),
                'require_alert_validator' => false,
                'periods' => [],
                'infos' => count($repport) == 0 ? 'Aucune déclaration' : 'Déclarations faite(s)'
            ];

            foreach ($personPeriods as $pp => $periodDetails) {
                if ($pp >= $period) {
                    continue;
                }

                $send = (array_key_exists($pp, $repport) && $repport[$pp]['send']);
                $valid = array_key_exists($pp, $repport) && $repport[$pp]['valid'] ? true : false;
                $step = array_key_exists($pp, $repport) ? $repport[$pp]['step'] : 0;
                $validators = [];

                foreach ($periodDetails['activities'] as $activityInfos) {
                    $activityId = $activityInfos['id'];

                    if (!array_key_exists('url_timesheet', $activityInfos) && $urlHelper) {
                        $activityInfos['url_timesheet'] = $urlHelper->fromRoute(
                            'contract/timesheet',
                            ['id' => $activityId]
                        );
                    }

                    if (!array_key_exists($activityId, $personActivities)) {
                        $personActivities[$activityId] = $activityInfos;
                        $activityValidators = [
                            'prj' => [],
                            'sci' => [],
                            'adm' => [],
                        ];

                        $activity = $this->getProjectGrantService()->getActivityById($activityId);

                        foreach ($activity->getValidatorsPrj() as $val) {
                            $activityValidators['prj'][$val->getId()] = [
                                'fullname' => (string)$val,
                                'email' => $val->getEmail()
                            ];
                        }
                        foreach ($activity->getValidatorsSci() as $val) {
                            $activityValidators['sci'][$val->getId()] = [
                                'fullname' => (string)$val,
                                'email' => $val->getEmail()
                            ];
                        }
                        foreach ($activity->getValidatorsAdm() as $val) {
                            $activityValidators['adm'][$val->getId()] = [
                                'fullname' => (string)$val,
                                'email' => $val->getEmail()
                            ];
                        }
                        $personActivities[$activityId]['validators'] = $activityValidators;
                    }
                }

                if ($send === false) {
                    $output[$personId]['send'] = false;
                } else {
                    $validators = $this->getValidatorsIdsPersonPeriod($personId, $pp);
                    foreach ($validators as $idValidator => $validatorFullName) {
                        $validatorsPerson[$idValidator] = $validatorFullName;
                    }
                }

                if ($send === false) {
                    $output[$personId]['send'] = false;
                }

                if ($send === true && $valid === false) {
                    $output[$personId]['require_alert_validator'] = true;
                }

                // état de la période
                $value = null;
                $periodInfos = [
                    'valid' => false,
                    'valid_prj' => false,
                    'valid_sci' => false,
                    'valid_adm' => false,
                    'send' => false,
                    'conflict' => false,
                    'step' => $step,
                    'validators' => $validators,
                    'activities' => $periodDetails['activities']
                ];
                if (array_key_exists($pp, $repport)) {
                    $periodInfos['valid'] = $repport[$pp]['valid'];
                    $periodInfos['valid_prj'] = $repport[$pp]['valid_prj'];
                    $periodInfos['valid_sci'] = $repport[$pp]['valid_sci'];
                    $periodInfos['valid_adm'] = $repport[$pp]['valid_adm'];
                    $periodInfos['send'] = $repport[$pp]['send'];
                    $periodInfos['conflict'] = $repport[$pp]['reject'];
                }

                if ($valid == false) {
                    $output[$personId]['infos'] = "Il y'a des déclarations non-terminée";
                    $output[$personId]['valid'] = false;
                }
                $periods[$pp] = $periodInfos;

                $output[$personId]['periods'] = $periods;
                $output[$personId]['activities'] = $personActivities;
            }

            $output[$personId]['periods'] = $periods;
            $output[$personId]['validators'] = $validatorsPerson;
        }

        return $output;
    }

    /**
     * Retourne le détails des informations sur les retards importants de déclaration pour une personne.
     *
     * @param int $personId
     * @param bool $includeNonActive (Inclus des activités inactives)
     * @return array
     */
    public function getHighDelayForPerson(int $personId, bool $includeNonActive = false)
    {
        $highdelays = $this->getPersonRepository()->getRepportDeclarationPerson($personId, $includeNonActive);
        $output = [];
        foreach ($highdelays as $highdelay) {
            $period = $highdelay['period'];
            $nbr = $highdelay['nbr'];
            $prj = $highdelay['prj'];
            $sci = $highdelay['sci'];
            $adm = $highdelay['adm'];

            $step = 0;
            if ($prj == $nbr) {
                $step = 1;
            }
            if ($sci == $nbr) {
                $step = 2;
            }
            if ($adm == $nbr) {
                $step = 3;
            }

            $valid = ($prj + $sci + $adm) == ($nbr * 3);
            $send = $nbr > 0;
            $rejprj = $highdelay['rejprj'];
            $rejsci = $highdelay['rejsci'];
            $rejadm = $highdelay['rejadm'];
            $reject = ($rejprj + $rejsci + $rejadm) > 0;

            // TODO Ajouter la détection des conflits
            $output[$highdelay['period']] = [
                'period' => $period,
                'valid' => $valid,
                'send' => $send,
                'reject' => $reject,
                'valid_prj' => $prj == $nbr,
                'valid_sci' => $sci == $nbr,
                'valid_adm' => $adm == $nbr,
                'step' => $step
            ];
        }
        return $output;
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
     * @param int $personActivityId
     * @param bool $throw
     * @return ActivityPerson|null
     * @throws OscarException
     */
    public function getPersonActivityById(int $personActivityId, $throw = true): ?ActivityPerson
    {
        $activityPerson = $this->getEntityManager()->getRepository(ActivityPerson::class)->find($personActivityId);
        if (!$activityPerson && $throw === true) {
            throw new OscarException(
                "Impossible de trouver l'affectation de la personnes à l'activité ($personActivityId)"
            );
        }
        return $activityPerson;
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

        return $repo->getAuthentificationPerson(
            $person,
            $this->getOscarConfigurationService()->getAuthPersonNormalize()
        );
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

        try {
            // Récupération des rôles via l'authentification
            $authentification = $this->getPersonAuthentification($person);

            foreach ($authentification->getRoles() as $role) {
                $inRoles[$role->getRoleId()] = $role;
            }

            if ($person->getLdapMemberOf()) {
                $roles = $this->getRoleRepository()->getRolesLdapFilter();

                /** @var Role $role */
                foreach ($roles as $role) {
                    // Le rôle est déjà présent "en dur"
                    if (array_key_exists($role->getRoleId(), $inRoles)) {
                        continue;
                    }

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
        } catch (\Exception $e) {
            $this->getLoggerService()->error(
                "Impossible de charger les rôles applicatif pour $person : " . $e->getMessage()
            );
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
            ->setParameters(
                [
                    'person' => $this->getCurrentPerson()
                ]
            )
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
        return new UnicaenDoctrinePaginator(
            $this->getBaseQuery(), $currentPage,
            $resultByPage
        );
    }

    public function searchPersonnel(
        $search = null,
        $currentPage = 1,
        $filters = [],
        $resultByPage = 50
    ) {
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

        return new UnicaenDoctrinePaginator(
            $query, $currentPage,
            $resultByPage
        );
    }

    /**
     * Retourne la liste des identifiants des personnes qui déclarent des feuilles de temps.
     *
     * @return int[]
     */
    public function getDeclarersIds(): array
    {
        return $this->getPersonRepository()->getIdsDeclarers();
    }

    /**
     * @param string $periodStr
     * @return int[]
     */
    public function getDeclarersIdsPeriod(string $periodStr): array
    {
        return $this->getPersonRepository()->getIdsDeclarers($periodStr, $periodStr);
    }


    /**
     * Liste des déclarants pour la période.
     *
     * @param string $periodStr
     * @return int[]
     */
    public function getDeclarersIdsBeforePeriod(string $periodStr, bool $includeNonActive = false): array
    {
        $ids = $this->getPersonRepository()->getIdsDeclarersBeforePeriod($periodStr, $includeNonActive);
        $output = [];
        foreach ($ids as $id) {
            $output[$id] = [
                'person' => $this->getPersonById($id),
                'periods' => []
            ];
        }
        return $output;
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
    ) {
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

//        if( preg_match('/id:([0-9]*)/m', $search, $matches) ){
//            $id = $matches[1];
//            $ids = [
//                $this->getPerson($id)->getId()
//            ];
//            if (array_key_exists('ids', $filters)) {
//                $filters['ids'] = array_intersect($filters['ids'], $ids);
//            } else {
//                $filters['ids'] = $ids;
//            }
//        }

        // RECHERCHE sur le connector
        // Ex: rest:p00000001
        if ($search != "") {
            // Recherche via les IDS
            if (preg_match_all(self::SEARCH_ID_PATTERN, $search, $matches, PREG_SET_ORDER, 0)) {
                //
            } // Recherche via le connecteur
            elseif (preg_match('/(([a-z]*):(\w*))/', $search, $matches)) {
                $connector = $matches[2];
                $connectorValue = $matches[3];
                try {
                    $query = $this->getEntityManager()->getRepository(Person::class)->getPersonByConnectorQuery(
                        $connector,
                        $connectorValue
                    );
                } catch (\Exception $e) {
                    $this->getLoggerService()->error("Requête sur le connecteur : " . $e->getMessage());
                    throw new OscarException("Impossible d'obtenir les personnes via l'UI de connector");
                }
            }

            try {
                $ids = $this->searchIds($search);


                if (array_key_exists('ids', $filters)) {
                    $filters['ids'] = array_intersect($filters['ids'], $ids);
                } else {
                    $filters['ids'] = $ids;
                }
            } catch (\Exception $e) {
                $this->getLoggerService()->error(
                    sprintf("Méthode de recherche des personnes non-disponible : %s", $e->getMessage())
                );
                throw new OscarException("Méthode de recherche des personnes non-disponible : %s", $e->getMessage());
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
                $this->getLoggerService()->error(
                    "Impossible de charger les personnes via les rôles des authentifications : " . $e->getMessage()
                );
                throw new OscarException("Erreur de chargement des rôles via l'authentification");
            }


            $filterLdap = [];

            // Création de la cause pour la selection des personnes niveau Application
            foreach ($roles->getQuery()->getResult() as $role) {
                if ($role->getLdapFilter()) {
                    $filterLdap[] = "ldapmemberof LIKE '%" . preg_replace(
                            '/\(memberOf=(.*)\)/',
                            '$1',
                            $role->getLdapFilter()
                        ) . "%'";
                }
            }

            if ($filterLdap) {
                // Récupération des IDPERSON avec les filtres LDAP
                $rsm = new Query\ResultSetMapping();
                $rsm->addScalarResult('person_id', 'person_id');
                $native = $this->getEntityManager()->createNativeQuery(
                    'select distinct id as person_id from person where ' . implode(
                        ' OR ',
                        $filterLdap
                    ),
                    $rsm
                );

                try {
                    foreach ($native->getResult() as $row) {
                        $ids[] = $row['person_id'];
                    }
                } catch (\Exception $e) {
                    $this->getLoggerService()->error(
                        "Impossible de charger les personnes via les filtres LDAP : " . $e->getMessage()
                    );
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

                foreach (
                    $native->setParameter(
                        'roles',
                        $filters['filter_roles']
                    )->getResult() as $row
                ) {
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
                    if ($i++ < $limit) {
                        $case .= sprintf('WHEN p.id = \'%s\' THEN %s ', $id, $i++);
                    }
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
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }

    /**
     * @param string $format
     * @return array
     */
    public function getAvailableRolesPersonActivity(string $format = OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT): array
    {
        return $this->getEntityManager()->getRepository(Role::class)->getRolesAtActivityArray($format);
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
            throw new OscarException(
                sprintf(_("La personne avec l'identifiant %s n'est pas présente dans la base de données."), $id)
            );
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
            throw new OscarException(
                sprintf(_("Le rôle avec l'identifiant %s n'est pas présente dans la base de données."), $id)
            );
        }
        return $role;
    }


    public function getPersonsPrincipalInActivityIncludeOrganization(Activity $activity)
    {
        $persons = [];

        /** @var ActivityPerson $activityperson */
        foreach ($activity->getPersonsDeep() as $activityperson) {
            if ($activityperson->isPrincipal() && !$activityperson->isOutOfDate()) {
                if (!in_array($activityperson->getPerson(), $persons)) {
                    $persons[] = $activityperson->getPerson();
                }
            }
        }

        /** @var ActivityOrganization $activityOrganization */
        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            if ($activityOrganization->isPrincipal() && !$activityOrganization->isOutOfDate()) {
                /** @var OrganizationPerson $organizationPerson */
                foreach ($activityOrganization->getOrganization()->getPersons() as $organizationPerson) {
                    if ($organizationPerson->isPrincipal() && !$organizationPerson->isOutOfDate() && !in_array(
                            $organizationPerson->getPerson(),
                            $persons
                        )) {
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

    public function getValidatorsIds()
    {
        return $this->getPersonRepository()->getIdsValidators();
    }

    public function getValidatorsIdsPeriod(string $period)
    {
        $periodInfo = PeriodInfos::getPeriodInfosObj($period);
        return $this->getPersonRepository()->getIdsValidators(true, $periodInfo->getPeriodCode());
    }

    /**
     * @return RecallExceptionRepository
     */
    public function getRecallExceptionRepository(): RecallExceptionRepository
    {
        return $this->getEntityManager()->getRepository(RecallException::class);
    }

    public function declarerCanReceiveTimesheetMail(Person $declarer): bool
    {
        $receive = true;
        if ($this->getOscarConfigurationService()->useDeclarersWhiteList()) {
            $receive = $this->getRecallExceptionRepository()->isInWhiteList($declarer->getId());
        }
        if ($receive == true) {
            $receive = !$this->getRecallExceptionRepository()->isInBlackList($declarer->getId());
        }
        return $receive;
    }


    /**
     * Liste des personnes dans la liste blanche
     *
     * @return RecallException[]
     */
    public function getDeclarersWhitelist()
    {
        return $this->getRecallExceptionRepository()->getWhitelist();
    }

    /**
     * @param Person[] $persons
     * @param Person $adder
     */
    public function addDeclarersToWhitelist(array $persons, Person $adder): void
    {
        /** @var RecallExceptionRepository $recallExceptions */
        $recallExceptions = $this->getEntityManager()->getRepository(RecallException::class);

        $included = $recallExceptions->getIncludedPersonsIds();
        foreach ($persons as $person) {
            if (!in_array($person->getId(), $included)) {
                $include = new RecallException();
                $include->setPerson($person)
                    ->setType(RecallException::TYPE_INCLUDED);
                $this->getEntityManager()->persist($include);
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Liste des personnes dans la liste noire
     *
     * @return RecallException[]
     */
    public function getDeclarersBlacklist()
    {
        return $this->getRecallExceptionRepository()->getBlacklist();
    }


    /**
     * Ajout d'une personne dans la liste noire.
     *
     * @param Person[] $persons
     * @param Person $adder
     */
    public function addDeclarersToBlacklist(array $persons, Person $adder): void
    {
        $excluded = $this->getRecallExceptionRepository()->getExcludedPersonsIds();
        foreach ($persons as $person) {
            if (!in_array($person->getId(), $excluded)) {
                $excluded = new RecallException();
                $excluded->setPerson($person)
                    ->setType(RecallException::TYPE_EXCLUDED);
                $this->getEntityManager()->persist($excluded);
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Suppression d'une personne dans la liste noire.
     *
     * @param Person[] $persons
     * @param Person $adder
     */
    public function removeDeclarersFromBlacklist(int $personId): void
    {
        $this->getRecallExceptionRepository()->removeDeclarerFromBlacklist($personId);
        $this->getEntityManager()->flush();
    }

    /**
     * Suppression d'une personne dans la liste noire.
     *
     * @param Person[] $persons
     * @param Person $adder
     */
    public function removeDeclarersFromWhitelist(int $personId): void
    {
        $this->getRecallExceptionRepository()->removeDeclarerFromWhitelist($personId);
        $this->getEntityManager()->flush();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AFFECTATIONS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Organization
    public function personOrganizationAdd(
        Organization $organization,
        Person $person,
        Role $role,
        $dateStart = null,
        $dateEnd = null
    ) {
        if (!$organization->hasPerson($person, $role)) {
            $message = sprintf(
                "a ajouté %s(%s) dans l'organisation %s",
                $person->log(),
                $role->getRoleId(),
                $organization->log()
            );
            $this->getLoggerService()->info($message);
            $op = new OrganizationPerson();
            $this->getEntityManager()->persist($op);

            $op->setPerson($person)
                ->setOrganization($organization)
                ->setRoleObj($role)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd);

            $this->getEntityManager()->flush($op);

            if ($role->isPrincipal()) {
                /** @var ActivityOrganization $oa */
                foreach ($organization->getActivities() as $oa) {
                    if ($oa->isPrincipal()) {
                        $this->getEntityManager()->refresh($oa->getActivity());
                        $this->getNotificationService()->jobUpdateNotificationsActivity($oa->getActivity());
                    }
                }
                foreach ($organization->getProjects() as $op) {
                    $this->getLoggerService()->info("Projet : " . $op->getProject());
                    if ($op->isPrincipal()) {
                        foreach ($op->getProject()->getActivities() as $a) {
                            $this->getNotificationService()->jobUpdateNotificationsActivity($a);
                        }
                    }
                }
            }

            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization($organization);
        }
    }


    /**
     * Suppression d'une personne d'une organization.
     *
     * @param OrganizationPerson $organizationPerson
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function personOrganizationRemove(OrganizationPerson $organizationPerson)
    {
        if ($organizationPerson->isPrincipal()) {
            /** @var OrganizationService $os */
            $os = $this->getOrganizationService();

            foreach (
                $os->getOrganizationActivititiesPrincipalActive(
                    $organizationPerson->getOrganization()
                ) as $activity
            ) {
                $this->getNotificationService()->jobUpdateNotificationsActivity($activity);
            }
        }
        $organization = $organizationPerson->getOrganization();
        $person = $organizationPerson->getPerson();

        $this->getEntityManager()->remove($organizationPerson);
        $this->getEntityManager()->flush();

        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization($organization);
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }


    /**
     * @param Person $person
     * @param false $pincipal
     * @param false $date
     * @return Organization[]
     */
    public function getPersonOrganizations(Person $person, $pincipal = false, $date = false)
    {
        return $this->getOrganizationRepository()->getOrganizationsPerson($person->getId(), $pincipal, $date);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// AFFECTATION AUX ACTIVITÉS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// PERSON <> ACTIVITY
    public function personActivityAdd(
        Activity $activity,
        Person $person,
        Role $role,
        ?\DateTime $dateStart = null,
        ?\DateTime $dateEnd = null
    ) {
        if (!$activity->hasPerson($person, $role, $dateStart, $dateEnd, false)) {
            $personActivity = new ActivityPerson();
            $this->getEntityManager()->persist($personActivity);
            $updateNotification = $role->isPrincipal();

            $personActivity->setPerson($person)
                ->setActivity($activity)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush();

            // LOG
            $this->getActivityLogService()->addUserInfo(
                sprintf("a ajouté %s(%s) dans l'activité %s ", $person->log(), $role->getRoleId(), $activity->log()),
                'Activity:person',
                $activity->getId()
            );

            if ($updateNotification) {
                $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
            }
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
        } else {
            $this->getLoggerService()->debug(
                sprintf(
                    "%s(%s) n'a pas été ajouté dans %s, car est déjà présent",
                    $person->log(),
                    $role->getRoleId(),
                    $activity->log()
                )
            );
        }
    }


    /**
     * @param ActivityPerson $activityPerson
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function personActivityRemove(ActivityPerson $activityPerson)
    {
        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();
        $roleId = $activityPerson->getRole();
        $updateNotification = $activityPerson->getRoleObj()->isPrincipal();
        $this->getEntityManager()->remove($activityPerson);
        $this->getEntityManager()->flush();

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $activity->log()),
            'Activity:person',
            $activity->getId()
        );

        if ($updateNotification) {
            $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
        }

        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }

    /**
     * Modification du rôle d'une personne dans une activité.
     *
     * @param ActivityPerson $activityPerson
     * @param Role $newRole
     * @param $dateStart
     * @param $dateEnd
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function personActivityChangeRole(ActivityPerson $activityPerson, Role $newRole, $dateStart, $dateEnd)
    {
        $person = $activityPerson->getPerson();
        $activity = $activityPerson->getActivity();

        $updateNotification = $activityPerson->getRoleObj()->isPrincipal() != $newRole->isPrincipal();
        $activityPerson->setRoleObj($newRole);
        $activityPerson->setDateStart($dateStart)->setDateEnd($dateEnd);
        $this->getEntityManager()->flush($activityPerson);
        $this->getLoggerService()->info(
            sprintf("Le rôle de personne %s a été modifié dans l'activité %s", $person, $activity)
        );

        // Si le rôle est principal, on actualise les notifications de la personne
        if ($updateNotification) {
            $this->getNotificationService()->jobUpdateNotificationsActivity($activity);
        }
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }

    /**
     * Ajout d'une personne à un projet.
     *
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
            $updateNotification = $role->isPrincipal();

            $personProject->setPerson($person)
                ->setProject($project)
                ->setDateStart($dateStart)
                ->setDateEnd($dateEnd)
                ->setRoleObj($role);

            $this->getEntityManager()->flush($personProject);

            foreach ($project->getActivities() as $activity) {
                if ($updateNotification) {
                    $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
                }
                $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
            }
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
        }
    }

    /**
     * @param ProjectMember $projectPerson
     * @throws \Doctrine\ORM\ORMException
     */
    public function personProjectRemove(ProjectMember $projectPerson)
    {
        $person = $projectPerson->getPerson();
        $project = $projectPerson->getProject();
        $updateNotification = $projectPerson->getRoleObj()->isPrincipal();

        $roleId = $projectPerson->getRole();

        $this->getEntityManager()->remove($projectPerson);

        // LOG
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé %s(%s) dans l'activité %s ", $person->log(), $roleId, $project->log()),
            'Project:person',
            $project->getId()
        );

        foreach ($project->getActivities() as $activity) {
            if ($updateNotification) {
                $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
            }
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
        }
        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }

    // PROJECT
    public function personProjectChangeRole(
        ProjectMember $personProject,
        Role $newRole,
        $dateStart = null,
        $dateEnd = null
    ) {
//        if ($newRole == $personProject->getRoleObj()) {
//            return;
//        }

        $person = $personProject->getPerson();
        $project = $personProject->getProject();

        $updateNotification = $personProject->getRoleObj()->isPrincipal() != $newRole->isPrincipal();
        $personProject->setRoleObj($newRole)
            ->setDateStart($dateStart)
            ->setDateEnd($dateEnd);
        $project->touch();

        $this->getEntityManager()->flush();

        $this->getLoggerService()->info(
            sprintf("Le rôle de personne %s a été modifié dans le projet %s", $person, $project)
        );

        foreach ($project->getActivities() as $activity) {
            if ($updateNotification) {
                $this->getGearmanJobLauncherService()->triggerUpdateNotificationActivity($activity);
            }
            $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);
        }

        $this->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SERVICES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return OrganizationService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getOrganizationService(): OrganizationService
    {
        return $this->getServiceContainer()->get(OrganizationService::class);
    }

    /**
     * @return MailingService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getMailingService(): MailingService
    {
        return $this->getServiceContainer()->get(MailingService::class);
    }

    /**
     * @return ProjectGrantService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getProjectGrantService(): ProjectGrantService
    {
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }

    /**
     * @return TimesheetService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTimesheetService(): TimesheetService
    {
        return $this->getServiceContainer()->get(TimesheetService::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// REPOSITORY
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return PersonRepository
     */
    public function getPersonRepository(): PersonRepository
    {
        return $this->getEntityManager()->getRepository(Person::class);
    }

    /**
     * @return AuthentificationRepository
     */
    public function getAuthentificationRepository(): AuthentificationRepository
    {
        return $this->getEntityManager()->getRepository(Authentification::class);
    }

    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository()
    {
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    /**
     * @return RoleRepository
     */
    public function getRoleRepository(): RoleRepository
    {
        return $this->getEntityManager()->getRepository(Role::class);
    }


    /**
     * @return OscarUserContext
     */
    public function getOscarUserContext()
    {
        return $this->getOscarUserContextService();
    }
}
