<?php

namespace Oscar\Service;

use Doctrine\ORM\Exception\NotSupported;
use Exception;
use Laminas\Http\Request;
use Oscar\Entity\Activity;
use Oscar\Entity\DateType;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use Oscar\Traits\UseSpentService;
use Oscar\Traits\UseSpentServiceTrait;
use Oscar\Utils\ArrayUtils;
use Oscar\Utils\DateTimeUtils;

/**
 * Cette classe gère la recherche avancée des activités
 */
class ProjectGrantSearchService implements UseEntityManager, UsePersonService, UseOscarConfigurationService,
                                           UseProjectGrantService, UseLoggerService, UseSpentService
{

    use UseEntityManagerTrait, UsePersonServiceTrait, UseOscarConfigurationServiceTrait, UseProjectGrantServiceTrait, UseLoggerServiceTrait, UseSpentServiceTrait;

    const FILTER_PERSON_ROLLED = 'ap';
    const FILTER_PERSON_ROLLED_OUT = 'sp';
    const FILTER_PERSONS = 'pm';
    const FILTER_ORGANIZATION_ROLLED = 'ao';
    const FILTER_ORGANIZATION_ROLLED_OUT = 'so';
    const FILTER_ORGANIZATIONS = 'om';
    const FILTER_ACTIVITY_STATUS = 'as';
    const FILTER_ACTIVITY_STATUS_OUT = 'ss';
    const FILTER_ORGANIZATION_COUNTRY = 'cnt';
    const FILTER_ORGANIZATION_TYPE = 'tnt';

    const FILTER_ACTIVITY_FINANCIAL_IMPACT = 'af';
    const FILTER_ACTIVITY_FINANCIAL_IMPACT_OUT = 'sf';
    const FILTER_ACTIVITY_AMOUNT = 'mp';
    const FILTER_ACTIVITY_TYPE = 'at';
    const FILTER_ACTIVITY_TYPE_OUT = 'st';
    const FILTER_ACTIVITY_DOCUMENT_TYPE = 'td';
    const FILTER_ACTIVITY_DATE_START = 'add';
    const FILTER_ACTIVITY_DATE_END = 'adf';
    const FILTER_ACTIVITY_DATE_CREATED = 'adc';
    const FILTER_ACTIVITY_DATE_UPDATED = 'adm';
    const FILTER_ACTIVITY_DATE_SIGNED = 'ads';
    const FILTER_ACTIVITY_DATE_FINANCIAL_OPENED = 'adp';
    const FILTER_ACTIVITY_NO_PROJECT = 'pp';
    const FILTER_ACTIVITY_TIMESHEET = 'fdt';
    const FILTER_ACTIVITY_DISCIPLINE = 'ds';
    const FILTER_ACTIVITY_MILESTONE = 'aj';
    const FILTER_ACTIVITY_ACCOUNT = 'cb';
    const FILTER_ACTIVITY_NUMBERS = 'num';
    const SORT_HIT = 'hit';
    const SORT_DATE_CREATED = 'dateCreated';
    const SORT_DATE_START = 'dateStart';
    const SORT_DATE_END = 'dateEnd';
    const SORT_DATE_UPDATED = 'dateUpdated';
    const SORT_DATE_SIGNED = 'dateSigned';
    const SORT_DATE_OPENED = 'dateOpened';
    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DIRECTON_ASC = 'asc';
    const QUERY_PARAM_PAGE = 'page';
    const QUERY_PARAM_SEARCH = 'q';
    const QUERY_PARAM_SORT_IGNORE_NULL = 'sortIgnoreNull';
    const QUERY_PARAM_SORT_DIRECTION = 'sortDirection';
    const QUERY_PARAM_PROJECTVIEW = 'projectview';
    const QUERY_PARAM_CRITERIA = 'criteria';
    const QUERY_PARAM_SORT = 'sort';

    /**
     * Filtres de recherche disponibles pour la recherche avancée.
     *
     * @return string[]
     */
    public function getFiltersTypes(): array
    {
        return [
            // Personnes
            self::FILTER_PERSON_ROLLED                  => "Personne - (avec rôle) impliqué",
            self::FILTER_PERSON_ROLLED_OUT              => "Personne - NON impliquée",
            self::FILTER_PERSONS                        => "Personnes (plusieurs) - impliquées",
            // Organisations
            self::FILTER_ORGANIZATION_ROLLED            => "Organisation (avec rôle) - impliquée",
            self::FILTER_ORGANIZATION_ROLLED_OUT        => "Organisation - NON impliquée",
            self::FILTER_ORGANIZATIONS                  => "Organisations (plusieurs) - impliquées",
            self::FILTER_ORGANIZATION_COUNTRY           => "Organisation - Pays",
            self::FILTER_ORGANIZATION_TYPE              => "Organisation - Type",
            // Basique
            self::FILTER_ACTIVITY_STATUS                => 'Statut - AVEC',
            self::FILTER_ACTIVITY_STATUS_OUT            => 'Statut - SANS',
            self::FILTER_ACTIVITY_TYPE                  => 'Type - est de type',
            self::FILTER_ACTIVITY_TYPE_OUT              => 'Type - n\'est pas de type',
            self::FILTER_ACTIVITY_DISCIPLINE            => 'Ayant pour discipline',
            self::FILTER_ACTIVITY_MILESTONE             => 'Ayant le jalon',
            self::FILTER_ACTIVITY_DOCUMENT_TYPE         => 'Ayant ce type de document',
            self::FILTER_ACTIVITY_TIMESHEET             => 'Activités soumise à feuille de temps',
            // Dates
            self::FILTER_ACTIVITY_DATE_START            => 'Date de début',
            self::FILTER_ACTIVITY_DATE_END              => 'Date de fin',
            self::FILTER_ACTIVITY_DATE_CREATED          => 'Date de création',
            self::FILTER_ACTIVITY_DATE_UPDATED          => 'Date de dernière mise à jour',
            self::FILTER_ACTIVITY_DATE_SIGNED           => 'Date de signature',
            self::FILTER_ACTIVITY_DATE_FINANCIAL_OPENED => 'Date d\'ouverture du numéro financier (' . $this->getOscarConfigurationService(
                )->getFinancialLabel() . ')',
            self::FILTER_ACTIVITY_NUMBERS               => 'Ayant une numérotation',
            // Finances
            self::FILTER_ACTIVITY_FINANCIAL_IMPACT      => 'Incidence financière - AVEC',
            self::FILTER_ACTIVITY_FINANCIAL_IMPACT_OUT  => "Incidence financière (n'est pas)",
            self::FILTER_ACTIVITY_AMOUNT                => 'Montant prévu',
            self::FILTER_ACTIVITY_ACCOUNT               => 'Impliquant le compte',
            // Autres
            self::FILTER_ACTIVITY_NO_PROJECT            => 'Activités sans projet',
        ];
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// UTILS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Extraction d'un entier positif depuis une chaine de caractère.
     * @param string|null $input
     * @param int|null $defaultValue
     * @return int|null
     */
    protected function extractValuePositiveInt(?string $input, ?int $defaultValue = null): ?int
    {
        $int = (int)$input;
        if ($int > 0) {
            return $int;
        }
        return $defaultValue;
    }


    /**
     * Retourne $input si cette valeur est bien présente comme clef dans le tableau $arrayRef. Sinon, retourne la
     * première clef du tableau ou la valeur par défaut si elle est spécifiée.
     * @param string|null $input
     * @param array $arrayRef
     * @param string|null $defaultValue
     * @return string
     */
    protected function extractValueInArrayKey(?string $input, array $arrayRef, ?string $defaultValue = null): string
    {
        if ($input && array_key_exists($input, $arrayRef)) {
            return $arrayRef[$input];
        }
        elseif ($defaultValue !== null) {
            return array_key_first($arrayRef);
        }
        else {
            return $defaultValue;
        }
    }


    public function searchFromRequest(Request $request, ?array $organizationsPerimeter = null): array
    {
        // Traitement des données reçues
        $params = [];

        // --- Paramètres de base
        $params[self::QUERY_PARAM_PAGE] =
            $this->extractValuePositiveInt($request->getQuery(self::QUERY_PARAM_PAGE), 1);

        $search = $request->getQuery(self::QUERY_PARAM_SEARCH, null);
        if ($search !== null) {
            $search = trim($search);
        }
        $params['search'] = $search;
        $params['startEmpty'] = $search === true;


        $params['include'] = $request->getQuery('include', null);

        // Champ de tri
        $params[self::QUERY_PARAM_SORT] = $this->extractValueInArrayKey(
            $request->getQuery(self::QUERY_PARAM_SORT),
            $this->getSortOptions(),
            $search ? self::SORT_HIT : self::SORT_DATE_UPDATED
        );

        // Ignorer les valeurs nulles
        $params[self::QUERY_PARAM_SORT_IGNORE_NULL] =
            (bool)$request->getQuery(self::QUERY_PARAM_SORT_IGNORE_NULL, false);

        // Sens du tri
        $params[self::QUERY_PARAM_SORT_DIRECTION] = $this->extractValueInArrayKey(
            $request->getQuery(self::QUERY_PARAM_SORT_DIRECTION),
            $this->getSortDirections(),
            self::SORT_DIRECTION_DESC
        );

        // Mode PROJET
        $params[self::QUERY_PARAM_PROJECTVIEW] =
            $request->getQuery(self::QUERY_PARAM_PROJECTVIEW, '') == "on";

        // Récupération des filtres
        $params[self::QUERY_PARAM_CRITERIA] =
        $criteria = $request->getQuery(self::QUERY_PARAM_CRITERIA, []);
        // Extraction des filtres
        $filters = [];
        $filtersError = false;
        foreach ($criteria as $criterion) {
            $c = $criterion;
            $params = explode(';', $c);
            $type = $params[0];
            $value1 = (int)$params[1];
            $value2 = (int)$params[2];

            $crit = [
                'raw'      => $criterion,
                'type'     => $type,
                'key'      => uniqid('filter_'),
                'val1'     => $value1,
                'val2'     => $value2,
                'error'    => null,
                'took'     => null,
                'filtered' => null
            ];

            switch ($type) {
                case 'mp':
                    if (!$value1 && !$value2) {
                        $filtersError = true;
                        $crit['error'] = 'Plage numérique farfelue...';
                    }
                    break;

                // Personne (plusieurs)
                case 'pm' :
                    $value1 = ArrayUtils::explodeIntegerFromString($params[1]);
                    if( $value1 < 1 ) {
                        $filtersError = true;
                        $crit['error'] = "Identifiant de personne incorrecte";
                    }
                    $crit['val1'] = $value1;
                    break;

                case 'om' :
                    // TODO extraction d'un tableau d'entiers positifs
                    $value1 = explode(',', $params[1]);
                    $crit['val1'] = $value1;
                    break;

                ////////////////////////// PERSONNE (Avec/Sans)
                case 'ap' :
                case 'sp' :
                    if (!$value1 && !$value2) {
                        $filtersError = true;
                        $crit['error'] = "Aucun critère pour ce filtre";
                    }
                    break;

                // --- Impliquant un compte
                case 'cb':
                    // TODO extraction d'un tableau d'entiers positifs
                    $crit['val1'] = explode(',', $params[1]);
                    break;

                // --- Compte général
                case 'cb2':
                    // TODO extraction d'un tableau d'entiers positifs
                    $crit['val1'] = explode(',', $params[1]);
                    break;

                // --- Type de document
                case 'td':
                    if ($crit['val1'] == "null" || !$crit['val1']) {
                        $crit['val1'] = [];
                    }
                    else {
                        // TODO extraction d'un tableau d'entiers positifs
                        $crit['val1'] = explode(',', $params[1]);
                    }
                    break;

                case 'num' :
                    // TODO extraction d'un tableau d'entiers positifs
                    $value1 = $crit['val1'] = explode(',', $params[1]);
                    break;

                case 'ao' :
                case 'so' :
                    $crit['val1Label'] = "Non déterminé";
                    break;

                // Filtre sur le statut de l'activité
                case 'as' :
                case 'ss' :
                case 'at' :
                case 'st' :
                case 'af' :
                case 'sf' :
                    break;

                case 'cnt' :
                    if ($params[1]) {
                        $crit['val1'] = explode(',', $params[1]);
                    }
                    break;

                case 'tnt' :
                    if ($params[1]) {
                        $crit['val1'] = explode(',', $params[1]);
                    }
                    break;

                case 'aj':
                    $progressStr = $params[2];
                    $progressArray = null;

                    if ($progressStr != null && $progressStr != "" && $progressStr != 'null' && $progressStr != 'undefined') {
                        $progressArray = explode(',', $progressStr);
                        $crit['val1'] = $value1;
                        $crit['val2'] = ArrayUtils::implode(',', $progressArray);
                    }
                    else {
                        $crit['val2'] = '';
                    }
                    break;

                case 'ds' :
                    break;

                case 'add' :
                case 'adf' :
                case 'adm' :
                case 'adc' :
                case 'ads' :
                case 'adp' :
                    $start = DateTimeUtils::toDatetime($params[1]);
                    $end = DateTimeUtils::toDatetime($params[2]);
                    $value1 = $start ? $start->format('Y-m-d') : '';
                    $value2 = $end ? $end->format('Y-m-d') : '';
                    $crit['val1'] = $value1;
                    $crit['val2'] = $value2;
                    $clause = [];

                    // TODO tester les dates incohérentes

                    if ($start && $end && $start > $clause) {
                        $crit['error'] = 'Plage de date invalide';
                    }
                    break;
            }
            $filters[] = $crit;
        }

        $params['filters'] = $filters;

        // Périmètre de recherche
        $params['organizationsPerimeter'] = $organizationsPerimeter;

        $affectationsDetails = null;

        /* TODO En cas de périmètre organization, calculer les affectations de la personne pour les retourner */
        $affectationsDetails = [];

        $output = $this->searchAPI($params);

        $output["affectationsDetails"] = $affectationsDetails;

        return $output;
    }

    public function searchAPI(array $params): array
    {
        $time_start = time();

        $criterias = [];
        $times = [];

        // Personnes pour afficher le filtre des personnes
        $filterPersons = [];
        $persons = []; // Idem, doublon ?

        // Organisations pour afficher le filtre des organisations
        $filterOrganizations = [];

        // Pour les responsables de structures, détermine le cadre de recherche (IDs des organisations)
        $include = [];

        $error = null;
        $search = $params['search'];
        $startEmpty = $params['startEmpty'];
        $page = $params['page'];
        $organizationsPerimeter = $params['organizationsPerimeter'];
        $projectview = $params['projectview'];

        // Résultats
        $activities = null;
        $activitiesIds = null;
        $projects = null;
        $projectsIds = null;

        // --- Périmètre (Organization)
        if ($organizationsPerimeter !== null) {
            die("Calculer le périmètre organisation");
            // TODO IDs des activités réservées
            // TODO IDs des projets réservés
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // --- Recherche textuelle
        if ($params['search']) {
            // Recherche NUMERO OSCAR
            $oscarNumSeparator = $this->getOscarConfigurationService()->getConfiguration("oscar_num_separator");
            if (preg_match("/^[0-9]{4}" . $oscarNumSeparator . ".*/mi", $search)) {
                $this->getLoggerService()->debug("Recherche stricte sur le Numéro OSCAR : $search");
                $idsActivitySearch = $this->getProjectGrantService()
                    ->getActivityRepository()
                    ->getActivityIdsByOscarNumLike($search . "%");

                $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                    array_intersect($idsActivitySearch, $activitiesIds);
            }

            // --- La saisie est un PFI
            elseif ($this->getOscarConfigurationService()->isPfiStrict()
                && preg_match($this->getOscarConfigurationService()->getValidationPFI(), $search)) {
                $this->getLoggerService()->debug(
                    "Recherche sur le PFI (regex: " . $this->getOscarConfigurationService()->getValidationPFI() . ")"
                );;

                $idsActivitySearch = $this->getProjectGrantService()
                    ->getActivityRepository()
                    ->getActivityIdsByPFI($search);

                $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                    array_intersect($idsActivitySearch, $activitiesIds);
            }

            // --- Recherche textuelle
            else {
                try {
                    $idsSearched = $this->getProjectGrantService()->search($search, true);
                    $idsActivitySearch = $idsSearched['activity_ids'];
                    $this->getLoggerService()->debug(
                        "La recherche textuelle a retournée " . count($idsActivitySearch) . " résultat(s) en " .
                        (time() - $time_start) . " ms"
                    );;

                    $activitiesIds = $activitiesIds === null ? $idsActivitySearch :
                        array_intersect($idsActivitySearch, $activitiesIds);
                } catch (Exception $e) {
                    $error = "Erreur Elasticsearch : " . $e->getMessage();
                    $idsActivityRestricted = [];
                    $idsProjectRestricted = [];
                }
            }
        }

        foreach ($params['filters'] as &$filter) {
            var_dump($filter);
            $value1 = $filter['val1'];
            $value2 = $filter['val2'];

            switch ($filter['type']) {
                ///////////////////////////////// Filtre PERSONNE/ROLE
                case 'ap' :
                case 'sp' :
                    try {

                            $personIds = [];
                            $roleId = 0;
                            $role = null;

                            // Personne
                            if ($value1) {
                                try {
                                    $person = $this->getProjectGrantService()->getPersonService()->getPerson($value1);
                                    $personIds = [$person->getId()];
                                    $persons[$person->getId()] = $person;
                                    $filter['val1Label'] = $person->getDisplayName();
                                } catch (Exception $e) {
                                    $this->getLoggerService()->error(
                                        "Erreur filtre 'sur la personne/role, impossible de charger la personne '$value1'' : " . $e->getMessage(
                                        )
                                    );
                                    $filter['error'] = "Impossible de trouver la personne";
                                }
                            }
                            if ($value2) {
                                try {
                                    $roles = $this->getProjectGrantService()->getOscarUserContextService()->getAllRoleIdPerson();
                                    if (array_key_exists($value2, $roles)) {
                                        $roleId = $value2;
                                        $role = $roles[$value2];
                                        $filter['val2Label'] = $role;
                                    }
                                } catch (Exception $e) {
                                    $this->getLoggerService()->error(
                                        "Erreur filtre 'sur la personne/role, impossible de charger le rôle '$value2'' : " . $e->getMessage(
                                        )
                                    );
                                    $filter['error'] = "Impossible de trouver le rôle";
                                }
                            }

                            $idsActivity = $this->getProjectGrantService()->getActivityRepository()
                                ->getIdsForPersonAndOrWithRole($personIds, $roleId);

                            $activitiesIds = $activitiesIds == null ? $idsActivity :
                                array_intersect($activitiesIds, $idsActivity);


                    } catch (Exception $e) {
                        $this->getLoggerService()->error(
                            "Erreur filtre 'sur la personne '$value1'/role '$roleId' : " . $e->getMessage()
                        );
                        $filter['error'] = "Impossible de filtrer sur la personne";
                    }
                    break;

            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // --- FILTRES
//        switch ($type) {
//
//            case 'mp':
//                $clause = [];
//
//                if ($value1) {
//                    $clause[] = 'c.amount >= :amountMin';
//                    $parameters['amountMin'] = $value1;
//                }
//                if ($value2) {
//                    $clause[] = 'c.amount <= :amountMax';
//                    $parameters['amountMax'] = $value2;
//                }
//
//                if (!$value1 && !$value2) {
//                    $crit['error'] = 'Plage numérique farfelue...';
//                }
//                else {
//                    $qb->andWhere(ArrayUtils::implode(' AND ', $clause));
//                }
//                break;
//
//            case 'pp' :
//                $qb->andWhere('c.project IS NULL');
//                break;
//
//            // Filtre sur les activités ayant des feuilles de temps (Lot de travail)
//            case 'fdt' :
//                $ids = $this->getProjectGrantService()->getActivityRepository()->getActivityIdsWithWorkpackage(
//                );
//                break;
//
//            // Personne (plusieurs)
//            case 'pm' :
//                $value1 = \Oscar\Utils\ArrayUtils::explodeIntegerFromString($params[1]);
//                $crit['val1'] = $value1;
//                $filterPersons = $this->getPersonService()->getPersonRepository()->getPersonsByIds_idValue(
//                    $value1
//                );
//                $ids = $this->getProjectGrantService()->getActivityRepository()
//                    ->getIdsForPersons(array_keys($filterPersons));
//                break;
//
//            case 'om' :
//                $value1 = explode(',', $params[1]);
//                $crit['val1'] = $value1;
//                $organisationsRequire = $this->getOrganizationService()->getOrganizationsByIds($value1);
//
//                foreach ($organisationsRequire as $organisation) {
//                    $filterOrganizations[$organisation->getId()] = (string)$organisation;
//                }
//
//                $ids = $this->getActivityService()->getActivityRepository()->getIdsWithOneOfOrganizationsRoled(
//                    $value1
//                );
//
//                break;
//
//            ////////////////////////// PERSONNE (Avec/Sans)
//            case 'ap' :
//            case 'sp' :
//                try {
//                    if (!$value1 && !$value2) {
//                        $crit['error'] = "Aucun critère pour ce filtre";
//                    }
//                    else {
//                        $personIds = [];
//                        $roleId = 0;
//                        $role = null;
//
//                        // Personne
//                        if ($value1) {
//                            try {
//                                $person = $this->getPersonService()->getPerson($value1);
//                                $personIds = [$person->getId()];
//                                $persons[$person->getId()] = $person;
//                                $crit['val1Label'] = $person->getDisplayName();
//                            } catch (Exception $e) {
//                                $this->getLoggerService()->error(
//                                    "Erreur filtre 'sur la personne/role, impossible de charger la personne '$value1'' : " . $e->getMessage(
//                                    )
//                                );
//                                $crit['error'] = "Impossible de trouver la personne";
//                            }
//                        }
//                        if ($value2) {
//                            try {
//                                $roles = $this->getOscarUserContextService()->getAllRoleIdPerson();
//                                if (array_key_exists($value2, $roles)) {
//                                    $roleId = $value2;
//                                    $role = $roles[$value2];
//                                    $crit['val2Label'] = $role;
//                                }
//                            } catch (Exception $e) {
//                                $this->getLoggerService()->error(
//                                    "Erreur filtre 'sur la personne/role, impossible de charger le rôle '$value2'' : " . $e->getMessage(
//                                    )
//                                );
//                                $crit['error'] = "Impossible de trouver le rôle";
//                            }
//                        }
//
//                        $idsActivity = $this->getActivityService()->getActivityRepository()
//                            ->getIdsForPersonAndOrWithRole($personIds, $roleId);
//
//                        $idsProject = $this->getActivityService()->getActivityRepository()
//                            ->getIdsProjectsForPersonAndOrWithRole($personIds, $roleId);
//
//                        $idsActivityRestricted = array_intersect($idsActivityRestricted, $idsActivity);
//                        $idsProjectRestricted = array_intersect($idsProjectRestricted, $idsProject);
//                    }
//                } catch (Exception $e) {
//                    $this->getLoggerService()->error(
//                        "Erreur filtre 'sur la personne '$value1'/role '$roleId' : " . $e->getMessage()
//                    );
//                    $crit['error'] = "Impossible de filtrer sur la personne";
//                }
//                break;
//
//            // --- Impliquant un compte
//            case 'cb':
//                $value1 = $crit['val1'] = explode(',', $params[1]);
//                try {
//                    $compteGeneralList = $accountsInfos->getCompteGeneralListByAccountIds($value1);
//                    $idsActivity = $this->getSpentService()->getIdsActivitiesForAccounts($compteGeneralList);
//                    $idsProject = $this->getActivityService()->getActivityRepository()
//                        ->getIdsProjectsForActivity($ids);
//                    $idsActivityRestricted = array_intersect($idsActivityRestricted, $idsActivity);
//                    $idsProjectRestricted = array_intersect($idsProjectRestricted, $idsProject);
//                } catch (Exception $e) {
//                    $crit['error'] = "Impossible de filtrer sur le compte";
//                }
//                break;
//
//
//            // --- Compte général
//            case 'cb2':
//                $value1 = $crit['val1'] = explode(',', $params[1]);
//
//                try {
//                    $idsActivity = $this->getSpentService()->getIdsActivitiesForCompteGeneral($value1);
//                    $idsProject = $this->getActivityService()->getActivityRepository(
//                    )->getIdsProjectsForActivity($idsActivity);
//                    $idsActivityRestricted = array_intersect($idsActivityRestricted, $idsActivity);
//                    $idsProjectRestricted = array_intersect($idsProjectRestricted, $idsProject);
//                } catch (Exception $e) {
//                    throw new OscarException($e->getMessage());
//                }
//                break;
//
//            // --- Type de document
//            case 'td':
//                if ($crit['val1'] == "null" || !$crit['val1']) {
//                    $value1 = [];
//                }
//                else {
//                    $value1 = $crit['val1'] = explode(',', $params[1]);
//                }
//
//                try {
//                    $reverse = $value2 == "1";
//                    $ids = $this->getActivityService()->getActivitiesIdsWithTypeDocument($value1, $reverse);
//                    $idsProject = $this->getActivityService()->getActivityRepository(
//                    )->getIdsProjectsForActivity($ids);
//                    $idsActivityRestricted = array_intersect($idsActivityRestricted, $ids);
//                    $idsProjectRestricted = array_intersect($idsProjectRestricted, $idsProject);
//                } catch (Exception $e) {
//                    throw new OscarException($e->getMessage());
//                }
//                break;
//
//            case 'num' :
//                $value1 = $crit['val1'] = explode(',', $params[1]);
//                try {
//                    $ids = $this->getActivityService()->getActivitiesWithNumerotation($value1);
//                } catch (Exception $e) {
//                    throw new OscarException($e->getMessage());
//                }
//                break;
//
//            case 'ao' :
//            case 'so' :
//                $organizationId[] = $value1;
//
//                $crit['val1Label'] = "Non déterminé";
//                $organization = null;
//
//                $organizationId = (int)$value1;
//                $roleId = (int)$value2;
//
//                // Récupération de l'organisation
//                if ($organizationId > 0) {
//                    try {
//                        $organization = $this->getOrganizationService()->getOrganization($value1);
//                        $organizations[$organization->getId()] = $organization;
//                        $crit['val1Label'] = (string)$organization;
//                    } catch (Exception $e) {
//                    }
//                }
//
//                try {
//                    $ids = $this->getActivityService()->getActivityRepository()->getIdsWithOrganizationAndRole(
//                        $organizationId,
//                        $roleId
//                    );
//                } catch (Exception $e) {
//                    $crit['error'] = $e->getMessage();
//                }
//
//                break;
//
//            // Filtre sur le statut de l'activité
//            case 'as' :
//                if (!isset($parameters['withstatus'])) {
//                    $parameters['withstatus'] = [];
//                }
//                $parameters['withstatus'][] = $value1;
//                $qb->andWhere('c.status IN (:withstatus)');
//                break;
//
//            case 'ss' :
//                if (!isset($parameters['withoutstatus'])) {
//                    $parameters['withoutstatus'] = [];
//                }
//                $parameters['withoutstatus'][] = $value1;
//                $qb->andWhere('c.status NOT IN (:withoutstatus)');
//                break;
//
//            case 'at' :
//
//                if (!isset($parameters['withtype'])) {
//                    $parameters['withtype'] = [];
//                    $qb->andWhere('c.activityType IN (:withtype)');
//                }
//
//                if ($value2 == 1) {
//                    $types = [$value1];
//                }
//                else {
//                    $types = $this->getActivityTypeService()->getTypeIdsInside($value1);
//                }
//
//                $parameters['withtype'] = array_merge(
//                    $parameters['withtype'],
//                    $types
//                );
//                $result = $qb->setParameters($parameters)->getQuery()->getResult();
//                break;
//
//            case 'st' :
//                if (!isset($parameters['withouttype'])) {
//                    $parameters['withouttype'] = [];
//                    $qb->andWhere('c.activityType NOT IN (:withouttype)');
//                }
//                $parameters['withouttype'] = array_merge(
//                    $parameters['withouttype'],
//                    $this->getActivityTypeService()->getTypeIdsInside($value1)
//                );
//                break;
//
//            // Filtre sur la/les incidences financière
//            case 'af' :
//                if (!isset($parameters['withfinancial'])) {
//                    $parameters['withfinancial'] = [];
//                    $qb->andWhere('c.financialImpact IN (:withfinancial)');
//                }
//                $parameters['withfinancial'][] = Activity::getFinancialImpactValues()[$value1];
//                break;
//
//            case 'sf' :
//                if (!isset($parameters['withoutfinancial'])) {
//                    $parameters['withoutfinancial'] = [];
//                    $qb->andWhere('c.financialImpact NOT IN (:withoutfinancial)');
//                }
//                $parameters['withoutfinancial'][] = Activity::getFinancialImpactValues()[$value1];
//                break;
//
//            case 'cnt' :
//                if ($params[1]) {
//                    $value1 = $crit['val1'] = explode(',', $params[1]);
//                    $ids = $this->getActivityService()->getActivityRepository()
//                        ->getIdsWithOrganizationOfCountry($value1);
//                }
//                break;
//
//            case 'tnt' :
//                if ($params[1]) {
//                    $value1 = $crit['val1'] = explode(',', $params[1]);
//                    $typeIds = $this->getOrganizationService()->getTypesIdsByLabel($value1);
//                    $ids = $this->getActivityService()->getActivityRepository()
//                        ->getIdsWithOrganizationOfType($typeIds);
//                }
//                break;
//
//            case 'aj':
//                $progressStr = $params[2];
//                $progressArray = null;
//
//                if ($progressStr != null && $progressStr != "" && $progressStr != 'null' && $progressStr != 'undefined') {
//                    $progressArray = explode(',', $progressStr);
//                    $crit['val1'] = $value1;
//                    $crit['val2'] = ArrayUtils::implode(',', $progressArray);
//                }
//                else {
//                    $crit['val2'] = '';
//                }
//                $filterIds = $this->getActivityService()->getActivityIdsByJalon($crit['val1'], $progressArray);
//                break;
//
//            case 'ds' :
//                $qb->andWhere('dis.id = :discipline');
//                $parameters['discipline'] = $value1;
//                break;
//
//            case 'add' :
//            case 'adf' :
//            case 'adm' :
//            case 'adc' :
//            case 'ads' :
//            case 'adp' :
//                $field = $dateFields[$type];
//
//                $start = DateTimeUtils::toDatetime($params[1]);
//                $end = DateTimeUtils::toDatetime($params[2]);
//                $value1 = $start ? $start->format('Y-m-d') : '';
//                $value2 = $end ? $end->format('Y-m-d') : '';
//                $crit['val1'] = $value1;
//                $crit['val2'] = $value2;
//                $clause = [];
//
//                if ($value1) {
//                    $clause[] = 'c.' . $field . ' >= :' . $filterKey . 'start';
//                    $parameters[$filterKey . 'start'] = $value1;
//                }
//                if ($value2) {
//                    $clause[] = 'c.' . $field . ' <= :' . $filterKey . 'end';
//                    $parameters[$filterKey . 'end'] = $value2;
//                }
//
//                if ($clause) {
//                    $qb->andWhere(ArrayUtils::implode(' AND ', $clause));
//                }
//                else {
//                    $crit['error'] = 'Plage de date invalide';
//                }
//                break;
//        }

        /////////////////////////////
        $totalResult = 0;
        $totalPages = 0;

        if ($startEmpty === false) {
            if (!$search && count($criterias) == 0) {
                if ($activitiesIds !== null) {
                    die("START EMPTY : TRUE");
                }
                else {
                    // AFFICHER TOUS
                    $allIds = $this->getEntityManager()->createQueryBuilder()
                        ->select('c.id')
                        ->from(Activity::class, 'c')
                        ->getQuery()
                        ->getResult();
                    $activitiesIds = array_map('current', $allIds);
                }
            }
            else {
            }
        }

        $idsRef = $activitiesIds;
        if ($projectview) {
            $idsRef = $projectsIds;
            die("VUE PROJET");
        }

        $totalResult = count($idsRef);
        $limit = 50;
        $totalPages = ceil($totalResult / $limit);
        $offset = ($page - 1) * $limit;
        $orderedIds = array_slice($idsRef, $offset, $limit);

        if ($projectview) {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('c')
                ->from(Project::class, 'c')
                ->where('c.id IN(:ids)')
                ->setParameter('ids', $idsProjectRestricted);
        }
        else {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('c')
                ->from(Activity::class, 'c')
                ->leftJoin('c.persons', 'm1')
                ->leftJoin('m1.person', 'pers1')
                ->leftJoin('c.disciplines', 'dis')
                ->leftJoin('c.activityType', 't1')
                ->leftJoin('c.organizations', 'p1')
                ->leftJoin('p1.organization', 'orga1')
                ->leftJoin('c.documents', 'd1')
                ->leftJoin('c.project', 'pr')
                ->leftJoin('pr.members', 'm2')
                ->leftJoin('pr.partners', 'p2')
                ->leftJoin('m2.person', 'pers2')
                ->leftJoin('p2.organization', 'orga2')
                ->where('c.id IN(:ids)')
                ->setParameter('ids', $orderedIds);
        }

        $activities = $qb->getQuery()->getResult();

        $output = [
            "took_ms"                => 0,
            "took_ms_filter"         => 0,
            "took_ms_search"         => 0,
            "params_requested"       => $params,
            "errors"                 => null,
            "page"                   => $params['page'] ?? 1,
            "totalResult"            => $totalResult,
            'totalPages'             => $totalPages,
            'projectview'            => $params['projectview'],
            'exportIds'              => [], //ArrayUtils::implode(',', $idsExport),
            'error'                  => $error,
            'criteria'               => $criterias,
            'persons'                => $persons,
            'activities'             => $activities,
            'search'                 => $params['search'],
            'filterPersons'          => $filterPersons,
            'filterOrganizations'    => $filterOrganizations,
            'include'                => $include,
            'organizationsPerimeter' => $params['organizationsPerimeter'],
            'sort'                   => $params['sort'],
            'sortDirection'          => $params['sortDirection'],
            'sortIgnoreNull'         => $params['sortIgnoreNull'],
            // Valeurs pour les champs de formulaire
            'filtersType'            => $this->getFiltersTypes(),
            'fieldsCSV'              => $this->getFilterOptionsFieldCSV(),
            'typeorgas'              => $this->getFilterOptionsOrganizationTypes(),
            'sortCriteria'           => $this->getSortOptions(),
            'countries'              => $this->getFilterOptionsCountries(),
            'filterJalons'           => $this->getFilterOptionsMilestones(),
            'numerotations'          => $this->getFilterOptionsActivityNumbers(),
            'accounts'               => $this->getFilterOptionsAccountsUsed(),
            'types'                  => $this->getFilterOptionsActivityTypes(),
            'documentsTypes'         => $this->getFilterOptionsDocumentTypes(),
            'disciplines'            => $this->getFilterOptionsDisciplines(),
            'projectsIds'            => $projectsIds,
        ];

        $time_end = time();

        $output['took'] = $time_end - $time_start;

        return $output;
    }

    /**
     * Retourne les options pour les jalons.
     * @throws NotSupported
     */
    public function getFilterOptionsMilestones(): array
    {
        /* TODO Utiliser la méthode 'ProjectGrantService->getDateTypesSelect()'
            pour afficher les facettes */
        $jalonsFilters = [];
        $jalons = $this->getEntityManager()->getRepository(DateType::class)->findAll();
        /** @var DateType $jalon */
        foreach ($jalons as $jalon) {
            $jalonsFilters[] = [
                'id'         => $jalon->getId(),
                'label'      => $jalon->getLabel(),
                'finishable' => $jalon->isFinishable()
            ];
        }
        return $jalonsFilters;
    }

    /**
     * Retourne la liste des comptes (Plan comptable) utilisés dans les dépenses.
     * @return array
     */
    private function getFilterOptionsAccountsUsed(): array
    {
        return $this->getSpentService()->getAccountsInfosUsed()->getAccounts();
    }

    /**
     * Retourne la liste des numérotations personnalisées dans les activités.
     * @return array
     */
    private function getFilterOptionsActivityNumbers(): array
    {
        return $this->getOscarConfigurationService()->getNumerotationKeys();
    }

    /**
     * Liste des types de document.
     * @return array
     */
    private function getFilterOptionsDocumentTypes(): array
    {
        return $this->getProjectGrantService()->getDocumentTypes();
    }

    /**
     * Liste des types d'activités.
     * @return array
     */
    private function getFilterOptionsActivityTypes(): array
    {
        return $this->getProjectGrantService()->getActivityTypes(true);
    }

    /**
     * Liste des disciplines.
     * @return array
     */
    private function getFilterOptionsDisciplines(): array
    {
        return $this->getProjectGrantService()->getDisciplines();
    }

    /**
     * Liste des pays.
     * @return array
     */
    private function getFilterOptionsCountries(): array
    {
        return $this->getProjectGrantService()->getOrganizationService()->getCountriesList();
    }

    /**
     * Liste des types d'organisations.
     * @return array
     */
    private function getFilterOptionsOrganizationTypes(): array
    {
        return $this->getProjectGrantService()->getOrganizationService()->getOrganizationTypesSelect();
    }

    private function getFilterOptionsFieldCSV(): array
    {
        return $this->getProjectGrantService()->getFieldsCSV();
    }

    /**
     * Correspondance entre les filtres de date et les champs.
     * @return string[]
     */
    public function getFiltersDateMatch(): array
    {
        // Correspondance des champs de type date
        return [
            'add' => self::SORT_DATE_START,
            'adc' => self::SORT_DATE_CREATED,
            'adf' => self::SORT_DATE_END,
            'adm' => self::SORT_DATE_UPDATED,
            'ads' => self::SORT_DATE_SIGNED,
            'adp' => self::SORT_DATE_OPENED,
        ];
    }

    /**
     * Retourne les options de tri disponibles (Champs).
     * @return string[]
     */
    public function getSortOptions(): array
    {
        // Critères de tri
        return [
            self::SORT_HIT          => 'Pertinence (Recherche textuelle)',
            self::SORT_DATE_CREATED => 'Date de création',
            self::SORT_DATE_START   => 'Date début',
            self::SORT_DATE_END     => 'Date fin',
            self::SORT_DATE_UPDATED => 'Date de mise à jour',
            self::SORT_DATE_SIGNED  => 'Date de signature',
            self::SORT_DATE_OPENED  => "Date d'ouverture du " . $this->getOscarConfigurationService(
                )->getFinancialLabel()
        ];
    }

    /**
     * Retourne les options de tri disponibles (Directions).
     * @return string[]
     */
    public function getSortDirections(): array
    {
        // Trie
        return [
            self::SORT_DIRECTION_DESC => 'Décroissant',
            self::SORT_DIRECTON_ASC   => 'Croissant'
        ];
    }
}
