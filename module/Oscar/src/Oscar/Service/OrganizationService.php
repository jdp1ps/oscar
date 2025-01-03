<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 10:20
 *
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Country3166;
use Oscar\Entity\Country3166Repository;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\OrganizationRoleRepository;
use Oscar\Entity\OrganizationType;
use Oscar\Entity\OrganizationTypeRepository;
use Oscar\Entity\Person;
use Oscar\Entity\ProjectPartner;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Formatter\OscarFormatterConst;
use Oscar\Formatter\OscarFormatterFactory;
use Oscar\Import\Organization\ImportOrganizationLdapStrategy;
use Oscar\Strategy\Search\IOrganizationSearchStrategy;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\Structure;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Organisations :
 *  - Partenaires
 *  - laboratoires
 *  - Composantes responsables.
 *
 * Class OrganizationService
 */
class OrganizationService implements UseOscarConfigurationService, UseEntityManager, UseOscarUserContextService,
                                     UseLoggerService
{

    use UseOscarConfigurationServiceTrait,
        UseEntityManagerTrait,
        UseLoggerServiceTrait,
        UseOscarUserContextServiceTrait;

    /** @var PersonService */
    private $personService;

    /**
     * @return PersonService
     */
    public function getPersonService(): PersonService
    {
        return $this->personService;
    }

    /**
     * @param PersonService $personService
     */
    public function setPersonService(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    public function getTypesIdsByLabel(?array $labels): array
    {
        $types = [];
        $result = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy(
            ['root' => null],
            ['label' => 'DESC']
        );

        /** @var OrganizationType $type */
        foreach ($result as $type) {
            if (in_array($type->getLabel(), $labels)) {
                $types[] = $type->getId();
            }
        }
        return $types;
    }


    private $cacheCountries = null;
    private $cacheConnectors = null;

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext(): OscarUserContext
    {
        return $this->getOscarUserContextService();
    }


    public function getOrganizationRoleRepository(): OrganizationRoleRepository
    {
        return $this->getEntityManager()->getRepository(OrganizationRole::class);
    }

    public function getOrganizationRepository(): OrganizationRepository
    {
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getOrganizationWithRnsr(): array
    {
        return $this->getOrganizationRepository()->getOrganizationsWithRnsr();
    }

    /**
     * Retourne la liste des Roles disponible pour une organisation dans une activité.
     */
    public function getAvailableRolesOrganisationActivity(string $format = OscarFormatterConst::FORMAT_ARRAY_ID_OBJECT
    ): array {
        return OscarFormatterFactory::getFormatter($format)->format($this->getOrganizationRoleRepository()->findAll());
    }

    /**
     * Retourne le OrganizationRole via l'ID
     *
     * @param int $organizationRoleId
     * @param bool $throw
     * @return OrganizationRole
     * @throws OscarException
     */
    public function getRoleOrganizationById(int $organizationRoleId, $throw = true): OrganizationRole
    {
        /** @var OrganizationRoleRepository $repo */
        $repo = $this->getOrganizationRoleRepository();

        $role = $repo->find($organizationRoleId);

        if ($role === null && $throw === true) {
            throw new OscarException("Impossible de charger le rôle d'organisation '$organizationRoleId'");
        }

        return $role;
    }

    /**
     * Retourne l'ActivityOrganization via l'ID.
     *
     * @param int $activityOrganizationId
     * @param bool $throw
     * @return ActivityOrganization
     * @throws OscarException
     */
    public function getActivityOrganization(int $activityOrganizationId, $throw = true): ActivityOrganization
    {
        $repo = $this->getEntityManager()->getRepository(ActivityOrganization::class);

        $activityOrganization = $repo->find($activityOrganizationId);

        if ($activityOrganization === null && $throw === true) {
            throw new OscarException("Impossible de charger le rôle d'organisation '$activityOrganizationId'");
        }

        return $activityOrganization;
    }

    /**
     * Retourne la liste des ID (Id de l'organisation et de ces sous-structures).
     *
     * @param $idParentOrganization
     * @return array
     */
    public function getOrganizationIdsDeep(int $idParentOrganization, bool $ignoreFirst = false): array
    {
        $ids = $this->getDescentsIdsDeep($idParentOrganization);
        if ($ids[0] == $idParentOrganization) {
            $ids = array_slice($ids, 1);
        }
        return $ids;
    }


    public function getAncestors(int $idOrganization): array
    {
        $ancestors = [];
        $ids = $this->getAncestorsIdsDeep($idOrganization);
        foreach ($ids as $id) {
            if ($id == $idOrganization) {
                continue;
            }
            $ancestors[] = $this->getOrganization($id);
        }
        return array_reverse($ancestors);
    }

    public function getAncestorsIds(int $organizationId): array
    {
        $out = [];
        return $this->getAncestorsIdsDeep($organizationId, $out);
    }

    private function getAncestorsIdsDeep(int $organizationId, &$output = []): array
    {
        $organization = $this->getOrganization($organizationId);
        $output[] = $organization->getId();
        if ($organization->getParent()) {
            $output = $this->getAncestorsIdsDeep($organization->getParent()->getId(), $output);
        }
        return $output;
    }

    public function getDescentsIds(int $fromParent, bool $ignoreFirst = false): array
    {
        $descents = $this->getDescentsIdsDeep($fromParent);
        if ($ignoreFirst == true && $descents[0] == $fromParent) {
            $descents = array_slice($descents, 1);
        }
        return $descents;
    }

    private function getDescentsIdsDeep(int $fromParent, &$output = []): array
    {
        $organization = $this->getOrganization($fromParent);
        $output[] = $organization->getId();
        if ($organization->getChildren()) {
            foreach ($organization->getChildren() as $organization) {
                $output = $this->getDescentsIdsDeep($organization->getId(), $output);
            }
        }
        return $output;
    }

    public function getPersonAffectationDetails(?Person $person, bool $principal = true): array
    {
        $output = [];
        if ($person) {
            /** @var OrganizationPerson $personOrganization */
            foreach ($person->getOrganizations() as $personOrganization) {
                if ($principal === true && !$personOrganization->isPrincipal()) {
                    continue;
                }
                $this->buildPersonAffectationDetailsDeep(
                    $personOrganization->getRoleObj(),
                    $personOrganization->getOrganization(),
                    $output
                );
            }
        }
        return $output;
    }

    private function buildPersonAffectationDetailsDeep(Role $roleToAdd, Organization $organization, &$output): void
    {
        if (!array_key_exists($organization->getId(), $output)) {
            $output[$organization->getId()] = [
                'organization'  => $organization,
                'roles_display' => [],
                'roles'         => []
            ];
        }
        $output[$organization->getId()]['roles'][] = $roleToAdd;
        if (!in_array($roleToAdd->getRoleId(), $output[$organization->getId()]['roles_display'])) {
            $output[$organization->getId()]['roles_display'][] = $roleToAdd->getRoleId();
        }

        if ($organization->getChildren()) {
            foreach ($organization->getChildren() as $subOrganization) {
                $this->buildPersonAffectationDetailsDeep($roleToAdd, $subOrganization, $output);
            }
        }
    }

    /**
     * @param $id
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @deprecated
     */
    public function deleteOrganization($id)
    {
        $o = $this->getOrganization($id);
        $this->getEntityManager()->remove($o);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne la liste des Organizations pour la personne
     *
     * @param Person $person
     * @param null $specifiqueRoleIds
     * @param bool $rolePrincipaux
     * @return Organization[]
     */
    public function getOrganizationsWithPersonRolled(Person $person, $specifiqueRoleIds = null, $rolePrincipaux = false)
    {
        // Filtrer les rôles

        if ($rolePrincipaux == true) {
            $roles = $this->getOscarUserContext()->getRoleIdPrimary();
        }
        else {
            $roles = $this->getOscarUserContext()->getRoleId();
        }

        if ($specifiqueRoleIds) {
            $roles = array_intersect($roles, $specifiqueRoleIds);
        }

        $structures = $this->getEntityManager()->getRepository(Organization::class)->createQueryBuilder('o')
            ->innerJoin('o.persons', 'p')
            ->innerJoin('p.roleObj', 'r')
            ->where('p.person = :person AND r.roleId IN(:roles)')
            ->setParameters(
                [
                    'person' => $person,
                    'roles'  => $roles,
                ]
            )
            ->getQuery()
            ->getResult();

        return $structures;
    }

    public function getConnectorsList()
    {
        if ($this->cacheConnectors == null) {
            $this->cacheConnectors = [];
            // todo Utiliser le service qui gère l'accès à la configuration
            $config = $this->getOscarConfigurationService();

            $connectors = $config->getConfiguration('connectors.organization');

            $this->cacheConnectors = array_keys($connectors);
        }
        return $this->cacheConnectors;
    }

    public function getCountriesList()
    {
        if ($this->cacheCountries == null) {
            $this->cacheCountries = [];
            $countries = $this->getEntityManager()->createQueryBuilder()
                ->select('o.country')
                ->from(Organization::class, 'o')
                ->distinct('o.country')
                ->where('o.country IS NOT NULL AND o.country != \'\'')
                ->getQuery()
                ->getScalarResult();
            foreach ($countries as $r) {
                $this->cacheCountries[] = $r['country'];
            }
        }
        return $this->cacheCountries;
    }

    public function getActivities($organizationId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('a, p, o, o, pe, m')
            ->from(Activity::class, 'a')
            ->innerJoin('a.organizations', 'p')
            ->innerJoin('p.organization', 'o')
            ->innerJoin('a.persons', 'm')
            ->innerJoin('m.person', 'pe')
            ->where('o.id = :id')
            ->orderBy('a.dateCreated', 'DESC')
            ->setParameter('id', $organizationId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des activités d'un organisation où elle a un role principal actif.
     *
     * @param Organization $o
     * @return array
     */
    public function getOrganizationActivititiesPrincipalActive(Organization $o)
    {
        $activities = [];
        /** @var ActivityOrganization $activity */
        foreach ($o->getActivities() as $activity) {
            if ($activity->isPrincipal() && !$activity->isOutOfDate()) {
                if (!in_array($activity->getActivity(), $activities)) {
                    $activities[] = $activity->getActivity();
                }
            }
        }

        /** @var ProjectPartner $p */
        foreach ($o->getProjects() as $p) {
            if ($p->isPrincipal() && !$p->isOutOfDate()) {
                foreach ($p->getProject()->getActivities() as $activity) {
                    if (!in_array($activity, $activities)) {
                        $activities[] = $activity;
                    }
                }
            }
        }

        return $activities;
    }

    /**
     * @param int $idOrganization
     * @return Organization[]
     */
    public function getOrganizationAndParents(int $idOrganization): array
    {
        return $this->getOrganizationRepository()->getOrganizationAndParents($idOrganization);
    }

    public function getSubStructure(int $organizationId): array
    {
        $structures = [];
        $subStructures = $this->getOrganizationRepository()->getSubOrganizations($organizationId);
        foreach ($subStructures as $organization) {
            $infos = $organization->toArray();
            $infos['show'] = "";
            $infos['persons'] = [];
            $infos['organizations'] = [];

            // personnels
            /** @var OrganizationPerson $p */
            foreach ($organization->getPersons() as $p) {
                if (!array_key_exists($p->getPerson()->getId(), $infos['persons'])) {
                    $infos['persons'][$p->getPerson()->getId()] = [
                        'label' => $p->getPerson()->getFullname(),
                        'roles' => []
                    ];
                }
                $infos['persons'][$p->getPerson()->getId()]['roles'][] = $p->getRoleObj()->getRoleId();
            }

            /** @var Organization $o */
            foreach ($organization->getChildren() as $o) {
                if (!array_key_exists($o->getId(), $infos['organizations'])) {
                    $infos['organizations'][$o->getId()] = $o->toArray();
                }
            }

            $structures[] = $infos;
        }
        return $structures;
    }

    /**
     * @param int $masterOrganizationId
     * @param int $subOrganizationId
     * @return void
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveSubStructure(int $masterOrganizationId, int $subOrganizationId): void
    {
        $subStructure = $this->getOrganizationRepository()->getOrganisationById($subOrganizationId);
        $parent = $this->getOrganizationRepository()->getOrganisationById($masterOrganizationId);
        $this->getLoggerService()->info(sprintf('Ajout de %s dans %s', $subStructure->log(), $parent->log()));

        $parentChildren = $this->getAncestorsIdsDeep($masterOrganizationId);

//        $idsInAffected = $this->getDescentsIds($subStructure->getId(), true);

        if (!in_array($subStructure->getId(), $parentChildren) && $masterOrganizationId != $subOrganizationId) {
            $subStructure->setParent($parent);
            $this->getEntityManager()->flush($subStructure);
        }
        else {
            throw new OscarException("L'affectation va provoquer une récurrence, opération annulée");
        }
    }

    //removeSubStructure
    public function removeSubStructure(?int $masterOrganizationId, int $subOrganizationId): void
    {
        $subStructure = $this->getOrganizationRepository()->getOrganisationById($subOrganizationId);
        $subStructure->setParent(null);
        $this->getEntityManager()->flush($subStructure);
    }

    /**
     * @param Person $person
     * @return int[]
     */
    public function getIdsForPerson(Person $person): array
    {
        $idsOrganization = [];
        /** @var OrganizationPerson $organizationPerson */
        foreach ($person->getOrganizations() as $organizationPerson) {
            $idsOrganization = $organizationPerson->getOrganization()->getChildrenDeepWithSelf($idsOrganization, true);
        }
        return $idsOrganization;
    }

    public function exportCsv($organizations): void
    {
        // Fichier temporaire
        $tmpDir = "/tmp";
        $filename = uniqid('oscar_export_organization_') . '.csv';
        $pathFileTmp = $tmpDir . '/' . $filename;
        $handler = fopen($pathFileTmp, 'w');

        if (!$handler) {
            $this->getLoggerService()->error(
                "Export organisation : L'emplacement $filename n'est pas accessible en écriture."
            );
            throw new OscarException("Impossible d'exporter les données - Accès en écriture impossible");
        }

        $headers = [
            'ID',
            'NomCourt',
            'NomLong',
            'Code',
            'Email',
            'URL',
            'rue1',
            'rue2',
            'rue3',
            'CP',
            'BP',
            'ville',
            'Pays',
            'CodePays',
            'Téléphone',
            'SIFAC',
            'SIRET',
            'Type',
            'TVA'
        ];

        fputcsv($handler, $headers);


        $i = 0;
        /** @var Organization $organization */
        foreach ($organizations as $organization) {
            $datas = [
                'ID'        => $organization->getId(),
                'NomCourt'  => $organization->getShortName(),
                'NomLong'   => $organization->getFullName(),
                'Code'      => $organization->getCode(),
                'Email'     => $organization->getEmail(),
                'URL'       => $organization->getUrl(),
                'rue1'      => $organization->getStreet1(),
                'rue2'      => $organization->getStreet2(),
                'rue3'      => $organization->getStreet3(),
                'CP'        => $organization->getZipCode(),
                'BP'        => $organization->getBp(),
                'ville'     => $organization->getCity(),
                'Pays'      => $organization->getCountry(),
                'CodePays'  => $organization->getCodePays(),
                'Téléphone' => $organization->getPhone(),
                'SIFAC'     => $organization->getSifacId(),
                'SIRET'     => $organization->getSiret(),
                'Type'      => $organization->getType(),
                'TVA'       => $organization->getNumTVACA(),
            ];
//            $activities = [];
//            /** @var ActivityOrganization $activity */
//            foreach ($organization->getActivities() as $activity) {
//                $activities[] = $activity->getActivity()->getOscarNum();
//            }
//            /** @var ProjectPartner $project */
//            foreach ($organization->getProjects() as $project) {
//                /** @var Activity $activity */
//                foreach ($project->getProject()->getActivities() as $activity) {
//                    $activities[] = $activity->getOscarNum();
//                }
//            }
//            $datas['activities'] = implode(', ', $activities);

            fputcsv($handler, $datas);
        }

        fclose($handler);

        header('Content-Disposition: attachment; filename=oscar-export-organisations.csv');
        header('Content-Length: ' . filesize($pathFileTmp));
        header('Content-type: plain/text');

        die(file_get_contents($pathFileTmp));
    }


    public function getOrganizationTypes()
    {
        $types = [];
        $result = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy(
            ['root' => null],
            ['label' => 'DESC']
        );

        /** @var OrganizationType $type */
        foreach ($result as $type) {
            $types[$type->getId()] = $type->toJson();
        }

        return $types;
    }

    public function getTypes()
    {
        $types = Organization::getTypes();

        $query = $this->getEntityManager()->createQueryBuilder('o')
            ->select('o.type')
            ->from(Organization::class, 'o')
            ->distinct()
            ->getQuery();

        foreach ($query->getResult(Query::HYDRATE_ARRAY) as $type) {
            $t = $type['type'];
            if (!in_array($t, $types) && $t != null) {
                $types[] = $t;
            }
        }
        sort($types);
        return $types;
    }

    public function getOrganizationTypesObject()
    {
        $options = [];

        $types = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy([], ['label' => 'ASC']);
        foreach ($types as $type) {
            $options[$type->getId()] = $type;
        }
        return $options;
    }

    public function getOrganizationTypesSelect()
    {
        $options = [];

        $types = $this->getEntityManager()->getRepository(OrganizationType::class)->findBy([], ['label' => 'ASC']);
        foreach ($types as $type) {
            $options[$type->getId()] = (string)$type;
        }
        return $options;
    }

    public function searchUpdate(Organization $organization): void
    {
        $this->updateIndex($organization);
    }

    public function updateIndex(Organization $organization): void
    {
        $this->getSearchEngineStrategy()->update($organization);
    }

    /**
     * @return IOrganizationSearchStrategy
     */
    public function getSearchEngineStrategy()
    {
        static $searchStrategy;
        if ($searchStrategy === null) {
            try {
                $opt = $this->getOscarConfigurationService()->getConfiguration('strategy.organization.search_engine');
                $class = new \ReflectionClass($opt['class']);
                $params = $opt['params'];
                $params[] = $this->getLoggerService();
                $searchStrategy = $class->newInstanceArgs($params);
            } catch (\Throwable $e) {
                $this->getLoggerService()->critical($e->getMessage());
                throw new OscarException("Impossible d'instancier l'accès au moteur de recherche");
            }
        }
        return $searchStrategy;
    }

    public function searchIndexRebuild()
    {
        return $this->getSearchEngineStrategy()->rebuildIndex($this->getOrganizations());
    }

    /**
     * Retourne le résultat de la recherche $search.
     *
     * @param string $search
     *
     * @return UnicaenDoctrinePaginator
     */
    public function getOrganizationsSearchPaged(string $search, int $page, array $filter = []): UnicaenDoctrinePaginator
    {
        $qb = $this->getSearchQuery($search, $filter);

        //die($qb->getDQL());
        return new UnicaenDoctrinePaginator($qb, $page);
    }

    /**
     * @param $search
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     * @throws OscarException
     */
    protected function getSearchNativeQuery($search, $filter)
    {
        $qb = $this->getBaseQuery();
        if ($search) {
            // Recherche sur le connector
            $reg = preg_match('/([a-z]*)=(.*)/', $search, $matches);
            if ($reg) {
                $connectors = $this->getConnectorsList();
                $connectorName = $matches[1];
                if (!in_array($connectorName, $connectors)) {
                    throw new OscarException("Le connecteur $connectorName n'existe pas.");
                }
                $connectorValue = $matches[2] . '%';
                $where = 'o.connectors LIKE \'%"' . $connectorName . '";s:%:"' . $connectorValue . '"%\'';
                $qb->orWhere($where);
            }
            else {
                $qb
                    ->orWhere('LOWER(o.shortName) LIKE :search')
                    ->orWhere('LOWER(o.fullName) LIKE :search')
                    ->orWhere('LOWER(o.city) LIKE :search')
                    ->orWhere('o.zipCode = :searchStrict')
                    ->orWhere('LOWER(o.code) LIKE :search');

                if (strlen($search) == 14) {
                    $qb->orWhere('o.siret = :searchStrict');
                }


                $qb->setParameters(
                    [
                        'search'       => '%' . strtolower($search) . '%',
                        'searchStrict' => strtolower($search),
                    ]
                );
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // FILTRE sur les types d'organisations
        if (isset($filter['type']) && $filter['type']) {
            // On purge les types vides (option "Tous")
            $cleanTypes = [];

            foreach ($filter['type'] as $typeValue) {
                if ($typeValue) {
                    $cleanTypes[] = $typeValue;
                }
            }

            if (count($cleanTypes) > 0) {
                $types = $this->getEntityManager()->getRepository(OrganizationType::class)->createQueryBuilder('t')
                    ->where('t.id IN (:types)')
                    ->setParameter('types', $cleanTypes)
                    ->getQuery()
                    ->getResult();

                $qb->leftJoin('o.typeObj', 't')
                    ->andWhere('t.id IN(:type)')
                    ->setParameter('type', $types);
            }
        }

        if (isset($filter['active']) && $filter['active']) {
            if ($filter['active'] == 'ON') {
                $qb->andWhere('o.dateEnd IS NULL OR o.dateEnd > :now')->setParameter('now', new \DateTime());
            }
            else {
                if ($filter['active'] == 'OFF') {
                    $qb->andWhere('o.dateEnd < :now')->setParameter('now', new \DateTime());
                }
            }
        }

        if (isset($filter['roles']) && count($filter['roles'])) {
            $ids = [];
            $roles = $this->getEntityManager()->getRepository(ProjectPartner::class)->createQueryBuilder('r')
                ->where('r.role IN (:roles)')
                ->setParameter('roles', $filter['roles'])->getQuery()->getResult();
            /** @var ProjectPartner $role */
            foreach ($roles as $role) {
                if (!in_array($role->getOrganization()->getId(), $ids)) {
                    $ids[] = $role->getOrganization()->getId();
                }
            }

            $qb->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        return $qb;
    }

    public function getSearchQuery($search, $filter)
    {
        // Path 2024/12/09
        if( !array_key_exists('sort', $filter )){
            $filter['sort'] = 'hit';
        }

        $qb = $this->getBaseQuery();

        if ($search != "") {
            $ids = $this->search($search, true);


            // ORDER BY de LREM
            // Permet de forcer le trie dans l'ordre des IDs fournit par Elastic Search
            if (count($ids) > 1 ) {
                if( $filter['sort'] == 'hit' ){
                    $sortSize = 25; // On ne trie que les 25 premiers
                    $this->getLoggerService()->debug("SORT BY HIT (elastic IDS)");

                    $selectHidden = "(CASE ";
                    $i = 0;
                    foreach ($ids as $id) {
                        $selectHidden .= " WHEN o.id = $id THEN $i ";
                        $i++;
                        if ($i > $sortSize) {
                            break;
                        }
                    }
                    $selectHidden .= " ELSE $i END) AS HIDDEN ORD";
                    $qb->addSelect($selectHidden);
                    $qb->orderBy('ORD', 'ASC');
                }
                $qb->where('o.id IN(:ids)')->setParameter('ids', $ids);
            }
        }

        if( $filter['sort'] != 'hit' ){
            $field = $filter['sort'];
            $direction = $filter['direction'];
            $this->getLoggerService()->debug("TRIE $field/$direction");
            $qb->addSelect("CASE WHEN o.$field IS NULL THEN 0 ELSE 1 END as HIDDEN null_value");
            $qb->addOrderBy('null_value', 'DESC');
            if( $field != 'dateUpdated' && $field != 'dateEnd' && $field != 'dateCreated'){
                $qb->addSelect("CASE WHEN o.$field =  '' THEN 0 ELSE 1 END as HIDDEN zero_value");
                $qb->addOrderBy('zero_value', 'DESC');
            }

            switch ($filter['sort']) {
                case 'shortName':
                    $qb->addOrderBy('o.shortName', $direction);
                    break;
                case 'fullName':
                    $qb->addOrderBy('o.fullName', $direction);
                    break;
                case 'code':
                    $qb->addOrderBy('o.code', $direction);
                    break;
                case 'dateUpdated':
                    $qb->addOrderBy('o.dateUpdated', $direction);
                    break;
                case 'dateEnd':
                    $qb->addOrderBy('o.dateEnd', $direction);
                    break;
                case 'dateCreated':
                    $qb->addOrderBy('o.dateCreated', $direction);
                    break;
            }


        }
//        else {
//            $qb->addOrderBy('o.dateEnd', 'DESC')->addOrderBy('o.dateUpdated', 'DESC');
//        }

        //

        // -------------------------------------------------------------------------------------------------------------
        // FILTRE sur les types d'organisations
        if (isset($filter['type']) && $filter['type']) {
            // On purge les types vides (option "Tous")
            $cleanTypes = [];

            foreach ($filter['type'] as $typeValue) {
                if ($typeValue) {
                    $cleanTypes[] = $typeValue;
                }
            }

            if (count($cleanTypes) > 0) {
                $types = $this->getEntityManager()->getRepository(OrganizationType::class)->createQueryBuilder('t')
                    ->where('t.id IN (:types)')
                    ->setParameter('types', $cleanTypes)
                    ->getQuery()
                    ->getResult();

                $qb->leftJoin('o.typeObj', 't')
                    ->andWhere('t.id IN(:type)')
                    ->setParameter('type', $types);
            }
        }

        if (isset($filter['active']) && $filter['active']) {
            if ($filter['active'] == 'ON') {
                $qb->andWhere('o.dateEnd IS NULL OR o.dateEnd > :now')->setParameter('now', new \DateTime());
            }
            else {
                if ($filter['active'] == 'OFF') {
                    $qb->andWhere('o.dateEnd < :now')->setParameter('now', new \DateTime());
                }
            }
        }

        if (isset($filter['roles']) && count($filter['roles'])) {
            $ids = [];
            $roles = $this->getEntityManager()->getRepository(ProjectPartner::class)->createQueryBuilder('r')
                ->where('r.role IN (:roles)')
                ->setParameter('roles', $filter['roles'])->getQuery()->getResult();
            /** @var ProjectPartner $role */
            foreach ($roles as $role) {
                if (!in_array($role->getOrganization()->getId(), $ids)) {
                    $ids[] = $role->getOrganization()->getId();
                }
            }

            $qb->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids);
        }
        //die($qb->getDQL());

        return $qb;
    }

    /**
     * @param $id
     *
     * @return Organization
     */
    public function getOrganization($id)
    {
        try {
            return $this->getBaseQuery()->andWhere('o.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            throw new OscarException("Impossible de trouver l'organisation '$id'.");
        }
    }

    /**
     * @return Organization[]
     */
    public function getOrganizations()
    {
        return $this->getBaseQuery()->getQuery()->getResult();
    }

    public function getOrganizationsByIds(array $ids)
    {
        $query = $this->getBaseQuery()->where('o.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery();
        return $query->getResult();
    }

    public function search($search, $justIds = false)
    {
        $strategy = $this->getSearchEngineStrategy();
        if ($strategy) {
            $ids = $strategy->search($search);
            if ($justIds == true) {
                return $ids;
            }

            return $this->getOrganizationsByIds($ids);
        }
        else {
            return $this->getSearchNativeQuery($search, [])->getQuery()->getResult();
        }
    }


    public function closeOrganizationPerson(OrganizationPerson $organizationPerson, \DateTime $dateEnd): void
    {
        $person = $organizationPerson->getPerson();
        $organization = $organizationPerson->getOrganization();
        $role = $organizationPerson->getRoleObj();

        $msg = sprintf(
            "Résiliation du rôle '%s' de '%s' dans l'organisation '%s'",
            $role->getRoleId(),
            $person,
            $organization
        );
        $this->getLoggerService()->notice($msg);

        try {
            $updateNotification = $role->isPrincipal();
            $organizationPerson->setDateEnd($dateEnd);
            $this->getEntityManager()->flush();
            if ($updateNotification) {
                $this->getPersonService()->getGearmanJobLauncherService()->triggerUpdateNotificationOrganization(
                    $organization
                );
            }
            $this->getPersonService()->getGearmanJobLauncherService()->triggerUpdateSearchIndexPerson($person);
            $this->getPersonService()->getGearmanJobLauncherService()->triggerUpdateSearchIndexOrganization(
                $organization
            );
        } catch (\Exception $e) {
            $this->getLoggerService()->error("! closeOrganizationPerson : $person > $organization > $role");
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQuery()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('o')
            ->from(Organization::class, 'o');
        return $queryBuilder;
    }

    const STRUCTURES_BASE_DN = 'ou=structures,dc=unicaen,dc=fr';
    const STAFF_ACTIVE_OR_DISABLED = 'ou=people,dc=unicaen,dc=fr';

    /**
     * @return array
     * @throws OscarException
     */
    public function getOrganizationsRolesAndUsage(): array
    {
        try {
            return $this->getOrganizationRoleRepository()->getRolesAndUsage();
        } catch (\Exception $e) {
            $msg = "Impossible de charger les roles des organisations";
            $this->getLoggerService()->error("$msg : " . $e->getMessage());
            throw new OscarException($msg);
        }
    }

    /**
     * Procédure de "fusion" des roles des organisations. Cela fait :
     * - Mise à jour des affectations des organisations dans les activités/projets
     * - Mise à jour des notifications si besoin.
     *
     * @param $fromId
     * @param $destId
     * @return void
     * @throws OscarException
     */
    public function mergeRoleOrganization($fromId, $destId): void
    {
        /** @var OrganizationRole $from */
        $from = $this->getOrganizationRoleRepository()->find($fromId);

        /** @var OrganizationRole $to */
        $to = $this->getOrganizationRoleRepository()->find($destId);

        $notificationsUpdate = false;

        // --------------------------------------------------------------
        // Note métier :
        // En migrant d'un role principal vers un role non-principale, et inversement,
        // il faut mettre à jour les notifications des structures
        // Donc on commence par vérifier si le role source et destination sont différents (pricipal ou pas)

        if ($from->isPrincipal() != $to->isPrincipal()) {
            $organizationsIds = $this->getOrganizationRepository()->getOrganizationsIdWithRole($from);
            $this->getLoggerService()->debug(sprintf("%s organisation(s) à recalculer", count($organizationsIds)));
            $notificationsUpdate = count($organizationsIds) > 0;
        }

        try {
            $this->getOrganizationRoleRepository()->merge($from, $to);
        } catch (\Exception $exception) {
            throw new OscarException($exception->getMessage());
        }

        if ($notificationsUpdate) {
            try {
                $organizations = $this->getOrganizationsByIds($organizationsIds);
                foreach ($organizations as $organization) {
                    $this->getPersonService()->getGearmanJobLauncherService()->triggerUpdateNotificationOrganization(
                        $organization
                    );
                }
            } catch (\Exception $exception) {
                $this->getLoggerService()->critical($exception->getMessage());
            }
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////// TYPES D'ORGANISATION
    ///
    public function updateOrCreateOrganizationType($datas)
    {
        $id = $datas['id'] ?? null;
        $type = null;
        if ($id) {
            /** @var OrganizationType $urganizationType */
            $type = $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(['id' => $id]);
        }
        else {
            $type = new OrganizationType();
            $this->getEntityManager()->persist($type);
        }

        $type->setLabel($datas['label']);
        $type->setDescription($datas['description']);
        $root = null;
        $root_id = intval($datas['root_id']);

        if ($root_id && $root_id != $type->getId()) {
            $root = $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(['id' => $root_id]);
        }

        $type->setRoot($root);
        $this->getEntityManager()->flush();

        return $type;
    }

    public function removeOrganizationType($id)
    {
        try {
            /** @var OrganizationType $urganizationType */
            $organizationType = $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(
                ['id' => $id]
            );

            /** @var OrganizationType $t */
            foreach ($organizationType->getChildren() as $t) {
                $t->setRoot(null);
            }

            $this->getEntityManager()->flush();
            $this->getEntityManager()->remove($organizationType);
            $this->getEntityManager()->flush($organizationType);
            return true;
//                        $t->setRoot(null);
//                    }

        } catch (NoResultException $e) {
            throw new OscarException(sprintf(_("Impossible de charger le type d'organisation '%s'."), $id));
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new OscarException(
                sprintf(
                    _("Impossible de supprimer le type d'organisation '%s', il est encore utilisé."),
                    $organizationType
                )
            );
        }
        throw new \Exception("A FAIRE !!");
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// REPOSITORIES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @return Country3166Repository
     */
    public function getCountries3166Repository()
    {
        return $this->getEntityManager()->getRepository(Country3166::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// REFERENCIELS
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateCountriesIso3166(): void
    {
        $referencielFilePath = realpath($this->getOscarConfigurationService()->getConfiguration('install.iso-3166'));
        if (!$referencielFilePath) {
            throw new OscarException("Fichier référenciel non-disponible");
        }

        $datas = json_decode(file_get_contents($referencielFilePath), true);
        $exists = $this->getCountries3166Repository()->allKeyByAlpha2();

        foreach ($datas as $data) {
            // Création du pays
            if (!array_key_exists($data['alpha2'], $exists)) {
                $country = new Country3166();
                $this->getEntityManager()->persist($country);
                $country->setAlpha2($data['alpha2'])
                    ->setAlpha3($data['alpha3'])
                    ->setEn($data['en'])
                    ->setFr($data['fr'])
                    ->setNumeric(intval($data['numeric']));
            }
            else {
                $country = $exists[$data['alpha2']];
                $country->setAlpha2($data['alpha2'])
                    ->setAlpha3($data['alpha3'])
                    ->setEn($data['en'])
                    ->setFr($data['fr'])
                    ->setNumeric(intval($data['numeric']));
            }
        }
        $this->getEntityManager()->flush();
    }

    public function getCountriesIso366(): array
    {
        return $this->getCountries3166Repository()->getAll();
    }

    public function getCountriesIso366Labels(): array
    {
        return $this->getCountries3166Repository()->getAllForSelects();
    }

    /**
     * Affiche les doublons d'utilisation des rôles des organisations
     * @return string[]
     */
    public function getOrganizationsRolesDoublonsPreview(): array
    {
        return array_merge(
            $this->getOrganizationRoleRepository()->getRoleDoublonsActivity(),
            $this->getOrganizationRoleRepository()->getRoleDoublonsProject()
        );
    }

    /**
     * Patch les doublons d'affectation des organisations aux activités/projet
     * @return mixed
     * @throws OscarException
     */
    public function organizationRoleDeDoublonnage()
    {
        // Récupération des données
        $inActivity = $this->getOrganizationRoleRepository()->getRoleDoublonsActivity();
        $toDelActivity = [];
        foreach ($inActivity as $activity) {
            $ids = json_decode($activity['activityorganization_id']);
            array_splice($ids, 0, 1);
            $toDelActivity = array_merge($toDelActivity, $ids);
        }
        $this->getLoggerService()->debug(implode(',', $toDelActivity));
        $this->getOrganizationRoleRepository()->doublonDeleteActivityOrganizationBydIds($toDelActivity);

        $inProject = $this->getOrganizationRoleRepository()->getRoleDoublonsProject();
        $toDelProject = [];
        foreach ($inProject as $activity) {
            $ids = json_decode($activity['projectpartner_id']);
            array_splice($ids, 0, 1);
            $toDelProject = array_merge($toDelProject, $ids);
        }
        $this->getLoggerService()->debug(implode(',', $toDelProject));
        $this->getOrganizationRoleRepository()->doublonDeleteProjectPartnerBydIds($toDelProject);
        return count($toDelActivity) + count($toDelProject);
    }
}
